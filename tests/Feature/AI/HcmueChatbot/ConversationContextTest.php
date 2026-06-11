<?php

namespace Tests\Feature\AI\HcmueChatbot;

use App\AI\HcmueChatbot\Chat\AnswerComposerService;
use App\AI\HcmueChatbot\Chat\ConversationContextService;
use App\AI\HcmueChatbot\Chat\HcmueChatService;
use App\AI\HcmueChatbot\LLM\EmbeddingService;
use App\Models\ChatSession;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ConversationContextTest extends TestCase
{
    use RefreshDatabase;

    protected string $qdrantUrl = 'http://localhost:6333';

    protected string $collection = 'hcmue_knowledge';

    protected function setUp(): void
    {
        parent::setUp();
        config(['ai.qdrant.url' => 'http://localhost:6333']);
        config(['ai.qdrant.collection' => 'hcmue_knowledge']);
    }

    public function test_student_policy_does_not_require_clarification_and_uses_rag(): void
    {
        // 1. Create a student user and a chat session
        $student = User::factory()->create(['email' => 'student@student.hcmue.edu.vn']);
        $session = ChatSession::create(['user_id' => $student->id, 'title' => 'Test Session']);

        // 2. Mock EmbeddingService
        $this->mock(EmbeddingService::class, function ($mock) {
            $mock->shouldReceive('batchEmbed')
                ->andReturnUsing(fn ($vars) => array_fill(0, count($vars), array_fill(0, 1024, 0.1)));
        });

        // 3. Fake Qdrant search
        Http::fake([
            "{$this->qdrantUrl}/collections/{$this->collection}/points/search" => Http::response(['result' => [
                [
                    'id' => 101,
                    'score' => 0.85,
                    'payload' => [
                        'source_document_id' => 1,
                        'document_name' => 'Sổ tay sinh viên.pdf',
                        'document_type' => 'student_handbook',
                        'khoa_hoc' => '2025 - Khóa 51',
                        'knowledge_type' => 'student_handbook',
                        'text' => 'Sinh viên học lại quá 5% số tín chỉ bị hạ bậc xếp loại tốt nghiệp.',
                    ],
                ],
            ]], 200),
        ]);

        // 4. Mock Composer to avoid real Gemini call
        $this->mock(AnswerComposerService::class, function ($mock) {
            $mock->shouldReceive('compose')->andReturn([
                'answer_text' => 'Sinh viên học lại bị hạ xếp loại tốt nghiệp theo Quy chế.',
                'model_provider' => 'gemini',
                'model_name' => 'gemini-2.0-flash',
                'latency_ms' => 12,
                'input_tokens' => 10,
                'output_tokens' => 10,
                'total_tokens' => 20,
            ]);
        });

        $chatService = $this->app->make(HcmueChatService::class);

        // Send query 1
        $res1 = $chatService->chat('Học lại bao nhiêu tín chỉ thì bị hạ bằng?', $session, $student);

        $this->assertFalse($res1['requires_clarification']);
        $this->assertEquals('rag', $res1['route']);
        $this->assertEquals('student_policy', $res1['intent']);
        $this->assertFalse($res1['is_follow_up']);

        // Verify context is saved in cache
        $contextService = $this->app->make(ConversationContextService::class);
        $context = $contextService->getContext($session->id);

        $this->assertEquals('student_policy', $context['last_intent']);
        $this->assertEquals('student_handbook', $context['last_knowledge_type']);
        $this->assertEquals('2025 - Khóa 51', $context['last_khoa_hoc']);

        // Send query 2 (follow-up)
        // Set up mock Qdrant response for the follow-up
        Http::fake([
            "{$this->qdrantUrl}/collections/{$this->collection}/points/search" => Http::response(['result' => [
                [
                    'id' => 102,
                    'score' => 0.82,
                    'payload' => [
                        'source_document_id' => 1,
                        'document_name' => 'Sổ tay sinh viên.pdf',
                        'document_type' => 'student_handbook',
                        'khoa_hoc' => '2021 - Khóa 47',
                        'knowledge_type' => 'student_handbook',
                        'text' => 'Sinh viên Khóa 47 học lại bị hạ bằng.',
                    ],
                ],
            ]], 200),
        ]);

        $res2 = $chatService->chat('Còn khóa 47 thì sao', $session, $student);

        $this->assertTrue($res2['is_follow_up']);
        $this->assertEquals('student_policy', $res2['inherited_intent']);
        $this->assertEquals('2021 - Khoá 47', $res2['overridden_cohort']);
        $this->assertFalse($res2['requires_clarification']);
    }

    public function test_decision_document_query_runs_fallback_sequence(): void
    {
        $student = User::factory()->create(['email' => 'student@student.hcmue.edu.vn']);
        $session = ChatSession::create(['user_id' => $student->id, 'title' => 'Test Session']);

        $this->mock(EmbeddingService::class, function ($mock) {
            $mock->shouldReceive('batchEmbed')
                ->andReturnUsing(fn ($vars) => array_fill(0, count($vars), array_fill(0, 1024, 0.1)));
        });

        Http::fake(function ($request) {
            $body = json_decode($request->body(), true);
            $filter = $body['filter'] ?? [];
            $must = $filter['must'] ?? [];

            $hasStrictFilter = false;
            $hasRelaxedFilter = false;

            foreach ($must as $clause) {
                $key = $clause['key'] ?? '';
                $val = $clause['match']['value'] ?? '';

                if ($key === 'loai_tai_lieu' && $val === 'quyet_dinh_ban_hanh') {
                    $hasStrictFilter = true;
                }
                if ($key === 'knowledge_type' && $val === 'student_handbook') {
                    $hasRelaxedFilter = true;
                }
            }

            if ($hasStrictFilter) {
                return Http::response(['result' => []], 200); // 0 results for strict
            }

            if ($hasRelaxedFilter) {
                return Http::response(['result' => [
                    [
                        'id' => 103,
                        'score' => 0.81,
                        'payload' => [
                            'source_document_id' => 1,
                            'document_name' => 'Sổ tay sinh viên.pdf',
                            'document_type' => 'student_handbook',
                            'knowledge_type' => 'student_handbook',
                            'text' => 'Quyết định ban hành Quy chế nghiên cứu khoa học của sinh viên.',
                        ],
                    ],
                ]], 200);
            }

            return Http::response(['result' => []], 200);
        });

        $this->mock(AnswerComposerService::class, function ($mock) {
            $mock->shouldReceive('compose')->andReturn([
                'answer_text' => 'Có 1 quyết định ban hành quy định nghiên cứu khoa học.',
                'model_provider' => 'gemini',
                'model_name' => 'gemini-2.0-flash',
                'latency_ms' => 10,
                'input_tokens' => 10,
                'output_tokens' => 10,
                'total_tokens' => 20,
            ]);
        });

        $chatService = $this->app->make(HcmueChatService::class);
        $res = $chatService->chat('Có mấy quyết định ban hành quy định về tổ chức hoạt động nghiên cứu khoa học của sinh viên?', $session, $student);

        $this->assertEquals('rag', $res['route']);
        $this->assertEquals('decision_document_query', $res['intent']);
        $this->assertCount(2, $res['fallback_attempts']);

        // Check fallback attempts
        $this->assertEquals('strict_decision_document', $res['fallback_attempts'][0]['attempt_name']);
        $this->assertEquals(0, $res['fallback_attempts'][0]['result_count']);

        $this->assertEquals('relaxed_student_handbook', $res['fallback_attempts'][1]['attempt_name']);
        $this->assertEquals(1, $res['fallback_attempts'][1]['result_count']);
    }

    public function test_major_detection_and_cohort_fallback_retrieval_and_followup_inheritance(): void
    {
        $student = User::factory()->create(['email' => 'student@student.hcmue.edu.vn']);
        $session = ChatSession::create(['user_id' => $student->id, 'title' => 'Test Session']);

        $this->mock(EmbeddingService::class, function ($mock) {
            $mock->shouldReceive('batchEmbed')
                ->andReturnUsing(fn ($vars) => array_fill(0, count($vars), array_fill(0, 1024, 0.1)));
        });

        // Mock Qdrant search to simulate Attempt 1 (cohort + major) returning 0 and Attempt 2 (cohort only) returning results
        Http::fake(function ($request) {
            $body = json_decode($request->body(), true);
            $filter = $body['filter'] ?? [];
            $must = $filter['must'] ?? [];

            $hasCohort = false;
            $hasMajor = false;

            foreach ($must as $clause) {
                $key = $clause['key'] ?? '';
                $val = $clause['match']['value'] ?? '';

                if ($key === 'khoa_hoc' && $val === '2021 - Khoá 47') {
                    $hasCohort = true;
                }
                if ($key === 'nganh' && $val === 'Giáo dục đặc biệt') {
                    $hasMajor = true;
                }
            }

            if ($hasCohort && $hasMajor) {
                return Http::response(['result' => []], 200); // Attempt 1 yields 0 points
            }

            if ($hasCohort && ! $hasMajor) {
                return Http::response(['result' => [
                    [
                        'id' => 201,
                        'score' => 0.86,
                        'payload' => [
                            'source_document_id' => 5,
                            'document_name' => '02_Chuong_trinh_khung_GDDB.pdf',
                            'document_type' => 'training_program',
                            'knowledge_type' => 'curriculum',
                            'khoa_hoc' => '2021 - Khoá 47',
                            'nganh' => 'Giáo dục đặc biệt',
                            'text' => 'Chương trình đào tạo ngành Giáo dục đặc biệt Khóa 47.',
                        ],
                    ],
                ]], 200);
            }

            return Http::response(['result' => []], 200);
        });

        $this->mock(AnswerComposerService::class, function ($mock) {
            $mock->shouldReceive('compose')->andReturn([
                'answer_text' => 'Chương trình GDĐB K47 cần 135 tín chỉ.',
                'model_provider' => 'gemini',
                'model_name' => 'gemini-2.5-flash',
                'latency_ms' => 10,
                'input_tokens' => 10,
                'output_tokens' => 10,
                'total_tokens' => 20,
            ]);
        });

        $chatService = $this->app->make(HcmueChatService::class);

        // Query 1: dynamic cohort & major detection + fallback retrieval check
        $res1 = $chatService->chat('Khóa 47 ngành giáo dục đặc biệt cần bao nhiêu tín chỉ để ra trường', $session, $student);

        $this->assertEquals('rag', $res1['route']);
        $this->assertEquals('2021 - Khoá 47', $res1['canonical_cohort']);
        $this->assertEquals('Giáo dục đặc biệt', $res1['canonical_major']);
        $this->assertEquals('giáo dục đặc biệt', $res1['matched_alias']);
        $this->assertCount(2, $res1['fallback_attempts']);

        $this->assertEquals('cohort_and_major', $res1['fallback_attempts'][0]['attempt_name']);
        $this->assertEquals(0, $res1['fallback_attempts'][0]['result_count']);

        $this->assertEquals('cohort_only', $res1['fallback_attempts'][1]['attempt_name']);
        $this->assertEquals(1, $res1['fallback_attempts'][1]['result_count']);

        // Query 2: follow-up context check
        // Reset HTTP fake for follow-up search
        Http::fake([
            '**/points/search' => Http::response(['result' => [
                [
                    'id' => 202,
                    'score' => 0.85,
                    'payload' => [
                        'source_document_id' => 5,
                        'document_name' => '02_Chuong_trinh_khung_GDDB.pdf',
                        'document_type' => 'training_program',
                        'knowledge_type' => 'curriculum',
                        'khoa_hoc' => '2021 - Khoá 47',
                        'nganh' => 'Giáo dục đặc biệt',
                        'text' => 'Sinh viên học lại GDĐB.',
                    ],
                ],
            ]], 200),
        ]);

        $res2 = $chatService->chat('Còn khóa 47 thì sao', $session, $student);

        $this->assertTrue($res2['is_follow_up']);
        $this->assertEquals('2021 - Khoá 47', $res2['canonical_cohort']);
        $this->assertEquals('Giáo dục đặc biệt', $res2['canonical_major']);
    }
}
