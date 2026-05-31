<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;

class AuditService
{
    public function log(array $data): AuditLog
    {
        $actor = Auth::user();

        $payload = array_merge([
            'actor_id' => $actor?->id,
            'actor_type' => $actor ? 'user' : 'system',
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ], $data);

        return AuditLog::create($payload);
    }
}
