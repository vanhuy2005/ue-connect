<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiStructuredQuery extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'question_id',
        'query_type',
        'filters_json',
        'result_count',
        'metadata_json',
        'created_at',
    ];

    protected $casts = [
        'filters_json' => 'array',
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
}
