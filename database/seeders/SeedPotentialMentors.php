<?php

namespace Database\Seeders;

use App\Enums\AccountStatus;
use App\Enums\IdentityType;
use App\Models\AcademicProgram;
use App\Models\AdvisorProfile;
use App\Models\AlumniProfile;
use App\Models\Faculty;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Seed 10 potential mentor accounts ready for mentor registration.
 *
 * Each account has:
 * - User (active, email verified)
 * - Profile (complete, public)
 * - AlumniProfile or AdvisorProfile
 *
 * No MentorAccessRequest or evidence media — the user will
 * register as a mentor through the UI form themselves.
 */
class SeedPotentialMentors extends Seeder
{
    private const PASSWORD = 'password';

    public function run(): void
    {
        $this->seedAlumniPotentialMentors();
        $this->seedTeacherPotentialMentors();
    }

    private function seedAlumniPotentialMentors(): void
    {
        $alumni = [
            ['Nguyễn Hoàng An - Cựu SV CNTT', 'potential.alumni1@gmail.com', 'cntt', 'cong-nghe-thong-tin', 'K42', 2022, 'Kỹ sư phần mềm', 'VNG Corporation', 'Công nghệ thông tin'],
            ['Trần Minh Khang - Cựu SV Toán', 'potential.alumni2@gmail.com', 'toan-thong-ke', 'toan-hoc', 'K41', 2021, 'Chuyên viên phân tích dữ liệu', 'FPT Software', 'Phân tích dữ liệu'],
            ['Lê Ngọc Trâm - Cựu SV Anh', 'potential.alumni3@gmail.com', 'tieng-anh', 'ngon-ngu-anh', 'K42', 2022, 'Biên dịch viên', 'Vietnam Translation Services', 'Ngôn ngữ'],
            ['Phạm Quốc Bảo - Cựu SV Lý', 'potential.alumni4@gmail.com', 'vat-ly', 'vat-ly-hoc', 'K40', 2020, 'Kỹ sư R&D', 'Samsung Vietnam', 'Công nghệ bán dẫn'],
            ['Đặng Thị Hồng - Cựu SV Hóa', 'potential.alumni5@gmail.com', 'hoa-hoc', 'hoa-hoc', 'K41', 2021, 'Chuyên viên kiểm nghiệm', 'Duy Tan Chemical', 'Hóa phân tích'],
        ];

        foreach ($alumni as [$name, $email, $facultySlug, $programSlug, $cohort, $gradYear, $position, $org, $industry]) {
            $user = $this->createUser($name, $email, 'alumni', IdentityType::ALUMNI);
            $this->createAlumniProfile($user, $facultySlug, $programSlug, $cohort, $gradYear, $position, $org, $industry);
        }
    }

    private function seedTeacherPotentialMentors(): void
    {
        $teachers = [
            ['ThS. Lê Văn Thành - Giảng viên CNTT', 'potential.teacher1@teacher.hcmue.edu.vn', 'cntt', 'Bộ môn Kỹ thuật Phần mềm', 'Giảng viên', 'Phát triển web, AI, cơ sở dữ liệu'],
            ['TS. Nguyễn Thị Phương - Giảng viên Văn', 'potential.teacher2@teacher.hcmue.edu.vn', 'ngu-van', 'Khoa Ngữ văn', 'Phó Giáo sư', 'Văn học Việt Nam, lý luận văn học'],
            ['ThS. Trần Đức Hải - Giảng viên Sử', 'potential.teacher3@teacher.hcmue.edu.vn', 'lich-su', 'Khoa Lịch sử', 'Giảng viên chính', 'Lịch sử Việt Nam, phương pháp dạy học lịch sử'],
            ['ThS. Hoàng Minh Tâm - Giảng viên Tâm lý', 'potential.teacher4@teacher.hcmue.edu.vn', 'tam-ly-hoc', 'Tâm lý học', 'Giảng viên', 'Tâm lý học đường, tham vấn học đường'],
            ['TS. Võ Ngọc Yến - Giảng viên Địa', 'potential.teacher5@teacher.hcmue.edu.vn', 'dia-ly', 'dia-ly-hoc', 'Giảng viên', 'Địa lý kinh tế, biến đổi khí hậu'],
        ];

        foreach ($teachers as [$name, $email, $facultySlug, $department, $title, $areas]) {
            $user = $this->createUser($name, $email, 'teacher', IdentityType::TEACHER_ADVISOR);
            $this->createAdvisorProfile($user, $facultySlug, $department, $title, $areas);
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
                'bio' => 'Tôi là '.$roleType.' HCMUE, sẵn sàng kết nối và hỗ trợ sinh viên.',
                'role_type' => $roleType,
                'profile_status' => 'complete',
                'visibility' => 'public',
                'discoverable' => true,
                'profile_completed_at' => now(),
            ]
        );
    }

    private function faculty(string $slug): Faculty
    {
        return Faculty::where('slug', $slug)->firstOrFail();
    }

    private function program(Faculty $faculty, string $slug): AcademicProgram
    {
        return AcademicProgram::where('faculty_id', $faculty->id)
            ->where('slug', $slug)
            ->firstOrFail();
    }

    private function createAlumniProfile(
        User $user,
        string $facultySlug,
        string $programSlug,
        string $cohort,
        int $gradYear,
        string $position,
        string $org,
        string $industry
    ): void {
        $profile = $this->createProfile($user, 'alumni');
        $faculty = $this->faculty($facultySlug);
        $program = $this->program($faculty, $programSlug);

        AlumniProfile::updateOrCreate(
            ['profile_id' => $profile->id],
            [
                'faculty_id' => $faculty->id,
                'academic_program_id' => $program->id,
                'cohort' => $cohort,
                'graduation_year' => $gradYear,
                'current_position' => $position,
                'current_organization' => $org,
                'industry' => $industry,
                'career_summary' => 'Cựu sinh viên HCMUE tốt nghiệp năm '.$gradYear.', hiện là '.$position.' tại '.$org.'.',
                'willing_to_mentor' => true,
            ]
        );
    }

    private function createAdvisorProfile(User $user, string $facultySlug, string $department, string $title, string $areas): void
    {
        $profile = $this->createProfile($user, 'teacher');
        $faculty = $this->faculty($facultySlug);

        AdvisorProfile::updateOrCreate(
            ['profile_id' => $profile->id],
            [
                'faculty_id' => $faculty->id,
                'department' => $department,
                'title' => $title,
                'office_location' => 'Cơ sở chính HCMUE',
                'advising_areas' => $areas,
            ]
        );
    }
}
