<?php

namespace App\Console\Commands;

use App\AI\HcmueChatbot\Ingestion\StudentHandbookIngestionService;
use App\Models\SourceDocument;
use Illuminate\Console\Command;

class IngestSourceDocument extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hcmue:rag:ingest-source {source_document_id : The ID of the Source Document}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Ingest a source document, extract text, chunk, embed and upsert to Qdrant';

    /**
     * Execute the console command.
     */
    public function handle(StudentHandbookIngestionService $ingestionService): int
    {
        $id = $this->argument('source_document_id');

        $sourceDoc = SourceDocument::find($id);
        if (! $sourceDoc) {
            $this->error("Source document with ID {$id} not found.");

            return self::FAILURE;
        }

        $this->info("Starting ingestion for Document ID {$id}: '{$sourceDoc->title}'...");

        $success = $ingestionService->ingest($id);

        if ($success) {
            $this->info("Successfully completed ingestion for Document ID {$id}!");

            return self::SUCCESS;
        }

        $this->error("Ingestion failed for Document ID {$id}. Check system logs.");

        return self::FAILURE;
    }
}
