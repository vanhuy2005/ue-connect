<?php

namespace Database\Seeders\Reference;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class AccessControlReferenceSeeder extends Seeder
{
    /**
     * Seed canonical RBAC reference data.
     */
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach ($this->permissions() as $permissionName) {
            Permission::findOrCreate($permissionName, 'web');
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach ($this->roles() as $roleName => $rolePermissions) {
            Role::findOrCreate($roleName, 'web')->syncPermissions($rolePermissions);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    /**
     * @return list<string>
     */
    private function permissions(): array
    {
        return [
            'view_app',
            'manage_own_profile',
            'submit_verification',
            'review_verification',
            'approve_verification',
            'reject_verification',
            'manage_users',
            'suspend_users',
            'ban_users',
            'manage_roles',
            'manage_permissions',
            'view_admin_dashboard',
            'access_admin_console',
            'view_audit_log',
            'view_audit_logs',
            'create_post',
            'update_own_post',
            'delete_own_post',
            'hide_own_feed_post',
            'report_content',
            'create_comment',
            'update_own_comment',
            'delete_own_comment',
            'send_connection_request',
            'manage_own_connections',
            'block_user',
            'view_conversation',
            'send_message',
            'delete_own_message',
            'share_post_to_message',
            'mentor_access',
            'manage_mentor_access',
            'manage_club',
            'manage_community',
            'manage_community_members',
            'manage_community_posts',
            'manage_community_resources',
            'manage_community_settings',
            'moderate_community',
            'moderate_community_chat',
            'approve_community',
            'manage_communities',
            'moderate_content',
            'manage_reports',
            'manage_moderation',
            'manage_system_settings',
            'manage_system_announcements',
            'manage_announcements',
            'create_announcement',
            'view_analytics',
            'manage_media',
            'view_media_usage',
            'manage_media_quota',
            'quarantine_media',
            'delete_media',
            'sync_cloudinary_media',
            'view_private_media',
        ];
    }

    /**
     * @return array<string, list<string>>
     */
    private function roles(): array
    {
        $verifiedUserPermissions = [
            'view_app',
            'manage_own_profile',
            'create_post',
            'update_own_post',
            'delete_own_post',
            'hide_own_feed_post',
            'report_content',
            'create_comment',
            'update_own_comment',
            'delete_own_comment',
            'send_connection_request',
            'manage_own_connections',
            'block_user',
            'view_conversation',
            'send_message',
            'delete_own_message',
            'share_post_to_message',
        ];

        $adminPermissions = array_values(array_unique(array_merge(
            $verifiedUserPermissions,
            [
                'access_admin_console',
                'view_admin_dashboard',
                'review_verification',
                'approve_verification',
                'reject_verification',
                'manage_mentor_access',
                'view_audit_log',
                'view_audit_logs',
                'manage_reports',
                'manage_moderation',
                'moderate_content',
                'moderate_community',
                'moderate_community_chat',
                'manage_club',
                'manage_community',
                'manage_community_members',
                'manage_community_posts',
                'manage_community_resources',
                'manage_community_settings',
                'manage_users',
                'suspend_users',
                'ban_users',
                'manage_roles',
                'manage_permissions',
                'approve_community',
                'manage_communities',
                'manage_system_settings',
                'manage_system_announcements',
                'manage_announcements',
                'create_announcement',
                'view_analytics',
                'manage_media',
                'view_media_usage',
                'manage_media_quota',
                'quarantine_media',
                'delete_media',
                'sync_cloudinary_media',
                'view_private_media',
            ],
        )));

        return [
            'student' => $verifiedUserPermissions,
            'alumni' => $verifiedUserPermissions,
            'teacher' => $verifiedUserPermissions,
            'admin' => $adminPermissions,
        ];
    }
}
