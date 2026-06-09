<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiFeedback extends Model
{
    protected $table = 'ai_feedback';

    public $timestamps = false;

    protected $fillable = [
        'answer_id',
        'user_id',
        'rating',
        'comment',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    /**
     * Get the answer.
     */
    public function answer(): BelongsTo
    {
        return $this->belongsTo(AiAnswer::class, 'answer_id');
    }

    /**
     * Get the user who gave feedback.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
