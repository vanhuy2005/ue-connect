<?php

namespace App\Actions\Posts;

use App\Enums\PostStatus;
use App\Enums\PostType;
use App\Enums\PostVisibility;
use App\Models\OpportunityDetail;
use App\Models\Post;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class CreatePost
{
    /**
     * Create a new post.
     *
     * @param  array{body: string, post_type?: string, visibility?: string, metadata?: array, opportunity?: array}  $data
     *
     * @throws AuthorizationException
     */
    public function execute(User $user, array $data): Post
    {
        Gate::forUser($user)->authorize('create', Post::class);

        $postType = PostType::tryFrom($data['post_type'] ?? 'standard') ?? PostType::STANDARD;

        // Opportunity posts require admin review before being published
        $status = $postType === PostType::OPPORTUNITY
            ? PostStatus::PENDING_REVIEW->value
            : PostStatus::PUBLISHED->value;

        return DB::transaction(function () use ($user, $data, $postType, $status) {
            $post = Post::create([
                'user_id' => $user->id,
                'post_type' => $postType->value,
                'body' => $data['body'],
                'visibility' => $data['visibility'] ?? PostVisibility::VERIFIED_USERS->value,
                'status' => $status,
                'metadata' => $data['metadata'] ?? null,
                'published_at' => $status === PostStatus::PUBLISHED->value ? now() : null,
            ]);

            if ($postType === PostType::OPPORTUNITY && ! empty($data['opportunity'])) {
                OpportunityDetail::create([
                    'post_id' => $post->id,
                    'company' => $data['opportunity']['company'] ?? null,
                    'position' => $data['opportunity']['position'] ?? null,
                    'location' => $data['opportunity']['location'] ?? null,
                    'application_url' => $data['opportunity']['application_url'] ?? null,
                    'application_deadline' => $data['opportunity']['application_deadline'] ?? null,
                    'field_tags' => $data['opportunity']['field_tags'] ?? null,
                ]);
            }

            return $post;
        });
    }
}
