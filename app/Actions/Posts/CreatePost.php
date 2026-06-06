<?php

namespace App\Actions\Posts;

use App\Enums\CommunityMemberStatus;
use App\Enums\PostStatus;
use App\Enums\PostVisibility;
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
     * @param  array{body: string, visibility?: string, community_id?: int|null}  $data
     *
     * @throws AuthorizationException|ValidationException
     */
    public function execute(User $user, array $data): Post
    {
        Gate::forUser($user)->authorize('create', Post::class);

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

        return Post::create([
            'user_id' => $user->id,
            'scope_type' => $scopeType,
            'scope_id' => $scopeId,
            'body' => $data['body'],
            'visibility' => $visibility,
            'status' => PostStatus::PUBLISHED->value,
            'published_at' => now(),
        ]);
    }
}
