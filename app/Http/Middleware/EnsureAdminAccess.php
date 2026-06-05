<?php

namespace App\Http\Middleware;

use App\Models\Announcement;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class EnsureAdminAccess
{
    /**
     * Handle an incoming request.
     * Allow if user has any of the recognized admin permissions.
     */
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (! $user || ! $user->isActive()) {
            abort(403);
        }

        if ($user->hasRole('admin')) {
            return $next($request);
        }

        $perms = [
            'manage_users',
            'suspend_users',
            'ban_users',
            'manage_permissions',
            'review_verification',
            'approve_verification',
            'manage_communities',
            'manage_mentor_access',
            'view_audit_log',
            'view_audit_logs',
            'manage_reports',
            'manage_system_settings',
            'manage_announcements',
            'manage_media',
            'view_media_usage',
            'manage_media_quota',
            'quarantine_media',
            'delete_media',
            'sync_cloudinary_media',
        ];

        // Allow if any simple abilities match
        if (Gate::any($perms)) {
            return $next($request);
        }

        // Special-case model-based policy for announcements
        if ($user->can('manage', Announcement::class)) {
            return $next($request);
        }

        abort(403);
    }
}
