<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Request;

class AuditLogService
{
    /**
     * Create an append-only audit log entry.
     */
    public static function log(
        ?int $actorId,
        string $actorType,
        string $actionKey,
        string $targetType,
        ?int $targetId = null,
        ?string $contextType = null,
        ?int $contextId = null,
        ?array $beforeSnapshot = null,
        ?array $afterSnapshot = null,
        ?string $reason = null,
        ?array $metadata = null
    ): AuditLog {
        return AuditLog::create([
            'actor_id' => $actorId,
            'actor_type' => $actorType,
            'action_key' => $actionKey,
            'target_type' => $targetType,
            'target_id' => $targetId,
            'context_type' => $contextType,
            'context_id' => $contextId,
            'before_snapshot_json' => $beforeSnapshot,
            'after_snapshot_json' => $afterSnapshot,
            'reason' => $reason,
            'metadata_json' => $metadata,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'created_at' => now(),
        ]);
    }
}
