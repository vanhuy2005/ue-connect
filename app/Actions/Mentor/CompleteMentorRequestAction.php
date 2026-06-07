<?php

namespace App\Actions\Mentor;

use App\Enums\MentorRequestStatus;
use App\Models\MentorRequest;
use App\Models\User;
use App\Notifications\Mentor\MentorRequestCompletedNotification;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Gate;

class CompleteMentorRequestAction
{
    /**
     * Either participant can mark a request as completed.
     *
     * @throws AuthorizationException
     */
    public function execute(User $user, MentorRequest $mentorRequest): MentorRequest
    {
        Gate::forUser($user)->authorize('complete', $mentorRequest);

        $mentorRequest->update([
            'status' => MentorRequestStatus::Completed,
            'completed_at' => now(),
        ]);

        // Notify both participants
        $mentorRequest->student->notify(new MentorRequestCompletedNotification($mentorRequest));
        $mentorRequest->mentor->notify(new MentorRequestCompletedNotification($mentorRequest));

        // TODO: emit mentor_request_completed analytics event

        return $mentorRequest->fresh();
    }
}
