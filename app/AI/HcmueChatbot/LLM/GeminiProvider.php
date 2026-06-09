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
        $embeddingModel = str_contains($this->model, 'embedding') ? $this->model : 'text-embedding-004';
        $cleanModel = ltrim($embeddingModel, 'models/');

        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$cleanModel}:embedContent?key={$this->apiKey}";

        $body = [
            'model' => "models/{$cleanModel}",
            'content' => [
                'parts' => [
                    ['text' => $text],
                ],
            ],
            'outputDimensionality' => config('ai.embedding.dimensions', 768),
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

    /**
     * Embed multiple texts in a single batch request.
     *
     * @param  array<string>  $texts
     * @return array<array<float>>
     */
    public function batchEmbed(array $texts): array
    {
        $embeddingModel = str_contains($this->model, 'embedding') ? $this->model : 'text-embedding-004';
        $cleanModel = ltrim($embeddingModel, 'models/');

        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$cleanModel}:batchEmbedContents?key={$this->apiKey}";

        $requests = [];
        $dims = config('ai.embedding.dimensions', 768);
        foreach ($texts as $text) {
            $requests[] = [
                'model' => "models/{$cleanModel}",
                'content' => [
                    'parts' => [
                        ['text' => $text],
                    ],
                ],
                'outputDimensionality' => $dims,
            ];
        }

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])
                ->withOptions([
                    'verify' => false,
                ])
                ->post($url, ['requests' => $requests]);

            if ($response->failed()) {
                throw new \Exception('Gemini Batch Embedding API Error: Status '.$response->status().' - '.$response->body());
            }

            $data = $response->json();
            $results = [];
            if (isset($data['embeddings'])) {
                foreach ($data['embeddings'] as $emb) {
                    $results[] = $emb['values'] ?? [];
                }
            }

            return $results;
        } catch (\Exception $e) {
            Log::error('Gemini batch embedding failed: '.$e->getMessage());
            throw $e;
        }
    }
}
