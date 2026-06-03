<?php

namespace Tests\Feature\Concerns;

use App\Actions\Media\AttachMediaToModelAction;
use App\Actions\Media\StoreTemporaryMediaAction;
use App\Enums\AccountStatus;
use App\Enums\MentorAvailabilityStatus;
use App\Enums\MentorRequestStatus;
use App\Enums\MentorUrgency;
use App\Models\MentorProfile;
use App\Models\MentorRequest;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

trait BuildsMentorFixtures
{
    protected function activeUser(string $roleType = 'student'): User
    {
        $user = User::factory()->create([
            'account_status' => AccountStatus::ACTIVE,
        ]);

        Profile::create([
            'user_id' => $user->id,
            'display_name' => $user->name,
            'role_type' => $roleType,
            'profile_status' => 'complete',
            'profile_completed_at' => now(),
        ]);

        Role::findOrCreate($roleType, 'web');
        $user->assignRole($roleType);

        return $user;
    }

    protected function adminUser(): User
    {
        Permission::findOrCreate('manage_mentor_access', 'web');
        $role = Role::findOrCreate('admin', 'web');
        $role->givePermissionTo('manage_mentor_access');

        $admin = $this->activeUser('advisor');
        $admin->assignRole('admin');

        return $admin;
    }

    protected function mentorProfile(?User $mentor = null, array $attributes = []): MentorProfile
    {
        $mentor ??= $this->activeUser('alumni');

        return MentorProfile::create(array_merge([
            'user_id' => $mentor->id,
            'headline' => 'Career mentor',
            'bio' => 'I help students prepare for internships.',
            'expertise_topics' => ['career', 'cv'],
            'help_topics' => ['internship'],
            'availability_status' => MentorAvailabilityStatus::Available,
            'mentor_visibility' => true,
            'max_pending_requests' => 5,
            'is_active' => true,
            'approved_at' => now(),
            'approved_by' => $this->adminUser()->id,
        ], $attributes));
    }

    protected function mentorRequest(User $student, MentorProfile $profile, array $attributes = []): MentorRequest
    {
        return MentorRequest::create(array_merge([
            'student_id' => $student->id,
            'mentor_id' => $profile->user_id,
            'mentor_profile_id' => $profile->id,
            'topic' => 'Career planning',
            'goal' => 'Prepare a career plan',
            'question' => 'How should I prepare for an internship?',
            'urgency' => MentorUrgency::Normal,
            'status' => MentorRequestStatus::Submitted,
        ], $attributes));
    }

    protected function attachAvatar(User $user): void
    {
        $profile = $user->profile()->firstOrFail();
        $media = app(StoreTemporaryMediaAction::class)->execute(
            $user,
            UploadedFile::fake()->image('avatar.jpg', 240, 240),
            'avatar',
            ['visibility' => 'public']
        );

        app(AttachMediaToModelAction::class)->execute($user, $profile, [$media->id], 'avatar');
    }
}
