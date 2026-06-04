<?php

namespace App\Models;

use App\Enums\CommunityJoinPolicy;
use App\Enums\CommunityMemberStatus;
use App\Enums\CommunityStatus;
use App\Enums\CommunityType;
use App\Enums\CommunityVisibility;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Community extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'type',
        'description',
        'short_description',
        'visibility',
        'join_policy',
        'status',
        'owner_id',
        'created_by',
        'related_faculty',
        'related_program_id',
        'rules',
        'settings',
        'members_count',
        'post_count',
        'resource_count',
        'suspended_reason',
        'suspended_safe_reason',
        'suspended_at',
        'archived_at',
    ];

    protected $casts = [
        'settings' => 'array',
        'type' => CommunityType::class,
        'status' => CommunityStatus::class,
        'visibility' => CommunityVisibility::class,
        'join_policy' => CommunityJoinPolicy::class,
        'suspended_at' => 'datetime',
        'archived_at' => 'datetime',
    ];

    // ─── Relationships ───────────────────────────────────────────────────────

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function members(): HasMany
    {
        return $this->hasMany(CommunityMember::class);
    }

    public function activeMembers(): HasMany
    {
        return $this->members()->where('status', CommunityMemberStatus::Active->value);
    }

    public function joinRequests(): HasMany
    {
        return $this->hasMany(CommunityJoinRequest::class);
    }

    public function pendingJoinRequests(): HasMany
    {
        return $this->joinRequests()->where('status', 'pending');
    }

    public function resources(): HasMany
    {
        return $this->hasMany(CommunityResource::class);
    }

    public function publishedResources(): HasMany
    {
        return $this->resources()->where('status', 'published');
    }

    public function suggestions(): HasMany
    {
        return $this->hasMany(CommunitySuggestion::class, 'converted_community_id');
    }

    public function events(): HasMany
    {
        return $this->hasMany(CommunityEvent::class);
    }

    public function relatedProgram(): BelongsTo
    {
        return $this->belongsTo(AcademicProgram::class, 'related_program_id');
    }

    /**
     * Community posts via scoped Post morph.
     */
    public function posts(): HasMany
    {
        return $this->hasMany(Post::class, 'scope_id')
            ->where('scope_type', 'community');
    }

    /**
     * Polymorphic media (avatar, cover) via Media morph.
     */
    public function media(): MorphMany
    {
        return $this->morphMany(Media::class, 'mediable');
    }

    public function avatar(): MorphMany
    {
        return $this->media()->where('collection', 'community_avatar');
    }

    public function cover(): MorphMany
    {
        return $this->media()->where('collection', 'community_cover');
    }

    /**
     * The conversation used as community chat channel.
     */
    public function chatConversation(): HasOne
    {
        return $this->hasOne(Conversation::class, 'source_id')
            ->where('source_type', 'community');
    }

    // ─── Scopes ──────────────────────────────────────────────────────────────

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', CommunityStatus::Active->value);
    }

    public function scopeDiscoverable(Builder $query): Builder
    {
        return $query->whereIn('status', [
            CommunityStatus::Active->value,
            CommunityStatus::Inactive->value,
        ])->whereNotIn('visibility', [
            CommunityVisibility::Hidden->value,
        ]);
    }

    public function scopeByType(Builder $query, string|CommunityType $type): Builder
    {
        $value = $type instanceof CommunityType ? $type->value : $type;

        return $query->where('type', $value);
    }

    public function scopeByVisibility(Builder $query, string|CommunityVisibility $visibility): Builder
    {
        $value = $visibility instanceof CommunityVisibility ? $visibility->value : $visibility;

        return $query->where('visibility', $value);
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    public function isActive(): bool
    {
        return $this->status === CommunityStatus::Active;
    }

    public function isSuspended(): bool
    {
        return $this->status === CommunityStatus::Suspended;
    }

    public function isArchived(): bool
    {
        return $this->status === CommunityStatus::Archived;
    }

    public function isOperational(): bool
    {
        return $this->status?->isOperational() ?? false;
    }

    public function allowsJoin(): bool
    {
        if (! $this->isActive()) {
            return false;
        }

        return ! in_array($this->join_policy, [
            CommunityJoinPolicy::Closed,
            CommunityJoinPolicy::AdminOnly,
            CommunityJoinPolicy::InviteOnly,
        ]);
    }

    public function requiresApproval(): bool
    {
        return $this->join_policy === CommunityJoinPolicy::ApprovalRequired;
    }

    /**
     * Resolve the membership status for a given user.
     */
    public function membershipFor(User $user): ?CommunityMember
    {
        return $this->members()->where('user_id', $user->id)->first();
    }

    /**
     * Whether a user is an active member of this community.
     */
    public function hasMember(User $user): bool
    {
        return $this->members()
            ->where('user_id', $user->id)
            ->where('status', CommunityMemberStatus::Active->value)
            ->exists();
    }

    /**
     * Whether a user is the community owner.
     */
    public function isOwnedBy(User $user): bool
    {
        return (int) $this->owner_id === (int) $user->id;
    }

    /**
     * Whether a user has manage_community_members scoped permission or owner role.
     */
    public function canManagedBy(User $user): bool
    {
        if ($this->isOwnedBy($user)) {
            return true;
        }

        return PermissionGrant::where('user_id', $user->id)
            ->where('scope_type', 'community')
            ->where('scope_id', $this->id)
            ->where('status', 'active')
            ->whereIn('permission_key', ['manage_community_members', 'manage_community'])
            ->exists();
    }
}
