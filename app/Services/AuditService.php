<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class AuditService
{
    public function log(array $data): AuditLog
    {
        $actor = Auth::user();

        $logicalPayload = array_merge([
            'actor_id' => $actor?->id,
            'actor_type' => $actor ? 'user' : 'system',
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ], $data);

        $availableColumns = Schema::hasTable('audit_logs') ? Schema::getColumnListing('audit_logs') : [];
        $logicalFields = [
            'action',
            'action_key',
            'before_values',
            'after_values',
            'metadata',
        ];

        $payload = [];

        foreach ($logicalPayload as $field => $value) {
            if ($value === null) {
                continue;
            }

            if (in_array($field, $logicalFields, true)) {
                $payload[$field] = $value;
                continue;
            }

            if (in_array($field, $availableColumns, true)) {
                $payload[$field] = $value;
            }
        }

        return AuditLog::create($payload);
    }
}
