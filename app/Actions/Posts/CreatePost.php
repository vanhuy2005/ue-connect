<?php

namespace App\Actions\Posts;

use App\Enums\PostStatus;
use App\Enums\PostVisibility;
use App\Models\Post;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Gate;

class CreatePost
{
    /**
     * Create a new post.
     *
     * @param  array{body: string, visibility?: string}  $data
     *
     * @throws AuthorizationException
     */
    public function execute(User $user, array $data): Post
    {
        Gate::forUser($user)->authorize('create', Post::class);

        return Post::create([
            'user_id' => $user->id,
            'body' => $data['body'],
            'visibility' => $data['visibility'] ?? PostVisibility::VERIFIED_USERS->value,
            'status' => PostStatus::PUBLISHED->value,
            'published_at' => now(),
        ]);
    }
}
