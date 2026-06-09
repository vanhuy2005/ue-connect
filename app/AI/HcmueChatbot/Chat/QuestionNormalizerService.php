<?php

namespace App\AI\HcmueChatbot\Chat;

class QuestionNormalizerService
{
    /**
     * Common abbreviation mappings used by students in Vietnamese.
     *
     * @var array<string, string>
     */
    private array $abbreviations = [
        'cntt' => 'Công nghệ thông tin',
        'ctdt' => 'chương trình đào tạo',
        'ctđt' => 'chương trình đào tạo',
        'tc' => 'tín chỉ',
        'hp' => 'học phần',
        'hk' => 'học kỳ',
        'sv' => 'sinh viên',
        'gv' => 'giảng viên',
        'pvdt' => 'Phòng Đào tạo',
        'sư phạm tin' => 'Sư phạm Tin học',
        'sptin' => 'Sư phạm Tin học',
        'sp toán' => 'Sư phạm Toán học',
        'sptoán' => 'Sư phạm Toán học',
        'sp văn' => 'Sư phạm Ngữ văn',
        'spvăn' => 'Sư phạm Ngữ văn',
        'ktpm' => 'Kỹ thuật phần mềm',
        'khmt' => 'Khoa học máy tính',
    ];

    /**
     * Cohort patterns — normalizes k51, K 51, khóa 51 → K51.
     */
    private string $cohortPattern = '/\b(?:k|khóa|khoa)\s*(\d{2})\b/iu';

    /**
     * Normalize a raw question and extract detected entities.
     *
     * @return array{
     *   original_question: string,
     *   normalized_question: string,
     *   detected_terms: array{
     *     cohort: ?string,
     *     major: ?string,
     *     faculty: ?string,
     *     course: ?string,
     *     policy_topic: ?string
     *   }
     * }
     */
    public function normalize(string $question): array
    {
        $original = $question;
        $normalized = $question;

        // Normalize whitespace
        $normalized = preg_replace('/\s+/', ' ', trim($normalized));

        // Expand abbreviations (word-boundary aware)
        foreach ($this->abbreviations as $abbr => $expansion) {
            $pattern = '/\b'.preg_quote($abbr, '/').'\b/iu';
            $normalized = preg_replace($pattern, $expansion, $normalized);
        }

        // Normalize cohort references: k51, khóa 51 → K51
        $normalized = preg_replace_callback($this->cohortPattern, function ($matches) {
            return 'K'.$matches[1];
        }, $normalized);

        // Detected entities
        $detected = $this->detectEntities($normalized, $original);

        return [
            'original_question' => $original,
            'normalized_question' => $normalized,
            'detected_terms' => $detected,
        ];
    }

    /**
     * Extract entities from the normalized question.
     *
     * @return array{cohort: ?string, major: ?string, faculty: ?string, course: ?string, policy_topic: ?string}
     */
    private function detectEntities(string $normalized, string $original): array
    {
        $cohort = null;
        $major = null;
        $faculty = null;
        $course = null;
        $policyTopic = null;

        // Extract cohort (K48, K49, K50, K51, ...)
        if (preg_match('/\bK(\d{2})\b/i', $normalized, $m)) {
            $cohort = 'K'.$m[1];
        }

        // Extract major keywords
        $majorKeywords = [
            'Công nghệ thông tin', 'Kỹ thuật phần mềm', 'Khoa học máy tính',
            'Sư phạm Tin học', 'Sư phạm Toán học', 'Sư phạm Ngữ văn',
            'Sư phạm Vật lý', 'Sư phạm Hóa học', 'Sư phạm Lịch sử',
            'Giáo dục học', 'Tâm lý học',
        ];
        foreach ($majorKeywords as $keyword) {
            if (mb_stripos($normalized, $keyword) !== false) {
                $major = $keyword;
                break;
            }
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
            'major' => $major,
            'faculty' => $faculty,
            'course' => $course,
            'policy_topic' => $policyTopic,
        ];
    }
}
