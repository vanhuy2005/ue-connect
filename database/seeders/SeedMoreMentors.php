<?php

namespace Database\Seeders;

use App\Enums\AccountStatus;
use App\Enums\MentorAccessStatus;
use App\Enums\MentorAvailabilityStatus;
use App\Models\MentorAccessRequest;
use App\Models\MentorProfile;
use App\Models\User;
use Illuminate\Database\Seeder;

class SeedMoreMentors extends Seeder
{
    public function run(): void
    {
        $manager = User::where('email', 'mentor.manager@teacher.hcmue.edu.vn')->firstOrFail();

        $mentors = [
            [
                'name' => 'Lê Hoàng Frontend',
                'email' => 'lehoang.frontend@gmail.com',
                'headline' => 'Senior Frontend Engineer - React, Vue, UI/UX',
                'bio' => '5 năm kinh nghiệm phát triển frontend, đã làm việc tại các công ty product. Muốn giúp sinh viên định hướng lộ trình học frontend hiệu quả.',
                'expertise_topics' => ['React', 'Vue.js', 'UI/UX', 'TypeScript'],
                'help_topics' => ['Lộ trình học Frontend', 'Chuẩn bị phỏng vấn', 'Xây dựng portfolio'],
                'availability' => MentorAvailabilityStatus::Available,
            ],
            [
                'name' => 'Trần Minh Backend',
                'email' => 'tranminh.backend@gmail.com',
                'headline' => 'Backend Engineer - Laravel, Node.js, System Design',
                'bio' => 'Kỹ sư backend với 6 năm kinh nghiệm, chuyên sâu về Laravel và Node.js. Đã từng mentor cho nhiều bạn fresher và intern.',
                'expertise_topics' => ['Laravel', 'Node.js', 'System Design', 'Database'],
                'help_topics' => ['Thiết kế API', 'Tối ưu database', 'Kiến trúc hệ thống'],
                'availability' => MentorAvailabilityStatus::Available,
            ],
            [
                'name' => 'Phạm Ngọc Data',
                'email' => 'phamngoc.data@gmail.com',
                'headline' => 'Data Scientist - Machine Learning, Python, Analytics',
                'bio' => 'Data Scientist với 4 năm kinh nghiệm trong lĩnh vực ML và phân tích dữ liệu. Sẵn sàng hướng dẫn sinh viên ngành data.',
                'expertise_topics' => ['Python', 'Machine Learning', 'Data Analysis', 'SQL'],
                'help_topics' => ['Định hướng ngành Data', 'Xây dựng project ML', 'Ôn thi phỏng vấn'],
                'availability' => MentorAvailabilityStatus::Available,
            ],
            [
                'name' => 'Nguyễn Hữu DevOps',
                'email' => 'nguyenhuu.devops@gmail.com',
                'headline' => 'DevOps Engineer - AWS, Docker, CI/CD, Kubernetes',
                'bio' => 'DevOps Engineer với 5 năm kinh nghiệm triển khai hạ tầng cloud. Muốn chia sẻ kiến thức về DevOps và cloud computing.',
                'expertise_topics' => ['AWS', 'Docker', 'Kubernetes', 'CI/CD'],
                'help_topics' => ['Bắt đầu với DevOps', 'Triển khai ứng dụng', 'Quản lý hạ tầng'],
                'availability' => MentorAvailabilityStatus::Available,
            ],
            [
                'name' => 'Đặng Thùy Mobile',
                'email' => 'dangthuy.mobile@gmail.com',
                'headline' => 'Mobile Developer - Flutter, React Native, iOS, Android',
                'bio' => 'Mobile developer với 4 năm kinh nghiệm phát triển cả native và cross-platform. Đam mê hướng dẫn sinh viên mới vào ngành mobile.',
                'expertise_topics' => ['Flutter', 'React Native', 'iOS', 'Android'],
                'help_topics' => ['Lộ trình học Mobile', 'Xây dựng app đầu tay', 'Xu hướng mobile'],
                'availability' => MentorAvailabilityStatus::Available,
            ],
        ];

        foreach ($mentors as $data) {
            $user = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'password' => bcrypt('password'),
                    'email_verified_at' => now(),
                    'account_status' => AccountStatus::ACTIVE,
                ]
            );

            if ($user->account_status !== AccountStatus::ACTIVE) {
                $user->update(['account_status' => AccountStatus::ACTIVE]);
            }

            if (! $user->hasApprovedMentorAccess()) {
                MentorAccessRequest::updateOrCreate(
                    ['user_id' => $user->id],
                    [
                        'requested_role_context' => 'alumni',
                        'status' => MentorAccessStatus::Approved,
                        'motivation' => 'Tôi muốn chia sẻ kinh nghiệm chuyên môn với sinh viên HCMUE.',
                        'experience_summary' => 'Có kinh nghiệm làm việc thực tế trong lĩnh vực.',
                        'expertise_topics' => $data['expertise_topics'],
                        'career_paths' => ['Công nghệ thông tin'],
                        'reviewed_by' => $manager->id,
                        'reviewed_at' => now(),
                        'review_reason' => 'Approved via seed.',
                    ]
                );
                $user->givePermissionTo('mentor_access');
            }

            MentorProfile::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'headline' => $data['headline'],
                    'bio' => $data['bio'],
                    'expertise_topics' => $data['expertise_topics'],
                    'career_paths' => ['Công nghệ thông tin', 'Phát triển phần mềm'],
                    'skills' => ['Mentoring', 'Technical guidance', 'Career coaching'],
                    'help_topics' => $data['help_topics'],
                    'preferred_request_types' => ['Định hướng nghề nghiệp', 'Review code/project', 'Lộ trình học tập'],
                    'availability_status' => $data['availability'],
                    'mentor_visibility' => true,
                    'is_active' => true,
                    'is_public_ready' => true,
                    'max_pending_requests' => 5,
                    'max_monthly_accepts' => 8,
                    'response_expectation_text' => 'Phản hồi trong 2-3 ngày làm việc.',
                    'office_hours_text' => 'Tối thứ 3 & thứ 5, 20:00-21:30.',
                    'approved_at' => now(),
                    'approved_by' => $manager->id,
                ]
            );

            $this->command->info("Seeded mentor: {$data['name']} <{$data['email']}>");
        }
    }
}
