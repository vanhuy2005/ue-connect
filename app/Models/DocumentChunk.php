<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentChunk extends Model
{
    use HasFactory;

    protected $fillable = [
        'source_document_id',
        'chunk_index',
        'chunk_text',
        'token_count',
        'page_start',
        'page_end',
        'part',
        'chapter',
        'section',
        'article',
        'clause',
        'metadata_json',
        'embedding_status',
        'vector_id',
    ];

    protected $casts = [
        'metadata_json' => 'array',
    ];

    /**
     * Get the source document.
     */
    public function document(): BelongsTo
    {
        return $this->belongsTo(SourceDocument::class, 'source_document_id');
    }
}
