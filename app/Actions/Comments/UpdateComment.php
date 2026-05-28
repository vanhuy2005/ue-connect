<?php

namespace App\Actions\Comments;

use App\Enums\CommentStatus;
use App\Models\Comment;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class UpdateComment
{
    /**
     * Update an existing comment.
     *
     * @param  array{body: string}  $data
     *
     * @throws AuthorizationException
     * @throws ValidationException
     */
    public function execute(User $user, Comment $comment, array $data): Comment
    {
        // Enforce user active status
        if (! $user->isActive()) {
            throw new AuthorizationException('Tài khoản không hoạt động.');
        }

        // Authorize comment updating via policy
        Gate::forUser($user)->authorize('update', $comment);

        // Validate body
        Validator::make($data, [
            'body' => 'required|string|max:1000',
        ])->validate();

        // Update comment attributes
        $comment->body = $data['body'];

        if (in_array($comment->status, [CommentStatus::PUBLISHED, CommentStatus::EDITED])) {
            $comment->status = CommentStatus::EDITED;
            $comment->edited_at = now();
        }

        $comment->save();

        return $comment;
    }
}
