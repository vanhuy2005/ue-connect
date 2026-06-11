<?php

namespace App\Actions\Push;

use App\Models\PushSubscription;

class CleanupInvalidPushSubscriptionsAction
{
    public function execute(): void
    {
        // Delete subscriptions that have failed 3 times or have been revoked for a long time
        PushSubscription::where('failed_attempts', '>=', 3)
            ->orWhere('revoked_at', '<', now()->subDays(30))
            ->delete();
    }
}
