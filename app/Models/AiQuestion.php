<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class AiQuestion extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'session_id',
        'user_id',
        'original_question',
        'normalized_question',
        'intent',
        'source_route',
        'confidence',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    /**
     * Get the chat session.
     */
    public function session(): BelongsTo
    {
        return $this->belongsTo(ChatSession::class, 'session_id');
    }

    /**
     * Get the user.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the AI answer.
     */
    public function answer(): HasOne
    {
        return $this->hasOne(AiAnswer::class, 'question_id');
    }

    /**
     * Get retrieved chunks.
     */
    public function retrievedChunks(): HasMany
    {
        return $this->hasMany(AiRetrievedChunk::class, 'question_id');
    }

    /**
     * Get structured queries plan log.
     */
    public function structuredQueries(): HasMany
    {
        return $this->hasMany(AiStructuredQuery::class, 'question_id');
    }
}
