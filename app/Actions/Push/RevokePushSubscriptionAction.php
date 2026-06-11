<?php

namespace App\Actions\Push;

use App\Models\PushSubscription;
use App\Models\User;

class RevokePushSubscriptionAction
{
    public function execute(User $user, string $endpoint): void
    {
        PushSubscription::where('user_id', $user->id)
            ->where('endpoint', $endpoint)
            ->update(['revoked_at' => now()]);
    }
}
