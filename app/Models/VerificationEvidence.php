<?php

namespace App\Models;

use App\Enums\EvidenceCaptureMethod;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class VerificationEvidence extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'verification_evidences';

    protected $fillable = [
        'verification_request_id',
        'media_file_id',
        'evidence_type',
        'evidence_link',
        'user_note',
        'status',
        'review_note',
        'capture_method',
        'captured_at',
        'capture_session_id',
        'client_user_agent',
        'image_quality_score',
    ];

    /**
     * @return array<string, mixed>
     */
    protected function casts(): array
    {
        return [
            'capture_method' => EvidenceCaptureMethod::class,
            'captured_at' => 'datetime',
            'image_quality_score' => 'decimal:4',
        ];
    }

    /**
     * Get the verification request that owns this evidence.
     */
    public function verificationRequest(): BelongsTo
    {
        return $this->belongsTo(VerificationRequest::class);
    }

    /**
     * Get the uploaded media file for this evidence, if applicable.
     */
    public function mediaFile(): BelongsTo
    {
        return $this->belongsTo(MediaFile::class);
    }

    public function captureSession(): BelongsTo
    {
        return $this->belongsTo(EvidenceCaptureSession::class, 'capture_session_id');
    }

    public function analysisJobs(): HasMany
    {
        return $this->hasMany(EvidenceAnalysisJob::class);
    }

    public function latestAnalysisResult(): HasOne
    {
        return $this->hasOne(EvidenceAnalysisResult::class)->latestOfMany();
    }
}
