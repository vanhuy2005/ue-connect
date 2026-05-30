<?php

namespace App\Actions\Settings;

use App\Models\User;
use Illuminate\Support\Facades\Gate;

class UpdateNotificationPreferencesAction
{
    /**
     * Update user notification preferences.
     *
     * @param  array<string, mixed>  $data
     */
    public function execute(User $user, array $data): void
    {
        Gate::forUser($user)->authorize('updateNotifications', $user);

        // Sanitize data
        unset($data['id'], $data['user_id'], $data['created_at'], $data['updated_at'], $data['deleted_at']);

        // Backend Enforcement: Critical notifications MUST remain enabled (always true)
        $data['safety_notifications'] = true;
        $data['moderation_notifications'] = true;
        $data['system_notifications'] = true;

        $user->notificationPreference()->update($data);
    }
}
