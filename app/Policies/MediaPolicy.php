<?php

namespace App\Policies;

use App\Models\Media;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Support\Facades\Gate;

class MediaPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the media.
     */
    public function view(User $user, Media $media): bool
    {
        // 1. Admin/Moderator override
        if ($user->can('review_verification') || $user->can('moderate_content')) {
            return true;
        }

        // 2. Unattached/temporary media is visible only to the owner
        if ($media->status === 'temporary' || empty($media->mediable_type)) {
            return $media->user_id === $user->id;
        }

        // 3. Delegate to polymorphic parent policy based on collection
        $parent = $media->mediable;

        if (! $parent) {
            // If parent record was soft deleted, check if owner
            return $media->user_id === $user->id;
        }

        switch ($media->collection) {
            case 'avatar':
            case 'profile_cover':
                // Check if profile is visible
                return Gate::forUser($user)->allows('viewProfile', $parent);

            case 'post_image':
                // Check if post is visible
                return Gate::forUser($user)->allows('view', $parent);

            case 'message_attachment':
                // Check if message/conversation is accessible
                return Gate::forUser($user)->allows('view', $parent);

            case 'verification_evidence':
                // Visible only to owner (or admin handled above)
                return $media->user_id === $user->id;

            case 'report_evidence':
                // Visible only to owner reporter (or moderator handled above)
                return $media->user_id === $user->id;
        }

        // Default fallback to owner access
        return $media->user_id === $user->id;
    }

    /**
     * Determine whether the user can create media.
     */
    public function create(User $user): bool
    {
        return $user->isActive();
    }

    /**
     * Determine whether the user can delete the media.
     */
    public function delete(User $user, Media $media): bool
    {
        // Owner can delete own temporary media or own avatar/covers
        if ($media->user_id === $user->id) {
            return true;
        }

        // Moderators/Admins can delete reported or non-compliant media
        if ($user->can('moderate_content')) {
            return true;
        }

        return false;
    }
}
