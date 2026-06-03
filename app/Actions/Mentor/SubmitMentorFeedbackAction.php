<?php

namespace App\Actions\Mentor;

use App\Models\MentorFeedback;
use App\Models\MentorRequest;
use App\Models\User;
use App\Notifications\Mentor\MentorFeedbackSubmittedNotification;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Gate;

class SubmitMentorFeedbackAction
{
    /**
     * Student submits private feedback for a completed mentor request.
     *
     * @param  array{helpfulness_level: string, feedback_text?: ?string}  $data
     *
     * @throws AuthorizationException
     */
    public function execute(User $student, MentorRequest $mentorRequest, array $data): MentorFeedback
    {
        Gate::forUser($student)->authorize('create', [MentorFeedback::class, $mentorRequest]);

        $feedback = MentorFeedback::create([
            'mentor_request_id' => $mentorRequest->id,
            'student_id' => $student->id,
            'mentor_id' => $mentorRequest->mentor_id,
            'helpfulness_level' => $data['helpfulness_level'],
            'feedback_text' => $data['feedback_text'] ?? null,
            'is_private' => true,
        ]);

        // Notify mentor (private)
        $mentorRequest->mentor->notify(new MentorFeedbackSubmittedNotification($feedback));

        // TODO: emit mentor_feedback_submitted analytics event

        return $feedback;
    }
}
