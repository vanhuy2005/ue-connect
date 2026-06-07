<?php

namespace App\Actions\Community;

use App\Enums\PostStatus;
use App\Enums\PostVisibility;
use App\Models\Community;
use App\Models\CommunityMember;
use App\Models\Post;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CreateCommunityPostAction
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function execute(User $user, Community $community, array $data): Post
    {
        if (! $community->isActive()) {
            throw ValidationException::withMessages([
                'community' => 'Không thể đăng bài trong cộng đồng đang không hoạt động.',
            ]);
        }

        if ($community->isOwnedBy($user)) {
            // Owner is allowed to post by default
        } else {
            $member = CommunityMember::where('community_id', $community->id)
                ->where('user_id', $user->id)
                ->first();

            if (! $member?->canPost()) {
                throw ValidationException::withMessages([
                    'community' => 'Bạn không có quyền đăng bài trong cộng đồng này.',
                ]);
            }
        }

        return DB::transaction(function () use ($user, $community, $data) {
            $post = Post::create([
                'user_id' => $user->id,
                'scope_type' => 'community',
                'scope_id' => $community->id,
                'community_post_type' => $data['community_post_type'] ?? 'standard_post',
                'body' => $data['body'],
                'visibility' => PostVisibility::COMMUNITY,
                'status' => PostStatus::PUBLISHED,
                'published_at' => now(),
            ]);

            $community->increment('post_count');

            return $post;
        });
    }
}
