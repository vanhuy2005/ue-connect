<?php

namespace App\Policies;

use App\Enums\ConnectionStatus;
use App\Enums\PostStatus;
use App\Enums\PostVisibility;
use App\Models\Connection;
use App\Models\Post;
use App\Models\User;

class PostPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->isActive();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Post $post): bool
    {
        if (! $user->isActive()) {
            return false;
        }

        if ($post->trashed()) {
            return false;
        }

        // Owner can always view their own post
        if ($post->user_id === $user->id) {
            return true;
        }

        // Normal users cannot view hidden, deleted, or moderated posts
        if (! in_array($post->status, [PostStatus::PUBLISHED, PostStatus::EDITED])) {
            return false;
        }

        // Check visibility settings
        if ($post->visibility === PostVisibility::PRIVATE) {
            return false;
        }

        if ($post->visibility === PostVisibility::CONNECTIONS_ONLY) {
            $userOneId = min($user->id, $post->user_id);
            $userTwoId = max($user->id, $post->user_id);

            return Connection::where('user_one_id', $userOneId)
                ->where('user_two_id', $userTwoId)
                ->where('status', ConnectionStatus::ACTIVE)
                ->exists();
        }

        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->isActive();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Post $post): bool
    {
        return $user->isActive()
            && $post->user_id === $user->id
            && in_array($post->status, [PostStatus::PUBLISHED, PostStatus::EDITED])
            && ! $post->trashed();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Post $post): bool
    {
        if (! $user->isActive()) {
            return false;
        }

        // Owner can delete their own post
        if ($post->user_id === $user->id) {
            return true;
        }

        return $user->can('moderate_content');
    }

    /**
     * Determine whether the user can report the model.
     */
    public function report(User $user, Post $post): bool
    {
        // Reporter must be able to view the post (covers: active, not trashed,
        // published/edited status, visibility rules) and cannot report their own post.
        return $this->view($user, $post)
            && $post->user_id !== $user->id
            && in_array($post->status, [PostStatus::PUBLISHED, PostStatus::EDITED])
            && ! $post->trashed();
    }

    /**
     * Determine whether the user can share the model.
     */
    public function share(User $user, Post $post): bool
    {
        return $this->view($user, $post)
            && in_array($post->status, [PostStatus::PUBLISHED, PostStatus::EDITED])
            && ! $post->trashed();
    }
}
