<?php

namespace App\AI\HcmueChatbot\Retrieval;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class QdrantVectorStore
{
    protected string $url;

    protected string $apiKey;

    protected string $collection;

    public function __construct()
    {
        $this->url = rtrim(config('ai.qdrant.url', 'http://localhost:6333'), '/');
        $this->apiKey = config('ai.qdrant.api_key', '');
        $this->collection = config('ai.qdrant.collection', 'hcmue_academic_chunks');
    }

    /**
     * Get the active collection name.
     */
    public function getCollectionName(): string
    {
        return $this->collection;
    }

    /**
     * Get headers for Qdrant API request.
     */
    protected function getHeaders(): array
    {
        $headers = [
            'Content-Type' => 'application/json',
        ];

        if (! empty($this->apiKey)) {
            $headers['api-key'] = $this->apiKey;
        }

        return $headers;
    }

    /**
     * Check if the collection exists in Qdrant.
     */
    public function collectionExists(?string $collectionName = null): bool
    {
        $collection = $collectionName ?: $this->collection;
        $response = Http::withHeaders($this->getHeaders())
            ->withoutVerifying()
            ->get("{$this->url}/collections/{$collection}");

        return $response->successful();
    }

    /**
     * Create a collection in Qdrant.
     */
    public function createCollection(int $vectorSize, string $distance = 'Cosine', ?string $collectionName = null): bool
    {
        $collection = $collectionName ?: $this->collection;

        $body = [
            'vectors' => [
                'size' => $vectorSize,
                'distance' => $distance,
            ],
        ];

        $response = Http::withHeaders($this->getHeaders())
            ->withoutVerifying()
            ->put("{$this->url}/collections/{$collection}", $body);

        if ($response->failed()) {
            Log::error("Failed to create Qdrant collection '{$collection}': ".$response->body());

            return false;
        }

        return true;
    }

    /**
     * Delete a collection in Qdrant.
     */
    public function deleteCollection(?string $collectionName = null): bool
    {
        $collection = $collectionName ?: $this->collection;

        $response = Http::withHeaders($this->getHeaders())
            ->withoutVerifying()
            ->delete("{$this->url}/collections/{$collection}");

        if ($response->failed() && $response->status() !== 444) { // 444 or 404 sometimes means not found depending on config
            Log::error("Failed to delete Qdrant collection '{$collection}': ".$response->body());

            return false;
        }

        return true;
    }

    /**
     * Upsert points to the Qdrant collection.
     *
     * @param  array<array{id: int|string, vector: array<float>, payload: array}>  $points
     */
    public function upsert(array $points, ?string $collectionName = null): bool
    {
        $collection = $collectionName ?: $this->collection;

        $body = [
            'points' => $points,
        ];

        $response = Http::withHeaders($this->getHeaders())
            ->withoutVerifying()
            ->put("{$this->url}/collections/{$collection}/points?wait=true", $body);

        if ($response->failed()) {
            Log::error("Failed to upsert points to Qdrant collection '{$collection}': ".$response->body());

            return false;
        }

        return true;
    }

    /**
     * Search the Qdrant collection.
     *
     * @param  array<float>  $vector
     * @param  array<string, mixed>  $filter  Optional payload filters (e.g. ['key' => 'value'])
     */
    public function search(array $vector, int $limit = 8, float $minScore = 0.0, array $filter = [], ?string $collectionName = null): array
    {
        $collection = $collectionName ?: $this->collection;

        $body = [
            'vector' => $vector,
            'limit' => $limit,
            'with_payload' => true,
            'with_vector' => false,
            'score_threshold' => $minScore > 0 ? $minScore : null,
        ];

        // Process filters into Qdrant filter structure if provided
        if (! empty($filter)) {
            $must = [];
            foreach ($filter as $key => $value) {
                if ($value !== null && $value !== '') {
                    $must[] = [
                        'key' => $key,
                        'match' => [
                            'value' => $value,
                        ],
                    ];
                }
            }
            if (! empty($must)) {
                $body['filter'] = [
                    'must' => $must,
                ];
            }
        }

        $response = Http::withHeaders($this->getHeaders())
            ->withoutVerifying()
            ->post("{$this->url}/collections/{$collection}/points/search", $body);

        if ($response->failed()) {
            Log::error("Failed to search Qdrant collection '{$collection}': ".$response->body());

            return [];
        }

        return $response->json('result') ?? [];
    }

    /**
     * Delete points from Qdrant.
     *
     * @param  array<int|string>  $ids
     */
    public function delete(array $ids, ?string $collectionName = null): bool
    {
        $collection = $collectionName ?: $this->collection;

        $body = [
            'points' => $ids,
        ];

        $response = Http::withHeaders($this->getHeaders())
            ->withoutVerifying()
            ->post("{$this->url}/collections/{$collection}/points/delete", $body);

        if ($response->failed()) {
            Log::error("Failed to delete points from Qdrant collection '{$collection}': ".$response->body());

            return false;
        }

        return true;
    }

    /**
     * Create payload index in Qdrant collection.
     */
    public function createPayloadIndex(string $fieldName, string $schemaType, ?string $collectionName = null): bool
    {
        $collection = $collectionName ?: $this->collection;
        $body = [
            'field_name' => $fieldName,
            'field_schema' => $schemaType,
        ];

        $response = Http::withHeaders($this->getHeaders())
            ->withoutVerifying()
            ->put("{$this->url}/collections/{$collection}/index?wait=true", $body);

        if ($response->failed()) {
            Log::error("Failed to create payload index for '{$fieldName}' in collection '{$collection}': ".$response->body());

            return false;
        }

        return true;
    }

    /**
     * Scroll through ALL points and collect unique values of a given payload field.
     *
     * Uses the Qdrant scroll API with `with_vector=false` and a selective payload
     * projection to minimise data transfer. Pages through in batches.
     *
     * @param  array<string, mixed>  $filter  Optional Qdrant must-match filter applied during scroll.
     * @return array<string>
     */
    public function scrollUniquePayloadValues(
        string $field,
        array $filter = [],
        int $batchSize = 250,
        int $maxBatches = 200,
        ?string $collectionName = null
    ): array {
        $collection = $collectionName ?: $this->collection;
        $unique = [];
        $offset = null;
        $batchCount = 0;

        do {
            $body = [
                'limit' => $batchSize,
                'with_payload' => ['include' => [$field]],
                'with_vector' => false,
            ];

            if ($offset) {
                $body['offset'] = $offset;
            }

            if (! empty($filter)) {
                $must = [];
                foreach ($filter as $k => $v) {
                    if ($v !== null && $v !== '') {
                        $must[] = ['key' => $k, 'match' => ['value' => $v]];
                    }
                }
                if (! empty($must)) {
                    $body['filter'] = ['must' => $must];
                }
            }

            $response = Http::withHeaders($this->getHeaders())
                ->withoutVerifying()
                ->timeout(30)
                ->post("{$this->url}/collections/{$collection}/points/scroll", $body);

            if ($response->failed()) {
                Log::warning("QdrantVectorStore::scrollUniquePayloadValues: scroll failed on batch {$batchCount}.", [
                    'status' => $response->status(),
                ]);
                break;
            }

            $result = $response->json('result') ?? [];
            $points = $result['points'] ?? [];

            foreach ($points as $point) {
                $value = $point['payload'][$field] ?? null;
                if ($value !== null && $value !== '' && ! in_array($value, $unique, true)) {
                    $unique[] = $value;
                }
            }

            $offset = $result['next_page_offset'] ?? null;
            $batchCount++;
        } while ($offset && $batchCount < $maxBatches);

        return $unique;
    }

    /**
     * Scroll filtered points and return those whose `text` payload field contains a given substring.
     *
     * Used as a lexical fallback to find chunks that contain exact phrases (e.g. "Tổng số tín chỉ").
     *
     * @param  string  $needle  The substring to search for inside payload['text'].
     * @param  array<string, mixed>  $filter  Qdrant must-match filters to narrow the scroll.
     * @param  int  $batchSize  Points per scroll page.
     * @param  int  $maxBatches  Safety cap on scroll pages.
     * @return array<array{id: int, payload: array}> Matching points.
     */
    public function scrollPayloadText(
        string $needle,
        array $filter = [],
        int $batchSize = 100,
        int $maxBatches = 20,
        ?string $collectionName = null
    ): array {
        $collection = $collectionName ?: $this->collection;
        $matches = [];
        $offset = null;
        $batchCount = 0;

        do {
            $body = [
                'limit' => $batchSize,
                'with_payload' => true,
                'with_vector' => false,
            ];

            if ($offset) {
                $body['offset'] = $offset;
            }

            if (! empty($filter)) {
                $must = [];
                foreach ($filter as $k => $v) {
                    if ($v !== null && $v !== '') {
                        $must[] = ['key' => $k, 'match' => ['value' => $v]];
                    }
                }
                if (! empty($must)) {
                    $body['filter'] = ['must' => $must];
                }
            }

            $response = Http::withHeaders($this->getHeaders())
                ->withoutVerifying()
                ->timeout(30)
                ->post("{$this->url}/collections/{$collection}/points/scroll", $body);

            if ($response->failed()) {
                Log::warning('QdrantVectorStore::scrollPayloadText: scroll failed.', [
                    'batch' => $batchCount,
                    'status' => $response->status(),
                ]);
                break;
            }

            $result = $response->json('result') ?? [];
            $points = $result['points'] ?? [];

            foreach ($points as $point) {
                $text = $point['payload']['text'] ?? $point['payload']['chunk_text'] ?? '';
                if (str_contains($text, $needle)) {
                    $matches[] = $point;
                }
            }

            $offset = $result['next_page_offset'] ?? null;
            $batchCount++;
        } while ($offset && $batchCount < $maxBatches);

        return $matches;
    }
}
