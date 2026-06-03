<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CreateAnnouncementRequest;
use App\Models\Announcement;
use App\Services\AuditService;
use Illuminate\Http\Request;

class AnnouncementController extends Controller
{
    public function index()
    {
        $this->authorize('manage', Announcement::class);

        $announcements = Announcement::latest()->paginate(25);

        return view('admin.announcements-list', ['announcements' => $announcements]);
    }

    public function create()
    {
        $this->authorize('create', Announcement::class);

        return view('admin.announcements-create');
    }

    public function store(CreateAnnouncementRequest $request, AuditService $audit)
    {
        $data = $request->validated();

        $announcement = Announcement::create([
            'title' => $data['title'],
            'body' => $data['body'],
            'type' => $data['type'] ?? 'system_announcement',
            'target' => $data['target'] ?? null,
            'starts_at' => $data['starts_at'] ?? null,
            'expires_at' => $data['expires_at'] ?? null,
            'status' => 'draft',
            'created_by' => $request->user()->id,
        ]);

        $audit->log([
            'action' => 'create_announcement',
            'target_type' => 'announcement',
            'target_id' => $announcement->id,
            'after_values' => $announcement->toArray(),
            'reason' => $request->input('reason') ?? null,
        ]);

        return redirect()->route('admin.announcements.index')->with('status', 'Announcement created.');
    }

    public function publish(Request $request, Announcement $announcement, AuditService $audit)
    {
        $this->authorize('manage', $announcement);

        $before = $announcement->toArray();
        $announcement->status = 'published';
        $announcement->starts_at = $announcement->starts_at ?? now();
        $announcement->save();

        $audit->log([
            'action' => 'publish_announcement',
            'target_type' => 'announcement',
            'target_id' => $announcement->id,
            'before_values' => $before,
            'after_values' => $announcement->toArray(),
            'reason' => $request->input('reason') ?? 'Publish via admin UI',
        ]);

        return back()->with('status', 'Announcement published.');
    }

    public function expire(Request $request, Announcement $announcement, AuditService $audit)
    {
        $this->authorize('manage', $announcement);

        $before = $announcement->toArray();
        $announcement->status = 'expired';
        $announcement->expires_at = now();
        $announcement->save();

        $audit->log([
            'action' => 'expire_announcement',
            'target_type' => 'announcement',
            'target_id' => $announcement->id,
            'before_values' => $before,
            'after_values' => $announcement->toArray(),
            'reason' => $request->input('reason') ?? 'Expire via admin UI',
        ]);

        return back()->with('status', 'Announcement expired.');
    }

    public function destroy(Request $request, Announcement $announcement, AuditService $audit)
    {
        $this->authorize('manage', $announcement);

        $before = $announcement->toArray();
        $announcement->delete();

        $audit->log([
            'action' => 'delete_announcement',
            'target_type' => 'announcement',
            'target_id' => $announcement->id,
            'before_values' => $before,
            'after_values' => null,
            'reason' => $request->input('reason') ?? 'Deleted via admin UI',
        ]);

        return redirect()->route('admin.announcements.index')->with('status', 'Announcement deleted.');
    }
}
