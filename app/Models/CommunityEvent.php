<?php

namespace App\Models;

use App\Enums\CommunityEventRsvpStatus;
use App\Enums\CommunityEventStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CommunityEvent extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'community_id',
        'created_by',
        'title',
        'slug',
        'description',
        'event_type',
        'status',
        'visibility',
        'starts_at',
        'ends_at',
        'location',
        'online_link',
        'rsvp_required',
        'rsvp_deadline',
        'capacity',
        'waitlist_enabled',
        'going_count',
        'interested_count',
        'waitlist_count',
        'cancelled_reason',
        'cancelled_at',
    ];

    protected $casts = [
        'status' => CommunityEventStatus::class,
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'rsvp_deadline' => 'datetime',
        'cancelled_at' => 'datetime',
        'rsvp_required' => 'boolean',
        'waitlist_enabled' => 'boolean',
    ];

    // ─── Relationships ───────────────────────────────────────────────────────

    public function community(): BelongsTo
    {
        return $this->belongsTo(Community::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function rsvps(): HasMany
    {
        return $this->hasMany(CommunityEventRsvp::class, 'event_id');
    }

    public function attendees(): HasMany
    {
        return $this->rsvps()->where('status', CommunityEventRsvpStatus::Going->value);
    }

    // ─── Scopes ──────────────────────────────────────────────────────────────

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', CommunityEventStatus::Published->value);
    }

    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->where('starts_at', '>=', now());
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    public function isPublished(): bool
    {
        return $this->status === CommunityEventStatus::Published;
    }

    public function isCancelled(): bool
    {
        return $this->status === CommunityEventStatus::Cancelled;
    }

    public function hasCapacity(): bool
    {
        if ($this->capacity === null) {
            return true;
        }

        return $this->going_count < $this->capacity;
    }

    public function isAtCapacity(): bool
    {
        return ! $this->hasCapacity();
    }
}
