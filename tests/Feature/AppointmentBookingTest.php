<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Physician;
use App\Notifications\AppointmentConfirmedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class AppointmentBookingTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_login_before_booking(): void
    {
        $this->get(route('patient.appointments.create'))
            ->assertRedirect(route('login'));

        $this->post(route('patient.appointments.store'), [])
            ->assertRedirect(route('login'));
    }

    public function test_patient_can_book_an_available_slot_and_receives_a_queue_number(): void
    {
        Notification::fake();

        $patient = $this->createUserWithRole('patient');
        $profile = $this->makePatientProfile($patient);

        $physician = $this->openPhysicianFor('physician');

        $date = now()->addDay()->toDateString();

        $this->actingAs($patient)
            ->post(route('patient.appointments.store'), [
                'physician_id' => $physician->id,
                'appointment_date' => $date,
                'start_time' => '09:00',
                'chief_complaint' => 'Recurring headache',
            ])
            ->assertRedirect();

        $appointment = Appointment::first();

        $this->assertNotNull($appointment);
        $this->assertSame($profile->id, $appointment->patient_id);
        $this->assertSame(1, $appointment->queue_number);
        $this->assertSame(Appointment::STATUS_PENDING, $appointment->status);
        $this->assertSame('online', $appointment->source);
        $this->assertSame('09:15:00', $appointment->end_time);

        Notification::assertSentTo($patient, AppointmentConfirmedNotification::class);
    }

    public function test_a_slot_that_is_already_booked_is_rejected(): void
    {
        $patient = $this->createUserWithRole('patient');
        $this->makePatientProfile($patient);

        $physician = $this->openPhysicianFor('physician');
        $date = now()->addDay()->toDateString();

        $booking = [
            'physician_id' => $physician->id,
            'appointment_date' => $date,
            'start_time' => '09:00',
        ];

        $this->actingAs($patient)->post(route('patient.appointments.store'), $booking)->assertRedirect();

        $this->actingAs($patient)
            ->from(route('patient.appointments.create'))
            ->post(route('patient.appointments.store'), $booking)
            ->assertRedirect(route('patient.appointments.create'))
            ->assertSessionHasErrors('start_time');

        $this->assertDatabaseCount('appointments', 1);
    }

    public function test_overlapping_slots_are_rejected_even_when_they_do_not_start_identically(): void
    {
        $patient = $this->createUserWithRole('patient');
        $this->makePatientProfile($patient);

        $physician = $this->openPhysicianFor('physician');
        $date = now()->addDay()->toDateString();

        $this->actingAs($patient)->post(route('patient.appointments.store'), [
            'physician_id' => $physician->id,
            'appointment_date' => $date,
            'start_time' => '09:00',
        ])->assertRedirect();

        // 09:05 falls inside the 09:00–09:15 appointment.
        $this->actingAs($patient)
            ->post(route('patient.appointments.store'), [
                'physician_id' => $physician->id,
                'appointment_date' => $date,
                'start_time' => '09:05',
            ])
            ->assertSessionHasErrors('start_time');

        $this->assertDatabaseCount('appointments', 1);
    }

    public function test_booking_outside_the_physician_schedule_is_rejected(): void
    {
        $patient = $this->createUserWithRole('patient');
        $this->makePatientProfile($patient);

        $physician = $this->openPhysicianFor('physician', '08:00', '12:00');

        $this->actingAs($patient)
            ->post(route('patient.appointments.store'), [
                'physician_id' => $physician->id,
                'appointment_date' => now()->addDay()->toDateString(),
                'start_time' => '13:00',
            ])
            ->assertSessionHasErrors('start_time');

        $this->assertDatabaseCount('appointments', 0);
    }

    public function test_booking_in_the_past_is_rejected(): void
    {
        $patient = $this->createUserWithRole('patient');
        $this->makePatientProfile($patient);

        $physician = $this->openPhysicianFor('physician');

        $this->actingAs($patient)
            ->post(route('patient.appointments.store'), [
                'physician_id' => $physician->id,
                'appointment_date' => now()->subDay()->toDateString(),
                'start_time' => '09:00',
            ])
            ->assertSessionHasErrors('appointment_date');

        $this->assertDatabaseCount('appointments', 0);
    }

    public function test_a_receptionist_cannot_use_the_patient_booking_portal(): void
    {
        $receptionist = $this->createUserWithRole('receptionist');

        $this->actingAs($receptionist)
            ->post(route('patient.appointments.store'), [])
            ->assertForbidden();

        $this->assertDatabaseCount('appointments', 0);
    }

    public function test_a_physician_cannot_use_the_patient_booking_portal(): void
    {
        $physicianUser = $this->createUserWithRole('physician');
        $this->makePhysicianProfile($physicianUser);

        $this->actingAs($physicianUser)
            ->get(route('patient.appointments.create'))
            ->assertForbidden();

        $this->actingAs($physicianUser)
            ->post(route('patient.appointments.store'), [])
            ->assertForbidden();
    }

    public function test_availability_endpoint_reports_open_and_taken_slots(): void
    {
        $patient = $this->createUserWithRole('patient');
        $this->makePatientProfile($patient);

        $physician = $this->openPhysicianFor('physician');
        $date = now()->addDay()->toDateString();

        $this->actingAs($patient)->post(route('patient.appointments.store'), [
            'physician_id' => $physician->id,
            'appointment_date' => $date,
            'start_time' => '09:00',
        ])->assertRedirect();

        $response = $this->actingAs($patient)->getJson(route('patient.appointments.availability', [
            'physician_id' => $physician->id,
            'date' => $date,
        ]));

        $response->assertOk()->assertJsonStructure(['slot_minutes', 'slots' => [['time', 'label', 'available']]]);

        $taken = collect($response->json('slots'))->firstWhere('time', '09:00');
        $free = collect($response->json('slots'))->firstWhere('time', '10:00');

        $this->assertFalse($taken['available']);
        $this->assertTrue($free['available']);
    }

    public function test_a_cancelled_slot_can_be_rebooked_by_another_patient(): void
    {
        $first = $this->createUserWithRole('patient');
        $this->makePatientProfile($first);

        $second = $this->createUserWithRole('patient');
        $this->makePatientProfile($second);

        $physician = $this->openPhysicianFor('physician');
        $date = now()->addDay()->toDateString();

        $this->actingAs($first)
            ->post(route('patient.appointments.store'), [
                'physician_id' => $physician->id,
                'appointment_date' => $date,
                'start_time' => '09:00',
            ])
            ->assertRedirect();

        $this->actingAs($first)
            ->delete(route('patient.appointments.destroy', Appointment::firstOrFail()))
            ->assertRedirect(route('patient.appointments.index'));

        // The slot is free again at the application level, so the database must
        // not reject the new booking with a unique-constraint violation.
        $this->actingAs($second)
            ->post(route('patient.appointments.store'), [
                'physician_id' => $physician->id,
                'appointment_date' => $date,
                'start_time' => '09:00',
            ])
            ->assertRedirect();

        $this->assertSame(2, Appointment::count());
    }

    public function test_the_slot_immediately_before_a_booking_remains_open(): void
    {
        $first = $this->createUserWithRole('patient');
        $this->makePatientProfile($first);

        $second = $this->createUserWithRole('patient');
        $this->makePatientProfile($second);

        $physician = $this->openPhysicianFor('physician');
        $date = now()->addDay()->toDateString();

        $this->actingAs($first)
            ->post(route('patient.appointments.store'), [
                'physician_id' => $physician->id,
                'appointment_date' => $date,
                'start_time' => '09:00',
            ])
            ->assertRedirect();

        // 08:45–09:00 ends exactly when the 09:00 booking starts, so the two
        // never overlap and the earlier slot must stay bookable.
        $this->actingAs($second)
            ->post(route('patient.appointments.store'), [
                'physician_id' => $physician->id,
                'appointment_date' => $date,
                'start_time' => '08:45',
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $this->assertSame(2, Appointment::count());
    }

    public function test_the_slot_immediately_after_a_booking_remains_open(): void
    {
        $first = $this->createUserWithRole('patient');
        $this->makePatientProfile($first);

        $second = $this->createUserWithRole('patient');
        $this->makePatientProfile($second);

        $physician = $this->openPhysicianFor('physician');
        $date = now()->addDay()->toDateString();

        $this->actingAs($first)
            ->post(route('patient.appointments.store'), [
                'physician_id' => $physician->id,
                'appointment_date' => $date,
                'start_time' => '09:00',
            ])
            ->assertRedirect();

        // 09:15 starts exactly when the 09:00–09:15 booking ends.
        $this->actingAs($second)
            ->post(route('patient.appointments.store'), [
                'physician_id' => $physician->id,
                'appointment_date' => $date,
                'start_time' => '09:15',
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $this->assertSame(2, Appointment::count());
    }

    public function test_a_patient_can_cancel_their_own_future_appointment(): void
    {
        $patient = $this->createUserWithRole('patient');
        $this->makePatientProfile($patient);

        $physician = $this->openPhysicianFor('physician');

        $this->actingAs($patient)->post(route('patient.appointments.store'), [
            'physician_id' => $physician->id,
            'appointment_date' => now()->addDay()->toDateString(),
            'start_time' => '09:00',
        ])->assertRedirect();

        $appointment = Appointment::firstOrFail();

        $this->actingAs($patient)
            ->delete(route('patient.appointments.destroy', $appointment))
            ->assertRedirect(route('patient.appointments.index'));

        $this->assertSame(Appointment::STATUS_CANCELLED, $appointment->fresh()->status);
    }

    private function openPhysicianFor(string $role, string $start = '08:00', string $end = '17:00'): Physician
    {
        $user = $this->createUserWithRole($role);
        $physician = $this->makePhysicianProfile($user);
        $this->openPhysicianSchedule($physician, $start, $end);

        return $physician;
    }
}
