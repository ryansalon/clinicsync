<?php

namespace Database\Factories;

use App\Models\Appointment;
use App\Models\Patient;
use App\Models\Physician;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Appointment>
 */
class AppointmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startTime = fake()->randomElement(['08:00', '08:15', '08:30', '09:00', '09:15', '10:00', '13:30', '14:00']);

        return [
            'patient_id' => Patient::factory(),
            'physician_id' => Physician::factory(),
            'appointment_date' => fake()->dateTimeBetween('+1 day', '+30 days')->format('Y-m-d'),
            'start_time' => $startTime,
            'end_time' => $this->addMinutes($startTime, 15),
            'source' => fake()->randomElement(['online', 'walk_in']),
            'status' => fake()->randomElement([
                Appointment::STATUS_PENDING,
                Appointment::STATUS_CONFIRMED,
            ]),
            'chief_complaint' => fake()->sentence(4),
            'queue_number' => fake()->unique()->numberBetween(1, 9999),
        ];
    }

    private function addMinutes(string $time, int $minutes): string
    {
        return date('H:i:s', strtotime($time) + $minutes * 60);
    }
}
