<?php

namespace App\Rules;

use App\Models\Physician;
use App\Models\PhysicianSchedule;
use App\Services\PhysicianAvailability;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Validator;

class AppointmentSlotAvailable
{
    public function __construct(
        private readonly Physician $physician,
        private readonly ?int $exceptAppointmentId = null,
    ) {}

    /**
     * Add validation errors to the given validator when the slot is unusable.
     *
     * @param  Validator  $validator  Validator receiving any errors
     */
    public function check(string $date, string $startTime, Validator $validator): void
    {
        try {
            $dateCarbon = Carbon::parse($date);
            $start = Carbon::parse($startTime);
        } catch (\Throwable) {
            $validator->errors()->add('start_time', 'The selected slot is invalid.');

            return;
        }

        $availability = new PhysicianAvailability;
        $dateString = $dateCarbon->toDateString();
        $slotMinutes = max((int) $this->physician->appointment_slot_minutes, 1);
        $end = $start->copy()->addMinutes($slotMinutes);

        $schedule = $availability->schedulesOn($this->physician, $dateString)
            ->first(fn (PhysicianSchedule $candidate) => $this->slotFitsWindow($start, $end, $candidate));

        if ($schedule === null) {
            $validator->errors()->add(
                'start_time',
                'The physician is not available on '.$dateCarbon->isoFormat('dddd, MMM D').' at '.$start->format('g:i A').'.',
            );

            return;
        }

        if ($availability->remainingCapacity($this->physician, $dateString, $this->exceptAppointmentId) <= 0) {
            $validator->errors()->add(
                'appointment_date',
                $availability->limitReachedMessage($this->physician),
            );

            return;
        }

        if ($availability->hasConflict(
            $this->physician,
            $dateString,
            $start->format('H:i:s'),
            $end->format('H:i:s'),
            $this->exceptAppointmentId,
        )) {
            $validator->errors()->add(
                'start_time',
                'The selected slot is already booked. Please choose another time.',
            );
        }
    }

    private function slotFitsWindow(Carbon $start, Carbon $end, PhysicianSchedule $schedule): bool
    {
        return $start->gte(Carbon::parse($schedule->start_time))
            && $end->lte(Carbon::parse($schedule->end_time));
    }
}
