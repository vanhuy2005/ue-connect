<?php

namespace App\Actions\Settings;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class EnsureUserSettingsExistAction
{
    /**
     * Idempotently ensure profile privacy settings and notification preferences exist for the user.
     */
    public function execute(User $user): void
    {
        DB::transaction(function () use ($user) {
            if (! $user->profilePrivacySetting()->exists()) {
                $user->profilePrivacySetting()->create([
                    'profile_visibility' => 'public_to_verified',
                    'discovery_visibility' => 'enabled',
                    'show_faculty' => true,
                    'show_major' => true,
                    'show_cohort' => true,
                    'show_class_code' => false,
                    'show_bio' => true,
                    'show_interests' => true,
                    'show_connection_goals' => true,
                    'show_communities' => false,
                    'show_career_info' => false,
                    'show_mentor_topics' => true,
                ]);
            }

            if (! $user->notificationPreference()->exists()) {
                $user->notificationPreference()->create([
                    'in_app_enabled' => true,
                    'browser_push_enabled' => false,
                    'email_enabled' => false,
                    'greeting_notifications' => true,
                    'message_notifications' => true,
                    'mentor_notifications' => true,
                    'community_notifications' => true,
                    'safety_notifications' => true,
                    'moderation_notifications' => true,
                    'system_notifications' => true,
                ]);
            }
        });
    }
}
