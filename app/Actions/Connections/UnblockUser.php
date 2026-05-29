<?php

namespace App\Actions\Connections;

use App\Models\BlockedUser;
use App\Models\User;

class UnblockUser
{
    /**
     * Unblock a blocked user.
     */
    public function execute(User $blocker, User $blocked): void
    {
        BlockedUser::where('blocker_id', $blocker->id)
            ->where('blocked_id', $blocked->id)
            ->delete();
    }
}
