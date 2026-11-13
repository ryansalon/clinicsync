<?php

namespace Tests;

use App\Models\Patient;
use App\Models\Physician;
use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Spatie\Permission\Models\Role;

abstract class TestCase extends BaseTestCase
{
    /**
     * Create a user that holds exactly one role from the access control matrix.
     *
     * @param  array<string, mixed>  $attributes
     */
    protected function createUserWithRole(string $role, array $attributes = []): User
    {
        $user = User::factory()->create($attributes);

        Role::findOrCreate($role, 'web');
        $user->assignRole($role);

        return $user;
    }

    /**
     * Link a physician profile to the given account.
     *
     * @param  array<string, mixed>  $attributes
     */
    protected function makePhysicianProfile(User $user, array $attributes = []): Physician
    {
        return Physician::factory()->create($attributes + [
            'user_id' => $user->id,
            'appointment_slot_minutes' => 15,
        ]);
    }

    /**
     * Link a patient profile to the given account.
     *
     * @param  array<string, mixed>  $attributes
     */
    protected function makePatientProfile(User $user, array $attributes = []): Patient
    {
        return Patient::factory()->create($attributes + [
            'user_id' => $user->id,
        ]);
    }

    /**
     * Open the physician for bookings on every day of the week.
     */
    protected function openPhysicianSchedule(Physician $physician, string $start = '08:00', string $end = '17:00'): void
    {
        foreach ([0, 1, 2, 3, 4, 5, 6] as $day) {
            $physician->schedules()->create([
                'day_of_week' => $day,
                'start_time' => $start,
                'end_time' => $end,
                'is_active' => true,
            ]);
        }
    }
}
