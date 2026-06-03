<?php

use App\Actions\Settings\EnsureUserSettingsExistAction;
use App\Http\Controllers\Admin\AdminSearchController;
use App\Http\Controllers\Admin\AnnouncementController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\CommunityController;
use App\Http\Controllers\Admin\MediaController as AdminMediaController;
use App\Http\Controllers\Admin\MentorAccessController;
use App\Http\Controllers\Admin\PermissionGrantController;
use App\Http\Controllers\Admin\SystemSettingsController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Admin\VerificationActionController;
use App\Http\Controllers\Admin\VerificationEvidenceController;
use App\Http\Controllers\MediaController;
use App\Http\Middleware\EnsureAdminAccess;
use App\Models\AuditLog;
use App\Models\BlockedUser;
use App\Models\Community;
use App\Models\Conversation;
use App\Models\Post;
use App\Models\Report;
use App\Models\User;
use App\Models\VerificationRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;

// 1. Public & Guest Routes
Route::view('/', 'welcome')->name('landing');

// 2. System pages
Route::view('system/account-restricted', 'system.account-restricted')
    ->middleware(['auth'])
    ->name('system.account-restricted');

Route::view('system/403', 'system.403')
    ->name('system.403');

// 3. Verification workflow (protected by account status)
Route::middleware(['auth', 'active.account'])->group(function () {
    Route::view('verification/status', 'verification.status')
        ->name('verification.status');

    Route::view('verification/start', 'verification.start')
        ->name('verification.start');
});

// 4. App Shell (protected by account status AND verified identity)
Route::middleware(['auth', 'active.account', 'verified.identity'])->group(function () {
    Route::view('app/home', 'app.home')
        ->name('dashboard');

    Route::get('app/posts/{post}', function (Post $post) {
        Gate::authorize('view', $post);

        return view('app.posts.show', ['post' => $post]);
    })->name('posts.show');

    Route::view('app/profile/setup', 'app.profile-setup')
        ->name('profile.setup');

    Route::get('app/profile', function () {
        return view('app.profile', ['user' => Auth::user()]);
    })->name('profile');

    Route::view('app/profile/edit', 'app.profile-edit')
        ->name('profile.edit');

    Route::get('app/profile/{user}', function (User $user) {
        $viewer = Auth::user();

        $isBlocked = BlockedUser::where(function ($q) use ($viewer, $user) {
            $q->where('blocker_id', $viewer->id)->where('blocked_id', $user->id);
        })->orWhere(function ($q) use ($viewer, $user) {
            $q->where('blocker_id', $user->id)->where('blocked_id', $viewer->id);
        })->exists();

        if (! $isBlocked && ! $viewer->can('viewProfile', $user->profile)) {
            abort(403, 'Hồ sơ này không khả dụng hoặc bạn không có quyền xem.');
        }

        return view('app.profile', ['user' => $user]);
    })->name('profile.show');

    Route::get('app/settings/{section?}/{subSection?}', function (?string $section = 'index', ?string $subSection = null) {
        $user = Auth::user();
        app(EnsureUserSettingsExistAction::class)->execute($user);

        return view('app.settings', ['section' => $section, 'subSection' => $subSection]);
    })->name('settings');

    Route::view('app/saved-posts', 'app.saved-posts')
        ->name('posts.saved');

    Route::view('app/discovery', 'app.discovery')
        ->name('discovery.index');

    Route::view('app/connections', 'app.connections')
        ->name('connections.index');

    Route::view('app/notifications', 'app.notifications')
        ->name('notifications.index');

    Route::get('app/messages/{conversation?}', function (?Conversation $conversation = null) {
        if ($conversation) {
            Gate::authorize('view', $conversation);
        }

        return view('app.messages', ['activeConversation' => $conversation]);
    })->name('messages.index');

    // 4.1 Secure Media Delivery Routes
    Route::get('app/media/{media}/preview', [MediaController::class, 'preview'])
        ->name('media.preview');
    Route::get('app/media/{media}/download', [MediaController::class, 'download'])
        ->name('media.download');
});

// 5. Admin Panel (protected by account status and any admin permission)
Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'active.account', EnsureAdminAccess::class])
    ->group(function () {
        Route::view('dashboard', 'admin.dashboard')->name('dashboard');

        // Verification workflow
        Route::get('verification/evidence/{evidence}', [VerificationEvidenceController::class, 'show'])
            ->name('verification.evidence');

        Route::view('verifications', 'admin.verification-queue')
            ->name('verifications.queue');

        Route::get('verifications/{id}', function ($id) {
            return view('admin.verification-detail', ['id' => $id]);
        })->name('verifications.detail');

        Route::post('verifications/{verificationRequest}/action', [VerificationActionController::class, 'handle'])
            ->name('verifications.action');

        // Audit logs
        Route::get('audit-logs', [AuditLogController::class, 'index'])
            ->name('audit-logs.index');

        // Notifications
        Route::get('notifications', function () {
            $user = Auth::user();

            return view('admin.notifications', [
                'notifications' => $user?->notifications()->latest()->paginate(20),
                'unreadCount' => $user?->unreadNotifications()->count() ?? 0,
            ]);
        })->name('notifications.index');

        // Users management
        Route::get('users', function () {
            if (! Gate::any(['manage_users', 'manage_permissions', 'review_verification'])) {
                abort(403);
            }

            return view('admin.users-list');
        })->name('users.index');

        Route::get('users/{user}', function (User $user) {
            if (! Gate::any(['manage_users', 'manage_permissions', 'review_verification'])) {
                abort(403);
            }

            return view('admin.users-detail', ['user' => $user]);
        })->name('users.show');

        Route::post('users/{user}/suspend', [UserManagementController::class, 'suspend'])
            ->name('users.suspend');

        Route::post('users/{user}/ban', [UserManagementController::class, 'ban'])
            ->name('users.ban');

        Route::post('users/{user}/reactivate', [UserManagementController::class, 'reactivate'])
            ->name('users.reactivate');

        // Communities
        Route::get('communities', [CommunityController::class, 'index'])->name('communities.index');
        Route::get('communities/create', [CommunityController::class, 'create'])->name('communities.create');
        Route::post('communities', [CommunityController::class, 'store'])->name('communities.store');
        Route::get('communities/{community}', [CommunityController::class, 'show'])->name('communities.show');
        Route::post('communities/{community}/update', [CommunityController::class, 'update'])->name('communities.update');
        Route::post('communities/{community}/suspend', [CommunityController::class, 'suspend'])->name('communities.suspend');
        Route::post('communities/{community}/reactivate', [CommunityController::class, 'reactivate'])->name('communities.reactivate');
        Route::post('communities/{community}/members', [CommunityController::class, 'addMember'])->name('communities.members.add');
        Route::delete('communities/{community}/members/{user}', [CommunityController::class, 'removeMember'])->name('communities.members.remove');

        // Mentor Access
        Route::get('mentors', [MentorAccessController::class, 'index'])->name('mentors.index');
        Route::get('mentors/{id}', [MentorAccessController::class, 'show'])->name('mentors.detail');
        Route::post('mentors/{mentorAccess}/action', [MentorAccessController::class, 'handle'])->name('mentors.action');

        // Permissions management
        Route::view('permissions', 'admin.permissions-list')->name('permissions.index');
        Route::get('permission-grants', [PermissionGrantController::class, 'index'])->name('permission-grants.index');
        Route::post('permission-grants', [PermissionGrantController::class, 'store'])->name('permission-grants.store');
        Route::post('permission-grants/{grant}/revoke', [PermissionGrantController::class, 'revoke'])->name('permission-grants.revoke');
        Route::view('permissions/create', 'admin.permissions-create')->name('permissions.create');
        Route::get('search', [AdminSearchController::class, 'search'])->name('search');

        // Announcements
        Route::get('announcements', [AnnouncementController::class, 'index'])->name('announcements.index');
        Route::get('announcements/create', [AnnouncementController::class, 'create'])->name('announcements.create');
        Route::post('announcements', [AnnouncementController::class, 'store'])->name('announcements.store');
        Route::post('announcements/{announcement}/publish', [AnnouncementController::class, 'publish'])->name('announcements.publish');
        Route::post('announcements/{announcement}/expire', [AnnouncementController::class, 'expire'])->name('announcements.expire');
        Route::post('announcements/{announcement}/delete', [AnnouncementController::class, 'destroy'])->name('announcements.delete');

        // Moderation Tools
        Route::get('moderation', function () {
            return view('admin.moderation', [
                'pendingReports' => Report::where('status', 'pending')->count(),
                'pendingVerifications' => VerificationRequest::where('status', 'pending_review')->count(),
                'suspendedUsers' => User::where('account_status', 'suspended')->count(),
                'recentActions' => AuditLog::latest()->limit(5)->get(),
            ]);
        })->name('moderation.index');

        // Analytics
        Route::get('analytics', function () {
            return view('admin.analytics', [
                'totalUsers' => User::count(),
                'totalCommunities' => Community::count(),
                'totalReports' => Report::count(),
                'totalVerifications' => VerificationRequest::count(),
                'totalPosts' => Post::count(),
            ]);
        })->name('analytics.index');

        // System Settings
        Route::get('system-settings', [SystemSettingsController::class, 'index'])
            ->name('system-settings.index');
        Route::post('system-settings', [SystemSettingsController::class, 'update'])
            ->name('system-settings.update');
        Route::post('system-settings/snapshot', [SystemSettingsController::class, 'saveSnapshot'])
            ->name('system-settings.snapshot');
        Route::post('system-settings/snapshot-restore', [SystemSettingsController::class, 'restoreSnapshot'])
            ->name('system-settings.snapshot.restore');
        Route::get('system-settings/snapshot-download/{file}', [SystemSettingsController::class, 'downloadSnapshot'])
            ->name('system-settings.snapshot.download');

        // Reports Moderation Queue
        Route::view('reports', 'admin.reports-queue')->name('reports.index');
        Route::get('reports/{report}', function (Report $report) {
            return view('admin.report-detail', ['report' => $report]);
        })->name('reports.show');

        // Media Management
        Route::get('media', [AdminMediaController::class, 'index'])->name('media.index');
        Route::get('media/usage', [AdminMediaController::class, 'usage'])->name('media.usage');
        Route::get('media/{media}', [AdminMediaController::class, 'show'])->name('media.show')->where('media', '[0-9]+');
        Route::post('media/{media}/quarantine', [AdminMediaController::class, 'quarantine'])->name('media.quarantine');
        Route::post('media/{media}/delete', [AdminMediaController::class, 'delete'])->name('media.delete');
        Route::post('media/health', [AdminMediaController::class, 'health'])->name('media.health');
        Route::post('media/quota', [AdminMediaController::class, 'quota'])->name('media.quota');
        Route::post('media/cloudinary-sync', [AdminMediaController::class, 'cloudinarySync'])->name('media.cloudinary-sync');
        Route::post('media/cleanup-temporary', [AdminMediaController::class, 'cleanupTemporary'])->name('media.cleanup-temporary');
        Route::post('media/cleanup-orphaned', [AdminMediaController::class, 'cleanupOrphaned'])->name('media.cleanup-orphaned');
    });

// 6. Legacy redirects
Route::redirect('/dashboard', '/app/home')->name('dashboard.legacy');
Route::redirect('/verification', '/verification/status')->name('verification.legacy');

/*
 * Design system preview — local environment only.
 * Not visible in production.
 */
if (app()->environment('local')) {
    Route::view('/design-system', 'dev.design-system')
        ->name('dev.design-system');
}

require __DIR__.'/auth.php';
