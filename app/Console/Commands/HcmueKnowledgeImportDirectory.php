<?php

namespace App\Console\Commands;

use App\AI\HcmueChatbot\Ingestion\AcademicKnowledgeImportService;
use Illuminate\Console\Command;

class HcmueKnowledgeImportDirectory extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hcmue:knowledge:import-directory 
                            {--path=database/AI : Directory path relative to project root}
                            {--dry-run : Only show what would be imported}
                            {--limit= : Maximum number of documents to import}
                            {--force : Force import and re-process even if file hash exists}
                            {--only=pdf : File extension to filter}
                            {--document-type= : Filter by guessed document type}
                            {--batch-key= : Custom key for this knowledge batch}
                            {--only-cohort= : Only import files matching this cohort (e.g. K49)}
                            {--only-major= : Only import files matching this major (e.g. "Công nghệ thông tin")}
                            {--sync : Run text extraction, chunking, embedding, and indexing synchronously}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import academic documents from a local directory into DB and Qdrant';

    /**
     * Execute the console command.
     */
    public function handle(AcademicKnowledgeImportService $importService): int
    {
        $path = $this->option('path');
        $dryRun = $this->option('dry-run');
        $limit = $this->option('limit') !== null ? (int) $this->option('limit') : null;
        $force = $this->option('force');
        $only = $this->option('only');
        $docType = $this->option('document-type');
        $sync = $this->option('sync');
        $batchKey = $this->option('batch-key');
        $onlyCohort = $this->option('only-cohort');
        $onlyMajor = $this->option('only-major');

        $this->info('Import Directory Command Started:');
        $this->line('Target Directory: '.$path);
        $this->line('Dry Run:          '.($dryRun ? 'YES' : 'NO'));
        $this->line('Limit:            '.($limit ?: 'None'));
        $this->line('Force:            '.($force ? 'YES' : 'NO'));
        $this->line('Sync Ingestion:   '.($sync ? 'YES' : 'NO'));
        if ($batchKey) {
            $this->line('Batch Key:        '.$batchKey);
        }
        if ($onlyCohort) {
            $this->line('Only Cohort:      '.$onlyCohort);
        }
        if ($onlyMajor) {
            $this->line('Only Major:       '.$onlyMajor);
        }
        $this->info('-----------------------------------------------------------------');

        $callback = function (string $status, string $file, int $count) {
            if ($status === 'processing') {
                $this->line("[$count] Ingestion step: Processing '{$file}'...");
            } elseif ($status === 'completed') {
                $this->info("[$count] Ingestion step: Successfully imported '{$file}'!");
            } elseif ($status === 'failed') {
                $this->error("[$count] Ingestion step: Failed to import '{$file}'!");
            }
        };

        try {
            $results = $importService->importDirectory($path, [
                'dry_run' => $dryRun,
                'limit' => $limit,
                'force' => $force,
                'only' => $only,
                'document_type' => $docType,
                'sync' => $sync,
                'batch_key' => $batchKey,
                'only_cohort' => $onlyCohort,
                'only_major' => $onlyMajor,
                'progress_callback' => $callback,
            ]);

            $this->newLine();
            $this->info('IMPORT COMPLETED SUMMARY:');
            $this->line('Scanned Files:  '.$results['scanned']);
            $this->line('Imported/Processed: '.$results['imported']);
            $this->line('Skipped Files:  '.$results['skipped']);
            $this->line('Failed Files:   '.$results['failed']);
            $this->info('-----------------------------------------------------------------');

            if ($dryRun) {
                $this->info('DRY RUN SUMMARY OF ACTION PLAN:');
                $headers = ['File Path', 'Doc Type', 'Cohort', 'Year', 'Major'];
                $rows = [];
                foreach ($results['details'] as $detail) {
                    $rows[] = [
                        $detail['file'],
                        $detail['metadata']['document_type'],
                        $detail['metadata']['cohort'] ?: 'N/A',
                        $detail['metadata']['academic_year'] ?: 'N/A',
                        $detail['metadata']['major'] ?: 'N/A',
                    ];
                }
                $this->table($headers, $rows);
            } else {
                $this->info('DETAILED RESULTS:');
                $headers = ['File Path', 'Status', 'Doc ID', 'Ingested', 'Error/Reason'];
                $rows = [];
                foreach (array_slice($results['details'], 0, 30) as $detail) {
                    $rows[] = [
                        $detail['file'],
                        $detail['status'],
                        $detail['document_id'] ?? 'N/A',
                        isset($detail['ingested']) ? ($detail['ingested'] ? 'Success' : 'Failed/OCR') : 'Pending',
                        $detail['reason'] ?? 'N/A',
                    ];
                }
                $this->table($headers, $rows);
                if (count($results['details']) > 30) {
                    $this->line('... and '.(count($results['details']) - 30).' more file details logged.');
                }
            }

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Failed to import directory: '.$e->getMessage());

            return self::FAILURE;
        }
    }
}
