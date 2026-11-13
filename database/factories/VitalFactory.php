<?php

namespace Database\Factories;

use App\Models\Encounter;
use App\Models\Vital;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Vital>
 */
class VitalFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'encounter_id' => Encounter::factory(),
            'temperature_c' => fake()->randomFloat(1, 36.0, 39.5),
            'systolic_bp' => fake()->numberBetween(95, 160),
            'diastolic_bp' => fake()->numberBetween(60, 100),
            'heart_rate' => fake()->numberBetween(55, 110),
            'respiratory_rate' => fake()->numberBetween(12, 24),
            'weight_kg' => fake()->randomFloat(2, 30, 120),
            'height_cm' => fake()->randomFloat(1, 120, 190),
            'spo2' => fake()->numberBetween(92, 100),
        ];
    }
}
