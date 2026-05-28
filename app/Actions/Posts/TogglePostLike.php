<?php

namespace App\Actions\Posts;

use App\Enums\PostStatus;
use App\Models\Post;
use App\Models\PostLike;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Gate;

class TogglePostLike
{
    /**
     * Toggle post like status.
     *
     *
     * @throws AuthorizationException
     */
    public function execute(User $user, Post $post): void
    {
        // Enforce user active status
        if (! $user->isActive()) {
            throw new AuthorizationException('Tài khoản không hoạt động.');
        }

        // Authorize post viewing before like
        Gate::forUser($user)->authorize('view', $post);

        // Reject liking if the post status is not active (published or edited)
        if (! in_array($post->status, [PostStatus::PUBLISHED, PostStatus::EDITED])) {
            throw new AuthorizationException('Không thể tương tác với bài viết đã bị ẩn hoặc xóa.');
        }

        $existingLike = PostLike::where('post_id', $post->id)
            ->where('user_id', $user->id)
            ->first();

        if ($existingLike) {
            $existingLike->delete();
        } else {
            PostLike::create([
                'post_id' => $post->id,
                'user_id' => $user->id,
            ]);
        }
    }
}
