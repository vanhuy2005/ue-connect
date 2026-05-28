<?php

namespace App\Actions\Posts;

use App\Enums\PostStatus;
use App\Models\Post;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class UpdatePost
{
    /**
     * Update an existing post.
     *
     * @param  array{body: string}  $data
     *
     * @throws AuthorizationException
     * @throws ValidationException
     */
    public function execute(User $user, Post $post, array $data): Post
    {
        // Enforce user active status
        if (! $user->isActive()) {
            throw new AuthorizationException('Tài khoản không hoạt động.');
        }

        // Authorize post updating via policy
        Gate::forUser($user)->authorize('update', $post);

        // Validate body
        Validator::make($data, [
            'body' => 'required|string|max:3000',
        ])->validate();

        // Update post attributes
        $post->body = $data['body'];

        if (in_array($post->status, [PostStatus::PUBLISHED, PostStatus::EDITED])) {
            $post->status = PostStatus::EDITED;
            $post->edited_at = now();
        }

        $post->save();

        return $post;
    }
}
