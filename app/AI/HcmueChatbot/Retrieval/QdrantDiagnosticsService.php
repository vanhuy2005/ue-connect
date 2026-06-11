<?php

namespace App\AI\HcmueChatbot\Retrieval;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class QdrantDiagnosticsService
{
    protected string $url;

    protected string $apiKey;

    protected string $collection;

    public function __construct(
        protected QdrantVectorStore $vectorStore
    ) {
        $this->url = rtrim(config('ai.qdrant.url', 'http://localhost:6333'), '/');
        $this->apiKey = config('ai.qdrant.api_key', '');
        $this->collection = config('ai.qdrant.collection', 'hcmue_academic_chunks');
    }

    /**
     * Get request headers.
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
     * Run diagnostics and return report.
     *
     * @return array{
     *   reachable: bool,
     *   version: ?string,
     *   collection_exists: bool,
     *   points_count: int,
     *   vector_size: ?int,
     *   distance: ?string,
     *   status: ?string,
     *   samples: array,
     *   search_test: array,
     *   error: ?string
     * }
     */
    public function diagnose(): array
    {
        $report = [
            'reachable' => false,
            'version' => null,
            'collection_exists' => false,
            'points_count' => 0,
            'vector_size' => null,
            'distance' => null,
            'status' => null,
            'payload_indexes' => [],
            'samples' => [],
            'search_test' => [
                'success' => false,
                'latency_ms' => 0,
                'results_count' => 0,
            ],
            'error' => null,
        ];

        try {
            // 1. Check Qdrant Reachability & Version
            $pingResponse = Http::withHeaders($this->getHeaders())
                ->withoutVerifying()
                ->timeout(5)
                ->get($this->url);

            if ($pingResponse->failed()) {
                throw new \Exception("Could not connect to Qdrant at {$this->url}. Status: ".$pingResponse->status().' Body: '.$pingResponse->body());
            }

            $report['reachable'] = true;
            $report['version'] = $pingResponse->json('title').' '.$pingResponse->json('version');

            // 2. Check Collection Existence & Configuration
            $collectionResponse = Http::withHeaders($this->getHeaders())
                ->withoutVerifying()
                ->timeout(5)
                ->get("{$this->url}/collections/{$this->collection}");

            if ($collectionResponse->failed()) {
                $report['error'] = "Collection '{$this->collection}' does not exist or failed to load. Body: ".$collectionResponse->body();

                return $report;
            }

            $report['collection_exists'] = true;
            $colData = $collectionResponse->json('result') ?? [];
            $report['status'] = $colData['status'] ?? 'unknown';
            $report['points_count'] = $colData['points_count'] ?? $colData['vectors_count'] ?? 0;
            $report['payload_indexes'] = $colData['payload_schema'] ?? [];

            // Extract vector configuration (dimensions & distance)
            $vectorConfig = $colData['config']['params']['vectors'] ?? [];
            $report['vector_size'] = $vectorConfig['size'] ?? null;
            $report['distance'] = $vectorConfig['distance'] ?? null;

            // 3. Retrieve point samples using Scroll API
            $scrollResponse = Http::withHeaders($this->getHeaders())
                ->withoutVerifying()
                ->timeout(5)
                ->post("{$this->url}/collections/{$this->collection}/points/scroll", [
                    'limit' => 3,
                    'with_payload' => true,
                    'with_vector' => false,
                ]);

            if ($scrollResponse->successful()) {
                $scrollResult = $scrollResponse->json('result') ?? [];
                $report['samples'] = $scrollResult['points'] ?? [];
            }

            // 4. Perform search test with a dummy vector of zero weights
            $vectorSize = $report['vector_size'] ?: config('ai.embedding.dimensions', 768);
            $dummyVector = array_fill(0, $vectorSize, 0.0);

            $startTime = microtime(true);
            $searchResults = $this->vectorStore->search($dummyVector, 2);
            $endTime = microtime(true);

            $report['search_test'] = [
                'success' => true,
                'latency_ms' => round(($endTime - $startTime) * 1000, 2),
                'results_count' => count($searchResults),
            ];

        } catch (\Exception $e) {
            Log::error('QdrantDiagnosticsService: Diagnosis failed - '.$e->getMessage());
            $report['error'] = $e->getMessage();
        }

        return $report;
    }
}
