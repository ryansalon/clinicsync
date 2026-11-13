<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\Allergy;
use App\Models\Appointment;
use App\Models\AuditLog;
use App\Models\Encounter;
use App\Models\MedicalHistory;
use App\Models\Patient;
use App\Models\Physician;
use App\Models\PhysicianSchedule;
use App\Models\Prescription;
use App\Models\User;
use App\Models\Vital;
use App\Services\AppointmentService;
use App\Services\SequenceGenerator;
use Illuminate\Database\Seeder;

class DemoDataSeeder extends Seeder
{
    /**
     * Seed a fully navigable demo clinic: staff, physicians, schedules and
     * a small amount of clinical history so every screen has real data.
     *
     * Demo accounts ship with the shared password "password", so this seeder
     * refuses to run outside local development.
     */
    public function run(): void
    {
        if (app()->isProduction()) {
            $this->command?->error('DemoDataSeeder is disabled in production. Use AdminSeeder for a real administrator.');

            return;
        }

        $administrator = $this->makeUser('Kurt Lynand M. Mier', 'admin@clinicsync.test', '09170000001', Role::Administrator);
        $receptionist = $this->makeUser('Mary Rose P. Abing', 'receptionist@clinicsync.test', '09170000002', Role::Receptionist);

        $physicianOne = $this->makePhysician('Elmer G. Santos', 'physician1@clinicsync.test', 'General Medicine');
        $physicianTwo = $this->makePhysician('Liza P. Delos Reyes', 'physician2@clinicsync.test', 'Pediatrics');

        $this->giveSchedules($physicianOne);
        $this->giveSchedules($physicianTwo);

        $patients = collect([
            $this->makePatient('Juan D. Cruz', 'patient1@clinicsync.test', '1998-04-12', 'male'),
            $this->makePatient('Maria C. Veloso', 'patient2@clinicsync.test', '2001-09-30', 'female'),
            $this->makePatient('Roberto M. Ombra', 'patient3@clinicsync.test', '1975-01-22', 'male'),
            $this->makePatient('Angela B. Tambo', 'patient4@clinicsync.test', '2014-06-05', 'female'),
            $this->makePatient('Crisanto L. Uy', 'patient5@clinicsync.test', '1989-11-18', 'male'),
        ]);

        $this->buildHistory($patients[2]);
        $this->buildHistory($patients[3]);
        $this->buildAllergies($patients[3]);

        $service = app(AppointmentService::class);

        $today = now()->toDateString();
        $tomorrow = now()->addDay()->toDateString();
        $nextWeek = now()->addDays(5)->toDateString();

        $booked = [
            [$patients[0], $physicianOne, $today, '09:00', 'Recurring headache for three days', Appointment::STATUS_CONFIRMED],
            [$patients[1], $physicianOne, $today, '09:30', 'Follow-up on hypertension medication', Appointment::STATUS_PENDING],
            [$patients[2], $physicianOne, $today, '10:00', 'Persistent cough', Appointment::STATUS_CONFIRMED],
            [$patients[3], $physicianTwo, $today, '09:15', 'Pediatric check-up', Appointment::STATUS_CONFIRMED],
            [$patients[4], $physicianTwo, $today, '09:45', 'Annual physical examination', Appointment::STATUS_PENDING],
            [$patients[0], $physicianTwo, $tomorrow, '08:30', 'Laboratory result consultation', Appointment::STATUS_PENDING],
            [$patients[4], $physicianOne, $nextWeek, '13:30', 'Dental referral follow-up', Appointment::STATUS_PENDING],
        ];

        $appointments = [];

        foreach ($booked as [$patient, $physician, $date, $time, $reason, $status]) {
            $appointments[] = $service->book($patient, $physician, [
                'appointment_date' => $date,
                'start_time' => $time,
                'status' => $status,
                'source' => 'online',
                'chief_complaint' => $reason,
                'reason' => $reason,
                'booked_by' => $patient->user_id,
            ]);
        }

        $this->closeOutFirstConsultation($appointments[2], $administrator);
    }

    private function makeUser(string $name, string $email, string $phone, Role $role): User
    {
        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'phone' => $phone,
                'password' => 'password',
                'is_active' => true,
            ],
        );

        $user->syncRoles([$role->value]);

        return $user;
    }

    private function makePhysician(string $name, string $email, string $specialization): Physician
    {
        $user = $this->makeUser($name, $email, '09170000010', Role::Physician);

        $physician = Physician::firstOrCreate(
            ['user_id' => $user->id],
            [
                'physician_number' => $this->nextNumber(Physician::query()->max('id') ?? 0, 'DR-'),
                'specialization' => $specialization,
                'license_number' => 'PTR-'.random_int(10000000, 99999999),
                'bio' => 'Attending physician at the Camiguin Polytechnic State College outpatient clinic.',
                'appointment_slot_minutes' => 15,
            ],
        );

        return $physician;
    }

    private function makePatient(string $name, string $email, string $dateOfBirth, string $sex): Patient
    {
        $user = $this->makeUser($name, $email, '0917'.random_int(1000000, 9999999), Role::Patient);

        return Patient::firstOrCreate(
            ['user_id' => $user->id],
            [
                'patient_number' => $this->nextNumber(Patient::query()->max('id') ?? 0, 'PT-'),
                'date_of_birth' => $dateOfBirth,
                'sex' => $sex,
                'civil_status' => 'single',
                'address' => 'Mambajao, Camiguin',
                'blood_type' => $this->randomOf(['A+', 'B+', 'O+', 'AB+']),
                'emergency_contact_name' => 'Emergency Contact',
                'emergency_contact_phone' => '09180000000',
                'emergency_contact_relationship' => 'Parent',
            ],
        );
    }

    private function giveSchedules(Physician $physician): void
    {
        foreach ([1, 2, 3, 4, 5] as $day) {
            PhysicianSchedule::firstOrCreate([
                'physician_id' => $physician->id,
                'day_of_week' => $day,
                'start_time' => '08:00',
                'end_time' => '12:00',
            ], ['is_active' => true]);

            PhysicianSchedule::firstOrCreate([
                'physician_id' => $physician->id,
                'day_of_week' => $day,
                'start_time' => '13:00',
                'end_time' => '17:00',
            ], ['is_active' => true]);
        }
    }

    private function buildHistory(Patient $patient): void
    {
        MedicalHistory::firstOrCreate(
            ['patient_id' => $patient->id, 'condition_name' => 'Hypertension'],
            ['type' => 'past_medical', 'year_diagnosed' => 2019, 'status' => 'controlled', 'notes' => 'Maintained with lifestyle changes.'],
        );

        MedicalHistory::firstOrCreate(
            ['patient_id' => $patient->id, 'condition_name' => 'Appendectomy'],
            ['type' => 'past_surgical', 'year_diagnosed' => 2016, 'status' => 'resolved', 'notes' => 'Uncomplicated recovery.'],
        );
    }

    private function buildAllergies(Patient $patient): void
    {
        Allergy::firstOrCreate(
            ['patient_id' => $patient->id, 'allergen' => 'Amoxicillin'],
            ['reaction' => 'Skin rash and itching', 'severity' => 'moderate', 'is_active' => true],
        );

        Allergy::firstOrCreate(
            ['patient_id' => $patient->id, 'allergen' => 'Peanuts'],
            ['reaction' => 'Anaphylaxis', 'severity' => 'severe', 'is_active' => true],
        );
    }

    /**
     * Close a real consultation so the EMR, vitals and e-prescription screens have data.
     */
    private function closeOutFirstConsultation(Appointment $appointment, User $administrator): void
    {
        $encounter = Encounter::firstOrCreate(
            ['appointment_id' => $appointment->id],
            [
                'patient_id' => $appointment->patient_id,
                'physician_id' => $appointment->physician_id,
                'encounter_number' => $this->nextEncounterNumber(),
                'encountered_at' => now()->subHours(2),
                'chief_complaint' => $appointment->chief_complaint,
                'diagnosis' => 'Tension-type headache',
                'diagnosis_code' => 'G44.209',
                'assessment' => 'Non-focal neurological examination. No red flags reported.',
                'treatment_plan' => 'Adequate hydration, rest, and analgesic as needed for two weeks.',
                'clinical_notes' => 'Patient advised to return if symptoms worsen or persist beyond 14 days.',
                'status' => Encounter::STATUS_COMPLETED,
            ],
        );

        Vital::firstOrCreate(
            ['encounter_id' => $encounter->id],
            [
                'temperature_c' => 36.8,
                'systolic_bp' => 118,
                'diastolic_bp' => 76,
                'heart_rate' => 82,
                'respiratory_rate' => 18,
                'weight_kg' => 64.5,
                'height_cm' => 170,
                'spo2' => 99,
            ],
        );

        Prescription::firstOrCreate(
            ['encounter_id' => $encounter->id, 'medication_name' => 'Paracetamol'],
            [
                'patient_id' => $encounter->patient_id,
                'physician_id' => $encounter->physician_id,
                'prescription_number' => $this->nextPrescriptionNumber(),
                'strength' => '500 mg',
                'dosage_form' => 'Tablet',
                'dosage' => '1 tablet',
                'frequency' => 'Every 8 hours as needed',
                'route' => 'oral',
                'duration' => '5 days',
                'quantity' => 15,
                'instructions' => 'Take after meals. Do not exceed 3 grams in 24 hours.',
                'issued_at' => now()->subHours(2),
            ],
        );

        $appointment->update([
            'status' => Appointment::STATUS_COMPLETED,
            'started_at' => now()->subHours(3),
            'finished_at' => now()->subHours(2),
            'handled_by' => $administrator->id,
        ]);

        AuditLog::firstOrCreate(
            ['user_id' => $administrator->id, 'action' => 'appointment.completed'],
            [
                'auditable_type' => $appointment->getMorphClass(),
                'auditable_id' => $appointment->id,
                'new_values' => ['status' => Appointment::STATUS_COMPLETED],
                'ip_address' => '127.0.0.1',
            ],
        );
    }

    private function nextNumber(int $currentId, string $prefix): string
    {
        return $prefix.str_pad((string) ($currentId + 1), 6, '0', STR_PAD_LEFT);
    }

    /**
     * @param  array<int, string>  $options
     */
    private function randomOf(array $options): string
    {
        return $options[array_rand($options)];
    }

    private function nextEncounterNumber(): string
    {
        return SequenceGenerator::next(
            Encounter::query()->orderByDesc('id')->value('encounter_number'),
            'ENC-',
        );
    }

    private function nextPrescriptionNumber(): string
    {
        return SequenceGenerator::next(
            Prescription::query()->orderByDesc('id')->value('prescription_number'),
            'RX-',
        );
    }
}
