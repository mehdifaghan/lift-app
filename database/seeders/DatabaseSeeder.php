<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Domain\Tenants\Tenant;
use App\Models\User;
use App\Domain\Buildings\Building;
use App\Domain\Elevators\Elevator;
use App\Domain\Tasks\Task;
use App\Domain\InventoryItems\InventoryItem;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // System admin
        $admin = User::create([
            'name' => 'System Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'system_role' => 'SYSTEM_ADMIN',
        ]);

        // Tenant demo
        $tenant = Tenant::create(['name' => 'Demo Co']);
        app()->instance('tenant.id', $tenant->id);

        $owner = User::create([
            'tenant_id' => $tenant->id,
            'name' => 'Owner',
            'email' => 'owner@example.com',
            'password' => Hash::make('password'),
            'role' => 'OWNER'
        ]);

        $tech = User::create([
            'tenant_id' => $tenant->id,
            'name' => 'Tech',
            'email' => 'tech@example.com',
            'password' => Hash::make('password'),
            'role' => 'TECH'
        ]);

        $building = Building::create(['name' => 'Main Tower', 'address' => 'Tehran']);
        $elevator = Elevator::create(['building_id' => $building->id, 'code' => 'EL-001', 'brand' => 'Otis']);

        $task = Task::create(['building_id' => $building->id, 'elevator_id' => $elevator->id, 'title' => 'Monthly service', 'assigned_to_id' => $tech->id]);

        InventoryItem::create(['code' => 'PART-001', 'name' => 'Brake Pad', 'stock' => 10]);
    }
}
