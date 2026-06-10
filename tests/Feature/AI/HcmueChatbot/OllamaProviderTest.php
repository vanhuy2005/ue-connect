<?php

namespace Tests\Feature\AI\HcmueChatbot;

use App\AI\HcmueChatbot\LLM\OllamaProvider;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class OllamaProviderTest extends TestCase
{
    /**
     * Provider calls the correct /api/chat endpoint and parses a valid response.
     */
    public function test_generate_calls_correct_endpoint_and_parses_response(): void
    {
        Http::fake([
            'http://127.0.0.1:11434/api/chat' => Http::response([
                'message' => ['role' => 'assistant', 'content' => 'Xin chào!'],
                'prompt_eval_count' => 15,
                'eval_count' => 5,
            ], 200),
        ]);

        $provider = new OllamaProvider('http://127.0.0.1:11434', 'gemma4:e2b');
        $result = $provider->generate('Xin chào');

        $this->assertSame('Xin chào!', $result['text']);
        $this->assertSame(15, $result['usage']['input_tokens']);
        $this->assertSame(5, $result['usage']['output_tokens']);
        $this->assertSame(20, $result['usage']['total_tokens']);

        Http::assertSent(function ($request) {
            return str_contains($request->url(), '/api/chat')
                && $request->data()['model'] === 'gemma4:e2b'
                && $request->data()['stream'] === false;
        });
    }

    /**
     * System instruction is included as a system role message when provided.
     */
    public function test_generate_includes_system_instruction_as_system_message(): void
    {
        Http::fake([
            'http://127.0.0.1:11434/api/chat' => Http::response([
                'message' => ['role' => 'assistant', 'content' => 'Tôi là HCMUE Assistant.'],
                'prompt_eval_count' => 20,
                'eval_count' => 8,
            ], 200),
        ]);

        $provider = new OllamaProvider('http://127.0.0.1:11434', 'gemma4:e2b');
        $provider->generate('Bạn là ai?', ['system_instruction' => 'Bạn là HCMUE Assistant.']);

        Http::assertSent(function ($request) {
            $messages = $request->data()['messages'];

            return $messages[0]['role'] === 'system'
                && $messages[1]['role'] === 'user';
        });
    }

    /**
     * ConnectionException is caught and re-thrown as RuntimeException with a clear message.
     */
    public function test_generate_throws_runtime_exception_on_connection_failure(): void
    {
        Http::fake([
            'http://127.0.0.1:11434/api/chat' => function () {
                throw new ConnectionException('Connection refused');
            },
        ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches('/Ollama is not available/');

        $provider = new OllamaProvider('http://127.0.0.1:11434', 'gemma4:e2b');
        $provider->generate('test');
    }

    /**
     * A 404 response (model not found) throws RuntimeException with a helpful pull message.
     */
    public function test_generate_throws_runtime_exception_when_model_not_found(): void
    {
        Http::fake([
            'http://127.0.0.1:11434/api/chat' => Http::response(
                ['error' => 'model not found'],
                404
            ),
        ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches('/model not found|ollama pull/i');

        $provider = new OllamaProvider('http://127.0.0.1:11434', 'gemma4:e2b');
        $provider->generate('test');
    }

    /**
     * isServerReachable() returns true when /api/tags responds 200.
     */
    public function test_is_server_reachable_returns_true_when_online(): void
    {
        Http::fake([
            'http://127.0.0.1:11434/api/tags' => Http::response(['models' => []], 200),
        ]);

        $provider = new OllamaProvider('http://127.0.0.1:11434', 'gemma4:e2b');

        $this->assertTrue($provider->isServerReachable());
    }

    /**
     * isServerReachable() returns false when the connection fails.
     */
    public function test_is_server_reachable_returns_false_when_offline(): void
    {
        Http::fake([
            'http://127.0.0.1:11434/api/tags' => function () {
                throw new ConnectionException('Connection refused');
            },
        ]);

        $provider = new OllamaProvider('http://127.0.0.1:11434', 'gemma4:e2b');

        $this->assertFalse($provider->isServerReachable());
    }

    /**
     * getInstalledModels() returns model names from /api/tags response.
     */
    public function test_get_installed_models_returns_model_names(): void
    {
        Http::fake([
            'http://127.0.0.1:11434/api/tags' => Http::response([
                'models' => [
                    ['name' => 'gemma4:e2b'],
                    ['name' => 'llama3:8b'],
                ],
            ], 200),
        ]);

        $provider = new OllamaProvider('http://127.0.0.1:11434', 'gemma4:e2b');
        $models = $provider->getInstalledModels();

        $this->assertContains('gemma4:e2b', $models);
        $this->assertContains('llama3:8b', $models);
    }
}
