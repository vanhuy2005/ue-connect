<?php

namespace App\Services\CareerPathway;

use App\Enums\ProgramStatus;
use App\Models\CareerDataQualityIssue;
use App\Models\CareerProgram;
use App\Models\CareerProgramCourse;
use App\Models\CareerSemester;
use App\Models\CareerSourceDocument;
use Illuminate\Support\Facades\File;

class CareerPathwayAuditService
{
    public function __construct(
        private MarkdownParserService $parserService
    ) {}

    public function runAudit(string $sourcePath, array $options = []): array
    {
        $summary = [
            'source_roadmap_files_count' => 0,
            'db_source_documents_count' => CareerSourceDocument::count(),
            'db_programs_count' => CareerProgram::count(),
            'db_semesters_count' => CareerSemester::count(),
            'db_program_courses_count' => CareerProgramCourse::count(),
            'programs_by_status' => CareerProgram::selectRaw('status, count(*) as count')->groupBy('status')->pluck('count', 'status')->toArray(),
            'source_without_db_program_count' => 0,
            'db_program_without_source_count' => 0,
            'semester_count_mismatch_count' => 0,
            'course_count_mismatch_count' => 0,
            'missing_description_mismatch_count' => 0,
            'suspicious_zero_credit_program_count' => 0,
            'duplicate_course_code_count' => 0,
            'data_quality_issues_by_type' => CareerDataQualityIssue::selectRaw('issue_type, count(*) as count')->groupBy('issue_type')->pluck('count', 'issue_type')->toArray(),
            'public_ready_program_count' => CareerProgram::whereIn('status', [ProgramStatus::READY->value, ProgramStatus::READY_WITH_MISSING_DESCRIPTIONS->value])->count(),
            'non_public_program_count' => CareerProgram::whereNotIn('status', [ProgramStatus::READY->value, ProgramStatus::READY_WITH_MISSING_DESCRIPTIONS->value])->count(),
        ];

        $mismatches = [];
        $programComparisons = [];

        // 1. Scan source files
        if (! File::exists($sourcePath)) {
            throw new \Exception("Source path does not exist: $sourcePath");
        }

        $allFiles = File::allFiles($sourcePath);
        $roadmapFiles = array_filter($allFiles, fn ($file) => $file->getFilename() === 'roadmap.md');
        $summary['source_roadmap_files_count'] = count($roadmapFiles);
        $scannedSourceDirs = [];

        foreach ($roadmapFiles as $file) {
            $path = str_replace('\\', '/', $file->getPathname());
            $normalizedSourcePath = str_replace('\\', '/', $sourcePath);
            $relativePath = str_replace(rtrim($normalizedSourcePath, '/').'/', '', $path);
            $dirPath = dirname($relativePath);

            $parts = explode('/', $dirPath);
            // Expected: KhA3a/Khoa/TAn Khoa/NgAnh/TAn NgAnh
            if (count($parts) < 5) {
                continue; // Skip malformed paths
            }

            $cohortName = $parts[0];
            $facultyName = $parts[2];
            $majorName = $parts[4];

            $scannedSourceDirs[] = $dirPath;

            $program = CareerProgram::whereHas('cohort', fn ($q) => $q->where('name', $cohortName))
                ->whereHas('faculty', fn ($q) => $q->where('name', $facultyName))
                ->whereHas('major', fn ($q) => $q->where('name', $majorName))
                ->first();

            $content = file_get_contents($path);
            $parsedDTO = $this->parserService->parse($content, $cohortName, $facultyName, $majorName, $dirPath, $relativePath);

            $programComparisons[] = [
                'cohort' => $cohortName,
                'faculty' => $facultyName,
                'major' => $majorName,
                'source_relative_dir' => $dirPath,
                'db_program_id' => $program?->id,
                'parsed_semesters' => count($parsedDTO->semesters),
                'parsed_courses' => collect($parsedDTO->semesters)->sum(fn ($s) => count($s->courses)),
                'db_semesters' => $program ? $program->semesters()->count() : 0,
                'db_courses' => $program ? $program->courses()->count() : 0,
            ];

            if (! $program) {
                $mismatches[] = $this->createMismatch(
                    'source_file_not_imported',
                    'high',
                    $cohortName, $facultyName, $majorName, $dirPath, $relativePath,
                    null, '1 program', '0 program',
                    'Found roadmap.md in source but no corresponding CareerProgram in DB.',
                    'Run import command for this directory.'
                );
                $summary['source_without_db_program_count']++;

                continue;
            }

            // Check semester count
            $parsedSemesterCount = count($parsedDTO->semesters);
            $dbSemesterCount = $program->semesters()->count();
            if ($parsedSemesterCount !== $dbSemesterCount) {
                $mismatches[] = $this->createMismatch(
                    'semester_count_mismatch',
                    'high',
                    $cohortName, $facultyName, $majorName, $dirPath, $relativePath,
                    $program->id, (string) $parsedSemesterCount, (string) $dbSemesterCount,
                    "Parsed $parsedSemesterCount semesters from MD but DB has $dbSemesterCount.",
                    'Re-import or check parser logic.'
                );
                $summary['semester_count_mismatch_count']++;
            }

            // Check course count
            $parsedCourseCount = collect($parsedDTO->semesters)->sum(fn ($s) => count($s->courses));
            $dbCourseCount = $program->courses()->count();
            if ($parsedCourseCount !== $dbCourseCount) {
                $mismatches[] = $this->createMismatch(
                    'course_count_mismatch',
                    'high',
                    $cohortName, $facultyName, $majorName, $dirPath, $relativePath,
                    $program->id, (string) $parsedCourseCount, (string) $dbCourseCount,
                    "Parsed $parsedCourseCount courses from MD but DB has $dbCourseCount.",
                    'Re-import or check parser logic.'
                );
                $summary['course_count_mismatch_count']++;
            }

            // Check suspicious zero credits
            $dbZeroCreditCount = $program->courses()->where(fn ($q) => $q->whereNull('credits')->orWhere('credits', 0))->count();
            if ($dbZeroCreditCount > 0 && $parsedCourseCount > 0 && $dbZeroCreditCount === $dbCourseCount) {
                $mismatches[] = $this->createMismatch(
                    'suspicious_zero_credits',
                    'medium',
                    $cohortName, $facultyName, $majorName, $dirPath, $relativePath,
                    $program->id, '> 0 credits', '0 credits',
                    'All courses in this program have 0 or null credits.',
                    'Check source document table for credit column.'
                );
                $summary['suspicious_zero_credit_program_count']++;
            }

            // Check missing descriptions issues
            $parsedMissingDescCount = collect($parsedDTO->dataQualityIssues)->where('issueType', 'missing_course_descriptions')->count() > 0 ? 1 : 0;
            $dbMissingDescCount = $program->dataQualityIssues()->where('issue_type', 'missing_course_descriptions')->exists() ? 1 : 0;
            if ($parsedMissingDescCount !== $dbMissingDescCount) {
                $mismatches[] = $this->createMismatch(
                    'missing_description_mismatch',
                    'low',
                    $cohortName, $facultyName, $majorName, $dirPath, $relativePath,
                    $program->id, $parsedMissingDescCount ? 'Has issue' : 'No issue', $dbMissingDescCount ? 'Has issue' : 'No issue',
                    'Mismatch in missing_course_descriptions flag.',
                    'Re-import.'
                );
                $summary['missing_description_mismatch_count']++;
            }

            // Check bad status exposure
            if (in_array($parsedDTO->status, [ProgramStatus::UNRESOLVED_SEMESTER_STRUCTURE, ProgramStatus::EMPTY_EXTRACTION, ProgramStatus::PARTIAL_SEMESTER_EXTRACTION])) {
                if (in_array($program->status, [ProgramStatus::READY, ProgramStatus::READY_WITH_MISSING_DESCRIPTIONS])) {
                    $mismatches[] = $this->createMismatch(
                        'invalid_public_status',
                        'critical',
                        $cohortName, $facultyName, $majorName, $dirPath, $relativePath,
                        $program->id, $parsedDTO->status->value, $program->status->value,
                        'Program has known parsing failure but is marked as READY.',
                        'Change program status to blocked.'
                    );
                }
            }

            // Check duplicate course codes
            $dbDuplicateCourses = $program->courses()->selectRaw('code, count(*) as count')->groupBy('code')->havingRaw('count > 1')->get();
            if ($dbDuplicateCourses->isNotEmpty()) {
                $mismatches[] = $this->createMismatch(
                    'duplicate_course_in_program',
                    'medium',
                    $cohortName, $facultyName, $majorName, $dirPath, $relativePath,
                    $program->id, 'Unique codes', 'Duplicates found',
                    'Found duplicate course codes: '.$dbDuplicateCourses->pluck('code')->join(', '),
                    'Fix source MD.'
                );
                $summary['duplicate_course_code_count']++;
            }
        }

        // 2. Check DB programs that have no source file
        $dbPrograms = CareerProgram::with(['cohort', 'faculty', 'major'])->get();
        foreach ($dbPrograms as $program) {
            $expectedDir = $program->cohort->name.'/Khoa/'.$program->faculty->name.'/Ngành/'.$program->major->name;
            $expectedDirReplaced = str_replace('\\', '/', $expectedDir);

            $found = false;
            foreach ($scannedSourceDirs as $scannedDir) {
                if (str_ends_with($scannedDir, $expectedDirReplaced)) {
                    $found = true;
                    break;
                }
            }

            if (! $found) {
                $mismatches[] = $this->createMismatch(
                    'db_program_without_source',
                    'high',
                    $program->cohort->name, $program->faculty->name, $program->major->name, null, null,
                    $program->id, 'Source file exists', 'No source file',
                    'Program exists in DB but no roadmap.md was found in source.',
                    'Verify if source file was deleted.'
                );
                $summary['db_program_without_source_count']++;
            }
        }

        if (isset($options['output']) && $options['output']) {
            $this->exportResults($summary, $mismatches, $programComparisons, $options['output']);
        }

        return [
            'summary' => $summary,
            'mismatches' => $mismatches,
            'programComparisons' => $programComparisons,
        ];
    }

    private function createMismatch($type, $severity, $cohort, $faculty, $major, $relativeDir, $mdPath, $programId, $expected, $actual, $message, $action): array
    {
        return [
            'issue_type' => $type,
            'severity' => $severity,
            'cohort' => $cohort,
            'faculty' => $faculty,
            'major' => $major,
            'source_relative_dir' => $relativeDir,
            'source_markdown_path' => $mdPath,
            'db_program_id' => $programId,
            'expected_value' => $expected,
            'actual_value' => $actual,
            'message' => $message,
            'recommended_action' => $action,
        ];
    }

    private function exportResults(array $summary, array $mismatches, array $programComparisons, string $outputPath): void
    {
        if (! File::exists($outputPath)) {
            File::makeDirectory($outputPath, 0755, true);
        }

        file_put_contents($outputPath.'/career_pathway_import_audit_summary.json', json_encode($summary, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        // Mismatches CSV
        if (count($mismatches) > 0) {
            $csv = fopen($outputPath.'/career_pathway_import_audit_mismatches.csv', 'w');
            fputcsv($csv, array_keys($mismatches[0]));
            foreach ($mismatches as $row) {
                fputcsv($csv, $row);
            }
            fclose($csv);
        } else {
            file_put_contents($outputPath.'/career_pathway_import_audit_mismatches.csv', 'No mismatches found.');
        }

        // Program Comparisons CSV
        if (count($programComparisons) > 0) {
            $csv = fopen($outputPath.'/career_pathway_program_comparison.csv', 'w');
            fputcsv($csv, array_keys($programComparisons[0]));
            foreach ($programComparisons as $row) {
                fputcsv($csv, $row);
            }
            fclose($csv);
        }
    }
}
