<?php

namespace App\Actions\Mentor;

use App\Enums\MentorAccessStatus;
use App\Models\MentorAccessRequest;
use App\Models\User;
use App\Notifications\Mentor\MentorAccessNeedMoreInfoNotification;
use App\Notifications\Mentor\MentorAccessRejectedNotification;
use App\Services\AuditService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Gate;

class ReviewMentorAccessAction
{
    public function __construct(private readonly AuditService $audit) {}

    /**
     * Admin reviews a mentor access request.
     *
     * @param  array{action: string, reason?: ?string, admin_notes?: ?string, more_info_question?: ?string}  $data
     *
     * @throws AuthorizationException
     * @throws \Exception
     */
    public function execute(User $admin, MentorAccessRequest $request, array $data): MentorAccessRequest
    {
        Gate::forUser($admin)->authorize('review', $request);

        $before = $request->toArray();
        $action = $data['action'];

        match ($action) {
            'under_review' => $request->status = MentorAccessStatus::UnderReview,
            'reject' => $request->status = MentorAccessStatus::Rejected,
            'need_more_info' => $request->status = MentorAccessStatus::NeedMoreInfo,
            default => throw new \Exception("Unknown review action: {$action}"),
        };

        $request->reviewed_by = $admin->id;
        $request->reviewed_at = now();
        $request->review_reason = $data['reason'] ?? null;

        if (isset($data['admin_notes'])) {
            $request->admin_notes = $data['admin_notes'];
        }

        $request->save();

        // Notify applicant
        if ($request->status === MentorAccessStatus::Rejected) {
            $request->user->notify(new MentorAccessRejectedNotification($request));
        } elseif ($request->status === MentorAccessStatus::NeedMoreInfo) {
            $request->user->notify(new MentorAccessNeedMoreInfoNotification($request));
        }

        $this->audit->log([
            'action' => "mentor_access_{$action}",
            'target_type' => 'mentor_access_request',
            'target_id' => $request->id,
            'before_values' => $before,
            'after_values' => $request->toArray(),
            'reason' => $data['reason'] ?? null,
        ]);

        return $request->fresh();
    }
}
