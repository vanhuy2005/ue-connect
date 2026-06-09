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
}
