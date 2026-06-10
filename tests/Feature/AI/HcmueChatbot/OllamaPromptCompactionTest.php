<?php

namespace Tests\Feature\AI\HcmueChatbot;

use App\AI\HcmueChatbot\Chat\AnswerComposerService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class OllamaPromptCompactionTest extends TestCase
{
    /**
     * When provider is Ollama, RAG chunks are limited to OLLAMA_RAG_TOP_K.
     */
    public function test_rag_chunks_are_limited_when_provider_is_ollama(): void
    {
        config(['ai.llm_provider' => 'ollama']);
        config(['ai.ollama.rag_top_k' => 3]);
        config(['ai.ollama.max_context_chars' => 50000]);
        config(['ai.ollama.fallback_enabled' => false]);
        config(['ai.ollama.base_url' => 'http://127.0.0.1:11434']);
        config(['ai.ollama.chat_model' => 'gemma4:e2b']);

        $capturedPayload = null;

        Http::fake([
            'http://127.0.0.1:11434/api/chat' => function ($request) use (&$capturedPayload) {
                $capturedPayload = $request->data();

                return Http::response([
                    'message' => ['role' => 'assistant', 'content' => 'Câu trả lời test.'],
                    'prompt_eval_count' => 100,
                    'eval_count' => 30,
                ], 200);
            },
        ]);

        // 8 chunks passed in — only 3 should reach the LLM
        $chunks = array_fill(0, 8, [
            'id' => 1,
            'score' => 0.85,
            'chunk_text' => 'Nội dung chunk test.',
            'document_name' => 'Tài liệu A',
            'document_type' => 'handbook',
            'article' => 'Điều 1',
            'page_start' => 1,
        ]);

        $service = new AnswerComposerService;
        $service->compose('test', 'test', ['intent' => 'rag', 'source' => 'rag'], null, $chunks);

        $this->assertNotNull($capturedPayload);

        // The user message should contain at most 3 "Đoạn" markers
        $userContent = collect($capturedPayload['messages'])->firstWhere('role', 'user')['content'] ?? '';
        $chunkCount = substr_count($userContent, '--- Đoạn ');

        $this->assertLessThanOrEqual(3, $chunkCount, "Expected at most 3 RAG chunks in prompt, found {$chunkCount}");
    }

    /**
     * When provider is Ollama, prompt is truncated to OLLAMA_MAX_CONTEXT_CHARS.
     */
    public function test_prompt_is_truncated_when_exceeds_ollama_max_context_chars(): void
    {
        config(['ai.llm_provider' => 'ollama']);
        config(['ai.ollama.rag_top_k' => 10]);
        config(['ai.ollama.max_context_chars' => 200]); // Very small limit to trigger truncation
        config(['ai.ollama.fallback_enabled' => false]);
        config(['ai.ollama.base_url' => 'http://127.0.0.1:11434']);
        config(['ai.ollama.chat_model' => 'gemma4:e2b']);

        $capturedPayload = null;

        Http::fake([
            'http://127.0.0.1:11434/api/chat' => function ($request) use (&$capturedPayload) {
                $capturedPayload = $request->data();

                return Http::response([
                    'message' => ['role' => 'assistant', 'content' => 'OK'],
                    'prompt_eval_count' => 10,
                    'eval_count' => 2,
                ], 200);
            },
        ]);

        // Long chunk text to generate a big prompt
        $longChunk = [
            'id' => 1,
            'score' => 0.9,
            'chunk_text' => str_repeat('Đây là nội dung rất dài. ', 50),
            'document_name' => 'Tài liệu B',
            'document_type' => 'handbook',
            'article' => 'Điều 2',
            'page_start' => 5,
        ];

        $service = new AnswerComposerService;
        $service->compose('test', 'test', ['intent' => 'rag', 'source' => 'rag'], null, [$longChunk]);

        $this->assertNotNull($capturedPayload);

        $userContent = collect($capturedPayload['messages'])->firstWhere('role', 'user')['content'] ?? '';
        $this->assertLessThanOrEqual(250, mb_strlen($userContent), 'Prompt should be truncated');
    }
}
