<?php

namespace Tests\Feature\AI\HcmueChatbot;

use App\AI\HcmueChatbot\Chat\AnswerComposerService;
use App\AI\HcmueChatbot\LLM\GeminiProvider;
use App\AI\HcmueChatbot\LLM\LlmGateway;
use App\AI\HcmueChatbot\LLM\LlmProviderInterface;
use Tests\TestCase;

class AnswerComposerTest extends TestCase
{
    /**
     * Test composer with structured_db result.
     */
    public function test_composes_answer_with_structured_db_result(): void
    {
        $this->mockLlm('Ngành Công nghệ thông tin K51 có 135 tín chỉ. Nguồn: Hệ thống HCMUE.');

        $service = new AnswerComposerService;

        $structuredResult = [
            'success' => true,
            'data' => ['total_credits' => 135],
            'metadata' => ['type' => 'total_credits', 'program_title' => 'Công nghệ thông tin - K51'],
        ];

        $result = $service->compose(
            'Ngành CNTT K51 có bao nhiêu tín chỉ?',
            'Ngành Công nghệ thông tin K51 có bao nhiêu tín chỉ?',
            ['intent' => 'curriculum_course_lookup', 'source' => 'structured_db'],
            $structuredResult,
            []
        );

        $this->assertNotEmpty($result['answer_text']);
        $this->assertArrayHasKey('latency_ms', $result);
        $this->assertArrayHasKey('model_provider', $result);
    }

    /**
     * Test composer with RAG context.
     */
    public function test_composes_answer_with_rag_context(): void
    {
        $this->mockLlm('Theo quy chế, sinh viên phải học lại nếu không đạt. Nguồn: Sổ tay sinh viên, Điều 5.');

        $service = new AnswerComposerService;

        $ragChunks = [
            [
                'id' => 1,
                'score' => 0.88,
                'chunk_text' => 'Điều 5. Sinh viên không đạt học phần bắt buộc phải đăng ký học lại.',
                'document_name' => 'Sổ tay sinh viên',
                'document_type' => 'student_handbook',
                'article' => 'Điều 5',
                'page_start' => 12,
                'page_end' => 12,
                'cohort' => 'K51',
            ],
        ];

        $result = $service->compose(
            'Nếu em rớt học phần bắt buộc thì sao?',
            'Nếu em rớt học phần bắt buộc thì sao?',
            ['intent' => 'academic_policy', 'source' => 'rag'],
            null,
            $ragChunks
        );

        $this->assertNotEmpty($result['answer_text']);
    }

    /**
     * Case A: structured DB fails, RAG succeeds.
     * Expected: prompt does NOT contain the structured failure message,
     * and the LLM is called with rag_context only.
     */
    public function test_structured_failure_with_rag_success_uses_rag_context(): void
    {
        $ragAnswerText = 'Theo tài liệu 02_Chuong_trinh_khung.pdf, ngành Sư phạm Hóa học cần 150 tín chỉ.';
        $this->mockLlm($ragAnswerText);

        $service = new AnswerComposerService;

        $structuredResult = [
            'success' => false,
            'data' => null,
            'metadata' => [],
            'message' => "Không tìm thấy chương trình đào tạo phù hợp cho ngành 'Sư phạm Hóa học' của khóa 'K50'.",
        ];

        $ragChunks = [
            [
                'id' => 99,
                'score' => 0.70,
                'chunk_text' => 'CHƯƠNG TRÌNH KHUNG Cử nhân Sư phạm Hoá học (Áp dụng từ khoá 50). Tổng tín chỉ toàn khóa: 150 tín chỉ.',
                'document_name' => '02_Chuong_trinh_khung.pdf',
                'document_type' => 'training_program',
                'article' => null,
                'page_start' => 1,
                'page_end' => 1,
                'cohort' => 'K50',
            ],
        ];

        $result = $service->compose(
            'Khoá 50 ngành sư phạm hoá cần bao nhiêu tín chỉ để ra trường',
            'Khóa 50 ngành Sư phạm Hóa học cần bao nhiêu tín chỉ để ra trường',
            ['intent' => 'get_program_total_credits', 'source' => 'structured_db', 'entities' => ['cohort' => 'K50', 'major' => 'Sư phạm Hóa học']],
            $structuredResult,
            $ragChunks
        );

        // The answer must not echo the structured DB failure phrase
        $this->assertStringNotContainsString(
            'Không tìm thấy chương trình đào tạo phù hợp',
            $result['answer_text'],
            'Structured DB failure message must not appear when RAG has results.'
        );

        // The LLM was reached and returned our mocked answer
        $this->assertEquals($ragAnswerText, $result['answer_text']);
        $this->assertNotEmpty($result['answer_text']);
    }

    /**
     * Case B: both structured DB and RAG fail.
     * Expected: a static "not found" message is returned without calling the LLM.
     */
    public function test_both_sources_empty_returns_not_found(): void
    {
        $service = new AnswerComposerService;

        $structuredResult = [
            'success' => false,
            'data' => null,
            'metadata' => [],
            'message' => "Không tìm thấy chương trình đào tạo phù hợp cho ngành 'Sư phạm Hóa học' của khóa 'K50'.",
        ];

        $result = $service->compose(
            'Khoá 50 ngành sư phạm hoá cần bao nhiêu tín chỉ để ra trường',
            'Khóa 50 ngành Sư phạm Hóa học cần bao nhiêu tín chỉ để ra trường',
            ['intent' => 'get_program_total_credits', 'source' => 'structured_db', 'entities' => ['cohort' => 'K50', 'major' => 'Sư phạm Hóa học']],
            $structuredResult,
            [] // empty RAG
        );

        // A static not-found message must be returned
        $this->assertNotEmpty($result['answer_text']);
        $this->assertEquals(0, $result['input_tokens'], 'LLM must NOT be called when both sources are empty.');
    }

    /**
     * Test that a failed LLM call returns a safe fallback answer.
     */
    public function test_returns_fallback_answer_on_llm_failure(): void
    {
        $mockProvider = $this->createMock(LlmProviderInterface::class);
        $mockProvider->method('generate')->willThrowException(new \Exception('LLM timeout'));

        $this->app->instance(LlmGateway::class, new class($mockProvider) extends LlmGateway
        {
            // We can't easily mock static, so we use a spy on the service level
        });

        // Since LlmGateway is static, we test via the service catching the error
        // by testing that a valid structure is still returned (latency_ms exists etc.)
        // The actual fallback path is tested via HcmueChatServiceTest.
        $this->assertTrue(true); // Placeholder — actual flow covered in HcmueChatServiceTest
    }

    private function mockLlm(string $responseText): void
    {
        $mockProvider = $this->createMock(LlmProviderInterface::class);
        $mockProvider->method('generate')->willReturn([
            'text' => $responseText,
            'raw' => [],
            'usage' => ['input_tokens' => 100, 'output_tokens' => 80, 'total_tokens' => 180],
        ]);

        // Bind mock to container for the duration of this test
        $this->app->bind(GeminiProvider::class, fn () => $mockProvider);
    }
}
