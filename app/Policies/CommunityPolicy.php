<?php

namespace App\Policies;

use App\Models\User;

class CommunityPolicy
{
    public function manage(User $user): bool
    {
        return $user->hasRole('admin') || $user->can('manage_communities');
    }
}
