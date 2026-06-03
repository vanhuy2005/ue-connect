<?php

namespace App\Models;

use App\Enums\MentorAccessStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MentorAccessRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'requested_role_context',
        'status',
        'motivation',
        'experience_summary',
        'expertise_topics',
        'career_paths',
        'evidence_media_id',
        'reviewed_by',
        'reviewed_at',
        'review_reason',
        'admin_notes',
    ];

    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
            'evidence_media_id' => 'integer',
            'reviewed_by' => 'integer',
            'status' => MentorAccessStatus::class,
            'expertise_topics' => 'array',
            'career_paths' => 'array',
            'reviewed_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<User, $this> */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function scopeSubmitted($query)
    {
        return $query->where('status', MentorAccessStatus::Submitted);
    }

    public function scopeUnderReview($query)
    {
        return $query->where('status', MentorAccessStatus::UnderReview);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', MentorAccessStatus::Approved);
    }

    public function scopeNeedMoreInfo($query)
    {
        return $query->where('status', MentorAccessStatus::NeedMoreInfo);
    }

    public function scopeRejected($query)
    {
        return $query->where('status', MentorAccessStatus::Rejected);
    }

    public function scopeRevoked($query)
    {
        return $query->where('status', MentorAccessStatus::Revoked);
    }

    public function isPending(): bool
    {
        return in_array($this->status, MentorAccessStatus::activeStatuses());
    }
}
