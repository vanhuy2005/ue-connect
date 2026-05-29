<?php

namespace App\Models;

use App\Enums\EvidenceCaptureStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class EvidenceCaptureSession extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'verification_request_id',
        'session_token_hash',
        'status',
        'required_evidence_type',
        'started_at',
        'expires_at',
        'completed_at',
        'failed_at',
        'attempt_count',
        'client_user_agent',
    ];

    /**
     * @return array<string, mixed>
     */
    protected function casts(): array
    {
        return [
            'status' => EvidenceCaptureStatus::class,
            'started_at' => 'datetime',
            'expires_at' => 'datetime',
            'completed_at' => 'datetime',
            'failed_at' => 'datetime',
            'attempt_count' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function verificationRequest(): BelongsTo
    {
        return $this->belongsTo(VerificationRequest::class);
    }

    public function verificationEvidence(): HasOne
    {
        return $this->hasOne(VerificationEvidence::class, 'capture_session_id');
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function isActive(): bool
    {
        return $this->status === EvidenceCaptureStatus::Started && ! $this->isExpired();
    }

    public function canAttempt(): bool
    {
        return $this->isActive()
            && $this->attempt_count < config('ai-verification.capture.max_attempts', 5);
    }
}
