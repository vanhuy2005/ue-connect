<?php

namespace App\Policies;

use App\Enums\AccountStatus;
use App\Enums\CommunityMemberRole;
use App\Enums\CommunityMemberStatus;
use App\Enums\CommunityVisibility;
use App\Models\Community;
use App\Models\CommunityMember;
use App\Models\PermissionGrant;
use App\Models\User;

class CommunityPolicy
{
    /**
     * Any verified active user can view the community list.
     */
    public function viewAny(User $user): bool
    {
        return $this->isEligibleUser($user);
    }

    /**
     * Whether a user can view a community detail page.
     * Visibility rules:
     *  - public/restricted: all verified active users
     *  - private: active members, admins, scoped managers
     *  - hidden: admins only
     *  - official_only: admins only
     *  - suspended: members + admins (limited view)
     */
    public function view(User $user, Community $community): bool
    {
        if (! $this->isEligibleUser($user)) {
            return false;
        }

        if ($user->hasRole('admin') || $user->can('manage_communities') || $community->isOwnedBy($user)) {
            return true;
        }

        return match ($community->visibility) {
            CommunityVisibility::Public,
            CommunityVisibility::Restricted => true,
            CommunityVisibility::Private => $this->isActiveMemberOrStaff($user, $community),
            CommunityVisibility::Hidden,
            CommunityVisibility::OfficialOnly => false,
            default => false,
        };
    }

    /**
     * Whether a user can attempt to join (or request to join) a community.
     */
    public function join(User $user, Community $community): bool
    {
        if (! $this->isEligibleUser($user)) {
            return false;
        }

        if (! $community->isActive()) {
            return false;
        }

        if ($community->isOwnedBy($user)) {
            return false;
        }

        // Cannot join if suspended/banned/already active member
        $existing = CommunityMember::where('community_id', $community->id)
            ->where('user_id', $user->id)
            ->first();

        if ($existing) {
            if (in_array($existing->status, [
                CommunityMemberStatus::Active,
                CommunityMemberStatus::Pending,
                CommunityMemberStatus::BannedFromCommunity,
            ])) {
                return false;
            }
        }

        return $community->allowsJoin();
    }

    /**
     * Whether a user can leave the community.
     */
    public function leave(User $user, Community $community): bool
    {
        if (! $this->isEligibleUser($user)) {
            return false;
        }

        $member = CommunityMember::where('community_id', $community->id)
            ->where('user_id', $user->id)
            ->where('status', CommunityMemberStatus::Active->value)
            ->first();

        if (! $member) {
            return false;
        }

        // Owners cannot just leave without transferring ownership
        return $member->role !== CommunityMemberRole::Owner;
    }

    /**
     * Whether user can update community settings. Requires scoped permission or admin.
     */
    public function update(User $user, Community $community): bool
    {
        if (! $this->isEligibleUser($user)) {
            return false;
        }

        return $community->isOwnedBy($user)
            || $this->hasScopedPermission($user, $community, 'manage_community')
            || $user->can('manage_communities');
    }

    /**
     * Suspend a community. Requires manage_communities or moderate_content.
     */
    public function suspend(User $user, Community $community): bool
    {
        if (! $this->isEligibleUser($user)) {
            return false;
        }

        return $user->can('manage_communities')
            || $user->can('moderate_content');
    }

    /**
     * Reactivate a community. Requires manage_communities.
     */
    public function reactivate(User $user, Community $community): bool
    {
        return $this->isEligibleUser($user) && $user->can('manage_communities');
    }

    /**
     * Archive a community. Requires manage_communities.
     */
    public function archive(User $user, Community $community): bool
    {
        return $this->isEligibleUser($user) && $user->can('manage_communities');
    }

    /**
     * Manage community members (add/remove/approve join requests).
     * Requires manage_community_members scoped to this community, or admin.
     */
    public function manageMember(User $user, Community $community): bool
    {
        if (! $this->isEligibleUser($user)) {
            return false;
        }

        if ($user->can('manage_communities')) {
            return true;
        }

        // Owner always can
        if ($community->isOwnedBy($user)) {
            return true;
        }

        return $this->hasScopedPermission($user, $community, 'manage_community_members')
            || $this->hasScopedPermission($user, $community, 'manage_community');
    }

    /**
     * Create a post in this community.
     * Must be an active member. Community must be active.
     * Muted/restricted/banned members cannot post.
     */
    public function createPost(User $user, Community $community): bool
    {
        if (! $this->isEligibleUser($user)) {
            return false;
        }

        if (! $community->isActive()) {
            return false;
        }

        if ($community->isOwnedBy($user)) {
            return true;
        }

        $member = CommunityMember::where('community_id', $community->id)
            ->where('user_id', $user->id)
            ->first();

        return $member?->canPost() ?? false;
    }

    /**
     * Moderate community content.
     */
    public function moderateContent(User $user, Community $community): bool
    {
        if (! $this->isEligibleUser($user)) {
            return false;
        }

        if ($user->can('moderate_content')) {
            return true;
        }

        return $this->hasScopedPermission($user, $community, 'moderate_community_posts')
            || $this->hasScopedPermission($user, $community, 'manage_community');
    }

    /**
     * Grant/revoke club manager permissions.
     */
    public function grantManager(User $user, Community $community): bool
    {
        return $this->isEligibleUser($user)
            && ($user->can('manage_communities')
            || $user->can('manage_permissions'));
    }

    /**
     * Send chat messages in community channel.
     */
    public function sendChat(User $user, Community $community): bool
    {
        if (! $this->isEligibleUser($user)) {
            return false;
        }

        if ($community->isSuspended() || $community->isArchived()) {
            return false;
        }

        if ($community->isOwnedBy($user)) {
            return true;
        }

        $member = CommunityMember::where('community_id', $community->id)
            ->where('user_id', $user->id)
            ->first();

        return $member?->canSendChat() ?? false;
    }

    /**
     * View community chat (read-only). Active + muted members can read.
     */
    public function viewChat(User $user, Community $community): bool
    {
        if (! $this->isEligibleUser($user)) {
            return false;
        }

        if ($community->isOwnedBy($user)) {
            return true;
        }

        $member = CommunityMember::where('community_id', $community->id)
            ->where('user_id', $user->id)
            ->first();

        return $member?->status?->canViewChat() ?? false;
    }

    // ─── Private Helpers ─────────────────────────────────────────────────────

    /**
     * Verify the user is active and not suspended/banned.
     */
    private function isEligibleUser(User $user): bool
    {
        return in_array($user->account_status, [
            AccountStatus::ACTIVE,
            AccountStatus::PROFILE_INCOMPLETE,
        ]);
    }

    /**
     * Whether the user is an active member or community staff member.
     */
    private function isActiveMemberOrStaff(User $user, Community $community): bool
    {
        return CommunityMember::where('community_id', $community->id)
            ->where('user_id', $user->id)
            ->where('status', CommunityMemberStatus::Active->value)
            ->exists();
    }

    /**
     * Check for a scoped PermissionGrant on this community.
     */
    private function hasScopedPermission(User $user, Community $community, string $permissionKey): bool
    {
        return PermissionGrant::where('user_id', $user->id)
            ->where('permission_key', $permissionKey)
            ->where('scope_type', 'community')
            ->where('scope_id', $community->id)
            ->where('status', 'active')
            ->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()))
            ->exists();
    }
}
