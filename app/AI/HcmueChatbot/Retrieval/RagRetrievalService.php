<?php

namespace App\AI\HcmueChatbot\Retrieval;

use App\AI\HcmueChatbot\LLM\EmbeddingService;
use App\Models\DocumentChunk;
use Illuminate\Support\Facades\Log;

class RagRetrievalService
{
    public function __construct(
        protected EmbeddingService $embeddingService,
        protected QdrantVectorStore $vectorStore,
        protected AcademicQueryAnalyzer $queryAnalyzer
    ) {}

    /**
     * Retrieve matching document chunks for a given search query.
     *
     * @param  string  $query  The search query.
     * @param  array<string, mixed>  $filters  Optional filters to apply.
     * @return array<array{
     *   id: int,
     *   score: float,
     *   rerank_score: float,
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
            // 1. Analyze the query to extract academic entities & intent
            $analysis = $this->queryAnalyzer->analyze($query);

            // 2. Generate 3-5 query variations for multi-query search
            $variations = $this->generateQueryVariations($query);

            // 3. Resolve filters based on query analysis
            $resolvedFilters = $filters;
            if ($analysis['cohort'] && empty($resolvedFilters['cohort'])) {
                $resolvedFilters['cohort'] = $analysis['cohort'];
            }
            if ($analysis['document_type'] !== 'unknown' && empty($resolvedFilters['document_type'])) {
                $resolvedFilters['document_type'] = $analysis['document_type'];
            }

            // 4. Batch embed all variations
            $vectors = $this->embeddingService->batchEmbed($variations);

            if (empty($vectors)) {
                Log::warning('RagRetrievalService: Query vector embedding batch is empty.');

                return [];
            }

            $limit = config('ai.retrieval.top_k', 8);
            $minScore = config('ai.retrieval.min_score', 0.65);

            // 5. Search Qdrant for each variation and merge results
            $mergedResults = [];
            foreach ($variations as $idx => $variation) {
                $vector = $vectors[$idx] ?? [];
                if (empty($vector)) {
                    continue;
                }
                $points = $this->vectorStore->search($vector, $limit, $minScore, $resolvedFilters);
                foreach ($points as $point) {
                    $id = (int) $point['id'];
                    $score = (float) $point['score'];
                    if (! isset($mergedResults[$id]) || $mergedResults[$id]['score'] < $score) {
                        $mergedResults[$id] = $point;
                    }
                }
            }

            if (empty($mergedResults)) {
                return [];
            }

            // 6. Apply Rule-Based Reranking
            $reranked = [];
            foreach ($mergedResults as $point) {
                $payload = $point['payload'] ?? [];
                $originalScore = (float) $point['score'];
                $rerankScore = $originalScore;

                // Rule A: +0.20 if document_type matches intent
                $chunkDocType = $payload['document_type'] ?? 'unknown';
                $queryDocType = $analysis['document_type'] ?? 'unknown';
                if ($queryDocType !== 'unknown' && $chunkDocType === $queryDocType) {
                    $rerankScore += 0.20;
                }

                // Rule B: +0.15 if cohort matches
                $chunkCohort = $payload['cohort'] ?? null;
                $queryCohort = $analysis['cohort'] ?? null;
                if ($queryCohort !== null) {
                    if ($chunkCohort === $queryCohort) {
                        $rerankScore += 0.15;
                    } elseif ($chunkCohort !== null) {
                        // Rule G: -0.30 if cohort mismatched
                        $rerankScore -= 0.30;
                    }
                }

                // Rule C: +0.15 if major matches
                $chunkMajor = $payload['major'] ?? null;
                $queryMajor = $analysis['major'] ?? null;
                if ($queryMajor !== null) {
                    if ($chunkMajor && str_contains(mb_strtolower($chunkMajor, 'UTF-8'), mb_strtolower($queryMajor, 'UTF-8'))) {
                        $rerankScore += 0.15;
                    }
                }

                // Rule D: +0.10 if faculty matches
                $chunkFaculty = $payload['faculty'] ?? null;
                $queryFaculty = $analysis['faculty'] ?? null;
                if ($queryFaculty !== null) {
                    if ($chunkFaculty && str_contains(mb_strtolower($chunkFaculty, 'UTF-8'), mb_strtolower($queryFaculty, 'UTF-8'))) {
                        $rerankScore += 0.10;
                    }
                }

                // Rule E: +0.10 if file name contains topic keyword or major
                $docName = mb_strtolower($payload['document_name'] ?? '', 'UTF-8');
                $containsKeyword = false;
                foreach ($analysis['topics'] as $topic) {
                    if (str_contains($docName, mb_strtolower($topic, 'UTF-8'))) {
                        $containsKeyword = true;
                        break;
                    }
                }
                if (! $containsKeyword && $queryMajor) {
                    if (str_contains($docName, mb_strtolower($queryMajor, 'UTF-8'))) {
                        $containsKeyword = true;
                    }
                }
                if ($containsKeyword) {
                    $rerankScore += 0.10;
                }

                // Rule F: -0.20 if document_type is unknown
                if ($chunkDocType === 'unknown') {
                    $rerankScore -= 0.20;
                }

                $point['original_score'] = $originalScore;
                $point['rerank_score'] = $rerankScore;
                $point['score'] = $rerankScore; // active score for sorting

                $reranked[] = $point;
            }

            // Sort by reranked score descending
            usort($reranked, fn ($a, $b) => $b['score'] <=> $a['score']);
            $topPoints = array_slice($reranked, 0, $limit);

            // 7. Fetch DB records to prevent desync
            $chunkIds = array_map(fn ($point) => (int) $point['id'], $topPoints);
            $chunks = DocumentChunk::whereIn('id', $chunkIds)
                ->get()
                ->keyBy('id');

            $formattedResults = [];
            foreach ($topPoints as $point) {
                $id = (int) $point['id'];
                $payload = $point['payload'] ?? [];
                $dbChunk = $chunks->get($id);
                $chunkText = $dbChunk ? $dbChunk->chunk_text : ($payload['chunk_text'] ?? '');

                $formattedResults[] = [
                    'id' => $id,
                    'score' => (float) ($point['original_score'] ?? $point['score']),
                    'rerank_score' => (float) ($point['rerank_score'] ?? $point['score']),
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

    /**
     * Generate 3-5 query variations for RAG expansion.
     *
     * @return array<string>
     */
    public function generateQueryVariations(string $query): array
    {
        $variations = [$query];

        // 1. Normalized lower-cased query
        $normalized = mb_strtolower(trim(preg_replace('/\s+/', ' ', $query)), 'UTF-8');
        if ($normalized !== mb_strtolower($query, 'UTF-8') && ! in_array($normalized, $variations)) {
            $variations[] = $normalized;
        }

        // 2. Expand/replace common Vietnamese academic shortcuts & synonyms
        $synonymMapping = [
            'cntt' => 'công nghệ thông tin',
            'ctđt' => 'chương trình đào tạo',
            'ctdt' => 'chương trình đào tạo',
            'cdr' => 'chuẩn đầu ra',
            'tc' => 'tín chỉ',
            'hk' => 'học kỳ',
            'sv' => 'sinh viên',
        ];

        $replaced = $normalized;
        $hasSynonym = false;
        foreach ($synonymMapping as $abbrev => $full) {
            $pattern = '/\b'.preg_quote($abbrev, '/').'\b/ui';
            if (preg_match($pattern, $replaced)) {
                $replaced = preg_replace($pattern, $full, $replaced);
                $hasSynonym = true;
            }
        }
        if ($hasSynonym && ! in_array($replaced, $variations)) {
            $variations[] = $replaced;
        }

        // 3. Unaccented (Vietnamese accent removal)
        $unaccented = $this->removeAccents($normalized);
        if ($unaccented !== $normalized && ! in_array($unaccented, $variations)) {
            $variations[] = $unaccented;
        }

        if ($hasSynonym) {
            $unaccentedReplaced = $this->removeAccents($replaced);
            if ($unaccentedReplaced !== $replaced && ! in_array($unaccentedReplaced, $variations)) {
                $variations[] = $unaccentedReplaced;
            }
        }

        // 4. Keyword search (strip common stopwords)
        $stopwords = ['là gì', 'cho em hỏi', 'như thế nào', 'làm sao', 'của', 'về', 'trong', 'tại', 'với', 'như', 'các', 'những', 'để'];
        $keywordsOnly = $normalized;
        foreach ($stopwords as $stopword) {
            $keywordsOnly = preg_replace('/\b'.preg_quote($stopword, '/').'\b/ui', '', $keywordsOnly);
        }
        $keywordsOnly = trim(preg_replace('/\s+/', ' ', $keywordsOnly));
        if ($keywordsOnly !== $normalized && ! empty($keywordsOnly) && ! in_array($keywordsOnly, $variations)) {
            $variations[] = $keywordsOnly;
        }

        return array_slice($variations, 0, 5);
    }

    /**
     * Remove accents from a Vietnamese string.
     */
    private function removeAccents(string $str): string
    {
        $unicode = [
            'a' => 'á|à|ả|ã|ạ|ă|ắ|ằ|ẳ|ẵ|ặ|â|ấ|ầ|ẩ|ẫ|ậ',
            'd' => 'đ',
            'e' => 'é|è|ẻ|ẽ|ẹ|ê|ế|ề|ể|ễ|ệ',
            'i' => 'í|ì|ỉ|ĩ|ị',
            'o' => 'ó|ò|ỏ|õ|ọ|ô|ố|ồ|ổ|ỗ|ộ|ơ|ớ|ờ|ở|ỡ|ợ',
            'u' => 'ú|ù|ủ|ũ|ụ|ư|ứ|ừ|ử|ữ|ự',
            'y' => 'ý|ỳ|ỷ|ỹ|ỵ',
            'A' => 'Á|À|Ả|Ã|Ạ|Ă|Ắ|Ằ|Ẳ|Ẵ|Ặ|Â|Ấ|Ầ|Ẩ|Ẫ|Ậ',
            'D' => 'Đ',
            'E' => 'É|È|Ẻ|E|Ẹ|Ê|Ế|Ề|Ể|Ễ|Ệ',
            'I' => 'Í|Ì|R|Ĩ|Ị',
            'O' => 'Ó|Ò|Ỏ|Õ|Ọ|Ô|Ố|Ồ|Ổ|Ỗ|Ộ|Ơ|Ớ|Ờ|Ở|Ỡ|Ợ',
            'U' => 'Ú|Ù|Ủ|Ũ|Ụ|Ư|Ứ|Ừ|Ử|Ữ|Ự',
            'Y' => 'Ý|Ỳ|Ỷ|Ỹ|Ỵ',
        ];
        foreach ($unicode as $nonUnicode => $uni) {
            $str = preg_replace("/($uni)/i", $nonUnicode, $str);
        }

        return $str;
    }
}
