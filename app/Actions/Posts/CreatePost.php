<?php

namespace App\Actions\Posts;

use App\Enums\CommunityMemberStatus;
use App\Enums\PostStatus;
use App\Enums\PostType;
use App\Enums\PostVisibility;
use App\Events\Feed\PostCreated;
use App\Models\Community;
use App\Models\CommunityMember;
use App\Models\Post;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class CreatePost
{
    /**
     * Create a new post.
     *
     * @param  array{body: string, visibility?: string, community_id?: int|null, post_type?: string|null, tags?: array, opportunity?: array}  $data
     *
     * @throws AuthorizationException|ValidationException
     */
    public function execute(User $user, array $data): Post
    {
        Gate::forUser($user)->authorize('create', Post::class);

        $tags = $data['tags'] ?? [];
        $postTypeValue = $data['post_type'] ?? null;

        if (empty($postTypeValue) && ! empty($tags)) {
            if (in_array('opportunity', $tags, true)) {
                $postTypeValue = 'opportunity';
            } elseif (in_array('experience', $tags, true)) {
                $postTypeValue = 'experience';
            } else {
                $postTypeValue = 'standard';
            }
        }

        $postType = $postTypeValue ? PostType::tryFrom($postTypeValue) : PostType::STANDARD;

        if ($postType !== PostType::STANDARD && ! $user->canPostType($postType)) {
            throw ValidationException::withMessages([
                'post_type' => 'Bạn không có quyền đăng loại bài viết này.',
            ]);
        }

        $oppData = [];
        if ($postType === PostType::OPPORTUNITY) {
            $isPedagogy = in_array('pedagogy', $tags, true) || (($data['opportunity']['category'] ?? '') === 'pedagogy');
            $oppData = [
                'category' => $isPedagogy ? 'pedagogy' : 'non_pedagogy',
            ];
        }

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

        $post = Post::create([
            'user_id' => $user->id,
            'scope_type' => $scopeType,
            'scope_id' => $scopeId,
            'body' => $data['body'],
            'visibility' => $visibility,
            'post_type' => $postType->value,
            'status' => PostStatus::PUBLISHED->value,
            'moderation_status' => $postType === PostType::OPPORTUNITY ? 'pending' : 'none',
            'published_at' => now(),
            'tags' => $tags,
        ]);

        if ($postType === PostType::OPPORTUNITY) {
            $post->opportunity()->create([
                'is_expired' => false,
                'category' => $oppData['category'] ?? 'non_pedagogy',
            ]);
        }

        if ($postType !== PostType::OPPORTUNITY) {
            PostCreated::dispatch($post);
        }

        return $post;
    }
}
