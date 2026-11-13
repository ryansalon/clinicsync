<?php

namespace Database\Factories;

use App\Models\Encounter;
use App\Models\Patient;
use App\Models\Physician;
use App\Models\Prescription;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Prescription>
 */
class PrescriptionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $medications = [
            ['Amoxicillin', '500mg', 'capsule'],
            ['Paracetamol', '500mg', 'tablet'],
            ['Lisinopril', '10mg', 'tablet'],
            ['Metformin', '500mg', 'tablet'],
            ['Cetirizine', '10mg', 'tablet'],
            ['Omeprazole', '20mg', 'capsule'],
        ];
        [$name, $strength, $form] = fake()->randomElement($medications);

        return [
            'encounter_id' => Encounter::factory(),
            'patient_id' => Patient::factory(),
            'physician_id' => Physician::factory(),
            'prescription_number' => strtoupper('RX-'.fake()->unique()->numerify('########')),
            'medication_name' => $name,
            'strength' => $strength,
            'dosage_form' => $form,
            'dosage' => '1',
            'frequency' => fake()->randomElement(['TID', 'BID', 'QID', 'QHS', 'PRN']),
            'route' => 'oral',
            'duration' => fake()->randomElement(['3 days', '5 days', '7 days', '14 days']),
            'quantity' => fake()->numberBetween(10, 60),
            'instructions' => fake()->sentence(),
            'issued_at' => fake()->dateTimeBetween('-30 days', 'now'),
        ];
    }
}
