<?php

namespace App\Models;

use App\Enums\EvidenceAnalysisStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class EvidenceAnalysisJob extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'verification_request_id',
        'verification_evidence_id',
        'media_file_id',
        'provider',
        'model_name',
        'status',
        'attempt_count',
        'queued_at',
        'started_at',
        'finished_at',
        'failed_at',
        'error_code',
        'error_message',
    ];

    /**
     * @return array<string, mixed>
     */
    protected function casts(): array
    {
        return [
            'status' => EvidenceAnalysisStatus::class,
            'attempt_count' => 'integer',
            'queued_at' => 'datetime',
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
            'failed_at' => 'datetime',
        ];
    }

    public function verificationRequest(): BelongsTo
    {
        return $this->belongsTo(VerificationRequest::class);
    }

    public function verificationEvidence(): BelongsTo
    {
        return $this->belongsTo(VerificationEvidence::class);
    }

    public function mediaFile(): BelongsTo
    {
        return $this->belongsTo(MediaFile::class);
    }

    public function result(): HasOne
    {
        return $this->hasOne(EvidenceAnalysisResult::class, 'analysis_job_id');
    }
}
