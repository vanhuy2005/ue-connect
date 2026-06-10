<?php

namespace Tests\Feature\AI\HcmueChatbot;

use App\AI\HcmueChatbot\Chat\StructuredQueryPlannerService;
use Tests\TestCase;

class StructuredQueryPlannerTest extends TestCase
{
    private StructuredQueryPlannerService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new StructuredQueryPlannerService;
    }

    public function test_plans_total_credits_query(): void
    {
        $routerResult = [
            'intent' => 'training_program_lookup',
            'source' => 'structured_db',
            'confidence' => 0.9,
            'entities' => ['cohort' => 'K51', 'major' => 'Công nghệ thông tin', 'semester' => null, 'course_code' => null, 'course_name' => null],
            'missing_required_fields' => [],
            'reason' => '',
        ];

        $plan = $this->service->plan($routerResult, 'Ngành Công nghệ thông tin K51 có bao nhiêu tín chỉ?');

        $this->assertEquals('get_program_total_credits', $plan['query_type']);
        $this->assertEquals('K51', $plan['filters']['cohort']);
        $this->assertEquals('Công nghệ thông tin', $plan['filters']['major']);
        $this->assertFalse($plan['requires_clarification']);
    }

    public function test_plans_course_list_query(): void
    {
        $routerResult = [
            'intent' => 'curriculum_course_lookup',
            'source' => 'structured_db',
            'confidence' => 0.9,
            'entities' => ['cohort' => 'K51', 'major' => 'Công nghệ thông tin', 'semester' => null, 'course_code' => null, 'course_name' => null],
            'missing_required_fields' => [],
            'reason' => '',
        ];

        $plan = $this->service->plan($routerResult, 'Ngành Công nghệ thông tin K51 học những môn gì?');

        $this->assertEquals('list_curriculum_courses', $plan['query_type']);
        $this->assertFalse($plan['requires_clarification']);
    }

    public function test_plans_elective_courses_query(): void
    {
        $routerResult = [
            'intent' => 'curriculum_course_lookup',
            'source' => 'structured_db',
            'confidence' => 0.88,
            'entities' => ['cohort' => 'K51', 'major' => 'Công nghệ thông tin', 'semester' => null, 'course_code' => null, 'course_name' => null],
            'missing_required_fields' => [],
            'reason' => '',
        ];

        $plan = $this->service->plan($routerResult, 'Ngành Công nghệ thông tin K51 có môn tự chọn không?');

        $this->assertEquals('list_elective_courses', $plan['query_type']);
        $this->assertEquals('elective', $plan['filters']['course_type']);
    }

    public function test_plans_semester_courses_query(): void
    {
        $routerResult = [
            'intent' => 'curriculum_course_lookup',
            'source' => 'structured_db',
            'confidence' => 0.88,
            'entities' => ['cohort' => 'K51', 'major' => 'Công nghệ thông tin', 'semester' => 1, 'course_code' => null, 'course_name' => null],
            'missing_required_fields' => [],
            'reason' => '',
        ];

        $plan = $this->service->plan($routerResult, 'Học kỳ 1 ngành Công nghệ thông tin K51 có môn nào?');

        $this->assertEquals('list_courses_by_semester', $plan['query_type']);
        $this->assertEquals(1, $plan['filters']['semester']);
    }

    public function test_returns_clarification_for_missing_cohort(): void
    {
        $routerResult = [
            'intent' => 'clarification',
            'source' => 'none',
            'confidence' => 0.9,
            'entities' => [],
            'missing_required_fields' => ['cohort', 'major'],
            'reason' => 'Thiếu thông tin',
        ];

        $plan = $this->service->plan($routerResult, 'Ngành này học bao nhiêu tín chỉ?');

        $this->assertTrue($plan['requires_clarification']);
        $this->assertNotEmpty($plan['clarification_question']);
    }
}
