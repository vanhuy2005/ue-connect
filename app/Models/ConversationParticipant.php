<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConversationParticipant extends Model
{
    use HasFactory;

    protected $fillable = [
        'conversation_id',
        'user_id',
        'participant_role',
        'status',
        'joined_at',
        'left_at',
        'last_read_at',
        'muted_until',
    ];

    protected function casts(): array
    {
        return [
            'conversation_id' => 'integer',
            'user_id' => 'integer',
            'joined_at' => 'datetime',
            'left_at' => 'datetime',
            'last_read_at' => 'datetime',
            'muted_until' => 'datetime',
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
     * Get the user.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
