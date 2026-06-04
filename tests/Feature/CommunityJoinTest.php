<?php

namespace Tests\Feature;

use App\Actions\Community\ApproveJoinRequestAction;
use App\Actions\Community\LeaveCommunityAction;
use App\Actions\Community\RejectJoinRequestAction;
use App\Actions\Community\RequestJoinCommunityAction;
use App\Enums\CommunityMemberStatus;
use App\Models\Community;
use App\Models\CommunityJoinRequest;
use App\Models\CommunityMember;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class CommunityJoinTest extends TestCase
{
    use RefreshDatabase;

    // ─── RequestJoinCommunityAction ────────────────────────────────────────────

    public function test_user_can_join_open_community_directly(): void
    {
        $user = User::factory()->create();
        $community = Community::factory()->openJoin()->create();

        app(RequestJoinCommunityAction::class)->execute($user, $community, []);

        $this->assertDatabaseHas('community_members', [
            'community_id' => $community->id,
            'user_id' => $user->id,
            'status' => CommunityMemberStatus::Active->value,
        ]);

        $this->assertDatabaseMissing('community_join_requests', [
            'community_id' => $community->id,
            'user_id' => $user->id,
        ]);
    }

    public function test_user_creates_pending_request_for_approval_required_community(): void
    {
        $user = User::factory()->create();
        $community = Community::factory()->requiresApproval()->create();

        app(RequestJoinCommunityAction::class)->execute($user, $community, ['join_reason' => 'I want to join.']);

        $this->assertDatabaseHas('community_join_requests', [
            'community_id' => $community->id,
            'user_id' => $user->id,
            'status' => 'pending',
        ]);

        $this->assertDatabaseMissing('community_members', [
            'community_id' => $community->id,
            'user_id' => $user->id,
            'status' => CommunityMemberStatus::Active->value,
        ]);
    }

    public function test_user_cannot_send_duplicate_join_request(): void
    {
        $user = User::factory()->create();
        $community = Community::factory()->requiresApproval()->create();

        app(RequestJoinCommunityAction::class)->execute($user, $community, []);

        $this->expectException(ValidationException::class);
        app(RequestJoinCommunityAction::class)->execute($user, $community, []);
    }

    public function test_existing_member_cannot_request_to_join_again(): void
    {
        $user = User::factory()->create();
        $community = Community::factory()->requiresApproval()->create();

        CommunityMember::factory()->active()->for($community)->for($user)->create();

        $this->expectException(ValidationException::class);
        app(RequestJoinCommunityAction::class)->execute($user, $community, []);
    }

    public function test_user_cannot_join_suspended_community(): void
    {
        $user = User::factory()->create();
        $community = Community::factory()->suspended()->openJoin()->create();

        $this->expectException(ValidationException::class);
        app(RequestJoinCommunityAction::class)->execute($user, $community, []);
    }

    // ─── ApproveJoinRequestAction ──────────────────────────────────────────────

    public function test_admin_can_approve_pending_join_request(): void
    {
        $admin = User::factory()->create();
        $user = User::factory()->create();
        $community = Community::factory()->requiresApproval()->create();
        $joinRequest = CommunityJoinRequest::factory()->for($community)->for($user)->create(['status' => 'pending']);

        app(ApproveJoinRequestAction::class)->execute($admin, $joinRequest);

        $this->assertDatabaseHas('community_join_requests', [
            'id' => $joinRequest->id,
            'status' => 'approved',
        ]);

        $this->assertDatabaseHas('community_members', [
            'community_id' => $community->id,
            'user_id' => $user->id,
            'status' => CommunityMemberStatus::Active->value,
        ]);
    }

    public function test_cannot_approve_already_processed_join_request(): void
    {
        $admin = User::factory()->create();
        $user = User::factory()->create();
        $community = Community::factory()->create();
        $joinRequest = CommunityJoinRequest::factory()->for($community)->for($user)->create(['status' => 'approved']);

        $this->expectException(ValidationException::class);
        app(ApproveJoinRequestAction::class)->execute($admin, $joinRequest);
    }

    // ─── RejectJoinRequestAction ───────────────────────────────────────────────

    public function test_admin_can_reject_pending_join_request_with_reason(): void
    {
        $admin = User::factory()->create();
        $user = User::factory()->create();
        $community = Community::factory()->create();
        $joinRequest = CommunityJoinRequest::factory()->for($community)->for($user)->create(['status' => 'pending']);

        app(RejectJoinRequestAction::class)->execute($admin, $joinRequest, 'Không đủ điều kiện.');

        $this->assertDatabaseHas('community_join_requests', [
            'id' => $joinRequest->id,
            'status' => 'rejected',
        ]);

        $this->assertDatabaseMissing('community_members', [
            'community_id' => $community->id,
            'user_id' => $user->id,
            'status' => CommunityMemberStatus::Active->value,
        ]);
    }

    public function test_rejecting_join_request_requires_reason(): void
    {
        $admin = User::factory()->create();
        $user = User::factory()->create();
        $community = Community::factory()->create();
        $joinRequest = CommunityJoinRequest::factory()->for($community)->for($user)->create(['status' => 'pending']);

        $this->expectException(ValidationException::class);
        app(RejectJoinRequestAction::class)->execute($admin, $joinRequest, '');
    }

    // ─── LeaveCommunityAction ─────────────────────────────────────────────────

    public function test_active_member_can_leave_community(): void
    {
        $user = User::factory()->create();
        $community = Community::factory()->create(['members_count' => 1]);
        CommunityMember::factory()->active()->for($community)->for($user)->create();

        app(LeaveCommunityAction::class)->execute($user, $community);

        $this->assertDatabaseHas('community_members', [
            'community_id' => $community->id,
            'user_id' => $user->id,
            'status' => 'left',
        ]);

        $this->assertDatabaseHas('communities', [
            'id' => $community->id,
            'members_count' => 0,
        ]);
    }

    public function test_non_member_cannot_leave_community(): void
    {
        $user = User::factory()->create();
        $community = Community::factory()->create();

        $this->expectException(ValidationException::class);
        app(LeaveCommunityAction::class)->execute($user, $community);
    }
}
