<?php

namespace App\Policies;

use App\Models\User;

class CommunitySuggestionPolicy
{
    /**
     * Any verified active user can submit a community suggestion.
     */
    public function create(User $user): bool
    {
        return in_array($user->account_status?->value, ['active', 'profile_incomplete']);
    }

    /**
     * Only admins can review suggestions.
     */
    public function review(User $user): bool
    {
        return $user->hasRole('admin') || $user->can('manage_communities');
    }
}
