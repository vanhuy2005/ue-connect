<?php

namespace App\Actions\Mentor;

use App\Enums\MentorAvailabilityStatus;
use App\Models\MentorProfile;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Gate;

class ToggleMentorAvailabilityAction
{
    /**
     * Mentor toggles their availability status.
     *
     * @throws AuthorizationException
     */
    public function execute(User $user, MentorProfile $mentorProfile, MentorAvailabilityStatus $newStatus): MentorProfile
    {
        Gate::forUser($user)->authorize('update', $mentorProfile);

        $mentorProfile->update(['availability_status' => $newStatus]);

        // TODO: emit mentor_availability_paused analytics event

        return $mentorProfile->fresh();
    }
}
