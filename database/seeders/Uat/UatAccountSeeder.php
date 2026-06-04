<?php

namespace Database\Seeders\Uat;

use App\Enums\AccountStatus;
use App\Enums\IdentityType;
use App\Models\AcademicProgram;
use App\Models\AdvisorProfile;
use App\Models\AlumniProfile;
use App\Models\Faculty;
use App\Models\Profile;
use App\Models\StudentProfile;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\PermissionRegistrar;

/**
 * Canonical UAT account seeder.
 *
 * Password for every UAT account: password
 */
class UatAccountSeeder extends Seeder
{
    private const PASSWORD = 'password';

    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        DB::transaction(function () {
            $this->seedAdminUsers();
            $this->seedStudents();
            $this->seedAlumni();
            $this->seedAdvisors();
        });

        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $this->printLoginGuide();
    }

    private function seedAdminUsers(): void
    {
        foreach ([
            ['UEConnect Super Admin', 'superadmin@hcmue.edu.vn', ['admin']],
            ['UEConnect Admin', 'admin@hcmue.edu.vn', ['admin']],
            ['Mentor Manager', 'mentor.manager@hcmue.edu.vn', ['admin']],
            ['Community Moderator', 'moderator@hcmue.edu.vn', ['admin']],
            ['Verification Reviewer', 'verification.reviewer@hcmue.edu.vn', ['admin']],
        ] as [$name, $email, $roles]) {
            $user = $this->user($name, $email, 'advisor', AccountStatus::ACTIVE, $roles, IdentityType::TEACHER_ADVISOR);
            $this->advisorProfile($user, 'cntt', 'Quản trị UEConnect', 'Nhân sự vận hành UAT', 'Admin console, moderation, verification');
        }
    }

    private function seedStudents(): void
    {
        $students = [
            ['Nguyễn Văn Student', 'student@hcmue.edu.vn', AccountStatus::ACTIVE, 'cntt', 'cong-nghe-thong-tin', 'K48', 'CNTT48A', 'SV240001'],
            ['Trần Thảo Student', 'student2@hcmue.edu.vn', AccountStatus::ACTIVE, 'toan-thong-ke', 'su-pham-toan-hoc', 'K48', 'TOAN48A', 'SV240002'],
            ['Lê Minh Unverified', 'unverified.student@hcmue.edu.vn', AccountStatus::REGISTERED, 'ngu-van', 'van-hoc', 'K49', 'VAN49A', 'SV240003'],
            ['Phạm An Suspended', 'suspended.student@hcmue.edu.vn', AccountStatus::SUSPENDED, 'tieng-anh', 'ngon-ngu-anh', 'K47', 'ANH47A', 'SV240004'],
            ['Hoàng Nam Banned', 'banned.student@hcmue.edu.vn', AccountStatus::BANNED, 'cntt', 'su-pham-tin-hoc', 'K46', 'TIN46A', 'SV240005'],
            ['Võ Long Limit', 'limit.student@hcmue.edu.vn', AccountStatus::ACTIVE, 'cntt', 'cong-nghe-thong-tin', 'K48', 'CNTT48B', 'SV240006'],
            ['Đỗ Minh Blocked', 'blocked.student@hcmue.edu.vn', AccountStatus::ACTIVE, 'toan-thong-ke', 'toan-hoc', 'K48', 'TOAN48B', 'SV240007'],
            ['Nguyễn Hạ CNTT', 'student.cntt@hcmue.edu.vn', AccountStatus::ACTIVE, 'cntt', 'cong-nghe-thong-tin', 'K49', 'CNTT49A', 'SV240008'],
            ['Trần Bảo Toán', 'student.math@hcmue.edu.vn', AccountStatus::ACTIVE, 'toan-thong-ke', 'toan-hoc', 'K49', 'TOAN49A', 'SV240009'],
            ['Lê Mai English', 'student.english@hcmue.edu.vn', AccountStatus::ACTIVE, 'tieng-anh', 'ngon-ngu-anh', 'K49', 'ANH49A', 'SV240010'],
            ['Phạm Linh Psychology', 'student.psychology@hcmue.edu.vn', AccountStatus::ACTIVE, 'tam-ly-hoc', 'tam-ly-hoc', 'K49', 'TLY49A', 'SV240011'],
            ['Bùi An Literature', 'student.literature@hcmue.edu.vn', AccountStatus::ACTIVE, 'ngu-van', 'su-pham-ngu-van', 'K49', 'VAN49B', 'SV240012'],
            ['Ngô Thanh Vật lý', 'student.physics@hcmue.edu.vn', AccountStatus::ACTIVE, 'vat-ly', 'su-pham-vat-ly', 'K50', 'LY50A', 'SV240013'],
            ['Đặng Tú Hóa học', 'student.chemistry@hcmue.edu.vn', AccountStatus::ACTIVE, 'hoa-hoc', 'hoa-hoc', 'K50', 'HOA50A', 'SV240014'],
            ['Mai Thảo Sinh học', 'student.biology@hcmue.edu.vn', AccountStatus::ACTIVE, 'sinh-hoc', 'su-pham-sinh-hoc', 'K50', 'SINH50A', 'SV240015'],
            ['Lý Khánh Lịch sử', 'student.history@hcmue.edu.vn', AccountStatus::ACTIVE, 'lich-su', 'lich-su', 'K49', 'SU49A', 'SV240016'],
            ['Hồ Gia Địa lý', 'student.geography@hcmue.edu.vn', AccountStatus::ACTIVE, 'dia-ly', 'dia-ly-hoc', 'K49', 'DIA49A', 'SV240017'],
            ['Trương Hân Tiểu học', 'student.primary@hcmue.edu.vn', AccountStatus::ACTIVE, 'giao-duc-tieu-hoc', 'giao-duc-tieu-hoc', 'K48', 'GDTH48A', 'SV240018'],
        ];

        foreach ($students as [$name, $email, $status, $facultySlug, $programSlug, $cohort, $className, $studentCode]) {
            $roles = $status === AccountStatus::ACTIVE ? ['student'] : [];
            $user = $this->user($name, $email, 'student', $status, $roles, IdentityType::CURRENT_STUDENT);
            $this->studentProfile($user, $facultySlug, $programSlug, $cohort, $className, $studentCode);
        }

        $peerMentor = $this->user('Student Peer Mentor', 'student.peermentor@hcmue.edu.vn', 'student', AccountStatus::ACTIVE, ['student'], IdentityType::CURRENT_STUDENT);
        $this->studentProfile($peerMentor, 'cntt', 'su-pham-tin-hoc', 'K47', 'TIN47A', 'SV240019');

        $legacyStudent = $this->user('Nguyễn Văn Test', 'student.test@hcmue.edu.vn', 'student', AccountStatus::REGISTERED, [], IdentityType::CURRENT_STUDENT);
        $this->studentProfile($legacyStudent, 'cntt', 'cong-nghe-thong-tin', 'K50', 'CNTT50A', 'SV240020');
    }

    private function seedAlumni(): void
    {
        foreach ([
            ['Nguyễn Minh Alumni Mentor', 'alumni.mentor@hcmue.edu.vn', 'cntt', 'cong-nghe-thong-tin', true],
            ['Trần Hoàng Paused Mentor', 'alumni.paused@hcmue.edu.vn', 'cntt', 'su-pham-tin-hoc', true],
            ['Lê Anh Hidden Mentor', 'alumni.hidden@hcmue.edu.vn', 'toan-thong-ke', 'toan-hoc', true],
            ['Phạm Long Full Mentor', 'alumni.full@hcmue.edu.vn', 'tieng-anh', 'ngon-ngu-anh', true],
            ['Võ Quang Pending Alumni', 'alumni.pending@hcmue.edu.vn', 'ngu-van', 'van-hoc', false],
            ['Ngô Khánh Under Review Alumni', 'alumni.underreview@hcmue.edu.vn', 'vat-ly', 'vat-ly-hoc', false],
            ['Đặng Hải More Info Alumni', 'alumni.moreinfo@hcmue.edu.vn', 'hoa-hoc', 'hoa-hoc', false],
            ['Bùi Sơn Rejected Alumni', 'alumni.rejected@hcmue.edu.vn', 'sinh-hoc', 'sinh-hoc', false],
            ['Mai Phúc Revoked Mentor', 'alumni.revoked@hcmue.edu.vn', 'tam-ly-hoc', 'tam-ly-hoc', false],
        ] as [$name, $email, $facultySlug, $programSlug, $willingToMentor]) {
            $user = $this->user($name, $email, 'alumni', AccountStatus::ACTIVE, ['alumni'], IdentityType::ALUMNI);
            $this->alumniProfile($user, $facultySlug, $programSlug, $willingToMentor);
        }
    }

    private function seedAdvisors(): void
    {
        foreach ([
            ['Thầy Lê Văn An', 'advisor.mentor@hcmue.edu.vn', 'cntt', 'Bộ môn Kỹ thuật Phần mềm', 'Cố vấn học tập', 'Nghiên cứu khoa học, AI, backend'],
            ['Cô Nguyễn Thu Advisor Pending', 'advisor.pending@hcmue.edu.vn', 'ngu-van', 'Khoa Ngữ văn', 'Giảng viên', 'Học thuật, viết học thuật'],
            ['Thầy Trần Minh More Info', 'advisor.moreinfo@hcmue.edu.vn', 'toan-thong-ke', 'Khoa Toán - Thống kê', 'Giảng viên', 'Đề tài nghiên cứu, thống kê'],
            ['Cô Lê Anh Rejected', 'advisor.rejected@hcmue.edu.vn', 'tieng-anh', 'Khoa Tiếng Anh', 'Giảng viên', 'Tư vấn học tập ngoại ngữ'],
            ['Thầy Phạm Hoàng Paused', 'advisor.paused@hcmue.edu.vn', 'vat-ly', 'Khoa Vật lý', 'Cố vấn học tập', 'Nghiên cứu vật lý, data'],
            ['Cô Đỗ Mai Hidden', 'advisor.hidden@hcmue.edu.vn', 'tam-ly-hoc', 'Khoa Tâm lý học', 'Giảng viên', 'Tâm lý học giáo dục'],
        ] as [$name, $email, $facultySlug, $department, $title, $areas]) {
            $user = $this->user($name, $email, 'advisor', AccountStatus::ACTIVE, ['advisor'], IdentityType::TEACHER_ADVISOR);
            $this->advisorProfile($user, $facultySlug, $department, $title, $areas);
        }

        $legacyTeacher = $this->user('Thầy Lê Văn An Feed', 'teacher.verified1@hcmue.edu.vn', 'advisor', AccountStatus::ACTIVE, ['advisor'], IdentityType::TEACHER_ADVISOR);
        $this->advisorProfile($legacyTeacher, 'cntt', 'Bộ môn Kỹ thuật Phần mềm', 'Giảng viên', 'Học vụ, nghiên cứu khoa học');
    }

    /**
     * @param  array<int, string>  $roles
     */
    private function user(string $name, string $email, string $roleType, AccountStatus $status, array $roles, IdentityType $identityType): User
    {
        $user = User::updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => Hash::make(self::PASSWORD),
                'account_status' => $status,
                'account_status_reason' => $status === AccountStatus::ACTIVE ? null : 'UAT account for feature testing.',
                'last_login_at' => now(),
                'intended_identity_type' => $identityType,
            ]
        );

        $user->forceFill([
            'email_verified_at' => $status === AccountStatus::REGISTERED ? null : now(),
        ])->save();

        $user->syncRoles($roles);
        $this->profile($user, $roleType);

        return $user;
    }

    private function profile(User $user, string $roleType): Profile
    {
        $isComplete = $user->account_status === AccountStatus::ACTIVE;

        return Profile::updateOrCreate(
            ['user_id' => $user->id],
            [
                'display_name' => $user->name,
                'bio' => 'UAT profile dùng để kiểm thử thủ công các tính năng UEConnect.',
                'role_type' => $roleType,
                'profile_status' => $isComplete ? 'complete' : 'incomplete',
                'visibility' => 'public',
                'discoverable' => $isComplete,
                'profile_completed_at' => $isComplete ? now() : null,
            ]
        );
    }

    private function studentProfile(User $user, string $facultySlug, string $programSlug, string $cohort, string $className, string $studentCode): void
    {
        $profile = $this->profile($user, 'student');
        [$faculty, $program] = $this->facultyAndProgram($facultySlug, $programSlug);

        StudentProfile::updateOrCreate(
            ['profile_id' => $profile->id],
            [
                'student_code' => $studentCode,
                'faculty_id' => $faculty->id,
                'academic_program_id' => $program->id,
                'cohort' => $cohort,
                'current_year' => 2,
                'class_name' => $className,
                'learning_goals' => 'Tìm bạn học, mentor và tài liệu phù hợp với ngành học.',
                'career_orientation' => 'Muốn hiểu rõ lộ trình học tập và nghề nghiệp sau khi ra trường.',
            ]
        );
    }

    private function alumniProfile(User $user, string $facultySlug, string $programSlug, bool $willingToMentor): void
    {
        $profile = $this->profile($user, 'alumni');
        [$faculty, $program] = $this->facultyAndProgram($facultySlug, $programSlug);

        AlumniProfile::updateOrCreate(
            ['profile_id' => $profile->id],
            [
                'faculty_id' => $faculty->id,
                'academic_program_id' => $program->id,
                'cohort' => 'K40',
                'graduation_year' => 2020,
                'current_position' => 'Chuyên viên / Mentor UAT',
                'current_organization' => 'Demo EdTech Studio',
                'industry' => 'Education Technology',
                'career_summary' => 'Cựu sinh viên HCMUE dùng cho kiểm thử mentor, discovery và profile.',
                'willing_to_mentor' => $willingToMentor,
            ]
        );
    }

    private function advisorProfile(User $user, string $facultySlug, string $department, string $title, string $areas): void
    {
        $profile = $this->profile($user, 'advisor');
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

    /**
     * @return array{0: Faculty, 1: AcademicProgram}
     */
    private function facultyAndProgram(string $facultySlug, string $programSlug): array
    {
        $faculty = $this->faculty($facultySlug);
        $program = AcademicProgram::where('faculty_id', $faculty->id)
            ->where('slug', $programSlug)
            ->firstOrFail();

        return [$faculty, $program];
    }

    private function faculty(string $slug): Faculty
    {
        return Faculty::where('slug', $slug)->firstOrFail();
    }

    private function printLoginGuide(): void
    {
        $this->command->newLine();
        $this->command->info('UEConnect UAT Accounts');
        $this->command->info('Password for all accounts: '.self::PASSWORD);
        $this->command->table(
            ['Group', 'Emails'],
            [
                ['Admin', 'superadmin@hcmue.edu.vn, admin@hcmue.edu.vn, mentor.manager@hcmue.edu.vn, moderator@hcmue.edu.vn, verification.reviewer@hcmue.edu.vn'],
                ['Student', 'student@hcmue.edu.vn, student2@hcmue.edu.vn, unverified.student@hcmue.edu.vn, suspended.student@hcmue.edu.vn, banned.student@hcmue.edu.vn'],
                ['Student+', 'limit.student@hcmue.edu.vn, blocked.student@hcmue.edu.vn, student.math@hcmue.edu.vn, student.english@hcmue.edu.vn, student.psychology@hcmue.edu.vn'],
                ['Alumni', 'alumni.mentor@hcmue.edu.vn, alumni.paused@hcmue.edu.vn, alumni.hidden@hcmue.edu.vn, alumni.full@hcmue.edu.vn, alumni.pending@hcmue.edu.vn'],
                ['Advisor', 'advisor.mentor@hcmue.edu.vn, advisor.pending@hcmue.edu.vn, advisor.moreinfo@hcmue.edu.vn, advisor.rejected@hcmue.edu.vn, advisor.paused@hcmue.edu.vn'],
            ]
        );
    }
}
