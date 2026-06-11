<?php

namespace App\AI\HcmueChatbot\Ingestion;

use App\AI\HcmueChatbot\LLM\EmbeddingService;
use App\AI\HcmueChatbot\Retrieval\QdrantVectorStore;
use App\Models\DocumentChunk;
use App\Models\SourceDocument;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class BatchIngestionService
{
    public function __construct(
        protected AcademicDocumentTextExtractor $extractor,
        protected AcademicDocumentChunker $chunker,
        protected EmbeddingService $embeddingService,
        protected QdrantVectorStore $vectorStore,
        protected PdfTextQualityAnalyzer $qualityAnalyzer,
        protected AcademicMetadataExtractor $metadataExtractor
    ) {}

    /**
     * Ingest a SourceDocument into DB and Qdrant.
     *
     * @param  int  $sourceDocumentId  ID of the source document.
     * @return bool True if successfully ingested, false if failed or marked as needs_ocr.
     */
    public function ingest(int $sourceDocumentId): bool
    {
        $sourceDoc = SourceDocument::findOrFail($sourceDocumentId);
        Log::info("BatchIngestionService: Starting ingestion for SourceDocument ID: {$sourceDocumentId} ({$sourceDoc->title})");

        $sourceDoc->update(['status' => 'processing']);

        try {
            $disk = Storage::disk('local');
            $filePath = $sourceDoc->file_path;

            if (! $disk->exists($filePath)) {
                throw new \Exception("Source file not found in storage: {$filePath}");
            }

            $absolutePath = $disk->path($filePath);

            // 1. Check quality (Text vs Scanned image)
            $quality = $this->qualityAnalyzer->analyze($absolutePath);
            if ($quality['needs_ocr']) {
                Log::warning("BatchIngestionService: Document ID {$sourceDocumentId} detected as SCANNED PDF / Empty text. Marking status as needs_ocr.");
                $sourceDoc->update(['status' => 'needs_ocr']);

                return false;
            }

            // 2. Extract text (we know it has text from the step above)
            $text = $this->extractor->extract($absolutePath);
            if (empty(trim($text))) {
                throw new \Exception('Extracted text is empty.');
            }

            // Run structured extraction if it is a training program
            if ($sourceDoc->document_type === 'training_program') {
                try {
                    $structuredResult = app(TrainingProgramStructuredExtractor::class)->extractAndSave($sourceDoc, $text);
                    Log::info("BatchIngestionService: Structured extraction completed for Document ID: {$sourceDoc->id}", $structuredResult);
                } catch (\Exception $e) {
                    Log::error("BatchIngestionService: Structured extraction failed for Document ID: {$sourceDoc->id} - ".$e->getMessage());
                }
            }

            // 3. Chunk text
            $chunksData = $this->chunker->chunk($text);
            if (empty($chunksData)) {
                throw new \Exception('No chunks generated from document.');
            }

            Log::info('BatchIngestionService: Generated '.count($chunksData)." chunks for document ID {$sourceDocumentId}");

            // 4. Retrieve metadata from source_url to reconstruct Faculty/Major/Cohort/AcademicYear
            $sourceFile = $sourceDoc->source_url ?: $filePath;
            $meta = $this->metadataExtractor->extract(base_path($sourceFile));

            // 5. Clean up any previous chunks and vectors for this document
            $existingChunkIds = $sourceDoc->chunks()->pluck('id')->toArray();
            $oldChunkCount = count($existingChunkIds);
            $newChunkCount = count($chunksData);
            Log::info("BatchIngestionService: Document ID {$sourceDocumentId} count comparison - Old chunks: {$oldChunkCount}, New chunks: {$newChunkCount}");

            if (! empty($existingChunkIds)) {
                Log::info('BatchIngestionService: Deleting '.count($existingChunkIds)." existing points in Qdrant for document ID {$sourceDocumentId}");
                $this->vectorStore->delete($existingChunkIds);
                $sourceDoc->chunks()->delete();
            }

            $dbChunks = [];
            $targetVectorSize = config('ai.embedding.dimensions', 768);

            // 6. Generate DB records and collect texts
            foreach ($chunksData as $data) {
                $tokenCount = (int) ceil(mb_strlen($data['chunk_text'], 'UTF-8') / 4);

                $payload = [
                    'source_document_id' => $sourceDoc->id,
                    'document_name' => $sourceDoc->title,
                    'document_type' => $sourceDoc->document_type,
                    'cohort' => $meta['cohort'] ?: $sourceDoc->cohort,
                    'academic_year' => (string) ($meta['academic_year'] ?: $sourceDoc->effective_year),
                    'faculty' => $meta['faculty'],
                    'major' => $meta['major'],
                    'program_level' => $meta['program_level'] ?: 'undergraduate',
                    'source_file' => $sourceDoc->source_url,
                    'source_hash' => $sourceDoc->source_hash,
                    'page_start' => $data['page_start'],
                    'page_end' => $data['page_end'],
                    'part' => $data['part'],
                    'chapter' => $data['chapter'],
                    'section' => $data['section'],
                    'article' => $data['article'],
                    'visibility' => 'public',
                    'chunk_text' => $data['chunk_text'], // Keep text in payload for fallback
                    'knowledge_batch_id' => (string) $sourceDoc->knowledge_batch_id,
                    'knowledge_batch_key' => $sourceDoc->knowledge_batch_key,
                ];

                // Create database record first to get the unique ID for vector point
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
                    'metadata_json' => $payload,
                    'embedding_status' => 'pending',
                    'knowledge_batch_id' => $sourceDoc->knowledge_batch_id,
                    'knowledge_batch_key' => $sourceDoc->knowledge_batch_key,
                ]);

                $dbChunks[] = $chunk;
            }

            // 7. Generate embeddings in batches of 50
            $batchSize = 50;
            $chunkCount = count($dbChunks);
            $points = [];

            for ($i = 0; $i < $chunkCount; $i += $batchSize) {
                if ($i > 0) {
                    Log::info('BatchIngestionService: Sleeping 3 seconds between embedding batches to respect rate limits...');
                    sleep(3);
                }
                $batchDbChunks = array_slice($dbChunks, $i, $batchSize);
                $batchTexts = array_map(fn ($c) => $c->chunk_text, $batchDbChunks);

                // Call batch embedding service
                $vectors = $this->embeddingService->batchEmbed($batchTexts);

                foreach ($batchDbChunks as $idx => $chunk) {
                    $vector = $vectors[$idx] ?? [];

                    if (empty($vector)) {
                        throw new \Exception('Generated embedding vector is empty for chunk index: '.$chunk->chunk_index);
                    }

                    // Verify dimension size
                    $vectorSize = count($vector);
                    if ($vectorSize !== $targetVectorSize) {
                        throw new \Exception("Embedding dimension mismatch. Expected {$targetVectorSize}, got {$vectorSize} for chunk index ".$chunk->chunk_index);
                    }

                    // Add to Qdrant points batch
                    $points[] = [
                        'id' => $chunk->id,
                        'vector' => $vector,
                        'payload' => $chunk->metadata_json,
                        '_chunk_record' => $chunk, // Keep database model reference temporarily
                    ];
                }
            }

            // 8. Upsert to Qdrant in batches of 50 to avoid heavy payloads
            for ($i = 0; $i < count($points); $i += $batchSize) {
                $batchPoints = array_slice($points, $i, $batchSize);

                // Strip the temporary DB record reference before sending to Qdrant
                $qdrantPoints = array_map(function ($point) {
                    $cleaned = $point;
                    unset($cleaned['_chunk_record']);

                    return $cleaned;
                }, $batchPoints);

                $upserted = $this->vectorStore->upsert($qdrantPoints);
                if (! $upserted) {
                    throw new \Exception('Failed to upsert vector batch '.($i / $batchSize + 1).' to Qdrant.');
                }

                // Mark batch chunks as success in DB
                foreach ($batchPoints as $point) {
                    $point['_chunk_record']->update([
                        'embedding_status' => 'success',
                        'vector_id' => (string) $point['id'],
                    ]);
                }
            }

            // 8. Update SourceDocument status to active
            $sourceDoc->update([
                'status' => 'active',
                'published_at' => now(),
            ]);

            Log::info("BatchIngestionService: Ingestion completed successfully for SourceDocument ID: {$sourceDocumentId}");

            return true;
        } catch (\Exception $e) {
            Log::error("BatchIngestionService: Ingestion failed for SourceDocument ID: {$sourceDocumentId} - Error: ".$e->getMessage());
            $sourceDoc->update(['status' => 'failed']);

            return false;
        }
    }
}
