<?php

namespace App\Models;

use App\Enums\ConversationStatus;
use App\Enums\ConversationType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Conversation extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'conversation_type',
        'status',
        'created_by',
        'direct_user_low_id',
        'direct_user_high_id',
        'last_message_id',
        'last_message_at',
        'mentor_request_id',
    ];

    protected function casts(): array
    {
        return [
            'created_by' => 'integer',
            'direct_user_low_id' => 'integer',
            'direct_user_high_id' => 'integer',
            'last_message_id' => 'integer',
            'conversation_type' => ConversationType::class,
            'status' => ConversationStatus::class,
            'last_message_at' => 'datetime',
        ];
    }

    /**
     * Get the creator.
     *
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get participants.
     *
     * @return HasMany<ConversationParticipant, $this>
     */
    public function participants(): HasMany
    {
        return $this->hasMany(ConversationParticipant::class);
    }

    /**
     * Get messages.
     *
     * @return HasMany<Message, $this>
     */
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    /**
     * Helper to get the direct chat recipient for a given user.
     */
    public function getRecipientFor(User $user): ?User
    {
        $participant = $this->participants
            ->first(function ($p) use ($user) {
                return (int) $p->user_id !== (int) $user->id;
            });

        return $participant ? $participant->user : null;
    }

    /**
     * Get the conversation pinned messages.
     *
     * @return HasMany<ConversationPinnedMessage, $this>
     */
    public function pinnedMessages(): HasMany
    {
        return $this->hasMany(ConversationPinnedMessage::class);
    }

    /**
     * Get the conversation user settings.
     *
     * @return HasMany<ConversationUserSetting, $this>
     */
    public function conversationUserSettings(): HasMany
    {
        return $this->hasMany(ConversationUserSetting::class);
    }

    /**
     * Get or create conversation user settings for a given user.
     */
    public function getUserSettingsFor(User $user): ConversationUserSetting
    {
        return $this->conversationUserSettings()->firstOrCreate([
            'user_id' => $user->id,
        ], [
            'nickname' => null,
            'muted_until' => null,
            'is_restricted' => false,
            'deleted_at' => null,
        ]);
    }

    /**
     * Get the mentor request that originated this conversation, if any.
     */
    public function mentorRequest(): BelongsTo
    {
        return $this->belongsTo(MentorRequest::class);
    }
}
