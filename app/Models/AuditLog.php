<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    use HasFactory;

    // Append-only table: do not update records; keep timestamps
    const UPDATED_AT = null;

    protected $table = 'audit_logs';

    protected $fillable = [
        'actor_id',
        'actor_type',
        'action',
        'target_type',
        'target_id',
        'before_values',
        'after_values',
        'reason',
        'ip_address',
        'user_agent',
        'metadata',
    ];

    protected $casts = [
        'before_values' => 'array',
        'after_values' => 'array',
        'metadata' => 'array',
    ];

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
