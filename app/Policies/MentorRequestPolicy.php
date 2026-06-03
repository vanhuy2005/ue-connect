<?php

namespace App\Policies;

use App\Enums\MentorRequestStatus;
use App\Models\MentorRequest;
use App\Models\User;

class MentorRequestPolicy
{
    /**
     * Active users can create a mentor request (subject to action-level guards).
     */
    public function create(User $user): bool
    {
        return $user->isActive();
    }

    /**
     * Participants can view their own mentor requests.
     */
    public function view(User $user, MentorRequest $request): bool
    {
        return $request->isParticipant($user);
    }

    /**
     * Only the mentor of this request can accept it.
     */
    public function accept(User $user, MentorRequest $request): bool
    {
        return $user->isActive()
            && $request->mentor_id === $user->id
            && in_array($request->status, [
                MentorRequestStatus::Submitted,
                MentorRequestStatus::NeedMoreInfo,
            ], true);
    }

    /**
     * Only the mentor of this request can decline it.
     */
    public function decline(User $user, MentorRequest $request): bool
    {
        return $user->isActive()
            && $request->mentor_id === $user->id
            && in_array($request->status, [
                MentorRequestStatus::Submitted,
                MentorRequestStatus::NeedMoreInfo,
            ]);
    }

    /**
     * Only the mentor of this request can ask for more info.
     */
    public function askMoreInfo(User $user, MentorRequest $request): bool
    {
        return $user->isActive()
            && $request->mentor_id === $user->id
            && $request->status === MentorRequestStatus::Submitted;
    }

    /**
     * Only the student of this request can cancel it.
     */
    public function cancel(User $user, MentorRequest $request): bool
    {
        return $user->isActive()
            && $request->student_id === $user->id
            && in_array($request->status, [
                MentorRequestStatus::Submitted,
                MentorRequestStatus::NeedMoreInfo,
            ]);
    }

    /**
     * Either participant can mark a request as completed.
     */
    public function complete(User $user, MentorRequest $request): bool
    {
        return $user->isActive()
            && $request->isParticipant($user)
            && $request->status === MentorRequestStatus::Accepted;
    }

    /**
     * Either participant can report a mentor request.
     */
    public function report(User $user, MentorRequest $request): bool
    {
        return $user->isActive() && $request->isParticipant($user);
    }

    /**
     * Only the student of this request can update it when it needs more info.
     */
    public function update(User $user, MentorRequest $request): bool
    {
        return $user->isActive()
            && $request->student_id === $user->id
            && $request->status === MentorRequestStatus::NeedMoreInfo;
    }
}
