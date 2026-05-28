<?php

namespace App\Actions\Posts;

use App\Enums\PostStatus;
use App\Models\Post;
use App\Models\PostSave;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Gate;

class TogglePostSave
{
    /**
     * Toggle post save status.
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

        // Authorize post viewing before save
        Gate::forUser($user)->authorize('view', $post);

        // Reject saving if the post status is not active (published or edited)
        if (! in_array($post->status, [PostStatus::PUBLISHED, PostStatus::EDITED])) {
            throw new AuthorizationException('Không thể tương tác với bài viết đã bị ẩn hoặc xóa.');
        }

        $existingSave = PostSave::where('post_id', $post->id)
            ->where('user_id', $user->id)
            ->first();

        if ($existingSave) {
            $existingSave->delete();
        } else {
            PostSave::create([
                'post_id' => $post->id,
                'user_id' => $user->id,
            ]);
        }
    }
}
