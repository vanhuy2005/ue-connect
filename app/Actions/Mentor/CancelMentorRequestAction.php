<?php

namespace App\Actions\Mentor;

use App\Enums\MentorRequestStatus;
use App\Models\MentorRequest;
use App\Models\User;
use App\Notifications\Mentor\MentorRequestCancelledNotification;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Gate;

class CancelMentorRequestAction
{
    /**
     * Student cancels a pending or need_more_info mentor request.
     *
     * @throws AuthorizationException
     */
    public function execute(User $student, MentorRequest $mentorRequest): MentorRequest
    {
        Gate::forUser($student)->authorize('cancel', $mentorRequest);

        $mentorRequest->update(['status' => MentorRequestStatus::Cancelled]);

        // Notify mentor
        $mentorRequest->mentor->notify(new MentorRequestCancelledNotification($mentorRequest));

        return $mentorRequest->fresh();
    }
}
