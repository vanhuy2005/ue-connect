<?php

namespace App\Actions\Community;

use App\Enums\CommunityMemberRole;
use App\Enums\CommunityMemberStatus;
use App\Models\Community;
use App\Models\CommunityMember;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class LeaveCommunityAction
{
    public function execute(User $user, Community $community): void
    {
        $member = CommunityMember::where('community_id', $community->id)
            ->where('user_id', $user->id)
            ->where('status', CommunityMemberStatus::Active->value)
            ->first();

        if (! $member) {
            throw ValidationException::withMessages([
                'community' => 'Bạn không phải là thành viên của cộng đồng này.',
            ]);
        }

        // Owners cannot leave without transferring ownership first
        if ($member->role === CommunityMemberRole::Owner) {
            throw ValidationException::withMessages([
                'community' => 'Chủ sở hữu không thể rời cộng đồng. Vui lòng chuyển quyền sở hữu cho thành viên khác trước.',
            ]);
        }

        DB::transaction(function () use ($member, $community) {
            $member->update([
                'status' => CommunityMemberStatus::Left->value,
                'left_at' => now(),
            ]);

            if ($community->members_count > 0) {
                $community->decrement('members_count');
            }
        });
    }
}
