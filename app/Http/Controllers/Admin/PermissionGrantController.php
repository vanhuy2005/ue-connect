<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\GrantPermissionRequest;
use App\Models\PermissionGrant;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class PermissionGrantController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('manage_permissions');

        $grants = PermissionGrant::with(['user', 'granter'])->latest()->paginate(25);

        $permissionKeys = [
            'manage_users', 'suspend_users', 'ban_users', 'manage_permissions',
            'review_verification', 'approve_verification', 'manage_communities',
            'manage_mentor_access', 'view_audit_log',
        ];

        return view('admin.permission-grants', [
            'grants' => $grants,
            'permissionKeys' => $permissionKeys,
        ]);
    }

    public function store(GrantPermissionRequest $request, AuditService $audit)
    {
        Gate::authorize('manage_permissions');

        $data = $request->validated();

        $grant = PermissionGrant::create([
            'user_id' => $data['user_id'],
            'permission_key' => $data['permission_key'],
            'scope_type' => $data['scope_type'] ?? null,
            'scope_id' => $data['scope_id'] ?? null,
            'granted_by' => $request->user()->id,
            'reason' => $data['reason'],
            'starts_at' => $data['starts_at'] ?? null,
            'expires_at' => $data['expires_at'] ?? null,
            'status' => 'active',
        ]);

        $audit->log([
            'action' => 'grant_permission',
            'target_type' => 'permission_grant',
            'target_id' => $grant->id,
            'before_values' => null,
            'after_values' => $grant->toArray(),
            'reason' => $grant->reason,
        ]);

        return redirect()->route('admin.permission-grants.index')->with('status', 'Permission granted.');
    }

    public function revoke(Request $request, PermissionGrant $grant, AuditService $audit)
    {
        Gate::authorize('manage_permissions');

        $request->validate(['reason' => 'required|string|min:10']);

        $before = $grant->toArray();

        $grant->update([
            'status' => 'revoked',
            'revoked_at' => now(),
            'revoked_by' => $request->user()->id,
        ]);

        $audit->log([
            'action' => 'revoke_permission',
            'target_type' => 'permission_grant',
            'target_id' => $grant->id,
            'before_values' => $before,
            'after_values' => $grant->toArray(),
            'reason' => $request->input('reason'),
        ]);

        return redirect()->route('admin.permission-grants.index')->with('status', 'Permission revoked.');
    }
}
