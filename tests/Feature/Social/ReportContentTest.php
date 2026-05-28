<?php

namespace Tests\Feature\Social;

use App\Actions\Reports\CreateReport;
use App\Enums\AccountStatus;
use App\Enums\ReportReason;
use App\Enums\ReportStatus;
use App\Models\Comment;
use App\Models\Post;
use App\Models\Report;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Livewire\Volt\Volt;
use Tests\TestCase;

class ReportContentTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected User $otherUser;

    protected Post $post;

    protected Comment $comment;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed', ['--class' => 'RoleAndPermissionSeeder']);

        // Main User
        $this->user = User::factory()->create([
            'account_status' => AccountStatus::ACTIVE,
        ]);
        $this->user->assignRole('student');
        $this->user->profile()->create([
            'display_name' => 'Active Student',
            'role_type' => 'student',
            'profile_status' => 'complete',
        ]);

        // Other User
        $this->otherUser = User::factory()->create([
            'account_status' => AccountStatus::ACTIVE,
        ]);
        $this->otherUser->assignRole('student');
        $this->otherUser->profile()->create([
            'display_name' => 'Other Student',
            'role_type' => 'student',
            'profile_status' => 'complete',
        ]);

        // Target content
        $this->post = Post::factory()->create([
            'user_id' => $this->otherUser->id,
            'body' => 'Target post content.',
            'published_at' => now(),
        ]);

        $this->comment = Comment::factory()->create([
            'post_id' => $this->post->id,
            'user_id' => $this->otherUser->id,
            'body' => 'Target comment content.',
        ]);
    }

    public function test_user_can_report_post_via_action(): void
    {
        $action = resolve(CreateReport::class);

        $report = $action->execute($this->user, $this->post, [
            'reason' => ReportReason::SPAM->value,
            'description' => 'Unwanted commercial content.',
        ]);

        $this->assertInstanceOf(Report::class, $report);
        $this->assertEquals($this->user->id, $report->reporter_id);
        $this->assertEquals('post', $report->target_type);
        $this->assertEquals($this->post->id, $report->target_id);
        $this->assertEquals(ReportReason::SPAM, $report->reason);
        $this->assertEquals('Unwanted commercial content.', $report->description);
        $this->assertEquals(ReportStatus::PENDING, $report->status);
    }

    public function test_user_cannot_report_own_post(): void
    {
        $ownPost = Post::factory()->create([
            'user_id' => $this->user->id,
            'body' => 'My own post.',
        ]);

        $action = resolve(CreateReport::class);

        $this->expectException(AuthorizationException::class);

        $action->execute($this->user, $ownPost, [
            'reason' => ReportReason::SPAM->value,
        ]);
    }

    public function test_user_cannot_duplicate_pending_report(): void
    {
        $action = resolve(CreateReport::class);

        // First report is successful
        $action->execute($this->user, $this->post, [
            'reason' => ReportReason::SPAM->value,
        ]);

        // Second report should throw validation exception
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Bạn đã gửi báo cáo cho nội dung này và hệ thống đang xử lý.');

        $action->execute($this->user, $this->post, [
            'reason' => ReportReason::HARASSMENT->value,
        ]);
    }

    public function test_user_can_report_post_via_home_feed_component(): void
    {
        $this->actingAs($this->user);

        Volt::test('pages.app.home-feed')
            ->call('openReport', $this->post->id)
            ->assertSet('reportingPost.id', $this->post->id)
            ->assertSet('showReportModal', true)
            ->set('reportReason', 'harassment')
            ->set('reportDescription', 'Insulting remarks.')
            ->call('submitReport')
            ->assertHasNoErrors()
            ->assertSet('showReportModal', false)
            ->assertSet('feedbackMessage', 'Báo cáo của bạn đã được gửi. Cảm ơn bạn đã góp phần xây dựng cộng đồng HCMUE an toàn.');

        $this->assertDatabaseHas('reports', [
            'reporter_id' => $this->user->id,
            'target_type' => 'post',
            'target_id' => $this->post->id,
            'reason' => 'harassment',
            'description' => 'Insulting remarks.',
        ]);
    }

    public function test_user_can_report_comment_via_post_detail_component(): void
    {
        $this->actingAs($this->user);

        Volt::test('pages.app.post-detail', ['post' => $this->post])
            ->call('openCommentReport', $this->comment->id)
            ->assertSet('reportingComment.id', $this->comment->id)
            ->assertSet('showReportModal', true)
            ->set('reportReason', 'spam')
            ->set('reportDescription', 'Advertising links.')
            ->call('submitReport')
            ->assertHasNoErrors()
            ->assertSet('showReportModal', false)
            ->assertSet('feedbackMessage', 'Báo cáo của bạn đã được gửi. Cảm ơn bạn đã đóng góp xây dựng môi trường HCMUE an toàn.');

        $this->assertDatabaseHas('reports', [
            'reporter_id' => $this->user->id,
            'target_type' => 'comment',
            'target_id' => $this->comment->id,
            'reason' => 'spam',
            'description' => 'Advertising links.',
        ]);
    }
}
