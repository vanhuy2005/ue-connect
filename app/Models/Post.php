<?php

namespace App\Models;

use App\Enums\PostStatus;
use App\Enums\PostVisibility;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Post extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'scope_type',
        'scope_id',
        'community_post_type',
        'pinned_at',
        'pinned_by',
        'body',
        'media_url',
        'visibility',
        'status',
        'edited_at',
        'published_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'visibility' => PostVisibility::class,
            'status' => PostStatus::class,
            'edited_at' => 'datetime',
            'published_at' => 'datetime',
            'pinned_at' => 'datetime',
        ];
    }

    /**
     * Scope posts for a specific community feed.
     */
    public function scopeInCommunity(Builder $query, int $communityId): Builder
    {
        return $query->where('scope_type', 'community')->where('scope_id', $communityId);
    }

    /**
     * Scope posts for the global home feed (no community scope).
     */
    public function scopeHomeFeed(Builder $query): Builder
    {
        return $query->whereNull('scope_type');
    }

    /**
     * Relationship to the user who pinned this post.
     */
    public function pinnedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pinned_by');
    }

    /**
     * Get the author of the post.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the comments of the post.
     *
     * @return HasMany<Comment, $this>
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * Get polymorphic media assets for this post.
     */
    public function media(): MorphMany
    {
        return $this->morphMany(Media::class, 'mediable');
    }

    public function images(): MorphMany
    {
        return $this->media()->where('collection', 'post_image');
    }

    /**
     * Get the likes of the post.
     *
     * @return HasMany<PostLike, $this>
     */
    public function likes(): HasMany
    {
        return $this->hasMany(PostLike::class);
    }

    /**
     * Get the saves of the post.
     *
     * @return HasMany<PostSave, $this>
     */
    public function saves(): HasMany
    {
        return $this->hasMany(PostSave::class);
    }

    /**
     * Get the hide exclusions of the post.
     *
     * @return HasMany<PostHide, $this>
     */
    public function hides(): HasMany
    {
        return $this->hasMany(PostHide::class);
    }
}
