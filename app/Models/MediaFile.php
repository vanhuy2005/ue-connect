<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class MediaFile extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'owner_id',
        'disk',
        'path',
        'original_name',
        'mime_type',
        'extension',
        'size_bytes',
        'visibility',
        'file_category',
        'checksum',
        'metadata_json',
    ];

    protected $casts = [
        'metadata_json' => 'array',
        'size_bytes' => 'integer',
    ];

    /**
     * Get the user who owns this media file.
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }
}
