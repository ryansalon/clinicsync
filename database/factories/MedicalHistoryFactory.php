<?php

namespace Database\Factories;

use App\Models\MedicalHistory;
use App\Models\Patient;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MedicalHistory>
 */
class MedicalHistoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'patient_id' => Patient::factory(),
            'type' => fake()->randomElement(MedicalHistory::TYPES),
            'condition_name' => fake()->randomElement([
                'Hypertension',
                'Diabetes Mellitus',
                'Asthma',
                'Appendectomy',
                'Hypertension (family)',
            ]),
            'year_diagnosed' => fake()->numberBetween(1990, (int) date('Y')),
            'status' => fake()->randomElement(['active', 'resolved', 'controlled']),
            'notes' => fake()->sentence(),
        ];
    }
}
