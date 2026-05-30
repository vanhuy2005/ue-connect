<?php

namespace App\Policies;

use App\Models\BlockedUser;
use App\Models\Profile;
use App\Models\User;

class ProfilePolicy
{
    /**
     * Determine whether the user can view the target's public profile.
     */
    public function viewProfile(User $viewer, Profile $profile): bool
    {
        // Own profile is always viewable
        if ((int) $viewer->id === (int) $profile->user_id) {
            return true;
        }

        $targetUser = $profile->user;
        if (! $targetUser) {
            return false;
        }

        // Both must be active
        if (! $viewer->isActive() || ! $targetUser->isActive()) {
            return false;
        }

        // Privacy block check: Blocked users cannot view full profile
        $hasBlock = BlockedUser::where(function ($q) use ($viewer, $targetUser) {
            $q->where('blocker_id', $viewer->id)->where('blocked_id', $targetUser->id);
        })->orWhere(function ($q) use ($viewer, $targetUser) {
            $q->where('blocker_id', $targetUser->id)->where('blocked_id', $viewer->id);
        })->exists();

        if ($hasBlock) {
            return false;
        }

        // Public/discoverable profile check
        if (! $profile->discoverable) {
            return false;
        }

        return true;
    }

    /**
     * Determine whether the user can edit the target's profile.
     */
    public function editProfile(User $viewer, Profile $profile): bool
    {
        return $viewer->isActive() && (int) $viewer->id === (int) $profile->user_id;
    }

    /**
     * Determine whether the user can message the target profile's owner.
     */
    public function message(User $viewer, Profile $profile): bool
    {
        if ((int) $viewer->id === (int) $profile->user_id) {
            return false;
        }

        $targetUser = $profile->user;

        return $viewer->isActive() && $targetUser && $targetUser->isActive();
    }

    /**
     * Determine whether the user can connect/send greeting to target profile's owner.
     */
    public function connect(User $viewer, Profile $profile): bool
    {
        if ((int) $viewer->id === (int) $profile->user_id) {
            return false;
        }

        $targetUser = $profile->user;

        return $viewer->isActive() && $targetUser && $targetUser->isActive();
    }

    /**
     * Determine whether the user can block the target profile's owner.
     */
    public function block(User $viewer, Profile $profile): bool
    {
        if ((int) $viewer->id === (int) $profile->user_id) {
            return false;
        }

        $targetUser = $profile->user;

        return $viewer->isActive() && $targetUser && $targetUser->isActive();
    }

    /**
     * Determine whether the user can report the target profile's owner.
     */
    public function report(User $viewer, Profile $profile): bool
    {
        if ((int) $viewer->id === (int) $profile->user_id) {
            return false;
        }

        $targetUser = $profile->user;

        return $viewer->isActive() && $targetUser && $targetUser->isActive();
    }
}
