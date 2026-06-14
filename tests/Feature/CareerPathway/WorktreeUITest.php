<?php

namespace Tests\Feature\CareerPathway;

use App\Enums\ProgramStatus;
use App\Models\CareerCohort;
use App\Models\CareerFaculty;
use App\Models\CareerMajor;
use App\Models\CareerProgram;
use App\Models\CareerSemester;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;
use Tests\TestCase;

class WorktreeUITest extends TestCase
{
    use RefreshDatabase;

    public function test_can_render_career_pathway_page()
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->withoutMiddleware()->get(route('career-pathways.index'));

        $response->assertStatus(200);
        $response->assertSee('Career Pathway');
        $response->assertSeeLivewire('pages.app.career-pathway');
    }

    public function test_volt_component_can_render_programs_and_worktree()
    {
        // Set up test data
        $cohort = CareerCohort::factory()->create(['name' => 'K48']);
        $faculty = CareerFaculty::factory()->create(['name' => 'CNTT']);
        $major = CareerMajor::factory()->create(['name' => 'KTPM', 'faculty_id' => $faculty->id]);

        $program = CareerProgram::factory()->create([
            'cohort_id' => $cohort->id,
            'faculty_id' => $faculty->id,
            'major_id' => $major->id,
            'status' => ProgramStatus::READY->value,
        ]);

        CareerSemester::factory()->create([
            'program_id' => $program->id,
            'semester_number' => 1,
            'title' => 'Học kỳ 1',
        ]);

        // Test rendering without selecting filters
        Volt::test('pages.app.career-pathway')
            ->assertSee($cohort->name)
            ->assertSee($faculty->name)
            ->assertSee($major->name)
            ->assertSee('Sẵn sàng khám phá');

        // Test rendering with selected filters
        Volt::test('pages.app.career-pathway')
            ->set('cohortId', $cohort->id)
            ->set('facultyId', $faculty->id)
            ->set('majorId', $major->id)
            ->assertSet('cohortId', $cohort->id)
            ->assertSee('Đã xác minh đầy đủ')
            ->assertSee('Học kỳ 1');
    }

    public function test_volt_component_filters_work_correctly()
    {
        $cohort1 = CareerCohort::factory()->create(['name' => 'K48']);
        $cohort2 = CareerCohort::factory()->create(['name' => 'K49']);
        $faculty1 = CareerFaculty::factory()->create(['name' => 'Faculty 1']);
        $major1 = CareerMajor::factory()->create(['name' => 'Major 1', 'faculty_id' => $faculty1->id]);

        $program1 = CareerProgram::factory()->create([
            'cohort_id' => $cohort1->id,
            'faculty_id' => $faculty1->id,
            'major_id' => $major1->id,
            'status' => ProgramStatus::READY->value,
            'name' => 'Program 1',
        ]);

        CareerSemester::factory()->create([
            'program_id' => $program1->id,
            'semester_number' => 1,
            'title' => 'Program 1 Semester',
        ]);

        $program2 = CareerProgram::factory()->create([
            'cohort_id' => $cohort2->id,
            'status' => ProgramStatus::READY->value,
            'name' => 'Program 2',
        ]);

        $component = Volt::test('pages.app.career-pathway')
            ->set('cohortId', $cohort1->id)
            ->set('facultyId', $faculty1->id)
            ->set('majorId', $major1->id);

        $component->assertSee('Học kỳ 1')
            ->assertDontSee('Program 2');
    }
}
