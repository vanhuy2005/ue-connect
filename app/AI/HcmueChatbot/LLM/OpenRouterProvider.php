<?php

namespace App\AI\HcmueChatbot\LLM;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OpenRouterProvider implements LlmProviderInterface
{
    protected string $apiKey;

    protected string $model;

    public function __construct(?string $apiKey = null, string $model = 'google/gemini-2.0-flash')
    {
        $this->apiKey = $apiKey ?: env('OPENROUTER_API_KEY', '');
        $this->model = $model;
    }

    /**
     * Generate content.
     */
    public function generate(string $prompt, array $options = []): array
    {
        if (empty($this->apiKey)) {
            Log::warning('OpenRouter API key is empty. Returning mock response.');

            return [
                'text' => '{"error": "OpenRouter Provider not configured"}',
                'raw' => [],
                'usage' => ['input_tokens' => 0, 'output_tokens' => 0, 'total_tokens' => 0],
            ];
        }

        $url = 'https://openrouter.ai/api/v1/chat/completions';
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
                'HTTP-Referer' => config('app.url', 'http://localhost'),
                'X-Title' => 'HCMUE Chatbot',
            ])
                ->post($url, $body);

            if ($response->failed()) {
                throw new \Exception('OpenRouter API Error: '.$response->body());
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
            Log::error('OpenRouter generate failed: '.$e->getMessage());
            throw $e;
        }
    }

    /**
     * Embed text.
     */
    public function embed(string $text): array
    {
        // OpenRouter doesn't specialize in embeddings API, typically we fallback to Gemini embedding
        $gemini = new GeminiProvider;

        return $gemini->embed($text);
    }
}
