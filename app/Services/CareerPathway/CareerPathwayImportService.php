<?php

namespace App\Services\CareerPathway;

use App\DTOs\CareerPathway\ProgramDTO;
use App\Enums\DataQualitySeverity;
use App\Enums\ImportRunStatus;
use App\Enums\SourceDocumentType;
use App\Enums\SourceExtractionStatus;
use App\Models\CareerCohort;
use App\Models\CareerCourse;
use App\Models\CareerCourseDescription;
use App\Models\CareerDataQualityIssue;
use App\Models\CareerFaculty;
use App\Models\CareerImportRun;
use App\Models\CareerMajor;
use App\Models\CareerProgram;
use App\Models\CareerProgramCourse;
use App\Models\CareerSemester;
use App\Models\CareerSourceDocument;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Symfony\Component\Console\Output\OutputInterface;

class CareerPathwayImportService
{
    public function __construct(
        private MarkdownParserService $parserService
    ) {}

    public function importFromDirectory(string $basePath, ?OutputInterface $output = null): CareerImportRun
    {
        $run = CareerImportRun::create(['status' => ImportRunStatus::RUNNING, 'started_at' => now()]);

        if (! File::exists($basePath)) {
            $run->update([
                'status' => ImportRunStatus::FAILED,
                'completed_at' => now(),
                'log' => "Directory not found: $basePath",
            ]);

            return $run;
        }

        $files = File::allFiles($basePath);
        $mdFiles = array_filter($files, fn ($file) => $file->getFilename() === 'roadmap.md');

        if ($output) {
            $output->writeln('Found '.count($mdFiles).' roadmap.md files to process.');
        }

        foreach ($mdFiles as $file) {
            try {
                DB::beginTransaction();

                $filePath = $file->getPathname();
                $relativePath = str_replace($basePath.DIRECTORY_SEPARATOR, '', $filePath);
                $parts = explode(DIRECTORY_SEPARATOR, $relativePath);

                if (count($parts) < 5) {
                    throw new \Exception("Invalid directory structure: $relativePath");
                }

                $cohortName = $parts[0];
                $facultyName = $parts[2];
                $majorName = $parts[4];
                $originalDir = dirname($relativePath);

                $content = File::get($filePath);

                $programDTO = $this->parserService->parse(
                    $content,
                    $cohortName,
                    $facultyName,
                    $majorName,
                    $originalDir,
                    $filePath
                );

                $this->upsertProgramData($run, $programDTO);

                DB::commit();

                if ($output) {
                    $output->writeln("Processed: $majorName ($cohortName) - Status: {$programDTO->status->value}");
                }

            } catch (\Exception $e) {
                DB::rollBack();
                if ($output) {
                    $output->writeln("<error>Error processing $filePath: ".$e->getMessage().'</error>');
                }
            }
        }

        $run->update([
            'status' => ImportRunStatus::COMPLETED,
            'completed_at' => now(),
        ]);

        return $run;
    }

    private function upsertProgramData(CareerImportRun $run, ProgramDTO $dto): void
    {
        $cohort = CareerCohort::firstOrCreate(
            ['slug' => Str::slug($dto->cohortName)],
            ['name' => $dto->cohortName]
        );

        $faculty = CareerFaculty::firstOrCreate(
            ['slug' => Str::slug($dto->facultyName)],
            ['name' => $dto->facultyName]
        );

        $major = CareerMajor::firstOrCreate(
            ['slug' => Str::slug($dto->majorName), 'faculty_id' => $faculty->id],
            ['name' => $dto->majorName]
        );

        $sourceDoc = CareerSourceDocument::create([
            'import_run_id' => $run->id,
            'file_path' => $dto->filePath,
            'document_type' => SourceDocumentType::CHUONGTRINHKHUNG,
            'extraction_status' => SourceExtractionStatus::EXTRACTED,
        ]);

        $program = CareerProgram::updateOrCreate(
            [
                'cohort_id' => $cohort->id,
                'major_id' => $major->id,
            ],
            [
                'faculty_id' => $faculty->id,
                'source_document_id' => $sourceDoc->id,
                'name' => $dto->majorName,
                'slug' => Str::slug($dto->cohortName.' '.$dto->majorName),
                'status' => $dto->status,
                'total_credits' => $dto->totalCredits,
                'total_semesters' => $dto->totalSemesters,
                'original_dir' => $dto->originalDir,
            ]
        );

        // Wipe old semesters and program courses for sync
        $program->semesters()->delete();
        $program->programCourses()->delete();

        foreach ($dto->semesters as $semesterDto) {
            $semester = CareerSemester::create([
                'program_id' => $program->id,
                'semester_number' => $semesterDto->semesterNumber,
                'title' => $semesterDto->title,
            ]);

            foreach ($semesterDto->courses as $courseDto) {
                $course = CareerCourse::firstOrCreate(
                    ['code' => $courseDto->code],
                    ['name' => $courseDto->name]
                );

                if ($courseDto->description) {
                    CareerCourseDescription::updateOrCreate(
                        ['course_id' => $course->id],
                        ['description_text' => $courseDto->description]
                    );
                }

                CareerProgramCourse::create([
                    'program_id' => $program->id,
                    'semester_id' => $semester->id,
                    'course_id' => $course->id,
                    'source_document_id' => $sourceDoc->id,
                    'credits' => $courseDto->credits,
                    'is_mandatory' => $courseDto->isMandatory,
                    'knowledge_block' => $courseDto->knowledgeBlock,
                ]);
            }
        }

        foreach ($dto->dataQualityIssues as $issueType) {
            CareerDataQualityIssue::create([
                'import_run_id' => $run->id,
                'source_document_id' => $sourceDoc->id,
                'program_id' => $program->id,
                'issue_type' => $issueType,
                'severity' => DataQualitySeverity::P1_WARNING, // Default mapping
                'message' => "Extracted issue: {$issueType->value}",
            ]);
        }
    }
}
