<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    use HasFactory;

    // Append-only table: only has created_at
    const UPDATED_AT = null;

    protected $fillable = [
        'actor_id',
        'actor_type',
        'action_key',
        'target_type',
        'target_id',
        'context_type',
        'context_id',
        'before_snapshot_json',
        'after_snapshot_json',
        'reason',
        'metadata_json',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'before_snapshot_json' => 'array',
        'after_snapshot_json' => 'array',
        'metadata_json' => 'array',
    ];

    /**
     * Get the user/actor who triggered this audit log.
     */
    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
