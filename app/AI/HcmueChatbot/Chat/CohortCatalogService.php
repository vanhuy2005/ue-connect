<?php

namespace App\AI\HcmueChatbot\Chat;

use App\AI\HcmueChatbot\Retrieval\QdrantVectorStore;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Sourced dynamically from Qdrant unique `khoa_hoc` values, cached for 24 hours.
 */
class CohortCatalogService
{
    public const CACHE_KEY = 'hcmue_cohort_catalog';

    public const CACHE_TTL = 86400; // 24 hours

    private array $fallbackCohorts = [
        '2018 - Khoá 44',
        '2019 - Khoá 45',
        '2020 - Khoá 46',
        '2021 - Khoá 47',
        '2022 - Khóa 48',
        '2023 - Khóa 49',
        '2024 - Khóa 50',
        '2025 - Khóa 51',
    ];

    public function __construct(
        protected QdrantVectorStore $vectorStore
    ) {}

    /**
     * Get all unique cohorts from Qdrant (cached 24h).
     */
    public function allCohorts(): array
    {
        $catalogService = app(CohortMajorCatalogService::class);
        $catalog = $catalogService->getCatalog();

        return $catalog['cohorts'] ?? $this->fallbackCohorts;
    }

    /**
     * Refresh the cohort catalog cache.
     */
    public function refresh(): array
    {
        Cache::forget(self::CACHE_KEY);
        Cache::forget('hcmue_catalog_data');

        return $this->allCohorts();
    }

    /**
     * Detect cohort from query dynamically.
     *
     * @return array{canonical: string, matched_alias: string, cohort_num: int}|null
     */
    public function detectCohort(string $query): ?array
    {
        $catalogService = app(CohortMajorCatalogService::class);
        $res = $catalogService->detectCohort($query);
        if ($res) {
            $canonical = $res['canonical_cohort'];
            $cohortNum = null;
            if (preg_match('/\b\d+\b/u', $canonical, $matches)) {
                $cohortNum = (int) $matches[0];
            }

            return [
                'canonical' => $canonical,
                'matched_alias' => $res['cohort_alias'] ?? $res['detected_cohort'] ?? '',
                'cohort_num' => $cohortNum,
            ];
        }

        return null;
    }

    /**
     * Fetch cohorts from Qdrant.
     */
    private function fetchFromQdrant(): array
    {
        try {
            $raw = $this->vectorStore->scrollUniquePayloadValues('khoa_hoc');
            if (empty($raw)) {
                Log::warning('CohortCatalogService: Qdrant returned empty khoa_hoc list, using fallback.');

                return $this->fallbackCohorts;
            }

            sort($raw);
            Log::info('CohortCatalogService: Loaded '.count($raw).' cohorts from Qdrant.');

            return $raw;
        } catch (\Exception $e) {
            Log::warning('CohortCatalogService: Failed to fetch from Qdrant: '.$e->getMessage());

            return $this->fallbackCohorts;
        }
    }
}
