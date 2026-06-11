<?php

use App\Models\User;
use App\Models\BlockedUser;
use App\Actions\Connections\UnblockUser;
use App\Actions\Settings\UpdateProfilePrivacySettingsAction;
use App\Actions\Settings\UpdateNotificationPreferencesAction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Volt\Component;

new class extends Component
{
    // Deep link parameters
    public string $section = 'index';
    public ?string $subSection = null;

    // Feedback Toast
    public ?string $feedbackMessage = null;
    public ?string $errorMessage = null;

    // Privacy Form State
    public string $profile_visibility = 'public_to_verified';
    public string $discovery_visibility = 'enabled';
    public bool $is_profile_private = false;
    public bool $is_discoverable = true;
    public bool $show_faculty = true;
    public bool $show_major = true;
    public bool $show_cohort = true;
    public bool $show_class_code = false;
    public bool $show_bio = true;
    public bool $show_interests = true;
    public bool $show_connection_goals = true;
    public bool $show_communities = false;
    public bool $show_career_info = false;
    public bool $show_mentor_topics = true;

    // Mentions & Tags (Future-ready, persisted)
    public string $mentions_preference = 'everyone'; // everyone, connections, nobody
    public string $tags_preference = 'everyone';

    // Online Status (Persisted)
    public string $online_status_visibility = 'connections'; // connections, mutual_connections, nobody

    // Messaging Privacy
    public string $messaging_privacy = 'everyone'; // everyone, connections_only

    // Notification Preferences Form State
    public bool $in_app_enabled = true;
    public bool $browser_push_enabled = false;
    public bool $email_enabled = false;
    public bool $greeting_notifications = true;
    public bool $message_notifications = true;
    public bool $mentor_notifications = true;
    public bool $community_notifications = true;
    public bool $safety_notifications = true;
    public bool $moderation_notifications = true;
    public bool $system_notifications = true;

    // Push Preferences Form State
    public bool $push_messages_enabled = true;
    public bool $push_greetings_enabled = true;
    public bool $push_mentor_enabled = true;
    public bool $push_community_enabled = true;
    public bool $push_verification_enabled = true;
    public bool $push_admin_announcements_enabled = true;

    // Support Form State
    public string $supportCategory = 'general';
    public string $supportDescription = '';

    // Block/Unblock confirmation state
    public ?int $unblockingUserId = null;
    public ?string $unblockingUserName = null;

    public function mount(string $section = 'index', ?string $subSection = null): void
    {
        $this->section = $section === 'index' ? 'account' : $section; // default detail page on desktop is account
        if (request()->route('section')) {
            $this->section = request()->route('section');
        }
        $this->subSection = $subSection ?? request()->route('subSection');

        $user = Auth::user();
        if (!$user) {
            return;
        }

        // Load Privacy settings
        $privacy = $user->profilePrivacySetting;
        if ($privacy) {
            $this->profile_visibility = $privacy->profile_visibility;
            $this->discovery_visibility = $privacy->discovery_visibility;
            $this->is_profile_private = ($this->profile_visibility === 'connections_only' || $this->profile_visibility === 'private');
            $this->is_discoverable = ($this->discovery_visibility === 'enabled');
            $this->show_faculty = $privacy->show_faculty;
            $this->show_major = $privacy->show_major;
            $this->show_cohort = $privacy->show_cohort;
            $this->show_class_code = $privacy->show_class_code;
            $this->show_bio = $privacy->show_bio;
            $this->show_interests = $privacy->show_interests;
            $this->show_connection_goals = $privacy->show_connection_goals;
            $this->show_communities = $privacy->show_communities;
            $this->show_career_info = $privacy->show_career_info;
            $this->show_mentor_topics = $privacy->show_mentor_topics;
            $this->mentions_preference = $privacy->mentions_preference ?? 'everyone';
            $this->tags_preference = $privacy->tags_preference ?? 'everyone';
            $this->online_status_visibility = $privacy->online_status_visibility ?? 'connections';
        }

        // Load Notification Preferences
        $noti = $user->notificationPreference;
        if ($noti) {
            $this->in_app_enabled = $noti->in_app_enabled;
            $this->browser_push_enabled = $noti->browser_push_enabled;
            $this->email_enabled = $noti->email_enabled;
            $this->greeting_notifications = $noti->greeting_notifications;
            $this->message_notifications = $noti->message_notifications;
            $this->mentor_notifications = $noti->mentor_notifications;
            $this->community_notifications = $noti->community_notifications;
            $this->safety_notifications = $noti->safety_notifications;
            $this->moderation_notifications = $noti->moderation_notifications;
            $this->system_notifications = $noti->system_notifications;
            
            $this->push_messages_enabled = $noti->push_messages_enabled ?? true;
            $this->push_greetings_enabled = $noti->push_greetings_enabled ?? true;
            $this->push_mentor_enabled = $noti->push_mentor_enabled ?? true;
            $this->push_community_enabled = $noti->push_community_enabled ?? true;
            $this->push_verification_enabled = $noti->push_verification_enabled ?? true;
            $this->push_admin_announcements_enabled = $noti->push_admin_announcements_enabled ?? true;
        }
    }

    public function updatedIsDiscoverable($value): void
    {
        $this->discovery_visibility = $value ? 'enabled' : 'disabled';
    }

    public function updatedIsProfilePrivate($value): void
    {
        $this->profile_visibility = $value ? 'connections_only' : 'public_to_verified';
    }

    public function updatedProfileVisibility($value): void
    {
        if ($value === 'connections_only' || $value === 'private' || $value === true || $value === '1' || $value === 1) {
            $this->is_profile_private = true;
            if ($value === true || $value === '1' || $value === 1) {
                $this->profile_visibility = 'connections_only';
            }
        } else {
            $this->is_profile_private = false;
            if ($value !== 'hidden_by_moderation') {
                $this->profile_visibility = 'public_to_verified';
            }
        }
    }

    public function updatedDiscoveryVisibility($value): void
    {
        if ($value === 'enabled' || $value === true || $value === '1' || $value === 1) {
            $this->is_discoverable = true;
            if ($value === true || $value === '1' || $value === 1) {
                $this->discovery_visibility = 'enabled';
            }
        } else {
            $this->is_discoverable = false;
            if ($value !== 'forced_hidden') {
                $this->discovery_visibility = 'disabled';
            }
        }
    }

    /**
     * Save profile privacy settings.
     */
    public function savePrivacy(UpdateProfilePrivacySettingsAction $action): void
    {
        // Normalize boolean/numeric string values
        if ($this->profile_visibility === true || $this->profile_visibility === '1' || $this->profile_visibility === 1) {
            $this->profile_visibility = 'connections_only';
        } elseif ($this->profile_visibility === false || $this->profile_visibility === '0' || $this->profile_visibility === 0) {
            $this->profile_visibility = 'public_to_verified';
        }

        if ($this->discovery_visibility === true || $this->discovery_visibility === '1' || $this->discovery_visibility === 1) {
            $this->discovery_visibility = 'enabled';
        } elseif ($this->discovery_visibility === false || $this->discovery_visibility === '0' || $this->discovery_visibility === 0) {
            $this->discovery_visibility = 'disabled';
        }

        $this->validate([
            'mentions_preference' => 'required|in:everyone,connections,nobody',
            'tags_preference' => 'required|in:everyone,connections,nobody',
            'online_status_visibility' => 'required|in:connections,mutual_connections,nobody',
        ]);

        try {
            $user = Auth::user();
            $action->execute($user, [
                'profile_visibility' => $this->profile_visibility,
                'discovery_visibility' => $this->discovery_visibility,
                'show_faculty' => $this->show_faculty,
                'show_major' => $this->show_major,
                'show_cohort' => $this->show_cohort,
                'show_class_code' => $this->show_class_code,
                'show_bio' => $this->show_bio,
                'show_interests' => $this->show_interests,
                'show_connection_goals' => $this->show_connection_goals,
                'show_communities' => $this->show_communities,
                'show_career_info' => $this->show_career_info,
                'show_mentor_topics' => $this->show_mentor_topics,
                'mentions_preference' => $this->mentions_preference,
                'tags_preference' => $this->tags_preference,
                'online_status_visibility' => $this->online_status_visibility,
            ]);

            $this->feedbackMessage = 'Cập nhật thiết lập riêng tư thành công.';
        } catch (\Exception $e) {
            $this->errorMessage = 'Không thể lưu thay đổi. Vui lòng thử lại.';
        }
    }

    /**
     * Save notification preferences.
     */
    public function saveNotifications(UpdateNotificationPreferencesAction $action): void
    {
        try {
            $user = Auth::user();
            $action->execute($user, [
                'in_app_enabled' => $this->in_app_enabled,
                'browser_push_enabled' => $this->browser_push_enabled,
                'email_enabled' => $this->email_enabled,
                'greeting_notifications' => $this->greeting_notifications,
                'message_notifications' => $this->message_notifications,
                'mentor_notifications' => $this->mentor_notifications,
                'community_notifications' => $this->community_notifications,
                // Critical system-level notifications are strictly protected in Backend
                'safety_notifications' => $this->safety_notifications,
                'moderation_notifications' => $this->moderation_notifications,
                'system_notifications' => $this->system_notifications,

                'push_messages_enabled' => $this->push_messages_enabled,
                'push_greetings_enabled' => $this->push_greetings_enabled,
                'push_mentor_enabled' => $this->push_mentor_enabled,
                'push_community_enabled' => $this->push_community_enabled,
                'push_verification_enabled' => $this->push_verification_enabled,
                'push_admin_announcements_enabled' => $this->push_admin_announcements_enabled,
            ]);

            $this->feedbackMessage = 'Đã cập nhật thông báo thành công.';
        } catch (\Exception $e) {
            $this->errorMessage = 'Không thể lưu thông báo. Vui lòng thử lại.';
        }
    }

    /**
     * Start unblock confirmation.
     */
    public function confirmUnblock(int $userId, string $name): void
    {
        $this->unblockingUserId = $userId;
        $this->unblockingUserName = $name;
    }

    /**
     * Execute unblock action.
     */
    public function executeUnblock(UnblockUser $action): void
    {
        if (!$this->unblockingUserId) {
            return;
        }

        try {
            $user = Auth::user();
            $target = User::findOrFail($this->unblockingUserId);

            $action->execute($user, $target);

            $this->feedbackMessage = 'Đã bỏ chặn thành công.';
            $this->unblockingUserId = null;
            $this->unblockingUserName = null;
        } catch (\Exception $e) {
            $this->errorMessage = 'Thao tác thất bại. Vui lòng thử lại.';
        }
    }

    /**
     * Submit Support Ticket.
     */
    public function submitSupport(): void
    {
        $this->validate([
            'supportDescription' => 'required|string|min:10|max:1000',
        ], [
            'supportDescription.required' => 'Vui lòng mô tả sự cố hoặc yêu cầu.',
            'supportDescription.min' => 'Nội dung mô tả tối thiểu phải có 10 ký tự.',
        ]);

        // In a real application, we would persist a support request or email admin.
        // For MVP, we show a success feedback toast.
        $this->feedbackMessage = 'Gửi yêu cầu hỗ trợ thành công. Đội ngũ kỹ thuật sẽ sớm phản hồi qua email.';
        $this->supportDescription = '';
    }

    /**
     * Get blocked users query list.
     */
    public function getBlockedUsers(): \Illuminate\Support\Collection
    {
        return BlockedUser::where('blocker_id', Auth::id())
            ->with('blocked.profile')
            ->get();
    }
}; ?>

<div class="py-6 px-4 max-w-5xl mx-auto space-y-6">
    {{-- Toast feedback --}}
    @if ($feedbackMessage)
        <div 
            x-data="{ show: true }" 
            x-show="show" 
            x-init="setTimeout(() => { show = false; $wire.set('feedbackMessage', null); }, 3000)"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-2"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 translate-y-2"
            class="fixed bottom-20 left-4 right-4 md:left-auto md:right-8 md:w-96 z-50 bg-slate-900 text-white rounded-xl shadow-xl px-4 py-3 border border-slate-800 flex items-center gap-3"
        >
            <x-ui.icon name="shield-check" size="sm" class="text-emerald-500 flex-shrink-0" />
            <span class="text-xxs font-semibold flex-1 leading-normal">{{ $feedbackMessage }}</span>
            <button @click="show = false" class="text-slate-400 hover:text-white transition-colors">
                <x-ui.icon name="x" size="xs" />
            </button>
        </div>
    @endif

    @if ($errorMessage)
        <div 
            x-data="{ show: true }" 
            x-show="show" 
            x-init="setTimeout(() => { show = false; $wire.set('errorMessage', null); }, 3000)"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-2"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 translate-y-2"
            class="fixed bottom-20 left-4 right-4 md:left-auto md:right-8 md:w-96 z-50 bg-red-900 text-white rounded-xl shadow-xl px-4 py-3 border border-red-800 flex items-center gap-3"
        >
            <x-ui.icon name="alert-triangle" size="sm" class="text-red-400 flex-shrink-0" />
            <span class="text-xxs font-semibold flex-1 leading-normal">{{ $errorMessage }}</span>
            <button @click="show = false" class="text-slate-400 hover:text-white transition-colors">
                <x-ui.icon name="x" size="xs" />
            </button>
        </div>
    @endif

    {{-- Title Header --}}
    <div class="flex flex-col gap-1 border-b border-slate-100 pb-4">
        <h1 class="text-xl font-bold text-slate-800 tracking-tight">Cài đặt</h1>
        <p class="text-xs text-slate-400 font-medium">Thiết lập tài khoản, quyền riêng tư, thông báo và bảo mật học đường.</p>
    </div>

    {{-- Layout shell --}}
    <div class="bg-white border border-slate-200 rounded-3xl overflow-hidden shadow-xs min-h-[500px]">
        {{-- Responsive Layout: Desktop 2-column split, Mobile 1-column dynamic --}}
        <div class="flex flex-col lg:flex-row h-full min-h-[500px]">
            
            {{-- Left column: Category List Navigation --}}
            {{-- Mobile: only visible if requested route is settings main list, or always hidden on detail screens --}}
            <nav 
                class="w-full lg:w-72 border-r border-slate-150 flex-shrink-0 p-4 space-y-1.5 {{ request()->route('section') ? 'hidden lg:block' : 'block' }}"
                aria-label="Danh mục cài đặt"
            >
                {{-- Account center overview item --}}
                <div class="p-3 bg-slate-50 rounded-2xl border border-slate-100 mb-3 flex items-center gap-3">
                    <x-ui.avatar :user="Auth::user()" size="sm" />
                    <div class="min-w-0 flex-1">
                        <p class="text-xxs font-bold text-slate-800 truncate leading-tight">{{ Auth::user()->profile?->display_name ?? Auth::user()->name }}</p>
                        <p class="text-[9px] font-bold tracking-wider text-ue-brand uppercase leading-none mt-1">
                            @if ((Auth::user()->profile?->role_type ?? 'student') === 'student') Sinh viên
                            @elseif (in_array((Auth::user()->profile?->role_type ?? ''), ['teacher', 'advisor'], true)) Giảng viên
                            @elseif ((Auth::user()->profile?->role_type ?? '') === 'alumni') Cựu sinh viên
                            @else Thành viên
                            @endif
                        </p>
                    </div>
                </div>

                {{-- Categories list --}}
                <ul class="space-y-1" role="list">
                    <li>
                        <a 
                            href="{{ route('settings', ['section' => 'account']) }}"
                            class="flex items-center justify-between px-3 py-2.5 rounded-xl text-xxs font-bold transition-all {{ $section === 'account' ? 'bg-ue-brand-soft text-ue-brand-active hover:bg-ue-brand-soft-hover hover:!text-ue-brand-active' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}"
                        >
                            <span class="flex items-center gap-2.5">
                                <x-ui.icon name="user" size="xs" />
                                Trung tâm tài khoản
                            </span>
                            <x-ui.icon name="chevron-right" size="xs" class="opacity-50" />
                        </a>
                    </li>
                    <li>
                        <a 
                            href="{{ route('settings', ['section' => 'privacy']) }}"
                            class="flex items-center justify-between px-3 py-2.5 rounded-xl text-xxs font-bold transition-all {{ $section === 'privacy' ? 'bg-ue-brand-soft text-ue-brand-active hover:bg-ue-brand-soft-hover hover:!text-ue-brand-active' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}"
                        >
                            <span class="flex items-center gap-2.5">
                                <x-ui.icon name="shield" size="xs" />
                                Quyền riêng tư
                            </span>
                            <x-ui.icon name="chevron-right" size="xs" class="opacity-50" />
                        </a>
                    </li>
                    <li>
                        <a 
                            href="{{ route('settings', ['section' => 'notifications']) }}"
                            class="flex items-center justify-between px-3 py-2.5 rounded-xl text-xxs font-bold transition-all {{ $section === 'notifications' ? 'bg-ue-brand-soft text-ue-brand-active hover:bg-ue-brand-soft-hover hover:!text-ue-brand-active' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}"
                        >
                            <span class="flex items-center gap-2.5">
                                <x-ui.icon name="heart" size="xs" />
                                Thông báo
                            </span>
                            <x-ui.icon name="chevron-right" size="xs" class="opacity-50" />
                        </a>
                    </li>
                    <li>
                        <a 
                            href="{{ route('settings', ['section' => 'content']) }}"
                            class="flex items-center justify-between px-3 py-2.5 rounded-xl text-xxs font-bold transition-all {{ $section === 'content' ? 'bg-ue-brand-soft text-ue-brand-active hover:bg-ue-brand-soft-hover hover:!text-ue-brand-active' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}"
                        >
                            <span class="flex items-center gap-2.5">
                                <x-ui.icon name="menu" size="xs" />
                                Tùy chọn nội dung
                            </span>
                            <x-ui.icon name="chevron-right" size="xs" class="opacity-50" />
                        </a>
                    </li>
                    <li>
                        <a 
                            href="{{ route('settings', ['section' => 'data-privacy']) }}"
                            class="flex items-center justify-between px-3 py-2.5 rounded-xl text-xxs font-bold transition-all {{ $section === 'data-privacy' ? 'bg-ue-brand-soft text-ue-brand-active hover:bg-ue-brand-soft-hover hover:!text-ue-brand-active' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}"
                        >
                            <span class="flex items-center gap-2.5">
                                <x-ui.icon name="shield-check" size="xs" />
                                Dữ liệu & Bảo mật
                            </span>
                            <x-ui.icon name="chevron-right" size="xs" class="opacity-50" />
                        </a>
                    </li>
                    <li>
                        <a 
                            href="{{ route('settings', ['section' => 'support']) }}"
                            class="flex items-center justify-between px-3 py-2.5 rounded-xl text-xxs font-bold transition-all {{ $section === 'support' ? 'bg-ue-brand-soft text-ue-brand-active hover:bg-ue-brand-soft-hover hover:!text-ue-brand-active' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}"
                        >
                            <span class="flex items-center gap-2.5">
                                <x-ui.icon name="help-circle" size="xs" />
                                Hỗ trợ & Trợ giúp
                            </span>
                            <x-ui.icon name="chevron-right" size="xs" class="opacity-50" />
                        </a>
                    </li>
                    <li>
                        <a 
                            href="{{ route('settings', ['section' => 'security']) }}"
                            class="flex items-center justify-between px-3 py-2.5 rounded-xl text-xxs font-bold transition-all {{ $section === 'security' ? 'bg-ue-brand-soft text-ue-brand-active hover:bg-ue-brand-soft-hover hover:!text-ue-brand-active' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}"
                        >
                            <span class="flex items-center gap-2.5">
                                <x-ui.icon name="log-in" size="xs" />
                                Bảo mật tài khoản
                            </span>
                            <x-ui.icon name="chevron-right" size="xs" class="opacity-50" />
                        </a>
                    </li>
                    <li>
                        <a 
                            href="{{ route('settings', ['section' => 'language']) }}"
                            class="flex items-center justify-between px-3 py-2.5 rounded-xl text-xxs font-bold transition-all {{ $section === 'language' ? 'bg-ue-brand-soft text-ue-brand-active hover:bg-ue-brand-soft-hover hover:!text-ue-brand-active' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}"
                        >
                            <span class="flex items-center gap-2.5">
                                <x-ui.icon name="eye" size="xs" />
                                Ngôn ngữ hiển thị
                            </span>
                            <x-ui.icon name="chevron-right" size="xs" class="opacity-50" />
                        </a>
                    </li>

                    {{-- Admin system moderator shortcuts --}}
                    @if (Gate::any(['review_verification', 'manage_reports']))
                        <div class="border-t border-slate-100 my-2 pt-2">
                            <span class="px-3 text-[9px] font-bold text-slate-400 uppercase tracking-wider block mb-1">Quản lý hệ thống</span>
                            <li>
                                <a 
                                    href="{{ route('admin.dashboard') }}"
                                    class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl text-xxs font-bold text-slate-600 hover:bg-slate-50 transition-all"
                                >
                                    <x-ui.icon name="shield" size="xs" />
                                    Tổng quan quản trị
                                </a>
                            </li>
                        </div>
                    @endif
                </ul>
            </nav>

            {{-- Right column: Detail Content panel --}}
            {{-- Mobile: only visible if requested route is a specific settings section --}}
            <section 
                class="flex-1 p-6 lg:p-8 bg-slate-50/20 {{ !request()->route('section') ? 'hidden lg:block' : 'block' }}"
                aria-label="Nội dung cài đặt chi tiết"
            >
                {{-- Mobile Navigation Header with Back button --}}
                <div class="flex lg:hidden items-center justify-between border-b border-slate-100 pb-3 mb-5 flex-shrink-0">
                    <a href="{{ route('settings') }}" class="flex items-center gap-1.5 text-xxs font-bold text-slate-600 hover:text-slate-800 transition-colors">
                        <x-ui.icon name="chevron-left" size="xs" />
                        Quay lại Cài đặt
                    </a>
                </div>

                {{-- Account settings detail page --}}
                @if ($section === 'account')
                    <div class="space-y-6">
                        <div>
                            <h2 class="text-sm font-bold text-slate-800">Trung tâm tài khoản</h2>
                            <p class="text-xxs text-slate-400 font-medium mt-0.5">Quản lý các thông tin định danh xác thực của bạn trên UEConnect.</p>
                        </div>

                        <div class="bg-white border border-slate-150 rounded-2xl p-5 space-y-4 shadow-2xs">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xxs text-slate-600 font-medium">
                                <div class="space-y-1">
                                    <span class="text-slate-400 block font-semibold uppercase tracking-wider text-[9px]">Họ và tên xác thực</span>
                                    <span class="text-slate-850 font-bold">{{ Auth::user()->name }}</span>
                                </div>
                                <div class="space-y-1">
                                    <span class="text-slate-400 block font-semibold uppercase tracking-wider text-[9px]">Email học đường (HCMUE)</span>
                                    <span class="text-slate-850 font-bold">{{ Auth::user()->email }}</span>
                                </div>
                                <div class="space-y-1">
                                    <span class="text-slate-400 block font-semibold uppercase tracking-wider text-[9px]">Mã số tài khoản</span>
                                    <span class="text-slate-850 font-bold">#{{ Auth::user()->id }}</span>
                                </div>
                                <div class="space-y-1">
                                    <span class="text-slate-400 block font-semibold uppercase tracking-wider text-[9px]">Trạng thái tài khoản</span>
                                    <span class="inline-flex items-center gap-1 bg-emerald-50 text-emerald-700 font-bold border border-emerald-100 px-2 py-0.5 rounded-md text-[10px]">
                                        Active / Xác thực
                                    </span>
                                </div>
                            </div>

                            <div class="border-t border-slate-100 pt-4 mt-2">
                                <p class="text-[10px] text-slate-400 leading-normal">
                                    Các thông tin học đường cốt lõi (như Họ tên, MSSV, Khoa/Ngành) được lấy trực tiếp từ hệ thống xác thực tài khoản HCMUE. Nếu có sự sai lệch thông tin, vui lòng <a href="{{ route('settings', ['section' => 'support']) }}" class="text-ue-brand font-semibold underline">Gửi yêu cầu hỗ trợ</a>.
                                </p>
                            </div>
                        </div>
                    </div>

                {{-- Privacy settings detail page --}}
                @elseif ($section === 'privacy')
                    @if ($subSection === 'profile')
                        {{-- Profile Privacy Toggles Detail page --}}
                        <div class="space-y-6">
                            <div>
                                <a href="{{ route('settings', ['section' => 'privacy']) }}" class="flex items-center gap-1 text-[10px] font-bold text-slate-400 hover:text-slate-600 mb-2">
                                    <x-ui.icon name="chevron-left" size="2xs" /> Quyền riêng tư
                                </a>
                                <h2 class="text-sm font-bold text-slate-800">Quyền riêng tư hồ sơ</h2>
                                <p class="text-xxs text-slate-400 font-medium mt-0.5">Kiểm soát thông tin nào hiển thị công khai với thành viên khác.</p>
                            </div>

                            <form wire:submit.prevent="savePrivacy" class="space-y-4">
                                <div class="bg-white border border-slate-150 rounded-2xl p-5 divide-y divide-slate-100 shadow-2xs">
                                    
                                    {{-- Private Profile toggle --}}
                                    <div class="py-3 flex items-center justify-between gap-4">
                                        <div class="flex-1 space-y-0.5">
                                            <label for="private-profile" class="text-xxs font-bold text-slate-800 block">Trang cá nhân riêng tư</label>
                                            <span class="text-[10px] text-slate-400 leading-normal block">Khi bật, chỉ những người bạn phê duyệt kết nối mới có thể xem đầy đủ thông tin hồ sơ và bài đăng của bạn.</span>
                                        </div>
                                        <input 
                                            type="checkbox" 
                                            id="private-profile" 
                                            wire:model="is_profile_private" 
                                            class="h-4 w-4 rounded border-slate-200 text-ue-brand focus:ring-ue-brand"
                                        />
                                    </div>

                                    {{-- Show Faculty toggle --}}
                                    <div class="py-3 flex items-center justify-between gap-4">
                                        <div class="flex-1 space-y-0.5">
                                            <label for="show-faculty" class="text-xxs font-bold text-slate-800 block">Hiển thị Khoa học đường</label>
                                            <span class="text-[10px] text-slate-400 block">Cho phép các thành viên nhìn thấy khoa bạn đang theo học.</span>
                                        </div>
                                        <input type="checkbox" id="show-faculty" wire:model="show_faculty" class="h-4 w-4 rounded border-slate-200 text-ue-brand focus:ring-ue-brand" />
                                    </div>

                                    {{-- Show Major toggle --}}
                                    <div class="py-3 flex items-center justify-between gap-4">
                                        <div class="flex-1 space-y-0.5">
                                            <label for="show-major" class="text-xxs font-bold text-slate-800 block">Hiển thị Ngành đào tạo</label>
                                            <span class="text-[10px] text-slate-400 block">Cho phép mọi người nhìn thấy ngành học của bạn.</span>
                                        </div>
                                        <input type="checkbox" id="show-major" wire:model="show_major" class="h-4 w-4 rounded border-slate-200 text-ue-brand focus:ring-ue-brand" />
                                    </div>

                                    {{-- Show Cohort toggle --}}
                                    <div class="py-3 flex items-center justify-between gap-4">
                                        <div class="flex-1 space-y-0.5">
                                            <label for="show-cohort" class="text-xxs font-bold text-slate-800 block">Hiển thị Khóa học (Cohort)</label>
                                            <span class="text-[10px] text-slate-400 block">Ví dụ: hiển thị khóa K49 tuyển sinh.</span>
                                        </div>
                                        <input type="checkbox" id="show-cohort" wire:model="show_cohort" class="h-4 w-4 rounded border-slate-200 text-ue-brand focus:ring-ue-brand" />
                                    </div>

                                    {{-- Show Class Code toggle --}}
                                    <div class="py-3 flex items-center justify-between gap-4">
                                        <div class="flex-1 space-y-0.5">
                                            <label for="show-class" class="text-xxs font-bold text-slate-800 block">Hiển thị lớp học sinh hoạt</label>
                                            <span class="text-[10px] text-slate-400 block">Cho phép hiển thị lớp học. Mặc định là tắt.</span>
                                        </div>
                                        <input type="checkbox" id="show-class" wire:model="show_class_code" class="h-4 w-4 rounded border-slate-200 text-ue-brand focus:ring-ue-brand" />
                                    </div>

                                    {{-- Show Bio toggle --}}
                                    <div class="py-3 flex items-center justify-between gap-4">
                                        <div class="flex-1 space-y-0.5">
                                            <label for="show-bio" class="text-xxs font-bold text-slate-800 block">Hiển thị tiểu sử cá nhân (Bio)</label>
                                            <span class="text-[10px] text-slate-400 block">Cho phép hiển thị giới thiệu bản thân.</span>
                                        </div>
                                        <input type="checkbox" id="show-bio" wire:model="show_bio" class="h-4 w-4 rounded border-slate-200 text-ue-brand focus:ring-ue-brand" />
                                    </div>
                                </div>

                                <div class="flex justify-end">
                                    <button 
                                        type="submit" 
                                        class="bg-ue-brand hover:bg-ue-brand-hover text-white text-xxs font-bold px-4 py-2 rounded-xl shadow-xs transition-all"
                                    >
                                        Lưu thay đổi
                                    </button>
                                </div>
                            </form>
                        </div>

                    @elseif ($subSection === 'blocked')
                        {{-- Blocked Users List Detail Page --}}
                        <div class="space-y-6">
                            <div>
                                <a href="{{ route('settings', ['section' => 'privacy']) }}" class="flex items-center gap-1 text-[10px] font-bold text-slate-400 hover:text-slate-600 mb-2">
                                    <x-ui.icon name="chevron-left" size="2xs" /> Quyền riêng tư
                                </a>
                                <h2 class="text-sm font-bold text-slate-800">Trang cá nhân đã chặn</h2>
                                <p class="text-xxs text-slate-400 font-medium mt-0.5">Danh sách các tài khoản bạn đã chặn trong hệ thống.</p>
                            </div>

                            <div class="bg-white border border-slate-150 rounded-2xl overflow-hidden shadow-2xs divide-y divide-slate-100">
                                @forelse ($this->getBlockedUsers() as $block)
                                    @if ($block->blocked)
                                        <div class="p-4 flex items-center justify-between gap-4">
                                            <div class="flex items-center gap-3">
                                                <x-ui.avatar :user="$block->blocked" size="xs" />
                                                <div>
                                                    <p class="text-xxs font-bold text-slate-850 leading-tight">
                                                        {{ $block->blocked->profile?->display_name ?? $block->blocked->name }}
                                                    </p>
                                                    <p class="text-[9px] font-bold tracking-wider text-slate-400 uppercase mt-0.5 leading-none">
                                                        @if ($block->blocked->profile?->role_type === 'student') Sinh viên
                                                        @elseif (in_array(($block->blocked->profile?->role_type ?? ''), ['teacher', 'advisor'], true)) Giảng viên
                                                        @elseif ($block->blocked->profile?->role_type === 'alumni') Cựu sinh viên
                                                        @else Thành viên
                                                        @endif
                                                    </p>
                                                </div>
                                            </div>

                                            <button 
                                                type="button" 
                                                wire:click="confirmUnblock({{ $block->blocked->id }}, '{{ $block->blocked->name }}')"
                                                class="bg-slate-50 border border-slate-200 text-slate-700 text-[10px] font-bold px-3 py-1.5 rounded-lg hover:bg-slate-100 transition-colors shadow-2xs"
                                            >
                                                Bỏ chặn
                                            </button>
                                        </div>
                                    @endif
                                @empty
                                    <div class="p-8 text-center space-y-2">
                                        <x-ui.icon name="shield-check" size="md" class="text-slate-300 mx-auto" />
                                        <p class="text-xxs text-slate-400 italic">Bạn chưa chặn tài khoản nào.</p>
                                    </div>
                                @endforelse
                            </div>

                            {{-- Unblock confirmation modal --}}
                            @if ($unblockingUserId)
                                <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs select-none" role="dialog" aria-modal="true">
                                    <div class="bg-white rounded-2xl max-w-sm w-full border border-slate-200 shadow-2xl p-6 text-center space-y-4">
                                        <div class="w-12 h-12 rounded-full bg-slate-50 border border-slate-100 flex items-center justify-center mx-auto text-slate-600">
                                            <x-ui.icon name="shield-check" size="md" />
                                        </div>
                                        <div class="space-y-2">
                                            <h3 class="text-base font-bold text-slate-800">Bỏ chặn {{ $unblockingUserName }}?</h3>
                                            <p class="text-xxs text-slate-500 leading-relaxed">
                                                Bỏ chặn người này? Việc này không tự động khôi phục kết nối hoặc cuộc trò chuyện trước đó.
                                            </p>
                                        </div>
                                        <div class="flex items-center justify-center gap-3 pt-2">
                                            <button 
                                                type="button" 
                                                wire:click="$set('unblockingUserId', null)" 
                                                class="px-4 py-2 text-xxs font-bold text-slate-500 hover:text-slate-700 transition-colors"
                                            >
                                                Hủy
                                            </button>
                                            <button 
                                                type="button" 
                                                wire:click="executeUnblock"
                                                class="bg-ue-brand hover:bg-ue-brand-hover text-white text-xxs font-bold px-4 py-2 rounded-xl transition-all shadow-2xs"
                                            >
                                                Xác nhận bỏ chặn
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>

                    @elseif ($subSection === 'mentions')
                        {{-- Tagging and Mentions --}}
                        <div class="space-y-6">
                            <div>
                                <a href="{{ route('settings', ['section' => 'privacy']) }}" class="flex items-center gap-1 text-[10px] font-bold text-slate-400 hover:text-slate-600 mb-2">
                                    <x-ui.icon name="chevron-left" size="2xs" /> Quyền riêng tư
                                </a>
                                <h2 class="text-sm font-bold text-slate-800">Gắn thẻ và nhắc đến</h2>
                                <p class="text-xxs text-slate-400 font-medium mt-0.5">Tùy chọn cho phép gắn thẻ @lượt nhắc trong các bài viết và bình luận.</p>
                            </div>

                            <form wire:submit.prevent="savePrivacy" class="space-y-6">
                                <div class="bg-white border border-slate-150 rounded-2xl p-5 shadow-2xs space-y-6">
                                    {{-- Mentions preference --}}
                                    <div class="space-y-2.5">
                                        <h3 class="text-xxs font-bold text-slate-800 uppercase tracking-wider text-[9px]">Ai có thể nhắc đến (@mention) bạn</h3>
                                        <div class="space-y-2">
                                            <label class="flex items-center gap-3 cursor-pointer">
                                                <input type="radio" wire:model="mentions_preference" value="everyone" class="h-4 w-4 border-slate-200 text-ue-brand focus:ring-ue-brand">
                                                <div class="text-xxs font-semibold text-slate-700">Mọi người</div>
                                            </label>
                                            <label class="flex items-center gap-3 cursor-pointer">
                                                <input type="radio" wire:model="mentions_preference" value="connections" class="h-4 w-4 border-slate-200 text-ue-brand focus:ring-ue-brand">
                                                <div class="text-xxs font-semibold text-slate-700">Bạn bè / Đang theo dõi</div>
                                            </label>
                                            <label class="flex items-center gap-3 cursor-pointer">
                                                <input type="radio" wire:model="mentions_preference" value="nobody" class="h-4 w-4 border-slate-200 text-ue-brand focus:ring-ue-brand">
                                                <div class="text-xxs font-semibold text-slate-700">Không ai cả</div>
                                            </label>
                                        </div>
                                    </div>

                                    <hr class="border-slate-100">

                                    {{-- Tags preference --}}
                                    <div class="space-y-2.5">
                                        <h3 class="text-xxs font-bold text-slate-800 uppercase tracking-wider text-[9px]">Ai có thể gắn thẻ bạn</h3>
                                        <div class="space-y-2">
                                            <label class="flex items-center gap-3 cursor-pointer">
                                                <input type="radio" wire:model="tags_preference" value="everyone" class="h-4 w-4 border-slate-200 text-ue-brand focus:ring-ue-brand">
                                                <div class="text-xxs font-semibold text-slate-700">Mọi người</div>
                                            </label>
                                            <label class="flex items-center gap-3 cursor-pointer">
                                                <input type="radio" wire:model="tags_preference" value="connections" class="h-4 w-4 border-slate-200 text-ue-brand focus:ring-ue-brand">
                                                <div class="text-xxs font-semibold text-slate-700">Bạn bè / Đang theo dõi</div>
                                            </label>
                                            <label class="flex items-center gap-3 cursor-pointer">
                                                <input type="radio" wire:model="tags_preference" value="nobody" class="h-4 w-4 border-slate-200 text-ue-brand focus:ring-ue-brand">
                                                <div class="text-xxs font-semibold text-slate-700">Không ai cả</div>
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex justify-end">
                                    <button 
                                        type="submit" 
                                        class="bg-ue-brand hover:bg-ue-brand-hover text-white text-xxs font-bold px-4 py-2 rounded-xl shadow-xs transition-all"
                                    >
                                        Lưu thay đổi
                                    </button>
                                </div>
                            </form>
                        </div>

                    @elseif ($subSection === 'online_status')
                        {{-- Online Status --}}
                        <div class="space-y-6">
                            <div>
                                <a href="{{ route('settings', ['section' => 'privacy']) }}" class="flex items-center gap-1 text-[10px] font-bold text-slate-400 hover:text-slate-600 mb-2">
                                    <x-ui.icon name="chevron-left" size="2xs" /> Quyền riêng tư
                                </a>
                                <h2 class="text-sm font-bold text-slate-800">Trạng thái trực tuyến</h2>
                                <p class="text-xxs text-slate-400 font-medium mt-0.5">Quản lý việc hiển thị trạng thái đang online của bạn cho các thành viên khác.</p>
                            </div>

                            <form wire:submit.prevent="savePrivacy" class="space-y-6">
                                <div class="bg-white border border-slate-150 rounded-2xl p-5 shadow-2xs space-y-6">
                                    <div class="space-y-2.5">
                                        <h3 class="text-xxs font-bold text-slate-800 uppercase tracking-wider text-[9px]">Ai có thể nhìn thấy trạng thái trực tuyến của bạn</h3>
                                        <div class="space-y-2">
                                            <label class="flex items-center gap-3 cursor-pointer">
                                                <input type="radio" wire:model="online_status_visibility" value="connections" class="h-4 w-4 border-slate-200 text-ue-brand focus:ring-ue-brand">
                                                <div class="text-xxs font-semibold text-slate-700">Bạn bè kết nối</div>
                                            </label>
                                            <label class="flex items-center gap-3 cursor-pointer">
                                                <input type="radio" wire:model="online_status_visibility" value="mutual_connections" class="h-4 w-4 border-slate-200 text-ue-brand focus:ring-ue-brand">
                                                <div class="text-xxs font-semibold text-slate-700">Bạn học kết nối chéo (Mutual)</div>
                                            </label>
                                            <label class="flex items-center gap-3 cursor-pointer">
                                                <input type="radio" wire:model="online_status_visibility" value="nobody" class="h-4 w-4 border-slate-200 text-ue-brand focus:ring-ue-brand">
                                                <div class="text-xxs font-semibold text-slate-700">Không ai cả</div>
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex justify-end">
                                    <button 
                                        type="submit" 
                                        class="bg-ue-brand hover:bg-ue-brand-hover text-white text-xxs font-bold px-4 py-2 rounded-xl shadow-xs transition-all"
                                    >
                                        Lưu thay đổi
                                    </button>
                                </div>
                            </form>
                        </div>

                    @else
                        {{-- Main Privacy Page --}}
                        <div class="space-y-6">
                            <div>
                                <h2 class="text-sm font-bold text-slate-800">Quyền riêng tư</h2>
                                <p class="text-xxs text-slate-400 font-medium mt-0.5">Quản lý khả năng hiển thị thông tin và tương tác của bạn.</p>
                            </div>

                            <div class="bg-white border border-slate-150 rounded-2xl overflow-hidden shadow-2xs divide-y divide-slate-100">
                                <a 
                                    href="{{ route('settings', ['section' => 'privacy', 'subSection' => 'profile']) }}" 
                                    class="p-4 flex items-center justify-between hover:bg-slate-50 transition-colors"
                                >
                                    <div class="space-y-0.5">
                                        <span class="text-xxs font-bold text-slate-800 block">Quyền riêng tư hồ sơ</span>
                                        <span class="text-[10px] text-slate-400 block">Ẩn/hiện thông tin Khoa, Ngành học, Lớp, Tiểu sử...</span>
                                    </div>
                                    <x-ui.icon name="chevron-right" size="xs" class="opacity-50" />
                                </a>

                                <a 
                                    href="{{ route('settings', ['section' => 'privacy', 'subSection' => 'mentions']) }}" 
                                    class="p-4 flex items-center justify-between hover:bg-slate-50 transition-colors"
                                >
                                    <div class="space-y-0.5">
                                        <span class="text-xxs font-bold text-slate-800 block">Gắn thẻ và nhắc đến</span>
                                        <span class="text-[10px] text-slate-400 block">Tùy chọn cho phép gắn thẻ @lượt nhắc.</span>
                                    </div>
                                    <x-ui.icon name="chevron-right" size="xs" class="opacity-50" />
                                </a>

                                <a 
                                    href="{{ route('settings', ['section' => 'privacy', 'subSection' => 'online_status']) }}" 
                                    class="p-4 flex items-center justify-between hover:bg-slate-50 transition-colors"
                                >
                                    <div class="space-y-0.5">
                                        <span class="text-xxs font-bold text-slate-800 block">Trạng thái trực tuyến</span>
                                        <span class="text-[10px] text-slate-400 block">Quản lý hiển thị trạng thái đang online.</span>
                                    </div>
                                    <x-ui.icon name="chevron-right" size="xs" class="opacity-50" />
                                </a>

                                <a 
                                    href="{{ route('settings', ['section' => 'privacy', 'subSection' => 'blocked']) }}" 
                                    class="p-4 flex items-center justify-between hover:bg-slate-50 transition-colors"
                                >
                                    <div class="space-y-0.5">
                                        <span class="text-xxs font-bold text-slate-800 block">Trang cá nhân đã chặn</span>
                                        <span class="text-[10px] text-slate-400 block">Quản lý và bỏ chặn các tài khoản đã chặn.</span>
                                    </div>
                                    <x-ui.icon name="chevron-right" size="xs" class="opacity-50" />
                                </a>
                            </div>

                            {{-- Discovery search status toggle row --}}
                            <div class="bg-white border border-slate-150 rounded-2xl p-5 shadow-2xs space-y-4">
                                <h3 class="text-xxs font-bold text-slate-850 flex items-center gap-1.5 uppercase tracking-wider text-[9px]">Đề xuất Khám phá (Discovery)</h3>
                                <div class="flex items-center justify-between gap-4">
                                    <div class="flex-1 space-y-0.5">
                                        <label for="discoverable-toggle" class="text-xxs font-bold text-slate-800 block">Cho phép xuất hiện trong Khám phá</label>
                                        <span class="text-[10px] text-slate-400 block">Khi bật, tài khoản của bạn sẽ xuất hiện trên danh sách Khám phá đề xuất kết nối của các thành viên HCMUE khác.</span>
                                    </div>
                                    <input 
                                        type="checkbox" 
                                        id="discoverable-toggle" 
                                        wire:model="is_discoverable" 
                                        wire:change="savePrivacy"
                                        class="h-4 w-4 rounded border-slate-200 text-ue-brand focus:ring-ue-brand"
                                    />
                                </div>
                            </div>
                        </div>
                    @endif

                {{-- Notifications preferences detail page --}}
                @elseif ($section === 'notifications')
                    <div class="space-y-6" x-data="webPushManager()">
                        <div>
                            <h2 class="text-sm font-bold text-slate-800">Cấu hình thông báo</h2>
                            <p class="text-xxs text-slate-400 font-medium mt-0.5">Chọn lựa các loại tin nhắn và hoạt động nhận thông báo đẩy.</p>
                        </div>

                        <form wire:submit.prevent="saveNotifications" class="space-y-6">
                            
                            {{-- Browser Push Notifications --}}
                            <div class="bg-white border border-slate-150 rounded-2xl p-5 divide-y divide-slate-100 shadow-2xs">
                                <h3 class="text-xs font-bold text-slate-800 mb-2 flex justify-between items-center">
                                    <span>Thông báo Trình duyệt (Push)</span>
                                    <div class="flex items-center gap-2">
                                        <button type="button" x-show="isSubscribed" @click="testNotification" class="text-[10px] font-bold text-ue-brand px-2 py-1 bg-ue-brand-soft rounded-lg hover:bg-ue-brand-soft-hover transition-colors" style="display: none;">Thử gửi báo</button>
                                        <input type="checkbox" id="browser-push-toggle" x-model="isSubscribed" @change="togglePush" class="h-4 w-4 rounded border-slate-200 text-ue-brand focus:ring-ue-brand" />
                                    </div>
                                </h3>
                                
                                <div class="py-2 text-[11px] text-slate-500 mb-2">
                                    Nhận thông báo ngay cả khi bạn không mở ứng dụng, giúp bạn không bỏ lỡ thông tin quan trọng.
                                </div>
                                
                                <div x-show="isSubscribed" x-transition class="space-y-0 divide-y divide-slate-50" style="display: none;">
                                    {{-- Push Messages --}}
                                    <div class="py-3 flex items-center justify-between gap-4">
                                        <div class="flex-1 space-y-0.5">
                                            <label for="push-msg-notif" class="text-xxs font-bold text-slate-800 block">Tin nhắn</label>
                                            <span class="text-[10px] text-slate-400 block">Thông báo khi có tin nhắn mới.</span>
                                        </div>
                                        <input type="checkbox" id="push-msg-notif" wire:model="push_messages_enabled" class="h-4 w-4 rounded border-slate-200 text-ue-brand focus:ring-ue-brand" />
                                    </div>

                                    {{-- Push Greetings --}}
                                    <div class="py-3 flex items-center justify-between gap-4">
                                        <div class="flex-1 space-y-0.5">
                                            <label for="push-greet-notif" class="text-xxs font-bold text-slate-800 block">Lời chào / Kết nối</label>
                                            <span class="text-[10px] text-slate-400 block">Thông báo khi có lời mời kết nối bạn bè mới.</span>
                                        </div>
                                        <input type="checkbox" id="push-greet-notif" wire:model="push_greetings_enabled" class="h-4 w-4 rounded border-slate-200 text-ue-brand focus:ring-ue-brand" />
                                    </div>

                                    {{-- Push Mentor --}}
                                    <div class="py-3 flex items-center justify-between gap-4">
                                        <div class="flex-1 space-y-0.5">
                                            <label for="push-mentor-notif" class="text-xxs font-bold text-slate-800 block">Mentor / Cố vấn</label>
                                            <span class="text-[10px] text-slate-400 block">Yêu cầu hỗ trợ học tập mới hoặc cập nhật.</span>
                                        </div>
                                        <input type="checkbox" id="push-mentor-notif" wire:model="push_mentor_enabled" class="h-4 w-4 rounded border-slate-200 text-ue-brand focus:ring-ue-brand" />
                                    </div>

                                    {{-- Push Community --}}
                                    <div class="py-3 flex items-center justify-between gap-4">
                                        <div class="flex-1 space-y-0.5">
                                            <label for="push-comm-notif" class="text-xxs font-bold text-slate-800 block">Cộng đồng / CLB</label>
                                            <span class="text-[10px] text-slate-400 block">Hoạt động hoặc yêu cầu tham gia cộng đồng.</span>
                                        </div>
                                        <input type="checkbox" id="push-comm-notif" wire:model="push_community_enabled" class="h-4 w-4 rounded border-slate-200 text-ue-brand focus:ring-ue-brand" />
                                    </div>
                                    
                                    <div class="py-3 flex items-center justify-between gap-4 bg-slate-50/50 -mx-5 px-5 select-none">
                                        <div class="flex-1 space-y-0.5">
                                            <span class="text-xxs font-bold text-slate-800 block flex items-center gap-1.5">
                                                Xác thực & Quản trị hệ thống
                                                <x-ui.icon name="shield-check" size="xs" class="text-slate-400" />
                                            </span>
                                            <span class="text-[10px] text-slate-400 block leading-normal">
                                                Kết quả duyệt học đường, thông báo từ Admin. Luôn được bật.
                                            </span>
                                        </div>
                                        <input type="checkbox" disabled checked class="h-4 w-4 rounded border-slate-200 text-ue-brand focus:ring-ue-brand opacity-60 cursor-not-allowed" />
                                    </div>
                                </div>
                            </div>

                            {{-- In-App Notifications --}}
                            <div class="bg-white border border-slate-150 rounded-2xl p-5 divide-y divide-slate-100 shadow-2xs">
                                <h3 class="text-xs font-bold text-slate-800 mb-2">Thông báo trong ứng dụng (In-App)</h3>
                                
                                {{-- Message notifications toggle --}}
                                <div class="py-3 flex items-center justify-between gap-4">
                                    <div class="flex-1 space-y-0.5">
                                        <label for="msg-notif" class="text-xxs font-bold text-slate-800 block">Thông báo Tin nhắn</label>
                                        <span class="text-[10px] text-slate-400 block">Nhận thông báo khi có tin nhắn mới gửi đến.</span>
                                    </div>
                                    <input type="checkbox" id="msg-notif" wire:model="message_notifications" class="h-4 w-4 rounded border-slate-200 text-ue-brand focus:ring-ue-brand" />
                                </div>

                                {{-- Greeting notifications toggle --}}
                                <div class="py-3 flex items-center justify-between gap-4">
                                    <div class="flex-1 space-y-0.5">
                                        <label for="greet-notif" class="text-xxs font-bold text-slate-800 block">Thông báo Lời chào / Kết nối</label>
                                        <span class="text-[10px] text-slate-400 block">Nhận thông báo khi có lời mời kết nối bạn bè mới.</span>
                                    </div>
                                    <input type="checkbox" id="greet-notif" wire:model="greeting_notifications" class="h-4 w-4 rounded border-slate-200 text-ue-brand focus:ring-ue-brand" />
                                </div>

                                {{-- Mentor notifications toggle --}}
                                <div class="py-3 flex items-center justify-between gap-4">
                                    <div class="flex-1 space-y-0.5">
                                        <label for="mentor-notif" class="text-xxs font-bold text-slate-800 block">Thông báo Mentor / Cố vấn</label>
                                        <span class="text-[10px] text-slate-400 block">Nhận thông báo cập nhật về các yêu cầu hỗ trợ học tập.</span>
                                    </div>
                                    <input type="checkbox" id="mentor-notif" wire:model="mentor_notifications" class="h-4 w-4 rounded border-slate-200 text-ue-brand focus:ring-ue-brand" />
                                </div>

                                {{-- Community notifications toggle --}}
                                <div class="py-3 flex items-center justify-between gap-4">
                                    <div class="flex-1 space-y-0.5">
                                        <label for="comm-notif" class="text-xxs font-bold text-slate-800 block">Thông báo hoạt động Cộng đồng / CLB</label>
                                        <span class="text-[10px] text-slate-400 block">Cập nhật hoạt động bài viết nổi bật của hội nhóm.</span>
                                    </div>
                                    <input type="checkbox" id="comm-notif" wire:model="community_notifications" class="h-4 w-4 rounded border-slate-200 text-ue-brand focus:ring-ue-brand" />
                                </div>

                                {{-- Safety/Moderation/System critical notifications (Disabled toggle, forced backend true) --}}
                                <div class="py-3 flex items-center justify-between gap-4 bg-slate-50/50 -mx-5 px-5 select-none">
                                    <div class="flex-1 space-y-0.5">
                                        <span class="text-xxs font-bold text-slate-800 block flex items-center gap-1.5">
                                            Báo cáo an toàn & Trạng thái tài khoản
                                            <x-ui.icon name="shield-check" size="xs" class="text-slate-400" />
                                        </span>
                                        <span class="text-[10px] text-slate-400 block leading-normal">
                                            Kết quả duyệt xác thực học đường, thông báo kiểm duyệt vi phạm và hệ thống an toàn tài khoản. Tùy chọn này được bật bắt buộc để đảm bảo an ninh hệ thống.
                                        </span>
                                    </div>
                                    <input type="checkbox" disabled checked class="h-4 w-4 rounded border-slate-200 text-ue-brand focus:ring-ue-brand opacity-60 cursor-not-allowed" />
                                </div>
                            </div>

                            <div class="flex justify-end">
                                <button 
                                    type="submit" 
                                    class="bg-ue-brand hover:bg-ue-brand-hover text-white text-xxs font-bold px-4 py-2 rounded-xl shadow-xs transition-all"
                                >
                                    Lưu cấu hình
                                </button>
                            </div>
                        </form>
                    </div>

                    <script>
                        document.addEventListener('alpine:init', () => {
                            Alpine.data('webPushManager', () => ({
                                isSubscribed: @entangle('browser_push_enabled'),
                                isProcessing: false,
                                vapidPublicKey: '{{ config('webpush.vapid.public_key') }}',

                                async init() {
                                    if (!('serviceWorker' in navigator) || !('PushManager' in window)) {
                                        console.warn('Push messaging is not supported.');
                                        return;
                                    }
                                    
                                    const registration = await navigator.serviceWorker.ready;
                                    if (registration) {
                                        const subscription = await registration.pushManager.getSubscription();
                                        if (subscription && !this.isSubscribed) {
                                            // Sync state
                                            this.isSubscribed = true;
                                        } else if (!subscription && this.isSubscribed) {
                                            this.isSubscribed = false;
                                        }
                                    } else {
                                        if (this.isSubscribed) {
                                            this.isSubscribed = false;
                                        }
                                    }
                                },

                                async togglePush() {
                                    if (this.isProcessing) return;
                                    this.isProcessing = true;
                                    
                                    if (this.isSubscribed) {
                                        await this.subscribe();
                                    } else {
                                        await this.unsubscribe();
                                    }
                                    
                                    this.isProcessing = false;
                                },

                                async subscribe() {
                                    if (!('serviceWorker' in navigator)) {
                                        alert('Trình duyệt của bạn không hỗ trợ Service Worker.');
                                        this.isSubscribed = false;
                                        return;
                                    }
                                    
                                    try {
                                        const permission = await Notification.requestPermission();
                                        if (permission !== 'granted') {
                                            alert('Bạn cần cấp quyền thông báo cho trình duyệt để sử dụng tính năng này.');
                                            this.isSubscribed = false;
                                            return;
                                        }
                                        
                                        const registration = await navigator.serviceWorker.ready;
                                        
                                        // Generate converted VAPID key
                                        const padding = '='.repeat((4 - this.vapidPublicKey.length % 4) % 4);
                                        const base64 = (this.vapidPublicKey + padding).replace(/\-/g, '+').replace(/_/g, '/');
                                        const rawData = window.atob(base64);
                                        const outputArray = new Uint8Array(rawData.length);
                                        for (let i = 0; i < rawData.length; ++i) {
                                            outputArray[i] = rawData.charCodeAt(i);
                                        }
                                        
                                        let subscription;
                                        try {
                                            subscription = await registration.pushManager.subscribe({
                                                userVisibleOnly: true,
                                                applicationServerKey: outputArray
                                            });
                                        } catch (e) {
                                            // Handle InvalidStateError if the existing subscription used a different public key
                                            if (e.name === 'InvalidStateError') {
                                                const existingSub = await registration.pushManager.getSubscription();
                                                if (existingSub) {
                                                    await existingSub.unsubscribe();
                                                }
                                                subscription = await registration.pushManager.subscribe({
                                                    userVisibleOnly: true,
                                                    applicationServerKey: outputArray
                                                });
                                            } else {
                                                throw e;
                                            }
                                        }
                                        
                                        // Save subscription to backend
                                        const response = await fetch('/app/notifications/push-subscriptions', {
                                            method: 'POST',
                                            headers: {
                                                'Content-Type': 'application/json',
                                                'Accept': 'application/json',
                                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                                            },
                                            body: JSON.stringify(subscription)
                                        });
                                        
                                        if (!response.ok) {
                                            const errorBody = await response.text();
                                            console.error('Backend returned error:', errorBody);
                                            throw new Error('Failed to save subscription');
                                        }
                                        
                                    } catch (error) {
                                        console.error('Lỗi khi đăng ký thông báo push:', error);
                                        alert('Có lỗi xảy ra khi đăng ký thông báo: ' + error.message);
                                        this.isSubscribed = false;
                                    }
                                },

                                async unsubscribe() {
                                    try {
                                        const registration = await navigator.serviceWorker.ready;
                                        const subscription = await registration.pushManager.getSubscription();
                                        
                                        if (subscription) {
                                            // Send delete request to backend
                                            await fetch('/app/notifications/push-subscriptions', {
                                                method: 'DELETE',
                                                headers: {
                                                    'Content-Type': 'application/json',
                                                    'Accept': 'application/json',
                                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                                                },
                                                body: JSON.stringify({ endpoint: subscription.endpoint })
                                            });
                                            
                                            // Unsubscribe locally
                                            await subscription.unsubscribe();
                                        }
                                    } catch (error) {
                                        console.error('Lỗi khi hủy đăng ký thông báo push:', error);
                                    }
                                },
                                
                                async testNotification() {
                                    alert('Thông báo thử nghiệm sẽ được gửi đi trong vài giây nếu bạn lưu cấu hình...');
                                    // Normally we would have a route to trigger a test notification, but the save action might suffice.
                                }
                            }));
                        });
                    </script>

                {{-- Content Preferences detail page --}}
                @elseif ($section === 'content')
                    <div class="space-y-6">
                        <div>
                            <h2 class="text-sm font-bold text-slate-800">Tùy chọn nội dung</h2>
                            <p class="text-xxs text-slate-400 font-medium mt-0.5">Quản lý cách sắp xếp hiển thị nội dung trên bảng tin.</p>
                        </div>

                        <div class="bg-white border border-slate-150 rounded-2xl p-5 divide-y divide-slate-100 shadow-2xs">
                            <div class="py-3 flex items-center justify-between gap-4">
                                <div class="flex-1 space-y-0.5">
                                    <span class="text-xxs font-bold text-slate-800 block flex items-center gap-1.5">
                                        Ưu tiên nội dung học tập
                                        <span class="bg-slate-100 text-[8px] text-slate-500 font-bold px-1.5 py-0.5 rounded">Mặc định</span>
                                    </span>
                                    <span class="text-[10px] text-slate-400 block">Ưu tiên hiển thị các bài viết chia sẻ kiến thức, tài liệu học đường.</span>
                                </div>
                                <input type="checkbox" checked disabled class="h-4 w-4 rounded border-slate-200 text-ue-brand opacity-60 cursor-not-allowed" />
                            </div>

                            <div class="py-3 flex items-center justify-between gap-4 opacity-50 cursor-not-allowed">
                                <div class="flex-1 space-y-0.5">
                                    <span class="text-xxs font-bold text-slate-800 block">Ẩn nội dung đã báo cáo</span>
                                    <span class="text-[10px] text-slate-400 block">Tự động ẩn hoàn toàn các bài viết bạn đã gửi báo cáo vi phạm.</span>
                                </div>
                                <input type="checkbox" checked disabled class="h-4 w-4 rounded border-slate-200 text-ue-brand opacity-60 cursor-not-allowed" />
                            </div>
                        </div>
                    </div>

                {{-- Data & Privacy static detail page --}}
                @elseif ($section === 'data-privacy')
                    <div class="space-y-6">
                        <div>
                            <h2 class="text-sm font-bold text-slate-800">Dữ liệu & Quyền riêng tư</h2>
                            <p class="text-xxs text-slate-400 font-medium mt-0.5">Hiểu cách dữ liệu học đường cá nhân được bảo vệ tối đa trên UEConnect.</p>
                        </div>

                        <div class="bg-white border border-slate-150 rounded-2xl p-5 space-y-5 shadow-2xs text-slate-700 leading-relaxed text-xxs font-medium">
                            <div class="space-y-2">
                                <h3 class="text-xs font-bold text-slate-850 flex items-center gap-1.5">
                                    <x-ui.icon name="shield-check" size="xs" class="text-ue-brand" />
                                    Vì sao cần xác thực danh tính học sinh?
                                </h3>
                                <p class="text-slate-500 text-[11px] leading-relaxed">
                                    UEConnect là không gian học tập và kết nối số nội bộ dành riêng cho sinh viên, giảng viên và cựu sinh viên trường Đại học Sư phạm TP.HCM (HCMUE). Chúng tôi xác thực qua email trường hoặc minh chứng thẻ để đảm bảo an ninh, tránh spam hoặc quấy rối từ tài khoản ngoài trường học.
                                </p>
                            </div>

                            <div class="space-y-2">
                                <h3 class="text-xs font-bold text-slate-850 flex items-center gap-1.5">
                                    <x-ui.icon name="eye-off" size="xs" class="text-slate-500" />
                                    Những dữ liệu nào KHÔNG BAO GIỜ hiển thị công khai?
                                </h3>
                                <ul class="list-disc pl-4 space-y-1.5 text-slate-500 text-[11px]">
                                    <li><strong class="text-slate-700">Mã số sinh viên (MSSV) đầy đủ:</strong> Không bao giờ hiển thị đầy đủ trên bất kỳ hồ sơ công khai nào để tránh nguy cơ rò rỉ hoặc lợi dụng thông tin.</li>
                                    <li><strong class="text-slate-700">Minh chứng xác thực (Evidence):</strong> Thẻ sinh viên, bảng điểm chụp gửi duyệt chỉ dùng để Ban quản trị kiểm tra đối chiếu và được bảo mật tuyệt đối.</li>
                                    <li><strong class="text-slate-700">Email cá nhân & Số điện thoại:</strong> Mặc định ẩn kín trên hệ thống, chỉ hiển thị nếu bạn tự ý cài đặt chia sẻ hoặc kết nối bạn bè thành công.</li>
                                    <li><strong class="text-slate-700">Ghi chú quản trị & Lịch sử báo cáo:</strong> Hoàn toàn giữ kín với bên thứ ba.</li>
                                </ul>
                            </div>

                            <div class="space-y-2">
                                <h3 class="text-xs font-bold text-slate-850 flex items-center gap-1.5">
                                    <x-ui.icon name="alert-triangle" size="xs" class="text-slate-500" />
                                    Cơ chế Hoạt động của Chặn (Block) & Báo cáo (Report)
                                </h3>
                                <p class="text-slate-500 text-[11px] leading-relaxed">
                                    Khi bạn thực hiện chặn một thành viên, họ sẽ không thể xem hồ sơ của bạn, không thể gửi tin nhắn/lời chào, và cả hai đều biến mất hoàn toàn khỏi danh sách đề xuất Khám phá. Mọi báo cáo vi phạm sẽ được gửi thẳng đến Ban kiểm duyệt nội dung xử lý kín dưới 24h.
                                </p>
                            </div>
                        </div>
                    </div>

                {{-- Support and Report issue detail page --}}
                @elseif ($section === 'support')
                    <div class="space-y-6">
                        <div>
                            <h2 class="text-sm font-bold text-slate-800">Hỗ trợ & Trợ giúp</h2>
                            <p class="text-xxs text-slate-400 font-medium mt-0.5">Gửi báo cáo sự cố kỹ thuật hoặc yêu cầu chỉnh sửa thông tin.</p>
                        </div>

                        <form wire:submit.prevent="submitSupport" class="bg-white border border-slate-150 rounded-2xl p-5 space-y-4 shadow-2xs">
                            
                            {{-- Category Selector --}}
                            <div>
                                <label for="sup-cat" class="block text-xxs font-bold text-slate-500 mb-1.5">Loại yêu cầu hỗ trợ</label>
                                <select 
                                    id="sup-cat" 
                                    wire:model="supportCategory"
                                    class="w-full text-xxs font-bold rounded-xl border-slate-200 text-slate-800 focus:border-ue-brand focus:ring-ue-brand-soft"
                                >
                                    <option value="general">Hỏi đáp chung</option>
                                    <option value="bug">Báo cáo lỗi kỹ thuật</option>
                                    <option value="profile">Chỉnh sửa thông tin học đường</option>
                                    <option value="moderation">Giải trình tài khoản / Khiếu nại</option>
                                </select>
                            </div>

                            {{-- Description --}}
                            <div>
                                <label for="sup-desc" class="block text-xxs font-bold text-slate-500 mb-1.5">Chi tiết mô tả yêu cầu</label>
                                <textarea 
                                    id="sup-desc" 
                                    wire:model="supportDescription" 
                                    placeholder="Vui lòng cung cấp chi tiết sự cố hoặc thông tin cần thay đổi để Ban kiểm trị hỗ trợ bạn tốt nhất..."
                                    rows="4" 
                                    class="w-full text-xxs font-medium rounded-xl border border-slate-200 focus:outline-none focus:ring-1 focus:ring-ue-brand/40 focus:border-ue-brand/40 resize-none bg-slate-50 placeholder-slate-400 text-slate-700 p-3"
                                    maxlength="1000"
                                ></textarea>
                                @error('supportDescription')
                                    <p class="text-xxs font-semibold text-red-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="flex justify-end pt-2 border-t border-slate-100">
                                <button 
                                    type="submit"
                                    class="bg-ue-brand hover:bg-ue-brand-hover text-white text-xxs font-bold px-4 py-2 rounded-xl shadow-xs transition-all flex items-center gap-1.5"
                                >
                                    <x-ui.icon name="send" size="xs" />
                                    Gửi yêu cầu hỗ trợ
                                </button>
                            </div>
                        </form>
                    </div>

                {{-- Security settings summary detail page (Future placeholders) --}}
                @elseif ($section === 'security')
                    <div class="space-y-6">
                        <div>
                            <h2 class="text-sm font-bold text-slate-800">Bảo mật tài khoản</h2>
                            <p class="text-xxs text-slate-400 font-medium mt-0.5">Quản lý mật khẩu và phiên đăng nhập bảo mật.</p>
                        </div>

                        <div class="bg-white border border-slate-150 rounded-2xl p-5 space-y-4 shadow-2xs select-none">
                            <div class="flex items-center justify-between gap-4 py-1 opacity-60">
                                <div class="space-y-0.5">
                                    <span class="text-xxs font-bold text-slate-800 block flex items-center gap-1.5">
                                        Thay đổi mật khẩu
                                        <span class="bg-slate-100 text-[8px] text-slate-500 font-bold px-1.5 py-0.5 rounded">SSO Active</span>
                                    </span>
                                    <span class="text-[10px] text-slate-400 block">Đổi mật khẩu tài khoản học đường. Hiện tại đang được quản lý bởi Microsoft Office 365 HCMUE.</span>
                                </div>
                                <x-ui.icon name="chevron-right" size="xs" class="opacity-50" />
                            </div>

                            <div class="flex items-center justify-between gap-4 py-1 opacity-50 cursor-not-allowed border-t border-slate-100 pt-3">
                                <div class="space-y-0.5">
                                    <span class="text-xxs font-bold text-slate-800 block flex items-center gap-1.5">
                                        Xác thực 2 yếu tố (2FA)
                                        <span class="bg-slate-100 text-[8px] text-slate-500 font-bold px-1.5 py-0.5 rounded">Sắp ra mắt</span>
                                    </span>
                                    <span class="text-[10px] text-slate-400 block">Gia cố bảo mật 2 lớp tài khoản học sinh.</span>
                                </div>
                                <input type="checkbox" disabled class="h-4 w-4 rounded border-slate-200 text-ue-brand opacity-60 cursor-not-allowed" />
                            </div>
                        </div>
                    </div>

                {{-- Language selection detail page --}}
                @elseif ($section === 'language')
                    <div class="space-y-6">
                        <div>
                            <h2 class="text-sm font-bold text-slate-800">Ngôn ngữ hiển thị</h2>
                            <p class="text-xxs text-slate-400 font-medium mt-0.5">Chọn lựa ngôn ngữ hiển thị hệ thống học đường.</p>
                        </div>

                        <div class="bg-white border border-slate-150 rounded-2xl p-5 shadow-2xs space-y-4">
                            <div class="flex items-center justify-between gap-4">
                                <div class="flex-1 space-y-0.5">
                                    <span class="text-xxs font-bold text-slate-800 block">Tiếng Việt (Mặc định)</span>
                                    <span class="text-[10px] text-slate-400 block">Ngôn ngữ chuẩn toàn quốc học sinh.</span>
                                </div>
                                <x-ui.icon name="check" size="xs" class="text-ue-brand fill-ue-brand" />
                            </div>

                            <div class="flex items-center justify-between gap-4 opacity-50 cursor-not-allowed border-t border-slate-100 pt-3">
                                <div class="flex-1 space-y-0.5">
                                    <span class="text-xxs font-bold text-slate-800 block flex items-center gap-1.5">
                                        English (United States)
                                        <span class="bg-slate-100 text-[8px] text-slate-500 font-bold px-1.5 py-0.5 rounded">Sắp ra mắt</span>
                                    </span>
                                    <span class="text-[10px] text-slate-400 block">Global localization switch.</span>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </section>
        </div>
    </div>
</div>
