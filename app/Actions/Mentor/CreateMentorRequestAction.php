<?php

namespace App\Actions\Mentor;

use App\Enums\MentorRequestStatus;
use App\Models\BlockedUser;
use App\Models\MentorProfile;
use App\Models\MentorRequest;
use App\Models\User;
use App\Notifications\Mentor\MentorRequestSubmittedNotification;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Gate;

class CreateMentorRequestAction
{
    /**
     * Student creates a structured mentor request.
     *
     * @param  array{topic: string, goal: string, question: string, urgency: string, context?: ?string, expected_outcome?: ?string}  $data
     *
     * @throws AuthorizationException
     * @throws \Exception
     */
    public function execute(User $student, MentorProfile $mentorProfile, array $data): MentorRequest
    {
        Gate::forUser($student)->authorize('create', MentorRequest::class);

        $mentor = $mentorProfile->user;

        // Guard: cannot request self
        if ($student->id === $mentor->id) {
            throw new \Exception('Bạn không thể tự gửi yêu cầu cố vấn cho chính mình.');
        }

        // Guard: mentor must be active and available
        if (! $mentorProfile->isAvailableForRequests()) {
            throw new \Exception('Mentor này hiện không nhận yêu cầu mới.');
        }

        // Guard: blocked users cannot interact
        $isBlocked = BlockedUser::where(function ($q) use ($student, $mentor) {
            $q->where('blocker_id', $student->id)->where('blocked_id', $mentor->id);
        })->orWhere(function ($q) use ($student, $mentor) {
            $q->where('blocker_id', $mentor->id)->where('blocked_id', $student->id);
        })->exists();

        if ($isBlocked) {
            throw new \Exception('Không thể gửi yêu cầu do trạng thái chặn giữa hai tài khoản.');
        }

        // Guard: duplicate pending request to same mentor
        if (config('mentor.duplicate_pending_block', true)) {
            $hasDuplicatePending = MentorRequest::where('student_id', $student->id)
                ->where('mentor_id', $mentor->id)
                ->whereIn('status', [
                    MentorRequestStatus::Submitted->value,
                    MentorRequestStatus::NeedMoreInfo->value,
                ])
                ->exists();

            if ($hasDuplicatePending) {
                throw new \Exception('Bạn đã có yêu cầu đang chờ phản hồi từ mentor này.');
            }
        }

        // Guard: student daily request limit
        $dailyLimit = config('mentor.student_daily_request_limit', 5);
        $todayCount = MentorRequest::where('student_id', $student->id)
            ->whereDate('created_at', today())
            ->count();

        if ($todayCount >= $dailyLimit) {
            throw new \Exception("Bạn đã đạt giới hạn {$dailyLimit} yêu cầu mentor trong ngày hôm nay.");
        }

        // Guard: student total pending limit
        $pendingLimit = config('mentor.student_pending_limit', 10);
        $pendingCount = MentorRequest::where('student_id', $student->id)
            ->whereIn('status', [
                MentorRequestStatus::Submitted->value,
                MentorRequestStatus::NeedMoreInfo->value,
            ])
            ->count();

        if ($pendingCount >= $pendingLimit) {
            throw new \Exception("Bạn đã có {$pendingLimit} yêu cầu mentor đang chờ phản hồi. Vui lòng chờ hoặc hủy bớt trước khi gửi thêm.");
        }

        $mentorRequest = MentorRequest::create([
            'student_id' => $student->id,
            'mentor_id' => $mentor->id,
            'mentor_profile_id' => $mentorProfile->id,
            'topic' => $data['topic'],
            'goal' => $data['goal'],
            'question' => $data['question'],
            'urgency' => $data['urgency'],
            'context' => $data['context'] ?? null,
            'expected_outcome' => $data['expected_outcome'] ?? null,
            'status' => MentorRequestStatus::Submitted,
        ]);

        // Notify mentor
        $mentor->notify(new MentorRequestSubmittedNotification($mentorRequest));

        // TODO: emit mentor_request_submitted analytics event

        return $mentorRequest;
    }
}
