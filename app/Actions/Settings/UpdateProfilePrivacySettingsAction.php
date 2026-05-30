<?php

namespace App\Actions\Settings;

use App\Models\User;
use Illuminate\Support\Facades\Gate;

class UpdateProfilePrivacySettingsAction
{
    /**
     * Update user profile privacy settings.
     *
     * @param  array<string, mixed>  $data
     */
    public function execute(User $user, array $data): void
    {
        Gate::forUser($user)->authorize('updatePrivacy', $user);

        // Filter and sanitize administrative-only attributes
        unset($data['id'], $data['user_id'], $data['created_at'], $data['updated_at'], $data['deleted_at']);

        // Never allow a normal user to force-hide or set moderation-controlled visibilities
        if (isset($data['profile_visibility']) && $data['profile_visibility'] === 'hidden_by_moderation') {
            unset($data['profile_visibility']);
        }
        if (isset($data['discovery_visibility']) && $data['discovery_visibility'] === 'forced_hidden') {
            unset($data['discovery_visibility']);
        }

        // Save
        $user->profilePrivacySetting()->update($data);
    }
}
