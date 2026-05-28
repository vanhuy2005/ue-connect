<?php

namespace App\Actions\Posts;

use App\Models\Post;
use App\Models\PostHide;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Gate;

class HidePostFromFeed
{
    /**
     * Hide a post from a user's feed.
     *
     * @throws AuthorizationException
     */
    public function execute(User $user, Post $post): PostHide
    {
        if (! $user->isActive()) {
            throw new AuthorizationException('Tài khoản của bạn đã bị khóa hoặc chưa kích hoạt.');
        }

        Gate::forUser($user)->authorize('view', $post);

        return PostHide::firstOrCreate([
            'post_id' => $post->id,
            'user_id' => $user->id,
        ]);
    }
}
