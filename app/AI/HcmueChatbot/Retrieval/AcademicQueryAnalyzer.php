<?php

namespace App\AI\HcmueChatbot\Retrieval;

class AcademicQueryAnalyzer
{
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
     *   topics: array<string>
     * }
     */
    public function analyze(string $query): array
    {
        $queryLower = mb_strtolower($query, 'UTF-8');

        // 1. Detect Cohort & Academic Year
        $cohort = null;
        $academicYear = null;

        // Regex patterns for K51, K50, khóa 51, khoa 51 etc.
        if (preg_match('/k\s*(\d+)/i', $queryLower, $matches)) {
            $cohort = 'K'.$matches[1];
            // Calculate academic year: K51 = 2025 (K44 = 2018)
            // Year = 1974 + Cohort
            $cohortNum = (int) $matches[1];
            $academicYear = 1974 + $cohortNum;
        } elseif (preg_match('/kh[oó]a\s*(\d+)/ui', $queryLower, $matches)) {
            $cohort = 'K'.$matches[1];
            $cohortNum = (int) $matches[1];
            $academicYear = 1974 + $cohortNum;
        } elseif (preg_match('/năm tuyển sinh\s*(\d+)/ui', $queryLower, $matches)) {
            $academicYear = (int) $matches[1];
            $cohort = 'K'.($academicYear - 1974);
        } elseif (preg_match('/tuyển sinh\s*(\d{4})/ui', $queryLower, $matches)) {
            $academicYear = (int) $matches[1];
            $cohort = 'K'.($academicYear - 1974);
        } elseif (preg_match('/(20\d{2})/i', $queryLower, $matches)) {
            $academicYear = (int) $matches[1];
            $cohort = 'K'.($academicYear - 1974);
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

        $majors = [
            'công nghệ thông tin' => 'Công nghệ thông tin',
            'sư phạm tin' => 'Sư phạm Tin học',
            'công nghệ giáo dục' => 'Công nghệ giáo dục',
            'sư phạm toán' => 'Sư phạm Toán học',
            'toán học' => 'Toán học',
            'ngôn ngữ anh' => 'Ngôn ngữ Anh',
            'sư phạm tiếng anh' => 'Sư phạm Tiếng Anh',
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

        foreach ($majors as $key => $majorName) {
            if (str_contains($queryLower, $key)) {
                $major = $majorName;
                break;
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

        return [
            'cohort' => $cohort,
            'academic_year' => $academicYear,
            'faculty' => $faculty,
            'major' => $major,
            'document_type' => $documentType,
            'topics' => $topics,
        ];
    }
}
