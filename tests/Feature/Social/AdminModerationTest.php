<?php

namespace Tests\Feature\Social;

use App\Enums\AccountStatus;
use App\Enums\CommentStatus;
use App\Enums\PostStatus;
use App\Enums\ReportReason;
use App\Enums\ReportStatus;
use App\Models\Comment;
use App\Models\Post;
use App\Models\Report;
use App\Models\User;
use Database\Seeders\Reference\AccessControlReferenceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;
use Tests\TestCase;

class AdminModerationTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected User $normalUser;

    protected Post $post;

    protected Comment $comment;

    protected Report $postReport;

    protected Report $commentReport;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed', ['--class' => AccessControlReferenceSeeder::class]);

        // Admin User
        $this->adminUser = User::factory()->create([
            'account_status' => AccountStatus::ACTIVE,
        ]);
        $this->adminUser->assignRole('admin');
        $this->adminUser->profile()->create([
            'display_name' => 'Admin Moderator',
            'role_type' => 'student',
            'profile_status' => 'complete',
        ]);

        // Normal User
        $this->normalUser = User::factory()->create([
            'account_status' => AccountStatus::ACTIVE,
        ]);
        $this->normalUser->assignRole('student');
        $this->normalUser->profile()->create([
            'display_name' => 'Normal Student',
            'role_type' => 'student',
            'profile_status' => 'complete',
        ]);

        // Content creator
        $creator = User::factory()->create(['account_status' => AccountStatus::ACTIVE]);
        $creator->profile()->create([
            'display_name' => 'Creator User',
            'role_type' => 'student',
            'profile_status' => 'complete',
        ]);

        $this->post = Post::factory()->create([
            'user_id' => $creator->id,
            'body' => 'Violative post content.',
            'status' => PostStatus::PUBLISHED,
            'published_at' => now(),
        ]);

        $this->comment = Comment::factory()->create([
            'post_id' => $this->post->id,
            'user_id' => $creator->id,
            'body' => 'Violative comment content.',
            'status' => CommentStatus::PUBLISHED,
        ]);

        // Reports
        $this->postReport = Report::create([
            'reporter_id' => $this->normalUser->id,
            'target_type' => 'post',
            'target_id' => $this->post->id,
            'reason' => ReportReason::SPAM,
            'description' => 'Spamming posts.',
            'status' => ReportStatus::PENDING,
        ]);

        $this->commentReport = Report::create([
            'reporter_id' => $this->normalUser->id,
            'target_type' => 'comment',
            'target_id' => $this->comment->id,
            'reason' => ReportReason::HARASSMENT,
            'description' => 'Harassing comment.',
            'status' => ReportStatus::PENDING,
        ]);
    }

    public function test_non_admin_cannot_access_reports_queue_and_detail(): void
    {
        $this->actingAs($this->normalUser);

        $this->get(route('admin.reports.index'))
            ->assertStatus(403);

        $this->get(route('admin.reports.show', $this->postReport))
            ->assertStatus(403);
    }

    public function test_admin_can_access_reports_queue_and_detail(): void
    {
        $this->actingAs($this->adminUser);

        $this->get(route('admin.reports.index'))
            ->assertStatus(200);

        $this->get(route('admin.reports.show', $this->postReport))
            ->assertStatus(200);
    }

    public function test_admin_can_dismiss_report_idempotently(): void
    {
        $this->actingAs($this->adminUser);

        Volt::test('pages.admin.report-detail', ['report' => $this->postReport])
            ->call('dismissReport')
            ->assertSet('feedbackMessage', 'Đã bỏ qua báo cáo vi phạm thành công.');

        $this->assertDatabaseHas('reports', [
            'id' => $this->postReport->id,
            'status' => ReportStatus::DISMISSED->value,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'actor_id' => $this->adminUser->id,
            'action_key' => 'report_dismissed',
            'target_type' => 'report',
            'target_id' => $this->postReport->id,
        ]);

        // Re-call for idempotency check
        Volt::test('pages.admin.report-detail', ['report' => $this->postReport->fresh()])
            ->call('dismissReport')
            ->assertSet('feedbackMessage', 'Báo cáo này đã được bỏ qua trước đó.');
    }

    public function test_admin_can_hide_reported_post_idempotently(): void
    {
        $this->actingAs($this->adminUser);

        Volt::test('pages.admin.report-detail', ['report' => $this->postReport])
            ->call('hideTargetContent')
            ->assertSet('feedbackMessage', 'Đã ẩn nội dung vi phạm và cập nhật báo cáo thành công.');

        $this->assertEquals(PostStatus::HIDDEN_BY_MODERATION, $this->post->fresh()->status);
        $this->assertEquals(ReportStatus::ACTION_TAKEN, $this->postReport->fresh()->status);

        $this->assertDatabaseHas('audit_logs', [
            'actor_id' => $this->adminUser->id,
            'action_key' => 'target_hidden',
            'target_type' => 'post',
            'target_id' => $this->post->id,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'actor_id' => $this->adminUser->id,
            'action_key' => 'report_resolved_hide',
            'target_type' => 'report',
            'target_id' => $this->postReport->id,
        ]);

        // Re-call for idempotency check
        Volt::test('pages.admin.report-detail', ['report' => $this->postReport->fresh()])
            ->call('hideTargetContent')
            ->assertSet('feedbackMessage', 'Nội dung mục tiêu đã được ẩn trước đó.');
    }

    public function test_admin_can_hide_reported_comment_idempotently(): void
    {
        $this->actingAs($this->adminUser);

        Volt::test('pages.admin.report-detail', ['report' => $this->commentReport])
            ->call('hideTargetContent')
            ->assertSet('feedbackMessage', 'Đã ẩn nội dung vi phạm và cập nhật báo cáo thành công.');

        $this->assertEquals(CommentStatus::HIDDEN_BY_MODERATION, $this->comment->fresh()->status);
        $this->assertEquals(ReportStatus::ACTION_TAKEN, $this->commentReport->fresh()->status);

        $this->assertDatabaseHas('audit_logs', [
            'actor_id' => $this->adminUser->id,
            'action_key' => 'target_hidden',
            'target_type' => 'comment',
            'target_id' => $this->comment->id,
        ]);

        // Re-call for idempotency check
        Volt::test('pages.admin.report-detail', ['report' => $this->commentReport->fresh()])
            ->call('hideTargetContent')
            ->assertSet('feedbackMessage', 'Nội dung mục tiêu đã được ẩn trước đó.');
    }

    public function test_admin_handling_missing_target_safely(): void
    {
        $this->actingAs($this->adminUser);

        // Force delete the post target
        $this->post->delete();

        Volt::test('pages.admin.report-detail', ['report' => $this->postReport->fresh()])
            ->call('hideTargetContent')
            ->assertSet('feedbackMessage', 'Nội dung mục tiêu không tồn tại hoặc đã bị xóa trước đó. Đã cập nhật trạng thái báo cáo.');

        $this->assertEquals(ReportStatus::ACTION_TAKEN, $this->postReport->fresh()->status);
    }

    public function test_hidden_post_and_comment_are_filtered_from_standard_users(): void
    {
        $this->actingAs($this->normalUser);

        // Initially visible on feed component
        Volt::test('pages.app.home-feed')
            ->assertSee($this->post->body);

        // Visit post detail directly
        $this->actingAs($this->normalUser)
            ->get(route('posts.show', $this->post))
            ->assertStatus(200)
            ->assertSee($this->post->body);

        // Hide post via admin
        $this->post->status = PostStatus::HIDDEN_BY_MODERATION;
        $this->post->save();

        // Feed must not see hidden post
        Volt::test('pages.app.home-feed')
            ->assertDontSee($this->post->body);

        // Visiting detail should throw 403/Forbidden due to view policy
        $this->actingAs($this->normalUser)
            ->get(route('posts.show', $this->post))
            ->assertStatus(403);
    }
}
