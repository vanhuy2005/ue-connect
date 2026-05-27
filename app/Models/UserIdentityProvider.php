<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserIdentityProvider extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'provider_name',
        'provider_user_id',
        'provider_tenant_id',
        'provider_email',
        'linked_at',
        'last_login_at',
    ];

    protected $casts = [
        'linked_at' => 'datetime',
        'last_login_at' => 'datetime',
    ];

    /**
     * Get the user that owns this identity provider.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
