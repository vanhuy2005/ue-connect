<?php

namespace App\Policies;

use App\Enums\ConversationStatus;
use App\Models\BlockedUser;
use App\Models\Connection;
use App\Models\Conversation;
use App\Models\User;

class ConversationPolicy
{
    /**
     * Determine whether the user can view the conversation.
     */
    public function view(User $user, Conversation $conversation): bool
    {
        if (! $user->isActive()) {
            return false;
        }

        // Must be a participant
        return $conversation->participants()->where('user_id', $user->id)->exists();
    }

    /**
     * Determine whether the user can send a message.
     */
    public function sendMessage(User $user, Conversation $conversation): bool
    {
        if (! $user->isActive()) {
            return false;
        }

        // Must be an active conversation
        if ($conversation->status !== ConversationStatus::ACTIVE) {
            return false;
        }

        // 1. Must be a participant
        $isParticipant = $conversation->participants()->where('user_id', $user->id)->exists();
        if (! $isParticipant) {
            return false;
        }

        // Get the other participant
        $recipient = $conversation->getRecipientFor($user);
        if (! $recipient || ! $recipient->isActive()) {
            return false;
        }

        // 2. Block check (either way)
        $hasBlock = BlockedUser::where(function ($q) use ($user, $recipient) {
            $q->where('blocker_id', $user->id)->where('blocked_id', $recipient->id);
        })->orWhere(function ($q) use ($user, $recipient) {
            $q->where('blocker_id', $recipient->id)->where('blocked_id', $user->id);
        })->exists();

        if ($hasBlock) {
            return false;
        }

        if ($conversation->mentor_request_id) {
            return true;
        }

        // 3. Connection check (must be currently connected unless this is a mentor conversation)
        $hasConnection = Connection::where(function ($q) use ($user, $recipient) {
            $q->where('user_one_id', min($user->id, $recipient->id))
                ->where('user_two_id', max($user->id, $recipient->id));
        })->where('status', 'active')->exists();

        if (! $hasConnection) {
            return false;
        }

        return true;
    }
}
