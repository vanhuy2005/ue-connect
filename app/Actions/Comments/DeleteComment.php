<?php

namespace App\Actions\Comments;

use App\Enums\CommentStatus;
use App\Models\Comment;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Gate;

class DeleteComment
{
    /**
     * Delete a comment.
     *
     *
     * @throws AuthorizationException
     */
    public function execute(User $user, Comment $comment): bool
    {
        Gate::forUser($user)->authorize('delete', $comment);

        // Determine who deleted the comment
        if ($comment->user_id === $user->id) {
            $comment->status = CommentStatus::DELETED_BY_OWNER;
        } else {
            $comment->status = CommentStatus::DELETED_BY_MODERATION;
        }

        $comment->save();

        return $comment->delete();
    }
}
