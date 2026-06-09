<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiRetrievedChunk extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'question_id',
        'document_chunk_id',
        'score',
        'rerank_score',
        'metadata_json',
        'created_at',
    ];

    protected $casts = [
        'metadata_json' => 'array',
        'created_at' => 'datetime',
    ];

    /**
     * Get the question.
     */
    public function question(): BelongsTo
    {
        return $this->belongsTo(AiQuestion::class, 'question_id');
    }

    /**
     * Get the document chunk.
     */
    public function chunk(): BelongsTo
    {
        return $this->belongsTo(DocumentChunk::class, 'document_chunk_id');
    }
}
