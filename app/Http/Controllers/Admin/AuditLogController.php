<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('view', AuditLog::class);

        $query = AuditLog::query()->with('actor')->latest();

        if ($request->filled('actor_id')) {
            $query->where('actor_id', $request->input('actor_id'));
        }

        if ($request->filled('action')) {
            $actionField = Schema::hasColumn('audit_logs', 'action_key') ? 'action_key' : 'action';
            $query->where($actionField, $request->input('action'));
        }

        $logs = $query->paginate(25)->withQueryString();

        return view('admin.audit-logs', ['logs' => $logs]);
    }
}
