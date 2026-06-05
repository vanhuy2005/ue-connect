<?php

namespace App\Policies;

use App\Models\User;

class AuditLogPolicy
{
    public function view(User $user): bool
    {
        return $user->isActive()
            && ($user->can('view_audit_log') || $user->can('view_audit_logs'));
    }
}
