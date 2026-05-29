<?php

namespace App\Policies;

use App\Models\User;

class UserBlockPolicy
{
    /**
     * Determine whether the user can block the target.
     */
    public function block(User $user, User $target): bool
    {
        return $user->isActive() && $target->isActive() && $user->id !== $target->id;
    }
}
