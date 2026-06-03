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
            'access_admin_console',
            'review_verification',
            'approve_verification',
            'manage_users',
            'suspend_users',
            'ban_users',
            'manage_permissions',
            'manage_communities',
            'manage_mentor_access',
            'view_audit_log',
            'view_audit_logs',
            'manage_reports',
            'manage_moderation',
            'manage_system_settings',
            'manage_announcements',
            'moderate_content',
            'view_admin_dashboard',
            'view_analytics',
            'manage_media',
            'view_media_usage',
            'manage_media_quota',
            'quarantine_media',
            'delete_media',
            'sync_cloudinary_media',
            'view_private_media',
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
            'verification_reviewer' => [
                'access_admin_console',
                'view_admin_dashboard',
                'review_verification',
                'approve_verification',
            ],
            'mentor_manager' => [
                'access_admin_console',
                'view_admin_dashboard',
                'manage_mentor_access',
                'view_audit_logs',
            ],
            'moderator' => [
                'access_admin_console',
                'view_admin_dashboard',
                'manage_reports',
                'manage_moderation',
                'moderate_content',
                'view_audit_logs',
            ],
            'admin' => $permissions,
            'super_admin' => $permissions,
        ];

        foreach ($roles as $roleName => $rolePermissions) {
            $role = Role::findOrCreate($roleName, 'web');
            $role->syncPermissions($rolePermissions);
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
}
