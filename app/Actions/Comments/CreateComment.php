<?php

namespace App\Actions\Comments;

use App\Enums\CommentStatus;
use App\Enums\PostStatus;
use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class CreateComment
{
    /**
     * Create a comment or one-level reply.
     *
     * @param  array{body: string, parent_id?: int|null}  $data
     *
     * @throws AuthorizationException
     * @throws ValidationException
     */
    public function execute(User $user, Post $post, array $data): Comment
    {
        Validator::make($data, [
            'body' => 'required|string|max:1000',
            'parent_id' => 'nullable|integer',
        ])->validate();

        Gate::forUser($user)->authorize('view', $post);
        Gate::forUser($user)->authorize('create', Comment::class);

        // Reject commenting on hidden/deleted/moderated posts
        if (! in_array($post->status, [PostStatus::PUBLISHED, PostStatus::EDITED])) {
            throw new AuthorizationException('Không thể bình luận trên bài viết đã bị ẩn hoặc xóa.');
        }

        $parentId = $data['parent_id'] ?? null;

        if ($parentId) {
            // Enforce that parent comment exists, belongs to same post, and is a top-level comment (parent_id is null)
            $parentComment = Comment::where('post_id', $post->id)
                ->where('id', $parentId)
                ->first();

            if (! $parentComment) {
                throw ValidationException::withMessages([
                    'parent_id' => 'Bình luận cha không tồn tại hoặc không thuộc bài viết này.',
                ]);
            }

            // Enforce exactly one level of reply depth (no nested replies to replies)
            if ($parentComment->parent_id !== null) {
                throw ValidationException::withMessages([
                    'parent_id' => 'Chỉ hỗ trợ phản hồi bình luận cấp 1. Không cho phép lồng nhau nhiều cấp.',
                ]);
            }
        }

        return Comment::create([
            'post_id' => $post->id,
            'user_id' => $user->id,
            'parent_id' => $parentId,
            'body' => $data['body'],
            'status' => CommentStatus::PUBLISHED->value,
        ]);
    }
}
