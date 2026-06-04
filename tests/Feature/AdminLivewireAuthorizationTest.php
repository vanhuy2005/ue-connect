<?php

namespace Tests\Feature;

use App\Enums\AccountStatus;
use App\Enums\MentorAccessStatus;
use App\Models\Community;
use App\Models\MentorAccessRequest;
use App\Models\MentorProfile;
use App\Models\PermissionGrant;
use App\Models\Profile;
use App\Models\User;
use Database\Seeders\Reference\AccessControlReferenceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class AdminLivewireAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('db:seed', ['--class' => AccessControlReferenceSeeder::class]);
    }

    public function test_admin_shell_permission_does_not_grant_verification_review_pages(): void
    {
        $actor = $this->activeUser();
        $actor->givePermissionTo('manage_users');

        $this->actingAs($actor)
            ->get(route('admin.verifications.queue'))
            ->assertForbidden();
    }

    public function test_review_permission_does_not_grant_user_or_permission_mutation_pages(): void
    {
        $actor = $this->activeUser();
        $actor->givePermissionTo('review_verification');
        $target = $this->activeUser();

        $this->actingAs($actor)
            ->get(route('admin.users.show', $target))
            ->assertForbidden();

        $this->actingAs($actor)
            ->get(route('admin.permissions.index'))
            ->assertForbidden();
    }

    public function test_inactive_admin_cannot_enter_admin_shell_even_with_permissions(): void
    {
        $actor = $this->activeUser(AccountStatus::SUSPENDED);
        $actor->assignRole('admin');
        $actor->givePermissionTo('manage_users');

        $this->actingAs($actor)
            ->get(route('admin.dashboard'))
            ->assertRedirect(route('system.account-restricted'));
    }

    public function test_scoped_community_permission_does_not_apply_to_another_community(): void
    {
        $actor = $this->activeUser();
        $grantingAdmin = $this->activeUser();
        $firstCommunity = Community::factory()->active()->create();
        $secondCommunity = Community::factory()->active()->create();

        PermissionGrant::create([
            'user_id' => $actor->id,
            'permission_key' => 'manage_community_members',
            'scope_type' => 'community',
            'scope_id' => $firstCommunity->id,
            'granted_by' => $grantingAdmin->id,
            'reason' => 'Scoped manager for first community only.',
            'status' => 'active',
        ]);

        $this->assertTrue(Gate::forUser($actor)->allows('manageMember', $firstCommunity));
        $this->assertFalse(Gate::forUser($actor)->allows('manageMember', $secondCommunity));
    }

    public function test_mentor_access_requires_active_account_and_approved_access(): void
    {
        Permission::findOrCreate('mentor_access', 'web');

        $mentor = $this->activeUser();
        $mentor->givePermissionTo('mentor_access');
        MentorProfile::create($this->mentorProfileAttributes($mentor));

        $this->assertFalse($mentor->fresh()->isActiveMentor());

        MentorAccessRequest::create([
            'user_id' => $mentor->id,
            'requested_role_context' => 'alumni',
            'status' => MentorAccessStatus::Approved,
            'motivation' => 'Approved mentor access for authorization test.',
            'policy_agreed' => true,
            'reviewed_by' => $this->activeUser()->id,
            'reviewed_at' => now(),
        ]);

        $this->assertTrue($mentor->fresh()->isActiveMentor());

        $mentor->forceFill(['account_status' => AccountStatus::SUSPENDED])->save();

        $this->assertFalse($mentor->fresh()->isActiveMentor());
    }

    private function activeUser(AccountStatus $status = AccountStatus::ACTIVE): User
    {
        $user = User::factory()->create([
            'account_status' => $status,
        ]);

        Profile::create([
            'user_id' => $user->id,
            'display_name' => $user->name,
            'role_type' => 'student',
            'profile_status' => $status === AccountStatus::ACTIVE ? 'complete' : 'incomplete',
            'profile_completed_at' => $status === AccountStatus::ACTIVE ? now() : null,
        ]);

        return $user;
    }

    /**
     * @return array<string, mixed>
     */
    private function mentorProfileAttributes(User $mentor): array
    {
        return [
            'user_id' => $mentor->id,
            'headline' => 'Career mentor',
            'bio' => 'I help students prepare for internships.',
            'expertise_topics' => ['career', 'cv'],
            'help_topics' => ['internship', 'resume'],
            'preferred_request_types' => ['online'],
            'response_expectation_text' => 'Within 3 days',
            'availability_status' => 'available',
            'mentor_visibility' => true,
            'max_pending_requests' => 5,
            'is_active' => true,
            'is_public_ready' => true,
            'approved_at' => now(),
            'approved_by' => $this->activeUser()->id,
        ];
    }
}
