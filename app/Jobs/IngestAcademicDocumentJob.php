<?php

namespace App\Jobs;

use App\AI\HcmueChatbot\Ingestion\StudentHandbookIngestionService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class IngestAcademicDocumentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public int $sourceDocumentId
    ) {}

    /**
     * Execute the job.
     */
    public function handle(StudentHandbookIngestionService $ingestionService): void
    {
        Log::info("Starting queued ingestion job for SourceDocument ID: {$this->sourceDocumentId}");

        $success = $ingestionService->ingest($this->sourceDocumentId);

        if (! $success) {
            throw new \Exception("Queued ingestion failed for SourceDocument ID: {$this->sourceDocumentId}");
        }
    }
}
