<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\AuditLog;
use App\Services\PhysicianAvailability;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class PhysicianAvailabilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_patient_booking_is_blocked_once_the_daily_limit_is_reached(): void
    {
        $patient = $this->createUserWithRole('patient');
        $this->makePatientProfile($patient);

        $otherPatient = $this->createUserWithRole('patient');
        $this->makePatientProfile($otherPatient);

        $physicianUser = $this->createUserWithRole('physician');
        $physician = $this->makePhysicianProfile($physicianUser, ['daily_patient_limit' => 1]);
        $this->openPhysicianSchedule($physician);

        $date = now()->addDay()->toDateString();

        Appointment::factory()->create([
            'patient_id' => $otherPatient->patient->id,
            'physician_id' => $physician->id,
            'appointment_date' => $date,
            'start_time' => '08:00:00',
            'end_time' => '08:15:00',
            'status' => Appointment::STATUS_CONFIRMED,
        ]);

        $this->actingAs($patient)
            ->post(route('patient.appointments.store'), [
                'physician_id' => $physician->id,
                'appointment_date' => $date,
                'start_time' => '09:00',
                'chief_complaint' => 'Follow-up check',
            ])
            ->assertSessionHasErrors('appointment_date');

        $this->assertSame(1, Appointment::where('physician_id', $physician->id)->count());
    }

    public function test_walk_in_registration_is_blocked_once_the_daily_limit_is_reached(): void
    {
        $receptionist = $this->createUserWithRole('receptionist');

        $patient = $this->createUserWithRole('patient');
        $this->makePatientProfile($patient);

        $otherPatient = $this->createUserWithRole('patient');
        $this->makePatientProfile($otherPatient);

        $physicianUser = $this->createUserWithRole('physician');
        $physician = $this->makePhysicianProfile($physicianUser, ['daily_patient_limit' => 1]);
        $this->openPhysicianSchedule($physician);

        $today = now()->toDateString();

        Appointment::factory()->create([
            'patient_id' => $otherPatient->patient->id,
            'physician_id' => $physician->id,
            'appointment_date' => $today,
            'start_time' => '08:00:00',
            'end_time' => '08:15:00',
            'status' => Appointment::STATUS_CONFIRMED,
        ]);

        $this->actingAs($receptionist)
            ->post(route('admin.queue.store'), [
                'patient_id' => $patient->patient->id,
                'physician_id' => $physician->id,
                'appointment_date' => $today,
                'start_time' => '09:30',
                'chief_complaint' => 'Tooth pain',
            ])
            ->assertSessionHasErrors('appointment_date');

        $this->assertSame(1, Appointment::where('physician_id', $physician->id)->count());
    }

    public function test_calendar_reports_available_full_and_past_dates(): void
    {
        $patient = $this->createUserWithRole('patient');
        $this->makePatientProfile($patient);

        $otherPatient = $this->createUserWithRole('patient');
        $this->makePatientProfile($otherPatient);

        $physicianUser = $this->createUserWithRole('physician');
        $physician = $this->makePhysicianProfile($physicianUser, ['daily_patient_limit' => 1]);
        $this->openPhysicianSchedule($physician);

        $tomorrow = now()->addDay();
        $dayAfter = now()->addDays(2);

        Appointment::factory()->create([
            'patient_id' => $otherPatient->patient->id,
            'physician_id' => $physician->id,
            'appointment_date' => $tomorrow->toDateString(),
            'start_time' => '08:00:00',
            'end_time' => '08:15:00',
            'status' => Appointment::STATUS_CONFIRMED,
        ]);

        $payload = $this->actingAs($patient)
            ->getJson(route('patient.availability.calendar', ['month' => now()->format('Y-m')]))
            ->assertOk()
            ->json();

        $this->assertSame('full', $payload['dates'][$tomorrow->toDateString()]['state']);
        $this->assertSame('available', $payload['dates'][$dayAfter->toDateString()]['state']);

        if (now()->day > 1) {
            $this->assertSame('past', $payload['dates'][now()->startOfMonth()->toDateString()]['state']);
        }

        $farDate = now()->addDays(PhysicianAvailability::BOOKING_HORIZON_DAYS + 5);

        $farPayload = $this->actingAs($patient)
            ->getJson(route('patient.availability.calendar', ['month' => $farDate->format('Y-m')]))
            ->assertOk()
            ->json();

        $this->assertSame('closed', $farPayload['dates'][$farDate->toDateString()]['state']);
    }

    public function test_calendar_endpoints_are_restricted_by_role(): void
    {
        $patient = $this->createUserWithRole('patient');
        $this->makePatientProfile($patient);

        $physicianUser = $this->createUserWithRole('physician');
        $this->makePhysicianProfile($physicianUser);

        $month = now()->format('Y-m');

        $this->actingAs($physicianUser)
            ->getJson(route('patient.availability.calendar', ['month' => $month]))
            ->assertForbidden();

        $this->actingAs($physicianUser)
            ->getJson(route('admin.availability.calendar', ['month' => $month]))
            ->assertForbidden();

        $this->actingAs($patient)
            ->getJson(route('admin.availability.calendar', ['month' => $month]))
            ->assertForbidden();
    }

    public function test_physician_can_post_availability_and_set_the_daily_limit(): void
    {
        $physicianUser = $this->createUserWithRole('physician');
        $physician = $this->makePhysicianProfile($physicianUser, ['daily_patient_limit' => 30]);

        $this->actingAs($physicianUser)
            ->get(route('physician.availability.index'))
            ->assertOk()
            ->assertSee('My availability')
            ->assertSee('30');

        $this->actingAs($physicianUser)
            ->post(route('physician.availability.store'), [
                'day_of_week' => 1,
                'start_time' => '08:00',
                'end_time' => '12:00',
                'is_active' => 1,
            ])
            ->assertSessionHasNoErrors()
            ->assertSessionHas('status', 'Availability added.');

        $this->assertDatabaseHas('physician_schedules', [
            'physician_id' => $physician->id,
            'day_of_week' => 1,
            'is_active' => true,
        ]);

        $this->actingAs($physicianUser)
            ->patch(route('physician.availability.limit'), ['daily_patient_limit' => 25])
            ->assertSessionHasNoErrors()
            ->assertSessionHas('status', 'Daily patient limit updated to 25.');

        $this->assertSame(25, $physician->fresh()->daily_patient_limit);
        $this->assertTrue(AuditLog::where('action', 'physician.limit_updated')->exists());
        $this->assertTrue(AuditLog::where('action', 'schedule.created')->exists());
    }

    public function test_physician_cannot_edit_another_physicians_schedule(): void
    {
        $owner = $this->createUserWithRole('physician');
        $ownerProfile = $this->makePhysicianProfile($owner);
        $this->openPhysicianSchedule($ownerProfile);

        $intruder = $this->createUserWithRole('physician');
        $this->makePhysicianProfile($intruder);

        $schedule = $ownerProfile->schedules()->first();

        $this->actingAs($intruder)
            ->put(route('physician.availability.update', $schedule), [
                'day_of_week' => $schedule->day_of_week,
                'start_time' => '09:00',
                'end_time' => '17:00',
            ])
            ->assertForbidden();

        $this->actingAs($intruder)
            ->delete(route('physician.availability.destroy', $schedule))
            ->assertForbidden();

        $this->assertDatabaseHas('physician_schedules', [
            'id' => $schedule->id,
            'start_time' => $schedule->start_time,
        ]);
    }

    public function test_duplicate_availability_window_is_rejected(): void
    {
        $physicianUser = $this->createUserWithRole('physician');
        $physician = $this->makePhysicianProfile($physicianUser);

        $payload = [
            'day_of_week' => 2,
            'start_time' => '13:00',
            'end_time' => '17:00',
            'is_active' => 1,
        ];

        $this->actingAs($physicianUser)
            ->post(route('physician.availability.store'), $payload)
            ->assertSessionHasNoErrors();

        $this->actingAs($physicianUser)
            ->post(route('physician.availability.store'), $payload)
            ->assertSessionHasErrors('start_time');

        $this->assertSame(1, $physician->schedules()->count());
    }

    public function test_booking_form_starts_hidden_until_a_date_is_chosen(): void
    {
        $patient = $this->createUserWithRole('patient');
        $this->makePatientProfile($patient);

        $this->actingAs($patient)
            ->get(route('patient.appointments.create'))
            ->assertOk()
            ->assertSee('data-availability-calendar', false)
            ->assertSee('data-reveal-target="booking-form"', false)
            ->assertSee('card mt-6 hidden', false);
    }

    public function test_booking_form_is_revealed_and_prefilled_when_coming_from_the_calendar(): void
    {
        $patient = $this->createUserWithRole('patient');
        $this->makePatientProfile($patient);

        $date = now()->addDays(2)->toDateString();

        $this->actingAs($patient)
            ->get(route('patient.appointments.create', ['date' => $date]))
            ->assertOk()
            ->assertDontSee('card mt-6 hidden', false)
            ->assertSee('value="'.$date.'"', false)
            ->assertSee('max="'.now()->addDays(PhysicianAvailability::BOOKING_HORIZON_DAYS)->toDateString().'"', false);
    }

    public function test_a_patient_can_reschedule_their_own_appointment_while_at_the_daily_limit(): void
    {
        Notification::fake();

        $patient = $this->createUserWithRole('patient');
        $this->makePatientProfile($patient);

        $physicianUser = $this->createUserWithRole('physician');
        $physician = $this->makePhysicianProfile($physicianUser, ['daily_patient_limit' => 1]);
        $this->openPhysicianSchedule($physician);

        $date = now()->addDays(2)->toDateString();

        $appointment = Appointment::factory()->create([
            'patient_id' => $patient->patient->id,
            'physician_id' => $physician->id,
            'appointment_date' => $date,
            'start_time' => '08:00:00',
            'end_time' => '08:15:00',
            'status' => Appointment::STATUS_CONFIRMED,
        ]);

        // Without the exclusion, the day really is at the limit.
        $atLimit = $this->actingAs($patient)
            ->getJson(route('patient.appointments.availability', [
                'physician_id' => $physician->id,
                'date' => $date,
            ]))
            ->assertOk()
            ->json();

        $this->assertTrue($atLimit['full']);

        // Excluding the appointment itself, the slot grid offers times again.
        $ownSlot = $this->actingAs($patient)
            ->getJson(route('patient.appointments.availability', [
                'physician_id' => $physician->id,
                'date' => $date,
                'appointment_id' => $appointment->id,
            ]))
            ->assertOk()
            ->json();

        $this->assertFalse($ownSlot['full']);
        $this->assertSame(1, $ownSlot['remaining']);

        // Another patient may not borrow that exclusion.
        $stranger = $this->createUserWithRole('patient');
        $this->makePatientProfile($stranger);

        $this->actingAs($stranger)
            ->getJson(route('patient.appointments.availability', [
                'physician_id' => $physician->id,
                'date' => $date,
                'appointment_id' => $appointment->id,
            ]))
            ->assertForbidden();

        // And the reschedule of the patient's own visit is accepted.
        $this->actingAs($patient)
            ->patch(route('patient.appointments.reschedule.update', $appointment), [
                'appointment_date' => $date,
                'start_time' => '09:00',
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('patient.appointments.show', $appointment));

        $this->assertSame('09:00', substr($appointment->fresh()->start_time, 0, 5));
    }

    public function test_cancelled_appointments_free_capacity_for_the_daily_limit(): void
    {
        Notification::fake();

        $patient = $this->createUserWithRole('patient');
        $this->makePatientProfile($patient);

        $otherPatient = $this->createUserWithRole('patient');
        $this->makePatientProfile($otherPatient);

        $physicianUser = $this->createUserWithRole('physician');
        $physician = $this->makePhysicianProfile($physicianUser, ['daily_patient_limit' => 1]);
        $this->openPhysicianSchedule($physician);

        $date = now()->addDay()->toDateString();

        Appointment::factory()->create([
            'patient_id' => $otherPatient->patient->id,
            'physician_id' => $physician->id,
            'appointment_date' => $date,
            'start_time' => '08:00:00',
            'end_time' => '08:15:00',
            'status' => Appointment::STATUS_CANCELLED,
        ]);

        $this->actingAs($patient)
            ->post(route('patient.appointments.store'), [
                'physician_id' => $physician->id,
                'appointment_date' => $date,
                'start_time' => '09:00',
                'chief_complaint' => 'Persistent cough',
            ])
            ->assertSessionHasNoErrors();

        $this->assertSame(
            1,
            Appointment::query()
                ->where('physician_id', $physician->id)
                ->where('status', '!=', Appointment::STATUS_CANCELLED)
                ->count(),
        );
    }

    public function test_the_daily_patient_limit_is_validated_between_one_and_one_hundred(): void
    {
        $physicianUser = $this->createUserWithRole('physician');
        $physician = $this->makePhysicianProfile($physicianUser, ['daily_patient_limit' => 30]);

        foreach ([0, 101] as $rejected) {
            $this->actingAs($physicianUser)
                ->patch(route('physician.availability.limit'), ['daily_patient_limit' => $rejected])
                ->assertSessionHasErrors('daily_patient_limit');
        }

        $this->assertSame(30, $physician->fresh()->daily_patient_limit);

        $this->actingAs($physicianUser)
            ->patch(route('physician.availability.limit'), ['daily_patient_limit' => 25])
            ->assertSessionHasNoErrors();

        $this->assertSame(25, $physician->fresh()->daily_patient_limit);
    }

    public function test_slot_endpoint_reports_full_and_zero_remaining_at_the_limit(): void
    {
        $patient = $this->createUserWithRole('patient');
        $this->makePatientProfile($patient);

        $otherPatient = $this->createUserWithRole('patient');
        $this->makePatientProfile($otherPatient);

        $physicianUser = $this->createUserWithRole('physician');
        $physician = $this->makePhysicianProfile($physicianUser, ['daily_patient_limit' => 1]);
        $this->openPhysicianSchedule($physician);

        $date = now()->addDay()->toDateString();

        Appointment::factory()->create([
            'patient_id' => $otherPatient->patient->id,
            'physician_id' => $physician->id,
            'appointment_date' => $date,
            'start_time' => '08:00:00',
            'end_time' => '08:15:00',
            'status' => Appointment::STATUS_CONFIRMED,
        ]);

        $payload = $this->actingAs($patient)
            ->getJson(route('patient.appointments.availability', [
                'physician_id' => $physician->id,
                'date' => $date,
            ]))
            ->assertOk()
            ->json();

        $this->assertTrue($payload['full']);
        $this->assertSame(0, $payload['remaining']);
        $this->assertNotEmpty($payload['slots']);
        $this->assertFalse(collect($payload['slots'])->contains('available', true));
    }
}
