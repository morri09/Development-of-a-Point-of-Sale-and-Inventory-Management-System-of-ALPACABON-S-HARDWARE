<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // Administrator - Full access
        Role::updateOrCreate(
            ['name' => 'administrator'],
            [
                'display_name' => 'Administrator',
                'description' => 'Full access to all system features',
                'permissions' => config('menu.all_keys', []),
                'is_system' => true,
            ]
        );

        // Cashier - POS and basic access
        Role::updateOrCreate(
            ['name' => 'cashier'],
            [
                'display_name' => 'Cashier',
                'description' => 'Access to POS terminal and transactions',
                'permissions' => ['dashboard', 'pos', 'transactions'],
                'is_system' => false,
            ]
        );

        // Stock Manager - Inventory management
        Role::updateOrCreate(
            ['name' => 'stock_manager'],
            [
                'display_name' => 'Stock Manager',
                'description' => 'Access to inventory and product management',
                'permissions' => ['dashboard', 'products', 'inventory', 'reports'],
                'is_system' => false,
            ]
        );
    }
}
