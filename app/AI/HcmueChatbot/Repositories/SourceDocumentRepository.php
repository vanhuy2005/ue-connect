<?php

namespace App\AI\HcmueChatbot\Repositories;

use App\Models\SourceDocument;
use Illuminate\Support\Collection;

class SourceDocumentRepository
{
    /**
     * Find document by ID.
     */
    public function find(int $id): ?SourceDocument
    {
        return SourceDocument::find($id);
    }

    /**
     * Get documents by type (e.g. student_handbook).
     */
    public function getByType(string $type): Collection
    {
        return SourceDocument::where('document_type', $type)->get();
    }

    /**
     * Find document by source hash.
     */
    public function findByHash(string $hash): ?SourceDocument
    {
        return SourceDocument::where('source_hash', $hash)->first();
    }

    /**
     * Get active/published documents.
     */
    public function getActiveDocuments(): Collection
    {
        return SourceDocument::where('status', 'active')->get();
    }
}
