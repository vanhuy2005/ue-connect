<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VerificationReviewAction extends Model
{
    use HasFactory;

    // Append-only table: only has created_at
    const UPDATED_AT = null;

    protected $fillable = [
        'verification_request_id',
        'admin_id',
        'action_key',
        'reason',
        'instruction',
        'before_snapshot_json',
        'after_snapshot_json',
    ];

    protected $casts = [
        'before_snapshot_json' => 'array',
        'after_snapshot_json' => 'array',
    ];

    /**
     * Get the verification request that was reviewed.
     */
    public function verificationRequest(): BelongsTo
    {
        return $this->belongsTo(VerificationRequest::class);
    }

    /**
     * Get the admin who performed this action.
     */
    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
}
