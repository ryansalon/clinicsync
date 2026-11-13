<?php

namespace Database\Factories;

use App\Models\Encounter;
use App\Models\Patient;
use App\Models\Physician;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Encounter>
 */
class EncounterFactory extends Factory
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
            'physician_id' => Physician::factory(),
            'encounter_number' => strtoupper('ENC-'.fake()->unique()->numerify('########')),
            'encountered_at' => fake()->dateTimeBetween('-30 days', 'now'),
            'chief_complaint' => fake()->sentence(5),
            'diagnosis' => fake()->randomElement([
                'Acute URI',
                'Hypertension, essential',
                'Type 2 Diabetes Mellitus',
                'Acute gastroenteritis',
                'Allergic rhinitis',
            ]),
            'treatment_plan' => fake()->paragraph(),
            'clinical_notes' => fake()->paragraph(),
            'status' => fake()->randomElement([
                Encounter::STATUS_IN_PROGRESS,
                Encounter::STATUS_COMPLETED,
            ]),
        ];
    }
}
