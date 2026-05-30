<?php

namespace App\Models;

use App\Enums\MessageStatus;
use App\Enums\MessageType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Message extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'conversation_id',
        'sender_id',
        'body',
        'message_type',
        'status',
        'shared_post_id',
        'metadata_json',
        'edited_at',
        'reply_to_message_id',
        'forwarded_from_message_id',
        'recalled_at',
        'recalled_by',
    ];

    protected function casts(): array
    {
        return [
            'conversation_id' => 'integer',
            'sender_id' => 'integer',
            'shared_post_id' => 'integer',
            'reply_to_message_id' => 'integer',
            'forwarded_from_message_id' => 'integer',
            'recalled_by' => 'integer',
            'message_type' => MessageType::class,
            'status' => MessageStatus::class,
            'metadata_json' => 'array',
            'edited_at' => 'datetime',
            'recalled_at' => 'datetime',
        ];
    }

    /**
     * Helper to check if message is recalled.
     */
    public function isRecalled(): bool
    {
        return $this->recalled_at !== null;
    }

    /**
     * Helper to check if message is visible to participants.
     */
    public function isVisibleToParticipants(): bool
    {
        return ! $this->isRecalled();
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
     * Get the sender.
     *
     * @return BelongsTo<User, $this>
     */
    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /**
     * Get the shared post.
     *
     * @return BelongsTo<Post, $this>
     */
    public function sharedPost(): BelongsTo
    {
        return $this->belongsTo(Post::class, 'shared_post_id')->withTrashed();
    }

    /**
     * Get the message being replied to.
     *
     * @return BelongsTo<Message, $this>
     */
    public function replyTo(): BelongsTo
    {
        return $this->belongsTo(Message::class, 'reply_to_message_id');
    }

    /**
     * Get the replies of this message.
     *
     * @return HasMany<Message, $this>
     */
    public function replies(): HasMany
    {
        return $this->hasMany(Message::class, 'reply_to_message_id');
    }

    /**
     * Get the original message this was forwarded from.
     *
     * @return BelongsTo<Message, $this>
     */
    public function forwardedFrom(): BelongsTo
    {
        return $this->belongsTo(Message::class, 'forwarded_from_message_id');
    }

    /**
     * Get the user who recalled this message.
     *
     * @return BelongsTo<User, $this>
     */
    public function recalledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recalled_by');
    }
}
