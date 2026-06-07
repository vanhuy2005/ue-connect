<?php

namespace App\Http\Controllers;

use App\Actions\Follows\FollowUser;
use App\Actions\Follows\UnfollowUser;
use App\Models\User;
use App\Models\UserFollow;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class UserFollowController extends Controller
{
    public function store(User $user, FollowUser $followUser): JsonResponse|RedirectResponse
    {
        $viewer = Auth::user();

        $followUser->execute($viewer, $user);

        if (request()->expectsJson()) {
            return response()->json($this->statusFor($viewer, $user));
        }

        return back()->with('status', 'Đã theo dõi người dùng.');
    }

    public function destroy(User $user, UnfollowUser $unfollowUser): JsonResponse|RedirectResponse
    {
        $viewer = Auth::user();

        $unfollowUser->execute($viewer, $user);

        if (request()->expectsJson()) {
            return response()->json($this->statusFor($viewer, $user));
        }

        return back()->with('status', 'Đã bỏ theo dõi người dùng.');
    }

    /**
     * @return array{isFollowing: bool, followersCount: int, followingCount: int}
     */
    private function statusFor(User $viewer, User $target): array
    {
        return [
            'isFollowing' => UserFollow::where('follower_id', $viewer->id)
                ->where('following_id', $target->id)
                ->exists(),
            'followersCount' => UserFollow::where('following_id', $target->id)->count(),
            'followingCount' => UserFollow::where('follower_id', $target->id)->count(),
        ];
    }
}
