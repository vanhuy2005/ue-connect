<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PermissionGrant extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'permission_grants';

    protected $fillable = [
        'user_id',
        'permission_key',
        'scope_type',
        'scope_id',
        'granted_by',
        'revoked_by',
        'reason',
        'starts_at',
        'expires_at',
        'status',
        'revoked_at',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'revoked_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function granter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'granted_by');
    }
}
