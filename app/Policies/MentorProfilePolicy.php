<?php

namespace App\Policies;

use App\Models\MentorProfile;
use App\Models\User;

class MentorProfilePolicy
{
    /**
     * Any active user can view a discoverable mentor profile.
     */
    public function view(User $user, MentorProfile $mentorProfile): bool
    {
        return $user->isActive() && $mentorProfile->is_active;
    }

    /**
     * Only the mentor themselves can update their own profile.
     */
    public function update(User $user, MentorProfile $mentorProfile): bool
    {
        return $user->isVerified()
            && (int) $mentorProfile->user_id === (int) $user->id;
    }

    /**
     * Admin can revoke a mentor profile.
     */
    public function revoke(User $user, MentorProfile $mentorProfile): bool
    {
        return $user->isActive() && ($user->can('manage_mentor_access') || $user->can('manage_reports'));
    }

    /**
     * Any active user can report a mentor profile (not their own).
     */
    public function report(User $user, MentorProfile $mentorProfile): bool
    {
        if (! $user->isActive()) {
            return false;
        }

        if ((int) $mentorProfile->user_id === (int) $user->id) {
            return false;
        }

        return $mentorProfile->is_active;
    }
}
