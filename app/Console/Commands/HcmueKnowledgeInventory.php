<?php

namespace App\Console\Commands;

use App\AI\HcmueChatbot\Ingestion\AcademicKnowledgeInventoryService;
use Illuminate\Console\Command;

class HcmueKnowledgeInventory extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hcmue:knowledge:inventory 
                            {--path=database/AI : The directory path to inventory relative to project root}
                            {--sample=50 : The number of sample files to list}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scan target directory and inventory academic files, document type classifications, and readability statistics';

    /**
     * Execute the console command.
     */
    public function handle(AcademicKnowledgeInventoryService $inventoryService): int
    {
        $path = $this->option('path');
        $sampleLimit = (int) $this->option('sample');

        $this->info("Scanning directory '{$path}' for inventory (sample limit: {$sampleLimit})...");
        $this->info('-----------------------------------------------------------------');

        try {
            $report = $inventoryService->runInventory($path, $sampleLimit);

            // General Info
            $this->info('GENERAL SUMMARY:');
            $this->line('Total Files:        '.$report['total_files']);
            $this->line('Total Size:         '.number_format($report['total_size_bytes'] / (1024 * 1024), 2).' MB');
            $this->line('Text Readable PDFs: '.$report['total_readable_pdfs']);
            $this->line('Scanned PDFs:       '.$report['total_scanned_pdfs']);
            $this->info('-----------------------------------------------------------------');

            // File Extensions
            $this->info('FILE EXTENSIONS:');
            $extensionsTable = [];
            foreach ($report['file_types'] as $ext => $count) {
                $extensionsTable[] = ['Extension' => '.'.$ext, 'Count' => $count];
            }
            $this->table(['Extension', 'Count'], $extensionsTable);
            $this->info('-----------------------------------------------------------------');

            // Guessed Document Types
            $this->info('DOCUMENT TYPE ESTIMATES (PDFs only):');
            $docTypesTable = [];
            foreach ($report['document_types'] as $type => $count) {
                $docTypesTable[] = ['Document Type' => $type, 'Count' => $count];
            }
            $this->table(['Document Type', 'Count'], $docTypesTable);
            $this->info('-----------------------------------------------------------------');

            // Guessed Cohorts
            $this->info('COHORT ESTIMATES (PDFs only):');
            $cohortsTable = [];
            arsort($report['cohorts']);
            foreach (array_slice($report['cohorts'], 0, 10, true) as $cohort => $count) {
                $cohortsTable[] = ['Cohort' => $cohort, 'Count' => $count];
            }
            $this->table(['Cohort', 'Count'], $cohortsTable);
            if (count($report['cohorts']) > 10) {
                $this->line('... and '.(count($report['cohorts']) - 10).' more cohorts.');
            }
            $this->info('-----------------------------------------------------------------');

            // Guessed Majors
            $this->info('MAJOR ESTIMATES (PDFs only):');
            $majorsTable = [];
            arsort($report['majors']);
            foreach (array_slice($report['majors'], 0, 15, true) as $major => $count) {
                $majorsTable[] = ['Major', 'Count' => $count];
            }
            // Replace the key with name
            $index = 0;
            foreach (array_slice($report['majors'], 0, 15, true) as $major => $count) {
                $majorsTable[$index++]['Major'] = $major;
            }
            $this->table(['Major', 'Count'], $majorsTable);
            if (count($report['majors']) > 15) {
                $this->line('... and '.(count($report['majors']) - 15).' more majors.');
            }
            $this->info('-----------------------------------------------------------------');

            // Guessed Faculties
            $this->info('FACULTY ESTIMATES (PDFs only):');
            $facultiesTable = [];
            arsort($report['faculties']);
            foreach (array_slice($report['faculties'], 0, 10, true) as $faculty => $count) {
                $facultiesTable[] = ['Faculty' => $faculty, 'Count' => $count];
            }
            $this->table(['Faculty', 'Count'], $facultiesTable);
            if (count($report['faculties']) > 10) {
                $this->line('... and '.(count($report['faculties']) - 10).' more faculties.');
            }
            $this->info('-----------------------------------------------------------------');

            // Top 30 Unknown Filenames
            if (! empty($report['unknown_filenames'])) {
                $this->warn('TOP UNKNOWN FILENAMES (Guessed Document Type = unknown):');
                foreach ($report['unknown_filenames'] as $fn) {
                    $this->line(" - {$fn}");
                }
                $this->info('-----------------------------------------------------------------');
            }

            // Scanned PDFs requiring OCR
            if ($report['total_scanned_pdfs'] > 0) {
                $this->warn('LIKELY SCANNED PDFs (Text Length < 500 chars, needs OCR):');
                $scannedTable = [];
                foreach (array_slice($report['scanned_pdfs'], 0, 10) as $pdf) {
                    $scannedTable[] = [
                        'Path' => $pdf['file_path'],
                        'Size' => number_format($pdf['size_bytes'] / 1024, 1).' KB',
                        'Text Length' => $pdf['text_length'],
                    ];
                }
                $this->table(['Path', 'Size', 'Text Length'], $scannedTable);
                if ($report['total_scanned_pdfs'] > 10) {
                    $this->warn('... and '.($report['total_scanned_pdfs'] - 10).' more scanned files.');
                }
            } else {
                $this->info('Great! No scanned PDFs (requiring OCR) were detected.');
            }

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Failed to run inventory: '.$e->getMessage());

            return self::FAILURE;
        }
    }
}
