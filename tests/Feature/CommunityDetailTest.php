<?php

namespace Tests\Feature;

use App\Models\Community;
use App\Models\CommunityJoinRequest;
use App\Models\CommunityMember;
use App\Models\CommunityResource;
use App\Models\MediaFile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Volt\Volt;
use Tests\Feature\Concerns\BuildsCommunityFixtures;
use Tests\TestCase;

class CommunityDetailTest extends TestCase
{
    use BuildsCommunityFixtures;
    use RefreshDatabase;

    public function test_user_can_view_public_community_detail(): void
    {
        $user = $this->createActiveUser();
        $community = Community::factory()->active()->create([
            'name' => 'IT Club',
            'visibility' => 'public',
        ]);

        $response = $this->actingAs($user)->get(route('community.show', $community));

        $response->assertStatus(200);
        $response->assertSeeLivewire('pages.app.community-show');
    }

    public function test_non_member_cannot_view_private_community_content(): void
    {
        $user = $this->createActiveUser();
        $community = Community::factory()->active()->create([
            'name' => 'Secret Society',
            'visibility' => 'private',
        ]);

        $response = $this->actingAs($user)->get(route('community.show', $community));

        $response->assertStatus(403);
    }

    public function test_member_can_view_private_community_detail(): void
    {
        $user = $this->createActiveUser();
        $community = Community::factory()->active()->create([
            'name' => 'Secret Society',
            'visibility' => 'private',
        ]);

        CommunityMember::factory()->active()->for($community)->for($user)->create();

        $response = $this->actingAs($user)->get(route('community.show', $community));

        $response->assertStatus(200);
        $response->assertSeeLivewire('pages.app.community-show');
    }

    public function test_community_page_contains_tabs(): void
    {
        $user = $this->createActiveUser();
        $community = Community::factory()->active()->create([
            'name' => 'Open Club',
            'visibility' => 'public',
        ]);

        CommunityMember::factory()->active()->for($community)->for($user)->create();

        $component = Volt::actingAs($user)
            ->test('pages.app.community-show', ['community' => $community]);

        $component->assertSee('Bảng tin')
            ->assertSee('Tài nguyên')
            ->assertSee('Thành viên')
            ->assertSee('Giới thiệu');
    }

    public function test_owner_is_automatically_an_active_member_and_can_interact(): void
    {
        $owner = $this->createActiveUser();
        $community = Community::factory()->active()->create([
            'name' => 'Owner Group',
            'owner_id' => $owner->id,
        ]);

        $this->assertTrue($owner->can('view', $community));
        $this->assertTrue($owner->can('createPost', $community));
        $this->assertTrue($owner->can('sendChat', $community));

        $component = Volt::actingAs($owner)
            ->test('pages.app.community-show', ['community' => $community]);

        $component->assertSee('Chủ sở hữu')
            ->assertDontSee('wire:click="openJoinModal"')
            ->assertDontSee('Gửi yêu cầu tham gia');
    }

    public function test_owner_can_update_community_settings_from_frontend(): void
    {
        $owner = $this->createActiveUser();
        $community = Community::factory()->draft()->forOwner($owner)->create([
            'name' => 'Draft Club',
            'join_policy' => 'approval_required',
            'visibility' => 'public',
        ]);

        Volt::actingAs($owner)
            ->test('pages.app.community-show', ['community' => $community])
            ->assertSee('Cài đặt')
            ->set('activeTab', 'settings')
            ->set('settingsName', 'Updated Club')
            ->set('settingsType', 'club')
            ->set('settingsJoinPolicy', 'open')
            ->set('settingsVisibility', 'restricted')
            ->set('settingsStatus', 'active')
            ->set('settingsRelatedFaculty', 'Khoa Công nghệ thông tin')
            ->set('settingsShortDescription', 'Mô tả ngắn mới')
            ->set('settingsDescription', 'Mô tả chi tiết mới cho cộng đồng.')
            ->set('settingsRules', 'Không spam.')
            ->call('saveSettings')
            ->assertHasNoErrors();

        $community->refresh();

        $this->assertSame('Updated Club', $community->name);
        $this->assertSame('active', $community->status->value);
        $this->assertSame('open', $community->join_policy->value);
        $this->assertSame('restricted', $community->visibility->value);
        $this->assertSame('Không spam.', $community->rules);
    }

    public function test_settings_tab_explains_visibility_status_and_join_policy_behavior(): void
    {
        $owner = $this->createActiveUser();
        $community = Community::factory()->active()->forOwner($owner)->create([
            'join_policy' => 'invite_only',
            'visibility' => 'private',
            'status' => 'active',
        ]);

        Volt::actingAs($owner)
            ->test('pages.app.community-show', ['community' => $community])
            ->set('activeTab', 'settings')
            ->assertSee('Preview hành vi sau khi lưu')
            ->assertSee('Không đưa cộng đồng ra trang khám phá')
            ->assertSee('Owner hoặc quản lý thêm thành viên bằng email')
            ->assertSee('Cộng đồng đang vận hành bình thường');
    }

    public function test_owner_can_add_member_by_email_to_private_invite_only_community(): void
    {
        $owner = $this->createActiveUser();
        $member = $this->createActiveUser();
        $member->update(['email' => 'new-member@hcmue.edu.vn']);
        $community = Community::factory()->active()->forOwner($owner)->create([
            'join_policy' => 'invite_only',
            'visibility' => 'private',
            'members_count' => 0,
        ]);

        Volt::actingAs($owner)
            ->test('pages.app.community-show', ['community' => $community])
            ->set('activeTab', 'members')
            ->set('memberEmailToAdd', $member->email)
            ->call('addMemberByEmail')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('community_members', [
            'community_id' => $community->id,
            'user_id' => $member->id,
            'role' => 'member',
            'status' => 'active',
        ]);

        $this->assertSame(1, $community->fresh()->members_count);
    }

    public function test_owner_cannot_manually_add_member_when_policy_is_admin_only(): void
    {
        $owner = $this->createActiveUser();
        $member = $this->createActiveUser();
        $member->update(['email' => 'admin-only-member@hcmue.edu.vn']);
        $community = Community::factory()->active()->forOwner($owner)->create([
            'join_policy' => 'admin_only',
        ]);

        Volt::actingAs($owner)
            ->test('pages.app.community-show', ['community' => $community])
            ->set('activeTab', 'members')
            ->set('memberEmailToAdd', $member->email)
            ->call('addMemberByEmail')
            ->assertDispatched('notify', type: 'error');

        $this->assertDatabaseMissing('community_members', [
            'community_id' => $community->id,
            'user_id' => $member->id,
        ]);
    }

    public function test_owner_can_review_pending_join_request_from_settings_tab(): void
    {
        $owner = $this->createActiveUser();
        $requester = $this->createActiveUser();
        $community = Community::factory()->active()->requiresApproval()->forOwner($owner)->create();
        $joinRequest = CommunityJoinRequest::factory()
            ->for($community)
            ->for($requester, 'user')
            ->pending()
            ->create(['join_reason' => 'Muốn tham gia học tập.']);

        Volt::actingAs($owner)
            ->test('pages.app.community-show', ['community' => $community])
            ->set('activeTab', 'settings')
            ->assertSee('Yêu cầu tham gia chờ duyệt')
            ->assertSee('Muốn tham gia học tập.')
            ->call('approveJoinRequest', $joinRequest->id)
            ->assertHasNoErrors();

        $this->assertDatabaseHas('community_join_requests', [
            'id' => $joinRequest->id,
            'status' => 'approved',
            'reviewed_by' => $owner->id,
        ]);

        $this->assertDatabaseHas('community_members', [
            'community_id' => $community->id,
            'user_id' => $requester->id,
            'status' => 'active',
        ]);
    }

    public function test_published_file_resource_renders_download_link_in_resources_tab(): void
    {
        Storage::fake('public');

        $owner = $this->createActiveUser();
        $community = Community::factory()->active()->forOwner($owner)->create();
        $mediaFile = MediaFile::create([
            'owner_id' => $owner->id,
            'disk' => 'public',
            'path' => UploadedFile::fake()->create('club-guide.pdf', 100, 'application/pdf')->store('communities/'.$community->id.'/resources', 'public'),
            'original_name' => 'club-guide.pdf',
            'mime_type' => 'application/pdf',
            'extension' => 'pdf',
            'size_bytes' => 102400,
            'visibility' => 'public',
            'file_category' => 'community_resource',
        ]);

        CommunityResource::factory()->published()->for($community)->for($owner, 'submitter')->create([
            'resource_type' => 'document',
            'url' => null,
            'file_id' => $mediaFile->id,
            'title' => 'Club Guide',
        ]);

        Volt::actingAs($owner)
            ->test('pages.app.community-show', ['community' => $community])
            ->set('activeTab', 'resources')
            ->assertSee('Club Guide')
            ->assertSee('Tải xuống');
    }

    public function test_owner_can_upload_community_cover_and_avatar(): void
    {
        Storage::fake('local');
        Storage::fake('r2_public');

        $owner = $this->createActiveUser();
        $community = Community::factory()->active()->forOwner($owner)->create();

        $coverFile = UploadedFile::fake()->image('cover.jpg', 1200, 400);
        $avatarFile = UploadedFile::fake()->image('avatar.png', 300, 300);

        Volt::actingAs($owner)
            ->test('pages.app.community-show', ['community' => $community])
            ->set('coverFile', $coverFile)
            ->set('avatarFile', $avatarFile)
            ->assertHasNoErrors();

        $community->refresh();

        $this->assertNotNull($community->cover()->first());
        $this->assertNotNull($community->avatar()->first());
    }
}
