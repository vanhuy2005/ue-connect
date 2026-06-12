<?php

namespace App\AI\HcmueChatbot\Ingestion;

use Illuminate\Support\Facades\File;

class AcademicMetadataExtractor
{
    /**
     * Extract academic metadata from a file path.
     *
     * @param  string  $filePath  Absolute or relative path to the file.
     * @return array{
     *   document_type: string,
     *   cohort: ?string,
     *   academic_year: ?int,
     *   faculty: ?string,
     *   major: ?string,
     *   normalized_major: ?string,
     *   program_level: string,
     *   title: string,
     *   confidence: float,
     *   evidence: array
     * }
     */
    public function extract(string $filePath): array
    {
        $normalizedPath = str_replace('\\', '/', $filePath);
        $filename = basename($filePath);
        $filenameLower = mb_strtolower($filename, 'UTF-8');

        // Defaults
        $documentType = 'unknown';
        $cohort = null;
        $academicYear = null;
        $faculty = null;
        $major = null;
        $programLevel = 'undergraduate';
        $title = pathinfo($filename, PATHINFO_FILENAME);
        $confidence = 0.5; // Base confidence for path/filename match
        $evidence = [];

        // 1. Try to read first page text for high-confidence metadata
        $firstPageText = $this->getFirstPageText($filePath);
        $firstPageTextLower = mb_strtolower($firstPageText, 'UTF-8');

        // 2. Scan metadata.json from directory structure
        $metadataJson = null;
        $currentDir = dirname($filePath);
        for ($i = 0; $i < 4; $i++) {
            $metaPath = $currentDir.'/metadata.json';
            if (File::exists($metaPath)) {
                $metadataJson = json_decode(File::get($metaPath), true);
                break;
            }
            $currentDir = dirname($currentDir);
        }

        if ($metadataJson) {
            $major = $metadataJson['ten_nganh'] ?? $metadataJson['ten_chuong_trinh'] ?? null;
            $faculty = $metadataJson['khoa'] ?? null;
            $confidence = 0.8;
            $evidence[] = "Found metadata.json in parent directory: Major='{$major}', Faculty='{$faculty}'";

            if (! empty($metadataJson['nam_tuyen_sinh'])) {
                $cohortStr = $metadataJson['nam_tuyen_sinh'];
                if (preg_match('/(20\d{2})\s*-\s*Kh[oó]a\s*(\d+)/ui', $cohortStr, $matches)) {
                    $academicYear = (int) $matches[1];
                    $cohort = 'K'.$matches[2];
                    $evidence[] = "Cohort matched from metadata.json 'nam_tuyen_sinh': {$cohort}";
                }
            }
            if (empty($academicYear) && ! empty($metadataJson['nam_ban_hanh'])) {
                $academicYear = (int) $metadataJson['nam_ban_hanh'];
            }
        }

        // 3. Document Type Identification (First Page Text > Filename > Path)
        if (str_contains($firstPageTextLower, 'sổ tay sinh viên') || str_contains($filenameLower, 'sổ tay sinh viên') || str_contains($filenameLower, 'sotaysinhvien') || str_contains($normalizedPath, 'Sotaysinhvien')) {
            $documentType = 'student_handbook';
            $evidence[] = 'Matched student_handbook';
        } elseif (str_contains($firstPageTextLower, 'chương trình khung') || str_contains($firstPageTextLower, 'chương trình đào tạo') || str_contains($filenameLower, 'ctk') || str_contains($filenameLower, 'khung') || str_contains($filenameLower, 'chương trình khung') || str_contains($filenameLower, 'chuongtrinhkhung') || str_contains($normalizedPath, '/Chuongtrinhkhung/')) {
            $documentType = 'training_program';
            $evidence[] = 'Matched training_program';
        } elseif (str_contains($firstPageTextLower, 'chuẩn đầu ra') || str_contains($filenameLower, 'cdr') || str_contains($filenameLower, 'cđr') || str_contains($filenameLower, 'chuẩn đầu ra') || str_contains($filenameLower, 'chuandaura') || str_contains($normalizedPath, '/Chuandaura/')) {
            $documentType = 'learning_outcome';
            $evidence[] = 'Matched learning_outcome';
        } elseif (str_contains($firstPageTextLower, 'quy chế') || str_contains($firstPageTextLower, 'quy định') || str_contains($firstPageTextLower, 'học vụ') || str_contains($filenameLower, 'quy chế') || str_contains($filenameLower, 'quy định') || str_contains($filenameLower, 'quyche') || str_contains($filenameLower, 'quydinh') || str_contains($filenameLower, 'học vụ')) {
            $documentType = 'academic_regulation';
            $evidence[] = 'Matched academic_regulation';
        }

        // 4. Cohort Identification (First Page Text > Path > Filename)
        $cohortRegex = '/\b(?:khóa|khoá|k)\s*(\d{2})\b/ui';
        if (preg_match($cohortRegex, $firstPageText, $matches)) {
            $cohort = 'K'.$matches[1];
            $confidence = max($confidence, 0.85);
            $evidence[] = "Matched cohort '{$cohort}' from first page text";
        } elseif (preg_match('/(20\d{2})\s*-\s*Kh[oó]a\s*(\d+)/ui', $normalizedPath, $matches)) {
            if (empty($cohort)) {
                $cohort = 'K'.$matches[2];
                $evidence[] = "Matched cohort '{$cohort}' from folder path";
            }
            if (empty($academicYear)) {
                $academicYear = (int) $matches[1];
            }
        } elseif (preg_match($cohortRegex, $filename, $matches)) {
            if (empty($cohort)) {
                $cohort = 'K'.$matches[1];
                $evidence[] = "Matched cohort '{$cohort}' from filename";
            }
        }

        // Guess academic year from cohort if not present: K49 -> 2023, K50 -> 2024, K51 -> 2025
        if (empty($academicYear) && $cohort) {
            $cohortNum = (int) substr($cohort, 1);
            $academicYear = 1974 + $cohortNum;
            $evidence[] = "Calculated academic year '{$academicYear}' from cohort '{$cohort}'";
        }

        // 5. Faculty & Major Identification from path or first page text
        if (preg_match('/\/Khoa\/([^\/]+)\/Ngành\/([^\/]+)/ui', $normalizedPath, $matches)) {
            if (empty($faculty)) {
                $faculty = trim($matches[1]);
                $evidence[] = "Matched faculty '{$faculty}' from folder path";
            }
            if (empty($major)) {
                $major = trim($matches[2]);
                $evidence[] = "Matched major '{$major}' from folder path";
            }
        }

        // If major / faculty empty, check text matching
        $majorKeywords = [
            'Công nghệ thông tin' => ['công nghệ thông tin', 'cntt'],
            'Sư phạm Tin học' => ['sư phạm tin học', 'sp tin', 'sptin'],
            'Sư phạm Toán học' => ['sư phạm toán học', 'sp toán', 'sptoán'],
            'Sư phạm Ngữ văn' => ['sư phạm ngữ văn', 'sp văn', 'spvăn'],
            'Kỹ thuật phần mềm' => ['kỹ thuật phần mềm', 'ktpm'],
            'Khoa học máy tính' => ['khoa học máy tính', 'khmt'],
            'Quản lý giáo dục' => ['quản lý giáo dục'],
            'Quốc tế học' => ['quốc tế học'],
            'Văn học' => ['văn học'],
            'Tiếng Việt và văn hóa Việt Nam' => ['tiếng việt và văn hóa việt nam', 'tiếng việt và văn hoá việt nam'],
            'Sư phạm Sinh học' => ['sư phạm sinh học', 'sp sinh'],
            'Sư phạm Tiếng Anh' => ['sư phạm tiếng anh', 'sp tiếng anh'],
            'Tiếng Anh biên phiên dịch' => ['tiếng anh biên phiên dịch'],
            'Tiếng Anh thương mại' => ['tiếng anh thương mại'],
            'Biên phiên dịch' => ['biên phiên dịch'],
            'Ngôn ngữ Nga' => ['ngôn ngữ nga'],
            'Sư phạm tiếng Nga' => ['sư phạm tiếng nga'],
            'Ngôn ngữ Nhật' => ['ngôn ngữ nhật'],
            'Ngôn ngữ Pháp - Biên phiên dịch' => ['ngôn ngữ pháp - biên phiên dịch'],
            'Ngôn ngữ Pháp - Du lịch' => ['ngôn ngữ pháp - du lịch'],
            'Sư phạm Tiếng Pháp' => ['sư phạm tiếng pháp'],
            'Ngôn ngữ Trung Quốc' => ['ngôn ngữ trung quốc'],
            'Sư phạm Tiếng Trung Quốc' => ['sư phạm tiếng trung quốc'],
        ];

        if (empty($major)) {
            foreach ($majorKeywords as $stdName => $aliases) {
                foreach ($aliases as $alias) {
                    if (mb_stripos($filenameLower, $alias) !== false || mb_stripos($firstPageTextLower, $alias) !== false) {
                        $major = $stdName;
                        $confidence = max($confidence, 0.90);
                        $evidence[] = "Matched major standard name '{$stdName}' using alias '{$alias}' in text/filename";
                        break 2;
                    }
                }
            }
        } else {
            // Standardize major name if matches key
            foreach ($majorKeywords as $stdName => $aliases) {
                if (mb_strtolower($major) === mb_strtolower($stdName)) {
                    $major = $stdName;
                    break;
                }
                foreach ($aliases as $alias) {
                    if (mb_strtolower($major) === mb_strtolower($alias)) {
                        $major = $stdName;
                        break 2;
                    }
                }
            }
        }

        // Standardize Faculty
        if ($faculty && ! str_starts_with(mb_strtolower($faculty, 'UTF-8'), 'khoa')) {
            $faculty = 'Khoa '.$faculty;
        }

        // Clean & Normalize major
        $normalizedMajor = $major ? $this->normalizeText($major) : null;

        // If first page text check succeeded and matched details, boost confidence
        if (! empty($firstPageText)) {
            $confidence = min(1.0, $confidence + 0.15);
        }

        return [
            'document_type' => $documentType,
            'cohort' => $cohort,
            'academic_year' => $academicYear,
            'faculty' => $faculty,
            'major' => $major,
            'normalized_major' => $normalizedMajor,
            'program_level' => $programLevel,
            'title' => $title,
            'confidence' => $confidence,
            'evidence' => $evidence,
        ];
    }

    /**
     * Fast page-1 PDF text extractor.
     */
    protected function getFirstPageText(string $filePath): string
    {
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        if ($extension !== 'pdf' || ! file_exists($filePath)) {
            return '';
        }

        $tempFile = null;
        $workPath = $filePath;
        $hasUnicode = preg_match('/[^\x00-\x7F]/', $filePath);
        $isWindows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';

        if ($hasUnicode && $isWindows) {
            $tempDir = sys_get_temp_dir();
            $tempFile = $tempDir.DIRECTORY_SEPARATOR.'hcmue_meta_tmp_'.md5($filePath).'.pdf';
            if (copy($filePath, $tempFile)) {
                $workPath = $tempFile;
            }
        }

        $escapedPath = escapeshellarg($workPath);
        $command = "pdftotext -f 1 -l 1 -enc UTF-8 {$escapedPath} -";
        $output = shell_exec($command);

        if ($tempFile && file_exists($tempFile)) {
            @unlink($tempFile);
        }

        if ($output === null) {
            return '';
        }

        return mb_convert_encoding($output, 'UTF-8', 'UTF-8');
    }

    /**
     * Accents removal helper.
     */
    protected function normalizeText(?string $text): ?string
    {
        if (! $text) {
            return null;
        }

        $text = mb_strtolower($text, 'UTF-8');

        $unicode = [
            'a' => 'á|à|ả|ã|ạ|ă|ắ|ằ|ẳ|ẵ|ặ|â|ấ|ầ|ẩ|ẫ|ậ',
            'd' => 'đ',
            'e' => 'é|è|ẻ|ẽ|ẹ|ê|ế|ề|ể|ễ|ệ',
            'i' => 'í|ì|ỉ|ĩ|ị',
            'o' => 'ó|ò|ỏ|õ|ọ|ô|ố|ồ|ổ|ỗ|ộ|ơ|ớ|ờ|ở|ỡ|ợ',
            'u' => 'ú|ù|ủ|ũ|ụ|ư|ứ|ừ|ử|ữ|ự',
            'y' => 'ý|ỳ|ỷ|ỹ|ỵ',
        ];

        foreach ($unicode as $nonUnicode => $unicodePattern) {
            $text = preg_replace("/($unicodePattern)/i", $nonUnicode, $text);
        }

        return trim(preg_replace('/\s+/', ' ', $text));
    }
}
