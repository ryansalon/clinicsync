<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\AuditLog;
use App\Models\Physician;
use App\Models\User;
use App\Notifications\AppointmentRescheduledNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class PatientRescheduleTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_the_login_page(): void
    {
        [, , $appointment] = $this->bookedAppointment();

        $this->get(route('patient.appointments.reschedule', $appointment))
            ->assertRedirect(route('login'));

        $this->patch(route('patient.appointments.reschedule.update', $appointment), [
            'appointment_date' => now()->addDays(3)->toDateString(),
            'start_time' => '11:00',
        ])->assertRedirect(route('login'));
    }

    public function test_the_reschedule_form_shows_the_current_booking(): void
    {
        [$patient, , $appointment] = $this->bookedAppointment();

        $this->actingAs($patient)
            ->get(route('patient.appointments.reschedule', $appointment))
            ->assertOk()
            ->assertSee('Reschedule appointment')
            ->assertSee(date('g:i A', strtotime($appointment->start_time)));
    }

    public function test_a_patient_can_move_an_appointment_to_a_new_slot(): void
    {
        Notification::fake();

        [$patient, $physician, $appointment] = $this->bookedAppointment();

        // Another patient already holds queue #1 on the target date, so the
        // rescheduled visit must be re-issued a fresh queue number.
        $queueMate = $this->makePatientProfile($this->createUserWithRole('patient'));
        $target = now()->addDays(2)->toDateString();

        Appointment::create([
            'patient_id' => $queueMate->id,
            'physician_id' => $physician->id,
            'appointment_date' => $target,
            'start_time' => '08:00:00',
            'end_time' => '08:15:00',
            'status' => Appointment::STATUS_CONFIRMED,
            'queue_number' => 1,
        ]);

        $this->actingAs($patient)
            ->patch(route('patient.appointments.reschedule.update', $appointment), [
                'appointment_date' => $target,
                'start_time' => '10:30',
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('patient.appointments.show', $appointment));

        $appointment->refresh();

        $this->assertSame($target, $appointment->appointment_date->toDateString());
        $this->assertSame('10:30', substr($appointment->start_time, 0, 5));
        $this->assertSame(Appointment::STATUS_PENDING, $appointment->status);
        $this->assertSame(2, $appointment->queue_number);

        $this->assertTrue(AuditLog::where('action', 'appointment.rescheduled')->exists());
        Notification::assertSentTo($patient, AppointmentRescheduledNotification::class);
    }

    public function test_a_patient_cannot_reschedule_another_patients_appointment(): void
    {
        [, , $appointment] = $this->bookedAppointment();

        $intruderUser = $this->createUserWithRole('patient');
        $this->makePatientProfile($intruderUser);

        $this->actingAs($intruderUser)
            ->get(route('patient.appointments.reschedule', $appointment))
            ->assertForbidden();

        $this->actingAs($intruderUser)
            ->patch(route('patient.appointments.reschedule.update', $appointment), [
                'appointment_date' => now()->addDays(3)->toDateString(),
                'start_time' => '11:00',
            ])
            ->assertForbidden();
    }

    public function test_a_completed_appointment_cannot_be_rescheduled(): void
    {
        [$patient, , $appointment] = $this->bookedAppointment([
            'status' => Appointment::STATUS_COMPLETED,
        ]);

        $this->actingAs($patient)
            ->patch(route('patient.appointments.reschedule.update', $appointment), [
                'appointment_date' => now()->addDays(3)->toDateString(),
                'start_time' => '11:00',
            ])
            ->assertForbidden();
    }

    public function test_rescheduling_into_an_occupied_slot_is_rejected(): void
    {
        Notification::fake();

        [$patient, $physician, $appointment] = $this->bookedAppointment();

        $other = $this->makePatientProfile($this->createUserWithRole('patient'));
        $target = now()->addDays(2)->toDateString();

        Appointment::create([
            'patient_id' => $other->id,
            'physician_id' => $physician->id,
            'appointment_date' => $target,
            'start_time' => '10:30:00',
            'end_time' => '10:45:00',
            'status' => Appointment::STATUS_CONFIRMED,
            'queue_number' => 1,
        ]);

        $this->actingAs($patient)
            ->from(route('patient.appointments.reschedule', $appointment))
            ->patch(route('patient.appointments.reschedule.update', $appointment), [
                'appointment_date' => $target,
                'start_time' => '10:30',
            ])
            ->assertSessionHasErrors('start_time')
            ->assertRedirect(route('patient.appointments.reschedule', $appointment));

        $appointment->refresh();

        $this->assertSame(now()->addDay()->toDateString(), $appointment->appointment_date->toDateString());
        $this->assertSame('09:00', substr($appointment->start_time, 0, 5));
        Notification::assertNothingSent();
    }

    public function test_rescheduling_to_a_time_earlier_today_is_rejected(): void
    {
        [$patient, , $appointment] = $this->bookedAppointment();

        $this->actingAs($patient)
            ->patch(route('patient.appointments.reschedule.update', $appointment), [
                'appointment_date' => now()->toDateString(),
                'start_time' => now()->subMinutes(30)->format('H:i'),
            ])
            ->assertSessionHasErrors('start_time');

        $appointment->refresh();

        $this->assertSame(now()->addDay()->toDateString(), $appointment->appointment_date->toDateString());
        $this->assertSame('09:00', substr($appointment->start_time, 0, 5));
    }

    /**
     * A confirmed future appointment with an open schedule behind it.
     *
     * @param  array<string, mixed>  $attributes
     * @return array{0: User, 1: Physician, 2: Appointment}
     */
    private function bookedAppointment(array $attributes = []): array
    {
        $patient = $this->createUserWithRole('patient');
        $profile = $this->makePatientProfile($patient);

        $physician = $this->makePhysicianProfile($this->createUserWithRole('physician'));
        $this->openPhysicianSchedule($physician);

        $appointment = Appointment::create([
            'patient_id' => $profile->id,
            'physician_id' => $physician->id,
            'appointment_date' => now()->addDay()->toDateString(),
            'start_time' => '09:00:00',
            'end_time' => '09:15:00',
            'status' => Appointment::STATUS_CONFIRMED,
            'queue_number' => 1,
            ...$attributes,
        ]);

        return [$patient, $physician, $appointment];
    }
}
