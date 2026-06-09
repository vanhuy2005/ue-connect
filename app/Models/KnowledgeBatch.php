<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KnowledgeBatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'batch_key',
        'name',
        'root_path',
        'status',
        'total_files',
        'total_imported',
        'total_failed',
        'total_needs_ocr',
        'total_chunks',
        'total_vectors',
        'started_at',
        'finished_at',
        'metadata_json',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
        'metadata_json' => 'array',
    ];

    /**
     * Get the source documents associated with this batch.
     */
    public function sourceDocuments(): HasMany
    {
        return $this->hasMany(SourceDocument::class, 'knowledge_batch_id');
    }
}
