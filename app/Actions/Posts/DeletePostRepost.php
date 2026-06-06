<?php

namespace App\Actions\Posts;

use App\Models\Post;
use App\Models\PostRepost;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;

class DeletePostRepost
{
    /**
     * Delete a repost for the given user and post.
     *
     * @throws AuthorizationException
     */
    public function execute(User $user, Post $post): void
    {
        if (! $user->isActive()) {
            throw new AuthorizationException('Tài khoản không hoạt động.');
        }

        PostRepost::where('post_id', $post->id)
            ->where('user_id', $user->id)
            ->delete();
    }
}
