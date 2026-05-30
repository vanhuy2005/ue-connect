<?php

namespace App\Policies;

use App\Models\User;

class SettingsPolicy
{
    /**
     * Determine if a user can view their settings page.
     */
    public function viewSettings(User $user, ?User $target = null): bool
    {
        if ($target && $user->id !== $target->id) {
            return false;
        }

        return $user->isActive() || $user->isRestricted();
    }

    /**
     * Determine if a user can update privacy settings.
     */
    public function updatePrivacy(User $user, ?User $target = null): bool
    {
        if ($target && $user->id !== $target->id) {
            return false;
        }

        return $user->isActive() && $user->isVerified();
    }

    /**
     * Determine if a user can update notification preferences.
     */
    public function updateNotifications(User $user, ?User $target = null): bool
    {
        if ($target && $user->id !== $target->id) {
            return false;
        }

        return $user->isActive() && $user->isVerified();
    }

    /**
     * Determine if a user can view blocked users.
     */
    public function viewBlockedUsers(User $user, ?User $target = null): bool
    {
        if ($target && $user->id !== $target->id) {
            return false;
        }

        return $user->isActive() && $user->isVerified();
    }
}
