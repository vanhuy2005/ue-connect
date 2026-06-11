<?php

namespace App\Policies;

use App\Enums\ConversationStatus;
use App\Models\BlockedUser;
use App\Models\Connection;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;

class MessagePolicy
{
    /**
     * Determine whether the user can view the message.
     */
    public function view(User $user, Message $message): bool
    {
        if (! $user->isActive()) {
            return false;
        }

        // Must be participant in the conversation
        return $message->conversation->participants()->where('user_id', $user->id)->exists();
    }

    /**
     * Determine whether the user can reply to the message.
     */
    public function reply(User $user, Message $message): bool
    {
        if (! $user->isActive()) {
            return false;
        }

        $conversation = $message->conversation;

        // 1. Must be participant in conversation
        $isParticipant = $conversation->participants()->where('user_id', $user->id)->exists();
        if (! $isParticipant) {
            return false;
        }

        // 2. Active connection check (cannot reply if blocked/disconnected)
        $recipient = $conversation->getRecipientFor($user);
        if (! $recipient || ! $recipient->isActive()) {
            return false;
        }

        $hasBlock = BlockedUser::where(function ($q) use ($user, $recipient) {
            $q->where('blocker_id', $user->id)->where('blocked_id', $recipient->id);
        })->orWhere(function ($q) use ($user, $recipient) {
            $q->where('blocker_id', $recipient->id)->where('blocked_id', $user->id);
        })->exists();

        if ($hasBlock) {
            return false;
        }

        $hasConnection = Connection::where(function ($q) use ($user, $recipient) {
            $q->where('user_one_id', min($user->id, $recipient->id))
                ->where('user_two_id', max($user->id, $recipient->id));
        })->where('status', 'active')->exists();

        // Must be an active conversation OR a direct conversation where they are connected friends
        if ($conversation->status !== ConversationStatus::ACTIVE && ! $hasConnection) {
            return false;
        }

        // Connection check for active conversations (must be currently connected unless this is a mentor conversation)
        if ($conversation->status === ConversationStatus::ACTIVE && ! $conversation->mentor_request_id && ! $hasConnection) {
            return false;
        }

        return true;
    }

    /**
     * Determine whether the user can recall their own message.
     */
    public function recall(User $user, Message $message): bool
    {
        if (! $user->isActive()) {
            return false;
        }

        // 1. Must be the sender
        if ((int) $message->sender_id !== (int) $user->id) {
            return false;
        }

        // 2. Cannot recall twice
        if ($message->isRecalled()) {
            return false;
        }

        // 3. Conversation must be active
        return $message->conversation->participants()->where('user_id', $user->id)->exists();
    }

    /**
     * Determine whether the user can delete/recall the message locally.
     */
    public function deleteOwn(User $user, Message $message): bool
    {
        return $this->recall($user, $message);
    }

    /**
     * Determine whether the user can pin the message.
     */
    public function pin(User $user, Message $message): bool
    {
        if (! $user->isActive()) {
            return false;
        }

        // 1. Must be participant
        $isParticipant = $message->conversation->participants()->where('user_id', $user->id)->exists();
        if (! $isParticipant) {
            return false;
        }

        // 2. Cannot pin recalled/deleted message
        if ($message->isRecalled()) {
            return false;
        }

        return true;
    }

    /**
     * Determine whether the user can unpin the message.
     */
    public function unpin(User $user, Message $message): bool
    {
        return $this->pin($user, $message);
    }

    /**
     * Determine whether the user can forward the message.
     */
    public function forward(User $user, Message $message, Conversation $targetConversation): bool
    {
        if (! $user->isActive()) {
            return false;
        }

        // 1. Must be able to view original message
        if (! $this->view($user, $message)) {
            return false;
        }

        // 2. Cannot forward recalled message
        if ($message->isRecalled()) {
            return false;
        }

        // 3. Must be participant in target conversation
        $isTargetParticipant = $targetConversation->participants()->where('user_id', $user->id)->exists();
        if (! $isTargetParticipant) {
            return false;
        }

        // 4. Target conversation active connection check (cannot forward to blocked/disconnected)
        $recipient = $targetConversation->getRecipientFor($user);
        if (! $recipient || ! $recipient->isActive()) {
            return false;
        }

        $hasBlock = BlockedUser::where(function ($q) use ($user, $recipient) {
            $q->where('blocker_id', $user->id)->where('blocked_id', $recipient->id);
        })->orWhere(function ($q) use ($user, $recipient) {
            $q->where('blocker_id', $recipient->id)->where('blocked_id', $user->id);
        })->exists();

        if ($hasBlock) {
            return false;
        }

        $hasConnection = Connection::where(function ($q) use ($user, $recipient) {
            $q->where('user_one_id', min($user->id, $recipient->id))
                ->where('user_two_id', max($user->id, $recipient->id));
        })->where('status', 'active')->exists();

        if (! $hasConnection) {
            return false;
        }

        return true;
    }

    /**
     * Determine whether the user can report the message.
     */
    public function report(User $user, Message $message): bool
    {
        if (! $user->isActive()) {
            return false;
        }

        // 1. Must be participant (can view original message)
        if (! $this->view($user, $message)) {
            return false;
        }

        // 2. Cannot report own message
        if ((int) $message->sender_id === (int) $user->id) {
            return false;
        }

        return true;
    }
}
