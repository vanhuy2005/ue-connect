<?php

namespace App\Actions\Connections;

use App\Enums\ConnectionStatus;
use App\Enums\GreetingStatus;
use App\Models\BlockedUser;
use App\Models\Connection;
use App\Models\Greeting;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class BlockUser
{
    /**
     * Block a target user.
     *
     * @param  array{reason?: string}  $data
     *
     * @throws AuthorizationException
     */
    public function execute(User $blocker, User $blocked, array $data = []): BlockedUser
    {
        Gate::forUser($blocker)->authorize('block', [BlockedUser::class, $blocked]);

        return DB::transaction(function () use ($blocker, $blocked, $data) {
            // 1. Create or update BlockedUser record
            $block = BlockedUser::updateOrCreate(
                [
                    'blocker_id' => $blocker->id,
                    'blocked_id' => $blocked->id,
                ],
                [
                    'reason' => $data['reason'] ?? null,
                ]
            );

            // 2. Terminate any active connections
            Connection::where(function ($q) use ($blocker, $blocked) {
                $q->where('user_one_id', min($blocker->id, $blocked->id))
                    ->where('user_two_id', max($blocker->id, $blocked->id));
            })->update([
                'status' => ConnectionStatus::BLOCKED,
                'disconnected_at' => now(),
            ]);

            // Soft delete active connection
            Connection::where(function ($q) use ($blocker, $blocked) {
                $q->where('user_one_id', min($blocker->id, $blocked->id))
                    ->where('user_two_id', max($blocker->id, $blocked->id));
            })->delete();

            // 3. Cancel any pending greetings between them
            Greeting::where(function ($q) use ($blocker, $blocked) {
                $q->where('sender_id', $blocker->id)->where('receiver_id', $blocked->id);
            })->orWhere(function ($q) use ($blocker, $blocked) {
                $q->where('sender_id', $blocked->id)->where('receiver_id', $blocker->id);
            })->where('status', GreetingStatus::PENDING)
                ->update([
                    'status' => GreetingStatus::BLOCKED,
                ]);

            return $block;
        });
    }
}
