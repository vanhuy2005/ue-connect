<?php

namespace App\Actions\Community;

use App\Enums\CommunityMemberRole;
use App\Models\Community;
use App\Models\CommunityMember;
use App\Models\PermissionGrant;
use App\Models\User;
use App\Notifications\Community\ClubManagerRevokedNotification;
use App\Services\AuditService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RevokeClubManagerAction
{
    public function __construct(private readonly AuditService $audit) {}

    public function execute(User $actor, Community $community, User $targetUser, string $reason): void
    {
        if (empty(trim($reason))) {
            throw ValidationException::withMessages([
                'reason' => 'Vui lòng cung cấp lý do thu hồi quyền quản lý.',
            ]);
        }

        $grant = PermissionGrant::where('user_id', $targetUser->id)
            ->where('permission_key', 'manage_community')
            ->where('scope_type', 'community')
            ->where('scope_id', $community->id)
            ->where('status', 'active')
            ->first();

        if (! $grant) {
            throw ValidationException::withMessages([
                'user' => 'Người dùng này không có quyền quản lý cộng đồng này.',
            ]);
        }

        DB::transaction(function () use ($actor, $community, $targetUser, $grant, $reason) {
            $before = $grant->toArray();

            $grant->update([
                'status' => 'revoked',
                'revoked_at' => now(),
                'revoked_by' => $actor->id,
                'reason' => $reason,
            ]);

            // Downgrade member role back to member
            CommunityMember::where('community_id', $community->id)
                ->where('user_id', $targetUser->id)
                ->where('role', CommunityMemberRole::Manager->value)
                ->update([
                    'role' => CommunityMemberRole::Member->value,
                    'role_label' => null,
                ]);

            $targetUser->notify(new ClubManagerRevokedNotification($community));

            $this->audit->log([
                'action' => 'revoke_club_manager',
                'target_type' => 'permission_grant',
                'target_id' => $grant->id,
                'context_type' => 'community',
                'context_id' => $community->id,
                'before_values' => $before,
                'after_values' => $grant->fresh()?->toArray(),
                'reason' => $reason,
            ]);
        });
    }
}
