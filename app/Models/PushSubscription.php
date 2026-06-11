<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PushSubscription extends Model
{
    protected $fillable = [
        'user_id',
        'endpoint',
        'public_key',
        'auth_token',
        'content_encoding',
        'user_agent',
        'browser_name',
        'device_name',
        'failed_attempts',
        'last_used_at',
        'revoked_at',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'failed_attempts' => 'integer',
        'last_used_at' => 'datetime',
        'revoked_at' => 'datetime',
    ];

    /**
     * Get the user that owns the subscription.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope a query to only include active subscriptions.
     */
    public function scopeActive($query)
    {
        return $query->whereNull('revoked_at');
    }

    /**
     * Scope a query to only include subscriptions for a specific user.
     */
    public function scopeForUser($query, User $user)
    {
        return $query->where('user_id', $user->id);
    }

    /**
     * Mark the subscription as used.
     */
    public function markUsed(): void
    {
        $this->update(['last_used_at' => now(), 'failed_attempts' => 0]);
    }

    /**
     * Mark the subscription as failed.
     */
    public function markFailed(): void
    {
        $this->increment('failed_attempts');
    }

    /**
     * Revoke the subscription.
     */
    public function revoke(): void
    {
        $this->update(['revoked_at' => now()]);
    }
}
