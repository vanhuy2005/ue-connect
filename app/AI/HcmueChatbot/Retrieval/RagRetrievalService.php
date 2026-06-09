<?php

namespace App\AI\HcmueChatbot\Retrieval;

use App\AI\HcmueChatbot\LLM\EmbeddingService;
use App\Models\DocumentChunk;
use Illuminate\Support\Facades\Log;

class RagRetrievalService
{
    public function __construct(
        protected EmbeddingService $embeddingService,
        protected QdrantVectorStore $vectorStore
    ) {}

    /**
     * Retrieve matching document chunks for a given search query.
     *
     * @param  string  $query  The search query.
     * @param  array<string, mixed>  $filters  Optional filters to apply (e.g., ['cohort' => '45']).
     * @return array<array{
     *   id: int,
     *   score: float,
     *   chunk_text: string,
     *   page_start: int,
     *   page_end: int,
     *   part: ?string,
     *   chapter: ?string,
     *   section: ?string,
     *   article: ?string,
     *   clause: ?string,
     *   document_name: string,
     *   document_type: string,
     *   cohort: ?string,
     *   effective_year: ?int,
     *   metadata: array
     * }>
     */
    public function retrieve(string $query, array $filters = []): array
    {
        try {
            // 1. Get embedding for the query
            $queryVector = $this->embeddingService->embed($query);

            if (empty($queryVector)) {
                Log::warning('RagRetrievalService: Query vector embedding is empty.');

                return [];
            }

            // 2. Read configurations
            $limit = config('ai.retrieval.top_k', 8);
            $minScore = config('ai.retrieval.min_score', 0.65);

            // 3. Search Qdrant
            $results = $this->vectorStore->search($queryVector, $limit, $minScore, $filters);

            if (empty($results)) {
                return [];
            }

            $formattedResults = [];

            // 4. Fetch the actual text from database using chunk IDs to avoid payload desync issues,
            // or use the payload text stored in Qdrant. Fetching from DB is safest and allows easy updates.
            $chunkIds = array_map(fn ($point) => (int) $point['id'], $results);

            $chunks = DocumentChunk::whereIn('id', $chunkIds)
                ->get()
                ->keyBy('id');

            foreach ($results as $point) {
                $id = (int) $point['id'];
                $score = (float) $point['score'];
                $payload = $point['payload'] ?? [];

                // Retrieve chunk from DB, or fallback to Qdrant payload if not in DB
                $dbChunk = $chunks->get($id);
                $chunkText = $dbChunk ? $dbChunk->chunk_text : ($payload['chunk_text'] ?? '');

                $formattedResults[] = [
                    'id' => $id,
                    'score' => $score,
                    'chunk_text' => $chunkText,
                    'page_start' => $dbChunk ? $dbChunk->page_start : ($payload['page_start'] ?? null),
                    'page_end' => $dbChunk ? $dbChunk->page_end : ($payload['page_end'] ?? null),
                    'part' => $dbChunk ? $dbChunk->part : ($payload['part'] ?? null),
                    'chapter' => $dbChunk ? $dbChunk->chapter : ($payload['chapter'] ?? null),
                    'section' => $dbChunk ? $dbChunk->section : ($payload['section'] ?? null),
                    'article' => $dbChunk ? $dbChunk->article : ($payload['article'] ?? null),
                    'clause' => $dbChunk ? $dbChunk->clause : ($payload['clause'] ?? null),
                    'document_name' => $payload['document_name'] ?? '',
                    'document_type' => $payload['document_type'] ?? '',
                    'cohort' => $payload['cohort'] ?? null,
                    'effective_year' => $payload['effective_year'] ?? null,
                    'metadata' => $payload,
                ];
            }

            return $formattedResults;
        } catch (\Exception $e) {
            Log::error('RagRetrievalService retrieval failed: '.$e->getMessage());

            return [];
        }
    }
}
