<?php

namespace App\AI\HcmueChatbot\Chat;

use App\AI\HcmueChatbot\Retrieval\QdrantVectorStore;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Provides a live, cached catalog of academic majors sourced from the Qdrant
 * `nganh` payload field. Falls back to `config/hcmue_majors.php` if Qdrant
 * is unreachable.
 *
 * Cache TTL: 24 hours (key: hcmue_major_catalog).
 */
class MajorCatalogService
{
    /** @var string Cache key used for the 24-hour catalog cache. */
    public const CACHE_KEY = 'hcmue_major_catalog';

    /** @var int Cache time-to-live in seconds (24 hours). */
    public const CACHE_TTL = 86400;

    /**
     * Custom alias table for majors that have well-known Vietnamese abbreviations
     * or colloquial short forms that auto-generation cannot infer.
     *
     * Format: alias (lowercase, unaccented) => canonical major name (as stored in Qdrant).
     *
     * @var array<string, string>
     */
    private array $manualAliases = [
        // Công nghệ thông tin
        'cntt' => 'Công nghệ thông tin',
        'it' => 'Công nghệ thông tin',
        // Sư phạm Tin học
        'sp tin' => 'Sư phạm Tin học',
        'sp tin hoc' => 'Sư phạm Tin học',
        'sptin' => 'Sư phạm Tin học',
        // Sư phạm Toán học
        'sp toan' => 'Sư phạm Toán học',
        'sp toan hoc' => 'Sư phạm Toán học',
        'sptoan' => 'Sư phạm Toán học',
        // Sư phạm Ngữ văn
        'sp van' => 'Sư phạm Ngữ văn',
        'sp ngu van' => 'Sư phạm Ngữ văn',
        'spvan' => 'Sư phạm Ngữ văn',
        // Sư phạm Vật lý
        'sp vat ly' => 'Sư phạm Vật lý',
        'sp ly' => 'Sư phạm Vật lý',
        // Sư phạm Hoá học
        'sp hoa' => 'Sư phạm Hoá học',
        'sp hoa hoc' => 'Sư phạm Hoá học',
        // Sư phạm Sinh học
        'sp sinh' => 'Sư phạm Sinh học',
        'sp sinh hoc' => 'Sư phạm Sinh học',
        // Sư phạm Lịch sử
        'sp lich su' => 'Sư phạm Lịch sử',
        'sp su' => 'Sư phạm Lịch sử',
        // Sư phạm Địa lý
        'sp dia ly' => 'Sư phạm Địa lý',
        'sp dia' => 'Sư phạm Địa lý',
        // Sư phạm Tiếng Anh
        'sp anh' => 'Sư phạm Tiếng Anh',
        'sp tieng anh' => 'Sư phạm Tiếng Anh',
        // Sư phạm Công nghệ
        'sp cong nghe' => 'Sư phạm Công nghệ',
        // Sư phạm Tiếng Pháp
        'sp tieng phap' => 'Sư phạm Tiếng Pháp',
        'sp phap' => 'Sư phạm Tiếng Pháp',
        // Sư phạm Tiếng Nga
        'sp tieng nga' => 'Sư phạm Tiếng Nga',
        'sp nga' => 'Sư phạm Tiếng Nga',
        // Sư phạm Tiếng Trung Quốc
        'sp tieng trung' => 'Sư phạm Tiếng Trung Quốc',
        'sp trung' => 'Sư phạm Tiếng Trung Quốc',
        // Sư phạm Khoa học tự nhiên
        'sp khtn' => 'Sư phạm Khoa học tự nhiên',
        'sp khoa hoc tu nhien' => 'Sư phạm Khoa học tự nhiên',
        // Giáo dục Mầm non
        'sp mam non' => 'Giáo dục Mầm non',
        'gd mam non' => 'Giáo dục Mầm non',
        'mam non' => 'Giáo dục Mầm non',
        // Giáo dục Tiểu học
        'sp tieu hoc' => 'Giáo dục Tiểu học',
        'gd tieu hoc' => 'Giáo dục Tiểu học',
        'tieu hoc' => 'Giáo dục Tiểu học',
        // Giáo dục đặc biệt
        'gd dac biet' => 'Giáo dục đặc biệt',
        // Ngôn ngữ Hàn Quốc
        'han quoc' => 'Ngôn ngữ Hàn Quốc',
        'tieng han' => 'Ngôn ngữ Hàn Quốc',
        'nn han' => 'Ngôn ngữ Hàn Quốc',
        'nn hq' => 'Ngôn ngữ Hàn Quốc',
        'ngon ngu han' => 'Ngôn ngữ Hàn Quốc',
        'ngôn ngữ hàn' => 'Ngôn ngữ Hàn Quốc',
        // Ngôn ngữ Nhật
        'nhat ban' => 'Ngôn ngữ Nhật',
        'tieng nhat' => 'Ngôn ngữ Nhật',
        'nn nhat' => 'Ngôn ngữ Nhật',
        // Ngôn ngữ Trung Quốc
        'tieng trung' => 'Ngôn ngữ Trung Quốc',
        'trung quoc' => 'Ngôn ngữ Trung Quốc',
        'nn trung' => 'Ngôn ngữ Trung Quốc',
        'ngôn ngữ trung' => 'Ngôn ngữ Trung Quốc',
        // Ngôn ngữ Nga
        'tieng nga' => 'Ngôn ngữ Nga',
        'nn nga' => 'Ngôn ngữ Nga',
        // Ngôn ngữ Pháp
        'tieng phap' => 'Ngôn ngữ Pháp',
        'nn phap' => 'Ngôn ngữ Pháp',
        // Ngôn ngữ Anh
        'tieng anh' => 'Ngôn ngữ Anh',
        'nn anh' => 'Ngôn ngữ Anh',
        // Tâm lý học
        'tam ly' => 'Tâm lý học',
        'tlh' => 'Tâm lý học',
        // Tâm lý học giáo dục / Tâm lý Giáo dục học
        'tam ly giao duc' => 'Tâm lý học giáo dục',
        // Quản lý giáo dục
        'qlgd' => 'Quản lý giáo dục',
        'quan ly giao duc' => 'Quản lý giáo dục',
        // Văn học
        'van hoc' => 'Văn học',
        // Vật lý học
        'vat ly' => 'Vật lý học',
        // Địa lý học
        'dia ly' => 'Địa lý học',
        // Hoá học
        'hoa hoc' => 'Hoá học',
        // Sinh học ứng dụng
        'sinh hoc' => 'Sinh học ứng dụng',
        'sinh hoc ung dung' => 'Sinh học ứng dụng',
        // Việt Nam học
        'viet nam hoc' => 'Việt Nam học',
        'vnhoc' => 'Việt Nam học',
        // Quốc tế học
        'quoc te hoc' => 'Quốc tế học',
        'qth' => 'Quốc tế học',
    ];

    public function __construct(
        protected QdrantVectorStore $vectorStore
    ) {}

    /**
     * Detect major dynamically from the query.
     *
     * Priority:
     *  1. Exact match: checks if query contains canonical name (case-insensitive)
     *  2. Alias match: checks if query contains alias or its unaccented form
     *  3. Contains match: checks if query contains alias/major or vice versa without boundaries
     *  4. Fuzzy similarity: checks for fuzzy match using similar_text/levenshtein >= 90%
     *
     * @return array{canonical: string, matched_alias: string, detected_major: string}|null
     */
    public function detectMajor(string $query): ?array
    {
        $queryLower = mb_strtolower($query, 'UTF-8');
        $queryNoAccent = $this->removeAccents($queryLower);

        $majors = $this->allMajors();
        $aliases = $this->aliases(); // alias => canonical, sorted by length desc

        // Priority 1: Exact canonical match (case-insensitive)
        foreach ($majors as $canonical) {
            $canonicalLower = mb_strtolower($canonical, 'UTF-8');
            if ($this->containsPhrase($queryLower, $canonicalLower)) {
                return [
                    'canonical' => $canonical,
                    'matched_alias' => $canonicalLower,
                    'detected_major' => $canonicalLower,
                ];
            }
        }

        // Priority 2: Alias match (accented and unaccented)
        foreach ($aliases as $alias => $canonical) {
            if ($this->containsPhrase($queryLower, $alias)) {
                return [
                    'canonical' => $canonical,
                    'matched_alias' => $alias,
                    'detected_major' => $alias,
                ];
            }

            $aliasNoAccent = $this->removeAccents($alias);
            if ($this->containsPhrase($queryNoAccent, $aliasNoAccent)) {
                return [
                    'canonical' => $canonical,
                    'matched_alias' => $alias,
                    'detected_major' => $alias,
                ];
            }
        }

        // Priority 3: Contains match (with word boundaries)
        foreach ($aliases as $alias => $canonical) {
            if ($this->containsPhrase($queryLower, $alias)) {
                return [
                    'canonical' => $canonical,
                    'matched_alias' => $alias,
                    'detected_major' => $alias,
                ];
            }
            $aliasNoAccent = $this->removeAccents($alias);
            if ($this->containsPhrase($queryNoAccent, $aliasNoAccent)) {
                return [
                    'canonical' => $canonical,
                    'matched_alias' => $alias,
                    'detected_major' => $alias,
                ];
            }
        }

        // Priority 4: Fuzzy similarity (>= 90%)
        $queryWords = explode(' ', preg_replace('/\s+/', ' ', $queryLower));
        foreach ($aliases as $alias => $canonical) {
            $aliasWords = explode(' ', $alias);
            $n = count($aliasWords);
            $m = count($queryWords);
            if ($m < $n) {
                continue;
            }
            for ($i = 0; $i <= $m - $n; $i++) {
                $subQuery = implode(' ', array_slice($queryWords, $i, $n));

                similar_text($alias, $subQuery, $percent);
                if ($percent >= 90.0) {
                    return [
                        'canonical' => $canonical,
                        'matched_alias' => $alias,
                        'detected_major' => $subQuery,
                    ];
                }

                similar_text($this->removeAccents($alias), $this->removeAccents($subQuery), $percentNoAccent);
                if ($percentNoAccent >= 90.0) {
                    return [
                        'canonical' => $canonical,
                        'matched_alias' => $alias,
                        'detected_major' => $subQuery,
                    ];
                }
            }
        }

        return null;
    }

    /**
     * Check if haystack contains phrase as a whole token/phrase.
     */
    private function containsPhrase(string $haystack, string $phrase): bool
    {
        if ($phrase === '') {
            return false;
        }

        $boundary = '(?<![a-zA-Z0-9\x{00C0}-\x{017F}\x{1E00}-\x{1EFF}])';
        $boundaryEnd = '(?![a-zA-Z0-9\x{00C0}-\x{017F}\x{1E00}-\x{1EFF}])';

        $pattern = '/'.$boundary.preg_quote($phrase, '/').$boundaryEnd.'/u';

        return (bool) preg_match($pattern, $haystack);
    }

    /**
     * Return all canonical major names, sourced from Qdrant (cached 24 h)
     * or config/hcmue_majors.php as fallback.
     *
     * @return array<string>
     */
    public function allMajors(): array
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            return $this->fetchFromQdrant();
        });
    }

    /**
     * Refresh the catalog from Qdrant and reset the cache.
     *
     * @return array<string>
     */
    public function refresh(): array
    {
        Cache::forget(self::CACHE_KEY);

        return $this->allMajors();
    }

    /**
     * Return a flat map of alias => canonical_major_name, built from:
     *   1. Manual alias table (high-confidence hand-crafted aliases)
     *   2. Auto-generated aliases from the full major list
     *
     * All keys are lowercase + unaccented for safe Unicode-safe matching.
     *
     * @return array<string, string> alias => canonical
     */
    public function aliases(): array
    {
        $result = [];

        // 1. Manual aliases first (highest priority)
        foreach ($this->manualAliases as $alias => $canonical) {
            $result[$alias] = $canonical;
        }

        // 2. Auto-generated from full catalog
        foreach ($this->allMajors() as $major) {
            $autoAliases = $this->generateAliases($major);
            foreach ($autoAliases as $alias) {
                // Do NOT overwrite manual aliases
                if (! isset($result[$alias])) {
                    $result[$alias] = $major;
                }
            }
        }

        // Sort by alias length descending so longest match wins during detection
        uksort($result, fn ($a, $b) => mb_strlen($b, 'UTF-8') <=> mb_strlen($a, 'UTF-8'));

        return $result;
    }

    /**
     * Generate auto aliases for a single canonical major name.
     *
     * Rules (no `\b` — uses accent-removed token matching instead):
     *   - Full lower-cased accented form
     *   - Accent-stripped form
     *   - Stripped of the leading word "ngành"
     *   - SP prefix shortening (Sư phạm → sp)
     *   - GD prefix shortening (Giáo dục → gd)
     *   - NN prefix shortening (Ngôn ngữ → nn)
     *
     * @return array<string>
     */
    public function generateAliases(string $major): array
    {
        $aliases = [];

        $lower = mb_strtolower($major, 'UTF-8');
        $aliases[] = $lower;

        $noAccent = $this->removeAccents($lower);
        if ($noAccent !== $lower) {
            $aliases[] = $noAccent;
        }

        // Strip ending " học" if present (e.g. Sư phạm Toán học -> Sư phạm Toán, Hóa học -> Hóa)
        // This automatically generates highly common short forms like "sư phạm toán", "sư phạm hoá"
        $forms = [$lower, $noAccent];
        foreach ($forms as $form) {
            if (preg_match('/(.+?)\s+học$/u', $form, $m)) {
                $stripped = $m[1];
                if (! in_array($stripped, $aliases, true)) {
                    $aliases[] = $stripped;
                }
            }
        }

        // Strip leading "ngành " prefix if present
        foreach ($aliases as $form) {
            $stripped = preg_replace('/^ngành\s+/u', '', $form);
            if ($stripped !== $form && ! in_array($stripped, $aliases, true)) {
                $aliases[] = $stripped;
            }
        }

        // "Sư phạm X" → "sp x"
        // Also support "sư phạm X" where X has had " học" stripped (already in $aliases)
        $spForms = [];
        foreach ($aliases as $alias) {
            if (mb_strpos($alias, 'sư phạm ') === 0) {
                $rest = mb_substr($alias, mb_strlen('sư phạm '));
                $spForms[] = 'sp '.$rest;
                $spForms[] = 'sp '.$this->removeAccents($rest);
            }
        }
        foreach ($spForms as $spForm) {
            if (! in_array($spForm, $aliases, true)) {
                $aliases[] = $spForm;
            }
        }

        // "Giáo dục X" → "gd x"
        $gdForms = [];
        foreach ($aliases as $alias) {
            if (mb_strpos($alias, 'giáo dục ') === 0) {
                $rest = mb_substr($alias, mb_strlen('giáo dục '));
                $gdForms[] = 'gd '.$rest;
                $gdForms[] = 'gd '.$this->removeAccents($rest);
            }
        }
        foreach ($gdForms as $gdForm) {
            if (! in_array($gdForm, $aliases, true)) {
                $aliases[] = $gdForm;
            }
        }

        // "Ngôn ngữ X" → "nn x" + "tiếng x"
        $nnForms = [];
        foreach ($aliases as $alias) {
            if (mb_strpos($alias, 'ngôn ngữ ') === 0) {
                $rest = mb_substr($alias, mb_strlen('ngôn ngữ '));
                $restNoAccent = $this->removeAccents($rest);
                $nnForms[] = 'nn '.$rest;
                $nnForms[] = 'nn '.$restNoAccent;
                $nnForms[] = 'tiếng '.$rest;
                $nnForms[] = 'tieng '.$restNoAccent;
            }
        }
        foreach ($nnForms as $nnForm) {
            if (! in_array($nnForm, $aliases, true)) {
                $aliases[] = $nnForm;
            }
        }

        return array_values(array_unique($aliases));
    }

    /**
     * Remove Vietnamese diacritic accents from a UTF-8 string.
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

    /**
     * Fetch unique `nganh` values from Qdrant, deduplicating case variants,
     * with fallback to `config/hcmue_majors.php`.
     *
     * @return array<string>
     */
    private function fetchFromQdrant(): array
    {
        try {
            $raw = $this->vectorStore->scrollUniquePayloadValues('nganh');

            if (empty($raw)) {
                Log::warning('MajorCatalogService: Qdrant returned empty nganh list, using config fallback.');

                return $this->configFallback();
            }

            // Deduplicate case variants: prefer the first seen capitalisation for each lowercased form
            $seen = [];
            $deduped = [];
            foreach ($raw as $major) {
                $key = mb_strtolower($major, 'UTF-8');
                if (! isset($seen[$key])) {
                    $seen[$key] = true;
                    $deduped[] = $major;
                }
            }

            sort($deduped);

            Log::info('MajorCatalogService: Loaded '.count($deduped).' majors from Qdrant.');

            return $deduped;
        } catch (\Exception $e) {
            Log::warning('MajorCatalogService: Failed to load from Qdrant, using config fallback. '.$e->getMessage());

            return $this->configFallback();
        }
    }

    /**
     * Return the hardcoded fallback list from config/hcmue_majors.php.
     *
     * @return array<string>
     */
    private function configFallback(): array
    {
        return config('hcmue_majors', []);
    }
}
