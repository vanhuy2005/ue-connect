<?php

namespace Tests\Feature\CareerPathway;

use App\Enums\ProgramStatus;
use App\Models\CareerCohort;
use App\Models\CareerCourse;
use App\Models\CareerFaculty;
use App\Models\CareerMajor;
use App\Models\CareerProgram;
use App\Models\CareerProgramCourse;
use App\Models\CareerSemester;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditCommandTest extends TestCase
{
    use RefreshDatabase;

    private string $fixturePath;

    protected function setUp(): void
    {
        parent::setUp();
        $this->fixturePath = base_path('tests/Fixtures/career_pathway_audit');
    }

    public function test_audit_reports_missing_program_in_db()
    {
        $path = $this->fixturePath.'/valid-8-semesters';

        // We do NOT create any DB records. The audit should flag 'source_file_not_imported'.
        $this->artisan('career-pathway:audit-import', ['sourcePath' => $path, '--fail-on-mismatch' => true])
            ->assertFailed()
            ->expectsOutputToContain('source_file_not_imported');
    }

    public function test_audit_reports_no_mismatches_for_valid_data()
    {
        $path = $this->fixturePath.'/valid-8-semesters';

        $cohort = CareerCohort::create(['name' => 'Khoa48', 'slug' => 'khoa-48', 'year' => 2023]);
        $faculty = CareerFaculty::create(['name' => 'CNTT']);
        $major = CareerMajor::create(['name' => 'CNTT', 'faculty_id' => $faculty->id]);

        $program = CareerProgram::create([
            'cohort_id' => $cohort->id,
            'faculty_id' => $faculty->id,
            'major_id' => $major->id,
            'status' => ProgramStatus::READY,
        ]);

        $s1 = CareerSemester::create(['program_id' => $program->id, 'semester_number' => 1, 'name' => 'Học kỳ 1']);
        $s2 = CareerSemester::create(['program_id' => $program->id, 'semester_number' => 2, 'name' => 'Học kỳ 2']);

        $c1 = CareerCourse::create(['code' => 'COMP101', 'name' => 'Nhập môn lập trình', 'credits' => 3]);
        $c2 = CareerCourse::create(['code' => 'COMP102', 'name' => 'Cấu trúc dữ liệu', 'credits' => 4]);

        CareerProgramCourse::create(['program_id' => $program->id, 'semester_id' => $s1->id, 'course_id' => $c1->id, 'knowledge_block' => 'Khối kiến thức chung', 'is_mandatory' => true]);
        CareerProgramCourse::create(['program_id' => $program->id, 'semester_id' => $s2->id, 'course_id' => $c2->id, 'knowledge_block' => 'Khối kiến thức chung', 'is_mandatory' => true]);

        $this->artisan('career-pathway:audit-import', ['sourcePath' => $path, '--fail-on-mismatch' => true])
            ->assertSuccessful()
            ->expectsOutputToContain('No mismatches found!');
    }

    public function test_audit_reports_semester_count_mismatch()
    {
        $path = $this->fixturePath.'/valid-8-semesters';

        $cohort = CareerCohort::create(['name' => 'Khoa48', 'slug' => 'khoa-48-s', 'year' => 2023]);
        $faculty = CareerFaculty::create(['name' => 'CNTT']);
        $major = CareerMajor::create(['name' => 'CNTT', 'faculty_id' => $faculty->id]);

        $program = CareerProgram::create([
            'cohort_id' => $cohort->id,
            'faculty_id' => $faculty->id,
            'major_id' => $major->id,
            'status' => ProgramStatus::READY,
        ]);

        // Only insert 1 semester, but markdown has 2
        $s1 = CareerSemester::create(['program_id' => $program->id, 'semester_number' => 1, 'name' => 'Học kỳ 1']);

        $this->artisan('career-pathway:audit-import', ['sourcePath' => $path, '--fail-on-mismatch' => true])
            ->assertFailed()
            ->expectsOutputToContain('semester_count_mismatch');
    }

    public function test_audit_reports_course_count_mismatch()
    {
        $path = $this->fixturePath.'/valid-8-semesters';

        $cohort = CareerCohort::create(['name' => 'Khoa48', 'slug' => 'khoa-48-c', 'year' => 2023]);
        $faculty = CareerFaculty::create(['name' => 'CNTT']);
        $major = CareerMajor::create(['name' => 'CNTT', 'faculty_id' => $faculty->id]);

        $program = CareerProgram::create([
            'cohort_id' => $cohort->id,
            'faculty_id' => $faculty->id,
            'major_id' => $major->id,
            'status' => ProgramStatus::READY,
        ]);

        $s1 = CareerSemester::create(['program_id' => $program->id, 'semester_number' => 1, 'name' => 'Học kỳ 1']);
        $s2 = CareerSemester::create(['program_id' => $program->id, 'semester_number' => 2, 'name' => 'Học kỳ 2']);

        // Insert 0 courses, but markdown has 2
        $this->artisan('career-pathway:audit-import', ['sourcePath' => $path, '--fail-on-mismatch' => true])
            ->assertFailed()
            ->expectsOutputToContain('course_count_mismatch');
    }
}
