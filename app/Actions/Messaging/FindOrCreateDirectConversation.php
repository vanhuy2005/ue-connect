<?php

namespace App\Actions\Messaging;

use App\Enums\ConversationStatus;
use App\Enums\ConversationType;
use App\Models\Conversation;
use App\Models\ConversationParticipant;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class FindOrCreateDirectConversation
{
    /**
     * Find or create a 1:1 direct conversation between two users.
     */
    public function execute(User $userA, User $userB): Conversation
    {
        // 1. Search for existing direct conversation where both are participants
        $existingConversation = Conversation::where('conversation_type', ConversationType::DIRECT)
            ->whereHas('participants', function ($query) use ($userA) {
                $query->where('user_id', $userA->id);
            })
            ->whereHas('participants', function ($query) use ($userB) {
                $query->where('user_id', $userB->id);
            })
            ->first();

        if ($existingConversation) {
            // Restore status to active if archived/deleted
            if ($existingConversation->status !== ConversationStatus::ACTIVE) {
                $existingConversation->update(['status' => ConversationStatus::ACTIVE]);
            }

            return $existingConversation;
        }

        // 2. Create new direct conversation
        return DB::transaction(function () use ($userA, $userB) {
            $conversation = Conversation::create([
                'conversation_type' => ConversationType::DIRECT,
                'status' => ConversationStatus::ACTIVE,
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
