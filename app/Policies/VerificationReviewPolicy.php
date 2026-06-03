<?php

namespace App\Policies;

use App\Models\User;

class VerificationReviewPolicy
{
    public function view(User $user): bool
    {
        return $user->hasRole('admin') || $user->can('review_verification');
    }

    public function act(User $user): bool
    {
        return $user->hasRole('admin') || $user->can('review_verification');
    }
}
