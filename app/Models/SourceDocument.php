<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SourceDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'document_type',
        'title',
        'cohort',
        'effective_year',
        'source_url',
        'file_path',
        'mime_type',
        'source_hash',
        'status',
        'uploaded_by',
        'published_at',
        'knowledge_batch_id',
        'knowledge_batch_key',
    ];

    protected $casts = [
        'published_at' => 'datetime',
    ];

    /**
     * Get the knowledge batch that owns this document.
     */
    public function knowledgeBatch(): BelongsTo
    {
        return $this->belongsTo(KnowledgeBatch::class, 'knowledge_batch_id');
    }

    /**
     * Get the user who uploaded the document.
     */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * Get the chunks for this source document.
     */
    public function chunks(): HasMany
    {
        return $this->hasMany(DocumentChunk::class, 'source_document_id');
    }

    /**
     * Get the first chunk for metadata prefilling.
     */
    public function firstChunk()
    {
        return $this->hasOne(DocumentChunk::class, 'source_document_id')->oldestOfMany();
    }
}
