<?php

use App\Actions\Settings\EnsureUserSettingsExistAction;
use App\Http\Controllers\Admin\AdminSearchController;
use App\Http\Controllers\Admin\AnnouncementController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\CommunityController;
use App\Http\Controllers\Admin\MentorAccessController;
use App\Http\Controllers\Admin\PermissionGrantController;
use App\Http\Controllers\Admin\SystemSettingsController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Admin\VerificationActionController;
use App\Http\Controllers\Admin\VerificationEvidenceController;
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
});

// 5. Admin Panel (protected by account status and any admin permission)
Route::middleware(['auth', 'active.account', EnsureAdminAccess::class])->group(function () {
    Route::view('admin/dashboard', 'admin.dashboard')
        ->name('admin.dashboard');

    Route::get('admin/verification/evidence/{evidence}', [VerificationEvidenceController::class, 'show'])
        ->name('admin.verification.evidence');

    Route::view('admin/verifications', 'admin.verification-queue')
        ->name('admin.verifications.queue');

    Route::get('admin/verifications/{id}', function ($id) {
        return view('admin.verification-detail', ['id' => $id]);
    })->name('admin.verifications.detail');

    // Verification action endpoint
    Route::post('admin/verifications/{verificationRequest}/action', [VerificationActionController::class, 'handle'])
        ->name('admin.verifications.action');

    Route::get('admin/audit-logs', [AuditLogController::class, 'index'])
        ->name('admin.audit-logs.index');

    Route::get('admin/notifications', function () {
        $user = Auth::user();

        return view('admin.notifications', [
            'notifications' => $user?->notifications()->latest()->paginate(20),
            'unreadCount' => $user?->unreadNotifications()->count() ?? 0,
        ]);
    })->name('admin.notifications.index');
});

// Admin user management (protected by account status; authorize inside route)
Route::middleware(['auth', 'active.account'])->group(function () {
    Route::get('admin/users', function () {
        if (! Gate::any(['manage_users', 'manage_permissions', 'review_verification'])) {
            abort(403);
        }

        return view('admin.users-list');
    })->name('admin.users.index');

    Route::get('admin/users/{user}', function (User $user) {
        if (! Gate::any(['manage_users', 'manage_permissions', 'review_verification'])) {
            abort(403);
        }

        return view('admin.users-detail', ['user' => $user]);
    })->name('admin.users.show');
});

// Admin community management (protected by admin access middleware)
Route::middleware(['auth', 'active.account', EnsureAdminAccess::class])->group(function () {
    Route::get('admin/communities', [CommunityController::class, 'index'])->name('admin.communities.index');
    Route::get('admin/communities/create', [CommunityController::class, 'create'])->name('admin.communities.create');
    Route::post('admin/communities', [CommunityController::class, 'store'])->name('admin.communities.store');
    Route::get('admin/communities/{community}', [CommunityController::class, 'show'])->name('admin.communities.show');
    Route::post('admin/communities/{community}/update', [CommunityController::class, 'update'])->name('admin.communities.update');
    Route::post('admin/communities/{community}/suspend', [CommunityController::class, 'suspend'])->name('admin.communities.suspend');
    Route::post('admin/communities/{community}/reactivate', [CommunityController::class, 'reactivate'])->name('admin.communities.reactivate');

    // Member management: add/remove members
    Route::post('admin/communities/{community}/members', [CommunityController::class, 'addMember'])->name('admin.communities.members.add');
    Route::delete('admin/communities/{community}/members/{user}', [CommunityController::class, 'removeMember'])->name('admin.communities.members.remove');
});

// Admin mentor access management (protected by admin access middleware)
Route::middleware(['auth', 'active.account', EnsureAdminAccess::class])->group(function () {
    Route::get('admin/mentors', [MentorAccessController::class, 'index'])->name('admin.mentors.index');
    Route::get('admin/mentors/{id}', [MentorAccessController::class, 'show'])->name('admin.mentors.detail');
    Route::post('admin/mentors/{mentorAccess}/action', [MentorAccessController::class, 'handle'])->name('admin.mentors.action');
});

// Admin permissions management (protected by admin access middleware)
Route::middleware(['auth', 'active.account', EnsureAdminAccess::class])->group(function () {
    Route::view('admin/permissions', 'admin.permissions-list')->name('admin.permissions.index');
    Route::get('admin/permission-grants', [PermissionGrantController::class, 'index'])->name('admin.permission-grants.index');
    Route::post('admin/permission-grants', [PermissionGrantController::class, 'store'])->name('admin.permission-grants.store');
    Route::post('admin/permission-grants/{grant}/revoke', [PermissionGrantController::class, 'revoke'])->name('admin.permission-grants.revoke');
    Route::view('admin/permissions/create', 'admin.permissions-create')->name('admin.permissions.create');
    Route::get('admin/search', [AdminSearchController::class, 'search'])->name('admin.search');
});

// Admin announcements management (protected by admin access middleware)
Route::middleware(['auth', 'active.account', EnsureAdminAccess::class])->group(function () {
    Route::get('admin/announcements', [AnnouncementController::class, 'index'])->name('admin.announcements.index');
    Route::get('admin/announcements/create', [AnnouncementController::class, 'create'])->name('admin.announcements.create');
    Route::post('admin/announcements', [AnnouncementController::class, 'store'])->name('admin.announcements.store');
    Route::post('admin/announcements/{announcement}/publish', [AnnouncementController::class, 'publish'])->name('admin.announcements.publish');
    Route::post('admin/announcements/{announcement}/expire', [AnnouncementController::class, 'expire'])->name('admin.announcements.expire');
    Route::post('admin/announcements/{announcement}/delete', [AnnouncementController::class, 'destroy'])->name('admin.announcements.delete');
});

// Admin moderation tools (protected by account status and admin access middleware)
Route::middleware(['auth', 'active.account', EnsureAdminAccess::class])->group(function () {
    Route::get('admin/moderation', function () {
        return view('admin.moderation', [
            'pendingReports' => Report::where('status', 'pending')->count(),
            'pendingVerifications' => VerificationRequest::where('status', 'pending_review')->count(),
            'suspendedUsers' => User::where('account_status', 'suspended')->count(),
            'recentActions' => AuditLog::latest()->limit(5)->get(),
        ]);
    })->name('admin.moderation.index');

    // Admin user status actions (suspend/ban/reactivate)
    Route::post('admin/users/{user}/suspend', [UserManagementController::class, 'suspend'])
        ->name('admin.users.suspend');

    Route::post('admin/users/{user}/ban', [UserManagementController::class, 'ban'])
        ->name('admin.users.ban');

    Route::post('admin/users/{user}/reactivate', [UserManagementController::class, 'reactivate'])
        ->name('admin.users.reactivate');

    Route::get('admin/analytics', function () {
        return view('admin.analytics', [
            'totalUsers' => User::count(),
            'totalCommunities' => Community::count(),
            'totalReports' => Report::count(),
            'totalVerifications' => VerificationRequest::count(),
            'totalPosts' => Post::count(),
        ]);
    })->name('admin.analytics.index');
});

// Admin system settings (protected by admin access middleware)
Route::middleware(['auth', 'active.account', EnsureAdminAccess::class])->group(function () {
    Route::get('admin/system-settings', [SystemSettingsController::class, 'index'])
        ->name('admin.system-settings.index');

    Route::post('admin/system-settings', [SystemSettingsController::class, 'update'])
        ->name('admin.system-settings.update');

    Route::post('admin/system-settings/snapshot', [SystemSettingsController::class, 'saveSnapshot'])
        ->name('admin.system-settings.snapshot');

    Route::post('admin/system-settings/snapshot-restore', [SystemSettingsController::class, 'restoreSnapshot'])
        ->name('admin.system-settings.snapshot.restore');

    Route::get('admin/system-settings/snapshot-download/{file}', [SystemSettingsController::class, 'downloadSnapshot'])
        ->name('admin.system-settings.snapshot.download');
});

// Admin reports moderation queue (protected by account status and admin access middleware)
Route::middleware(['auth', 'active.account', EnsureAdminAccess::class])->group(function () {
    Route::view('admin/reports', 'admin.reports-queue')->name('admin.reports.index');
    Route::get('admin/reports/{report}', function (Report $report) {
        return view('admin.report-detail', ['report' => $report]);
    })->name('admin.reports.show');
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
