<?php

namespace App\Policies;

use App\Enums\MentorRequestStatus;
use App\Models\BlockedUser;
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
        return $request->isParticipant($user)
            && ! $this->isBlockedBetween($request->student_id, $request->mentor_id);
    }

    /**
     * Only the mentor of this request can accept it.
     */
    public function accept(User $user, MentorRequest $request): bool
    {
        return $user->isActive()
            && $request->mentor_id === $user->id
            && $user->isActiveMentor()
            && ! $this->isBlockedBetween($request->student_id, $request->mentor_id)
            && in_array($request->status, [
                MentorRequestStatus::Submitted,
                MentorRequestStatus::NeedMoreInfo,
                MentorRequestStatus::UpdatedByStudent,
            ], true);
    }

    /**
     * Only the mentor of this request can decline it.
     */
    public function decline(User $user, MentorRequest $request): bool
    {
        return $user->isActive()
            && $request->mentor_id === $user->id
            && $user->isActiveMentor()
            && ! $this->isBlockedBetween($request->student_id, $request->mentor_id)
            && in_array($request->status, [
                MentorRequestStatus::Submitted,
                MentorRequestStatus::NeedMoreInfo,
                MentorRequestStatus::UpdatedByStudent,
            ], true);
    }

    /**
     * Only the mentor of this request can ask for more info.
     */
    public function askMoreInfo(User $user, MentorRequest $request): bool
    {
        return $user->isActive()
            && $request->mentor_id === $user->id
            && $user->isActiveMentor()
            && ! $this->isBlockedBetween($request->student_id, $request->mentor_id)
            && in_array($request->status, [
                MentorRequestStatus::Submitted,
                MentorRequestStatus::UpdatedByStudent,
            ], true);
    }

    /**
     * Only the student of this request can cancel it.
     */
    public function cancel(User $user, MentorRequest $request): bool
    {
        return $user->isActive()
            && $request->student_id === $user->id
            && ! $this->isBlockedBetween($request->student_id, $request->mentor_id)
            && in_array($request->status, [
                MentorRequestStatus::Submitted,
                MentorRequestStatus::NeedMoreInfo,
                MentorRequestStatus::UpdatedByStudent,
            ], true);
    }

    /**
     * Either participant can mark a request as completed.
     */
    public function complete(User $user, MentorRequest $request): bool
    {
        return $user->isActive()
            && $request->isParticipant($user)
            && ! $this->isBlockedBetween($request->student_id, $request->mentor_id)
            && $request->status === MentorRequestStatus::Accepted;
    }

    /**
     * Either participant can report a mentor request.
     */
    public function report(User $user, MentorRequest $request): bool
    {
        return $user->isActive()
            && $request->isParticipant($user)
            && ! $this->isBlockedBetween($request->student_id, $request->mentor_id);
    }

    /**
     * Only the student of this request can update it when it needs more info.
     */
    public function update(User $user, MentorRequest $request): bool
    {
        return $user->isActive()
            && $request->student_id === $user->id
            && ! $this->isBlockedBetween($request->student_id, $request->mentor_id)
            && $request->status === MentorRequestStatus::NeedMoreInfo;
    }

    /**
     * Check if a block exists between student and mentor.
     */
    private function isBlockedBetween(int $userOneId, int $userTwoId): bool
    {
        return BlockedUser::where(function ($q) use ($userOneId, $userTwoId) {
            $q->where('blocker_id', $userOneId)->where('blocked_id', $userTwoId);
        })->orWhere(function ($q) use ($userOneId, $userTwoId) {
            $q->where('blocker_id', $userTwoId)->where('blocked_id', $userOneId);
        })->exists();
    }
}
