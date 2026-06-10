<?php

namespace App\Actions\Mentor;

use App\Enums\MentorRequestStatus;
use App\Models\MentorRequest;
use App\Models\User;
use App\Notifications\Mentor\MentorRequestAcceptedNotification;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class AcceptMentorRequestAction
{
    public function __construct(private readonly CreateMentorConversationAction $createConversation) {}

    /**
     * Mentor accepts a request, creating the mentor conversation.
     *
     * @param  array{mentor_response?: ?string}  $data
     *
     * @throws AuthorizationException
     */
    public function execute(User $mentor, MentorRequest $mentorRequest, array $data = []): MentorRequest
    {
        Gate::forUser($mentor)->authorize('accept', $mentorRequest);

        return DB::transaction(function () use ($mentorRequest, $data) {
            // 1. Update request status
            $mentorRequest->update([
                'status' => MentorRequestStatus::Accepted,
                'mentor_response' => $data['mentor_response'] ?? null,
                'accepted_at' => now(),
            ]);

            // 2. Create mentor conversation
            $conversation = $this->createConversation->execute($mentorRequest);

            // 3. Link conversation to request
            $mentorRequest->update(['conversation_id' => $conversation->id]);

            // 4. Sync mentor availability (may switch to Full)
            $mentorRequest->mentorProfile->syncAvailabilityFromPendingCount();

            // 5. Notify student
            $mentorRequest->student->notify(new MentorRequestAcceptedNotification($mentorRequest));

            // TODO: emit mentor_request_accepted analytics event

            return $mentorRequest->fresh();
        });
    }
}
