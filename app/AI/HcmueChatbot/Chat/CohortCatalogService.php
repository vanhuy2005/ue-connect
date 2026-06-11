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
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            return $this->fetchFromQdrant();
        });
    }

    /**
     * Refresh the cohort catalog cache.
     */
    public function refresh(): array
    {
        Cache::forget(self::CACHE_KEY);

        return $this->allCohorts();
    }

    /**
     * Detect cohort from query dynamically.
     *
     * @return array{canonical: string, matched_alias: string, cohort_num: int}|null
     */
    public function detectCohort(string $query): ?array
    {
        $queryLower = mb_strtolower($query, 'UTF-8');
        $cohorts = $this->allCohorts();

        $cohortNum = null;
        $matchedAlias = null;

        // Pattern 1: match Kxx, khóa xx, khoá xx
        if (preg_match('/(?:khóa|khoá|k|khoa)\s*(\d{2})/iu', $queryLower, $matches)) {
            $cohortNum = (int) $matches[1];
            $matchedAlias = $matches[0];
        }
        // Pattern 2: match tuyển sinh xxxx, năm tuyển sinh xxxx, khóa tuyển sinh xxxx
        elseif (preg_match('/(?:tuyển\s+sinh|năm\s+tuyển\s+sinh|khóa\s+tuyển\s+sinh)\s*(20\d{2})/iu', $queryLower, $matches)) {
            $year = (int) $matches[1];
            $cohortNum = $year - 1974;
            $matchedAlias = $matches[0];
        }
        // Pattern 3: standalone year 20xx
        elseif (preg_match('/(20\d{2})/i', $queryLower, $matches)) {
            $year = (int) $matches[1];
            $cohortNum = $year - 1974;
            $matchedAlias = $matches[0];
        }

        if ($cohortNum !== null) {
            // Check mapped cohort in catalog
            foreach ($cohorts as $cohort) {
                // Check if cohort matches the cohort number (ends with it or contains it as a separate number)
                if (preg_match('/\b'.$cohortNum.'\b/u', $cohort)) {
                    return [
                        'canonical' => $cohort,
                        'matched_alias' => $matchedAlias,
                        'cohort_num' => $cohortNum,
                    ];
                }
            }

            // Fallback to calculated if not found in catalog
            $year = 1974 + $cohortNum;
            $fallbackCanonical = "{$year} - Khóa {$cohortNum}";

            return [
                'canonical' => $fallbackCanonical,
                'matched_alias' => $matchedAlias,
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
