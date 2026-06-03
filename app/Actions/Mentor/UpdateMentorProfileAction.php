<?php

namespace App\Actions\Mentor;

use App\Models\MentorProfile;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Gate;

class UpdateMentorProfileAction
{
    /**
     * Approved mentor updates their own profile.
     *
     * @param  array{headline?: string, bio?: ?string, expertise_topics?: array<string>, career_paths?: ?array<string>, skills?: ?array<string>, help_topics?: array<string>, preferred_request_types?: ?array<string>, response_expectation_text?: ?string, office_hours_text?: ?string, max_pending_requests?: int, max_monthly_accepts?: ?int}  $data
     *
     * @throws AuthorizationException
     */
    public function execute(User $user, MentorProfile $mentorProfile, array $data): MentorProfile
    {
        Gate::forUser($user)->authorize('update', $mentorProfile);

        $allowedFields = [
            'headline',
            'bio',
            'expertise_topics',
            'career_paths',
            'skills',
            'help_topics',
            'preferred_request_types',
            'response_expectation_text',
            'office_hours_text',
            'max_pending_requests',
            'max_monthly_accepts',
            'mentor_visibility',
            'availability_status',
        ];

        $mentorProfile->update(array_intersect_key($data, array_flip($allowedFields)));

        return $mentorProfile->fresh();
    }
}
