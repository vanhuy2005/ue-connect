<?php

namespace App\Actions\Mentor;

use App\Enums\MentorRequestStatus;
use App\Models\MentorRequest;
use App\Models\User;
use App\Notifications\Mentor\MentorRequestUpdatedNotification;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Gate;

class UpdateMentorRequestAction
{
    /**
     * Student updates request with more information, resetting status to submitted.
     *
     * @param  array{topic: string, goal: string, question: string, urgency: string, context?: ?string, expected_outcome?: ?string}  $data
     *
     * @throws AuthorizationException
     */
    public function execute(User $student, MentorRequest $mentorRequest, array $data): MentorRequest
    {
        Gate::forUser($student)->authorize('update', $mentorRequest);

        $mentorRequest->update(array_merge($data, [
            'status' => MentorRequestStatus::UpdatedByStudent,
            // Keep the previous more_info_question in record or clear it?
            // Clearing it is fine since it is now resolved.
            'more_info_question' => null,
        ]));

        $mentorRequest->mentor->notify(new MentorRequestUpdatedNotification($mentorRequest));

        return $mentorRequest->fresh();
    }
}
