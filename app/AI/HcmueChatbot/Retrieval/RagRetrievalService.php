<?php

namespace App\AI\HcmueChatbot\Retrieval;

use App\AI\HcmueChatbot\Chat\CohortCatalogService;
use App\AI\HcmueChatbot\Chat\CohortMajorCatalogService;
use App\AI\HcmueChatbot\LLM\EmbeddingService;
use Illuminate\Support\Facades\Log;

class RagRetrievalService
{
    public array $fallbackAttemptsLogs = [];

    public ?string $lastRewrittenQuery = null;

    public function __construct(
        protected EmbeddingService $embeddingService,
        protected QdrantVectorStore $vectorStore,
        protected AcademicQueryAnalyzer $queryAnalyzer,
        protected ?CohortCatalogService $cohortCatalog = null,
        protected ?CohortMajorCatalogService $cohortMajorCatalog = null
    ) {
        $this->cohortCatalog = $cohortCatalog ?? app(CohortCatalogService::class);
        $this->cohortMajorCatalog = $cohortMajorCatalog ?? app(CohortMajorCatalogService::class);
    }

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
            $this->fallbackAttemptsLogs = [];
            $this->lastRewrittenQuery = null;

            // 1. Analyze the query to extract academic academic entities & intent
            $analysis = $this->queryAnalyzer->analyze($query);
            $isTotalCreditsIntent = ($analysis['intent'] ?? 'general') === 'total_credits';
            $isStudentPolicyIntent = ($analysis['intent'] ?? 'general') === 'student_policy';
            $isDecisionDocumentIntent = ($analysis['intent'] ?? 'general') === 'decision_document_query';

            // 1a. For total_credits or student_policy intent: rewrite the semantic query
            $semanticQuery = $query;
            if ($isTotalCreditsIntent) {
                $semanticQuery = 'Tổng số tín chỉ toàn khóa học chương trình đào tạo tốt nghiệp';
            } elseif ($isStudentPolicyIntent) {
                $queryLower = mb_strtolower($query, 'UTF-8');
                if (str_contains($queryLower, '5%') || str_contains($queryLower, '5 phần trăm')) {
                    $semanticQuery = 'quy định học lại quá 5 phần trăm số tín chỉ';
                } elseif (str_contains($queryLower, 'hạ bằng')) {
                    $semanticQuery = 'điều kiện hạ xếp loại tốt nghiệp do học lại';
                }
            }

            $this->lastRewrittenQuery = $semanticQuery;

            // 2. Generate 3-5 query variations for multi-query search
            $variations = $this->generateQueryVariations($semanticQuery);

            // For total_credits or student_policy, prepend the canonical/rewritten phrase as the primary search variation
            if (($isTotalCreditsIntent || ($isStudentPolicyIntent && $semanticQuery !== $query)) && ! in_array($semanticQuery, $variations, true)) {
                array_unshift($variations, $semanticQuery);
                $variations = array_slice($variations, 0, 5);
            }

            // 3. Map filtered parameters to the new Qdrant schema
            $resolvedFilters = $filters;
            if ($isStudentPolicyIntent) {
                // Do not automatically populate cohort/major filters from analyzer unless they are explicitly provided in input filters
            } else {
                if ($analysis['cohort'] && empty($resolvedFilters['cohort'])) {
                    $resolvedFilters['cohort'] = $analysis['cohort'];
                }
                if ($analysis['document_type'] !== 'unknown' && empty($resolvedFilters['document_type'])) {
                    $resolvedFilters['document_type'] = $analysis['document_type'];
                }
                if ($analysis['major'] && empty($resolvedFilters['major'])) {
                    $docType = $resolvedFilters['document_type'] ?? $analysis['document_type'];
                    if (in_array($docType, ['training_program', 'learning_outcome'])) {
                        $resolvedFilters['major'] = $analysis['major'];
                    }
                }
            }

            if ($isStudentPolicyIntent) {
                $cohortVal = $resolvedFilters['cohort'] ?? null;
                $majorVal = $resolvedFilters['major'] ?? null;
                $docTypeVal = $resolvedFilters['document_type'] ?? null;
            } else {
                $cohortVal = $resolvedFilters['cohort'] ?? $analysis['cohort'];
                $majorVal = $resolvedFilters['major'] ?? $analysis['major'];
                $docTypeVal = $resolvedFilters['document_type'] ?? $analysis['document_type'];
            }

            // 4. Batch embed all variations
            $vectors = $this->embeddingService->batchEmbed($variations);

            if (empty($vectors)) {
                Log::warning('RagRetrievalService: Query vector embedding batch is empty.');

                return [];
            }

            $limit = $isTotalCreditsIntent
                ? config('ai.retrieval.total_credits_top_k', 20)
                : config('ai.retrieval.top_k', 8);
            $minScore = config('ai.retrieval.min_score', 0.55);

            // 5. Search Qdrant for each variation and merge results
            $mergedResults = [];

            if ($isDecisionDocumentIntent) {
                $attempts = [
                    [
                        'name' => 'strict_decision_document',
                        'filters' => ['loai_tai_lieu' => 'quyet_dinh_ban_hanh'],
                    ],
                    [
                        'name' => 'relaxed_student_handbook',
                        'filters' => ['knowledge_type' => 'student_handbook'],
                    ],
                    [
                        'name' => 'no_doc_type_filter',
                        'filters' => [],
                    ],
                ];

                foreach ($attempts as $attempt) {
                    $currentFilters = $attempt['filters'];
                    if (! empty($resolvedFilters['cohort'])) {
                        $currentFilters['khoa_hoc'] = $this->normalizeCohortSpelling($resolvedFilters['cohort']);
                    }
                    if (! empty($resolvedFilters['major'])) {
                        $currentFilters['nganh'] = $resolvedFilters['major'];
                    }

                    $attemptResults = [];
                    foreach ($variations as $idx => $variation) {
                        $vector = $vectors[$idx] ?? [];
                        if (empty($vector)) {
                            continue;
                        }
                        $points = $this->vectorStore->search($vector, $limit, $minScore, $currentFilters);
                        foreach ($points as $point) {
                            $id = (int) $point['id'];
                            $score = (float) $point['score'];
                            if (! isset($attemptResults[$id]) || $attemptResults[$id]['score'] < $score) {
                                $attemptResults[$id] = $point;
                            }
                        }
                    }

                    $count = count($attemptResults);
                    $topScore = 0.0;
                    if ($count > 0) {
                        $topScore = (float) max(array_column($attemptResults, 'score'));
                    }

                    $this->fallbackAttemptsLogs[] = [
                        'attempt_name' => $attempt['name'],
                        'filter' => $currentFilters,
                        'result_count' => $count,
                        'top_score' => $topScore,
                    ];

                    if ($count > 0) {
                        $mergedResults = $attemptResults;
                        break;
                    }
                }
            } else {
                $queryLower = mb_strtolower($query, 'UTF-8');

                // Classify knowledge_type based on rules 6 and 7
                $knowledgeType = null;

                // Handbook, regulation, and policy keywords
                $handbookKeywords = [
                    'sổ tay', 'student handbook', 'sotaysinhvien', 'quy chế', 'quy định',
                    'quyche', 'quydinh', 'học vụ', 'gpa', 'học phí', 'học bổng', 'rèn luyện',
                    'tốt nghiệp', 'ra trường', 'cảnh báo', 'buộc thôi học', 'học lại', 'học cải thiện',
                    'đăng ký học phần', 'hủy học phần', 'miễn giảm', 'kỷ luật', 'thôi học',
                    'rớt môn', 'nợ môn',
                ];
                // Curriculum, course, and credit keywords
                $curriculumKeywords = [
                    'chương trình đào tạo', 'chương trình khung', 'ctđt', 'ctdt', 'môn học',
                    'học phần', 'tín chỉ', 'tc', 'học kỳ', 'môn tự chọn', 'học phần tự chọn',
                    'môn bắt buộc', 'môn tiên quyết', 'chuẩn đầu ra', 'cdr', 'learning outcome',
                    'ngành',
                ];

                if ($isStudentPolicyIntent) {
                    $knowledgeType = 'student_handbook';
                } elseif ($docTypeVal === 'student_handbook' || $docTypeVal === 'academic_regulation') {
                    $knowledgeType = 'student_handbook';
                } elseif ($docTypeVal === 'training_program' || $docTypeVal === 'learning_outcome') {
                    $knowledgeType = 'curriculum';
                } else {
                    $hasHandbook = false;
                    foreach ($handbookKeywords as $kw) {
                        if (str_contains($queryLower, $kw)) {
                            $hasHandbook = true;
                            break;
                        }
                    }
                    $hasCurriculum = false;
                    foreach ($curriculumKeywords as $kw) {
                        if (str_contains($queryLower, $kw)) {
                            $hasCurriculum = true;
                            break;
                        }
                    }

                    if ($hasHandbook) {
                        $knowledgeType = 'student_handbook';
                    } elseif ($hasCurriculum) {
                        $knowledgeType = 'curriculum';
                    }
                }

                // Default fallback
                if ($knowledgeType === null) {
                    $knowledgeType = 'curriculum';
                }

                // Map document_type to loai_tai_lieu
                $loaiTaiLieu = null;
                if ($docTypeVal && $docTypeVal !== 'unknown') {
                    if ($docTypeVal === 'training_program') {
                        $loaiTaiLieu = 'chuong_trinh_khung';
                    } elseif ($docTypeVal === 'learning_outcome') {
                        $loaiTaiLieu = 'chuan_dau_ra';
                    } elseif ($docTypeVal === 'academic_regulation') {
                        $loaiTaiLieu = 'quyet_dinh_ban_hanh';
                    } elseif ($docTypeVal === 'student_handbook') {
                        $loaiTaiLieu = 'so_tay_sinh_vien';
                    }
                }

                // Build the 4 fallback attempts (Stop at Attempt 4)
                $attempts = [
                    [
                        'name' => 'cohort_and_major',
                        'filters' => [
                            'knowledge_type' => $knowledgeType,
                            'khoa_hoc' => $cohortVal ? $this->normalizeCohortSpelling($cohortVal) : null,
                            'nganh' => $isStudentPolicyIntent ? null : $majorVal,
                            'loai_tai_lieu' => $loaiTaiLieu,
                        ],
                    ],
                    [
                        'name' => 'cohort_only',
                        'filters' => [
                            'knowledge_type' => $knowledgeType,
                            'khoa_hoc' => $cohortVal ? $this->normalizeCohortSpelling($cohortVal) : null,
                            'loai_tai_lieu' => $loaiTaiLieu,
                        ],
                    ],
                    [
                        'name' => 'major_only',
                        'filters' => [
                            'knowledge_type' => $knowledgeType,
                            'nganh' => $isStudentPolicyIntent ? null : $majorVal,
                            'loai_tai_lieu' => $loaiTaiLieu,
                        ],
                    ],
                    [
                        'name' => 'knowledge_type_only',
                        'filters' => [
                            'knowledge_type' => $knowledgeType,
                        ],
                    ],
                ];

                foreach ($attempts as $attempt) {
                    $currentFilters = array_filter($attempt['filters'], fn ($v) => $v !== null && $v !== '');

                    $attemptResults = [];
                    foreach ($variations as $idx => $variation) {
                        $vector = $vectors[$idx] ?? [];
                        if (empty($vector)) {
                            continue;
                        }
                        $points = $this->vectorStore->search($vector, $limit, $minScore, $currentFilters);
                        foreach ($points as $point) {
                            $id = (int) $point['id'];
                            $score = (float) $point['score'];
                            if (! isset($attemptResults[$id]) || $attemptResults[$id]['score'] < $score) {
                                $attemptResults[$id] = $point;
                            }
                        }
                    }

                    $count = count($attemptResults);
                    $topScore = 0.0;
                    if ($count > 0) {
                        $topScore = (float) max(array_column($attemptResults, 'score'));
                    }

                    $this->fallbackAttemptsLogs[] = [
                        'attempt_name' => $attempt['name'],
                        'filter' => $currentFilters,
                        'result_count' => $count,
                        'top_score' => $topScore,
                    ];

                    if ($count > 0) {
                        $mergedResults = $attemptResults;
                        break;
                    }
                }
            }

            if (empty($mergedResults)) {
                // Log detailed report for empty results
                $pointsCount = 0;
                $topUnfiltered = [];
                try {
                    $diagnoseReport = app(QdrantDiagnosticsService::class)->diagnose();
                    $pointsCount = $diagnoseReport['points_count'] ?? 0;

                    $dummyVector = $vectors[0] ?? [];
                    if (! empty($dummyVector)) {
                        $topUnfiltered = $this->vectorStore->search($dummyVector, 3, 0.0, []);
                    }
                } catch (\Exception $ex) {
                    Log::warning('RagRetrievalService: Diagnostics failed during empty result handling: '.$ex->getMessage());
                }

                Log::warning('RagRetrievalService: Qdrant returned 0 results.', [
                    'normalized_question' => $query,
                    'detected_cohort' => $cohortVal,
                    'detected_major' => $majorVal,
                    'detected_document_type' => $docTypeVal,
                    'qdrant_collection_name' => $this->vectorStore->getCollectionName(),
                    'qdrant_filter_json' => json_encode($resolvedFilters, JSON_UNESCAPED_UNICODE),
                    'collection_points_count' => $pointsCount,
                    'top_unfiltered_samples' => $topUnfiltered,
                ]);

                return [];
            }

            // 6. Apply Rule-Based Reranking
            $reranked = [];
            foreach ($mergedResults as $point) {
                $payload = $point['payload'] ?? [];
                $originalScore = (float) $point['score'];
                $rerankScore = $originalScore;

                // Rule A: +0.20 if document_type matches intent
                $chunkDocType = $payload['loai_tai_lieu'] ?? $payload['document_type'] ?? 'unknown';
                // Normalize doc types for rule matching
                if ($chunkDocType === 'chuong_trinh_khung') {
                    $chunkDocType = 'training_program';
                } elseif ($chunkDocType === 'chuan_dau_ra') {
                    $chunkDocType = 'learning_outcome';
                } elseif ($chunkDocType === 'so_tay_sinh_vien') {
                    $chunkDocType = 'student_handbook';
                } elseif ($chunkDocType === 'quyet_dinh_ban_hanh') {
                    $chunkDocType = 'academic_regulation';
                }

                $queryDocType = $analysis['document_type'] ?? 'unknown';
                if ($queryDocType !== 'unknown' && $chunkDocType === $queryDocType) {
                    $rerankScore += 0.20;
                }

                // Rule B: +0.15 if cohort matches
                $chunkCohort = $payload['khoa_hoc'] ?? $payload['cohort'] ?? null;
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
                $chunkMajor = $payload['nganh'] ?? $payload['major'] ?? null;
                $queryMajor = $analysis['major'] ?? null;
                if ($queryMajor !== null) {
                    if ($chunkMajor && str_contains(mb_strtolower($chunkMajor, 'UTF-8'), mb_strtolower($queryMajor, 'UTF-8'))) {
                        $rerankScore += 0.15;
                    }
                }

                // Rule D: +0.10 if faculty matches
                $chunkFaculty = $payload['khoa'] ?? $payload['faculty'] ?? null;
                $queryFaculty = $analysis['faculty'] ?? null;
                if ($queryFaculty !== null) {
                    if ($chunkFaculty && str_contains(mb_strtolower($chunkFaculty, 'UTF-8'), mb_strtolower($queryFaculty, 'UTF-8'))) {
                        $rerankScore += 0.10;
                    }
                }

                // Rule E: +0.10 if file name contains topic keyword or major
                $docName = mb_strtolower($payload['source_file'] ?? $payload['document_name'] ?? '', 'UTF-8');
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

                // Rule H: boost for chunks containing total-credits summary phrases
                // (only applied for total_credits intent to avoid false positives).
                if ($isTotalCreditsIntent) {
                    $chunkTextLower = mb_strtolower($payload['text'] ?? $payload['chunk_text'] ?? '', 'UTF-8');
                    if (str_contains($chunkTextLower, 'tổng số tín chỉ toàn khóa học')) {
                        $rerankScore += 0.40;
                    } elseif (str_contains($chunkTextLower, 'tổng số tín chỉ')) {
                        $rerankScore += 0.30;
                    } elseif (str_contains($chunkTextLower, 'toàn khóa học')) {
                        $rerankScore += 0.20;
                    } elseif (
                        str_contains($chunkTextLower, 'số tín chỉ:') &&
                        ! str_contains($chunkTextLower, 'tổng số tín chỉ')
                    ) {
                        // Individual course credit line — do not boost
                        $rerankScore -= 0.05;
                    }
                }

                // Rule I: boost for student_policy handbook retrieval chunks
                if ($isStudentPolicyIntent) {
                    $chunkTextLower = mb_strtolower($payload['text'] ?? $payload['chunk_text'] ?? '', 'UTF-8');
                    $boostKeywords = [
                        'hạ bằng' => 0.40,
                        'hạ xếp loại' => 0.40,
                        'xếp loại tốt nghiệp' => 0.35,
                        'học lại' => 0.30,
                        'cảnh báo học vụ' => 0.35,
                        'buộc thôi học' => 0.35,
                        'thôi học' => 0.35,
                        'đình chỉ' => 0.30,
                        'bảo lưu' => 0.30,
                        'nghỉ học tạm thời' => 0.30,
                        'chuẩn đầu ra' => 0.30,
                        'quy chế' => 0.20,
                    ];

                    foreach ($boostKeywords as $kw => $boostAmount) {
                        if (str_contains($chunkTextLower, $kw)) {
                            $rerankScore += $boostAmount;
                        }
                    }
                }

                // Rule J: boost for decision document query chunks
                if ($isDecisionDocumentIntent) {
                    $chunkDocType = $payload['loai_tai_lieu'] ?? $payload['document_type'] ?? 'unknown';
                    if ($chunkDocType === 'quyet_dinh_ban_hanh') {
                        $rerankScore += 0.30;
                    }
                }

                $point['original_score'] = $originalScore;
                $point['rerank_score'] = $rerankScore;
                $point['score'] = $rerankScore; // active score for sorting

                $reranked[] = $point;
            }

            // Sort by reranked score descending
            usort($reranked, fn ($a, $b) => $b['score'] <=> $a['score']);
            $topPoints = array_slice($reranked, 0, $limit);

            $formattedResults = [];
            foreach ($topPoints as $point) {
                $id = (int) $point['id'];
                $payload = $point['payload'] ?? [];
                $chunkText = $payload['text'] ?? $payload['chunk_text'] ?? '';

                $formattedResults[] = [
                    'id' => $id,
                    'score' => (float) ($point['original_score'] ?? $point['score']),
                    'rerank_score' => (float) ($point['rerank_score'] ?? $point['score']),
                    'chunk_text' => $chunkText,
                    'page_start' => $payload['page'] ?? $payload['page_start'] ?? null,
                    'page_end' => $payload['page'] ?? $payload['page_end'] ?? null,
                    'part' => $payload['part'] ?? null,
                    'chapter' => $payload['chapter'] ?? null,
                    'section' => $payload['section'] ?? null,
                    'article' => $payload['article'] ?? null,
                    'clause' => $payload['clause'] ?? null,
                    'document_name' => $payload['source_file'] ?? $payload['document_name'] ?? '',
                    'document_type' => $payload['loai_tai_lieu'] ?? $payload['document_type'] ?? '',
                    'cohort' => $payload['khoa_hoc'] ?? $payload['cohort'] ?? null,
                    'effective_year' => $payload['academic_year'] ?? $payload['effective_year'] ?? null,
                    'metadata' => $payload,
                ];
            }

            // ── Lexical fallback for total_credits intent ─────────────────────────
            // If no top chunk already contains the total-credits phrase, scroll
            // Qdrant with the same metadata filters and inject matching chunks.
            if ($isTotalCreditsIntent) {
                $alreadyHasTotalPhrase = false;
                foreach ($formattedResults as $r) {
                    if (str_contains(mb_strtolower($r['chunk_text'], 'UTF-8'), 'tổng số tín chỉ')) {
                        $alreadyHasTotalPhrase = true;
                        break;
                    }
                }

                if (! $alreadyHasTotalPhrase) {
                    Log::info('RagRetrievalService: total_credits fallback — no summary chunk in top results, running lexical scroll.');

                    $scrollFilter = array_filter($currentFilters ?? [], fn ($v) => $v !== null && $v !== '');
                    $scrollMatches = $this->vectorStore->scrollPayloadText(
                        'Tổng số tín chỉ',
                        $scrollFilter,
                        batchSize: 100,
                        maxBatches: 10
                    );

                    foreach ($scrollMatches as $scrollPoint) {
                        $scrollId = (int) $scrollPoint['id'];
                        // Skip if already in formattedResults
                        $alreadyIncluded = false;
                        foreach ($formattedResults as $existing) {
                            if ($existing['id'] === $scrollId) {
                                $alreadyIncluded = true;
                                break;
                            }
                        }
                        if ($alreadyIncluded) {
                            continue;
                        }

                        $scrollPayload = $scrollPoint['payload'] ?? [];
                        $scrollText = $scrollPayload['text'] ?? $scrollPayload['chunk_text'] ?? '';

                        Log::info('RagRetrievalService: injecting lexical fallback chunk.', [
                            'id' => $scrollId,
                            'page' => $scrollPayload['page'] ?? null,
                            'text_preview' => mb_substr($scrollText, 0, 120),
                        ]);

                        // Prepend so the summary chunk appears first in context
                        array_unshift($formattedResults, [
                            'id' => $scrollId,
                            'score' => 0.95, // synthetic high score — lexical exact match
                            'rerank_score' => 0.99,
                            'chunk_text' => $scrollText,
                            'page_start' => $scrollPayload['page'] ?? $scrollPayload['page_start'] ?? null,
                            'page_end' => $scrollPayload['page'] ?? $scrollPayload['page_end'] ?? null,
                            'part' => $scrollPayload['part'] ?? null,
                            'chapter' => $scrollPayload['chapter'] ?? null,
                            'section' => $scrollPayload['section'] ?? null,
                            'article' => $scrollPayload['article'] ?? null,
                            'clause' => $scrollPayload['clause'] ?? null,
                            'document_name' => $scrollPayload['source_file'] ?? $scrollPayload['document_name'] ?? '',
                            'document_type' => $scrollPayload['loai_tai_lieu'] ?? $scrollPayload['document_type'] ?? '',
                            'cohort' => $scrollPayload['khoa_hoc'] ?? $scrollPayload['cohort'] ?? null,
                            'effective_year' => $scrollPayload['academic_year'] ?? $scrollPayload['effective_year'] ?? null,
                            'metadata' => $scrollPayload,
                        ]);
                    }

                    if (! empty($scrollMatches)) {
                        Log::info('RagRetrievalService: lexical fallback injected '.count($scrollMatches).' chunk(s).');
                    } else {
                        Log::warning('RagRetrievalService: lexical fallback found no chunks containing "Tổng số tín chỉ".');
                    }
                }
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

    /**
     * Normalize cohort spelling (Khoá vs Khóa) based on the database convention.
     * Cohorts <= 48 use "Khoá" (accent on o).
     * Cohorts >= 49 use "Khóa" (accent on a).
     */
    private function normalizeCohortSpelling(string $cohort): string
    {
        $matched = $this->cohortMajorCatalog->detectCohort($cohort);
        if ($matched) {
            return $matched['canonical_cohort'];
        }

        return $cohort;
    }
}
