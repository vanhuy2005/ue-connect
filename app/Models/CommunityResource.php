<?php

namespace App\Models;

use App\Enums\CommunityResourceStatus;
use App\Enums\CommunityResourceType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CommunityResource extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'community_id',
        'title',
        'description',
        'resource_type',
        'file_id',
        'url',
        'category',
        'copyright_attestation',
        'status',
        'submitted_by',
        'approved_by',
        'approved_at',
        'rejection_reason',
    ];

    protected $casts = [
        'resource_type' => CommunityResourceType::class,
        'status' => CommunityResourceStatus::class,
        'copyright_attestation' => 'boolean',
        'approved_at' => 'datetime',
    ];

    // ─── Relationships ───────────────────────────────────────────────────────

    public function community(): BelongsTo
    {
        return $this->belongsTo(Community::class);
    }

    public function submitter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /** File uploaded via legacy MediaFile (for document/image/template types). */
    public function mediaFile(): BelongsTo
    {
        return $this->belongsTo(MediaFile::class, 'file_id');
    }

    // ─── Scopes ──────────────────────────────────────────────────────────────

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', CommunityResourceStatus::Published->value);
    }

    public function scopePendingReview(Builder $query): Builder
    {
        return $query->where('status', CommunityResourceStatus::PendingReview->value);
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    public function isPublished(): bool
    {
        return $this->status === CommunityResourceStatus::Published;
    }
}
