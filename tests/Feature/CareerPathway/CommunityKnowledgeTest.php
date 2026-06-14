<?php

namespace Tests\Feature\CareerPathway;

use App\Enums\AccountStatus;
use App\Enums\CareerContributionStatus;
use App\Enums\CareerContributionType;
use App\Enums\CareerContributionVisibility;
use App\Enums\ProgramStatus;
use App\Models\CareerCohort;
use App\Models\CareerContribution;
use App\Models\CareerCourse;
use App\Models\CareerCourseDescription;
use App\Models\CareerFaculty;
use App\Models\CareerMajor;
use App\Models\CareerProgram;
use App\Models\CareerProgramCourse;
use App\Models\CareerSemester;
use App\Models\User;
use Database\Seeders\Reference\AccessControlReferenceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommunityKnowledgeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Since we are not running migrations before each test in some local setups, make sure migrations are up.
        // The RefreshDatabase trait handles this for standard Laravel projects.
    }

    public function test_user_can_create_course_contribution()
    {
        $user = User::factory()->create(['account_status' => AccountStatus::ACTIVE]);
        $course = CareerCourse::create(['code' => 'TEST101', 'name' => 'Test Course', 'credits' => 3]);

        $response = $this->actingAs($user)->postJson(route('career-pathway.courses.contributions.store', $course->id), [
            'contribution_type' => CareerContributionType::RESOURCE->value,
            'title' => 'Great YouTube Tutorial',
            'content' => 'Watch this video to pass the exam.',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('career_contributions', [
            'user_id' => $user->id,
            'target_type' => CareerCourse::class,
            'target_id' => $course->id,
            'contribution_type' => CareerContributionType::RESOURCE->value,
            'title' => 'Great YouTube Tutorial',
        ]);

        // Ensure official course data is completely untouched
        $this->assertDatabaseHas('career_courses', [
            'id' => $course->id,
            'name' => 'Test Course',
        ]);
    }

    public function test_user_can_vote_once_and_cannot_vote_own_contribution()
    {
        $author = User::factory()->create(['account_status' => AccountStatus::ACTIVE]);
        $voter = User::factory()->create(['account_status' => AccountStatus::ACTIVE]);
        $course = CareerCourse::create(['code' => 'TEST101', 'name' => 'Test Course', 'credits' => 3]);

        $contribution = CareerContribution::create([
            'user_id' => $author->id,
            'target_type' => CareerCourse::class,
            'target_id' => $course->id,
            'contribution_type' => CareerContributionType::EXPERIENCE->value,
            'content' => 'Hard class',
            'status' => CareerContributionStatus::PUBLISHED->value,
        ]);

        // Author cannot vote own
        $response = $this->actingAs($author)->postJson(route('career-pathway.contributions.vote', $contribution->id), [
            'value' => 1,
        ]);
        $response->assertStatus(403);

        // Voter can upvote
        $response = $this->actingAs($voter)->postJson(route('career-pathway.contributions.vote', $contribution->id), [
            'value' => 1,
        ]);
        $response->assertStatus(200);
        $this->assertEquals(1, $response->json('upvotes_count'));

        // Voter can change to downvote
        $response = $this->actingAs($voter)->postJson(route('career-pathway.contributions.vote', $contribution->id), [
            'value' => -1,
        ]);
        $response->assertStatus(200);
        $this->assertEquals(0, $response->json('upvotes_count'));
        $this->assertEquals(1, $response->json('downvotes_count'));

        // Verify max 1 vote record
        $this->assertDatabaseCount('career_contribution_votes', 1);
    }

    public function test_admin_can_moderate_and_hide_contribution()
    {
        $this->artisan('db:seed', ['--class' => AccessControlReferenceSeeder::class]);

        $admin = User::factory()->create(['account_status' => AccountStatus::ACTIVE]);
        $admin->givePermissionTo('review_verification');
        $author = User::factory()->create(['account_status' => AccountStatus::ACTIVE]);
        $course = CareerCourse::create(['code' => 'TEST101', 'name' => 'Test Course', 'credits' => 3]);

        $contribution = CareerContribution::create([
            'user_id' => $author->id,
            'target_type' => CareerCourse::class,
            'target_id' => $course->id,
            'contribution_type' => CareerContributionType::EXPERIENCE->value,
            'content' => 'Hard class',
            'status' => CareerContributionStatus::PUBLISHED->value,
        ]);

        // Hide contribution
        $response = $this->actingAs($admin)->patchJson(route('admin.career-pathway.contributions.moderate', $contribution->id), [
            'status' => CareerContributionStatus::HIDDEN_BY_MODERATION->value,
            'reason' => 'Inappropriate language',
        ]);

        $response->assertStatus(200);
        $this->assertEquals(CareerContributionStatus::HIDDEN_BY_MODERATION, $contribution->fresh()->status);

        // Verify public API no longer shows it
        $publicResponse = $this->actingAs($author)->getJson(route('career-pathway.courses.contributions.index', $course->id));
        $publicResponse->assertStatus(200);
        $this->assertCount(0, $publicResponse->json('data'));
    }

    public function test_suspended_user_cannot_contribute()
    {
        $suspendedUser = User::factory()->create(['account_status' => AccountStatus::SUSPENDED]);
        $course = CareerCourse::create(['code' => 'TEST101', 'name' => 'Test Course', 'credits' => 3]);

        $response = $this->actingAs($suspendedUser)->postJson(route('career-pathway.courses.contributions.store', $course->id), [
            'contribution_type' => CareerContributionType::RESOURCE->value,
            'title' => 'Tài liệu không nên được tạo',
            'content' => 'Tài khoản bị tạm ngưng không được đóng góp.',
        ]);

        $response->assertRedirect(route('system.account-restricted'));

        $this->assertDatabaseMissing('career_contributions', [
            'user_id' => $suspendedUser->id,
            'target_id' => $course->id,
            'title' => 'Tài liệu không nên được tạo',
        ]);
    }

    public function test_course_update_proposal_is_private_until_admin_approves_it(): void
    {
        $this->artisan('db:seed', ['--class' => AccessControlReferenceSeeder::class]);

        $user = User::factory()->create(['account_status' => AccountStatus::ACTIVE]);
        $admin = User::factory()->create(['account_status' => AccountStatus::ACTIVE]);
        $admin->givePermissionTo('review_verification');

        $faculty = CareerFaculty::create(['name' => 'Khoa Công nghệ thông tin', 'slug' => 'khoa-cong-nghe-thong-tin']);
        $major = CareerMajor::create(['faculty_id' => $faculty->id, 'name' => 'Sư phạm Tin học', 'slug' => 'su-pham-tin-hoc']);
        $cohort = CareerCohort::create(['name' => 'Khóa 51', 'slug' => 'khoa-51', 'start_year' => 2025]);
        $program = CareerProgram::create([
            'cohort_id' => $cohort->id,
            'faculty_id' => $faculty->id,
            'major_id' => $major->id,
            'name' => 'Sư phạm Tin học K51',
            'slug' => 'su-pham-tin-hoc-k51',
            'status' => ProgramStatus::READY->value,
            'total_credits' => 120,
        ]);
        $semester = CareerSemester::create([
            'program_id' => $program->id,
            'semester_number' => 1,
            'title' => 'Học kỳ 1',
        ]);
        $course = CareerCourse::create(['code' => 'COMP1010', 'name' => 'Nhập môn cũ']);
        $programCourse = CareerProgramCourse::create([
            'program_id' => $program->id,
            'semester_id' => $semester->id,
            'course_id' => $course->id,
            'credits' => 2,
            'is_mandatory' => true,
            'knowledge_block' => 'Cơ sở ngành',
        ]);

        $response = $this->actingAs($user)->from('/app/career-pathway/programs')->post(route('career-pathway.courses.update-proposals.store', $course), [
            'program_course_id' => $programCourse->id,
            'name' => 'Nhập môn lập trình',
            'credits' => 3,
            'description' => 'Môn học giới thiệu tư duy lập trình và cách xây project nhỏ.',
            'knowledge_block' => 'Cơ sở ngành cập nhật',
            'is_mandatory' => '1',
            'reason' => 'Thông tin trong chương trình đang thiếu mô tả và số tín chỉ chưa đúng.',
        ]);

        $response->assertRedirect('/app/career-pathway/programs');

        $contribution = CareerContribution::query()->where('target_id', $course->id)->firstOrFail();

        $this->assertSame(CareerContributionType::COURSE_UPDATE_PROPOSAL, $contribution->contribution_type);
        $this->assertSame(CareerContributionStatus::PENDING_REVIEW, $contribution->status);
        $this->assertSame(CareerContributionVisibility::PRIVATE, $contribution->visibility);
        $this->assertSame('Nhập môn cũ', $course->fresh()->name);

        $this->actingAs($admin)->patchJson(route('admin.career-pathway.contributions.moderate', $contribution), [
            'status' => CareerContributionStatus::APPROVED->value,
            'reason' => 'Dữ liệu hợp lệ.',
        ])->assertOk();

        $this->assertSame('Nhập môn lập trình', $course->fresh()->name);
        $this->assertSame(3, $programCourse->fresh()->credits);
        $this->assertSame('Cơ sở ngành cập nhật', $programCourse->fresh()->knowledge_block);
        $this->assertDatabaseHas('career_course_descriptions', [
            'course_id' => $course->id,
            'description_text' => 'Môn học giới thiệu tư duy lập trình và cách xây project nhỏ.',
        ]);
        $this->assertSame('Môn học giới thiệu tư duy lập trình và cách xây project nhỏ.', CareerCourseDescription::where('course_id', $course->id)->value('description_text'));
    }
}
