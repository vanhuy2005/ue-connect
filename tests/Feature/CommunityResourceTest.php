<?php

namespace Tests\Feature;

use App\Actions\Community\ReviewCommunityResourceAction;
use App\Actions\Community\SubmitCommunityResourceAction;
use App\Enums\CommunityResourceStatus;
use App\Models\AuditLog;
use App\Models\Community;
use App\Models\CommunityMember;
use App\Models\CommunityResource;
use App\Models\MediaFile;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Mockery;
use Tests\Feature\Concerns\BuildsCommunityFixtures;
use Tests\TestCase;

class CommunityResourceTest extends TestCase
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

    // ─── SubmitCommunityResourceAction ────────────────────────────────────────

    public function test_active_member_can_submit_resource(): void
    {
        $user = User::factory()->create();
        $community = Community::factory()->active()->create();
        CommunityMember::factory()->active()->for($community)->for($user)->create();

        $resource = app(SubmitCommunityResourceAction::class)->execute($user, $community, [
            'title' => 'PHP Documentation',
            'resource_type' => 'link',
            'url' => 'https://php.net',
            'copyright_attestation' => true,
        ]);

        $this->assertDatabaseHas('community_resources', [
            'id' => $resource->id,
            'status' => CommunityResourceStatus::PendingReview->value,
            'submitted_by' => $user->id,
        ]);
    }

    public function test_non_member_cannot_submit_resource(): void
    {
        $user = User::factory()->create();
        $community = Community::factory()->active()->create();

        $this->expectException(ValidationException::class);
        app(SubmitCommunityResourceAction::class)->execute($user, $community, [
            'title' => 'Test',
            'resource_type' => 'link',
            'url' => 'https://php.net',
            'copyright_attestation' => true,
        ]);
    }

    public function test_owner_can_submit_file_resource_without_membership_row(): void
    {
        Storage::fake('public');

        $owner = User::factory()->create();
        $community = Community::factory()->active()->forOwner($owner)->create();
        $mediaFile = MediaFile::create([
            'owner_id' => $owner->id,
            'disk' => 'public',
            'path' => UploadedFile::fake()->create('guide.pdf', 100, 'application/pdf')->store('communities/'.$community->id.'/resources', 'public'),
            'original_name' => 'guide.pdf',
            'mime_type' => 'application/pdf',
            'extension' => 'pdf',
            'size_bytes' => 102400,
            'visibility' => 'public',
            'file_category' => 'community_resource',
        ]);

        $resource = app(SubmitCommunityResourceAction::class)->execute($owner, $community, [
            'title' => 'Owner Guide',
            'resource_type' => 'document',
            'file_id' => $mediaFile->id,
            'copyright_attestation' => true,
        ]);

        $this->assertDatabaseHas('community_resources', [
            'id' => $resource->id,
            'file_id' => $mediaFile->id,
            'submitted_by' => $owner->id,
        ]);
    }

    public function test_active_member_can_submit_file_resource(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $community = Community::factory()->active()->create();
        CommunityMember::factory()->active()->for($community)->for($user)->create();
        $mediaFile = MediaFile::create([
            'owner_id' => $user->id,
            'disk' => 'public',
            'path' => UploadedFile::fake()->create('template.docx', 80, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document')->store('communities/'.$community->id.'/resources', 'public'),
            'original_name' => 'template.docx',
            'mime_type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'extension' => 'docx',
            'size_bytes' => 81920,
            'visibility' => 'public',
            'file_category' => 'community_resource',
        ]);

        $resource = app(SubmitCommunityResourceAction::class)->execute($user, $community, [
            'title' => 'Template nhập môn',
            'resource_type' => 'template',
            'file_id' => $mediaFile->id,
            'copyright_attestation' => true,
        ]);

        $this->assertSame($mediaFile->id, $resource->file_id);
        $this->assertSame('template', $resource->resource_type->value);
    }

    public function test_published_file_resource_keeps_media_file_relation(): void
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

        $resource = CommunityResource::query()->where('title', 'Club Guide')->firstOrFail();

        $this->assertTrue($resource->mediaFile->is($mediaFile));
        $this->assertSame('club-guide.pdf', $resource->mediaFile->original_name);
    }

    public function test_submitting_link_resource_without_url_fails(): void
    {
        $user = User::factory()->create();
        $community = Community::factory()->active()->create();
        CommunityMember::factory()->active()->for($community)->for($user)->create();

        $this->expectException(ValidationException::class);
        app(SubmitCommunityResourceAction::class)->execute($user, $community, [
            'title' => 'Missing URL',
            'resource_type' => 'link',
            'copyright_attestation' => true,
        ]);
    }

    public function test_submitting_resource_without_copyright_attestation_fails(): void
    {
        $user = User::factory()->create();
        $community = Community::factory()->active()->create();
        CommunityMember::factory()->active()->for($community)->for($user)->create();

        $this->expectException(ValidationException::class);
        app(SubmitCommunityResourceAction::class)->execute($user, $community, [
            'title' => 'Test',
            'resource_type' => 'link',
            'url' => 'https://php.net',
            'copyright_attestation' => false,
        ]);
    }

    // ─── ReviewCommunityResourceAction ────────────────────────────────────────

    public function test_admin_can_approve_pending_resource(): void
    {
        $admin = User::factory()->create();
        $resource = CommunityResource::factory()->create([
            'status' => CommunityResourceStatus::PendingReview->value,
        ]);

        app(ReviewCommunityResourceAction::class)->execute($admin, $resource, ['action' => 'approve']);

        $this->assertDatabaseHas('community_resources', [
            'id' => $resource->id,
            'status' => CommunityResourceStatus::Published->value,
        ]);
    }

    public function test_admin_can_reject_pending_resource_with_reason(): void
    {
        $admin = User::factory()->create();
        $resource = CommunityResource::factory()->create([
            'status' => CommunityResourceStatus::PendingReview->value,
        ]);

        app(ReviewCommunityResourceAction::class)->execute($admin, $resource, [
            'action' => 'reject',
            'reason' => 'Nội dung vi phạm quy định.',
        ]);

        $this->assertDatabaseHas('community_resources', [
            'id' => $resource->id,
            'status' => CommunityResourceStatus::Rejected->value,
        ]);
    }

    public function test_rejecting_resource_requires_reason(): void
    {
        $admin = User::factory()->create();
        $resource = CommunityResource::factory()->create([
            'status' => CommunityResourceStatus::PendingReview->value,
        ]);

        $this->expectException(ValidationException::class);
        app(ReviewCommunityResourceAction::class)->execute($admin, $resource, ['action' => 'reject']);
    }

    public function test_cannot_review_already_published_resource(): void
    {
        $admin = User::factory()->create();
        $resource = CommunityResource::factory()->create([
            'status' => CommunityResourceStatus::Published->value,
        ]);

        $this->expectException(ValidationException::class);
        app(ReviewCommunityResourceAction::class)->execute($admin, $resource, ['action' => 'approve']);
    }
}
