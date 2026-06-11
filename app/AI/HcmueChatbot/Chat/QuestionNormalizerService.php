<?php

namespace App\AI\HcmueChatbot\Chat;

class QuestionNormalizerService
{
    /**
     * Common abbreviation mappings used by students in Vietnamese.
     * Major-related expansions are handled by MajorCatalogService::aliases().
     * Only generic academic abbreviations remain here.
     *
     * @var array<string, string>
     */
    private array $abbreviations = [
        'ctdt' => 'chương trình đào tạo',
        'ctđt' => 'chương trình đào tạo',
        'tc' => 'tín chỉ',
        'hp' => 'học phần',
        'hk' => 'học kỳ',
        'sv' => 'sinh viên',
        'gv' => 'giảng viên',
        'pvdt' => 'Phòng Đào tạo',
    ];

    /**
     * Cohort patterns — matches standalone cohort mentions (e.g. k49, khóa 49, 2023 - khóa 49).
     */
    private string $cohortPattern = '/(?<![a-zA-Z0-9_\x{00C0}-\x{017F}\x{1E00}-\x{1EFF}])((?:20\d{2}\s*-\s*)?)(?:khóa|khoá|k|khoa)\s*(\d{2})(?![a-zA-Z0-9_\x{00C0}-\x{017F}\x{1E00}-\x{1EFF}])/iu';

    public function __construct(
        protected MajorCatalogService $majorCatalog,
        protected CohortCatalogService $cohortCatalog
    ) {}

    /**
     * Normalize a raw question and extract detected entities.
     *
     * @return array{
     *   original_question: string,
     *   normalized_question: string,
     *   detected_terms: array
     * }
     */
    public function normalize(string $question): array
    {
        $original = $question;
        $normalized = $question;

        // Normalize whitespace
        $normalized = preg_replace('/\s+/', ' ', trim($normalized));

        // Sort abbreviations by length descending to replace longer phrases first
        $abbreviations = $this->abbreviations;
        uksort($abbreviations, fn ($a, $b) => mb_strlen($b) <=> mb_strlen($a));

        // Build a single-pass combined regex pattern for all abbreviations
        $quotedAbbrs = array_map(fn ($abbr) => preg_quote($abbr, '/'), array_keys($abbreviations));
        $pattern = '/(?<![a-zA-Z0-9_\x{00C0}-\x{017F}\x{1E00}-\x{1EFF}])('.implode('|', $quotedAbbrs).')(?![a-zA-Z0-9_\x{00C0}-\x{017F}\x{1E00}-\x{1EFF}])/iu';

        $normalized = preg_replace_callback($pattern, function ($matches) use ($abbreviations) {
            $matchedLower = mb_strtolower($matches[0], 'UTF-8');

            return $abbreviations[$matchedLower] ?? $matches[0];
        }, $normalized);

        // Normalize cohort references dynamically using the catalog
        $cohortInfo = $this->cohortCatalog->detectCohort($normalized);
        if ($cohortInfo) {
            $boundary = '(?<![a-zA-Z0-9\x{00C0}-\x{017F}\x{1E00}-\x{1EFF}])';
            $boundaryEnd = '(?![a-zA-Z0-9\x{00C0}-\x{017F}\x{1E00}-\x{1EFF}])';
            $pattern = '/'.$boundary.preg_quote($cohortInfo['matched_alias'], '/').$boundaryEnd.'/iu';
            $normalized = preg_replace($pattern, $cohortInfo['canonical'], $normalized);
        } else {
            // Fallback math mapping
            $normalized = preg_replace_callback($this->cohortPattern, function ($matches) {
                $cohortNum = (int) $matches[2];
                $year = 1974 + $cohortNum;

                return "{$year} - Khóa {$cohortNum}";
            }, $normalized);
        }

        // Detected entities
        $detected = $this->detectEntities($normalized, $original);

        if ($detected['major'] && $detected['matched_alias']) {
            $boundary = '(?<![a-zA-Z0-9\x{00C0}-\x{017F}\x{1E00}-\x{1EFF}])';
            $boundaryEnd = '(?![a-zA-Z0-9\x{00C0}-\x{017F}\x{1E00}-\x{1EFF}])';
            $pattern = '/'.$boundary.preg_quote($detected['matched_alias'], '/').$boundaryEnd.'/iu';
            $normalized = preg_replace($pattern, $detected['major'], $normalized);
        }

        return [
            'original_question' => $original,
            'normalized_question' => $normalized,
            'detected_terms' => $detected,
        ];
    }

    /**
     * Extract entities from the normalized question.
     */
    private function detectEntities(string $normalized, string $original): array
    {
        $cohort = null;
        $cohortAlias = null;
        $major = null;
        $matchedAlias = null;
        $detectedMajorName = null;
        $faculty = null;
        $course = null;
        $policyTopic = null;

        // Detect cohort using CohortCatalogService
        $detectedCohort = $this->cohortCatalog->detectCohort($normalized);
        if (! $detectedCohort) {
            $detectedCohort = $this->cohortCatalog->detectCohort($original);
        }

        if ($detectedCohort) {
            $cohort = $detectedCohort['canonical'];
            $cohortAlias = $detectedCohort['matched_alias'];
        }

        // Detect major using MajorCatalogService
        [$major, $matchedAlias, $detectedMajorName] = $this->detectMajor($normalized, $original);

        // Extract faculty keywords
        $facultyKeywords = [
            'Công nghệ thông tin', 'Toán học', 'Vật lý', 'Hóa học',
            'Sinh học', 'Lịch sử', 'Địa lý', 'Ngữ văn', 'Tiếng Anh',
        ];
        foreach ($facultyKeywords as $keyword) {
            if (stripos($normalized, 'khoa '.$keyword) !== false) {
                $faculty = $keyword;
                break;
            }
        }

        // Detect policy topics
        $policyKeywords = [
            'điều kiện tốt nghiệp' => 'điều kiện tốt nghiệp',
            'cảnh báo học tập' => 'cảnh báo học tập',
            'học cải thiện' => 'học cải thiện',
            'học lại' => 'học lại',
            'tốt nghiệp' => 'tốt nghiệp',
            'học bổng' => 'học bổng',
            'rèn luyện' => 'rèn luyện',
            'đăng ký học phần' => 'đăng ký học phần',
            'hủy học phần' => 'hủy học phần',
            'miễn giảm' => 'miễn giảm',
            'quy chế' => 'quy chế đào tạo',
            'quy định' => 'quy định học vụ',
        ];
        foreach ($policyKeywords as $keyword => $topic) {
            if (mb_stripos($normalized, $keyword) !== false) {
                $policyTopic = $topic;
                break;
            }
        }

        // Detect course code mentions (e.g., COMP101, MATH201)
        if (preg_match('/\b([A-Z]{2,6}\d{3,})\b/i', $normalized, $m)) {
            $course = strtoupper($m[1]);
        }

        return [
            'cohort' => $cohort,
            'canonical_cohort' => $cohort,
            'detected_cohort' => $cohortAlias,
            'cohort_alias' => $cohortAlias,
            'major' => $major,
            'canonical_major' => $major,
            'detected_major' => $detectedMajorName,
            'matched_alias' => $matchedAlias,
            'faculty' => $faculty,
            'course' => $course,
            'policy_topic' => $policyTopic,
        ];
    }

    /**
     * Detect major by matching aliases against the normalized question.
     *
     * @return array{0: ?string, 1: ?string, 2: ?string} [canonical, matched_alias, detected_major]
     */
    private function detectMajor(string $normalized, string $original): array
    {
        $detected = $this->majorCatalog->detectMajor($normalized);
        if (! $detected) {
            $detected = $this->majorCatalog->detectMajor($original);
        }

        if ($detected) {
            return [$detected['canonical'], $detected['matched_alias'], $detected['detected_major']];
        }

        return [null, null, null];
    }

    /**
     * Check if $haystack contains $phrase as a whole token/phrase
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
}
