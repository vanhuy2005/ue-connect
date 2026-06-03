{{--
    App Sidebar Partial (v2)
    Source: docs/04-design/18-responsive-rules.md, 19-design-token-documentation.md
    
    Threads-inspired navigation shell with primary links, secondary list,
    and an accessible "Xem thêm" (More) popover menu at the bottom-left.
--}}

@php
$currentUser = auth()->user();
$adminRoles = ['admin', 'moderator', 'super_admin'];
$adminPermissions = [
    'view_admin_dashboard',
    'review_verification',
    'manage_reports',
    'manage_media',
];
$isAdmin = $currentUser && (
    $currentUser->roles()->whereIn('name', $adminRoles)->exists()
    || $currentUser->permissions()->whereIn('name', $adminPermissions)->exists()
);

$unreadNotificationsCount = $currentUser ? $currentUser->unreadNotifications()->count() : 0;
$unreadMessagesCount = $currentUser ? \App\Models\ConversationParticipant::where('user_id', $currentUser->id)
    ->where(function ($q) {
        $q->whereNull('last_read_at')
            ->orWhereHas('conversation', function ($q2) {
                $q2->whereColumn('last_message_at', '>', 'conversation_participants.last_read_at');
            });
    })
    ->whereHas('conversation', function ($q3) {
        $q3->whereNotNull('last_message_at');
    })
    ->count() : 0;

$primaryNav = [
    [
        'icon'   => 'home',
        'label'  => 'Trang chủ',
        'href'   => route('dashboard'),
        'active' => request()->routeIs('dashboard'),
        'badge'  => 0,
    ],
    [
        'icon'   => 'users',
        'label'  => 'Khám phá',
        'href'   => route('discovery.index'),
        'active' => request()->routeIs('discovery.*'),
        'badge'  => 0,
    ],
    [
        'icon'   => 'message',
        'label'  => 'Tin nhắn',
        'href'   => route('messages.index'),
        'active' => request()->routeIs('messages.*'),
        'badge'  => $unreadMessagesCount,
    ],
    [
        'icon'   => 'heart',
        'label'  => 'Hoạt động',
        'href'   => route('notifications.index'),
        'active' => request()->routeIs('notifications.*'),
        'badge'  => $unreadNotificationsCount,
    ],
    [
        'icon'   => 'user',
        'label'  => 'Hồ sơ',
        'href'   => route('profile'),
        'active' => request()->routeIs('profile'),
        'badge'  => 0,
    ],
];

$secondaryNav = [
    [
        'icon'   => 'community',
        'label'  => 'Cộng đồng',
        'href'   => '#',
        'active' => false,
    ],
    [
        'icon'   => 'graduation-cap',
        'label'  => 'Mentor',
        'href'   => '#',
        'active' => false,
    ],
    [
        'icon'   => 'bookmark',
        'label'  => 'Đã lưu',
        'href'   => route('posts.saved'),
        'active' => request()->routeIs('posts.saved'),
    ],
];

$canAccessAdminSidebar = $currentUser && (
    $currentUser->can('view_admin_dashboard')
    || $currentUser->can('review_verification')
    || $currentUser->can('manage_users')
    || $currentUser->can('manage_reports')
    || $currentUser->can('manage_mentor_access')
    || $currentUser->can('manage_communities')
    || $currentUser->can('manage', \App\Models\Announcement::class)
    || $currentUser->can('manage_permissions')
    || $currentUser->can('view_audit_log')
    || $currentUser->can('view_audit_logs')
    || $currentUser->can('manage_media')
);

$adminNavItems = [
    [
        'icon' => 'shield',
        'label' => 'Dashboard',
        'href' => route('admin.dashboard'),
        'active' => request()->routeIs('admin.dashboard'),
        'placeholder' => false,
    ],
    [
        'icon' => 'shield-check',
        'label' => 'Verification',
        'href' => route('admin.verifications.queue'),
        'active' => request()->routeIs('admin.verifications.*'),
        'placeholder' => false,
    ],
    [
        'icon' => 'users',
        'label' => 'Users',
        'href' => route('admin.users.index'),
        'active' => request()->routeIs('admin.users.*'),
        'placeholder' => false,
    ],
    [
        'icon' => 'flag',
        'label' => 'Moderation',
        'href' => route('admin.moderation.index'),
        'active' => request()->routeIs('admin.moderation.*'),
        'placeholder' => false,
    ],
    [
        'icon' => 'alert-triangle',
        'label' => 'Reports',
        'href' => route('admin.reports.index'),
        'active' => request()->routeIs('admin.reports.*'),
        'placeholder' => false,
    ],
    [
        'icon' => 'graduation-cap',
        'label' => 'Mentor Access',
        'href' => route('admin.mentors.index'),
        'active' => request()->routeIs('admin.mentors.*'),
        'placeholder' => false,
    ],
    [
        'icon' => 'building-2',
        'label' => 'Communities',
        'href' => route('admin.communities.index'),
        'active' => request()->routeIs('admin.communities.*'),
        'placeholder' => false,
    ],
    [
        'icon' => 'megaphone',
        'label' => 'Announcements',
        'href' => route('admin.announcements.index'),
        'active' => request()->routeIs('admin.announcements.*'),
        'placeholder' => false,
    ],
    [
        'icon' => 'key-round',
        'label' => 'Roles & Permissions',
        'href' => route('admin.permissions.index'),
        'active' => request()->routeIs('admin.permissions.*'),
        'placeholder' => false,
    ],
    [
        'icon' => 'history',
        'label' => 'Audit Logs',
        'href' => route('admin.audit-logs.index'),
        'active' => request()->routeIs('admin.audit-logs.*'),
        'placeholder' => false,
    ],
    [
        'icon' => 'bell',
        'label' => 'Notifications',
        'href' => route('admin.notifications.index'),
        'active' => request()->routeIs('admin.notifications.*'),
        'placeholder' => false,
    ],
    [
        'icon' => 'bar-chart-3',
        'label' => 'Analytics',
        'href' => route('admin.analytics.index'),
        'active' => request()->routeIs('admin.analytics.*'),
        'placeholder' => false,
    ],
    [
        'icon' => 'image',
        'label' => 'Media',
        'href' => route('admin.media.index'),
        'active' => request()->routeIs('admin.media.*'),
        'placeholder' => false,
    ],
    [
        'icon' => 'settings-2',
        'label' => 'System Settings',
        'href' => route('admin.system-settings.index'),
        'active' => request()->routeIs('admin.system-settings.*'),
        'placeholder' => false,
    ],
];
@endphp

@if(isset($shell) && $shell === 'admin')
<nav
    class="ue-shell__sidebar hidden lg:flex flex-col py-5 px-5 justify-between h-100dvh sticky top-0 border-r border-ue-border/80"
    aria-label="Điều hướng quản trị"
    role="navigation"
    x-data="{ moreOpen: false }"
>
    <div class="flex flex-col gap-6 flex-1 min-h-0 overflow-y-auto pr-1">
        {{-- Logo --}}
        <div class="px-1">
            <a href="{{ route('dashboard') }}" class="inline-flex items-center ue-focus-ring rounded-lg" aria-label="UEConnect - Trang chủ">
                <x-brand.logo variant="horizontal" size="lg" class="h-9 w-auto" />
            </a>
        </div>

        {{-- Back to App Link --}}
        <div class="px-1 flex flex-col gap-1">
            <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-ue-brand-active hover:underline">
                <x-ui.icon name="arrow-left" size="xs" />
                Quay lại ứng dụng
            </a>
            <h2 class="text-sm font-bold text-ue-text mt-2 leading-none">Quản trị UEConnect</h2>
            <p class="text-[10px] text-ue-text-muted mt-1 leading-normal">Vận hành, kiểm duyệt và bảo mật hệ thống</p>
        </div>

        {{-- Grouped Admin Navigation --}}
        <div class="flex flex-col gap-5 pt-2">
            @foreach(\App\Support\Navigation\AdminNavigation::getVisibleGroups() as $groupKey => $group)
                <div class="flex flex-col gap-1">
                    <p class="px-3 text-[10px] font-bold uppercase tracking-wider text-ue-text-muted/60">
                        {{ $group['vn_label'] }}
                    </p>
                    <ul class="flex flex-col gap-0.5" role="list">
                        @foreach($group['items'] as $item)
                            @php
                                $routeParts = explode('.', $item['route']);
                                if (count($routeParts) >= 2) {
                                    $baseRouteName = $routeParts[0] . '.' . $routeParts[1];
                                    $active = request()->routeIs($item['route']) || request()->routeIs($baseRouteName . '.*');
                                } else {
                                    $active = request()->routeIs($item['route']);
                                }
                            @endphp
                            <li role="listitem">
                                <a
                                    href="{{ route($item['route']) }}"
                                    class="ue-nav-link {{ $active ? 'active' : '' }}"
                                    @if($active) aria-current="page" @endif
                                >
                                    <x-ui.icon :name="$item['icon']" size="md" class="flex-shrink-0" />
                                    <span>{{ $item['label'] }}</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Bottom Account trigger --}}
    <div class="relative mt-auto pt-4 border-t border-ue-border/60">
        <button
            type="button"
            @click="moreOpen = !moreOpen"
            @click.away="moreOpen = false"
            class="ue-nav-link w-full flex items-center justify-between"
            :class="moreOpen ? 'bg-ue-brand-soft text-ue-brand-active' : ''"
            aria-haspopup="true"
            :aria-expanded="moreOpen"
            aria-label="Xem thêm menu"
        >
            <div class="flex items-center gap-3">
                <x-ui.avatar size="sm" />
                <span class="truncate text-xs font-semibold">{{ $currentUser?->name }}</span>
            </div>
            <x-ui.icon name="chevron-up" size="xs" class="text-ue-text-muted/60 transition-transform duration-150" x-bind:class="moreOpen ? 'rotate-180' : ''" />
        </button>

        {{-- More popover menu --}}
        <div
            x-show="moreOpen"
            x-transition:enter="transition ease-out duration-100"
            x-transition:enter-start="transform opacity-0 scale-95 translate-y-2"
            x-transition:enter-end="transform opacity-100 scale-100 translate-y-0"
            x-transition:leave="transition ease-in duration-75"
            x-transition:leave-start="transform opacity-100 scale-100 translate-y-0"
            x-transition:leave-end="transform opacity-0 scale-95 translate-y-2"
            class="absolute left-0 bottom-full mb-2 w-full bg-white rounded-2xl shadow-lg ring-1 ring-black/5 py-2 z-dropdown"
            style="display: none;"
            @keydown.escape.window="moreOpen = false"
        >
            <a href="{{ route('settings') }}" class="flex items-center gap-3 px-4 py-2 text-xs font-semibold text-ue-text-secondary hover:bg-ue-surface-hover hover:text-ue-brand-active transition-colors">
                <x-ui.icon name="settings" size="sm" class="text-ue-text-muted" />
                <span>Cài đặt cá nhân</span>
            </a>
            <form method="POST" action="{{ route('logout') }}" class="w-full">
                @csrf
                <button
                    type="submit"
                    class="w-full text-left flex items-center gap-3 px-4 py-2 text-xs font-semibold text-red-600 hover:bg-red-50/60 transition-colors"
                >
                    <x-ui.icon name="log-out" size="sm" class="text-red-500" />
                    <span>Đăng xuất</span>
                </button>
            </form>
        </div>
    </div>
</nav>
@else
<nav
    class="ue-shell__sidebar hidden lg:flex flex-col py-5 px-5 justify-between h-100dvh sticky top-0"
    aria-label="Điều hướng chính"
    role="navigation"
    x-data="{ moreOpen: false }"
>
    <div class="flex flex-col gap-7 flex-1 min-h-0 overflow-y-auto pr-1">
        {{-- Logo --}}
        <div class="px-1">
            <a href="{{ route('dashboard') }}" class="inline-flex items-center ue-focus-ring rounded-lg" aria-label="UEConnect - Trang chủ">
                <x-brand.logo variant="horizontal" size="lg" class="h-9 w-auto" />
            </a>
        </div>

        {{-- Primary Navigation --}}
        <div class="flex flex-col gap-1.5">
            <ul class="flex flex-col gap-1" role="list">
                @foreach($primaryNav as $item)
                    <li role="listitem">
                        <a
                            href="{{ $item['href'] }}"
                            class="ue-nav-link {{ $item['active'] ? 'active' : '' }}"
                            @if($item['active']) aria-current="page" @endif
                        >
                            <x-ui.icon :name="$item['icon']" size="md" aria-hidden="true" class="flex-shrink-0" />
                            <span>{{ $item['label'] }}</span>
                            @if (!empty($item['badge']) && $item['badge'] > 0)
                                <span class="ml-auto px-2 py-0.5 rounded-full bg-ue-brand text-white text-[10px] font-bold">
                                    {{ $item['badge'] }}
                                </span>
                            @endif
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>

        {{-- Secondary Navigation Divider & List --}}
        <div class="pt-1 flex flex-col gap-1.5">
            <p class="px-3 text-[10px] font-bold uppercase tracking-wider text-ue-text-muted/70">
                Mở rộng
            </p>
            <ul class="flex flex-col gap-1" role="list">
                @foreach($secondaryNav as $item)
                    <li role="listitem">
                        <a
                            href="{{ $item['href'] }}"
                            class="ue-nav-link {{ $item['active'] ? 'active' : '' }}"
                            @if($item['active']) aria-current="page" @endif
                        >
                            <x-ui.icon :name="$item['icon']" size="md" aria-hidden="true" class="flex-shrink-0" />
                            <span>{{ $item['label'] }}</span>
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>


    </div>

    {{-- Bottom More Trigger & Menu --}}
    <div class="relative mt-auto pt-4">
        <button
            type="button"
            @click="moreOpen = !moreOpen"
            @click.away="moreOpen = false"
            class="ue-nav-link w-full flex items-center justify-between"
            :class="moreOpen ? 'bg-ue-brand-soft text-ue-brand-active' : ''"
            aria-haspopup="true"
            :aria-expanded="moreOpen"
            aria-label="Xem thêm menu"
        >
            <div class="flex items-center gap-3">
                <x-ui.icon name="menu" size="md" class="flex-shrink-0" />
                <span>Xem thêm</span>
            </div>
            <x-ui.icon name="chevron-up" size="xs" class="text-ue-text-muted/60 transition-transform duration-150" x-bind:class="moreOpen ? 'rotate-180' : ''" />
        </button>

        {{-- More popover menu --}}
        <div
            x-show="moreOpen"
            x-transition:enter="transition ease-out duration-100"
            x-transition:enter-start="transform opacity-0 scale-95 translate-y-2"
            x-transition:enter-end="transform opacity-100 scale-100 translate-y-0"
            x-transition:leave="transition ease-in duration-75"
            x-transition:leave-start="transform opacity-100 scale-100 translate-y-0"
            x-transition:leave-end="transform opacity-0 scale-95 translate-y-2"
            class="absolute left-0 bottom-full mb-2 w-64 bg-white rounded-2xl shadow-lg ring-1 ring-black/5 py-2 z-dropdown"
            style="display: none;"
            @keydown.escape.window="moreOpen = false"
        >
            {{-- User info summary --}}
            @if ($currentUser)
                <div class="px-4 py-2 border-b border-ue-border mb-1">
                    <p class="text-ue-text font-bold truncate text-xs">{{ $currentUser->name }}</p>
                    <p class="text-[10px] text-ue-text-muted truncate mt-0.5">{{ $currentUser->email }}</p>
                </div>
            @endif

            {{-- General options --}}
            <button type="button" class="w-full text-left flex items-center gap-3 px-4 py-2 text-xs font-semibold text-ue-text-secondary hover:bg-ue-surface-hover hover:text-ue-brand-active transition-colors">
                <x-ui.icon name="eye" size="sm" class="text-ue-text-muted" />
                <span>Giao diện</span>
            </button>

            <a href="{{ route('settings') }}" class="flex items-center gap-3 px-4 py-2 text-xs font-semibold text-ue-text-secondary hover:bg-ue-surface-hover hover:text-ue-brand-active transition-colors">
                <x-ui.icon name="settings" size="sm" class="text-ue-text-muted" />
                <span>Cài đặt</span>
            </a>

            <a href="{{ route('posts.saved') }}" class="flex items-center gap-3 px-4 py-2 text-xs font-semibold text-ue-text-secondary hover:bg-ue-surface-hover hover:text-ue-brand-active transition-colors">
                <x-ui.icon name="bookmark" size="sm" class="text-ue-text-muted" />
                <span>Bài viết đã lưu</span>
            </a>

            <a href="{{ route('settings', ['section' => 'support']) }}" class="flex items-center gap-3 px-4 py-2 text-xs font-semibold text-ue-text-secondary hover:bg-ue-surface-hover hover:text-ue-brand-active transition-colors">
                <x-ui.icon name="help-circle" size="sm" class="text-ue-text-muted" />
                <span>Trung tâm hỗ trợ</span>
            </a>

            <a href="{{ route('settings', ['section' => 'support']) }}" class="flex items-center gap-3 px-4 py-2 text-xs font-semibold text-ue-text-secondary hover:bg-ue-surface-hover hover:text-ue-brand-active transition-colors">
                <x-ui.icon name="alert-triangle" size="sm" class="text-ue-text-muted" />
                <span>Báo cáo sự cố</span>
            </a>

            {{-- Admin items --}}
            @if ($isAdmin)
                <div class="h-2"></div>
                <p class="px-4 py-1 text-[9px] font-bold text-ue-text-muted uppercase tracking-wider">Quản trị</p>
                
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-4 py-2 text-xs font-semibold text-ue-text-secondary hover:bg-ue-surface-hover hover:text-ue-brand-active transition-colors">
                    <x-ui.icon name="shield" size="sm" class="text-ue-text-muted" />
                    <span>Tổng quan quản trị</span>
                </a>

                @can('review_verification')
                    <a href="{{ route('admin.verifications.queue') }}" class="flex items-center gap-3 px-4 py-2 text-xs font-semibold text-ue-text-secondary hover:bg-ue-surface-hover hover:text-ue-brand-active transition-colors">
                        <x-ui.icon name="shield-check" size="sm" class="text-ue-text-muted" />
                        <span>Duyệt xác thực</span>
                    </a>
                @endcan

                @can('manage_reports')
                    <a href="{{ route('admin.reports.index') }}" class="flex items-center gap-3 px-4 py-2 text-xs font-semibold text-ue-text-secondary hover:bg-ue-surface-hover hover:text-ue-brand-active transition-colors">
                        <x-ui.icon name="flag" size="sm" class="text-ue-text-muted" />
                        <span>Báo cáo vi phạm</span>
                    </a>
                @endcan
            @endif

            {{-- Logout --}}
            <div class="h-2"></div>
            <form method="POST" action="{{ route('logout') }}" class="w-full">
                @csrf
                <button
                    type="submit"
                    class="w-full text-left flex items-center gap-3 px-4 py-2 text-xs font-semibold text-red-600 hover:bg-red-50/60 transition-colors"
                >
                    <x-ui.icon name="log-out" size="sm" class="text-red-500" />
                    <span>Đăng xuất</span>
                </button>
            </form>
        </div>
    </div>
</nav>
@endif
