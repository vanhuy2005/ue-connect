<?php

use App\Actions\Mentor\AcceptMentorRequestAction;
use App\Actions\Mentor\AskMentorRequestMoreInfoAction;
use App\Actions\Mentor\CancelMentorRequestAction;
use App\Actions\Mentor\CompleteMentorRequestAction;
use App\Actions\Mentor\CreateMentorRequestAction;
use App\Actions\Mentor\DeclineMentorRequestAction;
use App\Actions\Mentor\ReportMentorRequestAction;
use App\Actions\Mentor\RequestMentorAccessAction;
use App\Actions\Mentor\SubmitMentorFeedbackAction;
use App\Actions\Mentor\ToggleMentorAvailabilityAction;
use App\Actions\Mentor\UpdateMentorProfileAction;
use App\Actions\Mentor\UpdateMentorRequestAction;
use App\Actions\Settings\EnsureUserSettingsExistAction;
use App\Enums\MentorAvailabilityStatus;
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
use App\Models\Media;
use App\Models\MentorProfile;
use App\Models\MentorRequest;
use App\Models\Post;
use App\Models\Report;
use App\Models\User;
use App\Models\VerificationRequest;
use App\Support\Navigation\AdminNavigation;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

// 1. Public & Guest Routes
Route::view('/', 'welcome')->name('landing');

// 2. System pages
Route::view('system/account-restricted', 'system.account-restricted')
    ->middleware(['auth'])
    ->name('system.account-restricted');

Route::view('system/403', 'system.403')
    ->name('system.403');

// 3. Verification workflow (protected by account status)
Route::middleware(['auth', 'verified', 'active.account'])->group(function () {
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

    // Community app routes
    Route::get('app/communities', fn () => view('app.communities'))
        ->name('community.index');

    Route::get('app/communities/{community}', function (Community $community) {
        Gate::authorize('view', $community);

        return view('app.community-show', ['community' => $community]);
    })->name('community.show');

    Route::get('app/mentors', fn () => view('app.mentors'))
        ->name('mentor.discovery');

    Route::get('app/mentors/{mentorProfile}', fn (MentorProfile $mentorProfile) => view('app.mentor-show', ['mentorProfile' => $mentorProfile]))
        ->name('mentor.show');

    Route::get('app/mentor/apply', fn () => view('app.mentor-apply'))
        ->name('mentor.apply');

    Route::post('app/mentor/apply', function (RequestMentorAccessAction $action) {
        $eligibleRoleContexts = RequestMentorAccessAction::eligibleRoleContextsFor(Auth::user());

        if (empty($eligibleRoleContexts)) {
            throw ValidationException::withMessages([
                'requested_role_context' => 'Hồ sơ hiện tại của bạn chưa đủ điều kiện đăng ký mentor.',
            ]);
        }

        $data = request()->validate([
            'requested_role_context' => ['required', 'string', Rule::in(array_keys($eligibleRoleContexts))],
            'motivation' => ['required', 'string', 'min:20', 'max:5000'],
            'experience_summary' => ['nullable', 'string', 'max:5000'],
            'expertise_topics' => ['required', 'array', 'min:2'],
            'expertise_topics.*' => ['string', 'max:80'],
            'career_paths' => ['nullable', 'array'],
            'career_paths.*' => ['string', 'max:80'],
            'skills' => ['nullable', 'array'],
            'skills.*' => ['string', 'max:80'],
            'portfolio_link' => ['nullable', 'url', 'max:255'],
            'availability_note' => ['nullable', 'string', 'max:1000'],
            'policy_agreed' => ['required', 'accepted'],
            'headline' => ['required', 'string', 'min:12', 'max:160'],
            'bio' => ['required', 'string', 'min:40', 'max:5000'],
            'help_topics' => ['required', 'array', 'min:2'],
            'help_topics.*' => ['string', 'max:80'],
            'preferred_request_types' => ['required', 'array', 'min:1'],
            'preferred_request_types.*' => ['string', 'max:80'],
            'response_expectation_text' => ['required', 'string', 'max:255'],
            'office_hours_text' => ['nullable', 'string', 'max:255'],
            'evidence_media_id' => ['nullable', 'integer', 'exists:media_files,id'],
        ]);

        try {
            $action->execute(Auth::user(), $data);
        } catch (Exception $exception) {
            throw ValidationException::withMessages([
                'requested_role_context' => $exception->getMessage(),
            ]);
        }

        return to_route('mentor.dashboard')->with('status', 'Yêu cầu trở thành mentor đã được gửi.');
    })->name('mentor.apply.store');

    Route::get('app/mentor/setup', fn () => view('app.mentor-setup'))
        ->name('mentor.setup');

    Route::patch('app/mentor/setup', function (UpdateMentorProfileAction $action) {
        $profile = Auth::user()->mentorProfile()->first();

        if (! $profile) {
            return back()
                ->withErrors(['mentor_profile' => 'Bạn chưa có hồ sơ mentor được duyệt. Hãy gửi đăng ký mentor hoặc chờ ban quản trị xét duyệt trước khi thiết lập hồ sơ.'])
                ->withInput();
        }

        $profileRecord = Auth::user()->profile;
        $hasTrustedAvatar = (bool) ($profileRecord?->avatar()->where('status', 'ready')->exists() || $profileRecord?->avatar_media_file_id);

        if (! $hasTrustedAvatar) {
            return back()
                ->withErrors(['avatar' => 'Vui lòng tải lên ảnh đại diện rõ mặt trước khi lưu hồ sơ mentor.'])
                ->withInput();
        }

        $data = request()->validate([
            'headline' => ['required', 'string', 'min:12', 'max:160'],
            'bio' => ['required', 'string', 'min:40', 'max:5000'],
            'expertise_topics_text' => ['required', 'string', 'max:1000'],
            'help_topics_text' => ['required', 'string', 'max:1000'],
            'career_paths_text' => ['nullable', 'string', 'max:1000'],
            'skills_text' => ['nullable', 'string', 'max:1000'],
            'preferred_request_types' => ['required', 'array', 'min:1'],
            'preferred_request_types.*' => ['string', 'max:80'],
            'availability_status' => ['required', 'string', 'in:available,paused,full,hidden'],
            'mentor_visibility' => ['required', 'boolean'],
            'max_pending_requests' => ['required', 'integer', 'min:1', 'max:50'],
            'response_expectation_text' => ['required', 'string', 'max:255'],
            'office_hours_text' => ['nullable', 'string', 'max:255'],
        ]);

        $normalizeList = function (?string $value, int $limit): array {
            return collect(preg_split('/[\r\n,]+/', (string) $value))
                ->map(fn (string $item) => trim($item))
                ->filter()
                ->unique()
                ->take($limit)
                ->values()
                ->all();
        };

        $data['expertise_topics'] = $normalizeList($data['expertise_topics_text'], 10);
        $data['help_topics'] = $normalizeList($data['help_topics_text'], 8);
        $data['career_paths'] = $normalizeList($data['career_paths_text'] ?? null, 8);
        $data['skills'] = $normalizeList($data['skills_text'] ?? null, 12);

        unset(
            $data['expertise_topics_text'],
            $data['help_topics_text'],
            $data['career_paths_text'],
            $data['skills_text'],
        );

        if (count($data['expertise_topics']) < 2) {
            return back()
                ->withErrors(['expertise_topics_text' => 'Vui lòng nhập ít nhất 2 chủ đề chuyên môn, ngăn cách bằng dấu phẩy hoặc xuống dòng.'])
                ->withInput();
        }

        if (count($data['help_topics']) < 2) {
            return back()
                ->withErrors(['help_topics_text' => 'Vui lòng nhập ít nhất 2 nội dung bạn có thể hỗ trợ.'])
                ->withInput();
        }

        try {
            $action->execute(Auth::user(), $profile, $data);
        } catch (AuthorizationException) {
            return back()
                ->withErrors(['mentor_profile' => 'Không thể lưu hồ sơ mentor vì hồ sơ này không thuộc tài khoản hiện tại hoặc tài khoản chưa đủ điều kiện cập nhật. Vui lòng đăng xuất và đăng nhập lại đúng tài khoản mentor.'])
                ->withInput();
        }

        return back()->with('status', 'Hồ sơ mentor đã được cập nhật.');
    })->name('mentor.setup.update');

    Route::view('app/mentor/dashboard', 'app.mentor-dashboard')
        ->name('mentor.dashboard');

    Route::view('app/mentor/requests', 'app.mentor-requests')
        ->name('mentor.requests.index');

    Route::get('app/mentor/requests/{mentorRequest}', function (MentorRequest $mentorRequest) {
        Gate::authorize('view', $mentorRequest);

        return view('app.mentor-request-show', ['mentorRequest' => $mentorRequest]);
    })->name('mentor.requests.show');

    Route::post('app/mentor/requests', function (CreateMentorRequestAction $action) {
        $data = request()->validate([
            'mentor_profile_id' => ['required', 'exists:mentor_profiles,id'],
            'topic' => ['required', 'string', 'max:255'],
            'goal' => ['required', 'string', 'max:5000'],
            'question' => ['required', 'string', 'max:5000'],
            'urgency' => ['required', 'string', 'in:low,normal,high,time_sensitive'],
            'context' => ['nullable', 'string', 'max:5000'],
            'expected_outcome' => ['nullable', 'string', 'max:1000'],
        ]);

        $mentorProfile = MentorProfile::findOrFail($data['mentor_profile_id']);
        unset($data['mentor_profile_id']);

        try {
            $mentorRequest = $action->execute(Auth::user(), $mentorProfile, $data);
        } catch (Exception $exception) {
            throw ValidationException::withMessages([
                'mentor_profile_id' => $exception->getMessage(),
            ]);
        }

        return to_route('mentor.requests.show', $mentorRequest)->with('status', 'Yêu cầu cố vấn đã được gửi.');
    })->name('mentor.requests.store');

    Route::patch('app/mentor/requests/{mentorRequest}', function (MentorRequest $mentorRequest, UpdateMentorRequestAction $action) {
        $data = request()->validate([
            'topic' => ['required', 'string', 'max:255'],
            'goal' => ['required', 'string', 'max:5000'],
            'question' => ['required', 'string', 'max:5000'],
            'urgency' => ['required', 'string', 'in:low,normal,high,time_sensitive'],
            'context' => ['nullable', 'string', 'max:5000'],
            'expected_outcome' => ['nullable', 'string', 'max:1000'],
        ]);

        try {
            $action->execute(Auth::user(), $mentorRequest, $data);
        } catch (Exception $exception) {
            throw ValidationException::withMessages([
                'mentor_request' => $exception->getMessage(),
            ]);
        }

        return to_route('mentor.requests.show', $mentorRequest)->with('status', 'Yêu cầu cố vấn đã được cập nhật thành công.');
    })->name('mentor.requests.update');

    Route::post('app/mentor/requests/{mentorRequest}/accept', function (MentorRequest $mentorRequest, AcceptMentorRequestAction $action) {
        try {
            $action->execute(Auth::user(), $mentorRequest, request()->only('mentor_response'));
        } catch (Exception $exception) {
            throw ValidationException::withMessages([
                'mentor_request' => $exception->getMessage(),
            ]);
        }

        return back()->with('status', 'Yêu cầu cố vấn đã được chấp nhận.');
    })->name('mentor.requests.accept');

    Route::post('app/mentor/requests/{mentorRequest}/decline', function (MentorRequest $mentorRequest, DeclineMentorRequestAction $action) {
        request()->validate(['decline_reason' => ['nullable', 'string', 'max:1000']]);
        try {
            $action->execute(Auth::user(), $mentorRequest, request()->only('decline_reason'));
        } catch (Exception $exception) {
            throw ValidationException::withMessages([
                'mentor_request' => $exception->getMessage(),
            ]);
        }

        return back()->with('status', 'Yêu cầu cố vấn đã bị từ chối.');
    })->name('mentor.requests.decline');

    Route::post('app/mentor/requests/{mentorRequest}/ask-more-info', function (MentorRequest $mentorRequest, AskMentorRequestMoreInfoAction $action) {
        $data = request()->validate(['more_info_question' => ['required', 'string', 'max:1000']]);
        try {
            $action->execute(Auth::user(), $mentorRequest, $data);
        } catch (Exception $exception) {
            throw ValidationException::withMessages([
                'mentor_request' => $exception->getMessage(),
            ]);
        }

        return back()->with('status', 'Đã yêu cầu thêm thông tin.');
    })->name('mentor.requests.ask-more-info');

    Route::post('app/mentor/requests/{mentorRequest}/cancel', function (MentorRequest $mentorRequest, CancelMentorRequestAction $action) {
        try {
            $action->execute(Auth::user(), $mentorRequest);
        } catch (Exception $exception) {
            throw ValidationException::withMessages([
                'mentor_request' => $exception->getMessage(),
            ]);
        }

        return back()->with('status', 'Yêu cầu cố vấn đã được hủy.');
    })->name('mentor.requests.cancel');

    Route::post('app/mentor/requests/{mentorRequest}/complete', function (MentorRequest $mentorRequest, CompleteMentorRequestAction $action) {
        try {
            $action->execute(Auth::user(), $mentorRequest);
        } catch (Exception $exception) {
            throw ValidationException::withMessages([
                'mentor_request' => $exception->getMessage(),
            ]);
        }

        return back()->with('status', 'Yêu cầu cố vấn đã hoàn thành.');
    })->name('mentor.requests.complete');

    Route::post('app/mentor/requests/{mentorRequest}/feedback', function (MentorRequest $mentorRequest, SubmitMentorFeedbackAction $action) {
        $data = request()->validate([
            'helpfulness_level' => ['required', 'string', 'in:helpful,somewhat_helpful,not_helpful'],
            'feedback_text' => ['nullable', 'string', 'max:2000'],
        ]);
        try {
            $action->execute(Auth::user(), $mentorRequest, $data);
        } catch (Exception $exception) {
            throw ValidationException::withMessages([
                'mentor_request' => $exception->getMessage(),
            ]);
        }

        return back()->with('status', 'Cảm ơn bạn đã gửi phản hồi.');
    })->name('mentor.requests.feedback');

    Route::post('app/mentor/requests/{mentorRequest}/report', function (MentorRequest $mentorRequest, ReportMentorRequestAction $action) {
        $data = request()->validate([
            'reason' => ['required', 'string', Rule::in(['spam', 'harassment', 'inappropriate_content', 'misinformation', 'privacy_violation', 'other'])],
            'description' => ['nullable', 'string', 'max:2000'],
        ]);

        try {
            $action->execute(Auth::user(), $mentorRequest, $data);
        } catch (Exception $exception) {
            throw ValidationException::withMessages([
                'mentor_request' => $exception->getMessage(),
            ]);
        }

        return back()->with('status', 'Báo cáo mentor request đã được gửi.');
    })->name('mentor.requests.report');

    Route::post('app/mentor/availability', function (ToggleMentorAvailabilityAction $action) {
        $profile = Auth::user()->mentorProfile;
        abort_unless($profile, 403);
        $data = request()->validate([
            'availability_status' => ['required', 'string', 'in:available,paused,full,hidden'],
        ]);

        $action->execute(Auth::user(), $profile, MentorAvailabilityStatus::from($data['availability_status']));

        return back()->with('status', 'Trạng thái mentor đã được cập nhật.');
    })->name('mentor.availability');

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
        Route::get('console/{group?}', function (?string $group = null) {
            $groups = AdminNavigation::getVisibleGroups();
            abort_if(empty($groups), 403);

            $selectedGroupKey = $group && array_key_exists($group, $groups)
                ? $group
                : array_key_first($groups);

            return view('admin.console', [
                'groups' => $groups,
                'selectedGroupKey' => $selectedGroupKey,
            ]);
        })->name('console');

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
                'notifications' => $user?->notifications()->reorder('created_at', 'desc')->paginate(20),
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

        // Communities — core management
        Route::get('communities', [CommunityController::class, 'index'])->name('communities.index');
        Route::get('communities/create', [CommunityController::class, 'create'])->name('communities.create');
        Route::post('communities', [CommunityController::class, 'store'])->name('communities.store');
        Route::get('communities/{community}', [CommunityController::class, 'show'])->name('communities.show');
        Route::post('communities/{community}/update', [CommunityController::class, 'update'])->name('communities.update');
        Route::post('communities/{community}/suspend', [CommunityController::class, 'suspend'])->name('communities.suspend');
        Route::post('communities/{community}/reactivate', [CommunityController::class, 'reactivate'])->name('communities.reactivate');
        Route::post('communities/{community}/archive', [CommunityController::class, 'archive'])->name('communities.archive');
        Route::post('communities/{community}/members', [CommunityController::class, 'addMember'])->name('communities.members.add');
        Route::delete('communities/{community}/members/{user}', [CommunityController::class, 'removeMember'])->name('communities.members.remove');
        Route::post('communities/{community}/managers/{user}/grant', [CommunityController::class, 'grantManager'])->name('communities.managers.grant');
        Route::post('communities/{community}/managers/{user}/revoke', [CommunityController::class, 'revokeManager'])->name('communities.managers.revoke');

        // Community join requests
        Route::get('communities/{community}/join-requests', [CommunityController::class, 'joinRequests'])->name('communities.join-requests.index');
        Route::post('community-join-requests/{joinRequest}/approve', [CommunityController::class, 'approveJoinRequest'])->name('community-join-requests.approve');
        Route::post('community-join-requests/{joinRequest}/reject', [CommunityController::class, 'rejectJoinRequest'])->name('community-join-requests.reject');

        // Community resources review
        Route::post('community-resources/{resource}/review', [CommunityController::class, 'reviewResource'])->name('community-resources.review');

        // Community suggestions
        Route::get('community-suggestions', [CommunityController::class, 'suggestions'])->name('community-suggestions.index');
        Route::post('community-suggestions/{suggestion}/review', [CommunityController::class, 'reviewSuggestion'])->name('community-suggestions.review');

        // Mentor Access
        Route::get('mentors', [MentorAccessController::class, 'index'])->name('mentors.index');
        Route::get('mentors/{id}', [MentorAccessController::class, 'show'])->name('mentors.detail');
        Route::post('mentors/{mentorAccess}/action', [MentorAccessController::class, 'handle'])->name('mentors.action');
        Route::post('mentors/{mentorAccessRequest}/approve', [MentorAccessController::class, 'approve'])->name('mentors.approve');
        Route::post('mentors/{mentorAccessRequest}/reject', [MentorAccessController::class, 'reject'])->name('mentors.reject');
        Route::post('mentors/{mentorAccessRequest}/need-more-info', [MentorAccessController::class, 'needMoreInfo'])->name('mentors.need-more-info');
        Route::post('mentors/{mentorProfile}/revoke', [MentorAccessController::class, 'revoke'])->name('mentors.revoke');
        Route::post('mentors/{user}/grant', [MentorAccessController::class, 'grant'])->name('mentors.grant');

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

// 6. Artisan command runner (temporary helper for production setup without shell)
Route::get('/run-artisan', function () {
    if (request('token') !== 'ueconnect_secret_token_2026') {
        abort(403, 'Unauthorized');
    }

    $command = request('command', 'migrate');

    if (! in_array($command, ['migrate', 'db:seed', 'optimize:clear', 'config:clear'])) {
        return 'Lệnh không được hỗ trợ để chạy qua Web.';
    }

    $params = ['--force' => true];
    if ($command === 'db:seed' && request()->has('class')) {
        $class = request('class');
        $class = str_replace('/', '\\', $class);

        if (! str_contains($class, '\\')) {
            if (class_exists("Database\\Seeders\\Reference\\{$class}")) {
                $class = "Database\\Seeders\\Reference\\{$class}";
            } elseif (class_exists("Database\\Seeders\\Uat\\{$class}")) {
                $class = "Database\\Seeders\\Uat\\{$class}";
            } elseif (class_exists("Database\\Seeders\\{$class}")) {
                $class = "Database\\Seeders\\{$class}";
            }
        }

        $params['--class'] = $class;
    }

    try {
        Artisan::call($command, $params);

        return '<pre>'.Artisan::output().'</pre>';
    } catch (Throwable $e) {
        return 'Lỗi: '.$e->getMessage()."\n\n".$e->getTraceAsString();
    }
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

Route::get('/run-diagnostics', function () {
    if (request('token') !== 'ueconnect_secret_token_2026') {
        abort(403, 'Unauthorized');
    }

    $results = [];

    // 1. Check environment variables & config
    $results['active_strategy'] = config('media.storage.strategy', config('media.default_strategy', 'local_only'));
    $results['public_disk'] = config('media.public_disk', 'public');
    $results['private_disk'] = config('media.private_disk', 'private');
    $results['r2_enabled'] = config('media.r2.enabled');
    $results['cloudinary_enabled'] = config('media.providers.cloudinary.enabled');

    // 2. Check GD extension
    $results['gd_loaded'] = extension_loaded('gd');
    if ($results['gd_loaded']) {
        $results['gd_info'] = gd_info();
    }

    // 3. Test R2 Public Disk Connection
    try {
        $publicDisk = config('media.public_disk', 'r2_public');
        Storage::disk($publicDisk)->put('diagnostics_test_public.txt', 'public test '.now()->toIso8601String());
        $results['r2_public_write'] = 'SUCCESS';
        $results['r2_public_url'] = Storage::disk($publicDisk)->url('diagnostics_test_public.txt');
        Storage::disk($publicDisk)->delete('diagnostics_test_public.txt');
    } catch (Throwable $e) {
        $results['r2_public_write_error'] = $e->getMessage()."\n".$e->getTraceAsString();
    }

    // 4. Test R2 Private Disk Connection
    try {
        $privateDisk = config('media.private_disk', 'r2_private');
        Storage::disk($privateDisk)->put('diagnostics_test_private.txt', 'private test '.now()->toIso8601String());
        $results['r2_private_write'] = 'SUCCESS';
        Storage::disk($privateDisk)->delete('diagnostics_test_private.txt');
    } catch (Throwable $e) {
        $results['r2_private_write_error'] = $e->getMessage()."\n".$e->getTraceAsString();
    }

    // 5. Check logs directory
    $logPath = storage_path('logs');
    if (is_dir($logPath)) {
        $results['logs_dir_writable'] = is_writable($logPath);
        $results['log_files'] = scandir($logPath);

        $laravelLog = storage_path('logs/laravel.log');
        if (file_exists($laravelLog)) {
            $lines = file($laravelLog);
            $tail = array_slice($lines, -150);
            $results['laravel_log_tail'] = implode('', $tail);
        } else {
            $results['laravel_log_status'] = 'laravel.log not found';
        }
    } else {
        $results['logs_dir_status'] = 'logs dir not found';
    }

    return response()->json($results, 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
});

require __DIR__.'/auth.php';
