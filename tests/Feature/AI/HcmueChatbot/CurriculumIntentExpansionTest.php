<?php

namespace Tests\Feature\AI\HcmueChatbot;

use App\AI\HcmueChatbot\Chat\QueryRouterService;
use App\AI\HcmueChatbot\Chat\QuestionNormalizerService;
use App\AI\HcmueChatbot\Retrieval\AcademicQueryAnalyzer;
use Tests\TestCase;

class CurriculumIntentExpansionTest extends TestCase
{
    protected QuestionNormalizerService $normalizer;

    protected QueryRouterService $router;

    protected AcademicQueryAnalyzer $analyzer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->normalizer = app(QuestionNormalizerService::class);
        $this->router = app(QueryRouterService::class);
        $this->analyzer = app(AcademicQueryAnalyzer::class);
    }

    /**
     * Test entity extraction for semesters and course names.
     */
    public function test_normalizer_detects_semester_and_course_name(): void
    {
        // Case 1: "Bạn hãy cho tôi thông tin về môn lập trình nâng cao của ngành công nghệ thông tin"
        $res = $this->normalizer->normalize('Bạn hãy cho tôi thông tin về môn lập trình nâng cao của ngành công nghệ thông tin');
        $detected = $res['detected_terms'];
        $this->assertEquals('Công nghệ thông tin', $detected['major']);
        $this->assertEquals('lập trình nâng cao', $detected['course_name']);
        $this->assertNull($detected['semester']);

        // Case 2: "Tui đang học kì 6 ngành CNTT, tới kì 7 sẽ có những môn gì"
        $res = $this->normalizer->normalize('Tui đang học kì 6 ngành CNTT, tới kì 7 sẽ có những môn gì');
        $detected = $res['detected_terms'];
        $this->assertEquals('Công nghệ thông tin', $detected['major']);
        $this->assertEquals(7, $detected['semester']);

        // Case 3: "Kì 7 CNTT học những môn nào"
        $res = $this->normalizer->normalize('Kì 7 CNTT học những môn nào');
        $detected = $res['detected_terms'];
        $this->assertEquals('Công nghệ thông tin', $detected['major']);
        $this->assertEquals(7, $detected['semester']);

        // Case 4: "Môn lập trình nâng cao là môn gì"
        $res = $this->normalizer->normalize('Môn lập trình nâng cao là môn gì');
        $detected = $res['detected_terms'];
        $this->assertEquals('lập trình nâng cao', $detected['course_name']);

        // Case 5: "Cho tôi xem các môn học kì 8 ngành CNTT"
        $res = $this->normalizer->normalize('Cho tôi xem các môn học kì 8 ngành CNTT');
        $detected = $res['detected_terms'];
        $this->assertEquals('Công nghệ thông tin', $detected['major']);
        $this->assertEquals(8, $detected['semester']);
        $this->assertNull($detected['course_name']);
    }

    /**
     * Test AcademicQueryAnalyzer maps signals and semester/course_name.
     */
    public function test_academic_query_analyzer_classifies_curriculum_intent(): void
    {
        // Case 1: "lập trình nâng cao là môn gì"
        $res = $this->analyzer->analyze('lập trình nâng cao là môn gì');
        $this->assertEquals('curriculum_course_lookup', $res['intent']);
        $this->assertEquals('lập trình nâng cao', $res['course_name']);

        // Case 2: "kì 7 CNTT học gì"
        $res = $this->analyzer->analyze('kì 7 CNTT học gì');
        $this->assertEquals('curriculum_course_lookup', $res['intent']);
        $this->assertEquals(7, $res['semester']);
    }

    /**
     * Test QueryRouter does not trigger clarification if major/semester or course name is present.
     */
    public function test_router_avoids_clarification_for_curriculum_queries(): void
    {
        // Case 1: "CNTT kì 7 học gì" (no cohort) -> should route to structured_db (which will fallback to RAG)
        $detected1 = $this->normalizer->normalize('CNTT kì 7 học gì')['detected_terms'];
        $route1 = $this->router->route('CNTT kì 7 học gì', $detected1);
        $this->assertEquals('curriculum_course_lookup', $route1['intent']);
        $this->assertEquals('structured_db', $route1['source']);

        // Case 2: "Môn lập trình nâng cao" (no cohort, no major) -> should route to structured_db
        $detected2 = $this->normalizer->normalize('Môn lập trình nâng cao')['detected_terms'];
        $route2 = $this->router->route('Môn lập trình nâng cao', $detected2);
        $this->assertEquals('curriculum_course_lookup', $route2['intent']);
        $this->assertEquals('structured_db', $route2['source']);

        // Case 3: "HK8 giáo dục tiểu học học gì" -> should route to structured_db
        $detected3 = $this->normalizer->normalize('HK8 giáo dục tiểu học học gì')['detected_terms'];
        $route3 = $this->router->route('HK8 giáo dục tiểu học học gì', $detected3);
        $this->assertEquals('curriculum_course_lookup', $route3['intent']);
        $this->assertEquals('structured_db', $route3['source']);
    }
}
