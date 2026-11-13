<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Encounter;
use App\Models\Patient;
use App\Models\Physician;
use App\Models\Prescription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmrAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_patient_can_read_their_own_electronic_medical_record(): void
    {
        [$patientUser, $patient] = $this->patientAccount();
        [$physicianUser, $physician] = $this->physicianAccount();

        $encounter = $this->recordEncounter($patient, $physician, 'Tension-type headache');
        Prescription::create([
            'encounter_id' => $encounter->id,
            'patient_id' => $patient->id,
            'physician_id' => $physician->id,
            'prescription_number' => 'RX-000001',
            'medication_name' => 'Paracetamol',
            'strength' => '500 mg',
            'route' => 'oral',
            'quantity' => 15,
            'instructions' => 'Take after meals.',
            'issued_at' => now(),
        ]);

        $response = $this->actingAs($patientUser)->get(route('patient.records'));

        $response->assertOk()
            ->assertSee($patient->patient_number)
            ->assertSee('Tension-type headache')
            ->assertSee('Paracetamol')
            ->assertSee('RX-000001');
    }

    public function test_a_patient_record_page_never_leaks_another_patients_data(): void
    {
        [$ownerUser, $owner] = $this->patientAccount();
        [$intruderUser] = $this->patientAccount();

        $this->actingAs($intruderUser)
            ->get(route('patient.records'))
            ->assertOk()
            ->assertDontSee($owner->patient_number);

        $this->actingAs($ownerUser)->get(route('patient.records'))->assertOk();
    }

    public function test_a_physician_can_retrieve_the_history_of_a_patient_they_are_consulting(): void
    {
        [, $patient] = $this->patientAccount();
        [$physicianUser, $physician] = $this->physicianAccount();

        Appointment::create([
            'patient_id' => $patient->id,
            'physician_id' => $physician->id,
            'appointment_date' => now()->addDay()->toDateString(),
            'start_time' => '09:00:00',
            'end_time' => '09:15:00',
            'status' => Appointment::STATUS_CONFIRMED,
            'queue_number' => 1,
        ]);

        $this->recordEncounter($patient, $physician, 'Migraine without aura');

        $this->actingAs($physicianUser)
            ->get(route('physician.patients.show', $patient))
            ->assertOk()
            ->assertSee($patient->patient_number)
            ->assertSee('Migraine without aura');
    }

    public function test_a_physician_cannot_retrieve_a_patient_they_do_not_care_for(): void
    {
        [, $patient] = $this->patientAccount();
        [$otherPhysicianUser] = $this->physicianAccount();

        $this->actingAs($otherPhysicianUser)
            ->get(route('physician.patients.show', $patient))
            ->assertForbidden();
    }

    public function test_a_receptionist_cannot_open_an_encounter(): void
    {
        [$patientUser, $patient] = $this->patientAccount();
        [, $physician] = $this->physicianAccount();
        $encounter = $this->recordEncounter($patient, $physician);

        $receptionist = $this->createUserWithRole('receptionist');

        $this->actingAs($receptionist)
            ->get(route('physician.encounters.show', $encounter))
            ->assertForbidden();

        $this->actingAs($receptionist)
            ->get(route('physician.patients.show', $patient))
            ->assertForbidden();

        $this->actingAs($patientUser)
            ->get(route('physician.encounters.show', $encounter))
            ->assertForbidden();
    }

    public function test_an_encounter_is_only_editable_by_the_physician_who_recorded_it(): void
    {
        [, $patient] = $this->patientAccount();
        [$physicianUser, $physician] = $this->physicianAccount();
        [$rivalUser] = $this->physicianAccount();

        $encounter = $this->recordEncounter($patient, $physician);

        $payload = [
            'diagnosis' => 'Updated diagnosis',
            'chief_complaint' => null,
            'diagnosis_code' => null,
            'assessment' => null,
            'treatment_plan' => null,
            'clinical_notes' => null,
        ];

        $this->actingAs($rivalUser)
            ->put(route('physician.encounters.update', $encounter), $payload)
            ->assertForbidden();

        $this->actingAs($physicianUser)
            ->put(route('physician.encounters.update', $encounter), $payload)
            ->assertRedirect(route('physician.encounters.show', $encounter));

        $this->assertSame('Updated diagnosis', $encounter->fresh()->diagnosis);
    }

    public function test_a_physician_can_record_an_encounter_and_close_the_appointment(): void
    {
        [, $patient] = $this->patientAccount();
        [$physicianUser, $physician] = $this->physicianAccount();

        $appointment = Appointment::create([
            'patient_id' => $patient->id,
            'physician_id' => $physician->id,
            'appointment_date' => now()->toDateString(),
            'start_time' => '09:00:00',
            'end_time' => '09:15:00',
            'status' => Appointment::STATUS_CONFIRMED,
            'queue_number' => 1,
        ]);

        $this->actingAs($physicianUser)
            ->post(route('physician.encounters.store', $appointment), [
                'chief_complaint' => 'Persistent cough',
                'diagnosis' => 'Acute bronchitis',
                'diagnosis_code' => 'J20.9',
                'temperature_c' => 37.6,
                'systolic_bp' => 120,
                'diastolic_bp' => 80,
                'heart_rate' => 88,
                'mark_completed' => 1,
            ])
            ->assertRedirect();

        $encounter = Encounter::firstOrFail();

        $this->assertSame($patient->id, $encounter->patient_id);
        $this->assertSame(Encounter::STATUS_COMPLETED, $encounter->status);
        $this->assertMatchesRegularExpression('/^ENC-\d{6}$/', $encounter->encounter_number);
        $this->assertNotNull($encounter->vitals);
        $this->assertSame(37.6, (float) $encounter->vitals->temperature_c);

        $appointment->refresh();
        $this->assertSame(Appointment::STATUS_COMPLETED, $appointment->status);
        $this->assertNotNull($appointment->finished_at);
        $this->assertSame($physicianUser->id, $appointment->handled_by);
    }

    public function test_a_physician_can_record_height_in_feet_and_inches(): void
    {
        [, $patient] = $this->patientAccount();
        [$physicianUser, $physician] = $this->physicianAccount();

        $appointment = Appointment::create([
            'patient_id' => $patient->id,
            'physician_id' => $physician->id,
            'appointment_date' => now()->toDateString(),
            'start_time' => '10:00:00',
            'end_time' => '10:15:00',
            'status' => Appointment::STATUS_CONFIRMED,
            'queue_number' => 1,
        ]);

        $this->actingAs($physicianUser)
            ->post(route('physician.encounters.store', $appointment), [
                'chief_complaint' => 'Annual check-up',
                'diagnosis' => 'Healthy, no findings',
                'height_feet' => 5,
                'height_inches' => 10,
                'weight_kg' => 62.5,
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $vitals = Encounter::firstOrFail()->vitals;

        // 5'10" = (5 × 30.48) + (10 × 2.54) = 177.8 cm
        $this->assertSame(177.8, (float) $vitals->height_cm);
        $this->assertSame(62.5, (float) $vitals->weight_kg);
    }

    /**
     * @return array{0: User, 1: Patient}
     */
    private function patientAccount(): array
    {
        $user = $this->createUserWithRole('patient');

        return [$user, $this->makePatientProfile($user)];
    }

    /**
     * @return array{0: User, 1: Physician}
     */
    private function physicianAccount(): array
    {
        $user = $this->createUserWithRole('physician');

        return [$user, $this->makePhysicianProfile($user)];
    }

    private function recordEncounter(Patient $patient, Physician $physician, string $diagnosis = 'Seasonal influenza'): Encounter
    {
        return Encounter::create([
            'patient_id' => $patient->id,
            'physician_id' => $physician->id,
            'encounter_number' => 'ENC-000001',
            'encountered_at' => now()->subDay(),
            'chief_complaint' => 'Consultation',
            'diagnosis' => $diagnosis,
            'status' => Encounter::STATUS_COMPLETED,
        ]);
    }
}
