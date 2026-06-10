<?php

namespace App\AI\HcmueChatbot\LLM;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * Embedding provider that delegates to a BGE-M3 service hosted on Hugging Face Space.
 *
 * Endpoint: POST {BGE_EMBEDDING_URL}/embed
 * Request:  { "text": "..." }
 * Response: { "dimension": 1024, "vector": [float, ...] }
 */
class BgeM3EmbeddingProvider implements LlmProviderInterface
{
    protected string $baseUrl;

    protected int $timeout;

    protected int $expectedDimension;

    public function __construct(
        ?string $baseUrl = null,
        int $timeout = 120,
        int $expectedDimension = 1024
    ) {
        $this->baseUrl = rtrim($baseUrl ?? config('ai.bge_m3.url', 'https://ntkhoi2005-hcmue-bge-m3-embedding.hf.space'), '/');
        $this->timeout = $timeout ?: (int) config('ai.bge_m3.timeout', 120);
        $this->expectedDimension = $expectedDimension ?: (int) config('ai.qdrant.vector_size', 1024);
    }

    /**
     * Not used for embedding-only provider; throws to make misuse obvious.
     *
     * @throws RuntimeException
     */
    public function generate(string $prompt, array $options = []): array
    {
        throw new RuntimeException('BgeM3EmbeddingProvider does not support text generation. Use an LLM provider instead.');
    }

    /**
     * Generate a 1024-dim BGE-M3 embedding via the HF Space API.
     *
     * @return array<float>
     *
     * @throws RuntimeException
     */
    public function embed(string $text): array
    {
        $url = "{$this->baseUrl}/embed";

        try {
            $response = Http::retry(3, function (int $attempt) {
                return [3000, 8000, 15000][$attempt - 1] ?? 15000;
            }, function (\Throwable $exception) {
                if ($exception instanceof RequestException) {
                    $status = $exception->response->status();

                    return $status >= 500;
                }

                return $exception instanceof ConnectionException;
            })
                ->timeout($this->timeout)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ])
                ->withOptions([
                    'verify' => false,
                ])
                ->post($url, ['text' => $text])
                ->throw();

            $data = $response->json();

            return $this->validateAndExtractVector($data, $text);
        } catch (RequestException $e) {
            $status = $e->response->status();
            $body = $e->response->body();
            Log::error('BgeM3EmbeddingProvider: HTTP error during embed.', [
                'status' => $status,
                'body' => mb_substr($body, 0, 500),
                'url' => $url,
            ]);
            throw new RuntimeException("BGE-M3 embedding service returned HTTP {$status}: {$body}");
        } catch (ConnectionException $e) {
            Log::error('BgeM3EmbeddingProvider: Connection failed.', ['url' => $url, 'message' => $e->getMessage()]);
            throw new RuntimeException("BGE-M3 embedding service is unreachable at {$url}: {$e->getMessage()}");
        } catch (\Exception $e) {
            if ($e instanceof RuntimeException) {
                throw $e;
            }
            Log::error('BgeM3EmbeddingProvider: Unexpected error.', ['message' => $e->getMessage()]);
            throw new RuntimeException("BGE-M3 embedding failed: {$e->getMessage()}");
        }
    }

    /**
     * Embed multiple texts sequentially (HF Space does not expose a batch endpoint).
     *
     * @param  array<string>  $texts
     * @return array<array<float>>
     */
    public function batchEmbed(array $texts): array
    {
        $results = [];
        foreach ($texts as $text) {
            $results[] = $this->embed($text);
        }

        return $results;
    }

    /**
     * Return the base URL (useful for diagnostics).
     */
    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    /**
     * Return the expected embedding dimension.
     */
    public function getExpectedDimension(): int
    {
        return $this->expectedDimension;
    }

    /**
     * Validate the API response and extract the vector array.
     *
     * @param  array<string, mixed>  $data
     * @return array<float>
     *
     * @throws RuntimeException
     */
    private function validateAndExtractVector(array $data, string $text): array
    {
        if (! isset($data['vector'])) {
            Log::error('BgeM3EmbeddingProvider: Response missing "vector" field.', ['response_keys' => array_keys($data)]);
            throw new RuntimeException('BGE-M3 embedding response is missing the "vector" field.');
        }

        if (! is_array($data['vector'])) {
            throw new RuntimeException('BGE-M3 embedding "vector" field is not an array.');
        }

        $vector = $data['vector'];
        $count = count($vector);

        if ($count !== $this->expectedDimension) {
            Log::error('BgeM3EmbeddingProvider: Dimension mismatch.', [
                'expected' => $this->expectedDimension,
                'actual' => $count,
                'text_preview' => mb_substr($text, 0, 100),
            ]);
            throw new RuntimeException("BGE-M3 embedding dimension mismatch: expected {$this->expectedDimension}, got {$count}.");
        }

        return array_map('floatval', $vector);
    }
}
