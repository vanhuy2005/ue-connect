<?php

namespace App\Policies;

use App\Models\CommunityJoinRequest;
use App\Models\PermissionGrant;
use App\Models\User;

class CommunityJoinRequestPolicy
{
    /**
     * Whether user can review (approve/reject) a join request.
     * Must have manage_community_members scoped permission, be the owner, or be admin.
     */
    public function review(User $user, CommunityJoinRequest $joinRequest): bool
    {
        $community = $joinRequest->community;

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
            ->whereIn('permission_key', ['manage_community_members', 'manage_community', 'manage_communities'])
            ->where('scope_type', 'community')
            ->where('scope_id', $community->id)
            ->where('status', 'active')
            ->where(fn ($q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
            ->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()))
            ->exists();
    }
}
