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
        protected CohortCatalogService $cohortCatalog,
        protected ?CohortMajorCatalogService $cohortMajorCatalog = null
    ) {
        $this->cohortMajorCatalog = $cohortMajorCatalog ?? app(CohortMajorCatalogService::class);
    }

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
        $cohortInfo = $this->cohortMajorCatalog->detectCohort($normalized);
        if ($cohortInfo) {
            $boundary = '(?<![a-zA-Z0-9\x{00C0}-\x{017F}\x{1E00}-\x{1EFF}])';
            $boundaryEnd = '(?![a-zA-Z0-9\x{00C0}-\x{017F}\x{1E00}-\x{1EFF}])';
            $pattern = '/'.$boundary.preg_quote($cohortInfo['cohort_alias'] ?? $cohortInfo['detected_cohort'] ?? '', '/').$boundaryEnd.'/iu';
            $normalized = preg_replace($pattern, $cohortInfo['canonical_cohort'], $normalized);
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

        // Detect cohort using CohortMajorCatalogService
        $detectedCohort = $this->cohortMajorCatalog->detectCohort($normalized);
        if (! $detectedCohort) {
            $detectedCohort = $this->cohortMajorCatalog->detectCohort($original);
        }

        if ($detectedCohort) {
            $cohort = $detectedCohort['canonical_cohort'];
            $cohortAlias = $detectedCohort['cohort_alias'] ?? $detectedCohort['detected_cohort'] ?? null;
        }

        // Detect major using MajorCatalogService
        [$major, $matchedAlias, $detectedMajorName] = $this->detectMajor($normalized, $original);

        // Detect semester
        $semester = null;
        if (preg_match_all('/(?:học\s+kỳ|học\s+kì|kì|hk)\s*(\d+)/ui', $normalized, $matches)) {
            $semVal = (int) end($matches[1]);
            if ($semVal >= 1 && $semVal <= 10) {
                $semester = $semVal;
            }
        } elseif (preg_match_all('/(?:học\s+kỳ|học\s+kì|kì|hk)\s*(\d+)/ui', $original, $matches)) {
            $semVal = (int) end($matches[1]);
            if ($semVal >= 1 && $semVal <= 10) {
                $semester = $semVal;
            }
        }

        // Detect course name
        $courseName = $this->detectCourseName($normalized, $major, $cohort, $semester);
        if (! $courseName) {
            $courseName = $this->detectCourseName($original, $major, $cohort, $semester);
        }

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
            'semester' => $semester,
            'course_name' => $courseName,
        ];
    }

    /**
     * Detect course name from raw query by removing cohort, major, semester and common prefix/suffix words.
     */
    private function detectCourseName(string $query, ?string $major, ?string $cohort, ?int $semester): ?string
    {
        $candidate = null;

        // 1. Suffix-based matching (e.g., "là gì")
        if (preg_match('/^(.+?)\s+(?:là gì|là môn gì|là học phần gì|là học phần nào|là môn nào|như thế nào|nhu the nao|la gi)/ui', $query, $matches)) {
            $candidate = trim($matches[1]);
        }
        // 2. Prefix-based matching (excluding suffix-like qualifiers like "môn gì", "môn nào")
        elseif (preg_match('/(?:môn học|học phần|môn|subject|course|mã học phần)\s+(?!gì\b|nào\b)([^,\.\?]+)/ui', $query, $matches)) {
            $candidate = trim($matches[1]);
        }

        if (! $candidate) {
            return null;
        }

        // Clean up candidate: remove major, cohort, semester terms
        $cleaned = $candidate;

        if ($major) {
            $majorPattern = '/(?:ngành|nganh)?\s*'.preg_quote($major, '/').'/ui';
            $cleaned = preg_replace($majorPattern, '', $cleaned);

            // Remove common major aliases
            $cleaned = preg_replace('/(?:ngành|nganh)?\s*(cntt|it|gdth|gdmn|sp toán|sp lý|sp hóa|sp anh|sp văn)/ui', '', $cleaned);
        }

        if ($cohort) {
            $cohortPattern = '/(?:khóa|khoá|k)?\s*'.preg_quote($cohort, '/').'/ui';
            $cleaned = preg_replace($cohortPattern, '', $cleaned);
            $cleaned = preg_replace('/\bk\d{2}\b/ui', '', $cleaned);
        }

        // Remove semester terms
        $cleaned = preg_replace('/(?:học\s+kỳ|học\s+kì|kì|hk)\s*\d+/ui', '', $cleaned);

        // Remove conjunctions and action verbs at boundaries (trim first to align boundary anchors)
        $cleaned = trim($cleaned);
        $cleaned = preg_replace('/^(?:môn học|học phần|môn|subject|course|mã học phần|của|ở|học|cho|xem|về|trong|tại|với|như|các|những|để)\s+/ui', '', $cleaned);
        $cleaned = preg_replace('/\s+(?:môn học|học phần|môn|subject|course|mã học phần|của|ở|học|cho|xem|về|trong|tại|với|như|các|những|để)$/ui', '', $cleaned);

        $cleaned = trim($cleaned, " \t\n\r\0\x0B,.-?_");

        if (empty($cleaned)) {
            return null;
        }

        // Skip if too short or matches common stop words
        $lowerClean = mb_strtolower($cleaned, 'UTF-8');
        $ignored = ['học', 'thi', 'xem', 'các', 'những', 'danh sách', 'thông tin', 'môn', 'học phần', 'nào', 'gì'];
        if (mb_strlen($cleaned, 'UTF-8') <= 2 || in_array($lowerClean, $ignored, true)) {
            return null;
        }

        return $cleaned;
    }

    /**
     * Detect major by matching aliases against the normalized question.
     *
     * @return array{0: ?string, 1: ?string, 2: ?string} [canonical, matched_alias, detected_major]
     */
    private function detectMajor(string $normalized, string $original): array
    {
        $detected = $this->cohortMajorCatalog->detectMajor($normalized);
        if (! $detected) {
            $detected = $this->cohortMajorCatalog->detectMajor($original);
        }

        if ($detected) {
            return [$detected['canonical_major'], $detected['matched_alias'], $detected['matched_alias']];
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
