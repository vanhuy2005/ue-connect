<?php

namespace App\AI\HcmueChatbot\Repositories;

use App\Models\DocumentChunk;
use Illuminate\Support\Collection;

class DocumentChunkRepository
{
    /**
     * Find chunk by ID.
     */
    public function find(int $id): ?DocumentChunk
    {
        return DocumentChunk::find($id);
    }

    /**
     * Get chunks belonging to a document.
     */
    public function getByDocument(int $documentId): Collection
    {
        return DocumentChunk::where('source_document_id', $documentId)
            ->orderBy('chunk_index')
            ->get();
    }

    /**
     * Save a chunk.
     */
    public function create(array $data): DocumentChunk
    {
        return DocumentChunk::create($data);
    }

    /**
     * Update embedding status.
     */
    public function updateEmbeddingStatus(int $chunkId, string $status, ?string $vectorId = null): bool
    {
        return DocumentChunk::where('id', $chunkId)->update([
            'embedding_status' => $status,
            'vector_id' => $vectorId,
        ]) > 0;
    }
}
