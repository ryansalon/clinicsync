<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * Role events are deliberately left enabled so spatie/laravel-permission
     * can flush its cache while roles are being created.
     *
     * AdminSeeder only acts when ADMIN_EMAIL / ADMIN_PASSWORD are set, so a
     * plain `db:seed` is safe in every environment.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            AdminSeeder::class,
            DemoDataSeeder::class,
        ]);
    }
}
