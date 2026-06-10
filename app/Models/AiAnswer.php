<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AiAnswer extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'question_id',
        'answer_text',
        'model_provider',
        'model_name',
        'prompt_version',
        'latency_ms',
        'input_tokens',
        'output_tokens',
        'total_tokens',
        'status',
        'created_at',
    ];

    protected $casts = [
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
     * Get the feedbacks.
     */
    public function feedbacks(): HasMany
    {
        return $this->hasMany(AiFeedback::class, 'answer_id');
    }
}
