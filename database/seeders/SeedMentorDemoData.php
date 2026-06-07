<?php

namespace Database\Seeders;

use App\Enums\AccountStatus;
use App\Enums\IdentityType;
use App\Enums\MentorAccessStatus;
use App\Enums\MentorAvailabilityStatus;
use App\Enums\MentorRequestStatus;
use App\Enums\MentorUrgency;
use App\Models\AcademicProgram;
use App\Models\AdvisorProfile;
use App\Models\AlumniProfile;
use App\Models\Faculty;
use App\Models\MentorAccessRequest;
use App\Models\MentorProfile;
use App\Models\MentorRequest;
use App\Models\Profile;
use App\Models\StudentProfile;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;

class SeedMentorDemoData extends Seeder
{
    private const PASSWORD = 'password';

    private User $adminMentor;

    public function run(): void
    {
        $this->setupAdminAsMentor();

        $this->createMentorsWithoutRegistration();
        $this->createStudentsWithoutRequests();
        $this->createStudentsWithRequests();
    }

    private function setupAdminAsMentor(): void
    {
        $admin = User::where('email', 'admin@teacher.hcmue.edu.vn')->firstOrFail();

        Permission::findOrCreate('mentor_access', 'web');
        $admin->givePermissionTo('mentor_access');

        MentorAccessRequest::updateOrCreate(
            ['user_id' => $admin->id, 'requested_role_context' => 'teacher'],
            [
                'status' => MentorAccessStatus::Approved,
                'motivation' => 'Admin mentor để hỗ trợ sinh viên trong chương trình cố vấn.',
                'experience_summary' => 'Giảng viên hướng dẫn nhiều sinh viên nghiên cứu và thực tập.',
                'expertise_topics' => ['Hướng dẫn học tập', 'Nghiên cứu khoa học', 'Định hướng nghề nghiệp'],
                'help_topics' => ['Chọn đề tài', 'Phương pháp học', 'Kỹ năng mềm'],
                'career_paths' => ['Giáo dục', 'Nghiên cứu'],
                'headline' => 'Admin mentor hỗ trợ định hướng học tập và nghiên cứu',
                'bio' => 'Với kinh nghiệm giảng dạy và hướng dẫn sinh viên, tôi sẵn sàng hỗ trợ các bạn trong học tập, nghiên cứu và định hướng nghề nghiệp.',
                'preferred_request_types' => ['academic_guidance', 'research_guidance', 'career_advice'],
                'response_expectation_text' => 'Phản hồi trong 1-2 ngày làm việc.',
                'policy_agreed' => true,
                'reviewed_by' => $admin->id,
                'reviewed_at' => now(),
                'review_reason' => 'Admin mentor cho demo.',
            ]
        );

        MentorProfile::updateOrCreate(
            ['user_id' => $admin->id],
            [
                'headline' => 'Admin mentor hỗ trợ định hướng học tập và nghiên cứu',
                'bio' => 'Với kinh nghiệm giảng dạy và hướng dẫn sinh viên, tôi sẵn sàng hỗ trợ các bạn trong học tập, nghiên cứu và định hướng nghề nghiệp.',
                'expertise_topics' => ['Hướng dẫn học tập', 'Nghiên cứu khoa học', 'Định hướng nghề nghiệp'],
                'help_topics' => ['Chọn đề tài', 'Phương pháp học', 'Kỹ năng mềm'],
                'career_paths' => ['Giáo dục', 'Nghiên cứu'],
                'skills' => ['Mentoring', 'Giảng dạy', 'Hướng dẫn nghiên cứu'],
                'preferred_request_types' => ['academic_guidance', 'research_guidance', 'career_advice'],
                'availability_status' => MentorAvailabilityStatus::Available,
                'mentor_visibility' => true,
                'is_public_ready' => true,
                'is_active' => true,
                'max_pending_requests' => 10,
                'response_expectation_text' => 'Phản hồi trong 1-2 ngày làm việc.',
                'office_hours_text' => 'Thứ 2-6, 9:00-17:00.',
                'approved_at' => now(),
                'approved_by' => $admin->id,
            ]
        );

        $this->adminMentor = $admin;
    }

    private function createMentorsWithoutRegistration(): void
    {
        $mentors = [
            ['Nguyễn Văn A - Cựu SV CNTT', 'demo.mentor.noapply1@gmail.com', 'alumni', 'cntt', 'cong-nghe-thong-tin'],
            ['Trần Thị B - Cựu SV Toán', 'demo.mentor.noapply2@gmail.com', 'alumni', 'toan-thong-ke', 'su-pham-toan-hoc'],
            ['Lê Văn C - Giảng viên Văn', 'demo.mentor.noapply3@teacher.hcmue.edu.vn', 'teacher', 'ngu-van', 'su-pham-ngu-van'],
            ['Phạm Thị D - Giảng viên Anh', 'demo.mentor.noapply4@teacher.hcmue.edu.vn', 'teacher', 'tieng-anh', 'su-pham-tieng-anh'],
            ['Hoàng Văn E - Cựu SV Lý', 'demo.mentor.pending1@gmail.com', 'alumni', 'vat-ly', 'su-pham-vat-ly'],
            ['Đỗ Thị F - Cựu SV Hóa', 'demo.mentor.pending2@gmail.com', 'alumni', 'hoa-hoc', 'su-pham-hoa-hoc'],
            ['Võ Văn G - Giảng viên Sử', 'demo.mentor.pending3@teacher.hcmue.edu.vn', 'teacher', 'lich-su', 'su-pham-lich-su'],
            ['Ngô Thị H - Giảng viên Địa', 'demo.mentor.pending4@teacher.hcmue.edu.vn', 'teacher', 'dia-ly', 'su-pham-dia-ly'],
        ];

        foreach ($mentors as [$name, $email, $roleType, $facultySlug, $programSlug]) {
            $identityType = $roleType === 'alumni' ? IdentityType::ALUMNI : IdentityType::TEACHER_ADVISOR;
            $user = $this->createUser($name, $email, $roleType, $identityType);
            $this->createTypeProfile($user, $roleType, $facultySlug, $programSlug);
        }
    }

    private function createStudentsWithoutRequests(): void
    {
        $students = [
            ['Mai Thị K - SV CNTT', 'demo.student.noreq1@student.hcmue.edu.vn', 'cntt', 'cong-nghe-thong-tin', 'DK1', 'DCT117C1', '511234001'],
            ['Lý Văn L - SV Toán', 'demo.student.noreq2@student.hcmue.edu.vn', 'toan-thong-ke', 'su-pham-toan-hoc', 'DK2', 'DSP117C2', '511234002'],
            ['Trương Thị M - SV Anh', 'demo.student.noreq3@student.hcmue.edu.vn', 'tieng-anh', 'su-pham-tieng-anh', 'DK3', 'DTA117C1', '511234003'],
            ['Đặng Văn N - SV Lý', 'demo.student.noreq4@student.hcmue.edu.vn', 'vat-ly', 'su-pham-vat-ly', 'DK1', 'DVL117C1', '511234004'],
        ];

        foreach ($students as [$name, $email, $facultySlug, $programSlug, $cohort, $class, $code]) {
            $user = $this->createUser($name, $email, 'student', IdentityType::CURRENT_STUDENT);
            $this->createStudentProfile($user, $facultySlug, $programSlug, $cohort, $class, $code);
        }
    }

    private function createStudentsWithRequests(): void
    {
        $students = [
            ['Bùi Thị P - SV CNTT', 'demo.student.req1@student.hcmue.edu.vn', 'cntt', 'cong-nghe-thong-tin', 'DK1', 'DCT117C2', '511234005', 'Hướng dẫn làm khóa luận tốt nghiệp', 'Em sắp làm khóa luận CNTT, cần mentor định hướng đề tài và phương pháp nghiên cứu.'],
            ['Hồ Văn Q - SV Toán', 'demo.student.req2@student.hcmue.edu.vn', 'toan-thong-ke', 'su-pham-toan-hoc', 'DK2', 'DSP117C3', '511234006', 'Định hướng nghề nghiệp sau tốt nghiệp', 'Em sắp ra trường, muốn được tư vấn về lộ trình nghề nghiệp và cơ hội việc làm.'],
            ['Dương Thị R - SV Anh', 'demo.student.req3@student.hcmue.edu.vn', 'tieng-anh', 'su-pham-tieng-anh', 'DK3', 'DTA117C2', '511234007', 'Phương pháp học tập hiệu quả', 'Em muốn cải thiện phương pháp học và kỹ năng nghiên cứu cho ngành Ngữ văn Anh.'],
            ['Vũ Văn S - SV Lý', 'demo.student.req4@student.hcmue.edu.vn', 'vat-ly', 'su-pham-vat-ly', 'DK1', 'DVL117C2', '511234008', 'Tư vấn chọn chuyên ngành sư phạm', 'Em phân vân giữa các chuyên ngành hẹp của sư phạm Vật lý, cần mentor góp ý.'],
        ];

        $mentorProfile = $this->adminMentor->mentorProfile()->firstOrFail();

        foreach ($students as [$name, $email, $facultySlug, $programSlug, $cohort, $class, $code, $topic, $context]) {
            $user = $this->createUser($name, $email, 'student', IdentityType::CURRENT_STUDENT);
            $this->createStudentProfile($user, $facultySlug, $programSlug, $cohort, $class, $code);

            MentorRequest::create([
                'student_id' => $user->id,
                'mentor_id' => $this->adminMentor->id,
                'mentor_profile_id' => $mentorProfile->id,
                'topic' => $topic,
                'goal' => 'Muốn nhận được lời khuyên và định hướng từ mentor.',
                'question' => $context,
                'urgency' => MentorUrgency::Normal,
                'status' => MentorRequestStatus::Submitted,
            ]);
        }
    }

    private function createUser(string $name, string $email, string $roleType, IdentityType $identityType): User
    {
        $user = User::updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => Hash::make(self::PASSWORD),
                'account_status' => AccountStatus::ACTIVE,
                'last_login_at' => now(),
                'intended_identity_type' => $identityType,
                'email_verified_at' => now(),
            ]
        );

        $user->syncRoles([$roleType]);

        return $user;
    }

    private function createProfile(User $user, string $roleType): Profile
    {
        return Profile::updateOrCreate(
            ['user_id' => $user->id],
            [
                'display_name' => $user->name,
                'bio' => 'Demo profile dùng để kiểm thử mentor flow UEConnect.',
                'role_type' => $roleType,
                'profile_status' => 'complete',
                'visibility' => 'public',
                'discoverable' => true,
                'profile_completed_at' => now(),
            ]
        );
    }

    private function createTypeProfile(User $user, string $roleType, string $facultySlug, string $programSlug): void
    {
        $profile = $this->createProfile($user, $roleType);
        $faculty = $this->faculty($facultySlug);

        if ($roleType === 'alumni') {
            $program = AcademicProgram::where('faculty_id', $faculty->id)
                ->where('slug', $programSlug)
                ->firstOrFail();

            AlumniProfile::updateOrCreate(
                ['profile_id' => $profile->id],
                [
                    'faculty_id' => $faculty->id,
                    'academic_program_id' => $program->id,
                    'cohort' => 'K40',
                    'graduation_year' => 2020,
                    'current_position' => 'Chuyên viên / Mentor Demo',
                    'current_organization' => 'Demo EdTech Studio',
                    'industry' => 'Education Technology',
                    'career_summary' => 'Cựu sinh viên HCMUE dùng cho kiểm thử mentor flow.',
                    'willing_to_mentor' => true,
                ]
            );
        } elseif ($roleType === 'teacher') {
            AdvisorProfile::updateOrCreate(
                ['profile_id' => $profile->id],
                [
                    'faculty_id' => $faculty->id,
                    'department' => 'Bộ môn chuyên ngành',
                    'title' => 'Giảng viên',
                    'office_location' => 'Cơ sở chính HCMUE',
                    'advising_areas' => 'Học vụ, nghiên cứu khoa học',
                ]
            );
        }
    }

    private function createStudentProfile(User $user, string $facultySlug, string $programSlug, string $cohort, string $className, string $studentCode): void
    {
        $profile = $this->createProfile($user, 'student');
        $faculty = $this->faculty($facultySlug);
        $program = AcademicProgram::where('faculty_id', $faculty->id)
            ->where('slug', $programSlug)
            ->firstOrFail();

        StudentProfile::updateOrCreate(
            ['profile_id' => $profile->id],
            [
                'student_code' => $studentCode,
                'faculty_id' => $faculty->id,
                'academic_program_id' => $program->id,
                'cohort' => $cohort,
                'current_year' => 3,
                'class_name' => $className,
                'learning_goals' => 'Tìm mentor phù hợp để được hướng dẫn học tập và nghề nghiệp.',
                'career_orientation' => 'Muốn hiểu rõ lộ trình học tập và nghề nghiệp sau khi ra trường.',
            ]
        );
    }

    private function faculty(string $slug): Faculty
    {
        return Faculty::where('slug', $slug)->firstOrFail();
    }
}
