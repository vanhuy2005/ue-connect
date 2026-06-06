<?php

namespace App\Actions\Follows;

use App\Models\User;
use App\Models\UserFollow;
use Illuminate\Validation\ValidationException;

class FollowUser
{
    /**
     * Follow a user without mutating friend or mentor relationships.
     *
     * @throws ValidationException
     */
    public function execute(User $follower, User $target): UserFollow
    {
        if ($follower->is($target)) {
            throw ValidationException::withMessages([
                'follow' => 'Bạn không thể tự theo dõi chính mình.',
            ]);
        }

        if (UserFollow::where('follower_id', $follower->id)
            ->where('following_id', $target->id)
            ->exists()) {
            throw ValidationException::withMessages([
                'follow' => 'Bạn đã theo dõi người dùng này.',
            ]);
        }

        return UserFollow::create([
            'follower_id' => $follower->id,
            'following_id' => $target->id,
        ]);
    }
}
