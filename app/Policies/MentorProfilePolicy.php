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
        return $user->isActive() && $user->can('manage_mentor_access');
    }
}
