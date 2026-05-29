<?php

namespace Database\Seeders;

use App\Enums\AccountStatus;
use App\Enums\CommentStatus;
use App\Enums\PostStatus;
use App\Enums\PostVisibility;
use App\Enums\ReportReason;
use App\Enums\ReportStatus;
use App\Models\AcademicProgram;
use App\Models\AdvisorProfile;
use App\Models\Comment;
use App\Models\CommentLike;
use App\Models\Faculty;
use App\Models\Post;
use App\Models\PostLike;
use App\Models\PostSave;
use App\Models\Profile;
use App\Models\Report;
use App\Models\StudentProfile;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UatFeedSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 0. Resolve Faculty and AcademicPrograms
        $facultyMath = Faculty::where('slug', 'toan-thong-ke')->first();
        $programMath = AcademicProgram::where('slug', 'toan-hoc')->first();

        $facultyCs = Faculty::where('slug', 'cntt')->first();
        $programCs = AcademicProgram::where('slug', 'cong-nghe-thong-tin')->first();

        if (! $facultyMath || ! $facultyCs) {
            // Fallbacks in case seeder hasn't run yet
            $facultyMath = Faculty::create(['name' => 'Khoa Toán - Thống kê', 'slug' => 'toan-thong-ke', 'status' => 'active']);
            $programMath = AcademicProgram::create(['faculty_id' => $facultyMath->id, 'name' => 'Toán học', 'slug' => 'toan-hoc', 'status' => 'active']);

            $facultyCs = Faculty::create(['name' => 'Khoa Công nghệ Thông tin', 'slug' => 'cntt', 'status' => 'active']);
            $programCs = AcademicProgram::create(['faculty_id' => $facultyCs->id, 'name' => 'Công nghệ Thông tin', 'slug' => 'cong-nghe-thong-tin', 'status' => 'active']);
        }

        // 1. Create 3 Verified Active Users
        $users = [];

        // User 1: Nguyễn Thảo (Student)
        $user1 = User::updateOrCreate(
            ['email' => 'student.verified1@hcmue.edu.vn'],
            [
                'name' => 'Nguyễn Thảo',
                'password' => Hash::make('Password@123'),
                'email_verified_at' => now(),
                'account_status' => AccountStatus::ACTIVE,
            ]
        );
        $profile1 = Profile::updateOrCreate(
            ['user_id' => $user1->id],
            [
                'display_name' => 'Thảo Nguyễn',
                'role_type' => 'student',
                'bio' => 'Sinh viên năm 3 yêu thích Giải tích và Lập trình Python.',
                'profile_status' => 'complete',
            ]
        );
        StudentProfile::updateOrCreate(
            ['profile_id' => $profile1->id],
            [
                'student_code' => '49.01.104.089',
                'faculty_id' => $facultyMath->id,
                'academic_program_id' => $programMath->id,
                'cohort' => 'K49',
                'current_year' => 3,
                'class_name' => 'Toán A K49',
            ]
        );
        $users[] = $user1;

        // User 2: Trần Minh Hoàng (Student)
        $user2 = User::updateOrCreate(
            ['email' => 'student.verified2@hcmue.edu.vn'],
            [
                'name' => 'Trần Minh Hoàng',
                'password' => Hash::make('Password@123'),
                'email_verified_at' => now(),
                'account_status' => AccountStatus::ACTIVE,
            ]
        );
        $profile2 = Profile::updateOrCreate(
            ['user_id' => $user2->id],
            [
                'display_name' => 'Minh Hoàng',
                'role_type' => 'student',
                'bio' => 'Đam mê lập trình Web Laravel và React. Rất vui được kết nối!',
                'profile_status' => 'complete',
            ]
        );
        StudentProfile::updateOrCreate(
            ['profile_id' => $profile2->id],
            [
                'student_code' => '49.01.104.012',
                'faculty_id' => $facultyCs->id,
                'academic_program_id' => $programCs->id,
                'cohort' => 'K49',
                'current_year' => 3,
                'class_name' => 'CNTT B K49',
            ]
        );
        $users[] = $user2;

        // User 3: Thầy Lê Văn An (Advisor)
        $user3 = User::updateOrCreate(
            ['email' => 'teacher.verified1@hcmue.edu.vn'],
            [
                'name' => 'Thầy Lê Văn An',
                'password' => Hash::make('Password@123'),
                'email_verified_at' => now(),
                'account_status' => AccountStatus::ACTIVE,
            ]
        );
        $profile3 = Profile::updateOrCreate(
            ['user_id' => $user3->id],
            [
                'display_name' => 'Thầy Lê An',
                'role_type' => 'advisor',
                'bio' => 'Cố vấn học tập khoa CNTT. Sẵn sàng hỗ trợ học vụ và định hướng nghề nghiệp.',
                'profile_status' => 'complete',
            ]
        );
        AdvisorProfile::updateOrCreate(
            ['profile_id' => $profile3->id],
            [
                'faculty_id' => $facultyCs->id,
                'department' => 'Bộ môn Kỹ thuật Phần mềm',
                'title' => 'Giảng viên',
            ]
        );
        $users[] = $user3;

        // 2. Create 5 Mock Posts
        $post1 = Post::create([
            'user_id' => $user1->id,
            'body' => 'Chào cả nhà! Mình đang tổng hợp tài liệu tự học môn Giải tích 2. Có bạn nào K49 cần không nhỉ? Link tải driver tài liệu đầy đủ ở phần bình luận nhé 📚✨',
            'visibility' => PostVisibility::VERIFIED_USERS,
            'status' => PostStatus::PUBLISHED,
            'published_at' => now()->subHours(5),
        ]);

        $post2 = Post::create([
            'user_id' => $user2->id,
            'body' => 'Khoa CNTT chuẩn bị tổ chức buổi workshop về Laravel và xây dựng web app thực tế vào sáng thứ Bảy tuần này tại hội trường B. Có UEer nào quan tâm tham gia cùng nhóm mình không? Đăng ký trực tiếp qua cổng thông tin nhé!',
            'media_url' => 'https://images.unsplash.com/photo-1555066931-4365d14bab8c?w=800&auto=format&fit=crop&q=80',
            'visibility' => PostVisibility::VERIFIED_USERS,
            'status' => PostStatus::PUBLISHED,
            'published_at' => now()->subHours(3),
        ]);

        $post3 = Post::create([
            'user_id' => $user3->id,
            'body' => 'Thông báo học vụ quan trọng: Thời hạn đăng ký đề tài nghiên cứu khoa học cấp sinh viên đã được gia hạn đến hết ngày 15 tháng sau. Các nhóm gặp khó khăn trong việc tìm giáo viên hướng dẫn có thể liên hệ trực tiếp văn phòng khoa để được giới thiệu nhé.',
            'visibility' => PostVisibility::VERIFIED_USERS,
            'status' => PostStatus::PUBLISHED,
            'published_at' => now()->subHours(2),
        ]);

        $post4 = Post::create([
            'user_id' => $user1->id,
            'body' => 'Có ai đang ôn thi chuẩn đầu ra Tiếng Anh B1 của trường mình không ạ? Cho mình xin ít kinh nghiệm phần viết và nói với, lo lắng quá 😭',
            'visibility' => PostVisibility::VERIFIED_USERS,
            'status' => PostStatus::PUBLISHED,
            'published_at' => now()->subHour(),
        ]);

        $post5 = Post::create([
            'user_id' => $user2->id,
            'body' => '[Cảnh báo] Bài viết thử nghiệm về vi phạm quy chuẩn cộng đồng nhằm kiểm tra tính năng báo cáo vi phạm nội dung. Vui lòng bỏ qua bài viết này.',
            'visibility' => PostVisibility::VERIFIED_USERS,
            'status' => PostStatus::PUBLISHED,
            'published_at' => now()->subMinutes(10),
        ]);

        // 3. Create Comments and One-level Replies
        $comment1 = Comment::create([
            'post_id' => $post1->id,
            'user_id' => $user2->id,
            'body' => 'Tài liệu hay quá Thảo ơi! Đúng phần mình đang bị hổng kiến thức giải tích nhiều biến. Cảm ơn bạn rất nhiều nhé!',
            'status' => CommentStatus::PUBLISHED,
        ]);

        Comment::create([
            'post_id' => $post1->id,
            'user_id' => $user1->id,
            'parent_id' => $comment1->id,
            'body' => 'Không có gì đâu Hoàng, hy vọng tài liệu này giúp ích cho bạn trong kỳ thi sắp tới nhé!',
            'status' => CommentStatus::PUBLISHED,
        ]);

        $comment2 = Comment::create([
            'post_id' => $post3->id,
            'user_id' => $user1->id,
            'body' => 'Dạ thưa thầy, sinh viên năm nhất có được đăng ký tham gia nghiên cứu khoa học không hay phải từ năm hai ạ?',
            'status' => CommentStatus::PUBLISHED,
        ]);

        Comment::create([
            'post_id' => $post3->id,
            'user_id' => $user3->id,
            'parent_id' => $comment2->id,
            'body' => 'Năm nhất hoàn toàn có thể tham gia cùng các nhóm anh chị khóa trên để làm quen và học hỏi em nhé.',
            'status' => CommentStatus::PUBLISHED,
        ]);

        // 4. Create Post Likes and Saves
        PostLike::create(['post_id' => $post1->id, 'user_id' => $user2->id]);
        PostLike::create(['post_id' => $post1->id, 'user_id' => $user3->id]);
        PostLike::create(['post_id' => $post3->id, 'user_id' => $user1->id]);

        PostSave::create(['post_id' => $post1->id, 'user_id' => $user2->id]);
        PostSave::create(['post_id' => $post3->id, 'user_id' => $user1->id]);

        CommentLike::create(['comment_id' => $comment1->id, 'user_id' => $user1->id]);

        // 5. Create one sample report to verify safety queue integration
        Report::create([
            'reporter_id' => $user1->id,
            'target_type' => 'post',
            'target_id' => $post5->id,
            'reason' => ReportReason::SPAM,
            'description' => 'Đây là bài viết thử nghiệm báo cáo tin rác.',
            'status' => ReportStatus::PENDING,
        ]);
    }
}
