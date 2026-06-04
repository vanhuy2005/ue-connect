<?php

namespace App\Models;

use App\Enums\CommunitySuggestionStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CommunitySuggestion extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'submitted_by',
        'suggested_name',
        'community_type',
        'join_policy',
        'visibility',
        'purpose',
        'target_members',
        'rules',
        'related_faculty',
        'related_program_id',
        'proposed_owner_id',
        'status',
        'admin_instruction',
        'admin_reason',
        'reviewed_by',
        'converted_community_id',
    ];

    protected $casts = [
        'status' => CommunitySuggestionStatus::class,
    ];

    // ─── Relationships ───────────────────────────────────────────────────────

    public function submitter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function proposedOwner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'proposed_owner_id');
    }

    public function convertedCommunity(): BelongsTo
    {
        return $this->belongsTo(Community::class, 'converted_community_id');
    }

    // ─── Scopes ──────────────────────────────────────────────────────────────

    public function scopePending(Builder $query): Builder
    {
        return $query->whereIn('status', [
            CommunitySuggestionStatus::Submitted->value,
            CommunitySuggestionStatus::UnderReview->value,
            CommunitySuggestionStatus::NeedMoreInformation->value,
        ]);
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    public function isPending(): bool
    {
        return in_array($this->status, [
            CommunitySuggestionStatus::Submitted,
            CommunitySuggestionStatus::UnderReview,
            CommunitySuggestionStatus::NeedMoreInformation,
        ]);
    }
}
