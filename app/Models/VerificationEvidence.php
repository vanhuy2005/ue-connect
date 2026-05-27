<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
    ];

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
}
