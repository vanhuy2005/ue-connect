<?php

namespace Tests\Feature;

use App\Actions\Community\ReviewCommunitySuggestionAction;
use App\Enums\CommunityStatus;
use App\Enums\CommunitySuggestionStatus;
use App\Models\AuditLog;
use App\Models\Community;
use App\Models\CommunitySuggestion;
use App\Services\AuditService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;
use Mockery;
use Tests\Feature\Concerns\BuildsCommunityFixtures;
use Tests\TestCase;

class CommunitySuggestionTest extends TestCase
{
    use BuildsCommunityFixtures;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->instance(AuditService::class, Mockery::mock(AuditService::class, function ($m) {
            $m->shouldReceive('log')->andReturn(new AuditLog);
        }));
    }

    public function test_user_can_submit_community_suggestion_with_unified_fields(): void
    {
        $user = $this->createActiveUser();

        Volt::actingAs($user)
            ->test('pages.app.communities')
            ->set('suggestName', 'Physics Enthusiasts')
            ->set('suggestType', 'interest_group')
            ->set('suggestJoinPolicy', 'open')
            ->set('suggestVisibility', 'public')
            ->set('suggestPurpose', 'We discuss classical and quantum mechanics.')
            ->set('suggestTargetMembers', 'Physics majors and hobbyists')
            ->set('suggestRules', 'Be respectful and cite sources.')
            ->call('submitSuggestion');

        $this->assertDatabaseHas('community_suggestions', [
            'submitted_by' => $user->id,
            'suggested_name' => 'Physics Enthusiasts',
            'community_type' => 'interest_group',
            'join_policy' => 'open',
            'visibility' => 'public',
            'purpose' => 'We discuss classical and quantum mechanics.',
            'target_members' => 'Physics majors and hobbyists',
            'rules' => 'Be respectful and cite sources.',
            'status' => CommunitySuggestionStatus::Submitted->value,
        ]);
    }

    public function test_admin_can_convert_suggestion_to_community_retaining_unified_fields(): void
    {
        $admin = $this->createAdminUser();
        $user = $this->createActiveUser();

        $suggestion = CommunitySuggestion::create([
            'submitted_by' => $user->id,
            'suggested_name' => 'Chemistry Lab',
            'community_type' => 'academic_group',
            'join_policy' => 'invite_only',
            'visibility' => 'private',
            'purpose' => 'Doing chemistry experiments virtually.',
            'target_members' => 'Chemistry students K48',
            'rules' => 'Wear virtual goggles.',
            'proposed_owner_id' => $user->id,
            'status' => 'submitted',
        ]);

        app(ReviewCommunitySuggestionAction::class)->execute($admin, $suggestion, [
            'action' => 'create_community',
            'community_name' => 'Chemistry Lab Group',
        ]);

        $this->assertDatabaseHas('communities', [
            'name' => 'Chemistry Lab Group',
            'type' => 'academic_group',
            'join_policy' => 'invite_only',
            'visibility' => 'private',
            'description' => 'Doing chemistry experiments virtually.',
            'rules' => 'Wear virtual goggles.',
            'owner_id' => $user->id,
            'status' => CommunityStatus::Draft->value,
        ]);

        $community = Community::where('name', 'Chemistry Lab Group')->first();
        $this->assertEquals('Chemistry students K48', $community->settings['target_members'] ?? null);

        $this->assertDatabaseHas('community_members', [
            'community_id' => $community->id,
            'user_id' => $user->id,
            'role' => 'owner',
            'status' => 'active',
        ]);
    }

    public function test_admin_can_approve_suggestion_converting_it_to_community_with_proposer_fallback(): void
    {
        $admin = $this->createAdminUser();
        $user = $this->createActiveUser();

        $suggestion = CommunitySuggestion::create([
            'submitted_by' => $user->id,
            'suggested_name' => 'Biology Explorers',
            'community_type' => 'academic_group',
            'join_policy' => 'approval_required',
            'visibility' => 'public',
            'purpose' => 'Explore biology.',
            'target_members' => 'All students',
            'proposed_owner_id' => null, // Test fallback to submitted_by
            'status' => 'submitted',
        ]);

        app(ReviewCommunitySuggestionAction::class)->execute($admin, $suggestion, [
            'action' => 'approve',
            'reason' => 'Approved and converted to community',
        ]);

        $this->assertDatabaseHas('communities', [
            'name' => 'Biology Explorers',
            'type' => 'academic_group',
            'owner_id' => $user->id, // Should fall back to submitted_by
            'status' => CommunityStatus::Draft->value,
        ]);

        $community = Community::where('name', 'Biology Explorers')->first();

        $this->assertDatabaseHas('community_members', [
            'community_id' => $community->id,
            'user_id' => $user->id,
            'role' => 'owner',
            'status' => 'active',
        ]);

        $this->assertEquals(CommunitySuggestionStatus::ConvertedToCommunity, $suggestion->fresh()->status);
    }

    public function test_review_action_sends_notification_on_need_more_information(): void
    {
        $admin = $this->createAdminUser();
        $user = $this->createActiveUser();

        $suggestion = CommunitySuggestion::create([
            'submitted_by' => $user->id,
            'suggested_name' => 'Math Club',
            'community_type' => 'academic_group',
            'join_policy' => 'approval_required',
            'visibility' => 'public',
            'purpose' => 'We solve math puzzles.',
            'target_members' => 'Math majors',
            'status' => 'submitted',
        ]);

        app(ReviewCommunitySuggestionAction::class)->execute($admin, $suggestion, [
            'action' => 'need_more_information',
            'instruction' => 'Please explain target members in more detail.',
        ]);

        $this->assertEquals(CommunitySuggestionStatus::NeedMoreInformation, $suggestion->fresh()->status);
        $this->assertCount(1, $user->notifications);
        $notification = $user->notifications->first();
        $this->assertEquals('community_suggestion_reviewed', $notification->data['type']);
        $this->assertEquals('need_more_information', $notification->data['status']);
        $this->assertStringContainsString('Please explain target members', $notification->data['body']);
    }

    public function test_user_can_resubmit_suggestion_via_livewire(): void
    {
        $user = $this->createActiveUser();

        $suggestion = CommunitySuggestion::create([
            'submitted_by' => $user->id,
            'suggested_name' => 'Math Club',
            'community_type' => 'academic_group',
            'join_policy' => 'approval_required',
            'visibility' => 'public',
            'purpose' => 'We solve math puzzles.',
            'target_members' => 'Math majors',
            'status' => 'need_more_information',
            'admin_instruction' => 'Provide more details.',
        ]);

        Volt::actingAs($user)
            ->test('pages.app.communities')
            ->call('editSuggestion', $suggestion->id)
            ->assertSet('editingSuggestionId', $suggestion->id)
            ->assertSet('suggestName', 'Math Club')
            ->set('suggestTargetMembers', 'Math majors K47 and K48')
            ->call('submitSuggestion');

        $this->assertDatabaseHas('community_suggestions', [
            'id' => $suggestion->id,
            'target_members' => 'Math majors K47 and K48',
            'status' => CommunitySuggestionStatus::Submitted->value,
        ]);
    }
}
