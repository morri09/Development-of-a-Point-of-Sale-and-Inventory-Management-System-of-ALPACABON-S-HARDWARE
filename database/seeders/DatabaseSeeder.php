<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed roles first
        $this->call(RoleSeeder::class);

        // Get the administrator role
        $adminRole = Role::where('name', 'administrator')->first();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'role' => 'admin',
            'role_id' => $adminRole?->id,
        ]);

        // Seed default store settings
        $this->call([
            SettingsSeeder::class,
            ProductSeeder::class,
            SalesSeeder::class,
        ]);
    }
}
