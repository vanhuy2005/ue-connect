<?php

namespace App\AI\Evidence\Services;

use App\AI\Evidence\DTO\ExtractedStudentCardFieldsData;

class StudentCardFieldExtractor
{
    /**
     * Known HCMUE school name aliases.
     *
     * @var list<string>
     */
    private const HCMUE_ALIASES = [
        'HCMUE',
        'ĐHSP TPHCM',
        'ĐH SƯ PHẠM TP',
        'Đại học Sư phạm TP',
        'Đại học Sư phạm Thành phố Hồ Chí Minh',
        'Ho Chi Minh City University of Education',
        'trường đại học sư phạm',
        'sư phạm tp.hcm',
        'sư phạm tp hcm',
    ];

    /**
     * Faculty alias mapping.
     *
     * @var array<string, string>
     */
    private const FACULTY_ALIASES = [
        'CNTT' => 'Công nghệ thông tin',
        'Công nghệ thông tin' => 'Công nghệ thông tin',
        'Khoa Công nghệ Thông tin' => 'Công nghệ thông tin',
        'Khoa CNTT' => 'Công nghệ thông tin',
        'Toán' => 'Toán học',
        'Khoa Toán' => 'Toán học',
        'Vật lý' => 'Vật lý',
        'Hóa' => 'Hóa học',
        'Sinh' => 'Sinh học',
        'Ngữ văn' => 'Ngữ văn',
        'Lịch sử' => 'Lịch sử',
        'Địa lý' => 'Địa lý',
        'Giáo dục thể chất' => 'Giáo dục thể chất',
        'Tâm lý' => 'Tâm lý học',
        'Tiếng Anh' => 'Tiếng Anh',
        'Tiếng Pháp' => 'Tiếng Pháp',
    ];

    public function extract(string $ocrText): ExtractedStudentCardFieldsData
    {
        $lines = $this->normalizeLines($ocrText);
        $fullText = implode(' ', $lines);

        return new ExtractedStudentCardFieldsData(
            fullName: $this->extractFullName($lines),
            studentCode: $this->extractStudentCode($fullText),
            faculty: $this->extractFaculty($fullText),
            academicProgram: null,
            cohort: $this->extractCohort($fullText),
            schoolName: $this->extractSchoolName($fullText),
            portraitPresentHint: null,
        );
    }

    /**
     * @return list<string>
     */
    private function normalizeLines(string $text): array
    {
        $lines = preg_split('/[\r\n]+/', $text) ?: [];

        return array_values(array_filter(
            array_map('trim', $lines),
            fn (string $line) => $line !== ''
        ));
    }

    private function extractStudentCode(string $text): ?string
    {
        // Standard MSSV patterns: 49.01.104.055 or 4901104055 or 49 01 104 055
        if (preg_match('/(?:MSSV|Mã số sinh viên|Student ID)[:\s]*([\d][\d.\s]{6,14}[\d])/ui', $text, $matches)) {
            return $this->normalizeStudentCode($matches[1]);
        }

        // Standalone pattern matching MSSV format
        if (preg_match('/\b(\d{2}[.\s]?\d{2}[.\s]?\d{3}[.\s]?\d{3})\b/', $text, $matches)) {
            return $this->normalizeStudentCode($matches[1]);
        }

        return null;
    }

    private function normalizeStudentCode(string $code): string
    {
        return preg_replace('/[.\s]/', '', $code) ?? $code;
    }

    private function extractSchoolName(string $text): ?string
    {
        $lower = mb_strtolower($text, 'UTF-8');

        foreach (self::HCMUE_ALIASES as $alias) {
            if (str_contains($lower, mb_strtolower($alias, 'UTF-8'))) {
                return 'Trường Đại học Sư phạm TP.HCM';
            }
        }

        return null;
    }

    private function extractFaculty(string $text): ?string
    {
        $lower = mb_strtolower($text, 'UTF-8');

        foreach (self::FACULTY_ALIASES as $alias => $canonical) {
            if (str_contains($lower, mb_strtolower($alias, 'UTF-8'))) {
                return $canonical;
            }
        }

        return null;
    }

    private function extractCohort(string $text): ?string
    {
        // K49, K 49, Khóa 49, khóa 49
        if (preg_match('/(?:Khóa|Khoa|K)[\s]*(\d{2})/ui', $text, $matches)) {
            return 'K'.$matches[1];
        }

        // Year range: 2023-2027, 2023 - 2027
        if (preg_match('/(\d{4})[\s]*[-–][\s]*(\d{4})/', $text, $matches)) {
            return $matches[1].'-'.$matches[2];
        }

        return null;
    }

    /**
     * @param  list<string>  $lines
     */
    private function extractFullName(array $lines): ?string
    {
        // Pass 1: Look for anchor "THẺ SINH VIÊN" and take the first valid subsequent line
        foreach ($lines as $i => $line) {
            if (str_contains(mb_strtolower($line, 'UTF-8'), 'thẻ sinh viên')) {
                for ($j = $i + 1; $j < min($i + 4, count($lines)); $j++) {
                    $nextLine = trim($lines[$j]);
                    if (preg_match('/^[\p{L}\s]+$/u', $nextLine) && mb_strlen($nextLine, 'UTF-8') >= 5) {
                        return mb_strtoupper($nextLine, 'UTF-8');
                    }
                }
            }
        }

        // Pass 2: Fallback logic with expanded skip keywords
        foreach ($lines as $line) {
            // Skip lines that are likely labels, codes, or school names
            if (preg_match('/\d{4,}/', $line)) {
                continue;
            }

            if (mb_strlen($line, 'UTF-8') < 5 || mb_strlen($line, 'UTF-8') > 60) {
                continue;
            }

            // Skip known label words
            $lower = mb_strtolower($line, 'UTF-8');
            $skipKeywords = [
                'khoa', 'ngành', 'khóa', 'trường', 'đại học', 'hcmue', 'mssv', 'student', 
                'university', 'faculty', 'thành phố', 'hồ chí minh', 'thẻ sinh viên', 
                'chất lượng', 'nhân văn', 'sáng tạo', 'education', 'sp', 'tp', 'city'
            ];
            
            $hasSkipKeyword = false;
            foreach ($skipKeywords as $keyword) {
                if (str_contains($lower, $keyword)) {
                    $hasSkipKeyword = true;
                    break;
                }
            }

            if ($hasSkipKeyword) {
                continue;
            }

            // Likely a name: mostly letters and spaces
            if (preg_match('/^[\p{L}\s]+$/u', $line) && str_word_count($line) >= 2) {
                return mb_strtoupper(mb_substr($line, 0, 1, 'UTF-8'), 'UTF-8').mb_substr($line, 1, null, 'UTF-8');
            }
        }

        return null;
    }
}
