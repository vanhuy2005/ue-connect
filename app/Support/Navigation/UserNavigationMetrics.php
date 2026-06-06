<?php

namespace App\Support\Navigation;

use App\Models\ConversationParticipant;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

class UserNavigationMetrics
{
    /**
     * @var array<int, array{unread_notifications: int, unread_messages: int}>
     */
    private array $memoizedMetrics = [];

    /**
     * @return array{unread_notifications: int, unread_messages: int}
     */
    public function forUser(?User $user): array
    {
        if (! $user) {
            return [
                'unread_notifications' => 0,
                'unread_messages' => 0,
            ];
        }

        if (isset($this->memoizedMetrics[$user->id])) {
            return $this->memoizedMetrics[$user->id];
        }

        return $this->memoizedMetrics[$user->id] = Cache::remember(
            $this->cacheKey($user->id),
            now()->addSeconds(20),
            fn (): array => [
                'unread_notifications' => $user->unreadNotifications()->count(),
                'unread_messages' => $this->unreadMessagesCount($user),
            ],
        );
    }

    public function forgetForUser(User|int|null $user): void
    {
        if (! $user) {
            return;
        }

        $userId = $user instanceof User ? $user->id : $user;

        unset($this->memoizedMetrics[$userId]);
        Cache::forget($this->cacheKey($userId));
    }

    private function unreadMessagesCount(User $user): int
    {
        return ConversationParticipant::query()
            ->where('user_id', $user->id)
            ->where(function ($query): void {
                $query->whereNull('last_read_at')
                    ->orWhereHas('conversation', function ($conversationQuery): void {
                        $conversationQuery->whereColumn('last_message_at', '>', 'conversation_participants.last_read_at');
                    });
            })
            ->whereHas('conversation', function ($conversationQuery): void {
                $conversationQuery->whereNotNull('last_message_at');
            })
            ->count();
    }

    private function cacheKey(int $userId): string
    {
        return "navigation-metrics:user:{$userId}";
    }
}
