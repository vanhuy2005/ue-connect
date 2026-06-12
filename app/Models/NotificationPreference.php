<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class NotificationPreference extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'in_app_enabled',
        'browser_push_enabled',
        'email_enabled',
        'greeting_notifications',
        'message_notifications',
        'mentor_notifications',
        'community_notifications',
        'safety_notifications',
        'moderation_notifications',
        'system_notifications',
        'push_messages_enabled',
        'push_greetings_enabled',
        'push_mentor_enabled',
        'push_community_enabled',
        'push_verification_enabled',
        'push_admin_announcements_enabled',
        'push_mentions',
        'push_comments',
        'push_connections',
        'push_messages',
        'push_system',
        'email_mentions',
        'email_comments',
        'email_connections',
        'email_messages',
        'email_system',
        'email_marketing',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'in_app_enabled' => 'boolean',
        'browser_push_enabled' => 'boolean',
        'email_enabled' => 'boolean',
        'greeting_notifications' => 'boolean',
        'message_notifications' => 'boolean',
        'mentor_notifications' => 'boolean',
        'community_notifications' => 'boolean',
        'safety_notifications' => 'boolean',
        'moderation_notifications' => 'boolean',
        'system_notifications' => 'boolean',
        'push_messages_enabled' => 'boolean',
        'push_greetings_enabled' => 'boolean',
        'push_mentor_enabled' => 'boolean',
        'push_community_enabled' => 'boolean',
        'push_verification_enabled' => 'boolean',
        'push_admin_announcements_enabled' => 'boolean',
        'push_mentions' => 'boolean',
        'push_comments' => 'boolean',
        'push_connections' => 'boolean',
        'push_messages' => 'boolean',
        'push_system' => 'boolean',
        'email_mentions' => 'boolean',
        'email_comments' => 'boolean',
        'email_connections' => 'boolean',
        'email_messages' => 'boolean',
        'email_system' => 'boolean',
        'email_marketing' => 'boolean',
    ];

    /**
     * Get the user that owns these notification preferences.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
