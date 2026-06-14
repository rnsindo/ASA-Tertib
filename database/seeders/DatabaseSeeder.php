<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->call([
            RolePermissionSeeder::class,
            AppSettingSeeder::class,
            DefaultUserSeeder::class,
            QueueServiceSeeder::class,
            QueueServiceDependencySeeder::class,
            ServiceCounterSeeder::class,
            ServiceDailyQuotaSeeder::class,
        ]);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
