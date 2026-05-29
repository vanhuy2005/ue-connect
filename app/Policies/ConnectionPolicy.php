<?php

namespace App\Policies;

use App\Enums\GreetingStatus;
use App\Models\BlockedUser;
use App\Models\Connection;
use App\Models\Greeting;
use App\Models\User;

class ConnectionPolicy
{
    /**
     * Determine whether the user can send a greeting/connection request to the receiver.
     */
    public function send(User $user, User $receiver): bool
    {
        // 1. Both must be verified active
        if (! $user->isActive() || ! $receiver->isActive()) {
            return false;
        }

        // 2. Cannot send to self
        if ($user->id === $receiver->id) {
            return false;
        }

        // 3. Block check (either blocker or blocked)
        $hasBlock = BlockedUser::where(function ($q) use ($user, $receiver) {
            $q->where('blocker_id', $user->id)->where('blocked_id', $receiver->id);
        })->orWhere(function ($q) use ($user, $receiver) {
            $q->where('blocker_id', $receiver->id)->where('blocked_id', $user->id);
        })->exists();

        if ($hasBlock) {
            return false;
        }

        // 4. Duplicate pending check
        $hasPending = Greeting::where('sender_id', $user->id)
            ->where('receiver_id', $receiver->id)
            ->where('status', GreetingStatus::PENDING)
            ->exists();

        if ($hasPending) {
            return false;
        }

        // 5. Already connected check
        $hasConnection = Connection::where(function ($q) use ($user, $receiver) {
            $q->where('user_one_id', min($user->id, $receiver->id))
                ->where('user_two_id', max($user->id, $receiver->id));
        })->where('status', 'active')->exists();

        if ($hasConnection) {
            return false;
        }

        return true;
    }

    /**
     * Determine whether the user can accept a greeting.
     */
    public function accept(User $user, Greeting $greeting): bool
    {
        return $user->isActive()
            && $greeting->receiver_id === $user->id
            && $greeting->status === GreetingStatus::PENDING;
    }

    /**
     * Determine whether the user can decline a greeting.
     */
    public function decline(User $user, Greeting $greeting): bool
    {
        return $user->isActive()
            && $greeting->receiver_id === $user->id
            && $greeting->status === GreetingStatus::PENDING;
    }

    /**
     * Determine whether the user can cancel a greeting.
     */
    public function cancel(User $user, Greeting $greeting): bool
    {
        return $user->isActive()
            && $greeting->sender_id === $user->id
            && $greeting->status === GreetingStatus::PENDING;
    }

    /**
     * Determine whether the user can remove a connection.
     */
    public function remove(User $user, Connection $connection): bool
    {
        return $user->isActive()
            && ($connection->user_one_id === $user->id || $connection->user_two_id === $user->id);
    }
}
