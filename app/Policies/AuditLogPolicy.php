<?php

namespace App\Policies;

use App\Models\User;

class AuditLogPolicy
{
    public function view(User $user): bool
    {
        return $user->hasRole('admin') || (method_exists($user, 'hasPermissionTo') && $user->hasPermissionTo('view_audit_log'));
    }
}
