<?php

use App\Actions\Settings\EnsureUserSettingsExistAction;
use App\Http\Controllers\Admin\VerificationEvidenceController;
use App\Http\Controllers\MediaController;
use App\Models\BlockedUser;
use App\Models\Conversation;
use App\Models\Post;
use App\Models\Report;
use App\Models\User;
use App\Services\Media\MediaQuotaService;
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

// 5. Admin Panel (protected by account status and review permission)
Route::middleware(['auth', 'active.account', 'can:review_verification'])->group(function () {
    Route::view('admin/dashboard', 'admin.dashboard')
        ->name('admin.dashboard');

    Route::get('admin/verification/evidence/{evidence}', [VerificationEvidenceController::class, 'show'])
        ->name('admin.verification.evidence');

    Route::view('admin/verifications', 'admin.verification-queue')
        ->name('admin.verifications.queue');

    Route::get('admin/verifications/{id}', function ($id) {
        return view('admin.verification-detail', ['id' => $id]);
    })->name('admin.verifications.detail');
});

// Admin reports moderation queue (protected by account status and manage_reports permission)
Route::middleware(['auth', 'active.account', 'can:manage_reports'])->group(function () {
    Route::view('admin/reports', 'admin.reports-queue')->name('admin.reports.index');
    Route::get('admin/reports/{report}', function (Report $report) {
        return view('admin.report-detail', ['report' => $report]);
    })->name('admin.reports.show');

    Route::get('admin/media-usage', function (MediaQuotaService $quota) {
        return view('admin.media-usage', ['report' => $quota->report()]);
    })->name('admin.media-usage');
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
