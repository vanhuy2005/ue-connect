<?php

namespace App\Policies;

use App\Models\User;

class AnnouncementPolicy
{
    public function manage(User $user): bool
    {
        return $user->hasRole('admin') || $user->can('manage_announcements');
    }

    public function create(User $user): bool
    {
        return $user->hasRole('admin') || $user->can('create_announcement');
    }
}
