<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConversationUserSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'conversation_id',
        'user_id',
        'target_user_id',
        'nickname',
        'muted_until',
        'is_restricted',
        'deleted_at',
    ];

    protected function casts(): array
    {
        return [
            'conversation_id' => 'integer',
            'user_id' => 'integer',
            'target_user_id' => 'integer',
            'muted_until' => 'datetime',
            'is_restricted' => 'boolean',
            'deleted_at' => 'datetime',
        ];
    }

    /**
     * Get the conversation.
     *
     * @return BelongsTo<Conversation, $this>
     */
    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    /**
     * Get the settings owner/viewer.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the target user (the participant whose nickname is being overridden).
     *
     * @return BelongsTo<User, $this>
     */
    public function targetUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'target_user_id');
    }
}
