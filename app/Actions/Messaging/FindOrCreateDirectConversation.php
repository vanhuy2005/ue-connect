<?php

namespace App\Actions\Messaging;

use App\Enums\ConnectionStatus;
use App\Enums\ConversationStatus;
use App\Enums\ConversationType;
use App\Models\BlockedUser;
use App\Models\Connection;
use App\Models\Conversation;
use App\Models\ConversationParticipant;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class FindOrCreateDirectConversation
{
    /**
     * Find or create a 1:1 direct conversation between two users.
     *
     * @throws \Exception
     */
    public function execute(User $userA, User $userB): Conversation
    {
        // 1. Guard against same user
        if ($userA->id === $userB->id) {
            throw new \Exception('Không thể tạo cuộc trò chuyện với chính mình.');
        }

        // 2. Guard against inactive users
        if (! $userA->isActive() || ! $userB->isActive()) {
            throw new \Exception('Cả hai tài khoản người dùng phải ở trạng thái hoạt động.');
        }

        $lowId = min($userA->id, $userB->id);
        $highId = max($userA->id, $userB->id);

        // 3. Guard against blocked state
        $isBlocked = BlockedUser::where(function ($q) use ($userA, $userB) {
            $q->where('blocker_id', $userA->id)->where('blocked_id', $userB->id);
        })->orWhere(function ($q) use ($userA, $userB) {
            $q->where('blocker_id', $userB->id)->where('blocked_id', $userA->id);
        })->exists();

        if ($isBlocked) {
            throw new \Exception('Không thể tạo cuộc trò chuyện do trạng thái chặn giữa hai người dùng.');
        }

        // 4. Guard against non-connected users
        $isConnected = Connection::where('user_one_id', $lowId)
            ->where('user_two_id', $highId)
            ->where('status', ConnectionStatus::ACTIVE)
            ->exists();

        if (! $isConnected) {
            throw new \Exception('Hãy kết nối bạn bè trước khi bắt đầu trò chuyện.');
        }

        // 5. Search for existing direct conversation where both are participants using normalized pair
        $existingConversation = Conversation::where('conversation_type', ConversationType::DIRECT)
            ->where('direct_user_low_id', $lowId)
            ->where('direct_user_high_id', $highId)
            ->first();

        if ($existingConversation) {
            // Restore status to active if archived/deleted
            if ($existingConversation->status !== ConversationStatus::ACTIVE) {
                $existingConversation->update(['status' => ConversationStatus::ACTIVE]);
            }

            return $existingConversation;
        }

        // 6. Create new direct conversation
        return DB::transaction(function () use ($userA, $userB, $lowId, $highId) {
            $conversation = Conversation::create([
                'conversation_type' => ConversationType::DIRECT,
                'status' => ConversationStatus::ACTIVE,
                'direct_user_low_id' => $lowId,
                'direct_user_high_id' => $highId,
            ]);

            // Add Participant A
            ConversationParticipant::create([
                'conversation_id' => $conversation->id,
                'user_id' => $userA->id,
                'participant_role' => 'member',
                'status' => 'active',
                'joined_at' => now(),
            ]);

            // Add Participant B
            ConversationParticipant::create([
                'conversation_id' => $conversation->id,
                'user_id' => $userB->id,
                'participant_role' => 'member',
                'status' => 'active',
                'joined_at' => now(),
            ]);

            return $conversation;
        });
    }
}
