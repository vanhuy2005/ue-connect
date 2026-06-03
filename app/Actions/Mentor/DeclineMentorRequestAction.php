<?php

namespace App\Actions\Mentor;

use App\Enums\MentorRequestStatus;
use App\Models\MentorRequest;
use App\Models\User;
use App\Notifications\Mentor\MentorRequestDeclinedNotification;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Gate;

class DeclineMentorRequestAction
{
    /**
     * Mentor declines a request. No conversation is created.
     *
     * @param  array{decline_reason?: ?string}  $data
     *
     * @throws AuthorizationException
     */
    public function execute(User $mentor, MentorRequest $mentorRequest, array $data = []): MentorRequest
    {
        Gate::forUser($mentor)->authorize('decline', $mentorRequest);

        $mentorRequest->update([
            'status' => MentorRequestStatus::Declined,
            'decline_reason' => $data['decline_reason'] ?? null,
            'declined_at' => now(),
        ]);

        // Notify student — no conversation created
        $mentorRequest->student->notify(new MentorRequestDeclinedNotification($mentorRequest));

        // TODO: emit mentor_request_declined analytics event

        return $mentorRequest->fresh();
    }
}
