<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleAndPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // 1. Create permissions
        $permissions = [
            'review_verification',
            'manage_users',
            'manage_reports',
            'moderate_content',
            'view_audit_log',
            'view_admin_dashboard',
        ];

        foreach ($permissions as $permissionName) {
            Permission::findOrCreate($permissionName, 'web');
        }

        // Forget cached permissions to force reload from DB during role syncing
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // 2. Create roles and assign permissions
        $roles = [
            'student' => [],
            'alumni' => [],
            'advisor' => [],
            'moderator' => [
                'moderate_content',
                'manage_reports',
                'view_admin_dashboard',
            ],
            'admin' => $permissions,
            'super_admin' => $permissions,
        ];

        foreach ($roles as $roleName => $rolePermissions) {
            $role = Role::findOrCreate($roleName, 'web');
            if (! empty($rolePermissions)) {
                $role->syncPermissions($rolePermissions);
            }
        }
    }
}
