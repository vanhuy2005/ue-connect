<?php

namespace App\Services\Settings;

use App\Models\User;

class ProfileVisibilityService
{
    /**
     * Determine if $viewer can see $target's full profile based on privacy settings.
     */
    public function canViewProfile(User $target, ?User $viewer = null): bool
    {
        // If viewer is the same as target, they can always view
        if ($viewer && $viewer->id === $target->id) {
            return true;
        }

        $privacy = $target->profilePrivacySetting;

        // If no privacy settings exist, default to public
        if (! $privacy) {
            return true;
        }

        $visibility = $privacy->profile_visibility;

        if ($visibility === 'public') {
            return true;
        }

        if ($visibility === 'public_to_verified') {
            // Must be authenticated and verified (implement verified check if needed)
            return $viewer !== null; // && $viewer->isVerified();
        }

        if ($visibility === 'connections_only') {
            if (! $viewer) {
                return false;
            }

            // Check if they are connected (you would use your connection logic here)
            return $this->areConnected($target, $viewer);
        }

        if ($visibility === 'private') {
            return false;
        }

        return true;
    }

    /**
     * Check if a specific profile field should be visible to the viewer.
     */
    public function canViewField(string $field, User $target, ?User $viewer = null): bool
    {
        if (! $this->canViewProfile($target, $viewer)) {
            return false;
        }

        $privacy = $target->profilePrivacySetting;
        if (! $privacy) {
            return true;
        }

        // List of toggleable fields
        $toggleableFields = [
            'faculty' => 'show_faculty',
            'major' => 'show_major',
            'cohort' => 'show_cohort',
            'class_code' => 'show_class_code',
            'bio' => 'show_bio',
            'interests' => 'show_interests',
            'connection_goals' => 'show_connection_goals',
            'communities' => 'show_communities',
            'career_info' => 'show_career_info',
            'mentor_topics' => 'show_mentor_topics',
        ];

        if (array_key_exists($field, $toggleableFields)) {
            $attribute = $toggleableFields[$field];

            return (bool) $privacy->$attribute;
        }

        return true; // Field is not toggleable, so visible
    }

    private function areConnected(User $user1, User $user2): bool
    {
        // Stub for checking connection between two users
        // Implement according to the Connection system (e.g. Connection model)
        // return Connection::where(function($query) use ($user1, $user2) {
        //     $query->where('user_id', $user1->id)->where('connected_user_id', $user2->id);
        // })->orWhere(function($query) use ($user1, $user2) {
        //     $query->where('user_id', $user2->id)->where('connected_user_id', $user1->id);
        // })->where('status', 'accepted')->exists();
        return false;
    }
}
