<?php

namespace Tests\Feature\AI\HcmueChatbot;

use App\AI\HcmueChatbot\Ingestion\TrainingProgramImportService;
use App\AI\HcmueChatbot\Retrieval\StructuredRetrievalService;
use App\Models\TrainingProgram;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StructuredRetrievalTest extends TestCase
{
    use RefreshDatabase;

    protected TrainingProgramImportService $importService;

    protected StructuredRetrievalService $retrievalService;

    protected TrainingProgram $program;

    protected function setUp(): void
    {
        parent::setUp();

        $this->importService = $this->app->make(TrainingProgramImportService::class);
        $this->retrievalService = $this->app->make(StructuredRetrievalService::class);

        // Seed a dummy training program using import service to make it fully realistic
        $curriculumData = [
            'cohort' => [
                'year' => 2022,
                'cohort_name' => 'Khóa 48',
                'note' => 'Test cohort',
            ],
            'faculty' => [
                'code' => 'CNTT',
                'name' => 'Công nghệ thông tin',
            ],
            'major' => [
                'code' => '7480201',
                'name' => 'Công nghệ thông tin',
                'degree_level' => 'undergraduate',
                'source_url' => 'http://example.com/curriculum',
            ],
            'program' => [
                'title' => 'Công nghệ thông tin - Khóa 48',
                'total_credits' => 135,
                'effective_from' => 2022,
                'effective_to' => 2026,
            ],
            'courses' => [
                [
                    'semester' => 1,
                    'course_code' => 'COMP101',
                    'course_name' => 'Tin học đại cương',
                    'credits' => 3,
                    'course_type' => 'required',
                    'group_name' => 'Kiến thức đại cương',
                    'theory_hours' => 30,
                    'practice_hours' => 30,
                    'self_study_hours' => 90,
                    'is_required' => true,
                ],
                [
                    'semester' => 1,
                    'course_code' => 'MLN101',
                    'course_name' => 'Triết học Mác - Lênin',
                    'credits' => 3,
                    'course_type' => 'required',
                    'group_name' => 'Kiến thức đại cương',
                    'theory_hours' => 45,
                    'practice_hours' => 0,
                    'self_study_hours' => 90,
                    'is_required' => true,
                ],
                [
                    'semester' => 2,
                    'course_code' => 'IT102',
                    'course_name' => 'Kỹ thuật lập trình',
                    'credits' => 3,
                    'course_type' => 'required',
                    'group_name' => 'Kiến thức cơ sở ngành',
                    'theory_hours' => 30,
                    'practice_hours' => 30,
                    'self_study_hours' => 90,
                    'is_required' => true,
                ],
                [
                    'semester' => 5,
                    'course_code' => 'IT303',
                    'course_name' => 'Lập trình Web',
                    'credits' => 3,
                    'course_type' => 'elective',
                    'group_name' => 'Kiến thức chuyên ngành',
                    'theory_hours' => 30,
                    'practice_hours' => 30,
                    'self_study_hours' => 90,
                    'is_required' => false,
                ],
            ],
            'learning_outcomes' => [
                [
                    'code' => 'PLO1',
                    'description' => 'Hiểu và áp dụng các nguyên lý cốt lõi.',
                    'category' => 'Kiến thức',
                ],
                [
                    'code' => 'PLO2',
                    'description' => 'Có kỹ năng tư duy phản biện.',
                    'category' => 'Kỹ năng',
                ],
            ],
        ];

        $this->program = $this->importService->import($curriculumData);
    }

    public function test_find_training_program(): void
    {
        $queryPlan = [
            'query_type' => 'find_training_program',
            'filters' => [
                'cohort' => 'Khóa 48',
                'major' => 'Công nghệ thông tin',
            ],
        ];

        $result = $this->retrievalService->retrieve($queryPlan);

        $this->assertTrue($result['success']);
        $this->assertEquals('Công nghệ thông tin - Khóa 48', $result['data']->title);
        $this->assertEquals('Công nghệ thông tin', $result['data']->faculty->name);
    }

    public function test_list_curriculum_courses(): void
    {
        $queryPlan = [
            'query_type' => 'list_curriculum_courses',
            'filters' => [
                'cohort' => 'Khóa 48',
                'major' => 'Công nghệ thông tin',
            ],
        ];

        $result = $this->retrievalService->retrieve($queryPlan);

        $this->assertTrue($result['success']);
        $this->assertCount(4, $result['data']);
        $this->assertEquals('COMP101', $result['data'][0]->course_code);
    }

    public function test_get_program_total_credits(): void
    {
        $queryPlan = [
            'query_type' => 'get_program_total_credits',
            'filters' => [
                'cohort' => '48',
                'major' => 'Công nghệ thông tin',
            ],
        ];

        $result = $this->retrievalService->retrieve($queryPlan);

        $this->assertTrue($result['success']);
        $this->assertEquals(135, $result['data']['total_credits']);
    }

    public function test_list_courses_by_semester(): void
    {
        $queryPlan = [
            'query_type' => 'list_courses_by_semester',
            'filters' => [
                'cohort' => 'Khóa 48',
                'major' => 'Công nghệ thông tin',
                'semester' => 1,
            ],
        ];

        $result = $this->retrievalService->retrieve($queryPlan);

        $this->assertTrue($result['success']);
        $this->assertCount(2, $result['data']);
    }

    public function test_find_course_detail(): void
    {
        $queryPlan = [
            'query_type' => 'find_course_detail',
            'filters' => [
                'cohort' => 'Khóa 48',
                'major' => 'Công nghệ thông tin',
                'course_code' => 'COMP101',
            ],
        ];

        $result = $this->retrievalService->retrieve($queryPlan);

        $this->assertTrue($result['success']);
        $this->assertEquals('Tin học đại cương', $result['data']->course_name);
        $this->assertEquals(30, $result['data']->theory_hours);
    }

    public function test_list_elective_courses(): void
    {
        $queryPlan = [
            'query_type' => 'list_elective_courses',
            'filters' => [
                'cohort' => 'Khóa 48',
                'major' => 'Công nghệ thông tin',
            ],
        ];

        $result = $this->retrievalService->retrieve($queryPlan);

        $this->assertTrue($result['success']);
        $this->assertCount(1, $result['data']);
        $this->assertEquals('IT303', $result['data'][0]->course_code);
    }

    public function test_list_required_courses(): void
    {
        $queryPlan = [
            'query_type' => 'list_required_courses',
            'filters' => [
                'cohort' => 'Khóa 48',
                'major' => 'Công nghệ thông tin',
            ],
        ];

        $result = $this->retrievalService->retrieve($queryPlan);

        $this->assertTrue($result['success']);
        $this->assertCount(3, $result['data']);
    }

    public function test_get_major_faculty(): void
    {
        $queryPlan = [
            'query_type' => 'get_major_faculty',
            'filters' => [
                'major' => 'Công nghệ thông tin',
            ],
        ];

        $result = $this->retrievalService->retrieve($queryPlan);

        $this->assertTrue($result['success']);
        $this->assertEquals('Công nghệ thông tin', $result['data']->name);
        $this->assertEquals('Công nghệ thông tin', $result['data']->faculty->name);
    }

    public function test_get_learning_outcomes(): void
    {
        $queryPlan = [
            'query_type' => 'get_learning_outcomes',
            'filters' => [
                'cohort' => 'Khóa 48',
                'major' => 'Công nghệ thông tin',
            ],
        ];

        $result = $this->retrievalService->retrieve($queryPlan);

        $this->assertTrue($result['success']);
        $this->assertCount(2, $result['data']);
    }
}
