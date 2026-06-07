<?php

namespace App\Actions\Posts;

use App\Enums\CommunityMemberStatus;
use App\Enums\PostStatus;
use App\Enums\PostType;
use App\Enums\PostVisibility;
use App\Models\Community;
use App\Models\CommunityMember;
use App\Models\OpportunityDetail;
use App\Models\Post;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class CreatePost
{
    /**
     * Create a new post.
     *
     * @param  array{body: string, post_type?: string, visibility?: string, metadata?: array, opportunity?: array, community_id?: int|null}  $data
     *
     * @throws AuthorizationException|ValidationException
     */
    public function execute(User $user, array $data): Post
    {
        Gate::forUser($user)->authorize('create', Post::class);

        $postType = PostType::tryFrom($data['post_type'] ?? 'standard') ?? PostType::STANDARD;

        $visibility = $data['visibility'] ?? PostVisibility::VERIFIED_USERS->value;
        $scopeType = null;
        $scopeId = null;

        if ($visibility === PostVisibility::COMMUNITY->value) {
            $communityId = $data['community_id'] ?? null;

            if (! $communityId) {
                throw ValidationException::withMessages([
                    'selectedCommunityId' => 'Vui lòng chọn cộng đồng để đăng bài.',
                ]);
            }

            $community = Community::find($communityId);

            if (! $community?->isActive()) {
                throw ValidationException::withMessages([
                    'selectedCommunityId' => 'Cộng đồng đã chọn không khả dụng.',
                ]);
            }

            $isActiveMember = CommunityMember::where('community_id', $community->id)
                ->where('user_id', $user->id)
                ->where('status', CommunityMemberStatus::Active->value)
                ->exists();

            if (! $isActiveMember && ! $community->isOwnedBy($user)) {
                throw ValidationException::withMessages([
                    'selectedCommunityId' => 'Bạn chỉ có thể đăng vào cộng đồng mình đã tham gia.',
                ]);
            }

            $scopeType = 'community';
            $scopeId = $community->id;
        }

        $status = $postType === PostType::OPPORTUNITY
            ? PostStatus::PENDING_REVIEW->value
            : PostStatus::PUBLISHED->value;

        return DB::transaction(function () use ($user, $data, $postType, $status, $visibility, $scopeType, $scopeId) {
            $post = Post::create([
                'user_id' => $user->id,
                'post_type' => $postType->value,
                'body' => $data['body'],
                'visibility' => $visibility,
                'scope_type' => $scopeType,
                'scope_id' => $scopeId,
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
