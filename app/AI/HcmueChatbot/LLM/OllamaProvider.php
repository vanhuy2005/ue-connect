<?php

namespace App\AI\HcmueChatbot\LLM;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OllamaProvider implements LlmProviderInterface
{
    protected string $baseUrl;

    protected string $model;

    protected int $timeout;

    protected float $temperature;

    protected float $topP;

    protected int $numCtx;

    protected int $numPredict;

    public function __construct(
        ?string $baseUrl = null,
        ?string $model = null,
        ?int $timeout = null,
    ) {
        $this->baseUrl = rtrim($baseUrl ?? config('ai.ollama.base_url', 'http://127.0.0.1:11434'), '/');
        $this->model = $model ?? config('ai.ollama.chat_model', 'gemma4:e2b');
        $this->timeout = $timeout ?? config('ai.ollama.timeout', 120);
        $this->temperature = (float) config('ai.ollama.temperature', 0.2);
        $this->topP = (float) config('ai.ollama.top_p', 0.9);
        $this->numCtx = (int) config('ai.ollama.num_ctx', 4096);
        $this->numPredict = (int) config('ai.ollama.num_predict', 1024);
    }

    /**
     * Generate content via Ollama /api/chat endpoint.
     *
     * @param  array{temperature?: float, json_mode?: bool, system_instruction?: string}  $options
     * @return array{text: string, raw: array, usage: array{input_tokens: int, output_tokens: int, total_tokens: int}}
     */
    public function generate(string $prompt, array $options = []): array
    {
        $systemInstruction = $options['system_instruction'] ?? null;
        $temperature = $options['temperature'] ?? $this->temperature;

        $messages = [];

        if ($systemInstruction) {
            $messages[] = ['role' => 'system', 'content' => $systemInstruction];
        }

        $messages[] = ['role' => 'user', 'content' => $prompt];

        $payload = [
            'model' => $this->model,
            'messages' => $messages,
            'stream' => false,
            'options' => [
                'temperature' => $temperature,
                'top_p' => $this->topP,
                'num_ctx' => $this->numCtx,
                'num_predict' => $this->numPredict,
            ],
        ];

        try {
            $response = Http::timeout($this->timeout)
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post("{$this->baseUrl}/api/chat", $payload);

            if ($response->failed()) {
                $status = $response->status();
                $body = $response->body();

                if ($status === 404) {
                    throw new \RuntimeException(
                        "Ollama model not found: '{$this->model}'. ".
                        "Please run: ollama pull {$this->model}"
                    );
                }

                throw new \RuntimeException("Ollama API Error [{$status}]: {$body}");
            }

            $data = $response->json();
            $text = $data['message']['content'] ?? '';

            // Ollama returns eval_count (output tokens) and prompt_eval_count (input tokens)
            $inputTokens = $data['prompt_eval_count'] ?? (int) (mb_strlen($prompt) / 4);
            $outputTokens = $data['eval_count'] ?? (int) (mb_strlen($text) / 4);
            $totalTokens = $inputTokens + $outputTokens;

            return [
                'text' => $text,
                'raw' => $data,
                'usage' => [
                    'input_tokens' => $inputTokens,
                    'output_tokens' => $outputTokens,
                    'total_tokens' => $totalTokens,
                ],
            ];
        } catch (ConnectionException $e) {
            $message = 'Ollama is not available. Please check that Ollama is running at '.
                "{$this->baseUrl} and model '{$this->model}' is installed. ".
                "Error: {$e->getMessage()}";

            Log::error('OllamaProvider connection failed.', [
                'base_url' => $this->baseUrl,
                'model' => $this->model,
                'error' => $e->getMessage(),
            ]);

            throw new \RuntimeException($message, 0, $e);
        } catch (\RuntimeException $e) {
            Log::error('OllamaProvider failed: '.$e->getMessage(), [
                'base_url' => $this->baseUrl,
                'model' => $this->model,
            ]);

            throw $e;
        }
    }

    /**
     * Embed text — delegates to GeminiProvider since Ollama embedding is
     * intentionally kept separate from chat in Phase 1 (local LLM only).
     *
     * @return array<float>
     */
    public function embed(string $text): array
    {
        return (new GeminiProvider)->embed($text);
    }

    /**
     * Check if the Ollama server is reachable.
     */
    public function isServerReachable(): bool
    {
        try {
            $response = Http::timeout(5)->get("{$this->baseUrl}/api/tags");

            return $response->successful();
        } catch (\Exception) {
            return false;
        }
    }

    /**
     * Get the list of installed model names from Ollama.
     *
     * @return array<string>
     */
    public function getInstalledModels(): array
    {
        try {
            $response = Http::timeout(5)->get("{$this->baseUrl}/api/tags");

            if ($response->failed()) {
                return [];
            }

            $models = $response->json('models', []);

            return array_map(fn ($m) => $m['name'] ?? '', $models);
        } catch (\Exception) {
            return [];
        }
    }

    public function getModel(): string
    {
        return $this->model;
    }

    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }
}
