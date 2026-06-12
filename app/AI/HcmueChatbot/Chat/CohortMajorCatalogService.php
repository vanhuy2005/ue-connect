<?php

namespace App\AI\HcmueChatbot\Chat;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class CohortMajorCatalogService
{
    public function __construct(
        protected MajorCatalogService $majorCatalog,
        protected CohortCatalogService $cohortCatalog
    ) {}

    /**
     * Get the cached or fallback catalog data.
     */
    public function getCatalog(): array
    {
        $cached = Cache::get('hcmue_catalog_data');
        if (is_array($cached) && ! empty($cached)) {
            return $cached;
        }

        if (Storage::exists('hcmue_catalog.json')) {
            try {
                $content = Storage::get('hcmue_catalog.json');
                $data = json_decode($content, true);
                if (is_array($data) && ! empty($data)) {
                    Cache::put('hcmue_catalog_data', $data, 86400 * 30); // Cache for 30 days

                    return $data;
                }
            } catch (\Exception $e) {
                Log::warning('Failed loading hcmue_catalog.json: '.$e->getMessage());
            }
        }

        return config('hcmue_major_dictionary', []);
    }

    /**
     * Get the catalog data source name (qdrant or config).
     */
    public function getCatalogSource(): string
    {
        $cached = Cache::get('hcmue_catalog_data');
        if (is_array($cached) && ! empty($cached)) {
            return 'qdrant';
        }

        if (Storage::exists('hcmue_catalog.json')) {
            return 'qdrant';
        }

        return 'config';
    }

    /**
     * Detect cohort from query.
     */
    public function detectCohort(string $query): ?array
    {
        $queryLower = mb_strtolower($query, 'UTF-8');
        $aliases = config('hcmue_cohort_aliases', []);
        uksort($aliases, fn ($a, $b) => mb_strlen($b, 'UTF-8') <=> mb_strlen($a, 'UTF-8'));

        $candidate = null;
        $matchedAlias = null;
        foreach ($aliases as $alias => $canonical) {
            if ($this->containsPhrase($queryLower, $alias)) {
                if ($this->isBareYearAllowed($query, $alias)) {
                    $candidate = $canonical;
                    $matchedAlias = $alias;
                    break;
                }
            }
        }

        if (! $candidate) {
            return null;
        }

        $catalog = $this->getCatalog();
        $catalogCohorts = $catalog['cohorts'] ?? [];

        $finalCohort = null;
        if (! empty($catalogCohorts)) {
            foreach ($catalogCohorts as $qCohort) {
                if (mb_strtolower($qCohort, 'UTF-8') === mb_strtolower($candidate, 'UTF-8')) {
                    $finalCohort = $qCohort;
                    break;
                }
            }
        }

        if (! $finalCohort) {
            $finalCohort = $candidate;
        }

        $result = [
            'canonical_cohort' => $finalCohort,
            'cohort_alias' => $matchedAlias,
        ];

        if (preg_match('/^(k\d+|khóa\s*\d+|khoá\s*\d+)$/ui', $matchedAlias)) {
            $boundary = '(?<![a-zA-Z0-9\x{00C0}-\x{017F}\x{1E00}-\x{1EFF}])';
            $boundaryEnd = '(?![a-zA-Z0-9\x{00C0}-\x{017F}\x{1E00}-\x{1EFF}])';
            $pattern = '/'.$boundary.preg_quote($matchedAlias, '/').$boundaryEnd.'/iu';
            if (preg_match($pattern, $query, $matches)) {
                $result['detected_cohort'] = $matches[0];
            } else {
                $result['detected_cohort'] = $matchedAlias;
            }
        }

        return $result;
    }

    /**
     * Detect major from query.
     */
    public function detectMajor(string $query): ?array
    {
        $queryLower = mb_strtolower($query, 'UTF-8');
        $queryNoAccent = $this->removeAccents($queryLower);

        $catalog = $this->getCatalog();
        $catalogMajors = $catalog['majors'] ?? [];

        // Priority 1: Exact case-sensitive canonical match
        foreach ($catalogMajors as $canonical) {
            if ($this->containsPhrase($query, $canonical, true)) {
                return [
                    'matched_alias' => $canonical,
                    'canonical_major' => $canonical,
                ];
            }
        }

        // Priority 2: Exact case-insensitive canonical match with case similarity scoring
        $candidates = [];
        foreach ($catalogMajors as $canonical) {
            $canonicalLower = mb_strtolower($canonical, 'UTF-8');
            if ($this->containsPhrase($queryLower, $canonicalLower)) {
                $boundary = '(?<![a-zA-Z0-9\x{00C0}-\x{017F}\x{1E00}-\x{1EFF}])';
                $boundaryEnd = '(?![a-zA-Z0-9\x{00C0}-\x{017F}\x{1E00}-\x{1EFF}])';
                $pattern = '/'.$boundary.preg_quote($canonicalLower, '/').$boundaryEnd.'/iu';
                if (preg_match($pattern, $query, $matches)) {
                    $candidates[] = [
                        'canonical' => $canonical,
                        'matched_sub' => $matches[0],
                    ];
                } else {
                    $candidates[] = [
                        'canonical' => $canonical,
                        'matched_sub' => $canonical,
                    ];
                }
            }
        }

        if (! empty($candidates)) {
            $bestCanonical = null;
            $bestScore = -1;
            $bestMatchedAlias = null;

            foreach ($candidates as $cand) {
                $score = 0;
                $canonical = $cand['canonical'];
                $matchedSub = $cand['matched_sub'];
                $len = min(mb_strlen($canonical, 'UTF-8'), mb_strlen($matchedSub, 'UTF-8'));
                for ($i = 0; $i < $len; $i++) {
                    if (mb_substr($canonical, $i, 1, 'UTF-8') === mb_substr($matchedSub, $i, 1, 'UTF-8')) {
                        $score++;
                    }
                }

                if ($score > $bestScore) {
                    $bestScore = $score;
                    $bestCanonical = $canonical;
                    $bestMatchedAlias = mb_strtolower($matchedSub, 'UTF-8');
                }
            }

            return [
                'matched_alias' => $bestMatchedAlias,
                'canonical_major' => $bestCanonical,
            ];
        }

        // Priority 2: Alias config match
        $aliases = config('hcmue_major_aliases', []);
        uksort($aliases, fn ($a, $b) => mb_strlen($b, 'UTF-8') <=> mb_strlen($a, 'UTF-8'));

        foreach ($aliases as $alias => $canonical) {
            $aliasLower = mb_strtolower($alias, 'UTF-8');
            if ($this->containsPhrase($queryLower, $aliasLower)) {
                return [
                    'matched_alias' => $aliasLower,
                    'canonical_major' => $canonical,
                ];
            }

            $aliasNoAccent = $this->removeAccents($aliasLower);
            if ($this->containsPhrase($queryNoAccent, $aliasNoAccent)) {
                return [
                    'matched_alias' => $aliasLower,
                    'canonical_major' => $canonical,
                ];
            }
        }

        // Priority 3: Contains match against catalog majors
        foreach ($catalogMajors as $canonical) {
            $canonicalLower = mb_strtolower($canonical, 'UTF-8');
            if (str_contains($queryLower, $canonicalLower)) {
                return [
                    'matched_alias' => $canonicalLower,
                    'canonical_major' => $canonical,
                ];
            }
        }

        // Priority 4: Fuzzy match >= 90% (against catalog majors and aliases)
        $targets = [];
        foreach ($catalogMajors as $canonical) {
            $targets[mb_strtolower($canonical, 'UTF-8')] = $canonical;
        }
        foreach ($aliases as $alias => $canonical) {
            $targets[mb_strtolower($alias, 'UTF-8')] = $canonical;
        }

        $queryWords = explode(' ', preg_replace('/\s+/', ' ', $queryLower));
        foreach ($targets as $targetLower => $canonical) {
            $targetWords = explode(' ', $targetLower);
            $n = count($targetWords);
            $m = count($queryWords);
            if ($m < $n) {
                continue;
            }
            for ($i = 0; $i <= $m - $n; $i++) {
                $subQuery = implode(' ', array_slice($queryWords, $i, $n));

                similar_text($targetLower, $subQuery, $percent);
                if ($percent >= 90.0 && mb_strlen($targetLower, 'UTF-8') >= 5 && mb_strlen($subQuery, 'UTF-8') >= 5) {
                    return [
                        'matched_alias' => $targetLower,
                        'canonical_major' => $canonical,
                    ];
                }

                similar_text($this->removeAccents($targetLower), $this->removeAccents($subQuery), $percentNoAccent);
                if ($percentNoAccent >= 90.0 && mb_strlen($targetLower, 'UTF-8') >= 5 && mb_strlen($subQuery, 'UTF-8') >= 5) {
                    return [
                        'matched_alias' => $targetLower,
                        'canonical_major' => $canonical,
                    ];
                }
            }
        }

        return null;
    }

    /**
     * Check if a major is valid for a cohort.
     */
    public function hasMajorInCohort(string $cohort, string $major): bool
    {
        $majors = $this->getMajorsForCohort($cohort);
        foreach ($majors as $m) {
            if (mb_strtolower($m, 'UTF-8') === mb_strtolower($major, 'UTF-8')) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get majors in a cohort.
     */
    public function getMajorsForCohort(string $cohort): array
    {
        $catalog = $this->getCatalog();
        $byCohort = $catalog['by_cohort'] ?? [];

        foreach ($byCohort as $c => $majors) {
            if (mb_strtolower($c, 'UTF-8') === mb_strtolower($cohort, 'UTF-8')) {
                return $majors;
            }
        }

        return [];
    }

    /**
     * Get cohorts for a major.
     */
    public function getCohortsForMajor(string $major): array
    {
        $catalog = $this->getCatalog();
        $byMajor = $catalog['by_major'] ?? [];

        foreach ($byMajor as $m => $cohorts) {
            if (mb_strtolower($m, 'UTF-8') === mb_strtolower($major, 'UTF-8')) {
                return $cohorts;
            }
        }

        return [];
    }

    /**
     * Disambiguate bare year context (only allow cohort mapping if query context contains cohort keywords).
     */
    protected function isBareYearAllowed(string $query, string $alias): bool
    {
        if (preg_match('/^\d{4}$/', $alias)) {
            $queryLower = mb_strtolower($query, 'UTF-8');
            $contextWords = [
                'khóa', 'khoá', 'ngành', 'chương trình', 'tín chỉ', 'học kỳ', 'ra trường', 'tốt nghiệp',
                'khoa', 'nganh', 'chuong trinh', 'tin chi', 'hoc ky', 'ra truong', 'tot nghiep',
            ];
            foreach ($contextWords as $word) {
                if (str_contains($queryLower, $word)) {
                    return true;
                }
            }

            // Check major aliases
            $majorAliases = config('hcmue_major_aliases', []);
            foreach ($majorAliases as $mAlias => $canonical) {
                if (str_contains($queryLower, $mAlias)) {
                    return true;
                }
            }

            // Check dictionary canonical majors
            $dictionaryMajors = config('hcmue_major_dictionary.majors', []);
            foreach ($dictionaryMajors as $major) {
                if (str_contains($queryLower, mb_strtolower($major, 'UTF-8'))) {
                    return true;
                }
            }

            return false;
        }

        return true;
    }

    /**
     * Match full phrase with word boundaries.
     */
    protected function containsPhrase(string $haystack, string $phrase, bool $caseSensitive = false): bool
    {
        if ($phrase === '') {
            return false;
        }

        $boundary = '(?<![a-zA-Z0-9\x{00C0}-\x{017F}\x{1E00}-\x{1EFF}])';
        $boundaryEnd = '(?![a-zA-Z0-9\x{00C0}-\x{017F}\x{1E00}-\x{1EFF}])';

        $modifiers = $caseSensitive ? 'u' : 'iu';
        $pattern = '/'.$boundary.preg_quote($phrase, '/').$boundaryEnd.'/'.$modifiers;

        return (bool) preg_match($pattern, $haystack);
    }

    /**
     * Remove accents from a Vietnamese string.
     */
    public function removeAccents(string $str): string
    {
        $map = [
            'a' => 'á|à|ả|ã|ạ|ă|ắ|ằ|ẳ|ẵ|ặ|â|ấ|ầ|ẩ|ẫ|ậ',
            'd' => 'đ',
            'e' => 'é|è|ẻ|ẽ|ẹ|ê|ế|ề|ể|ễ|ệ',
            'i' => 'í|ì|ỉ|ĩ|ị',
            'o' => 'ó|ò|ỏ|õ|ọ|ô|ố|ồ|ổ|ỗ|ộ|ơ|ớ|ờ|ở|ỡ|ợ',
            'u' => 'ú|ù|ủ|ũ|ụ|ư|ứ|ừ|ử|ữ|ự',
            'y' => 'ý|ỳ|ỷ|ỹ|ỵ',
        ];

        foreach ($map as $ascii => $pattern) {
            $str = preg_replace("/({$pattern})/iu", $ascii, $str);
        }

        return $str;
    }
}
