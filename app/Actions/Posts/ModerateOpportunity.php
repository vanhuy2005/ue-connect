<?php

namespace App\Actions\Posts;

use App\Enums\ModerationStatus;
use App\Events\Feed\PostCreated;
use App\Models\Post;
use App\Models\User;
use App\Notifications\OpportunityReviewedNotification;
use App\Support\Navigation\UserNavigationMetrics;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Gate;

class ModerateOpportunity
{
    /**
     * Approve an opportunity post.
     *
     * @throws AuthorizationException
     */
    public function approve(User $admin, Post $post, ?string $note = null): void
    {
        Gate::forUser($admin)->authorize('moderate', $post);

        $post->update(['moderation_status' => ModerationStatus::APPROVED]);

        PostCreated::dispatch($post);

        $post->user->notify(new OpportunityReviewedNotification($post, 'approved', $note));

        app(UserNavigationMetrics::class)->forgetForUser($post->user);
    }

    /**
     * Reject an opportunity post.
     *
     * @throws AuthorizationException
     */
    public function reject(User $admin, Post $post, string $reason): void
    {
        Gate::forUser($admin)->authorize('moderate', $post);

        $post->update(['moderation_status' => ModerationStatus::REJECTED]);

        $post->user->notify(new OpportunityReviewedNotification($post, 'rejected', $reason));

        app(UserNavigationMetrics::class)->forgetForUser($post->user);
    }

    /**
     * Mark an opportunity as expired.
     *
     * @throws AuthorizationException
     */
    public function expire(User $user, Post $post): void
    {
        Gate::forUser($user)->authorize('expire', $post);

        $post->update([
            'moderation_status' => ModerationStatus::EXPIRED,
        ]);

        if ($post->opportunity) {
            $post->opportunity->update(['is_expired' => true]);
        }
    }
}
