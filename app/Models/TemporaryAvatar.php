<?php

namespace App\Models;

use Database\Factories\TemporaryAvatarFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TemporaryAvatar extends Model
{
    /** @use HasFactory<TemporaryAvatarFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'previous_media_id',
        'current_media_id',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
            'previous_media_id' => 'integer',
            'current_media_id' => 'integer',
            'expires_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<Media, $this>
     */
    public function previousMedia(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'previous_media_id');
    }

    /**
     * @return BelongsTo<Media, $this>
     */
    public function currentMedia(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'current_media_id');
    }
}
