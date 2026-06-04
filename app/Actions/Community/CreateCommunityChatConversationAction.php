<?php

namespace App\Actions\Community;

use App\Enums\ConversationType;
use App\Models\Community;
use App\Models\Conversation;
use App\Models\ConversationParticipant;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CreateCommunityChatConversationAction
{
    public function execute(User $actor, Community $community): Conversation
    {
        // Check if chat already exists
        $existing = Conversation::where('source_type', 'community')
            ->where('source_id', $community->id)
            ->whereNull('deleted_at')
            ->first();

        if ($existing) {
            return $existing;
        }

        return DB::transaction(function () use ($actor, $community) {
            $conversation = Conversation::create([
                'conversation_type' => ConversationType::COMMUNITY_CHAT->value,
                'source_type' => 'community',
                'source_id' => $community->id,
                'title' => $community->name,
                'status' => 'active',
                'created_by' => $actor->id,
            ]);

            // Add all active members as participants
            $community->activeMembers()
                ->with('user')
                ->get()
                ->each(function ($member) use ($conversation) {
                    ConversationParticipant::create([
                        'conversation_id' => $conversation->id,
                        'user_id' => $member->user_id,
                        'participant_role' => 'member',
                        'status' => 'active',
                        'joined_at' => now(),
                    ]);
                });

            return $conversation;
        });
    }

    /**
     * Sync a single new member into the community chat conversation.
     */
    public function syncMember(Community $community, int $userId): void
    {
        $conversation = Conversation::where('source_type', 'community')
            ->where('source_id', $community->id)
            ->whereNull('deleted_at')
            ->first();

        if (! $conversation) {
            return;
        }

        ConversationParticipant::firstOrCreate(
            ['conversation_id' => $conversation->id, 'user_id' => $userId],
            [
                'participant_role' => 'member',
                'status' => 'active',
                'joined_at' => now(),
            ]
        );
    }
}
