<?php

namespace App\AI\HcmueChatbot\Ingestion;

use App\AI\HcmueChatbot\Retrieval\QdrantVectorStore;
use App\Models\DocumentChunk;
use App\Models\KnowledgeBatch;
use App\Models\SourceDocument;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AcademicKnowledgeImportService
{
    public function __construct(
        protected AcademicMetadataExtractor $metadataExtractor,
        protected SourceDocumentUploadService $uploadService,
        protected BatchIngestionService $ingestionService
    ) {}

    /**
     * Import PDF files from a directory path.
     *
     * @param  string  $path  Directory path relative to project root.
     * @param array{
     *   dry_run?: bool,
     *   limit?: ?int,
     *   force?: bool,
     *   only?: string,
     *   document_type?: ?string,
     *   sync?: bool,
     *   progress_callback?: ?callable
     * } $options Import options.
     * @return array{
     *   scanned: int,
     *   imported: int,
     *   skipped: int,
     *   failed: int,
     *   details: array
     * }
     */
    public function importDirectory(string $path, array $options = []): array
    {
        $dryRun = $options['dry_run'] ?? false;
        $limit = $options['limit'] ?? null;
        $force = $options['force'] ?? false;
        $only = $options['only'] ?? 'pdf';
        $targetDocType = $options['document_type'] ?? null;
        $sync = $options['sync'] ?? false;
        $progressCallback = $options['progress_callback'] ?? null;

        $batchKey = $options['batch_key'] ?? 'batch_'.time().'_'.Str::random(4);
        $onlyCohort = $options['only_cohort'] ?? null;
        $onlyMajor = $options['only_major'] ?? null;

        $absolutePath = base_path($path);
        if (! File::exists($absolutePath)) {
            throw new \InvalidArgumentException("Import path does not exist: {$path}");
        }

        Log::info("Starting directory import from: {$path}");

        // Find files recursively
        $allFiles = File::allFiles($absolutePath);
        $scannedCount = 0;
        $importedCount = 0;
        $skippedCount = 0;
        $failedCount = 0;
        $needsOcrCount = 0;
        $details = [];

        // Pre-filter files by extension first
        $pdfFiles = [];
        foreach ($allFiles as $file) {
            $filePath = $file->getRealPath() ?: $file->getPathname();
            $extension = strtolower($file->getExtension());
            if ($only && $extension !== strtolower($only)) {
                continue;
            }
            $pdfFiles[] = $file;
        }

        $batch = null;
        if (! $dryRun) {
            $batch = KnowledgeBatch::firstOrCreate(
                ['batch_key' => $batchKey],
                [
                    'name' => 'Import batch '.$batchKey,
                    'root_path' => $path,
                    'status' => 'processing',
                    'total_files' => count($pdfFiles),
                    'started_at' => now(),
                ]
            );
        }

        foreach ($pdfFiles as $file) {
            $filePath = $file->getRealPath() ?: $file->getPathname();
            $scannedCount++;

            // 2. Extract metadata
            $meta = $this->metadataExtractor->extract($filePath);

            // 3. Filter by cohort
            if ($onlyCohort && (! isset($meta['cohort']) || strtolower($meta['cohort']) !== strtolower($onlyCohort))) {
                $skippedCount++;

                continue;
            }

            // 4. Filter by major
            if ($onlyMajor && (! isset($meta['major']) || stripos($meta['major'], $onlyMajor) === false)) {
                $skippedCount++;

                continue;
            }

            // 5. Filter by document type
            if ($targetDocType && strtolower($meta['document_type']) !== strtolower($targetDocType)) {
                $skippedCount++;

                continue;
            }

            // 6. Compute file hash to detect duplicates
            $hash = md5_file($filePath);
            $existing = SourceDocument::where('source_hash', $hash)->first();

            $relPath = str_replace(str_replace('\\', '/', base_path().'/'), '', str_replace('\\', '/', $filePath));

            if ($existing && ! $force) {
                $skippedCount++;
                $details[] = [
                    'file' => $relPath,
                    'status' => 'skipped',
                    'reason' => 'Hash match with existing SourceDocument ID: '.$existing->id,
                ];

                continue;
            }

            // Check if limit is reached
            if ($limit !== null && $importedCount >= $limit) {
                break;
            }

            if ($progressCallback) {
                $progressCallback('processing', $relPath, $importedCount + 1);
            }

            if ($dryRun) {
                $importedCount++;
                $details[] = [
                    'file' => $relPath,
                    'status' => 'dry-run',
                    'metadata' => $meta,
                ];

                continue;
            }

            try {
                // Determine document type mapping for upload service
                $uploadMetadata = [
                    'document_type' => $meta['document_type'],
                    'title' => $meta['title'],
                    'cohort' => $meta['cohort'],
                    'effective_year' => $meta['academic_year'],
                    'source_url' => $relPath, // Use relative path as internal source indicator
                    'knowledge_batch_id' => $batch ? $batch->id : null,
                    'knowledge_batch_key' => $batch ? $batch->batch_key : null,
                ];

                // If forcing update of an existing document, delete old chunks/vectors first to avoid orphans
                if ($existing && $force) {
                    // Truncate previous Qdrant vectors and DB records
                    $existingChunkIds = $existing->chunks()->pluck('id')->toArray();
                    if (! empty($existingChunkIds)) {
                        app(QdrantVectorStore::class)->delete($existingChunkIds);
                        $existing->chunks()->delete();
                    }
                    $existing->delete();
                }

                // 5. Upload/Copy file using SourceDocumentUploadService
                $doc = $this->uploadService->upload($filePath, $uploadMetadata);

                $detailsItem = [
                    'file' => $relPath,
                    'status' => 'imported',
                    'document_id' => $doc->id,
                    'ingested' => false,
                ];

                // 6. Ingest synchronously if requested
                if ($sync) {
                    $ingestResult = $this->ingestionService->ingest($doc->id);
                    $detailsItem['ingested'] = $ingestResult;
                    $detailsItem['ingestion_status'] = $ingestResult ? 'success' : 'failed';
                    if (! $ingestResult) {
                        $needsOcrCount++;
                    }
                }

                $importedCount++;
                $details[] = $detailsItem;

                if ($progressCallback) {
                    $progressCallback('completed', $relPath, $importedCount);
                }

            } catch (\Exception $e) {
                $failedCount++;
                Log::error("Failed to import file {$filePath}: ".$e->getMessage());
                $details[] = [
                    'file' => $relPath,
                    'status' => 'failed',
                    'reason' => $e->getMessage(),
                ];

                if ($progressCallback) {
                    $progressCallback('failed', $relPath, $importedCount);
                }
            }
        }

        // Update batch statistics when completed
        if ($batch) {
            $totalChunks = DocumentChunk::where('knowledge_batch_id', $batch->id)->count();
            $totalVectors = DocumentChunk::where('knowledge_batch_id', $batch->id)
                ->where('embedding_status', 'success')
                ->count();

            $batch->update([
                'status' => 'success',
                'total_imported' => $importedCount,
                'total_failed' => $failedCount,
                'total_needs_ocr' => $needsOcrCount,
                'total_chunks' => $totalChunks,
                'total_vectors' => $totalVectors,
                'finished_at' => now(),
            ]);
        }

        return [
            'scanned' => $scannedCount,
            'imported' => $importedCount,
            'skipped' => $skippedCount,
            'failed' => $failedCount,
            'details' => $details,
        ];
    }
}
