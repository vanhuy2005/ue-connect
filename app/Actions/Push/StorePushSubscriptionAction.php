<?php

namespace App\Actions\Push;

use App\Models\PushSubscription;
use App\Models\User;

class StorePushSubscriptionAction
{
    public function execute(User $user, array $data): PushSubscription
    {
        return PushSubscription::updateOrCreate(
            [
                'endpoint' => $data['endpoint'],
            ],
            [
                'user_id' => $user->id,
                'public_key' => $data['keys']['p256dh'],
                'auth_token' => $data['keys']['auth'],
                'content_encoding' => $data['contentEncoding'] ?? 'aes128gcm',
                'user_agent' => request()->userAgent(),
                'revoked_at' => null, // Reset if previously revoked
                'failed_attempts' => 0, // Reset errors
            ]
        );
    }
}
