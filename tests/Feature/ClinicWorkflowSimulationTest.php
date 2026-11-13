<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\AuditLog;
use App\Models\Encounter;
use App\Models\Prescription;
use App\Models\User;
use App\Notifications\AppointmentConfirmedNotification;
use App\Notifications\AppointmentReminderNotification;
use App\Notifications\AppointmentRescheduledNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * End-to-end simulation of a full clinic day: a patient registers and books,
 * reception runs the queue, a physician consults and prescribes, the patient
 * reviews records, reminders go out, appointments move and are cancelled, and
 * an administrator audits the whole trail.
 */
class ClinicWorkflowSimulationTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_full_clinic_day_can_be_simulated_end_to_end(): void
    {
        Notification::fake();

        // ================================================================
        // SETUP — The clinic opens with its staff on duty.
        // ================================================================
        foreach (['administrator', 'receptionist', 'physician', 'patient'] as $role) {
            Role::findOrCreate($role, 'web');
        }

        $receptionist = $this->createUserWithRole('receptionist');
        $physicianUser = $this->createUserWithRole('physician');
        $physician = $this->makePhysicianProfile($physicianUser);
        $this->openPhysicianSchedule($physician);
        $admin = $this->createUserWithRole('administrator');

        // ================================================================
        // ACT 1 — A new patient registers and books a visit online.
        // ================================================================
        $this->post(route('register'), [
            'name' => 'Maria Santos',
            'email' => 'maria.santos@clinicsync.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'date_of_birth' => '1998-04-12',
            'sex' => 'female',
        ])->assertRedirect(route('patient.dashboard'));

        $maria = User::where('email', 'maria.santos@clinicsync.test')->firstOrFail();

        $this->assertNotNull($maria->patient);
        $this->assertMatchesRegularExpression('/^PT-\d{6}$/', $maria->patient->patient_number);
        $this->assertTrue(AuditLog::where('action', 'user.registered')->where('user_id', $maria->id)->exists());

        $this->post(route('patient.appointments.store'), [
            'physician_id' => $physician->id,
            'appointment_date' => now()->toDateString(),
            'start_time' => '09:00',
            'chief_complaint' => 'Persistent fever and cough',
        ])->assertSessionHasNoErrors()->assertRedirect();

        $mariaAppointment = Appointment::where('patient_id', $maria->patient->id)->firstOrFail();

        $this->assertSame(Appointment::STATUS_PENDING, $mariaAppointment->status);
        $this->assertSame(1, $mariaAppointment->queue_number);
        Notification::assertSentTo($maria, AppointmentConfirmedNotification::class);
        $this->assertTrue(AuditLog::where('action', 'appointment.booked')->exists());

        // Maria sees her live queue position on the dashboard and the
        // appointment timeline with reschedule actions.
        $this->get(route('patient.dashboard'))
            ->assertOk()
            ->assertSee('Queue #1')
            ->assertSee('You are next');

        $this->get(route('patient.appointments.index'))
            ->assertOk()
            ->assertSee('Queue #1');

        $this->get(route('patient.appointments.show', $mariaAppointment))
            ->assertOk()
            ->assertSee('Booked');

        // ================================================================
        // ACT 2 — A returning patient books tomorrow's follow-up.
        // ================================================================
        $this->post(route('logout'))->assertRedirect(route('login'));

        $this->post(route('register'), [
            'name' => 'John Reyes',
            'email' => 'john.reyes@clinicsync.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'date_of_birth' => '1985-11-03',
            'sex' => 'male',
        ])->assertRedirect(route('patient.dashboard'));

        $john = User::where('email', 'john.reyes@clinicsync.test')->firstOrFail();

        $this->post(route('patient.appointments.store'), [
            'physician_id' => $physician->id,
            'appointment_date' => now()->addDay()->toDateString(),
            'start_time' => '10:00',
            'chief_complaint' => 'Follow-up: blood pressure check',
        ])->assertSessionHasNoErrors()->assertRedirect();

        $johnAppointment = Appointment::where('patient_id', $john->patient->id)->firstOrFail();

        $this->assertSame(1, $johnAppointment->queue_number);
        Notification::assertSentTo($john, AppointmentConfirmedNotification::class);

        $this->post(route('logout'))->assertRedirect(route('login'));

        // ================================================================
        // ACT 3 — Reception runs the front desk queue.
        // ================================================================
        $this->post(route('login'), ['email' => $receptionist->email, 'password' => 'password'])
            ->assertRedirect();

        $this->get(route('admin.queue.index'))
            ->assertOk()
            ->assertSee('Maria Santos');

        // Maria arrives and is called forward.
        $this->patch(route('admin.queue.call', $mariaAppointment))
            ->assertSessionHas('status', 'Patient called forward.')
            ->assertRedirect();

        $mariaAppointment->refresh();

        $this->assertNotNull($mariaAppointment->called_at);
        $this->assertSame(Appointment::STATUS_CONFIRMED, $mariaAppointment->status);
        $this->assertSame($receptionist->id, $mariaAppointment->handled_by);
        $this->assertTrue(AuditLog::where('action', 'appointment.called')->exists());

        // John walks in without an appointment.
        $this->post(route('admin.queue.store'), [
            'patient_id' => $john->patient->id,
            'physician_id' => $physician->id,
            'appointment_date' => now()->toDateString(),
            'start_time' => '09:30',
            'chief_complaint' => 'Migraine since yesterday',
        ])
            ->assertSessionHasNoErrors()
            ->assertSessionHas('status', 'Walk-in added to queue as #2.');

        $johnWalkIn = Appointment::where('patient_id', $john->patient->id)
            ->whereDate('appointment_date', now()->toDateString())
            ->firstOrFail();

        $this->assertSame(2, $johnWalkIn->queue_number);
        $this->assertSame(Appointment::STATUS_CONFIRMED, $johnWalkIn->status);
        $this->assertTrue(AuditLog::where('action', 'appointment.walk_in_registered')->exists());

        // John calls to move his follow-up to a later slot.
        $this->patch(route('admin.queue.update', $johnAppointment), [
            'appointment_date' => now()->addDay()->toDateString(),
            'start_time' => '14:00',
        ])
            ->assertSessionHasNoErrors()
            ->assertSessionHas('status', 'Queue updated.');

        $johnAppointment->refresh();

        $this->assertSame('14:00', substr($johnAppointment->start_time, 0, 5));
        $this->assertSame(Appointment::STATUS_PENDING, $johnAppointment->status);
        Notification::assertSentTo($john, AppointmentRescheduledNotification::class);
        $this->assertTrue(AuditLog::where('action', 'appointment.updated')->exists());

        $this->post(route('logout'))->assertRedirect(route('login'));

        // ================================================================
        // ACT 4 — The physician consults Maria and issues a prescription.
        // ================================================================
        $this->post(route('login'), ['email' => $physicianUser->email, 'password' => 'password'])
            ->assertRedirect();

        $this->get(route('physician.dashboard'))->assertOk();

        $this->get(route('physician.consultation', $mariaAppointment))->assertOk();

        $this->post(route('physician.encounters.store', $mariaAppointment), [
            'chief_complaint' => 'Persistent fever and cough',
            'diagnosis' => 'Acute bronchitis',
            'diagnosis_code' => 'J20.9',
            'assessment' => 'Low-grade fever, throat slightly red; lungs clear.',
            'treatment_plan' => 'Rest, hydration, antipyretics.',
            'temperature_c' => 38.2,
            'systolic_bp' => 118,
            'diastolic_bp' => 76,
            'heart_rate' => 92,
            'mark_completed' => 1,
        ])->assertSessionHasNoErrors()->assertRedirect();

        $encounter = Encounter::where('patient_id', $maria->patient->id)->firstOrFail();

        $this->assertMatchesRegularExpression('/^ENC-\d{6}$/', $encounter->encounter_number);
        $this->assertSame(Encounter::STATUS_COMPLETED, $encounter->status);
        $this->assertSame('Acute bronchitis', $encounter->diagnosis);
        $this->assertTrue(AuditLog::where('action', 'encounter.created')->exists());

        $mariaAppointment->refresh();

        $this->assertSame(Appointment::STATUS_COMPLETED, $mariaAppointment->status);
        $this->assertNotNull($mariaAppointment->finished_at);
        $this->assertSame($physicianUser->id, $mariaAppointment->handled_by);

        $this->post(route('physician.prescriptions.store', $encounter), [
            'medication_name' => 'Paracetamol',
            'strength' => '500 mg',
            'dosage_form' => 'Tablet',
            'dosage' => '1 tablet',
            'frequency' => 'Three times daily',
            'route' => 'Oral',
            'duration' => '5 days',
            'quantity' => 15,
            'instructions' => 'Take after meals if fever persists.',
        ])->assertSessionHasNoErrors()->assertRedirect();

        $prescription = Prescription::where('encounter_id', $encounter->id)->firstOrFail();

        $this->assertMatchesRegularExpression('/^RX-\d{6}$/', $prescription->prescription_number);
        $this->assertTrue(AuditLog::where('action', 'prescription.issued')->exists());

        $this->get(route('physician.encounters.show', $encounter))
            ->assertOk()
            ->assertSee('Acute bronchitis');

        $this->post(route('logout'))->assertRedirect(route('login'));

        // ================================================================
        // ACT 5 — Maria comes back to review her records.
        // ================================================================
        $this->post(route('login'), ['email' => $maria->email, 'password' => 'password123'])
            ->assertRedirect();

        $this->get(route('patient.dashboard'))
            ->assertOk()
            ->assertSee('Your consultation today has finished');

        $this->get(route('patient.records'))
            ->assertOk()
            ->assertSee('Acute bronchitis')
            ->assertSee('Paracetamol');

        // ================================================================
        // ACT 6 — The nightly reminder job runs for tomorrow's queue.
        // ================================================================
        $this->artisan('appointments:send-reminders', ['--date' => now()->addDay()->toDateString()])
            ->assertSuccessful();

        Notification::assertSentTo($john, AppointmentReminderNotification::class);
        Notification::assertNotSentTo($maria, AppointmentReminderNotification::class);

        // ================================================================
        // ACT 7 — John moves his follow-up once more, then cancels it.
        // ================================================================
        $this->post(route('logout'))->assertRedirect(route('login'));

        $this->post(route('login'), ['email' => $john->email, 'password' => 'password123'])
            ->assertRedirect();

        $this->get(route('patient.appointments.reschedule', $johnAppointment))
            ->assertOk()
            ->assertSee('Reschedule appointment');

        $this->patch(route('patient.appointments.reschedule.update', $johnAppointment), [
            'appointment_date' => now()->addDays(2)->toDateString(),
            'start_time' => '11:00',
        ])->assertSessionHasNoErrors()->assertRedirect();

        $johnAppointment->refresh();

        $this->assertSame(now()->addDays(2)->toDateString(), $johnAppointment->appointment_date->toDateString());
        $this->assertSame('11:00', substr($johnAppointment->start_time, 0, 5));
        $this->assertSame(1, $johnAppointment->queue_number);
        $this->assertSame(Appointment::STATUS_PENDING, $johnAppointment->status);
        Notification::assertSentTo($john, AppointmentRescheduledNotification::class);
        $this->assertTrue(AuditLog::where('action', 'appointment.rescheduled')->exists());

        $this->delete(route('patient.appointments.destroy', $johnAppointment))
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $this->assertSame(Appointment::STATUS_CANCELLED, $johnAppointment->fresh()->status);
        $this->assertTrue(AuditLog::where('action', 'appointment.cancelled')->exists());

        $this->post(route('logout'))->assertRedirect(route('login'));

        // ================================================================
        // ACT 8 — The administrator audits the day and suspends John.
        // ================================================================
        $this->post(route('login'), ['email' => $admin->email, 'password' => 'password'])
            ->assertRedirect();

        $this->get(route('admin.users.index'))
            ->assertOk()
            ->assertSee('Maria Santos');

        $this->get(route('admin.reports.index'))->assertOk();
        $this->get(route('admin.audit-logs.index'))->assertOk();

        $this->patch(route('admin.users.status', $john))
            ->assertSessionHas('status', 'John Reyes suspended.');

        $this->assertFalse($john->fresh()->is_active);
        $this->assertTrue(
            AuditLog::where('action', 'user.suspended')
                ->where('auditable_id', $john->id)
                ->exists()
        );

        $this->post(route('logout'))->assertRedirect(route('login'));

        // A suspended patient cannot get back in.
        $this->post(route('login'), ['email' => $john->email, 'password' => 'password123']);

        $this->get(route('patient.dashboard'))->assertRedirect(route('login'));

        // ================================================================
        // SCOREBOARD — Everything the simulation proved, end to end.
        // ================================================================
        $this->assertSame(3, Appointment::count());
        $this->assertSame(1, Encounter::count());
        $this->assertSame(1, Prescription::count());
        $this->assertSame(5, User::count());

        foreach ([
            'user.registered',
            'user.login',
            'appointment.booked',
            'appointment.called',
            'appointment.walk_in_registered',
            'appointment.updated',
            'encounter.created',
            'prescription.issued',
            'appointment.rescheduled',
            'appointment.cancelled',
            'user.suspended',
        ] as $action) {
            $this->assertTrue(
                AuditLog::where('action', $action)->exists(),
                "Missing audit trail entry [{$action}]."
            );
        }
    }

    public function test_removing_a_queued_appointment_keeps_its_queue_number_reserved(): void
    {
        Notification::fake();

        foreach (['administrator', 'receptionist', 'physician', 'patient'] as $role) {
            Role::findOrCreate($role, 'web');
        }

        $admin = $this->createUserWithRole('administrator');
        $physicianUser = $this->createUserWithRole('physician');
        $physician = $this->makePhysicianProfile($physicianUser);
        $this->openPhysicianSchedule($physician);

        $first = $this->createUserWithRole('patient');
        $this->makePatientProfile($first);
        $second = $this->createUserWithRole('patient');
        $this->makePatientProfile($second);

        $date = now()->toDateString();

        $this->actingAs($first)->post(route('patient.appointments.store'), [
            'physician_id' => $physician->id,
            'appointment_date' => $date,
            'start_time' => '09:00',
        ])->assertRedirect();

        $removed = Appointment::firstOrFail();
        $this->assertSame(1, $removed->queue_number);

        // An administrator removes the row from the queue entirely.
        $this->actingAs($admin)
            ->delete(route('admin.queue.destroy', $removed))
            ->assertSessionHas('status', 'Appointment removed from the queue.');

        $this->assertSoftDeleted('appointments', ['id' => $removed->id]);

        // A later booking must not reuse queue #1 — the removed row still owns it.
        $this->actingAs($second)->post(route('patient.appointments.store'), [
            'physician_id' => $physician->id,
            'appointment_date' => $date,
            'start_time' => '09:30',
        ])->assertRedirect();

        $this->assertSame(2, Appointment::withoutTrashed()->latest('id')->firstOrFail()->queue_number);
    }
}
