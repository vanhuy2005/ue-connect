<?php

namespace App\Models;

use App\Enums\CommunityMemberRole;
use App\Enums\CommunityMemberStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CommunityMember extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'community_id',
        'user_id',
        'role',
        'role_label',
        'status',
        'joined_at',
        'left_at',
        'removed_at',
        'removed_by',
        'remove_reason',
    ];

    protected $casts = [
        'role' => CommunityMemberRole::class,
        'status' => CommunityMemberStatus::class,
        'joined_at' => 'datetime',
        'left_at' => 'datetime',
        'removed_at' => 'datetime',
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

    public function removedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'removed_by');
    }

    // ─── Scopes ──────────────────────────────────────────────────────────────

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', CommunityMemberStatus::Active->value);
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', CommunityMemberStatus::Pending->value);
    }

    public function scopeByRole(Builder $query, CommunityMemberRole $role): Builder
    {
        return $query->where('role', $role->value);
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    public function isActive(): bool
    {
        return $this->status === CommunityMemberStatus::Active;
    }

    public function isBanned(): bool
    {
        return $this->status === CommunityMemberStatus::BannedFromCommunity;
    }

    public function isMuted(): bool
    {
        return $this->status === CommunityMemberStatus::Muted;
    }

    public function isRestricted(): bool
    {
        return $this->status === CommunityMemberStatus::Restricted;
    }

    public function canPost(): bool
    {
        return $this->status?->canPost() ?? false;
    }

    public function canSendChat(): bool
    {
        return $this->status?->canSendChat() ?? false;
    }

    public function isOwner(): bool
    {
        return $this->role === CommunityMemberRole::Owner;
    }

    public function isManagerOrAbove(): bool
    {
        return in_array($this->role, [CommunityMemberRole::Owner, CommunityMemberRole::Manager]);
    }

    public function isStaff(): bool
    {
        return $this->role?->isCommunityStaff() ?? false;
    }
}
