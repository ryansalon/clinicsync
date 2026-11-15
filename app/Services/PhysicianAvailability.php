<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Physician;
use App\Models\PhysicianSchedule;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;

class PhysicianAvailability
{
    /** How far ahead a date may be booked; mirrors the +90 days validation rule. */
    public const BOOKING_HORIZON_DAYS = 90;

    private const CALENDAR_VERSION_KEY = 'availability:calendar:version';

    public static function flushCalendarCache(): void
    {
        Cache::forever(self::CALENDAR_VERSION_KEY, ((int) Cache::get(self::CALENDAR_VERSION_KEY, 1)) + 1);
    }

    /**
     * Check whether a proposed recurring window overlaps another schedule for the
     * same physician, weekday and overlapping effective date range.
     */
    public function hasOverlappingSchedule(
        int $physicianId,
        int $dayOfWeek,
        string $startTime,
        string $endTime,
        ?string $effectiveFrom = null,
        ?string $effectiveUntil = null,
        ?int $ignoreId = null,
    ): bool {
        $start = strlen($startTime) === 5 ? $startTime.':00' : $startTime;
        $end = strlen($endTime) === 5 ? $endTime.':00' : $endTime;

        return PhysicianSchedule::query()
            ->where('physician_id', $physicianId)
            ->where('day_of_week', $dayOfWeek)
            ->when($ignoreId !== null, fn ($query) => $query->where('id', '!=', $ignoreId))
            ->get()
            ->contains(function (PhysicianSchedule $existing) use ($start, $end, $effectiveFrom, $effectiveUntil): bool {
                $existingStart = strlen($existing->start_time) === 5 ? $existing->start_time.':00' : $existing->start_time;
                $existingEnd = strlen($existing->end_time) === 5 ? $existing->end_time.':00' : $existing->end_time;

                if (! $this->timesOverlap($existingStart, $existingEnd, $start, $end)) {
                    return false;
                }

                $fromA = $effectiveFrom ?? '0000-01-01';
                $untilA = $effectiveUntil ?? '9999-12-31';
                $fromB = $existing->effective_from?->toDateString() ?? '0000-01-01';
                $untilB = $existing->effective_until?->toDateString() ?? '9999-12-31';

                return $fromA <= $untilB && $untilA >= $fromB;
            });
    }

    /**
     * Bookable slots for a physician on a single date, capped by the daily
     * patient limit so the grid never offers more patients than the clinic set.
     *
     * @param  int|null  $exceptAppointmentId  Appointment allowed to keep its slot (reschedules)
     * @return array{slot_minutes: int, remaining: int, full: bool, daily_patient_limit: int, physician_name: string|null, slots: array<int, array{time: string, label: string, available: bool}>}
     */
    public function slots(Physician $physician, string $date, ?int $exceptAppointmentId = null): array
    {
        $physician->loadMissing('user');

        $slotMinutes = max((int) $physician->appointment_slot_minutes, 1);
        $booked = $this->bookedInRange([$physician->id], $date, $date, $exceptAppointmentId);
        $remaining = $this->capacityRemaining($physician, $booked->count());

        $schedules = $this->schedulesOn($physician, $date);

        if ($schedules->isEmpty()) {
            return [
                'slot_minutes' => $slotMinutes,
                'remaining' => $remaining,
                'full' => $remaining <= 0,
                'daily_patient_limit' => (int) $physician->daily_patient_limit,
                'physician_name' => $physician->user?->name,
                'slots' => [],
            ];
        }

        $slots = $this->generateSlots(
            $schedules,
            $date,
            $slotMinutes,
            $this->activeOnly($booked),
            $remaining > 0,
        );

        return [
            'slot_minutes' => $slotMinutes,
            'remaining' => $remaining,
            'full' => ! collect($slots)->contains('available', true),
            'daily_patient_limit' => (int) $physician->daily_patient_limit,
            'physician_name' => $physician->user?->name,
            'slots' => $slots,
        ];
    }

    /**
     * Patients the physician may still accept on the given date.
     *
     * @param  int|null  $exceptAppointmentId  Appointment allowed to keep its slot (reschedules)
     * @param  bool  $lockRows  Take a locking read, for recounts inside booking transactions
     */
    public function remainingCapacity(
        Physician $physician,
        string $date,
        ?int $exceptAppointmentId = null,
        bool $lockRows = false,
    ): int {
        $booked = Appointment::query()
            ->forPhysician($physician->id)
            ->forDate($date)
            ->whereNotIn('status', [Appointment::STATUS_CANCELLED])
            ->when(
                $exceptAppointmentId !== null,
                fn ($query) => $query->where('id', '!=', $exceptAppointmentId),
            )
            ->when($lockRows, fn ($query) => $query->lockForUpdate())
            ->count();

        return $this->capacityRemaining($physician, $booked);
    }

    /**
     * Re-check the daily limit inside a booking transaction, where validation
     * has already passed. Concurrent bookings serialize on the physician row
     * and the recount locks what it reads, so two simultaneous requests can
     * never push a physician past the limit.
     *
     * @param  int|null  $exceptAppointmentId  Appointment allowed to keep its slot (reschedules)
     *
     * @throws ValidationException when the limit is reached
     */
    public function guardCapacity(Physician $physician, string $date, ?int $exceptAppointmentId = null): void
    {
        Physician::query()->whereKey($physician->id)->lockForUpdate()->first();

        if ($this->remainingCapacity($physician, $date, $exceptAppointmentId, lockRows: true) > 0) {
            return;
        }

        throw ValidationException::withMessages([
            'appointment_date' => $this->limitReachedMessage($physician),
        ]);
    }

    /**
     * The patient-facing message shown when a physician is at the daily limit.
     */
    public function limitReachedMessage(Physician $physician): string
    {
        $physician->loadMissing('user');

        $who = $physician->user?->name !== null ? 'Dr. '.$physician->user->name : 'This physician';
        $limit = (int) $physician->daily_patient_limit;

        return 'This date is full — '.$who.' can cater at most '.$limit.' patient'.($limit === 1 ? '' : 's').' a day.';
    }

    /**
     * Whether an active appointment already occupies part of the window.
     */
    public function hasConflict(
        Physician $physician,
        string $date,
        string $start,
        string $end,
        ?int $exceptAppointmentId = null,
    ): bool {
        return Appointment::query()
            ->forPhysician($physician->id)
            ->forDate($date)
            ->active()
            ->when(
                $exceptAppointmentId !== null,
                fn ($query) => $query->where('id', '!=', $exceptAppointmentId),
            )
            ->where('start_time', '<', $end)
            ->where('end_time', '>', $start)
            ->exists();
    }

    /**
     * The physician's active schedule windows that apply on the given date.
     *
     * @return Collection<int, PhysicianSchedule>
     */
    public function schedulesOn(Physician $physician, string $date): Collection
    {
        $weekday = (int) Carbon::parse($date)->format('w');

        return $physician->schedules
            ->filter(fn (PhysicianSchedule $schedule) => $schedule->is_active
                && (int) $schedule->day_of_week === $weekday
                && $this->scheduleAppliesOn($schedule, $date))
            ->sortBy('start_time')
            ->values();
    }

    /**
     * Month calendar payload: for every date of the month, which physicians
     * can still be booked and whether the date itself is full.
     *
     * @return array{month: string, today: string, horizon: string, physicians: array<int, array{id: int, name: string, specialization: string, daily_patient_limit: int}>, dates: array<string, array{state: string, physicians: array<int, array{id: int, state: string, remaining: int}>}>}
     */
    public function calendarMonth(string $month): array
    {
        $compute = function () use ($month): array {
            $monthStart = Carbon::parse($month.'-01')->startOfMonth();
            $monthEnd = $monthStart->copy()->endOfMonth();
            $today = Carbon::today();
            $horizon = $today->copy()->addDays(self::BOOKING_HORIZON_DAYS);

            $physicians = Physician::query()
                ->with(['user', 'schedules' => fn ($query) => $query->active()->orderBy('day_of_week')->orderBy('start_time')])
                ->orderBy('id')
                ->get();

            $computeStart = $monthStart->lt($today) ? $today : $monthStart;
            $computeEnd = $monthEnd->gt($horizon) ? $horizon : $monthEnd;

            $booked = $computeStart->lte($computeEnd)
                ? $this->bookedInRange(
                    $physicians->pluck('id')->all(),
                    $computeStart->toDateString(),
                    $computeEnd->toDateString(),
                )->groupBy(fn (Appointment $appointment) => $appointment->physician_id.'|'.$appointment->appointment_date->toDateString())
                : collect();

            $dates = [];

            for ($day = $monthStart->copy(); $day->lte($monthEnd); $day->addDay()) {
                $dateString = $day->toDateString();

                if ($day->lt($today)) {
                    $dates[$dateString] = ['state' => 'past', 'physicians' => []];

                    continue;
                }

                if ($day->gt($horizon)) {
                    $dates[$dateString] = ['state' => 'closed', 'physicians' => []];

                    continue;
                }

                $dates[$dateString] = $this->dateState($physicians, $dateString, $day->isToday(), $booked);
            }

            return [
                'month' => $month,
                'today' => $today->toDateString(),
                'horizon' => $horizon->toDateString(),
                'physicians' => $physicians->map(fn (Physician $physician) => [
                    'id' => $physician->id,
                    'name' => $physician->user?->name ?? 'Physician',
                    'specialization' => $physician->specialization,
                    'daily_patient_limit' => (int) $physician->daily_patient_limit,
                ])->values()->all(),
                'dates' => $dates,
            ];
        };

        if (Carbon::hasTestNow()) {
            return $compute();
        }

        $version = (int) Cache::get(self::CALENDAR_VERSION_KEY, 1);
        $key = "availability:calendar:{$version}:".Carbon::today()->toDateString().":{$month}";

        return Cache::remember($key, now()->addSeconds(60), $compute);
    }

    /**
     * Availability of every physician on one date.
     *
     * @param  Collection<string, Collection<int, Appointment>>  $booked  Appointments keyed by "physicianId|date"
     * @return array{state: string, physicians: array<int, array{id: int, state: string, remaining: int}>}
     */
    private function dateState(Collection $physicians, string $date, bool $isToday, Collection $booked): array
    {
        $entries = [];
        $anyAvailable = false;
        $anyScheduled = false;

        foreach ($physicians as $physician) {
            $schedules = $this->schedulesOn($physician, $date);

            if ($schedules->isEmpty()) {
                $entries[] = ['id' => $physician->id, 'state' => 'unavailable', 'remaining' => 0];

                continue;
            }

            $anyScheduled = true;

            $dayBooked = $booked->get($physician->id.'|'.$date, collect());
            $remaining = $this->capacityRemaining($physician, $dayBooked->count());

            if ($remaining <= 0) {
                $entries[] = ['id' => $physician->id, 'state' => 'full', 'remaining' => 0];

                continue;
            }

            $slots = $this->generateSlots(
                $schedules,
                $date,
                max((int) $physician->appointment_slot_minutes, 1),
                $this->activeOnly($dayBooked),
                true,
            );

            $free = (int) collect($slots)->where('available', true)->count();

            if ($free === 0) {
                $entries[] = ['id' => $physician->id, 'state' => 'full', 'remaining' => 0];

                continue;
            }

            $anyAvailable = true;
            $entries[] = [
                'id' => $physician->id,
                'state' => 'available',
                'remaining' => min($remaining, $free),
            ];
        }

        return [
            'state' => $anyAvailable ? 'available' : ($anyScheduled ? 'full' : 'none'),
            'physicians' => $entries,
        ];
    }

    /**
     * Generate the slot grid for a date, marking each time bookable or not.
     *
     * @param  Collection<int, PhysicianSchedule>  $schedules
     * @param  Collection<int, Appointment>  $activeBooked
     * @return array<int, array{time: string, label: string, available: bool}>
     */
    private function generateSlots(
        Collection $schedules,
        string $date,
        int $slotMinutes,
        Collection $activeBooked,
        bool $capacityOpen,
    ): array {
        $slots = [];

        foreach ($schedules as $schedule) {
            $cursor = Carbon::parse($schedule->start_time);
            $windowEnd = Carbon::parse($schedule->end_time);

            while ($cursor->copy()->addMinutes($slotMinutes)->lte($windowEnd)) {
                $start = $cursor->format('H:i:s');
                $end = $cursor->copy()->addMinutes($slotMinutes)->format('H:i:s');
                $slotStart = Carbon::parse($date)->setTimeFromTimeString($cursor->format('H:i'));

                $conflicting = $activeBooked->contains(
                    fn (Appointment $taken) => $this->timesOverlap($taken->start_time, $taken->end_time, $start, $end),
                );

                $slots[] = [
                    'time' => $cursor->format('H:i'),
                    'label' => $slotStart->format('g:i A'),
                    'available' => $capacityOpen && ! $conflicting && $slotStart->isFuture(),
                ];

                $cursor->addMinutes($slotMinutes);
            }
        }

        return collect($slots)->unique('time')->values()->all();
    }

    /**
     * Non-cancelled appointments for the physicians inside the date range.
     *
     * @param  array<int, int>  $physicianIds
     * @param  int|null  $exceptAppointmentId  Appointment allowed to keep its slot (reschedules)
     * @return Collection<int, Appointment>
     */
    private function bookedInRange(array $physicianIds, string $from, string $to, ?int $exceptAppointmentId = null): Collection
    {
        if ($physicianIds === []) {
            return collect();
        }

        return Appointment::query()
            ->whereIn('physician_id', $physicianIds)
            ->where('appointment_date', '>=', $from)
            ->where('appointment_date', '<', Carbon::parse($to)->addDay()->toDateString())
            ->whereNotIn('status', [Appointment::STATUS_CANCELLED])
            ->when(
                $exceptAppointmentId !== null,
                fn ($query) => $query->where('id', '!=', $exceptAppointmentId),
            )
            ->get(['id', 'physician_id', 'appointment_date', 'start_time', 'end_time', 'status']);
    }

    /**
     * @param  Collection<int, Appointment>  $booked
     * @return Collection<int, Appointment>
     */
    private function activeOnly(Collection $booked): Collection
    {
        return $booked->filter(
            fn (Appointment $appointment) => in_array($appointment->status, [
                Appointment::STATUS_PENDING,
                Appointment::STATUS_CONFIRMED,
            ], true),
        )->values();
    }

    private function capacityRemaining(Physician $physician, int $bookedCount): int
    {
        return max((int) $physician->daily_patient_limit - $bookedCount, 0);
    }

    private function scheduleAppliesOn(PhysicianSchedule $schedule, string $date): bool
    {
        if ($schedule->effective_from !== null && $schedule->effective_from->toDateString() > $date) {
            return false;
        }

        if ($schedule->effective_until !== null && $schedule->effective_until->toDateString() < $date) {
            return false;
        }

        return true;
    }

    /**
     * Whether two H:i:s windows overlap.
     */
    private function timesOverlap(string $aStart, string $aEnd, string $bStart, string $bEnd): bool
    {
        return $aStart < $bEnd && $aEnd > $bStart;
    }
}
