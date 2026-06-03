<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Artisan;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        $permissions = [
            ['name' => 'manage_announcements', 'guard_name' => 'web'],
        ];

        foreach ($permissions as $p) {
            Permission::firstOrCreate($p);
        }

        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $admin->givePermissionTo(array_column($permissions, 'name'));

        // Ensure Spatie permission cache is reset after seeding
        Artisan::call('permission:cache-reset');
    }
}
