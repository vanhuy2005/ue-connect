<?php

namespace Tests\Feature\CareerPathway;

use App\Enums\CareerPositionStatus;
use App\Enums\CareerPositionVisibility;
use App\Enums\CareerUserPathwayStatus;
use App\Enums\CareerUserPathwayVisibility;
use App\Enums\ProgramStatus;
use App\Models\CareerCourse;
use App\Models\CareerPosition;
use App\Models\CareerProgram;
use App\Models\CareerUserPathway;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CareerPathwaySearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_search_course_by_code_and_name()
    {
        $program = CareerProgram::factory()->create(['name' => 'IT', 'status' => ProgramStatus::READY->value]);
        $course = CareerCourse::create(['code' => 'COMP101', 'name' => 'Toán Rời Rạc']);
        $program->courses()->attach($course->id);

        $response = $this->getJson(route('career-pathway.career-pathways.search').'?q=COMP101');
        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals('course', $response->json('data.0.type'));

        $response2 = $this->getJson(route('career-pathway.career-pathways.search').'?q=Toán');
        $response2->assertStatus(200);
        $this->assertCount(1, $response2->json('data'));
    }

    public function test_hidden_program_not_returned()
    {
        CareerProgram::factory()->create(['name' => 'Hidden IT', 'status' => ProgramStatus::EMPTY_EXTRACTION->value]);

        $response = $this->getJson(route('career-pathway.career-pathways.search').'?q=Hidden');
        $response->assertStatus(200);
        $this->assertCount(0, $response->json('data'));
    }

    public function test_course_in_hidden_program_not_returned()
    {
        $program = CareerProgram::factory()->create(['name' => 'Hidden IT', 'status' => ProgramStatus::EMPTY_EXTRACTION->value]);
        $course = CareerCourse::create(['code' => 'COMP102', 'name' => 'Secret Course']);
        $program->courses()->attach($course->id);

        $response = $this->getJson(route('career-pathway.career-pathways.search').'?q=COMP102');
        $response->assertStatus(200);
        $this->assertCount(0, $response->json('data'));
    }

    public function test_search_positions_enforces_visibility()
    {
        $author = User::factory()->create();

        // Published & Public
        CareerPosition::create([
            'title' => 'Frontend Dev',
            'slug' => 'frontend-dev',
            'created_by' => $author->id,
            'status' => CareerPositionStatus::PUBLISHED->value,
            'visibility' => CareerPositionVisibility::PUBLIC->value,
        ]);

        // Published & Private (should be hidden)
        CareerPosition::create([
            'title' => 'Secret Dev',
            'slug' => 'secret-dev',
            'created_by' => $author->id,
            'status' => CareerPositionStatus::PUBLISHED->value,
            'visibility' => CareerPositionVisibility::PRIVATE->value,
        ]);

        $response = $this->getJson(route('career-pathway.career-pathways.search').'?q=Dev');
        $response->assertStatus(200);

        // Should only return Frontend Dev
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals('Frontend Dev', $data[0]['title']);
    }

    public function test_search_senior_pathways_enforces_visibility()
    {
        $author = User::factory()->create();

        // Published & Public
        CareerUserPathway::create([
            'title' => 'My Public Journey',
            'slug' => 'public-journey',
            'user_id' => $author->id,
            'status' => CareerUserPathwayStatus::PUBLISHED->value,
            'visibility' => CareerUserPathwayVisibility::PUBLIC->value,
        ]);

        // Published & Private (should be hidden)
        CareerUserPathway::create([
            'title' => 'My Secret Journey',
            'slug' => 'secret-journey',
            'user_id' => $author->id,
            'status' => CareerUserPathwayStatus::PUBLISHED->value,
            'visibility' => CareerUserPathwayVisibility::PRIVATE->value,
        ]);

        $response = $this->getJson(route('career-pathway.career-pathways.search').'?q=Journey');
        $response->assertStatus(200);

        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals('My Public Journey', $data[0]['title']);
    }
}
