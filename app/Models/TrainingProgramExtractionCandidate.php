<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrainingProgramExtractionCandidate extends Model
{
    public $timestamps = false;

    protected $table = 'training_program_extraction_candidates';

    protected $fillable = [
        'source_document_id',
        'field_name',
        'candidate_value',
        'confidence',
        'evidence_text',
        'page',
        'metadata_json',
    ];

    protected $casts = [
        'confidence' => 'double',
        'metadata_json' => 'array',
        'created_at' => 'datetime',
    ];

    /**
     * Get the source document associated with this candidate.
     */
    public function sourceDocument(): BelongsTo
    {
        return $this->belongsTo(SourceDocument::class, 'source_document_id');
    }
}
