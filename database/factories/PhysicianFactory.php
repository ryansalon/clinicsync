<?php

namespace Database\Factories;

use App\Models\Physician;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Physician>
 */
class PhysicianFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => UserFactory::new(),
            'physician_number' => strtoupper('DR-'.fake()->unique()->numerify('####')),
            'specialization' => fake()->randomElement([
                'General Medicine',
                'Pediatrics',
                'Internal Medicine',
                'Obstetrics and Gynecology',
                'Family Medicine',
            ]),
            'license_number' => fake()->numerify('PTR-########'),
            'appointment_slot_minutes' => fake()->randomElement([10, 15, 20, 30]),
        ];
    }
}
