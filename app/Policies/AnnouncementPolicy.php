<?php

namespace App\Policies;

use App\Models\User;

class AnnouncementPolicy
{
    public function manage(User $user): bool
    {
        return $user->hasRole('admin') || method_exists($user, 'hasPermissionTo') && $user->hasPermissionTo('manage_announcements');
    }

    public function create(User $user): bool
    {
        return $user->hasRole('admin') || method_exists($user, 'hasPermissionTo') && $user->hasPermissionTo('create_announcement');
    }
}
