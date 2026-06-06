<?php

namespace App\Actions\Posts;

use App\Enums\PostStatus;
use App\Models\Post;
use App\Models\PostRepost;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Gate;

class CreatePostRepost
{
    /**
     * Create a repost for the given user and post.
     *
     * @throws AuthorizationException
     */
    public function execute(User $user, Post $post): PostRepost
    {
        if (! $user->isActive()) {
            throw new AuthorizationException('Tài khoản không hoạt động.');
        }

        if ($post->user_id === $user->id) {
            throw new AuthorizationException('Không thể đăng lại bài viết của chính mình.');
        }

        if (! Gate::forUser($user)->allows('view', $post)) {
            throw new AuthorizationException('Bạn không có quyền xem bài viết này.');
        }

        if (! in_array($post->status, [PostStatus::PUBLISHED, PostStatus::EDITED], true)) {
            throw new AuthorizationException('Không thể đăng lại bài viết đã bị ẩn hoặc xóa.');
        }

        return PostRepost::firstOrCreate([
            'post_id' => $post->id,
            'user_id' => $user->id,
        ]);
    }
}
