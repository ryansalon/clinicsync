<?php

namespace Tests\Feature;

use App\Models\Allergy;
use App\Models\Appointment;
use App\Models\AuditLog;
use App\Models\MedicalHistory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ClinicalPolishTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['administrator', 'receptionist', 'physician', 'patient'] as $role) {
            Role::findOrCreate($role, 'web');
        }
    }

    public function test_patient_can_complete_triage_and_emergency_contact_details_on_profile_settings(): void
    {
        $patientUser = $this->createUserWithRole('patient');
        $patient = $this->makePatientProfile($patientUser, [
            'blood_type' => null,
            'emergency_contact_name' => null,
            'emergency_contact_phone' => null,
        ]);

        $this->actingAs($patientUser)
            ->get(route('patient.dashboard'))
            ->assertOk()
            ->assertSee('Profile details incomplete');

        $this->actingAs($patientUser)
            ->get(route('profile.edit'))
            ->assertOk()
            ->assertSee('Outpatient triage')
            ->assertSee('Emergency contact');

        $this->actingAs($patientUser)
            ->patch(route('profile.update'), [
                'name' => 'Maria Clara Santos',
                'phone' => '09171234567',
                'blood_type' => 'O+',
                'civil_status' => 'single',
                'address' => 'Poblacion, Mambajao, Camiguin',
                'emergency_contact_name' => 'Elena Santos',
                'emergency_contact_phone' => '09189876543',
                'emergency_contact_relationship' => 'Mother',
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('profile.edit'));

        $patient->refresh();

        $this->assertSame('O+', $patient->blood_type);
        $this->assertSame('single', $patient->civil_status);
        $this->assertSame('Poblacion, Mambajao, Camiguin', $patient->address);
        $this->assertSame('Elena Santos', $patient->emergency_contact_name);
        $this->assertSame('09189876543', $patient->emergency_contact_phone);
        $this->assertSame('Mother', $patient->emergency_contact_relationship);

        $this->actingAs($patientUser)
            ->get(route('patient.dashboard'))
            ->assertOk()
            ->assertDontSee('Profile details incomplete');

        $this->assertTrue(AuditLog::where('action', 'user.profile_updated')->exists());
    }

    public function test_physician_can_record_and_toggle_allergies_and_add_medical_history_on_patient_chart(): void
    {
        $physicianUser = $this->createUserWithRole('physician');
        $physician = $this->makePhysicianProfile($physicianUser);
        $this->openPhysicianSchedule($physician);

        $patientUser = $this->createUserWithRole('patient');
        $patient = $this->makePatientProfile($patientUser);

        Appointment::factory()->create([
            'patient_id' => $patient->id,
            'physician_id' => $physician->id,
            'appointment_date' => now()->toDateString(),
            'start_time' => '09:00:00',
            'end_time' => '09:15:00',
            'status' => Appointment::STATUS_CONFIRMED,
            'called_at' => now(),
        ]);

        // Physician dashboard highlights the called appointment and links to the patient chart
        $this->actingAs($physicianUser)
            ->get(route('physician.dashboard'))
            ->assertOk()
            ->assertSee('Called')
            ->assertSee(route('physician.patients.show', $patient), false);

        // Record a new allergy
        $this->actingAs($physicianUser)
            ->post(route('physician.patients.allergies.store', $patient), [
                'allergen' => 'Amoxicillin',
                'severity' => 'severe',
                'reaction' => 'Anaphylaxis and hives',
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('physician.patients.show', $patient));

        $allergy = Allergy::where('patient_id', $patient->id)->firstOrFail();
        $this->assertSame('Amoxicillin', $allergy->allergen);
        $this->assertSame('severe', $allergy->severity);
        $this->assertTrue($allergy->is_active);
        $this->assertTrue(AuditLog::where('action', 'allergy.recorded')->exists());

        // Toggle allergy inactive
        $this->actingAs($physicianUser)
            ->patch(route('physician.patients.allergies.toggle', [$patient, $allergy]))
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('physician.patients.show', $patient));

        $this->assertFalse($allergy->fresh()->is_active);
        $this->assertTrue(AuditLog::where('action', 'allergy.updated')->exists());

        // Record a medical history condition
        $this->actingAs($physicianUser)
            ->post(route('physician.patients.histories.store', $patient), [
                'condition_name' => 'Essential hypertension',
                'type' => 'past_medical',
                'year_diagnosed' => 2022,
                'status' => 'chronic',
                'notes' => 'Controlled on Losartan 50 mg daily',
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('physician.patients.show', $patient));

        $history = MedicalHistory::where('patient_id', $patient->id)->firstOrFail();
        $this->assertSame('Essential hypertension', $history->condition_name);
        $this->assertSame('chronic', $history->status);
        $this->assertTrue(AuditLog::where('action', 'medical_history.recorded')->exists());
    }
}
