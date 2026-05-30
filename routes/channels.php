<?php

use App\Models\Conversation;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('user.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('conversation.{conversationId}', function ($user, $conversationId) {
    if (! $user->isActive()) {
        return false;
    }

    $conversation = Conversation::find($conversationId);
    if (! $conversation) {
        return false;
    }

    return $conversation->participants()->where('user_id', $user->id)->exists();
});
