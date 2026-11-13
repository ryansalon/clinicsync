<?php

namespace Database\Factories;

use App\Models\Patient;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Patient>
 */
class PatientFactory extends Factory
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
            'patient_number' => strtoupper('PT-'.fake()->unique()->numerify('######')),
            'date_of_birth' => fake()->dateTimeBetween('-80 years', '-1 year')->format('Y-m-d'),
            'sex' => fake()->randomElement(['male', 'female']),
            'civil_status' => fake()->randomElement(['single', 'married', 'widowed']),
            'address' => fake()->address(),
            'blood_type' => fake()->randomElement(['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-']),
            'emergency_contact_name' => fake()->name(),
            'emergency_contact_phone' => fake()->numerify('09#########'),
            'emergency_contact_relationship' => fake()->randomElement(['Spouse', 'Parent', 'Sibling', 'Friend']),
        ];
    }
}
