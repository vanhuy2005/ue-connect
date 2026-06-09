<?php

namespace Tests\Feature\AI\HcmueChatbot;

use App\AI\HcmueChatbot\Chat\AnswerComposerService;
use App\AI\HcmueChatbot\LLM\GeminiProvider;
use App\AI\HcmueChatbot\LLM\LlmGateway;
use App\AI\HcmueChatbot\LLM\OllamaProvider;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class LlmGatewayOllamaFallbackTest extends TestCase
{
    /**
     * LlmGateway::driver('ollama') returns an OllamaProvider instance.
     */
    public function test_gateway_returns_ollama_provider_when_driver_is_ollama(): void
    {
        $provider = LlmGateway::driver('ollama');

        $this->assertInstanceOf(OllamaProvider::class, $provider);
    }

    /**
     * LlmGateway::driver('gemini') returns a GeminiProvider instance.
     */
    public function test_gateway_returns_gemini_provider_when_driver_is_gemini(): void
    {
        $provider = LlmGateway::driver('gemini');

        $this->assertInstanceOf(GeminiProvider::class, $provider);
    }

    /**
     * driverWithFallback() sets fallback_enabled=true when primary is ollama.
     */
    public function test_driver_with_fallback_sets_fallback_enabled_for_ollama(): void
    {
        config(['ai.llm_provider' => 'ollama']);
        config(['ai.ollama.fallback_enabled' => true]);
        config(['ai.ollama.fallback_provider' => 'gemini']);

        $gateway = LlmGateway::driverWithFallback();

        $this->assertSame('ollama', $gateway['primary']);
        $this->assertTrue($gateway['fallback_enabled']);
        $this->assertSame('gemini', $gateway['fallback_provider']);
    }

    /**
     * driverWithFallback() sets fallback_enabled=false for non-ollama providers.
     */
    public function test_driver_with_fallback_sets_fallback_disabled_for_gemini(): void
    {
        config(['ai.llm_provider' => 'gemini']);

        $gateway = LlmGateway::driverWithFallback();

        $this->assertSame('gemini', $gateway['primary']);
        $this->assertFalse($gateway['fallback_enabled']);
    }

    /**
     * AnswerComposerService falls back to Gemini when Ollama fails and fallback is enabled.
     */
    public function test_answer_composer_falls_back_to_gemini_when_ollama_fails(): void
    {
        config(['ai.llm_provider' => 'ollama']);
        config(['ai.ollama.fallback_enabled' => true]);
        config(['ai.ollama.fallback_provider' => 'gemini']);
        config(['ai.ollama.chat_model' => 'gemma4:e2b']);
        config(['ai.ollama.base_url' => 'http://127.0.0.1:11434']);

        // Ollama /api/chat fails
        Http::fake([
            'http://127.0.0.1:11434/api/chat' => function () {
                throw new ConnectionException('Connection refused');
            },
            // Gemini succeeds
            '*generativelanguage.googleapis.com*' => Http::response([
                'candidates' => [
                    ['content' => ['parts' => [['text' => 'Fallback answer from Gemini.']]]],
                ],
                'usageMetadata' => ['promptTokenCount' => 50, 'candidatesTokenCount' => 20, 'totalTokenCount' => 70],
            ], 200),
        ]);

        $service = new AnswerComposerService;
        $result = $service->compose(
            'Câu hỏi test',
            'Câu hỏi test',
            ['intent' => 'academic_policy', 'source' => 'rag'],
            null,
            []
        );

        $this->assertNotEmpty($result['answer_text']);
        $this->assertTrue($result['fallback_used']);
        $this->assertSame('gemini', $result['fallback_provider']);
    }

    /**
     * AnswerComposerService returns safe error when Ollama fails and fallback is disabled.
     */
    public function test_answer_composer_returns_safe_error_when_ollama_fails_no_fallback(): void
    {
        config(['ai.llm_provider' => 'ollama']);
        config(['ai.ollama.fallback_enabled' => false]);
        config(['ai.ollama.chat_model' => 'gemma4:e2b']);
        config(['ai.ollama.base_url' => 'http://127.0.0.1:11434']);

        Http::fake([
            'http://127.0.0.1:11434/api/chat' => function () {
                throw new ConnectionException('Connection refused');
            },
        ]);

        $service = new AnswerComposerService;
        $result = $service->compose(
            'Câu hỏi test',
            'Câu hỏi test',
            ['intent' => 'academic_policy', 'source' => 'rag'],
            null,
            []
        );

        $this->assertStringContainsString('lỗi', $result['answer_text']);
        $this->assertFalse($result['fallback_used']);
        $this->assertNull($result['fallback_provider']);
    }
}
