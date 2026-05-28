<?php

namespace App\Actions\Posts;

use App\Enums\PostStatus;
use App\Models\Post;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Gate;

class DeletePost
{
    /**
     * Delete a post.
     *
     *
     * @throws AuthorizationException
     */
    public function execute(User $user, Post $post): bool
    {
        Gate::forUser($user)->authorize('delete', $post);

        // Determine who deleted the post
        if ($post->user_id === $user->id) {
            $post->status = PostStatus::DELETED_BY_OWNER;
        } else {
            $post->status = PostStatus::DELETED_BY_MODERATION;
        }

        $post->save();

        return $post->delete();
    }
}
