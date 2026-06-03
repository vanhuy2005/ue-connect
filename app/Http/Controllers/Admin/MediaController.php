<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Media;
use App\Services\AuditService;
use App\Services\Media\MediaQuotaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class MediaController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('manage_media');

        $query = Media::query()->with('user')->latest();

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->input('user_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('visibility')) {
            $query->where('visibility', $request->input('visibility'));
        }

        if ($request->filled('collection')) {
            $query->where('collection', $request->input('collection'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('original_filename', 'like', "%{$search}%")
                    ->orWhere('uuid', 'like', "%{$search}%")
                    ->orWhere('mime_type', 'like', "%{$search}%");
            });
        }

        $media = $query->paginate(20)->withQueryString();

        return view('admin.media.index', compact('media'));
    }

    public function show(Media $media): View
    {
        Gate::authorize('manage_media');

        return view('admin.media.show', compact('media'));
    }

    public function quarantine(Media $media): RedirectResponse
    {
        Gate::authorize('quarantine_media');

        $media->update(['status' => 'quarantined']);

        // Write an audit log
        app(AuditService::class)->log([
            'action' => 'quarantine_media',
            'target_type' => 'media',
            'target_id' => $media->id,
            'reason' => 'Admin quarantined suspicious media file.',
            'before_values' => ['status' => 'ready'],
            'after_values' => ['status' => 'quarantined'],
        ]);

        return back()->with('status', 'File media đã được chuyển vào khu vực cách ly.');
    }

    public function delete(Media $media): RedirectResponse
    {
        Gate::authorize('delete_media');

        $before = $media->toArray();
        $media->delete(); // Soft delete

        app(AuditService::class)->log([
            'action' => 'delete_media',
            'target_type' => 'media',
            'target_id' => $media->id,
            'reason' => 'Admin deleted media file.',
            'before_values' => $before,
            'after_values' => null,
        ]);

        return redirect()->route('admin.media.index')->with('status', 'File media đã được xóa.');
    }

    public function usage(MediaQuotaService $quota): View
    {
        Gate::authorize('view_media_usage');

        $report = $quota->report();

        return view('admin.media.usage', compact('report'));
    }

    public function health(): RedirectResponse
    {
        Gate::authorize('manage_media');

        try {
            Artisan::call('media:health-check');
            $output = Artisan::output();

            return back()->with('status', 'Diagnostic check completed: '.nl2br(e($output)));
        } catch (\Throwable $e) {
            return back()->withErrors(['error' => 'Failed to run health check: '.$e->getMessage()]);
        }
    }

    public function quota(Request $request): RedirectResponse
    {
        Gate::authorize('manage_media_quota');

        $userId = $request->input('user_id');
        $params = $userId ? ['--user' => (int) $userId] : [];

        try {
            Artisan::call('media:quota-check', $params);
            $output = Artisan::output();

            return back()->with('status', 'Quota inspection completed: '.nl2br(e($output)));
        } catch (\Throwable $e) {
            return back()->withErrors(['error' => 'Failed to run quota check: '.$e->getMessage()]);
        }
    }

    public function cloudinarySync(): RedirectResponse
    {
        Gate::authorize('sync_cloudinary_media');

        try {
            Artisan::call('media:sync-cloudinary', ['--failed-only' => true]);
            $output = Artisan::output();

            return back()->with('status', 'Cloudinary sync completed: '.nl2br(e($output)));
        } catch (\Throwable $e) {
            return back()->withErrors(['error' => 'Failed to run Cloudinary sync: '.$e->getMessage()]);
        }
    }

    public function cleanupTemporary(): RedirectResponse
    {
        Gate::authorize('manage_media');

        try {
            Artisan::call('media:cleanup-temporary');
            $output = Artisan::output();

            return back()->with('status', 'Temporary media cleanup completed: '.nl2br(e($output)));
        } catch (\Throwable $e) {
            return back()->withErrors(['error' => 'Failed to run cleanup temporary media: '.$e->getMessage()]);
        }
    }

    public function cleanupOrphaned(): RedirectResponse
    {
        Gate::authorize('manage_media');

        try {
            Artisan::call('media:cleanup-orphaned');
            $output = Artisan::output();

            return back()->with('status', 'Orphaned media cleanup completed: '.nl2br(e($output)));
        } catch (\Throwable $e) {
            return back()->withErrors(['error' => 'Failed to run cleanup orphaned media: '.$e->getMessage()]);
        }
    }
}
