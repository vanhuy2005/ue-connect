<?php

namespace App\Actions\Follows;

use App\Models\User;
use App\Models\UserFollow;
use Illuminate\Validation\ValidationException;

class UnfollowUser
{
    /**
     * Unfollow a user without mutating friend or mentor relationships.
     *
     * @throws ValidationException
     */
    public function execute(User $follower, User $target): bool
    {
        if ($follower->is($target)) {
            throw ValidationException::withMessages([
                'follow' => 'Bạn không thể tự bỏ theo dõi chính mình.',
            ]);
        }

        return (bool) UserFollow::where('follower_id', $follower->id)
            ->where('following_id', $target->id)
            ->delete();
    }
}
