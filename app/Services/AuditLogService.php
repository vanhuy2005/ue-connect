<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Schema;

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
        $availableColumns = Schema::hasTable('audit_logs') ? Schema::getColumnListing('audit_logs') : [];

        $logicalAttributes = [
            'actor_id' => $actorId,
            'actor_type' => $actorType,
            'action' => $actionKey,
            'target_type' => $targetType,
            'target_id' => $targetId,
            'context_type' => $contextType,
            'context_id' => $contextId,
            'before_values' => $beforeSnapshot,
            'after_values' => $afterSnapshot,
            'reason' => $reason,
            'metadata' => $metadata,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'created_at' => now(),
        ];

        $logicalFields = [
            'action',
            'action_key',
            'before_values',
            'after_values',
            'metadata',
        ];

        $attributes = [];

        foreach ($logicalAttributes as $field => $value) {
            if ($value === null) {
                continue;
            }

            if (in_array($field, $logicalFields, true)) {
                $attributes[$field] = $value;

                continue;
            }

            if (in_array($field, $availableColumns, true)) {
                $attributes[$field] = $value;
            }
        }

        return AuditLog::create($attributes);
    }
}
