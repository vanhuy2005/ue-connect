<?php

namespace App\Models;

use App\Enums\CommunityJoinRequestStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CommunityJoinRequest extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'community_id',
        'user_id',
        'join_reason',
        'status',
        'reviewed_by',
        'review_reason',
        'reviewed_at',
    ];

    protected $casts = [
        'status' => CommunityJoinRequestStatus::class,
        'reviewed_at' => 'datetime',
    ];

    // ─── Relationships ───────────────────────────────────────────────────────

    public function community(): BelongsTo
    {
        return $this->belongsTo(Community::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    // ─── Scopes ──────────────────────────────────────────────────────────────

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', CommunityJoinRequestStatus::Pending->value);
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    public function isPending(): bool
    {
        return $this->status === CommunityJoinRequestStatus::Pending;
    }
}
