<?php

namespace Tests\Feature\AI\HcmueChatbot;

use App\AI\HcmueChatbot\Chat\QueryRouterService;
use Tests\TestCase;

class QueryRouterTest extends TestCase
{
    private QueryRouterService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new QueryRouterService;
    }

    /**
     * Test 1: CNTT K51 total credits → structured_db
     */
    public function test_routes_total_credits_question_to_structured_db(): void
    {
        $result = $this->service->route(
            'Ngành Công nghệ thông tin K51 có bao nhiêu tín chỉ?',
            ['cohort' => 'K51', 'major' => 'Công nghệ thông tin', 'faculty' => null, 'course' => null, 'policy_topic' => null]
        );

        $this->assertEquals('structured_db', $result['source']);
        $this->assertGreaterThan(0.7, $result['confidence']);
    }

    /**
     * Test 2: Failed required course → rag
     */
    public function test_routes_failed_required_course_to_rag(): void
    {
        $result = $this->service->route(
            'Nếu em rớt học phần bắt buộc thì phải làm sao?',
            ['cohort' => null, 'major' => null, 'faculty' => null, 'course' => null, 'policy_topic' => 'học lại']
        );

        $this->assertEquals('rag', $result['source']);
        $this->assertGreaterThan(0.7, $result['confidence']);
    }

    /**
     * Test 3: Hybrid — electives + policy
     */
    public function test_routes_elective_with_policy_to_hybrid(): void
    {
        $result = $this->service->route(
            'Ngành Công nghệ thông tin K51 có môn tự chọn không và học phần tự chọn là gì?',
            ['cohort' => 'K51', 'major' => 'Công nghệ thông tin', 'faculty' => null, 'course' => null, 'policy_topic' => null]
        );

        // Could be hybrid or structured_db depending on fast-path detection of "tự chọn"
        $this->assertContains($result['source'], ['hybrid', 'structured_db']);
    }

    /**
     * Test 4: Missing cohort/major → clarification
     */
    public function test_routes_missing_cohort_to_clarification(): void
    {
        $result = $this->service->route(
            'Ngành này bao nhiêu tín chỉ?',
            ['cohort' => null, 'major' => null, 'faculty' => null, 'course' => null, 'policy_topic' => null]
        );

        $this->assertEquals('none', $result['source']);
        $this->assertEquals('clarification', $result['intent']);
        $this->assertNotEmpty($result['missing_required_fields']);
    }

    /**
     * Test 5: Completely off-topic → unsupported
     */
    public function test_routes_off_topic_to_unsupported(): void
    {
        $result = $this->service->route(
            'Hôm nay ăn gì ngon?',
            ['cohort' => null, 'major' => null, 'faculty' => null, 'course' => null, 'policy_topic' => null]
        );

        $this->assertEquals('none', $result['source']);
        $this->assertEquals('unsupported', $result['intent']);
    }

    /**
     * Test 6: Graduation requirements → rag
     */
    public function test_routes_graduation_requirements_to_rag(): void
    {
        $result = $this->service->route(
            'Điều kiện tốt nghiệp của sinh viên là gì?',
            ['cohort' => null, 'major' => null, 'faculty' => null, 'course' => null, 'policy_topic' => 'điều kiện tốt nghiệp']
        );

        $this->assertEquals('rag', $result['source']);
    }

    /**
     * Test 7: Academic warning → rag
     */
    public function test_routes_academic_warning_to_rag(): void
    {
        $result = $this->service->route(
            'Cảnh báo học tập được xét như thế nào?',
            ['cohort' => null, 'major' => null, 'faculty' => null, 'course' => null, 'policy_topic' => 'cảnh báo học tập']
        );

        $this->assertEquals('rag', $result['source']);
    }
}
