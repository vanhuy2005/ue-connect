<?php

namespace App\Console\Commands;

use App\AI\HcmueChatbot\Ingestion\BatchIngestionService;
use App\Models\SourceDocument;
use Illuminate\Console\Command;

class HcmueKnowledgeIngestPending extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hcmue:knowledge:ingest-pending 
                            {--limit=10 : Maximum number of documents to ingest in this batch}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Ingest source documents that are in uploaded, pending, or failed status';

    /**
     * Execute the console command.
     */
    public function handle(BatchIngestionService $ingestionService): int
    {
        $limit = (int) $this->option('limit');
        $this->info('Scanning database for pending/uploaded/failed source documents...');

        $pendingDocs = SourceDocument::whereIn('status', ['uploaded', 'pending', 'failed'])
            ->limit($limit)
            ->get();

        $totalFound = $pendingDocs->count();
        if ($totalFound === 0) {
            $this->info('No pending, uploaded, or failed source documents found. Database is fully synced.');

            return self::SUCCESS;
        }

        $this->info("Found {$totalFound} documents to process.");
        $this->info('-----------------------------------------------------------------');

        $successCount = 0;
        $ocrCount = 0;
        $failCount = 0;

        foreach ($pendingDocs as $index => $doc) {
            $this->line('['.($index + 1)."/{$totalFound}] Processing Document ID: {$doc->id} - Path: {$doc->file_path}");

            $result = $ingestionService->ingest($doc->id);

            // Fetch fresh state to check OCR vs Failed
            $doc->refresh();

            if ($result) {
                $this->info('--> Success! Document Ingested & indexed in Qdrant.');
                $successCount++;
            } elseif ($doc->status === 'needs_ocr') {
                $this->warn('--> Skipped. Document marked as Scanned (needs OCR).');
                $ocrCount++;
            } else {
                $this->error('--> Failed. Ingestion aborted.');
                $failCount++;
            }
            $this->newLine();
        }

        $this->info('INGEST PENDING COMPLETED:');
        $this->line('Successfully Ingested: '.$successCount);
        $this->line('Needs OCR:             '.$ocrCount);
        $this->line('Ingestion Failures:    '.$failCount);
        $this->info('-----------------------------------------------------------------');

        return self::SUCCESS;
    }
}
