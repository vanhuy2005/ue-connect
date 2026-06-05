<?php

namespace Database\Seeders\Uat;

use App\Enums\CommentStatus;
use App\Enums\PostStatus;
use App\Enums\PostVisibility;
use App\Enums\ReportReason;
use App\Enums\ReportStatus;
use App\Models\Comment;
use App\Models\CommentLike;
use App\Models\Post;
use App\Models\PostLike;
use App\Models\PostSave;
use App\Models\Report;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UatFeedSeeder extends Seeder
{
    /** @var array<string, User> */
    private array $users = [];

    /** @var array<string, Post> */
    private array $posts = [];

    /** @var array<string, Comment> */
    private array $comments = [];

    public function run(): void
    {
        DB::transaction(function () {
            $this->resolveUsers();
            $this->seedPosts();
            $this->seedComments();
            $this->seedEngagement();
            $this->seedReports();
        });
    }

    private function resolveUsers(): void
    {
        foreach ([
            'student' => 'student@student.hcmue.edu.vn',
            'student2' => 'student2@student.hcmue.edu.vn',
            'student_math' => 'student.math@student.hcmue.edu.vn',
            'student_english' => 'student.english@student.hcmue.edu.vn',
            'teacher' => 'teacher.mentor@teacher.hcmue.edu.vn',
            'moderator' => 'moderator@teacher.hcmue.edu.vn',
        ] as $key => $email) {
            $this->users[$key] = User::where('email', $email)->firstOrFail();
        }
    }

    private function seedPosts(): void
    {
        $this->posts['math_resources'] = $this->post(
            'math_resources',
            $this->users['student_math'],
            'Chào cả nhà! Mình đang tổng hợp tài liệu tự học môn Giải tích 2. Có bạn nào K49 cần không nhỉ? Link tài liệu mình để ở phần bình luận nhé.',
            now()->subHours(5)
        );

        $this->posts['laravel_workshop'] = $this->post(
            'laravel_workshop',
            $this->users['student2'],
            'Khoa CNTT chuẩn bị tổ chức workshop Laravel và xây dựng web app thực tế vào sáng thứ Bảy tuần này. Có UEer nào quan tâm tham gia cùng nhóm mình không?',
            now()->subHours(3),
            'https://images.unsplash.com/photo-1555066931-4365d14bab8c?w=800&auto=format&fit=crop&q=80'
        );

        $this->posts['research_notice'] = $this->post(
            'research_notice',
            $this->users['teacher'],
            'Thông báo học vụ: thời hạn đăng ký đề tài nghiên cứu khoa học cấp sinh viên được gia hạn đến hết ngày 15 tháng sau.',
            now()->subHours(2)
        );

        $this->posts['english_exam'] = $this->post(
            'english_exam',
            $this->users['student_english'],
            'Có ai đang ôn chuẩn đầu ra tiếng Anh B1 không ạ? Cho mình xin kinh nghiệm phần viết và nói với.',
            now()->subHour()
        );

        $this->posts['report_demo'] = $this->post(
            'report_demo',
            $this->users['student2'],
            '[UAT] Bài viết thử nghiệm báo cáo vi phạm nội dung để kiểm tra moderation queue. Vui lòng bỏ qua bài này.',
            now()->subMinutes(10)
        );
    }

    private function seedComments(): void
    {
        $this->comments['math_reply'] = $this->comment(
            'math_reply',
            $this->posts['math_resources'],
            $this->users['student2'],
            'Tài liệu hay quá, đúng phần mình đang bị hổng kiến thức giải tích nhiều biến. Cảm ơn bạn nhiều nhé!'
        );

        $this->comments['math_reply_child'] = $this->comment(
            'math_reply_child',
            $this->posts['math_resources'],
            $this->users['student_math'],
            'Không có gì đâu, mình sẽ cập nhật thêm bài tập mẫu trước kỳ thi.',
            $this->comments['math_reply']
        );

        $this->comments['research_question'] = $this->comment(
            'research_question',
            $this->posts['research_notice'],
            $this->users['student'],
            'Dạ thưa thầy, sinh viên năm nhất có được tham gia nhóm nghiên cứu khoa học không ạ?'
        );

        $this->comments['research_answer'] = $this->comment(
            'research_answer',
            $this->posts['research_notice'],
            $this->users['teacher'],
            'Năm nhất có thể tham gia cùng nhóm anh chị khóa trên để làm quen và học hỏi em nhé.',
            $this->comments['research_question']
        );
    }

    private function seedEngagement(): void
    {
        foreach ([
            [$this->posts['math_resources'], $this->users['student2']],
            [$this->posts['math_resources'], $this->users['teacher']],
            [$this->posts['research_notice'], $this->users['student']],
        ] as [$post, $user]) {
            PostLike::updateOrCreate(['post_id' => $post->id, 'user_id' => $user->id]);
        }

        foreach ([
            [$this->posts['math_resources'], $this->users['student2']],
            [$this->posts['research_notice'], $this->users['student']],
        ] as [$post, $user]) {
            PostSave::updateOrCreate(['post_id' => $post->id, 'user_id' => $user->id]);
        }

        CommentLike::updateOrCreate([
            'comment_id' => $this->comments['math_reply']->id,
            'user_id' => $this->users['student_math']->id,
        ]);
    }

    private function seedReports(): void
    {
        Report::updateOrCreate(
            [
                'reporter_id' => $this->users['student']->id,
                'target_type' => 'post',
                'target_id' => $this->posts['report_demo']->id,
            ],
            [
                'reason' => ReportReason::SPAM,
                'description' => 'Bài viết UAT dùng để kiểm tra hàng đợi báo cáo spam.',
                'status' => ReportStatus::PENDING,
            ]
        );
    }

    private function post(string $seedKey, User $user, string $body, mixed $publishedAt, ?string $mediaUrl = null): Post
    {
        return Post::updateOrCreate(
            ['body' => '[seed:'.$seedKey.'] '.$body],
            [
                'user_id' => $user->id,
                'media_url' => $mediaUrl,
                'visibility' => PostVisibility::VERIFIED_USERS,
                'status' => PostStatus::PUBLISHED,
                'published_at' => $publishedAt,
            ]
        );
    }

    private function comment(string $seedKey, Post $post, User $user, string $body, ?Comment $parent = null): Comment
    {
        return Comment::updateOrCreate(
            [
                'post_id' => $post->id,
                'body' => '[seed:'.$seedKey.'] '.$body,
            ],
            [
                'user_id' => $user->id,
                'parent_id' => $parent?->id,
                'status' => CommentStatus::PUBLISHED,
            ]
        );
    }
}
