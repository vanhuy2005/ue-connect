<?php

namespace Tests\Feature;

use App\Actions\Community\ArchiveCommunityAction;
use App\Actions\Community\ReactivateCommunityAction;
use App\Actions\Community\RemoveCommunityMemberAction;
use App\Actions\Community\SuspendCommunityAction;
use App\Enums\CommunityStatus;
use App\Models\AuditLog;
use App\Models\Community;
use App\Models\CommunityMember;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Mockery;
use Tests\TestCase;

class CommunityModerationTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create();
        $this->instance(AuditService::class, Mockery::mock(AuditService::class, function ($m) {
            $m->shouldReceive('log')->andReturn(new AuditLog);
        }));
    }

    // ─── SuspendCommunityAction ───────────────────────────────────────────────

    public function test_admin_can_suspend_active_community(): void
    {
        $community = Community::factory()->active()->create();

        app(SuspendCommunityAction::class)->execute($this->admin, $community, [
            'reason' => 'Violates community standards due to repeated reports.',
            'safe_reason' => 'Cộng đồng đang được xem xét.',
            'notify_members' => false,
        ]);

        $this->assertDatabaseHas('communities', [
            'id' => $community->id,
            'status' => CommunityStatus::Suspended->value,
        ]);
    }

    public function test_suspending_community_requires_reason(): void
    {
        $community = Community::factory()->active()->create();

        $this->expectException(ValidationException::class);
        app(SuspendCommunityAction::class)->execute($this->admin, $community, [
            'reason' => '',
            'safe_reason' => '',
        ]);
    }

    public function test_cannot_suspend_already_suspended_community(): void
    {
        $community = Community::factory()->suspended()->create();

        $this->expectException(ValidationException::class);
        app(SuspendCommunityAction::class)->execute($this->admin, $community, [
            'reason' => 'Some reason here.',
            'safe_reason' => 'Some reason.',
        ]);
    }

    // ─── ReactivateCommunityAction ────────────────────────────────────────────

    public function test_admin_can_reactivate_suspended_community(): void
    {
        $community = Community::factory()->suspended()->create();

        app(ReactivateCommunityAction::class)->execute($this->admin, $community, 'Issue resolved.');

        $this->assertDatabaseHas('communities', [
            'id' => $community->id,
            'status' => CommunityStatus::Active->value,
        ]);
    }

    public function test_cannot_reactivate_non_suspended_community(): void
    {
        $community = Community::factory()->active()->create();

        $this->expectException(ValidationException::class);
        app(ReactivateCommunityAction::class)->execute($this->admin, $community, null);
    }

    // ─── ArchiveCommunityAction ───────────────────────────────────────────────

    public function test_admin_can_archive_active_community(): void
    {
        $community = Community::factory()->active()->create();

        app(ArchiveCommunityAction::class)->execute($this->admin, $community, 'Club dissolved.');

        $this->assertDatabaseHas('communities', [
            'id' => $community->id,
            'status' => CommunityStatus::Archived->value,
        ]);
    }

    public function test_archiving_community_requires_reason(): void
    {
        $community = Community::factory()->active()->create();

        $this->expectException(ValidationException::class);
        app(ArchiveCommunityAction::class)->execute($this->admin, $community, '');
    }

    public function test_cannot_archive_already_archived_community(): void
    {
        $community = Community::factory()->archived()->create();

        $this->expectException(ValidationException::class);
        app(ArchiveCommunityAction::class)->execute($this->admin, $community, 'Re-archive attempt.');
    }

    // ─── RemoveCommunityMemberAction ─────────────────────────────────────────

    public function test_admin_can_remove_active_member(): void
    {
        $targetUser = User::factory()->create();
        $community = Community::factory()->create(['members_count' => 1]);
        CommunityMember::factory()->active()->for($community)->for($targetUser)->create();

        app(RemoveCommunityMemberAction::class)->execute($this->admin, $community, $targetUser, 'Behavioural violation.');

        $this->assertDatabaseHas('community_members', [
            'community_id' => $community->id,
            'user_id' => $targetUser->id,
            'status' => 'removed',
        ]);

        $this->assertDatabaseHas('communities', [
            'id' => $community->id,
            'members_count' => 0,
        ]);
    }

    public function test_removing_member_requires_reason(): void
    {
        $targetUser = User::factory()->create();
        $community = Community::factory()->create();
        CommunityMember::factory()->active()->for($community)->for($targetUser)->create();

        $this->expectException(ValidationException::class);
        app(RemoveCommunityMemberAction::class)->execute($this->admin, $community, $targetUser, '');
    }

    public function test_cannot_remove_non_member(): void
    {
        $targetUser = User::factory()->create();
        $community = Community::factory()->create();

        $this->expectException(ValidationException::class);
        app(RemoveCommunityMemberAction::class)->execute($this->admin, $community, $targetUser, 'Reason here.');
    }
}
