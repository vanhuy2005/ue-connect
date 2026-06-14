<?php

namespace App\Services\CareerPathway;

use App\DTOs\CareerPathway\CourseDTO;
use App\DTOs\CareerPathway\ProgramDTO;
use App\DTOs\CareerPathway\SemesterDTO;
use App\Enums\DataQualityIssueType;
use App\Enums\ProgramStatus;

class MarkdownParserService
{
    /**
     * Parses the roadmap.md content into a ProgramDTO.
     */
    public function parse(string $content, string $cohortName, string $facultyName, string $majorName, string $originalDir, string $filePath): ProgramDTO
    {
        $programDTO = new ProgramDTO($cohortName, $facultyName, $majorName, $originalDir, $filePath);

        if (empty(trim($content))) {
            $programDTO->status = ProgramStatus::EMPTY_EXTRACTION;
            $programDTO->dataQualityIssues[] = DataQualityIssueType::EMPTY_MARKDOWN;

            return $programDTO;
        }

        $programDTO->semesters = $this->parseSemesters($content, $programDTO);
        $this->parseCourseDescriptions($content, $programDTO);

        $this->evaluateDataQuality($programDTO);

        return $programDTO;
    }

    private function parseSemesters(string $content, ProgramDTO $programDTO): array
    {
        $semesters = [];
        // Split by "## Học kỳ X"
        $semesterBlocks = preg_split('/^##\s+(Học kỳ\s+\d+|Học kỳ\s+[IVX]+|Semester\s+\d+)/mi', $content, -1, PREG_SPLIT_DELIM_CAPTURE);

        for ($i = 1; $i < count($semesterBlocks); $i += 2) {
            $title = trim($semesterBlocks[$i]);
            $blockContent = $semesterBlocks[$i + 1] ?? '';

            preg_match('/\d+/', $title, $matches);
            $semesterNumber = isset($matches[0]) ? (int) $matches[0] : (count($semesters) + 1);

            if (! isset($matches[0]) && preg_match('/(?:I|V|X)+/i', $title, $romanMatches)) {
                $semesterNumber = $this->romanToInt(strtoupper($romanMatches[0]));
            }

            $courses = $this->parseCoursesFromList($blockContent, $programDTO);
            $semesters[] = new SemesterDTO($semesterNumber, $title, $courses);
        }

        if (count($semesters) === 0) {
            $courses = $this->parseCoursesFromList($content, $programDTO);
            if (count($courses) > 0) {
                $semesters[] = new SemesterDTO(0, 'Học kỳ 0', $courses);
            }
        }

        return $semesters;
    }

    private function parseCoursesFromList(string $blockContent, ProgramDTO $programDTO): array
    {
        $courses = [];

        // Split block by course heading ###
        $courseBlocks = preg_split('/^###\s+(.*)$/mi', $blockContent, -1, PREG_SPLIT_DELIM_CAPTURE);

        for ($i = 1; $i < count($courseBlocks); $i += 2) {
            $heading = trim($courseBlocks[$i]);
            $details = $courseBlocks[$i + 1] ?? '';

            // Extract Code and Name from Heading, e.g. "K. CNTT COMP1069 Công nghệ phần mềm nâng cao"
            // Usually Code is [A-Z]{3,4}\d{4}
            $code = '';
            $name = $heading;
            if (preg_match('/([A-Z]{3,4}\d{4})/', $heading, $codeMatches)) {
                $code = $codeMatches[1];
                // Remove code and "K. CNTT" prefix if any
                $name = trim(str_replace($code, '', $heading));
                $name = preg_replace('/^K\.\s*[A-Z]+\s*/i', '', $name);
            }

            // Extract from details
            $realCode = $code;
            if (preg_match('/-\s*\*\*Mã học phần:\*\*\s*`?([A-Z0-9]+)`?/i', $details, $codeMatch)) {
                $realCode = trim($codeMatch[1]);
            }
            if (empty($realCode)) {
                continue;
            } // Not a valid course block

            $credits = 0;
            if (preg_match('/-\s*\*\*Số tín chỉ:\*\*\s*(\d+)/i', $details, $creditMatch)) {
                $credits = (int) $creditMatch[1];
            }

            $isMandatory = true;
            if (preg_match('/-\s*\*\*Loại:\*\*\s*(.*)/i', $details, $typeMatch)) {
                $typeStr = mb_strtolower(trim($typeMatch[1]));
                if (str_contains($typeStr, 'tự chọn')) {
                    $isMandatory = false;
                }
            }

            $knowledgeBlock = null;
            if (preg_match('/-\s*\*\*Nhóm học phần:\*\*\s*(.*)/i', $details, $kbMatch)) {
                $knowledgeBlock = trim($kbMatch[1]);
                if ($knowledgeBlock === 'Không rõ') {
                    $knowledgeBlock = null;
                }
            }

            $description = null;
            if (preg_match('/\*\*Mô tả học phần:\*\*\s*(.*?)(?=\n- \*\*|$)/msi', $details, $descMatch)) {
                $descRaw = trim($descMatch[1]);
                if ($descRaw !== 'Chưa trích xuất được từ PDF.') {
                    $description = $descRaw;
                }
            }

            if (! empty($realCode) && ! empty($name)) {
                $course = new CourseDTO($realCode, $name, $credits, $isMandatory, $knowledgeBlock);
                if ($description) {
                    $course->description = $description;
                }
                $courses[] = $course;
                $programDTO->totalCredits += $credits;
            }
        }

        return $courses;
    }

    private function parseCourseDescriptions(string $content, ProgramDTO $programDTO): void
    {
        // No-op because descriptions are now parsed inline inside parseCoursesFromList
    }

    private function evaluateDataQuality(ProgramDTO $programDTO): void
    {
        $programDTO->totalSemesters = count($programDTO->semesters);

        if ($programDTO->totalSemesters === 1 && $programDTO->semesters[0]->semesterNumber === 0) {
            $programDTO->status = ProgramStatus::UNRESOLVED_SEMESTER_STRUCTURE;
            $programDTO->dataQualityIssues[] = DataQualityIssueType::UNRESOLVED_SEMESTER_STRUCTURE;

            return;
        }

        if ($programDTO->totalSemesters > 0 && $programDTO->totalSemesters < 6) {
            $programDTO->status = ProgramStatus::PARTIAL_SEMESTER_EXTRACTION;
            $programDTO->dataQualityIssues[] = DataQualityIssueType::PARTIAL_SEMESTER_EXTRACTION;

            return;
        }

        $missingDescriptions = 0;
        foreach ($programDTO->semesters as $semester) {
            foreach ($semester->courses as $course) {
                if (empty($course->description)) {
                    $missingDescriptions++;
                }
            }
        }

        if ($missingDescriptions > 0) {
            $programDTO->status = ProgramStatus::READY_WITH_MISSING_DESCRIPTIONS;
            $programDTO->dataQualityIssues[] = DataQualityIssueType::MISSING_COURSE_DESCRIPTIONS;

            return;
        }

        $programDTO->status = ProgramStatus::READY;
    }

    private function romanToInt(string $roman): int
    {
        $romans = ['M' => 1000, 'CM' => 900, 'D' => 500, 'CD' => 400, 'C' => 100, 'XC' => 90, 'L' => 50, 'XL' => 40, 'X' => 10, 'IX' => 9, 'V' => 5, 'IV' => 4, 'I' => 1];
        $result = 0;
        foreach ($romans as $key => $value) {
            while (strpos($roman, $key) === 0) {
                $result += $value;
                $roman = substr($roman, strlen($key));
            }
        }

        return $result ?: 1;
    }
}
