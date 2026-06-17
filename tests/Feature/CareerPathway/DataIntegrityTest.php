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

class DataIntegrityTest extends TestCase
{
    use RefreshDatabase;

    public function test_every_imported_program_has_cohort_faculty_major()
    {
        $cohort = CareerCohort::create(['name' => 'Khoa48', 'slug' => 'khoa-48', 'year' => 2023]);
        $faculty = CareerFaculty::create(['name' => 'CNTT', 'slug' => 'cntt-'.uniqid()]);
        $major = CareerMajor::create(['name' => 'CNTT', 'slug' => 'cntt-'.uniqid(), 'faculty_id' => $faculty->id]);

        $program = CareerProgram::create([
            'cohort_id' => $cohort->id,
            'faculty_id' => $faculty->id,
            'major_id' => $major->id,
            'status' => ProgramStatus::READY,
            'name' => 'CNTT',
            'slug' => 'cntt-'.uniqid(),
        ]);

        $this->assertNotNull($program->cohort_id);
        $this->assertNotNull($program->faculty_id);
        $this->assertNotNull($program->major_id);
    }

    public function test_ready_programs_must_have_semesters_and_courses_to_be_considered_valid()
    {
        // This is a logical integrity check
        $cohort = CareerCohort::create(['name' => 'Khoa48', 'slug' => 'khoa-48', 'year' => 2023]);
        $faculty = CareerFaculty::create(['name' => 'CNTT', 'slug' => 'cntt-'.uniqid()]);
        $major = CareerMajor::create(['name' => 'CNTT', 'slug' => 'cntt-'.uniqid(), 'faculty_id' => $faculty->id]);

        $program = CareerProgram::create([
            'cohort_id' => $cohort->id,
            'faculty_id' => $faculty->id,
            'major_id' => $major->id,
            'status' => ProgramStatus::READY,
            'name' => 'CNTT',
            'slug' => 'cntt-'.uniqid(),
        ]);

        $s1 = CareerSemester::create(['program_id' => $program->id, 'semester_number' => 1, 'name' => 'Học kỳ 1']);
        $c1 = CareerCourse::create(['code' => 'COMP101', 'name' => 'Nhập môn lập trình']);
        CareerProgramCourse::create(['program_id' => $program->id, 'semester_id' => $s1->id, 'course_id' => $c1->id, 'knowledge_block' => 'Khối kiến thức chung', 'is_mandatory' => true, 'credits' => 3]);

        // Just assert that we can query the relationship and it is not empty
        $this->assertTrue($program->semesters()->count() > 0);
        $this->assertTrue($program->courses()->count() > 0);
    }

    public function test_bad_status_programs_are_not_exposed_to_public_api()
    {
        $cohort = CareerCohort::create(['name' => 'Khoa48', 'slug' => 'khoa-48', 'year' => 2023]);
        $faculty = CareerFaculty::create(['name' => 'CNTT', 'slug' => 'cntt-'.uniqid()]);
        $major = CareerMajor::create(['name' => 'CNTT', 'slug' => 'cntt-'.uniqid(), 'faculty_id' => $faculty->id]);

        // Bad programs
        $badStatuses = [
            ProgramStatus::UNRESOLVED_SEMESTER_STRUCTURE,
            ProgramStatus::EMPTY_EXTRACTION,
            ProgramStatus::PARTIAL_SEMESTER_EXTRACTION,
        ];

        foreach ($badStatuses as $status) {
            CareerProgram::create([
                'cohort_id' => $cohort->id,
                'faculty_id' => $faculty->id,
                'major_id' => $major->id,
                'status' => $status,
                'name' => 'CNTT - '.$status->value,
                'slug' => 'cntt-'.$status->value.'-'.uniqid(),
            ]);
        }

        // Good program
        CareerProgram::create([
            'cohort_id' => $cohort->id,
            'faculty_id' => $faculty->id,
            'major_id' => $major->id,
            'status' => ProgramStatus::READY,
            'name' => 'CNTT',
            'slug' => 'cntt-'.uniqid(),
        ]);

        $response = $this->getJson(route('career-pathway.programs', ['cohort_id' => $cohort->id]));

        $response->assertStatus(200);

        // Only the ready one should be returned
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals(ProgramStatus::READY->value, $response->json('data.0.status'));
    }
}
