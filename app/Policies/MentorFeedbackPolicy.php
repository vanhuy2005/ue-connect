<?php

namespace App\Policies;

use App\Enums\MentorRequestStatus;
use App\Models\MentorFeedback;
use App\Models\MentorRequest;
use App\Models\User;

class MentorFeedbackPolicy
{
    /**
     * Student can submit feedback for a completed mentor request they participated in,
     * and only if feedback has not been submitted yet.
     */
    public function create(User $user, MentorRequest $mentorRequest): bool
    {
        if (! $user->isActive()) {
            return false;
        }

        if ($mentorRequest->student_id !== $user->id) {
            return false;
        }

        if ($mentorRequest->status !== MentorRequestStatus::Completed) {
            return false;
        }

        // Only one feedback per request
        return ! $mentorRequest->feedback()->exists();
    }

    /**
     * Mentor can view their own private feedback aggregate (admin can view all).
     */
    public function view(User $user, MentorFeedback $feedback): bool
    {
        return $feedback->mentor_id === $user->id
            || $user->can('manage_mentor_access')
            || $user->hasRole('admin');
    }
}
