<?php

namespace App\Actions\Community;

use App\Enums\CommunityMemberRole;
use App\Enums\CommunityMemberStatus;
use App\Models\Community;
use App\Models\CommunityMember;
use App\Models\PermissionGrant;
use App\Models\User;
use App\Notifications\Community\ClubManagerGrantedNotification;
use App\Services\AuditService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class GrantClubManagerAction
{
    public function __construct(private readonly AuditService $audit) {}

    public function execute(User $actor, Community $community, User $targetUser, string $reason): PermissionGrant
    {
        if (empty(trim($reason))) {
            throw ValidationException::withMessages([
                'reason' => 'Vui lòng cung cấp lý do cấp quyền quản lý.',
            ]);
        }

        // Target must be an active member
        $member = CommunityMember::where('community_id', $community->id)
            ->where('user_id', $targetUser->id)
            ->where('status', CommunityMemberStatus::Active->value)
            ->first();

        if (! $member) {
            throw ValidationException::withMessages([
                'user' => 'Người dùng này phải là thành viên đang hoạt động của cộng đồng.',
            ]);
        }

        // Cannot grant owner role via this action
        if ($member->role === CommunityMemberRole::Owner) {
            throw ValidationException::withMessages([
                'user' => 'Người dùng này đã là chủ sở hữu cộng đồng.',
            ]);
        }

        return DB::transaction(function () use ($actor, $community, $targetUser, $member, $reason) {
            // Revoke any existing active manager permission for this scope first
            PermissionGrant::where('user_id', $targetUser->id)
                ->where('permission_key', 'manage_community')
                ->where('scope_type', 'community')
                ->where('scope_id', $community->id)
                ->where('status', 'active')
                ->update(['status' => 'revoked', 'revoked_at' => now(), 'revoked_by' => $actor->id]);

            $grant = PermissionGrant::create([
                'user_id' => $targetUser->id,
                'permission_key' => 'manage_community',
                'scope_type' => 'community',
                'scope_id' => $community->id,
                'granted_by' => $actor->id,
                'reason' => $reason,
                'starts_at' => now(),
                'status' => 'active',
            ]);

            // Update the member's role label
            $member->update([
                'role' => CommunityMemberRole::Manager->value,
                'role_label' => 'Quản lý cộng đồng',
            ]);

            $targetUser->notify(new ClubManagerGrantedNotification($community));

            $this->audit->log([
                'action' => 'grant_club_manager',
                'target_type' => 'permission_grant',
                'target_id' => $grant->id,
                'context_type' => 'community',
                'context_id' => $community->id,
                'after_values' => $grant->toArray(),
                'reason' => $reason,
            ]);

            return $grant;
        });
    }
}
