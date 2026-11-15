<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\AuditLog;
use App\Models\Encounter;
use App\Models\PhysicianSchedule;
use App\Models\Prescription;
use App\Notifications\AppointmentConfirmedNotification;
use App\Notifications\AppointmentReminderNotification;
use App\Services\AuditLogger;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ProductionReadinessTest extends TestCase
{
    use RefreshDatabase;

    public function test_malformed_dates_fall_back_safely_on_queue_and_reports(): void
    {
        $admin = $this->createUserWithRole('administrator');

        $this->actingAs($admin)
            ->get(route('admin.queue.index', ['date' => 'not-a-date']))
            ->assertOk();

        $this->actingAs($admin)
            ->get(route('admin.reports.index', ['from' => 'bad', 'to' => 'worse']))
            ->assertOk();

        $this->actingAs($admin)
            ->getJson(route('admin.queue.index', ['date' => 'not-a-date']))
            ->assertOk()
            ->assertJsonStructure(['html']);
    }

    public function test_suspended_users_receive_json_403_and_active_database_sessions_are_purged(): void
    {
        $admin = $this->createUserWithRole('administrator');
        $patient = $this->createUserWithRole('patient');
        $this->makePatientProfile($patient);

        config(['session.driver' => 'database']);

        DB::table('sessions')->insert([
            'id' => 'test-session-id',
            'user_id' => $patient->id,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'PHPUnit',
            'payload' => 'serialized',
            'last_activity' => time(),
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.users.status', $patient), [
                'is_active' => false,
            ])
            ->assertRedirect();

        $this->assertDatabaseMissing('sessions', ['id' => 'test-session-id']);

        $this->actingAs($patient->fresh())
            ->getJson(route('patient.dashboard'))
            ->assertForbidden()
            ->assertJsonStructure(['message']);
    }

    public function test_appointment_reminders_are_idempotent_across_repeated_runs(): void
    {
        Notification::fake();

        $patientUser = $this->createUserWithRole('patient');
        $patient = $this->makePatientProfile($patientUser);
        $physicianUser = $this->createUserWithRole('physician');
        $physician = $this->makePhysicianProfile($physicianUser);

        $tomorrow = now()->addDay()->toDateString();

        $appointment = Appointment::create([
            'appointment_number' => 'APT-2026-0001',
            'patient_id' => $patient->id,
            'physician_id' => $physician->id,
            'appointment_date' => $tomorrow,
            'start_time' => '09:00:00',
            'end_time' => '09:15:00',
            'queue_number' => 1,
            'status' => Appointment::STATUS_CONFIRMED,
            'source' => 'online',
        ]);

        $this->artisan('appointments:send-reminders')->assertSuccessful();
        $this->artisan('appointments:send-reminders')->assertSuccessful();

        Notification::assertSentToTimes($patientUser, AppointmentReminderNotification::class, 1);
        $this->assertNotNull($appointment->fresh()->reminder_sent_at);
    }

    public function test_overlapping_schedule_windows_are_rejected_for_admin_and_physician(): void
    {
        $admin = $this->createUserWithRole('administrator');
        $physicianUser = $this->createUserWithRole('physician');
        $physician = $this->makePhysicianProfile($physicianUser);

        PhysicianSchedule::create([
            'physician_id' => $physician->id,
            'day_of_week' => 1,
            'start_time' => '08:00',
            'end_time' => '12:00',
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->from(route('admin.schedules.index'))
            ->post(route('admin.schedules.store'), [
                'physician_id' => $physician->id,
                'day_of_week' => 1,
                'start_time' => '11:00',
                'end_time' => '15:00',
                'is_active' => '1',
            ])
            ->assertRedirect(route('admin.schedules.index'))
            ->assertSessionHasErrors('start_time');

        $this->actingAs($physicianUser)
            ->from(route('physician.availability.index'))
            ->post(route('physician.availability.store'), [
                'day_of_week' => 1,
                'start_time' => '10:00',
                'end_time' => '13:00',
            ])
            ->assertRedirect(route('physician.availability.index'))
            ->assertSessionHasErrors('start_time');
    }

    public function test_duplicate_encounter_creation_for_same_appointment_redirects_to_existing_record(): void
    {
        $patientUser = $this->createUserWithRole('patient');
        $patient = $this->makePatientProfile($patientUser);
        $physicianUser = $this->createUserWithRole('physician');
        $physician = $this->makePhysicianProfile($physicianUser);

        $appointment = Appointment::create([
            'appointment_number' => 'APT-2026-0002',
            'patient_id' => $patient->id,
            'physician_id' => $physician->id,
            'appointment_date' => now()->toDateString(),
            'start_time' => '09:00:00',
            'end_time' => '09:15:00',
            'queue_number' => 1,
            'status' => Appointment::STATUS_CONFIRMED,
            'source' => 'online',
        ]);

        $encounter = Encounter::create([
            'appointment_id' => $appointment->id,
            'patient_id' => $patient->id,
            'physician_id' => $physician->id,
            'encounter_number' => 'ENC-2026-0001',
            'encountered_at' => now(),
            'chief_complaint' => 'Fever',
            'diagnosis' => 'Viral syndrome',
            'status' => Encounter::STATUS_COMPLETED,
        ]);

        $this->actingAs($physicianUser)
            ->get(route('physician.consultation', $appointment))
            ->assertRedirect(route('physician.encounters.show', $encounter));

        $this->actingAs($physicianUser)
            ->post(route('physician.encounters.store', $appointment), [
                'chief_complaint' => 'Fever',
                'diagnosis' => 'Duplicate attempt',
            ])
            ->assertRedirect(route('physician.encounters.show', $encounter));
    }

    public function test_audit_logger_masks_sensitive_clinical_fields(): void
    {
        $user = $this->createUserWithRole('physician');

        AuditLogger::log(
            $user,
            'encounter.recorded',
            null,
            ['diagnosis' => 'Hypertension', 'chief_complaint' => 'Dizziness', 'status' => 'completed'],
            ['diagnosis' => 'Primary hypertension', 'clinical_notes' => 'Confidential note'],
        );

        $log = AuditLog::latest('id')->firstOrFail();

        $this->assertSame('[redacted]', $log->old_values['diagnosis']);
        $this->assertSame('[redacted]', $log->old_values['chief_complaint']);
        $this->assertSame('completed', $log->old_values['status']);
        $this->assertSame('[redacted]', $log->new_values['diagnosis']);
        $this->assertSame('[redacted]', $log->new_values['clinical_notes']);
    }

    public function test_notifications_read_all_marks_unread_items_read_in_bulk(): void
    {
        $patientUser = $this->createUserWithRole('patient');
        $patient = $this->makePatientProfile($patientUser);
        $physicianUser = $this->createUserWithRole('physician');
        $physician = $this->makePhysicianProfile($physicianUser);

        $appointment = Appointment::create([
            'appointment_number' => 'APT-2026-0003',
            'patient_id' => $patient->id,
            'physician_id' => $physician->id,
            'appointment_date' => now()->addDay()->toDateString(),
            'start_time' => '10:00:00',
            'end_time' => '10:15:00',
            'queue_number' => 1,
            'status' => Appointment::STATUS_CONFIRMED,
            'source' => 'online',
        ]);

        $patientUser->notifyNow(new AppointmentConfirmedNotification($appointment));
        $patientUser->notifyNow(new AppointmentConfirmedNotification($appointment));

        $this->assertSame(2, $patientUser->unreadNotifications()->count());

        $this->actingAs($patientUser)
            ->post(route('notifications.read-all'))
            ->assertRedirect();

        $this->assertSame(0, $patientUser->fresh()->unreadNotifications()->count());
    }

    public function test_admin_overview_dashboard_is_restricted_to_administrators(): void
    {
        $admin = $this->createUserWithRole('administrator');
        $receptionist = $this->createUserWithRole('receptionist');
        $physician = $this->createUserWithRole('physician');
        $this->makePhysicianProfile($physician);
        $patient = $this->createUserWithRole('patient');
        $this->makePatientProfile($patient);

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('7-day appointment volume');

        $this->actingAs($receptionist)->get(route('admin.dashboard'))->assertForbidden();
        $this->actingAs($physician)->get(route('admin.dashboard'))->assertForbidden();
        $this->actingAs($patient)->get(route('admin.dashboard'))->assertForbidden();
    }

    public function test_daily_census_csv_export_streams_and_neutralizes_formulas(): void
    {
        $admin = $this->createUserWithRole('administrator');
        $patientUser = $this->createUserWithRole('patient', ['name' => '=HYPERLINK("http://evil.test","click")']);
        $patient = $this->makePatientProfile($patientUser);
        $physicianUser = $this->createUserWithRole('physician');
        $physician = $this->makePhysicianProfile($physicianUser);

        $today = now()->toDateString();

        Appointment::create([
            'patient_id' => $patient->id,
            'physician_id' => $physician->id,
            'appointment_date' => $today,
            'start_time' => '09:00:00',
            'end_time' => '09:15:00',
            'queue_number' => 1,
            'status' => Appointment::STATUS_CONFIRMED,
            'source' => 'online',
            'chief_complaint' => '+1+1',
        ]);

        $response = $this->actingAs($admin)
            ->get(route('admin.reports.export', ['date' => $today]));

        $response->assertOk();
        $this->assertStringContainsString('text/csv', (string) $response->headers->get('Content-Type'));
        $csv = $response->streamedContent();
        $this->assertStringContainsString("'=HYPERLINK(", $csv);
        $this->assertStringContainsString("'+1+1", $csv);
    }

    public function test_role_dashboards_render_new_operational_widgets_and_polling_payloads(): void
    {
        $patientUser = $this->createUserWithRole('patient');
        $patient = $this->makePatientProfile($patientUser, [
            'blood_type' => null,
            'emergency_contact_name' => null,
        ]);
        $physicianUser = $this->createUserWithRole('physician');
        $physician = $this->makePhysicianProfile($physicianUser);
        $receptionist = $this->createUserWithRole('receptionist');

        $today = now()->toDateString();

        Appointment::create([
            'patient_id' => $patient->id,
            'physician_id' => $physician->id,
            'appointment_date' => $today,
            'start_time' => '09:00:00',
            'end_time' => '09:15:00',
            'queue_number' => 1,
            'status' => Appointment::STATUS_CONFIRMED,
            'called_at' => now(),
            'source' => 'online',
            'chief_complaint' => 'Persistent cough',
        ]);

        $encounter = Encounter::create([
            'patient_id' => $patient->id,
            'physician_id' => $physician->id,
            'encounter_number' => 'ENC-2026-0099',
            'encountered_at' => now()->subDay(),
            'chief_complaint' => 'Cough',
            'diagnosis' => 'Acute bronchitis',
            'status' => Encounter::STATUS_COMPLETED,
        ]);

        Prescription::create([
            'encounter_id' => $encounter->id,
            'patient_id' => $patient->id,
            'physician_id' => $physician->id,
            'prescription_number' => 'RX-2026-0099',
            'medication_name' => 'Amoxicillin',
            'strength' => '500 mg',
            'route' => 'oral',
            'quantity' => 14,
            'instructions' => 'Take every 8 hours.',
            'issued_at' => now()->subDay(),
        ]);

        $this->actingAs($patientUser)
            ->get(route('patient.dashboard'))
            ->assertOk()
            ->assertSee('Amoxicillin')
            ->assertSee('Update profile');

        $this->actingAs($physicianUser)
            ->getJson(route('physician.dashboard'))
            ->assertOk()
            ->assertJsonStructure(['html'])
            ->assertSee('Next patient up');

        $this->actingAs($receptionist)
            ->get(route('admin.queue.index', ['date' => $today]))
            ->assertOk()
            ->assertSee('Now calling:')
            ->assertSee('Daily capacity:');
    }
}
