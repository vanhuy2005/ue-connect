<?php

namespace App\AI\HcmueChatbot\Ingestion;

use App\AI\HcmueChatbot\LLM\EmbeddingService;
use App\AI\HcmueChatbot\Retrieval\QdrantVectorStore;
use App\Models\DocumentChunk;
use App\Models\SourceDocument;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class StudentHandbookIngestionService
{
    public function __construct(
        protected AcademicDocumentTextExtractor $extractor,
        protected AcademicDocumentChunker $chunker,
        protected EmbeddingService $embeddingService,
        protected QdrantVectorStore $vectorStore
    ) {}

    /**
     * Ingest a source document into the database and Qdrant vector store.
     *
     * @throws \Exception
     */
    public function ingest(int $sourceDocumentId): bool
    {
        $sourceDoc = SourceDocument::findOrFail($sourceDocumentId);

        Log::info("Starting ingestion for SourceDocument ID: {$sourceDocumentId} - Title: {$sourceDoc->title}");

        $sourceDoc->update(['status' => 'processing']);

        try {
            $disk = Storage::disk('local');
            $filePath = $sourceDoc->file_path;

            if (! $disk->exists($filePath)) {
                throw new \Exception("Source file not found on local disk: {$filePath}");
            }

            $absolutePath = $disk->path($filePath);

            // 1. Extract text
            $text = $this->extractor->extract($absolutePath);
            if (empty(trim($text))) {
                throw new \Exception('Extracted text is empty.');
            }

            // 2. Chunks text
            $chunksData = $this->chunker->chunk($text);
            if (empty($chunksData)) {
                throw new \Exception('No chunks generated from document.');
            }

            Log::info('Generated '.count($chunksData)." chunks for document ID {$sourceDocumentId}");

            // 3. Clear existing chunks if any to allow safe re-ingestion
            $existingChunkIds = $sourceDoc->chunks()->pluck('id')->toArray();
            if (! empty($existingChunkIds)) {
                // Delete from Qdrant
                $this->vectorStore->delete($existingChunkIds);
                // Delete from DB
                $sourceDoc->chunks()->delete();
            }

            // 4. Save and embed each chunk
            foreach ($chunksData as $index => $data) {
                // Estimate token count roughly (e.g. word count or char count / 4)
                $tokenCount = (int) ceil(mb_strlen($data['chunk_text']) / 4);

                // Create chunk DB record
                $chunk = DocumentChunk::create([
                    'source_document_id' => $sourceDoc->id,
                    'chunk_index' => $data['chunk_index'],
                    'chunk_text' => $data['chunk_text'],
                    'token_count' => $tokenCount,
                    'page_start' => $data['page_start'],
                    'page_end' => $data['page_end'],
                    'part' => $data['part'],
                    'chapter' => $data['chapter'],
                    'section' => $data['section'],
                    'article' => $data['article'],
                    'clause' => $data['clause'],
                    'metadata_json' => [
                        'source_document_id' => $sourceDoc->id,
                        'document_name' => $sourceDoc->title,
                        'document_type' => $sourceDoc->document_type,
                        'cohort' => $sourceDoc->cohort,
                        'effective_year' => $sourceDoc->effective_year,
                        'page_start' => $data['page_start'],
                        'page_end' => $data['page_end'],
                        'part' => $data['part'],
                        'chapter' => $data['chapter'],
                        'section' => $data['section'],
                        'article' => $data['article'],
                        'clause' => $data['clause'],
                        'source_file' => basename($filePath),
                        'visibility' => 'public',
                    ],
                    'embedding_status' => 'pending',
                ]);

                try {
                    // Generate embedding vector
                    $vector = $this->embeddingService->embed($chunk->chunk_text);

                    if (empty($vector)) {
                        throw new \Exception('Generated embedding vector is empty.');
                    }

                    // Upsert point to Qdrant. Use chunk ID as point ID
                    $point = [
                        'id' => $chunk->id,
                        'vector' => $vector,
                        'payload' => $chunk->metadata_json,
                    ];

                    $upserted = $this->vectorStore->upsert([$point]);

                    if (! $upserted) {
                        throw new \Exception('Failed to upsert vector to Qdrant vector store.');
                    }

                    // Update chunk DB record
                    $chunk->update([
                        'embedding_status' => 'success',
                        'vector_id' => (string) $chunk->id,
                    ]);
                } catch (\Exception $chunkEx) {
                    Log::error("Failed to embed chunk ID: {$chunk->id} - Error: ".$chunkEx->getMessage());
                    $chunk->update([
                        'embedding_status' => 'failed',
                    ]);
                    // Re-throw to fail the whole document ingestion
                    throw $chunkEx;
                }
            }

            // Mark document as active and set published_at
            $sourceDoc->update([
                'status' => 'active',
                'published_at' => now(),
            ]);

            Log::info("Ingestion completed successfully for SourceDocument ID: {$sourceDocumentId}");

            return true;
        } catch (\Exception $e) {
            Log::error("Ingestion failed for SourceDocument ID: {$sourceDocumentId} - Error: ".$e->getMessage());
            $sourceDoc->update(['status' => 'failed']);

            return false;
        }
    }
}
