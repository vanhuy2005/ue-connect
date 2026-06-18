<?php

namespace App\Policies;

use App\Models\Community;
use App\Models\CommunityEvent;
use App\Models\CommunityMember;
use App\Models\PermissionGrant;
use App\Models\User;

class CommunityEventPolicy
{
    /**
     * Members can view published events in their community.
     */
    public function viewAny(User $user, Community $community): bool
    {
        if ($user->hasRole('admin') || $user->can('manage_communities')) {
            return true;
        }

        return CommunityMember::where('community_id', $community->id)
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->exists();
    }

    /**
     * Community owners/managers or admins can create events.
     */
    public function create(User $user, Community $community): bool
    {
        if ($user->hasRole('admin') || $user->can('manage_communities')) {
            return true;
        }

        if ($community->isOwnedBy($user)) {
            return true;
        }

        return PermissionGrant::where('user_id', $user->id)
            ->whereIn('permission_key', ['manage_community', 'manage_community_events', 'manage_communities'])
            ->where('scope_type', 'community')
            ->where('scope_id', $community->id)
            ->where('status', 'active')
            ->where(fn ($q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
            ->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()))
            ->exists();
    }

    /**
     * Creator, owner, scoped manager, or admin can update an event.
     */
    public function update(User $user, CommunityEvent $event): bool
    {
        if ($user->hasRole('admin') || $user->can('manage_communities')) {
            return true;
        }

        if ((int) $event->created_by === (int) $user->id) {
            return true;
        }

        $community = $event->community;

        if ($community && $community->isOwnedBy($user)) {
            return true;
        }

        return $community && PermissionGrant::where('user_id', $user->id)
            ->whereIn('permission_key', ['manage_community', 'manage_community_events', 'manage_communities'])
            ->where('scope_type', 'community')
            ->where('scope_id', $community->id)
            ->where('status', 'active')
            ->where(fn ($q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
            ->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()))
            ->exists();
    }
}
