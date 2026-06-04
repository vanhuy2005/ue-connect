<?php

namespace App\Actions\Community;

use App\Enums\CommunityMemberRole;
use App\Enums\CommunityMemberStatus;
use App\Models\Community;
use App\Models\CommunityMember;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RemoveCommunityMemberAction
{
    public function __construct(private readonly AuditService $audit) {}

    public function execute(User $actor, Community $community, User $targetUser, string $reason): void
    {
        if (empty(trim($reason))) {
            throw ValidationException::withMessages([
                'reason' => 'Vui lòng cung cấp lý do gỡ thành viên.',
            ]);
        }

        $member = CommunityMember::where('community_id', $community->id)
            ->where('user_id', $targetUser->id)
            ->where('status', CommunityMemberStatus::Active->value)
            ->first();

        if (! $member) {
            throw ValidationException::withMessages([
                'user' => 'Người dùng này không phải là thành viên đang hoạt động.',
            ]);
        }

        // Cannot remove the owner without global admin power
        if ($member->role === CommunityMemberRole::Owner && ! $actor->hasRole('admin')) {
            throw ValidationException::withMessages([
                'user' => 'Chỉ admin mới có thể gỡ chủ sở hữu cộng đồng.',
            ]);
        }

        DB::transaction(function () use ($actor, $community, $member, $reason) {
            $before = $member->toArray();

            $member->update([
                'status' => CommunityMemberStatus::Removed->value,
                'removed_at' => now(),
                'removed_by' => $actor->id,
                'remove_reason' => $reason,
            ]);

            if ($community->members_count > 0) {
                $community->decrement('members_count');
            }

            $this->audit->log([
                'action' => 'remove_community_member',
                'target_type' => 'community_member',
                'target_id' => $member->id,
                'context_type' => 'community',
                'context_id' => $community->id,
                'before_values' => $before,
                'after_values' => $member->fresh()?->toArray(),
                'reason' => $reason,
            ]);
        });
    }
}
