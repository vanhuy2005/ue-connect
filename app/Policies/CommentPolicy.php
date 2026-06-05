<?php

namespace App\Policies;

use App\Enums\CommentStatus;
use App\Models\Comment;
use App\Models\User;

class CommentPolicy
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
    public function view(User $user, Comment $comment): bool
    {
        if (! $user->isActive()) {
            return false;
        }

        // Owner can always view their own comment
        if ($comment->user_id === $user->id) {
            return true;
        }

        // Normal users cannot view hidden, deleted, or moderated comments
        return in_array($comment->status, [CommentStatus::PUBLISHED, CommentStatus::EDITED]);
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
    public function update(User $user, Comment $comment): bool
    {
        return $user->isActive()
            && $comment->user_id === $user->id
            && in_array($comment->status, [CommentStatus::PUBLISHED, CommentStatus::EDITED])
            && ! $comment->trashed();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Comment $comment): bool
    {
        if (! $user->isActive()) {
            return false;
        }

        // Owner can delete their own comment
        if ($comment->user_id === $user->id) {
            return true;
        }

        return $user->can('moderate_content');
    }

    /**
     * Determine whether the user can report the model.
     */
    public function report(User $user, Comment $comment): bool
    {
        // Active verified users can report comments, except their own
        return $user->isActive() && $comment->user_id !== $user->id;
    }
}
