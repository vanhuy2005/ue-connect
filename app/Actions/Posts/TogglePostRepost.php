<?php

namespace App\Actions\Posts;

use App\Models\Post;
use App\Models\PostRepost;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;

class TogglePostRepost
{
    /**
     * Toggle a repost and return true when the post is now reposted.
     *
     * @throws AuthorizationException
     */
    public function execute(User $user, Post $post): bool
    {
        $existingRepost = PostRepost::where('post_id', $post->id)
            ->where('user_id', $user->id)
            ->first();

        if ($existingRepost) {
            app(DeletePostRepost::class)->execute($user, $post);

            return false;
        }

        app(CreatePostRepost::class)->execute($user, $post);

        return true;
    }
}
