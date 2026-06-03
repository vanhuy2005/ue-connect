<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MediaVariant extends Model
{
    use HasFactory;

    protected $fillable = [
        'media_id',
        'variant_name',
        'provider',
        'disk',
        'path',
        'url',
        'cloudinary_public_id',
        'cloudinary_version',
        'cloudinary_secure_url',
        'cloudinary_format',
        'cloudinary_bytes',
        'cloudinary_resource_type',
        'cloudinary_synced_at',
        'cloudinary_sync_status',
        'cloudinary_error_code',
        'cloudinary_error_message',
        'mime_type',
        'size_bytes',
        'width',
        'height',
    ];

    protected $casts = [
        'media_id' => 'integer',
        'size_bytes' => 'integer',
        'width' => 'integer',
        'height' => 'integer',
        'cloudinary_version' => 'integer',
        'cloudinary_bytes' => 'integer',
        'cloudinary_synced_at' => 'datetime',
    ];

    /**
     * Get the parent media model.
     */
    public function media(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'media_id');
    }
}
