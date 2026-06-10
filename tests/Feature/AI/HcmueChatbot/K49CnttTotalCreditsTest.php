<?php

namespace Tests\Feature\AI\HcmueChatbot;

use App\AI\HcmueChatbot\Chat\HcmueChatService;
use App\AI\HcmueChatbot\Chat\QueryRouterService;
use App\AI\HcmueChatbot\Chat\QuestionNormalizerService;
use App\AI\HcmueChatbot\Chat\StructuredQueryPlannerService;
use App\AI\HcmueChatbot\Retrieval\StructuredRetrievalService;
use App\Models\AdmissionCohort;
use App\Models\ChatSession;
use App\Models\Faculty;
use App\Models\Major;
use App\Models\SourceDocument;
use App\Models\TrainingProgram;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class K49CnttTotalCreditsTest extends TestCase
{
    use RefreshDatabase;

    private function seedK49CnttProgram(int $credits = 135): TrainingProgram
    {
        $faculty = Faculty::create([
            'code' => 'CNTT',
            'name' => 'Công nghệ thông tin',
            'slug' => 'cong-nghe-thong-tin',
        ]);

        $major = Major::create([
            'code' => '7480201',
            'name' => 'Công nghệ thông tin',
            'normalized_name' => 'công nghệ thông tin',
            'slug' => 'cong-nghe-thong-tin',
            'faculty_id' => $faculty->id,
            'degree_level' => 'undergraduate',
        ]);

        $cohort = AdmissionCohort::create([
            'year' => 2023,
            'cohort_name' => 'K49',
            'normalized_name' => 'k49',
        ]);

        $program = TrainingProgram::create([
            'major_id' => $major->id,
            'cohort_id' => $cohort->id,
            'faculty_id' => $faculty->id,
            'title' => 'Công nghệ thông tin - K49',
            'total_credits' => $credits,
            'effective_from' => 2023,
        ]);

        SourceDocument::create([
            'title' => 'Chương trình đào tạo ngành Công nghệ thông tin K49',
            'document_type' => 'training_program',
            'cohort' => 'K49',
            'faculty' => 'Khoa Công nghệ thông tin',
            'major' => 'Công nghệ thông tin',
            'file_path' => 'C:/laragon/www/Old1/database/AI/Chuongtrinhdaotao/CNTT_K49.pdf',
            'status' => 'active',
            'effective_year' => 2023,
        ]);

        return $program;
    }

    public function test_normalization_k49_cntt_credits(): void
    {
        $normalizer = $this->app->make(QuestionNormalizerService::class);
        $result = $normalizer->normalize('K49 CNTT cần bao nhiêu tín chỉ để tốt nghiệp?');

        $this->assertStringContainsString('Công nghệ thông tin', $result['normalized_question']);
        // K49 is now expanded to "2023 - Khóa 49" by the normalizer
        $this->assertStringContainsString('2023 - Khóa 49', $result['normalized_question']);
        $this->assertEquals('2023 - Khóa 49', $result['detected_terms']['cohort']);
        $this->assertEquals('Công nghệ thông tin', $result['detected_terms']['major']);
    }

    public function test_routing_to_structured_db(): void
    {
        $router = $this->app->make(QueryRouterService::class);
        $result = $router->route(
            'K49 Công nghệ thông tin cần bao nhiêu tín chỉ để tốt nghiệp?',
            [
                'cohort' => 'K49',
                'major' => 'Công nghệ thông tin',
                'faculty' => null,
                'course' => null,
                'policy_topic' => null,
            ]
        );

        $this->assertEquals('structured_db', $result['source']);
        $this->assertEquals('curriculum_course_lookup', $result['intent']);
    }

    public function test_planning_and_retrieval_k49_cntt_credits(): void
    {
        $this->seedK49CnttProgram(132);

        $planner = $this->app->make(StructuredQueryPlannerService::class);
        $routerResult = [
            'intent' => 'curriculum_course_lookup',
            'source' => 'structured_db',
            'confidence' => 0.95,
            'entities' => [
                'cohort' => 'K49',
                'major' => 'Công nghệ thông tin',
            ],
            'missing_required_fields' => [],
        ];

        $queryPlan = $planner->plan($routerResult, 'K49 Công nghệ thông tin cần bao nhiêu tín chỉ để tốt nghiệp?');

        $this->assertEquals('get_program_total_credits', $queryPlan['query_type']);
        $this->assertEquals('K49', $queryPlan['filters']['cohort']);
        $this->assertEquals('Công nghệ thông tin', $queryPlan['filters']['major']);

        $retrieval = $this->app->make(StructuredRetrievalService::class);
        $retrievalResult = $retrieval->retrieve($queryPlan);

        $this->assertTrue($retrievalResult['success']);
        $this->assertEquals(132, $retrievalResult['data']['total_credits']);
    }

    public function test_end_to_end_chat_response_k49_cntt_credits(): void
    {
        $this->seedK49CnttProgram(140);

        $user = User::factory()->create();
        $session = ChatSession::create([
            'user_id' => $user->id,
            'title' => 'Test Session',
        ]);

        $chatService = $this->app->make(HcmueChatService::class);
        $response = $chatService->chat('K49 CNTT cần bao nhiêu tín chỉ để tốt nghiệp?', $session, $user);

        $this->assertEquals('structured_db', $response['route']);
        $this->assertFalse($response['requires_clarification']);

        // The answer is composed by the LLM using structured DB data.
        // We assert the pipeline produced an answer string (LLM 503 may vary in CI).
        $this->assertIsString($response['answer']);
        $this->assertNotEmpty($response['answer']);
    }
}
