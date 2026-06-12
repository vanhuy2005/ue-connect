<?php

namespace App\AI\HcmueChatbot\Retrieval;

use App\AI\HcmueChatbot\Chat\CohortCatalogService;
use App\AI\HcmueChatbot\Chat\CohortMajorCatalogService;
use App\AI\HcmueChatbot\Chat\MajorCatalogService;

class AcademicQueryAnalyzer
{
    protected ?MajorCatalogService $majorCatalog = null;

    protected ?CohortMajorCatalogService $cohortMajorCatalog = null;

    public function __construct(
        ?MajorCatalogService $majorCatalog = null,
        ?CohortCatalogService $cohortCatalog = null,
        ?CohortMajorCatalogService $cohortMajorCatalog = null
    ) {
        $this->majorCatalog = $majorCatalog;
        if (! $this->majorCatalog && function_exists('app')) {
            try {
                $this->majorCatalog = app(MajorCatalogService::class);
            } catch (\Exception $e) {
                // Ignore resolution error if not in container context
            }
        }

        $this->cohortCatalog = $cohortCatalog;
        if (! $this->cohortCatalog && function_exists('app')) {
            try {
                $this->cohortCatalog = app(CohortCatalogService::class);
            } catch (\Exception $e) {
                // Ignore resolution error if not in container context
            }
        }

        $this->cohortMajorCatalog = $cohortMajorCatalog;
        if (! $this->cohortMajorCatalog && function_exists('app')) {
            try {
                $this->cohortMajorCatalog = app(CohortMajorCatalogService::class);
            } catch (\Exception $e) {
                // Ignore resolution error if not in container context
            }
        }
    }

    /**
     * Analyze a search query to detect entities and topic.
     *
     * @param  string  $query  The user's query.
     * @return array{
     *   cohort: ?string,
     *   academic_year: ?int,
     *   faculty: ?string,
     *   major: ?string,
     *   document_type: string,
     *   intent: string,
     *   topics: array<string>
     * }
     */
    public function analyze(string $query): array
    {
        $queryLower = mb_strtolower($query, 'UTF-8');

        // 1. Detect Cohort & Academic Year
        $cohort = null;
        $academicYear = null;

        if ($this->cohortMajorCatalog) {
            $cohortInfo = $this->cohortMajorCatalog->detectCohort($query);
            if ($cohortInfo) {
                $cohort = $cohortInfo['canonical_cohort'];
                if (preg_match('/\b\d+\b/u', $cohort, $matches)) {
                    $cohortNum = (int) $matches[0];
                    $academicYear = 1974 + $cohortNum;
                }
            }
        }

        if (! $cohort) {
            // Regex patterns fallback for "2023 - Khóa 49", K51, K50, khóa 51, khoa 51 etc.
            if (preg_match('/(20\d{2})\s*-\s*kh[oó]a\s*(\d+)/ui', $queryLower, $matches)) {
                $cohortNum = (int) $matches[2];
                $academicYear = (int) $matches[1];
                $cohort = "{$academicYear} - Khóa {$cohortNum}";
            } elseif (preg_match('/k\s*(\d+)/i', $queryLower, $matches)) {
                $cohortNum = (int) $matches[1];
                $academicYear = 1974 + $cohortNum;
                $cohort = "{$academicYear} - Khóa {$cohortNum}";
            } elseif (preg_match('/kh[oó]a\s*(\d+)/ui', $queryLower, $matches)) {
                $cohortNum = (int) $matches[1];
                $academicYear = 1974 + $cohortNum;
                $cohort = "{$academicYear} - Khóa {$cohortNum}";
            } elseif (preg_match('/năm tuyển sinh\s*(\d+)/ui', $queryLower, $matches)) {
                $academicYear = (int) $matches[1];
                $cohortNum = $academicYear - 1974;
                $cohort = "{$academicYear} - Khóa {$cohortNum}";
            } elseif (preg_match('/tuyển sinh\s*(\d{4})/ui', $queryLower, $matches)) {
                $academicYear = (int) $matches[1];
                $cohortNum = $academicYear - 1974;
                $cohort = "{$academicYear} - Khóa {$cohortNum}";
            } elseif (preg_match('/(20\d{2})/i', $queryLower, $matches)) {
                $academicYear = (int) $matches[1];
                $cohortNum = $academicYear - 1974;
                $cohort = "{$academicYear} - Khóa {$cohortNum}";
            }
        }

        // 2. Detect Document Type
        $documentType = 'unknown';
        if (str_contains($queryLower, 'chuẩn đầu ra') || str_contains($queryLower, 'cdr') || str_contains($queryLower, 'learning outcome')) {
            $documentType = 'learning_outcome';
        } elseif (str_contains($queryLower, 'sổ tay') || str_contains($queryLower, 'student handbook') || str_contains($queryLower, 'sotaysinhvien')) {
            $documentType = 'student_handbook';
        } elseif (str_contains($queryLower, 'quy chế') || str_contains($queryLower, 'quy định') || str_contains($queryLower, 'quyche') || str_contains($queryLower, 'quydinh') || str_contains($queryLower, 'học vụ')) {
            $documentType = 'academic_regulation';
        } elseif (str_contains($queryLower, 'chương trình đào tạo') || str_contains($queryLower, 'chương trình khung') || str_contains($queryLower, 'ctđt') || str_contains($queryLower, 'ctdt') || str_contains($queryLower, 'môn học') || str_contains($queryLower, 'học phần') || str_contains($queryLower, 'tín chỉ')) {
            $documentType = 'training_program';
        }

        // 3. Detect Major / Faculty keywords (Fuzzy/Keyword matching)
        $major = null;
        $faculty = null;

        $faculties = [
            'công nghệ thông tin' => 'Khoa Công nghệ thông tin',
            'toán' => 'Khoa Toán - Tin',
            'tin học' => 'Khoa Công nghệ thông tin',
            'vật lý' => 'Khoa Vật lý',
            'hóa học' => 'Khoa Hóa học',
            'sinh học' => 'Khoa Sinh học',
            'ngữ văn' => 'Khoa Ngữ văn',
            'lịch sử' => 'Khoa Lịch sử',
            'địa lý' => 'Khoa Địa lý',
            'tiếng anh' => 'Khoa Tiếng Anh',
            'tiếng pháp' => 'Khoa Tiếng Pháp',
            'tiếng trung' => 'Khoa Tiếng Trung',
            'tiếng hàn' => 'Khoa Tiếng Hàn Quốc',
            'tiếng nhật' => 'Khoa Tiếng Nhật',
            'tiếng nga' => 'Khoa Tiếng Nga',
            'tâm lý' => 'Khoa Tâm lý học',
            'mầm non' => 'Giáo dục Mầm non',
            'tiểu học' => 'Giáo dục Tiểu học',
            'đặc biệt' => 'Giáo dục đặc biệt',
            'thể chất' => 'Giáo dục thể chất',
            'quốc phòng' => 'Giáo dục Quốc phòng',
            'chính trị' => 'Giáo dục chính trị',
        ];

        foreach ($faculties as $key => $facName) {
            if (str_contains($queryLower, $key)) {
                $faculty = $facName;
                break;
            }
        }

        if ($this->cohortMajorCatalog) {
            $detected = $this->cohortMajorCatalog->detectMajor($query);
            if ($detected) {
                $major = $detected['canonical_major'];
            }
        } else {
            $majors = [
                'công nghệ thông tin' => 'Công nghệ thông tin',
                'sư phạm tin học' => 'Sư phạm Tin học',
                'sư phạm tin' => 'Sư phạm Tin học',
                'công nghệ giáo dục' => 'Công nghệ giáo dục',
                'sư phạm toán học' => 'Sư phạm Toán học',
                'sư phạm toán' => 'Sư phạm Toán học',
                'toán học' => 'Toán học',
                'ngôn ngữ anh' => 'Ngôn ngữ Anh',
                'sư phạm tiếng anh' => 'Sư phạm Tiếng Anh',
                'sư phạm ngữ văn' => 'Sư phạm Ngữ văn',
                'sư phạm văn' => 'Sư phạm Ngữ văn',
                'ngữ văn' => 'Ngữ văn',
                'âm nhạc' => 'Sư phạm Âm nhạc',
                'mỹ thuật' => 'Sư phạm Mỹ thuật',
                'giáo dục mầm non' => 'Giáo dục Mầm non',
                'giáo dục tiểu học' => 'Giáo dục Tiểu học',
                'giáo dục đặc biệt' => 'Giáo dục đặc biệt',
                'quản lý giáo dục' => 'Quản lý giáo dục',
                'tâm lý học giáo dục' => 'Tâm lý học giáo dục',
                'tâm lý học' => 'Tâm lý học',
                'tiếng trung' => 'Ngôn ngữ Trung Quốc',
                'tiếng hàn' => 'Ngôn ngữ Hàn Quốc',
                'tiếng nhật' => 'Ngôn ngữ Nhật Bản',
                'địa lý học' => 'Địa lý học',
                'lịch sử' => 'Lịch sử',
            ];

            // Sort majors by length descending to match longest first
            uksort($majors, fn ($a, $b) => mb_strlen($b) <=> mb_strlen($a));

            foreach ($majors as $key => $majorName) {
                if (str_contains($queryLower, $key)) {
                    $major = $majorName;
                    break;
                }
            }
        }

        // 4. Detect Topics
        $topics = [];
        $topicKeywords = [
            'tín chỉ' => ['tín chỉ', 'tc', 'credits'],
            'học kỳ' => ['học kỳ', 'hk', 'semester'],
            'chuẩn đầu ra' => ['chuẩn đầu ra', 'cdr', 'learning outcomes'],
            'điều kiện tốt nghiệp' => ['tốt nghiệp', 'ra trường', 'graduat'],
            'học phần bắt buộc' => ['bắt buộc', 'compulsory', 'required'],
            'học phần tự chọn' => ['tự chọn', 'elective'],
            'học lại' => ['học lại', 'cải thiện', 'thi lại'],
            'cảnh báo học tập' => ['cảnh báo', 'buộc thôi học', 'hạ điểm', 'bị đuổi', 'đình chỉ'],
        ];

        foreach ($topicKeywords as $topicName => $keywords) {
            foreach ($keywords as $kw) {
                if (str_contains($queryLower, $kw)) {
                    $topics[] = $topicName;
                    break;
                }
            }
        }

        // 5. Detect high-level intent
        $intent = 'general';

        // Check if query is about "quyết định ban hành" / "quyết định" etc.
        $decisionKeywords = [
            'quyết định ban hành',
            'quyết định',
            'văn bản ban hành',
            'quy định ban hành',
            'số quyết định',
            'ban hành quy định',
            'tổ chức hoạt động nghiên cứu khoa học',
            'nghiên cứu khoa học của sinh viên',
        ];

        $isDecisionQuery = false;
        foreach ($decisionKeywords as $kw) {
            if (str_contains($queryLower, $kw)) {
                $intent = 'decision_document_query';
                $isDecisionQuery = true;
                break;
            }
        }

        if (! $isDecisionQuery) {
            // Check if query is about "chuẩn đầu ra ngành [X]"
            $isMajorOutcomeQuery = false;
            if (str_contains($queryLower, 'chuẩn đầu ra') || str_contains($queryLower, 'cdr') || str_contains($queryLower, 'learning outcome')) {
                if (str_contains($queryLower, 'ngành') || $major !== null) {
                    $isMajorOutcomeQuery = true;
                }
            }

            $studentPolicySignals = [
                'hạ bằng',
                'hạ xếp loại',
                'xếp loại tốt nghiệp',
                'học lại',
                'học cải thiện',
                '5% số tín chỉ',
                '5% tín chỉ',
                'cảnh báo học vụ',
                'buộc thôi học',
                'thôi học',
                'đình chỉ',
                'bảo lưu',
                'nghỉ học tạm thời',
                'quy chế',
                'học phí',
                'miễn giảm học phí',
                'học bổng',
                'điểm rèn luyện',
                'kỷ luật',
                'chuẩn đầu ra ngoại ngữ',
                'chuẩn đầu ra tin học',
            ];

            if (! $isMajorOutcomeQuery) {
                $studentPolicySignals[] = 'chuẩn đầu ra';
                $studentPolicySignals[] = 'điều kiện tốt nghiệp';
            }

            $isStudentPolicy = false;
            foreach ($studentPolicySignals as $signal) {
                if (str_contains($queryLower, $signal)) {
                    $intent = 'student_policy';
                    $isStudentPolicy = true;
                    break;
                }
            }

            if (! $isStudentPolicy) {
                $totalCreditsSignals = [
                    'bao nhiêu tín chỉ',
                    'tín chỉ để ra trường',
                    'tín chỉ tốt nghiệp',
                    'tổng số tín chỉ',
                    'tổng tín chỉ toàn khóa',
                    'tổng tín chỉ',
                    'cần tích lũy',
                    'điều kiện tốt nghiệp tín chỉ',
                ];
                foreach ($totalCreditsSignals as $signal) {
                    if (str_contains($queryLower, $signal)) {
                        $intent = 'total_credits';
                        break;
                    }
                }

                if ($intent === 'general') {
                    $curriculumSignals = [
                        'môn', 'học phần', 'subject', 'course', 'mã học phần', 'tiên quyết',
                        'song hành', 'nâng cao', 'cơ sở ngành', 'chuyên ngành', 'chương trình khung',
                        'ctdt', 'ctkh', 'học kỳ', 'học kì', 'semester', 'kì', 'hk',
                    ];
                    foreach ($curriculumSignals as $sig) {
                        if (str_contains($queryLower, $sig)) {
                            $intent = 'curriculum_course_lookup';
                            break;
                        }
                    }
                }
            }
        }

        // Detect semester
        $semester = null;
        if (preg_match_all('/(?:học\s+kỳ|học\s+kì|kì|hk)\s*(\d+)/ui', $queryLower, $matches)) {
            $semVal = (int) end($matches[1]);
            if ($semVal >= 1 && $semVal <= 10) {
                $semester = $semVal;
            }
        }

        // Detect course name
        $courseName = $this->detectCourseName($queryLower, $major, $cohort, $semester);

        return [
            'cohort' => $cohort,
            'academic_year' => $academicYear,
            'faculty' => $faculty,
            'major' => $major,
            'document_type' => $documentType,
            'intent' => $intent,
            'topics' => $topics,
            'semester' => $semester,
            'course_name' => $courseName,
        ];
    }

    /**
     * Detect course name from query by removing cohort, major, semester and common prefix/suffix words.
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
}
