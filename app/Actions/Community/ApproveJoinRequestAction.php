<?php

namespace App\Actions\Community;

use App\Enums\CommunityJoinRequestStatus;
use App\Enums\CommunityMemberRole;
use App\Enums\CommunityMemberStatus;
use App\Models\CommunityJoinRequest;
use App\Models\CommunityMember;
use App\Models\User;
use App\Notifications\Community\CommunityJoinApprovedNotification;
use App\Services\AuditService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ApproveJoinRequestAction
{
    public function __construct(private readonly AuditService $audit) {}

    public function execute(
        User $actor,
        CommunityJoinRequest $joinRequest,
        ?string $reason = null
    ): CommunityMember {
        if (! $joinRequest->isPending()) {
            throw ValidationException::withMessages([
                'join_request' => 'Yêu cầu tham gia này không còn ở trạng thái chờ.',
            ]);
        }

        $community = $joinRequest->community;

        if (! $community || ! $community->isActive()) {
            throw ValidationException::withMessages([
                'join_request' => 'Cộng đồng không còn hoạt động.',
            ]);
        }

        return DB::transaction(function () use ($actor, $joinRequest, $community, $reason) {
            $before = $joinRequest->toArray();

            // Update request status
            $joinRequest->update([
                'status' => CommunityJoinRequestStatus::Approved->value,
                'reviewed_by' => $actor->id,
                'review_reason' => $reason,
                'reviewed_at' => now(),
            ]);

            // Create or reactivate membership
            $member = CommunityMember::updateOrCreate(
                ['community_id' => $community->id, 'user_id' => $joinRequest->user_id],
                [
                    'role' => CommunityMemberRole::Member->value,
                    'status' => CommunityMemberStatus::Active->value,
                    'joined_at' => now(),
                    'removed_at' => null,
                    'removed_by' => null,
                    'remove_reason' => null,
                    'left_at' => null,
                ]
            );

            $community->increment('members_count');

            // Notify the user
            $joinRequest->user?->notify(
                new CommunityJoinApprovedNotification($community)
            );

            $this->audit->log([
                'action' => 'approve_community_join_request',
                'target_type' => 'community_join_request',
                'target_id' => $joinRequest->id,
                'context_type' => 'community',
                'context_id' => $community->id,
                'before_values' => $before,
                'after_values' => $joinRequest->fresh()?->toArray(),
                'reason' => $reason,
            ]);

            return $member;
        });
    }
}
