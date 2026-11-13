<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleSeeder extends Seeder
{
    /**
     * Authorization is role-based only; the app gates every route and policy on
     * roles, so no fine-grained permissions are seeded.
     *
     * @var string[]
     */
    private const ROLES = [
        'administrator',
        'receptionist',
        'physician',
        'patient',
    ];

    /**
     * Create every role required by the access control matrix.
     */
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (self::ROLES as $role) {
            Role::findOrCreate($role, 'web');
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
