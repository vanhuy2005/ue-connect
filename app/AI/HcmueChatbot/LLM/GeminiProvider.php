<?php

namespace App\AI\HcmueChatbot\LLM;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiProvider implements LlmProviderInterface
{
    protected string $apiKey;

    protected string $model;

    public function __construct(?string $apiKey = null, string $model = 'gemini-2.0-flash')
    {
        $this->apiKey = $apiKey ?: config('services.gemini.key', env('GEMINI_API_KEY'));
        $this->model = $model;
    }

    /**
     * Generate content.
     */
    public function generate(string $prompt, array $options = []): array
    {
        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$this->model}:generateContent?key={$this->apiKey}";

        $systemInstruction = $options['system_instruction'] ?? null;
        $jsonMode = $options['json_mode'] ?? false;
        $temperature = $options['temperature'] ?? 0.2;

        $contents = [
            [
                'parts' => [
                    ['text' => $prompt],
                ],
            ],
        ];

        // Format body
        $body = [
            'contents' => $contents,
            'generationConfig' => [
                'temperature' => $temperature,
            ],
        ];

        if ($systemInstruction) {
            $body['systemInstruction'] = [
                'parts' => [
                    ['text' => $systemInstruction],
                ],
            ];
        }

        if ($jsonMode) {
            $body['generationConfig']['responseMimeType'] = 'application/json';
        }

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])
                ->withOptions([
                    'verify' => false, // Bypass SSL issues in local Laragon environment
                ])
                ->post($url, $body);

            if ($response->failed()) {
                throw new \Exception('Gemini API Error: Status '.$response->status().' - '.$response->body());
            }

            $data = $response->json();
            $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';

            // Estimate tokens (Fallback in case metadata is not returned)
            $inputTokens = $data['usageMetadata']['promptTokenCount'] ?? mb_strlen($prompt) / 4;
            $outputTokens = $data['usageMetadata']['candidatesTokenCount'] ?? mb_strlen($text) / 4;
            $totalTokens = $data['usageMetadata']['totalTokenCount'] ?? ($inputTokens + $outputTokens);

            return [
                'text' => $text,
                'raw' => $data,
                'usage' => [
                    'input_tokens' => (int) $inputTokens,
                    'output_tokens' => (int) $outputTokens,
                    'total_tokens' => (int) $totalTokens,
                ],
            ];
        } catch (\Exception $e) {
            Log::error('Gemini provider failed: '.$e->getMessage());
            throw $e;
        }
    }

    /**
     * Embed text.
     */
    public function embed(string $text): array
    {
        $url = "https://generativelanguage.googleapis.com/v1beta/models/text-embedding-004:embedContent?key={$this->apiKey}";

        $body = [
            'model' => 'models/text-embedding-004',
            'content' => [
                'parts' => [
                    ['text' => $text],
                ],
            ],
        ];

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])
                ->withOptions([
                    'verify' => false, // Bypass SSL issues in local Laragon environment
                ])
                ->post($url, $body);

            if ($response->failed()) {
                throw new \Exception('Gemini Embedding API Error: Status '.$response->status().' - '.$response->body());
            }

            $data = $response->json();

            return $data['embedding']['values'] ?? [];
        } catch (\Exception $e) {
            Log::error('Gemini embedding failed: '.$e->getMessage());
            throw $e;
        }
    }
}
