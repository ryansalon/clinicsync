<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role as SpatieRole;

class AdminSeeder extends Seeder
{
    /**
     * Provision a real administrator without any demo data.
     *
     * Credentials come from ADMIN_EMAIL / ADMIN_PASSWORD so no shared or
     * hardcoded password can ever reach a deployed environment.
     */
    public function run(): void
    {
        $email = (string) env('ADMIN_EMAIL');
        $password = (string) env('ADMIN_PASSWORD');

        if ($email === '' || $password === '') {
            $this->command?->error('Set ADMIN_EMAIL and ADMIN_PASSWORD in the environment before seeding an administrator.');

            return;
        }

        SpatieRole::findOrCreate(Role::Administrator->value, 'web');

        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'name' => (string) env('ADMIN_NAME', 'System Administrator'),
                'password' => $password,
                'is_active' => true,
            ],
        );

        if ($password !== '' && ! $user->wasRecentlyCreated) {
            $user->update(['password' => $password]);
        }

        $user->syncRoles([Role::Administrator->value]);

        $this->command?->info("Administrator ready: {$email}");
    }
}
