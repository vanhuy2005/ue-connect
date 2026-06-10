<?php

namespace App\AI\HcmueChatbot\LLM;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OpenAiProvider implements LlmProviderInterface
{
    protected string $apiKey;

    protected string $model;

    public function __construct(?string $apiKey = null, string $model = 'gpt-4o-mini')
    {
        $this->apiKey = $apiKey ?: env('OPENAI_API_KEY', '');
        $this->model = $model;
    }

    /**
     * Generate content.
     */
    public function generate(string $prompt, array $options = []): array
    {
        if (empty($this->apiKey)) {
            Log::warning('OpenAI API key is empty. Returning mock response.');

            return [
                'text' => '{"error": "OpenAI Provider not configured"}',
                'raw' => [],
                'usage' => ['input_tokens' => 0, 'output_tokens' => 0, 'total_tokens' => 0],
            ];
        }

        $url = 'https://api.openai.com/v1/chat/completions';
        $systemInstruction = $options['system_instruction'] ?? null;
        $jsonMode = $options['json_mode'] ?? false;
        $temperature = $options['temperature'] ?? 0.2;

        $messages = [];
        if ($systemInstruction) {
            $messages[] = ['role' => 'system', 'content' => $systemInstruction];
        }
        $messages[] = ['role' => 'user', 'content' => $prompt];

        $body = [
            'model' => $this->model,
            'messages' => $messages,
            'temperature' => $temperature,
        ];

        if ($jsonMode) {
            $body['response_format'] = ['type' => 'json_object'];
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$this->apiKey}",
                'Content-Type' => 'application/json',
            ])
                ->post($url, $body);

            if ($response->failed()) {
                throw new \Exception('OpenAI API Error: '.$response->body());
            }

            $data = $response->json();
            $text = $data['choices'][0]['message']['content'] ?? '';
            $usage = $data['usage'] ?? [];

            return [
                'text' => $text,
                'raw' => $data,
                'usage' => [
                    'input_tokens' => $usage['prompt_tokens'] ?? 0,
                    'output_tokens' => $usage['completion_tokens'] ?? 0,
                    'total_tokens' => $usage['total_tokens'] ?? 0,
                ],
            ];
        } catch (\Exception $e) {
            Log::error('OpenAI generate failed: '.$e->getMessage());
            throw $e;
        }
    }

    /**
     * Embed text.
     */
    public function embed(string $text): array
    {
        if (empty($this->apiKey)) {
            return array_fill(0, 1536, 0.0); // Mock embedding vector of size 1536
        }

        $url = 'https://api.openai.com/v1/embeddings';

        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$this->apiKey}",
                'Content-Type' => 'application/json',
            ])
                ->post($url, [
                    'model' => 'text-embedding-3-small',
                    'input' => $text,
                ]);

            if ($response->failed()) {
                throw new \Exception('OpenAI Embed Error: '.$response->body());
            }

            $data = $response->json();

            return $data['data'][0]['embedding'] ?? [];
        } catch (\Exception $e) {
            Log::error('OpenAI embed failed: '.$e->getMessage());
            throw $e;
        }
    }
}
