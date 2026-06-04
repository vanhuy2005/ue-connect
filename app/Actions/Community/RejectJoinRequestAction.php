<?php

namespace App\Actions\Community;

use App\Enums\CommunityJoinRequestStatus;
use App\Models\CommunityJoinRequest;
use App\Models\User;
use App\Notifications\Community\CommunityJoinRejectedNotification;
use App\Services\AuditService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RejectJoinRequestAction
{
    public function __construct(private readonly AuditService $audit) {}

    public function execute(
        User $actor,
        CommunityJoinRequest $joinRequest,
        string $reason
    ): void {
        if (! $joinRequest->isPending()) {
            throw ValidationException::withMessages([
                'join_request' => 'Yêu cầu tham gia này không còn ở trạng thái chờ.',
            ]);
        }

        if (empty(trim($reason))) {
            throw ValidationException::withMessages([
                'reason' => 'Vui lòng cung cấp lý do từ chối.',
            ]);
        }

        DB::transaction(function () use ($actor, $joinRequest, $reason) {
            $before = $joinRequest->toArray();

            $joinRequest->update([
                'status' => CommunityJoinRequestStatus::Rejected->value,
                'reviewed_by' => $actor->id,
                'review_reason' => $reason,
                'reviewed_at' => now(),
            ]);

            // Notify user with a safe-copy reason (reuse the same reason for now)
            $joinRequest->user?->notify(
                new CommunityJoinRejectedNotification($joinRequest->community, $reason)
            );

            $this->audit->log([
                'action' => 'reject_community_join_request',
                'target_type' => 'community_join_request',
                'target_id' => $joinRequest->id,
                'context_type' => 'community',
                'context_id' => $joinRequest->community_id,
                'before_values' => $before,
                'after_values' => $joinRequest->fresh()?->toArray(),
                'reason' => $reason,
            ]);
        });
    }
}
