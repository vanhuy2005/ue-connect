<?php

namespace App\AI\HcmueChatbot\Ingestion;

use App\Models\SourceDocument;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SourceDocumentUploadService
{
    /**
     * Upload a document via HTTP UploadedFile or local path.
     *
     * @param  UploadedFile|string  $file  The uploaded file or absolute/relative file path.
     * @param array{
     *   document_type: string,
     *   title: string,
     *   cohort?: string,
     *   effective_year?: int,
     *   source_url?: string,
     *   uploaded_by?: int
     * } $metadata Additional metadata.
     *
     * @throws \Exception
     */
    public function upload($file, array $metadata): SourceDocument
    {
        $disk = Storage::disk('local');
        $directory = 'hcmue/sources';

        // Make sure directory exists
        if (! $disk->exists($directory)) {
            $disk->makeDirectory($directory);
        }

        $sourceHash = '';
        $originalName = '';
        $mimeType = '';
        $savedPath = '';

        if ($file instanceof UploadedFile) {
            $originalName = $file->getClientOriginalName();
            $mimeType = $file->getClientMimeType();
            $sourceHash = md5_file($file->getRealPath());

            // Generate unique safe name
            $fileName = Str::uuid().'.'.$file->getClientOriginalExtension();
            $savedPath = $file->storeAs($directory, $fileName, 'local');
        } elseif (is_string($file)) {
            if (! file_exists($file)) {
                throw new \Exception("Local file not found for upload: {$file}");
            }
            $originalName = basename($file);
            $mimeType = mime_content_type($file) ?: 'application/octet-stream';
            $sourceHash = md5_file($file);

            // Generate unique safe name
            $extension = pathinfo($file, PATHINFO_EXTENSION);
            $fileName = Str::uuid().'.'.$extension;
            $savedPath = $directory.'/'.$fileName;

            // Copy file to local disk
            $copied = $disk->put($savedPath, file_get_contents($file));
            if (! $copied) {
                throw new \Exception("Failed to copy local file {$file} to storage.");
            }
        } else {
            throw new \Exception('Invalid file type passed to SourceDocumentUploadService.');
        }

        // Create the database record
        return SourceDocument::create([
            'document_type' => $metadata['document_type'],
            'title' => $metadata['title'] ?? pathinfo($originalName, PATHINFO_FILENAME),
            'cohort' => $metadata['cohort'] ?? null,
            'effective_year' => $metadata['effective_year'] ?? null,
            'source_url' => $metadata['source_url'] ?? null,
            'file_path' => $savedPath,
            'mime_type' => $mimeType,
            'source_hash' => $sourceHash,
            'status' => 'uploaded',
            'uploaded_by' => $metadata['uploaded_by'] ?? null,
            'published_at' => null,
        ]);
    }
}
