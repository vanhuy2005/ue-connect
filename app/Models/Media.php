<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Media extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'media';

    protected $fillable = [
        'uuid',
        'user_id',
        'mediable_type',
        'mediable_id',
        'collection',
        'primary_provider',
        'primary_disk',
        'primary_path',
        'delivery_provider',
        'delivery_url',
        'storage_strategy',
        'visibility',
        'original_filename',
        'mime_type',
        'extension',
        'size_bytes',
        'width',
        'height',
        'checksum_sha256',
        'status',
        'metadata_json',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'mediable_id' => 'integer',
        'size_bytes' => 'integer',
        'width' => 'integer',
        'height' => 'integer',
        'metadata_json' => 'array',
    ];

    protected static function booted(): void
    {
        static::creating(function (Media $media) {
            if (empty($media->uuid)) {
                $media->uuid = (string) Str::uuid();
            }
        });
    }

    /**
     * Get the user who uploaded the media.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the parent mediable model.
     */
    public function mediable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the variants of this media asset.
     */
    public function variants(): HasMany
    {
        return $this->hasMany(MediaVariant::class, 'media_id');
    }

    public function variant(string $name): ?MediaVariant
    {
        return $this->variants->firstWhere('variant_name', $name)
            ?? $this->variants()->where('variant_name', $name)->first();
    }

    /**
     * Check if the media asset is ready.
     */
    public function isReady(): bool
    {
        return $this->status === 'ready';
    }

    /**
     * Check if the media asset is private.
     */
    public function isPrivate(): bool
    {
        return $this->visibility === 'private';
    }

    /**
     * Get the storage provider name.
     * Maps primary_provider to provider for compatibility with MediaVariant.
     */
    public function getProviderAttribute(): ?string
    {
        return $this->primary_provider;
    }

    /**
     * Get the storage disk name.
     * Maps primary_disk to disk for compatibility with MediaVariant.
     */
    public function getDiskAttribute(): ?string
    {
        return $this->primary_disk;
    }

    /**
     * Get the storage path.
     * Maps primary_path to path for compatibility with MediaVariant.
     */
    public function getPathAttribute(): ?string
    {
        return $this->primary_path;
    }
}
