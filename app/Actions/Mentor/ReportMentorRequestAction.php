<?php

namespace App\Actions\Mentor;

use App\Enums\MentorRequestStatus;
use App\Models\BlockedUser;
use App\Models\MentorRequest;
use App\Models\Report;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Gate;

class ReportMentorRequestAction
{
    /**
     * Either participant can report a mentor request.
     *
     * @param  array{reason: string, description?: ?string}  $data
     *
     * @throws AuthorizationException
     */
    public function execute(User $user, MentorRequest $mentorRequest, array $data): Report
    {
        Gate::forUser($user)->authorize('report', $mentorRequest);

        $report = Report::create([
            'reporter_id' => $user->id,
            'target_type' => 'mentor_request',
            'target_id' => $mentorRequest->id,
            'reason' => $data['reason'],
            'description' => $data['description'] ?? null,
            'status' => 'pending',
        ]);

        $blockedUserId = $mentorRequest->student_id === $user->id
            ? $mentorRequest->mentor_id
            : $mentorRequest->student_id;

        BlockedUser::firstOrCreate([
            'blocker_id' => $user->id,
            'blocked_id' => $blockedUserId,
        ], [
            'reason' => 'mentor_request_report',
            'source_type' => 'report',
            'source_id' => $report->id,
        ]);

        // Mark request as reported
        $mentorRequest->update(['status' => MentorRequestStatus::Reported]);

        // TODO: emit mentor_reported analytics event

        return $report;
    }
}
