<?php

namespace App\Models;

use App\Enums\EvidenceAnalysisRecommendation;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EvidenceAnalysisResult extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'analysis_job_id',
        'verification_request_id',
        'verification_evidence_id',
        'document_type_detected',
        'document_type_confidence',
        'ocr_text',
        'extracted_fields_json',
        'match_result_json',
        'risk_flags_json',
        'confidence_score',
        'recommendation',
        'review_summary',
    ];

    /**
     * @return array<string, mixed>
     */
    protected function casts(): array
    {
        return [
            'extracted_fields_json' => 'array',
            'match_result_json' => 'array',
            'risk_flags_json' => 'array',
            'confidence_score' => 'decimal:4',
            'document_type_confidence' => 'decimal:4',
            'recommendation' => EvidenceAnalysisRecommendation::class,
        ];
    }

    public function analysisJob(): BelongsTo
    {
        return $this->belongsTo(EvidenceAnalysisJob::class, 'analysis_job_id');
    }

    public function verificationRequest(): BelongsTo
    {
        return $this->belongsTo(VerificationRequest::class);
    }

    public function verificationEvidence(): BelongsTo
    {
        return $this->belongsTo(VerificationEvidence::class);
    }
}
