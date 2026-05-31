<?php

namespace App\Http\Middleware;

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

        if ($user && ($user->hasRole('admin') || $user->hasRole('super_admin'))) {
            return $next($request);
        }

        $perms = [
            'manage_users',
            'manage_permissions',
            'review_verification',
            'manage_communities',
            'manage_mentor_access',
            'manage_reports',
            'manage_system_settings',
        ];

        // Allow if any simple abilities match
        if (Gate::any($perms)) {
            return $next($request);
        }

        // Special-case model-based policy for announcements
        if ($user && $user->can('manage', \App\Models\Announcement::class)) {
            return $next($request);
        }

        abort(403);
    }
}
