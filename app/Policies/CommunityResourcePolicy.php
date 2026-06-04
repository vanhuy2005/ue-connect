<?php

namespace App\Policies;

use App\Models\Community;
use App\Models\CommunityMember;
use App\Models\CommunityResource;
use App\Models\PermissionGrant;
use App\Models\User;

class CommunityResourcePolicy
{
    /**
     * Whether a user can submit resources to a community.
     * Must be an active member and community must be active.
     */
    public function create(User $user, Community $community): bool
    {
        if (! $community->isActive()) {
            return false;
        }

        return CommunityMember::where('community_id', $community->id)
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->exists();
    }

    /**
     * Whether a user can approve/reject a community resource.
     * Must have manage_community_resources scoped permission, be owner, or admin.
     */
    public function review(User $user, CommunityResource $resource): bool
    {
        $community = $resource->community;

        if (! $community) {
            return false;
        }

        if ($user->hasRole('admin') || $user->can('manage_communities')) {
            return true;
        }

        if ($community->isOwnedBy($user)) {
            return true;
        }

        return PermissionGrant::where('user_id', $user->id)
            ->whereIn('permission_key', ['manage_community_resources', 'manage_community'])
            ->where('scope_type', 'community')
            ->where('scope_id', $community->id)
            ->where('status', 'active')
            ->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()))
            ->exists();
    }
}
