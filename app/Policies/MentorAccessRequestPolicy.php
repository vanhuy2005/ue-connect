<?php

namespace App\Policies;

use App\Actions\Mentor\RequestMentorAccessAction;
use App\Models\MentorAccessRequest;
use App\Models\User;

class MentorAccessRequestPolicy
{
    /**
     * Alumni or advisor (or exceptional student with config enabled) can apply.
     */
    public function create(User $user): bool
    {
        if (! $user->isActive()) {
            return false;
        }

        $profile = $user->profile;
        if (! $profile) {
            return false;
        }

        if (! empty(RequestMentorAccessAction::eligibleRoleContextsFor($user))) {
            return true;
        }

        return false;
    }

    /**
     * Admin can view any mentor access request.
     */
    public function view(User $user, MentorAccessRequest $request): bool
    {
        if ($request->user_id === $user->id) {
            return true;
        }

        return $user->isActive() && $user->can('manage_mentor_access');
    }

    /**
     * Only admin can review/approve/reject/revoke.
     */
    public function review(User $user, MentorAccessRequest $request): bool
    {
        return $user->isActive() && $user->can('manage_mentor_access');
    }

    /**
     * Admin can grant mentor access directly.
     */
    public function grant(User $user): bool
    {
        return $user->isActive() && $user->can('manage_mentor_access');
    }

    /**
     * Admin can revoke mentor access.
     */
    public function revoke(User $user): bool
    {
        return $user->isActive() && $user->can('manage_mentor_access');
    }
}
