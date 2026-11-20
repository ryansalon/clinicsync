<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\AuditLog;
use App\Models\Patient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class WalkInRegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['administrator', 'receptionist', 'physician', 'patient'] as $role) {
            Role::findOrCreate($role, 'web');
        }
    }

    public function test_new_walk_in_patient_gets_auto_generated_patient_id_and_queue_number(): void
    {
        Notification::fake();

        $receptionist = $this->createUserWithRole('receptionist');
        $physicianUser = $this->createUserWithRole('physician');
        $physician = $this->makePhysicianProfile($physicianUser);
        $this->openPhysicianSchedule($physician);

        $today = now()->toDateString();

        $this->actingAs($receptionist)
            ->get(route('admin.queue.index'))
            ->assertOk()
            ->assertSee('ID auto-generated · PT-000001')
            ->assertDontSee('Existing patient');

        $this->actingAs($receptionist)
            ->post(route('admin.queue.store'), [
                'patient_name' => 'Elena Morales',
                'date_of_birth' => '2003-07-19',
                'sex' => 'female',
                'phone' => '09171234567',
                'physician_id' => $physician->id,
                'appointment_date' => $today,
                'start_time' => '09:00',
                'chief_complaint' => 'Mild fever and dizziness',
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('admin.queue.index', ['date' => $today]))
            ->assertSessionHas('status', 'Walk-in registered (PT-000001) and added to queue as #1.');

        $patient = Patient::with('user')->firstOrFail();

        $this->assertSame('PT-000001', $patient->patient_number);
        $this->assertSame('Elena Morales', $patient->user->name);
        $this->assertSame('pt000001@walkin.clinicsync.local', $patient->user->email);
        $this->assertTrue($patient->user->hasRole('patient'));

        $appointment = Appointment::firstOrFail();

        $this->assertSame($patient->id, $appointment->patient_id);
        $this->assertSame('walk_in', $appointment->source);
        $this->assertSame(Appointment::STATUS_CONFIRMED, $appointment->status);
        $this->assertSame(1, $appointment->queue_number);
        $this->assertTrue(AuditLog::where('action', 'user.created')->exists());
        $this->assertTrue(AuditLog::where('action', 'appointment.walk_in_registered')->exists());
    }

    public function test_consecutive_new_walk_ins_without_emails_get_unique_sequential_ids(): void
    {
        Notification::fake();

        $receptionist = $this->createUserWithRole('receptionist');
        $physicianUser = $this->createUserWithRole('physician');
        $physician = $this->makePhysicianProfile($physicianUser);
        $this->openPhysicianSchedule($physician);

        $today = now()->toDateString();

        $this->actingAs($receptionist)->post(route('admin.queue.store'), [
            'patient_name' => 'First Walk-In',
            'date_of_birth' => '2001-03-10',
            'sex' => 'male',
            'physician_id' => $physician->id,
            'appointment_date' => $today,
            'start_time' => '09:00',
        ])->assertSessionHasNoErrors();

        $this->actingAs($receptionist)->post(route('admin.queue.store'), [
            'patient_name' => 'Second Walk-In',
            'date_of_birth' => '2002-08-22',
            'sex' => 'female',
            'physician_id' => $physician->id,
            'appointment_date' => $today,
            'start_time' => '09:30',
        ])->assertSessionHasNoErrors()
            ->assertSessionHas('status', 'Walk-in registered (PT-000002) and added to queue as #2.');

        $numbers = Patient::query()->orderBy('id')->pluck('patient_number')->all();
        $this->assertSame(['PT-000001', 'PT-000002'], $numbers);
    }

    public function test_new_walk_in_validates_required_demographics_and_unique_email(): void
    {
        $receptionist = $this->createUserWithRole('receptionist');
        $physicianUser = $this->createUserWithRole('physician');
        $physician = $this->makePhysicianProfile($physicianUser);
        $this->openPhysicianSchedule($physician);

        $this->actingAs($receptionist)
            ->from(route('admin.queue.index'))
            ->post(route('admin.queue.store'), [
                'email' => $receptionist->email,
                'physician_id' => $physician->id,
                'appointment_date' => now()->toDateString(),
                'start_time' => '09:00',
            ])
            ->assertRedirect(route('admin.queue.index'))
            ->assertSessionHasErrors(['patient_name', 'date_of_birth', 'sex', 'email']);
    }
}
