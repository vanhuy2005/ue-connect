<?php

namespace App\Console\Commands;

use App\AI\HcmueChatbot\Ingestion\TrainingProgramPdfParser;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ImportCurriculum extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ai:import-curriculum
                            {--cohort= : The cohort name to filter (e.g. "2022 - Khóa 48")}
                            {--major= : The major name to filter (e.g. "Công nghệ thông tin")}
                            {--limit=2 : Maximum number of programs to process in this run to avoid rate limit}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scan database/AI/Chuongtrinhdaotao/ and import PDF curriculum into structured database';

    /**
     * Execute the console command.
     */
    public function handle(TrainingProgramPdfParser $parser)
    {
        $baseDir = base_path('database/AI/Chuongtrinhdaotao');

        if (! File::exists($baseDir)) {
            $this->error("Base directory does not exist: {$baseDir}");

            return Command::FAILURE;
        }

        $this->info("Scanning for curriculum directories in {$baseDir}...");

        $metadataFiles = [];
        $cohortDirs = File::directories($baseDir);

        foreach ($cohortDirs as $cohortDir) {
            $cohortName = basename($cohortDir);

            if ($this->option('cohort') && ! str_contains(strtolower($cohortName), strtolower($this->option('cohort')))) {
                continue;
            }

            // Path to Khoa
            $khoaPath = $cohortDir.DIRECTORY_SEPARATOR.'Khoa';
            if (! File::exists($khoaPath)) {
                continue;
            }

            $faculties = File::directories($khoaPath);
            foreach ($faculties as $faculty) {
                $majorPath = $faculty.DIRECTORY_SEPARATOR.'Ngành';
                if (! File::exists($majorPath)) {
                    continue;
                }

                $majors = File::directories($majorPath);
                foreach ($majors as $major) {
                    $majorName = basename($major);

                    if ($this->option('major') && ! str_contains(strtolower($majorName), strtolower($this->option('major')))) {
                        continue;
                    }

                    $metadataPath = $major.DIRECTORY_SEPARATOR.'metadata.json';
                    if (File::exists($metadataPath)) {
                        $metadataFiles[] = [
                            'metadata_path' => $metadataPath,
                            'major_dir' => $major,
                            'cohort' => $cohortName,
                            'major' => $majorName,
                        ];
                    }
                }
            }
        }

        $totalFound = count($metadataFiles);
        if ($totalFound === 0) {
            $this->info('No curriculum program matches found.');

            return Command::SUCCESS;
        }

        $this->info("Found {$totalFound} training programs matching filters.");

        $limit = (int) $this->option('limit');
        $processedCount = 0;

        foreach ($metadataFiles as $item) {
            if ($processedCount >= $limit) {
                $this->warn("Reached limit of {$limit} programs. Stopping execution to prevent API rate limit.");
                break;
            }

            $this->newLine();
            $this->info("Processing: {$item['cohort']} - {$item['major']}");

            try {
                // 1. Read metadata.json
                $metadata = json_decode(File::get($item['metadata_path']), true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    $this->error('Failed to parse metadata.json: '.json_last_error_msg());

                    continue;
                }

                // 2. Locate the pdf file
                $pdfPath = null;
                $allFiles = File::allFiles($item['major_dir']);

                // First pass: search for typical curriculum framework keywords
                foreach ($allFiles as $file) {
                    if (strtolower($file->getExtension()) === 'pdf') {
                        $filename = strtolower($file->getFilename());
                        if (str_contains($filename, 'ctk') ||
                            str_contains($filename, 'khung') ||
                            str_contains($filename, 'chuongtrinhkhung') ||
                            str_contains($filename, 'chuong_trinh_khung') ||
                            str_contains($filename, 'ctdt') ||
                            str_contains($filename, 'chuong_trinh_dao_tao')) {
                            $pdfPath = $file->getRealPath();
                            break;
                        }
                    }
                }

                // Second pass fallback: any PDF that is not a learning outcome (CDR)
                if (! $pdfPath) {
                    foreach ($allFiles as $file) {
                        if (strtolower($file->getExtension()) === 'pdf') {
                            $filename = strtolower($file->getFilename());
                            if (! str_contains($filename, 'cdr') && ! str_contains($filename, 'chuan_dau_ra')) {
                                $pdfPath = $file->getRealPath();
                                break;
                            }
                        }
                    }
                }

                if (! $pdfPath) {
                    $this->error("No PDF curriculum file found in {$item['major_dir']}");

                    continue;
                }

                $this->info('Found PDF: '.basename($pdfPath));

                // 3. Trigger parser
                $result = $parser->parseAndImport($pdfPath, $metadata);

                if ($result['success']) {
                    $this->info("Successfully imported! Title: {$result['title']} (Courses: {$result['course_count']})");
                    $processedCount++;
                }

            } catch (\Exception $e) {
                $this->error('Failed to process program: '.$e->getMessage());
            }

            // Sleep briefly to prevent API rate limits between files
            if ($processedCount < $limit) {
                sleep(2);
            }
        }

        $this->newLine();
        $this->info("Processed {$processedCount} / {$totalFound} programs successfully.");

        return Command::SUCCESS;
    }
}
