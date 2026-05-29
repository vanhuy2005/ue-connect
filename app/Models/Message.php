<?php

namespace App\Models;

use App\Enums\MessageStatus;
use App\Enums\MessageType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
    ];

    protected function casts(): array
    {
        return [
            'message_type' => MessageType::class,
            'status' => MessageStatus::class,
            'metadata_json' => 'array',
            'edited_at' => 'datetime',
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
}
