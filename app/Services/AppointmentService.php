<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Patient;
use App\Models\Physician;
use App\Notifications\AppointmentCancelledNotification;
use App\Notifications\AppointmentConfirmedNotification;
use App\Notifications\AppointmentRescheduledNotification;
use Illuminate\Support\Facades\DB;

class AppointmentService
{
    public function __construct(private readonly PhysicianAvailability $availability) {}

    /**
     * Create an appointment for a patient and assign its daily queue number.
     *
     * @param  array{appointment_date: string, start_time: string, source?: string, status?: string, chief_complaint?: string|null, reason?: string|null, booked_by?: int|null}  $data
     */
    public function book(Patient $patient, Physician $physician, array $data): Appointment
    {
        $appointment = SequenceGenerator::retryTransaction(function () use ($patient, $physician, $data) {
            // Validation ran before the transaction; re-check the daily limit
            // here so a concurrent booking can never slip past it.
            $this->availability->guardCapacity($physician, $data['appointment_date']);

            $queueNumber = $this->nextQueueNumber($data['appointment_date']);
            $start = $data['start_time'];
            $end = $this->resolveEndTime($start, $physician);

            $appointment = Appointment::create([
                'patient_id' => $patient->id,
                'physician_id' => $physician->id,
                'appointment_date' => $data['appointment_date'],
                'start_time' => $start,
                'end_time' => $end,
                'source' => $data['source'] ?? 'online',
                'status' => $data['status'] ?? Appointment::STATUS_PENDING,
                'chief_complaint' => $data['chief_complaint'] ?? null,
                'reason' => $data['reason'] ?? null,
                'queue_number' => $queueNumber,
                'booked_by' => $data['booked_by'] ?? null,
            ]);

            $appointment->load(['patient.user', 'physician.user']);

            $patientUser = $appointment->patient->user;

            // Queue the confirmation only after the booking actually commits,
            // so a worker can never notify about a rolled-back appointment.
            DB::afterCommit(fn () => $patientUser->notify(new AppointmentConfirmedNotification($appointment)));

            return $appointment;
        });

        PhysicianAvailability::flushCalendarCache();

        return $appointment;
    }

    public function cancel(Appointment $appointment, int $cancelledBy): void
    {
        DB::transaction(function () use ($appointment, $cancelledBy) {
            $appointment->update([
                'status' => Appointment::STATUS_CANCELLED,
                'handled_by' => $cancelledBy,
            ]);

            DB::afterCommit(function () use ($appointment): void {
                $appointment->load(['patient.user', 'physician.user']);
                $appointment->patient?->user?->notify(new AppointmentCancelledNotification($appointment));
            });
        });

        PhysicianAvailability::flushCalendarCache();
    }

    /**
     * Move an existing appointment to a new slot and re-issue its queue number.
     *
     * @param  array{appointment_date: string, start_time: string, physician_id?: int}  $data
     */
    public function reschedule(Appointment $appointment, array $data, int $handledBy): Appointment
    {
        $refreshed = SequenceGenerator::retryTransaction(function () use ($appointment, $data, $handledBy) {
            $physician = isset($data['physician_id'])
                ? Physician::findOrFail($data['physician_id'])
                : $appointment->physician;

            $date = $data['appointment_date'];

            // The appointment being moved is excluded, so a full day can still
            // be re-shuffled by the patient who already holds a slot on it.
            $this->availability->guardCapacity($physician, $date, $appointment->id);

            $appointment->update([
                'physician_id' => $physician->id,
                'appointment_date' => $date,
                'start_time' => $data['start_time'],
                'end_time' => $this->resolveEndTime($data['start_time'], $physician),
                'queue_number' => $this->nextQueueNumber($date, $appointment->id),
                'status' => Appointment::STATUS_PENDING,
                'handled_by' => $handledBy,
            ]);

            $refreshed = $appointment->refresh();

            DB::afterCommit(function () use ($refreshed) {
                $refreshed->load(['patient.user', 'physician.user']);

                $refreshed->patient->user->notify(new AppointmentRescheduledNotification($refreshed));
            });

            return $refreshed;
        });

        PhysicianAvailability::flushCalendarCache();

        return $refreshed;
    }

    /**
     * @param  int|null  $excludeId  Appointment whose current number must be ignored
     */
    private function nextQueueNumber(string $date, ?int $excludeId = null): int
    {
        // Trashed appointments keep their queue number forever, so count them
        // too — reusing one would trip the (appointment_date, queue_number)
        // unique index when an admin removes a queued row.
        $max = Appointment::query()
            ->withTrashed()
            ->forDate($date)
            ->when($excludeId !== null, fn ($query) => $query->where('id', '!=', $excludeId))
            ->lockForUpdate()
            ->max('queue_number');

        return ((int) $max) + 1;
    }

    private function resolveEndTime(string $startTime, Physician $physician): string
    {
        $slotMinutes = max((int) $physician->appointment_slot_minutes, 1);

        return date('H:i:s', strtotime($startTime) + $slotMinutes * 60);
    }
}
