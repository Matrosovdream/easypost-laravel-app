<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RolesSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['slug' => 'admin',    'name' => 'Admin',    'is_system' => true, 'sort_order' => 10, 'description' => 'Full tenant access'],
            ['slug' => 'manager',  'name' => 'Manager',  'is_system' => true, 'sort_order' => 20, 'description' => 'Ops + approvals + hiring'],
            ['slug' => 'shipper',  'name' => 'Shipper',  'is_system' => true, 'sort_order' => 30, 'description' => 'Warehouse — pack, print, ship'],
            ['slug' => 'cs_agent', 'name' => 'CS Agent', 'is_system' => true, 'sort_order' => 40, 'description' => 'Customer support — returns, claims, comms'],
            ['slug' => 'client',   'name' => 'Client',   'is_system' => true, 'sort_order' => 50, 'description' => 'External merchant (3PL use)'],
            ['slug' => 'viewer',   'name' => 'Viewer',   'is_system' => true, 'sort_order' => 60, 'description' => 'Read-only — accountants / execs'],
        ];

        foreach ($roles as $r) {
            Role::updateOrCreate(['slug' => $r['slug']], $r);
        }
    }
}
