<?php

use App\Http\Controllers\Admin\VerificationEvidenceController;
use Illuminate\Support\Facades\Route;

// 1. Public & Guest Routes
Route::view('/', 'welcome');

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

    Route::view('app/profile/setup', 'app.profile-setup')
        ->name('profile.setup');

    Route::view('app/profile', 'profile')
        ->name('profile');
});

// 5. Admin Panel (protected by account status and review permission)
Route::middleware(['auth', 'active.account', 'can:review_verification'])->group(function () {
    Route::get('admin/verification/evidence/{evidence}', [VerificationEvidenceController::class, 'show'])
        ->name('admin.verification.evidence');

    Route::view('admin/verifications', 'admin.verification-queue')
        ->name('admin.verifications.queue');

    Route::get('admin/verifications/{id}', function ($id) {
        return view('admin.verification-detail', ['id' => $id]);
    })->name('admin.verifications.detail');
});

/*
 * Design system preview — local environment only.
 * Not visible in production.
 */
if (app()->environment('local')) {
    Route::view('/design-system', 'dev.design-system')
        ->name('dev.design-system');
}

require __DIR__.'/auth.php';
