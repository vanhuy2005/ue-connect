<?php

namespace Tests\Feature\CareerPathway;

use App\Enums\ProgramStatus;
use App\Models\CareerCohort;
use App\Models\CareerDataQualityIssue;
use App\Models\CareerFaculty;
use App\Models\CareerMajor;
use App\Models\CareerProgram;
use App\Models\CareerSemester;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_cohorts_endpoint_only_includes_cohorts_connected_to_public_ready_programs()
    {
        $cohort1 = CareerCohort::factory()->create();
        $cohort2 = CareerCohort::factory()->create();

        CareerProgram::factory()->create([
            'cohort_id' => $cohort1->id,
            'status' => ProgramStatus::READY->value,
        ]);

        CareerProgram::factory()->create([
            'cohort_id' => $cohort2->id,
            'status' => ProgramStatus::UNRESOLVED_SEMESTER_STRUCTURE->value,
        ]);

        $response = $this->getJson(route('career-pathway.cohorts'));

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $cohort1->id);
    }

    public function test_faculties_endpoint_only_includes_faculties_connected_to_public_ready_programs()
    {
        $faculty1 = CareerFaculty::factory()->create();
        $faculty2 = CareerFaculty::factory()->create();

        CareerProgram::factory()->create([
            'faculty_id' => $faculty1->id,
            'status' => ProgramStatus::READY->value,
        ]);

        CareerProgram::factory()->create([
            'faculty_id' => $faculty2->id,
            'status' => ProgramStatus::EMPTY_EXTRACTION->value,
        ]);

        $response = $this->getJson(route('career-pathway.faculties'));

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $faculty1->id);
    }

    public function test_majors_endpoint_only_includes_majors_connected_to_public_ready_programs()
    {
        $major1 = CareerMajor::factory()->create();
        $major2 = CareerMajor::factory()->create();

        CareerProgram::factory()->create([
            'major_id' => $major1->id,
            'status' => ProgramStatus::READY->value,
        ]);

        CareerProgram::factory()->create([
            'major_id' => $major2->id,
            'status' => ProgramStatus::EXCLUDED_NON_PROGRAM_DOCUMENT->value,
        ]);

        $response = $this->getJson(route('career-pathway.majors'));

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $major1->id);
    }

    public function test_programs_endpoint_returns_only_ready_and_ready_with_missing_descriptions()
    {
        CareerProgram::factory()->create(['status' => ProgramStatus::READY->value]);
        CareerProgram::factory()->create(['status' => ProgramStatus::READY_WITH_MISSING_DESCRIPTIONS->value]);
        CareerProgram::factory()->create(['status' => ProgramStatus::PARTIAL_SEMESTER_EXTRACTION->value]);

        $response = $this->getJson(route('career-pathway.programs'));

        $response->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_programs_endpoint_filters_by_cohort_faculty_major()
    {
        $cohort = CareerCohort::factory()->create();
        $faculty = CareerFaculty::factory()->create();
        $major = CareerMajor::factory()->create();

        CareerProgram::factory()->create([
            'cohort_id' => $cohort->id,
            'faculty_id' => $faculty->id,
            'major_id' => $major->id,
            'status' => ProgramStatus::READY->value,
        ]);

        CareerProgram::factory()->create([
            'status' => ProgramStatus::READY->value,
        ]);

        $response = $this->getJson(route('career-pathway.programs', [
            'cohort_id' => $cohort->id,
            'faculty_id' => $faculty->id,
            'major_id' => $major->id,
        ]));

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.cohort_id', $cohort->id);
    }

    public function test_direct_request_to_non_public_program_returns_404()
    {
        $program = CareerProgram::factory()->create([
            'status' => ProgramStatus::UNRESOLVED_SEMESTER_STRUCTURE->value,
        ]);

        $response = $this->getJson(route('career-pathway.programs.worktree', $program));

        $response->assertNotFound();
    }

    public function test_worktree_endpoint_returns_semesters_and_courses_sorted_correctly()
    {
        $program = CareerProgram::factory()->create([
            'status' => ProgramStatus::READY->value,
        ]);

        $semester2 = CareerSemester::factory()->create(['program_id' => $program->id, 'semester_number' => 2]);
        $semester1 = CareerSemester::factory()->create(['program_id' => $program->id, 'semester_number' => 1]);

        $response = $this->getJson(route('career-pathway.programs.worktree', $program));

        $response->assertOk()
            ->assertJsonPath('data.semesters.0.semester_number', 1)
            ->assertJsonPath('data.semesters.1.semester_number', 2);
    }

    public function test_public_api_never_exposes_raw_context()
    {
        $program = CareerProgram::factory()->create(['status' => ProgramStatus::READY->value]);

        CareerDataQualityIssue::factory()->create([
            'program_id' => $program->id,
            'context' => json_encode(['raw' => 'SECRET']),
        ]);

        $response = $this->getJson(route('career-pathway.programs.worktree', $program));

        $response->assertOk()
            ->assertJsonMissing(['SECRET']);
    }
}
