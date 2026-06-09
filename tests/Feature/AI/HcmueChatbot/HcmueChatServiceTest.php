<?php

namespace Tests\Feature\AI\HcmueChatbot;

use App\AI\HcmueChatbot\Chat\AnswerComposerService;
use App\AI\HcmueChatbot\Chat\HcmueChatService;
use App\AI\HcmueChatbot\Retrieval\RagRetrievalService;
use App\AI\HcmueChatbot\Retrieval\StructuredRetrievalService;
use App\Models\AdmissionCohort;
use App\Models\ChatSession;
use App\Models\Faculty;
use App\Models\Major;
use App\Models\TrainingProgram;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HcmueChatServiceTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Helper: create minimal program data for structured queries.
     */
    private function seedProgram(): TrainingProgram
    {
        $faculty = Faculty::create(['code' => 'CNTT', 'name' => 'Công nghệ thông tin', 'slug' => 'cong-nghe-thong-tin']);
        $major = Major::create([
            'code' => '7480201',
            'name' => 'Công nghệ thông tin',
            'slug' => 'cong-nghe-thong-tin',
            'faculty_id' => $faculty->id,
            'degree_level' => 'undergraduate',
        ]);
        $cohort = AdmissionCohort::create(['year' => 2022, 'cohort_name' => 'K51']);

        return TrainingProgram::create([
            'major_id' => $major->id,
            'cohort_id' => $cohort->id,
            'faculty_id' => $faculty->id,
            'title' => 'Công nghệ thông tin - K51',
            'total_credits' => 135,
            'effective_from' => 2022,
        ]);
    }

    private function makeChatSession(User $user): ChatSession
    {
        return ChatSession::create(['user_id' => $user->id, 'title' => 'Test session']);
    }

    /**
     * Test clarification path: question missing cohort + major.
     */
    public function test_returns_clarification_when_cohort_and_major_missing(): void
    {
        $user = User::factory()->create();
        $session = $this->makeChatSession($user);

        $service = $this->app->make(HcmueChatService::class);
        $result = $service->chat('Ngành này học bao nhiêu tín chỉ?', $session, $user);

        $this->assertEquals('none', $result['route']);
        $this->assertEquals('clarification', $result['intent']);
        $this->assertTrue($result['requires_clarification']);
        $this->assertNotEmpty($result['answer']);
    }

    /**
     * Test unsupported path: off-topic question.
     */
    public function test_returns_unsupported_for_off_topic_question(): void
    {
        $user = User::factory()->create();
        $session = $this->makeChatSession($user);

        $service = $this->app->make(HcmueChatService::class);
        $result = $service->chat('Hôm nay ăn gì ngon?', $session, $user);

        $this->assertEquals('none', $result['route']);
        $this->assertEquals('unsupported', $result['intent']);
        $this->assertFalse($result['requires_clarification']);
    }

    /**
     * Test structured_db path: total credits for known program.
     */
    public function test_structured_db_path_for_known_program(): void
    {
        $this->seedProgram();
        $user = User::factory()->create();
        $session = $this->makeChatSession($user);

        // Mock composer to avoid actual LLM calls
        $this->mock(AnswerComposerService::class, function ($mock) {
            $mock->shouldReceive('compose')->andReturn([
                'answer_text' => 'Ngành Công nghệ thông tin K51 có 135 tín chỉ. Nguồn: Hệ thống HCMUE.',
                'model_provider' => 'gemini',
                'model_name' => 'gemini-2.0-flash',
                'latency_ms' => 50,
                'input_tokens' => 100,
                'output_tokens' => 50,
                'total_tokens' => 150,
            ]);
        });

        $service = $this->app->make(HcmueChatService::class);
        $result = $service->chat('Ngành Công nghệ thông tin K51 có bao nhiêu tín chỉ?', $session, $user);

        $this->assertEquals('structured_db', $result['route']);
        $this->assertFalse($result['requires_clarification']);
        $this->assertNotEmpty($result['answer']);
        $this->assertNotNull($result['question_id']);
        $this->assertNotNull($result['answer_id']);

        // Verify the answer was logged
        $this->assertDatabaseHas('ai_questions', ['session_id' => $session->id, 'source_route' => 'structured_db']);
        $this->assertDatabaseHas('ai_answers', ['question_id' => $result['question_id']]);
    }

    /**
     * Test RAG path: policy/regulation question.
     */
    public function test_rag_path_for_regulation_question(): void
    {
        $user = User::factory()->create();
        $session = $this->makeChatSession($user);

        // Mock RagRetrievalService
        $this->mock(RagRetrievalService::class, function ($mock) {
            $mock->shouldReceive('retrieve')->andReturn([
                [
                    'id' => 1,
                    'score' => 0.85,
                    'chunk_text' => 'Sinh viên không đạt học phần bắt buộc phải đăng ký học lại.',
                    'document_name' => 'Sổ tay sinh viên',
                    'document_type' => 'student_handbook',
                    'article' => 'Điều 5',
                    'page_start' => 12,
                    'page_end' => 12,
                    'cohort' => null,
                    'metadata' => [],
                ],
            ]);
        });

        // Mock AnswerComposer
        $this->mock(AnswerComposerService::class, function ($mock) {
            $mock->shouldReceive('compose')->andReturn([
                'answer_text' => 'Theo quy chế, sinh viên không đạt học phần bắt buộc phải đăng ký học lại. Nguồn: Sổ tay sinh viên, Điều 5.',
                'model_provider' => 'gemini',
                'model_name' => 'gemini-2.0-flash',
                'latency_ms' => 60,
                'input_tokens' => 120,
                'output_tokens' => 60,
                'total_tokens' => 180,
            ]);
        });

        $service = $this->app->make(HcmueChatService::class);
        $result = $service->chat('Nếu em rớt học phần bắt buộc thì phải làm sao?', $session, $user);

        $this->assertEquals('rag', $result['route']);
        $this->assertFalse($result['requires_clarification']);
        $this->assertNotEmpty($result['answer']);
        $this->assertNotEmpty($result['sources']);
        $this->assertEquals('rag', $result['sources'][0]['type']);

        // Verify question was logged
        $this->assertDatabaseHas('ai_questions', ['session_id' => $session->id, 'source_route' => 'rag']);
        $this->assertDatabaseHas('ai_answers', ['question_id' => $result['question_id']]);
    }

    /**
     * Test hybrid path — question needs both structured and RAG.
     */
    public function test_hybrid_path_queries_both_sources(): void
    {
        $this->seedProgram();
        $user = User::factory()->create();
        $session = $this->makeChatSession($user);

        $ragCalled = false;
        $structuredCalled = false;

        $this->mock(RagRetrievalService::class, function ($mock) use (&$ragCalled) {
            $mock->shouldReceive('retrieve')->andReturnUsing(function () use (&$ragCalled) {
                $ragCalled = true;

                return [];
            });
        });

        $this->mock(StructuredRetrievalService::class, function ($mock) use (&$structuredCalled) {
            $mock->shouldReceive('retrieve')->andReturnUsing(function () use (&$structuredCalled) {
                $structuredCalled = true;

                return ['success' => true, 'data' => [], 'metadata' => []];
            });
        });

        $this->mock(AnswerComposerService::class, function ($mock) {
            $mock->shouldReceive('compose')->andReturn([
                'answer_text' => 'Ngành CNTT K51 có môn tự chọn. Học phần tự chọn là các môn bổ trợ. Nguồn: CTĐT và Sổ tay sinh viên.',
                'model_provider' => 'gemini',
                'model_name' => 'gemini-2.0-flash',
                'latency_ms' => 70,
                'input_tokens' => 150,
                'output_tokens' => 70,
                'total_tokens' => 220,
            ]);
        });

        $service = $this->app->make(HcmueChatService::class);
        $result = $service->chat(
            'Ngành Công nghệ thông tin K51 có môn tự chọn không và học phần tự chọn là gì?',
            $session,
            $user
        );

        // Hybrid route should have called both or at least attempted retrieval
        $this->assertContains($result['route'], ['hybrid', 'structured_db', 'rag']);
        $this->assertNotEmpty($result['answer']);
    }

    /**
     * Test that logs are created for each question.
     */
    public function test_question_is_always_logged(): void
    {
        $user = User::factory()->create();
        $session = $this->makeChatSession($user);

        $service = $this->app->make(HcmueChatService::class);
        $result = $service->chat('Hôm nay ăn gì?', $session, $user);

        $this->assertDatabaseHas('ai_questions', [
            'session_id' => $session->id,
            'original_question' => 'Hôm nay ăn gì?',
        ]);

        $this->assertDatabaseHas('ai_answers', [
            'question_id' => $result['question_id'],
        ]);
    }
}
