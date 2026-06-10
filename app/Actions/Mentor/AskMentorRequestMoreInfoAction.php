<?php

namespace App\Actions\Mentor;

use App\Enums\MentorRequestStatus;
use App\Models\MentorRequest;
use App\Models\User;
use App\Notifications\Mentor\MentorRequestMoreInfoNotification;
use App\Support\Navigation\UserNavigationMetrics;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Gate;

class AskMentorRequestMoreInfoAction
{
    /**
     * Mentor asks student for more information before deciding.
     *
     * @param  array{more_info_question: string}  $data
     *
     * @throws AuthorizationException
     */
    public function execute(User $mentor, MentorRequest $mentorRequest, array $data): MentorRequest
    {
        Gate::forUser($mentor)->authorize('askMoreInfo', $mentorRequest);

        $mentorRequest->update([
            'status' => MentorRequestStatus::NeedMoreInfo,
            'more_info_question' => $data['more_info_question'],
        ]);

        // Notify student
        $mentorRequest->student->notify(new MentorRequestMoreInfoNotification($mentorRequest));

        app(UserNavigationMetrics::class)->forgetForUser($mentorRequest->student);

        // TODO: emit mentor_request_more_info_requested analytics event

        return $mentorRequest->fresh();
    }
}
