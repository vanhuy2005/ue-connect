<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AuditService;
use App\Services\SystemSettingsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SystemSettingsController extends Controller
{
    public function index(SystemSettingsService $svc)
    {
        $stored = $svc->getAll();

        $defaults = [
            'app_name' => config('app.name'),
            'app_env' => config('app.env'),
            'app_url' => config('app.url'),
            'timezone' => config('app.timezone'),
            'queue_driver' => config('queue.default'),
            'mail_driver' => config('mail.default'),
            'broadcasting' => config('broadcasting.default'),
            'session_driver' => config('session.driver'),
        ];

        $settings = array_merge($defaults, $stored);

        return view('admin.system-settings', [
            'settings' => $settings,
            'snapshots' => $svc->listSnapshots(),
        ]);
    }

    public function update(Request $request, SystemSettingsService $svc, AuditService $audit)
    {
        $data = $request->validate([
            'app_name' => 'required|string|max:255',
            'app_env' => 'required|string|max:50',
            'app_url' => 'required|url|max:1000',
            'timezone' => 'required|string|max:100',
            'queue_driver' => 'nullable|string|max:100',
            'mail_driver' => 'nullable|string|max:100',
            'broadcasting' => 'nullable|string|max:100',
            'session_driver' => 'nullable|string|max:100',
            'reason' => 'required|string|min:10|max:2000',
        ]);

        $reason = $data['reason'];
        unset($data['reason']);

        // Persist editable settings to storage
        $before = $svc->getAll();
        $svc->setAll($data);

        // Audit
        $audit->log([
            'action' => 'update_system_settings',
            'target_type' => 'system_settings',
            'target_id' => null,
            'before_values' => $before,
            'after_values' => $data,
            'reason' => $reason,
        ]);

        return redirect()->route('admin.system-settings.index')->with('status', 'Cài đặt đã được lưu.');
    }

    public function saveSnapshot(Request $request, SystemSettingsService $svc, AuditService $audit)
    {
        $data = $request->validate([
            'name' => 'nullable|string|max:100',
            'reason' => 'required|string|min:10|max:2000',
        ]);

        $name = $data['name'] ?? null;
        $file = $svc->saveSnapshot($name);

        $audit->log([
            'action' => 'create_system_settings_snapshot',
            'target_type' => 'system_settings_snapshot',
            'target_id' => $file,
            'metadata' => ['snapshot_name' => $name],
            'reason' => $data['reason'],
        ]);

        return redirect()->route('admin.system-settings.index')->with('status', "Snapshot '$file' đã được tạo.");
    }

    public function restoreSnapshot(Request $request, SystemSettingsService $svc, AuditService $audit)
    {
        $data = $request->validate([
            'snapshot' => 'required|string',
            'reason' => 'required|string|min:10|max:2000',
        ]);

        $snap = $data['snapshot'];
        $before = $svc->getAll();
        if (! $svc->restoreSnapshot($snap)) {
            abort(404);
        }

        $after = $svc->getAll();

        $audit->log([
            'action' => 'restore_system_settings_snapshot',
            'target_type' => 'system_settings_snapshot',
            'target_id' => $snap,
            'before_values' => $before,
            'after_values' => $after,
            'reason' => $data['reason'],
        ]);

        return redirect()->route('admin.system-settings.index')->with('status', 'Snapshot đã được khôi phục.');
    }

    public function downloadSnapshot($file)
    {
        $path = 'system_settings_snapshots/'.$file;
        if (! Storage::exists($path)) {
            abort(404);
        }

        return Storage::download($path);
    }
}
