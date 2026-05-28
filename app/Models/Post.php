<?php

namespace App\Models;

use App\Enums\PostStatus;
use App\Enums\PostVisibility;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
        'body',
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
        ];
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
}
