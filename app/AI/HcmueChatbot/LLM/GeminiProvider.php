<?php

namespace App\AI\HcmueChatbot\LLM;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;

class GeminiProvider implements LlmProviderInterface
{
    protected string $apiKey;

    protected string $model;

    protected GeminiKeyManager $keyManager;

    public function __construct(?string $apiKey = null, string $model = 'gemini-2.0-flash')
    {
        $this->apiKey = $apiKey ?: config('services.gemini.key', env('GEMINI_API_KEY'));
        $this->model = $model;

        // If an API key was explicitly passed, override the pool keys
        $overrideKeys = ! empty($apiKey) ? [$apiKey] : null;
        $this->keyManager = new GeminiKeyManager($overrideKeys);
    }

    /**
     * Get the underlying KeyManager instance (useful for testing).
     */
    public function getKeyManager(): GeminiKeyManager
    {
        return $this->keyManager;
    }

    /**
     * Generate content.
     */
    public function generate(string $prompt, array $options = []): array
    {
        return $this->keyManager->run(function (string $apiKey) use ($prompt, $options) {
            $url = "https://generativelanguage.googleapis.com/v1beta/models/{$this->model}:generateContent?key={$apiKey}";

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

            $response = Http::retry(3, function (int $attempt) {
                return [2000, 5000, 10000][$attempt - 1] ?? 10000;
            }, function (\Throwable $exception) {
                if ($exception instanceof RequestException) {
                    $status = $exception->response->status();

                    return $status === 503;
                }

                return $exception instanceof ConnectionException;
            })
                ->withHeaders([
                    'Content-Type' => 'application/json',
                ])
                ->withOptions([
                    'verify' => false, // Bypass SSL issues in local Laragon environment
                ])
                ->post($url, $body)
                ->throw();

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
        });
    }

    /**
     * Embed text.
     */
    public function embed(string $text): array
    {
        return $this->keyManager->run(function (string $apiKey) use ($text) {
            $embeddingModel = str_contains($this->model, 'embedding') ? $this->model : 'text-embedding-004';
            $cleanModel = ltrim($embeddingModel, 'models/');

            $url = "https://generativelanguage.googleapis.com/v1beta/models/{$cleanModel}:embedContent?key={$apiKey}";

            $body = [
                'model' => "models/{$cleanModel}",
                'content' => [
                    'parts' => [
                        ['text' => $text],
                    ],
                ],
                'outputDimensionality' => config('ai.embedding.dimensions', 768),
            ];

            $response = Http::retry(3, function (int $attempt) {
                return [2000, 5000, 10000][$attempt - 1] ?? 10000;
            }, function (\Throwable $exception) {
                if ($exception instanceof RequestException) {
                    $status = $exception->response->status();

                    return $status === 503;
                }

                return $exception instanceof ConnectionException;
            })
                ->withHeaders([
                    'Content-Type' => 'application/json',
                ])
                ->withOptions([
                    'verify' => false, // Bypass SSL issues in local Laragon environment
                ])
                ->post($url, $body)
                ->throw();

            $data = $response->json();

            return $data['embedding']['values'] ?? [];
        });
    }

    /**
     * Embed multiple texts in a single batch request.
     *
     * @param  array<string>  $texts
     * @return array<array<float>>
     */
    public function batchEmbed(array $texts): array
    {
        return $this->keyManager->run(function (string $apiKey) use ($texts) {
            $embeddingModel = str_contains($this->model, 'embedding') ? $this->model : 'text-embedding-004';
            $cleanModel = ltrim($embeddingModel, 'models/');

            $url = "https://generativelanguage.googleapis.com/v1beta/models/{$cleanModel}:batchEmbedContents?key={$apiKey}";

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

            $response = Http::retry(3, function (int $attempt) {
                return [3000, 7000, 15000][$attempt - 1] ?? 15000;
            }, function (\Throwable $exception) {
                if ($exception instanceof RequestException) {
                    $status = $exception->response->status();

                    return $status === 503;
                }

                return $exception instanceof ConnectionException;
            })
                ->withHeaders([
                    'Content-Type' => 'application/json',
                ])
                ->withOptions([
                    'verify' => false,
                ])
                ->post($url, ['requests' => $requests])
                ->throw();

            $data = $response->json();
            $results = [];
            if (isset($data['embeddings'])) {
                foreach ($data['embeddings'] as $emb) {
                    $results[] = $emb['values'] ?? [];
                }
            }

            return $results;
        });
    }
}
