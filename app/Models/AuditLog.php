<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Schema;

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
        'action_key',
        'target_type',
        'target_id',
        'context_type',
        'context_id',
        'before_values',
        'before_snapshot_json',
        'after_values',
        'after_snapshot_json',
        'reason',
        'ip_address',
        'user_agent',
        'metadata',
        'metadata_json',
    ];

    protected $casts = [
        'before_values' => 'array',
        'after_values' => 'array',
        'metadata' => 'array',
    ];

    public function setActionAttribute(?string $value): void
    {
        if (Schema::hasColumn('audit_logs', 'action')) {
            $this->attributes['action'] = $value;
        }

        if (Schema::hasColumn('audit_logs', 'action_key')) {
            $this->attributes['action_key'] = $value;
        }
    }

    public function getActionAttribute(): ?string
    {
        return $this->attributes['action_key'] ?? $this->attributes['action'] ?? null;
    }

    public function setActionKeyAttribute(?string $value): void
    {
        if (Schema::hasColumn('audit_logs', 'action_key')) {
            $this->attributes['action_key'] = $value;
        }

        if (Schema::hasColumn('audit_logs', 'action')) {
            $this->attributes['action'] = $value;
        }
    }

    public function getBeforeValuesAttribute(): ?array
    {
        return $this->attributes['before_values'] ?? $this->attributes['before_snapshot_json'] ?? null;
    }

    public function setBeforeValuesAttribute(?array $value): void
    {
        $payload = $value === null ? null : json_encode($value, JSON_UNESCAPED_UNICODE);

        if (Schema::hasColumn('audit_logs', 'before_values')) {
            $this->attributes['before_values'] = $payload;
        }

        if (Schema::hasColumn('audit_logs', 'before_snapshot_json')) {
            $this->attributes['before_snapshot_json'] = $payload;
        }
    }

    public function getAfterValuesAttribute(): ?array
    {
        return $this->attributes['after_values'] ?? $this->attributes['after_snapshot_json'] ?? null;
    }

    public function setAfterValuesAttribute(?array $value): void
    {
        $payload = $value === null ? null : json_encode($value, JSON_UNESCAPED_UNICODE);

        if (Schema::hasColumn('audit_logs', 'after_values')) {
            $this->attributes['after_values'] = $payload;
        }

        if (Schema::hasColumn('audit_logs', 'after_snapshot_json')) {
            $this->attributes['after_snapshot_json'] = $payload;
        }
    }

    public function getMetadataAttribute(): ?array
    {
        return $this->attributes['metadata'] ?? $this->attributes['metadata_json'] ?? null;
    }

    public function setMetadataAttribute(?array $value): void
    {
        $payload = $value === null ? null : json_encode($value, JSON_UNESCAPED_UNICODE);

        if (Schema::hasColumn('audit_logs', 'metadata')) {
            $this->attributes['metadata'] = $payload;
        }

        if (Schema::hasColumn('audit_logs', 'metadata_json')) {
            $this->attributes['metadata_json'] = $payload;
        }
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
