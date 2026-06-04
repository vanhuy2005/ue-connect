<?php

namespace App\Actions\Community;

use App\Enums\CommunityJoinPolicy;
use App\Enums\CommunityJoinRequestStatus;
use App\Enums\CommunityMemberRole;
use App\Enums\CommunityMemberStatus;
use App\Models\Community;
use App\Models\CommunityJoinRequest;
use App\Models\CommunityMember;
use App\Models\User;
use App\Notifications\Community\CommunityJoinRequestReceivedNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RequestJoinCommunityAction
{
    /**
     * Execute the join request flow.
     *
     * @param  array<string, mixed>  $data
     */
    public function execute(
        User $user,
        Community $community,
        array $data = []
    ): CommunityMember|CommunityJoinRequest {
        // Guard: community must be active
        if (! $community->isActive()) {
            throw ValidationException::withMessages([
                'community' => 'Cộng đồng này hiện không hoạt động.',
            ]);
        }

        // Guard: join policy allows
        if (! $community->allowsJoin()) {
            throw ValidationException::withMessages([
                'community' => 'Cộng đồng này không nhận thành viên mới.',
            ]);
        }

        // Guard: no duplicate active/pending/banned membership
        $existing = CommunityMember::where('community_id', $community->id)
            ->where('user_id', $user->id)
            ->first();

        if ($existing) {
            if ($existing->status === CommunityMemberStatus::Active) {
                throw ValidationException::withMessages([
                    'community' => 'Bạn đã là thành viên của cộng đồng này.',
                ]);
            }

            if ($existing->status === CommunityMemberStatus::BannedFromCommunity) {
                throw ValidationException::withMessages([
                    'community' => 'Bạn đã bị cấm khỏi cộng đồng này.',
                ]);
            }
        }

        // Guard: no duplicate pending join request
        $hasPendingRequest = CommunityJoinRequest::where('community_id', $community->id)
            ->where('user_id', $user->id)
            ->where('status', CommunityJoinRequestStatus::Pending->value)
            ->whereNull('deleted_at')
            ->exists();

        if ($hasPendingRequest) {
            throw ValidationException::withMessages([
                'community' => 'Bạn đã có yêu cầu tham gia đang chờ xét duyệt.',
            ]);
        }

        return DB::transaction(function () use ($user, $community, $data) {
            if ($community->join_policy === CommunityJoinPolicy::Open) {
                return $this->joinDirectly($user, $community);
            }

            return $this->submitJoinRequest($user, $community, $data);
        });
    }

    private function joinDirectly(User $user, Community $community): CommunityMember
    {
        $member = CommunityMember::create([
            'community_id' => $community->id,
            'user_id' => $user->id,
            'role' => CommunityMemberRole::Member->value,
            'status' => CommunityMemberStatus::Active->value,
            'joined_at' => now(),
        ]);

        $community->increment('members_count');

        return $member;
    }

    private function submitJoinRequest(
        User $user,
        Community $community,
        array $data
    ): CommunityJoinRequest {
        $joinRequest = CommunityJoinRequest::create([
            'community_id' => $community->id,
            'user_id' => $user->id,
            'join_reason' => $data['join_reason'] ?? null,
            'status' => CommunityJoinRequestStatus::Pending->value,
        ]);

        // Notify owner and scoped managers
        $owner = $community->owner;

        if ($owner) {
            $owner->notify(new CommunityJoinRequestReceivedNotification($joinRequest));
        }

        return $joinRequest;
    }
}
