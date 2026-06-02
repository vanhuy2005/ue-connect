{{--
    App Sidebar Partial (v2)
    Source: docs/04-design/18-responsive-rules.md, 19-design-token-documentation.md
    
    Threads-inspired navigation shell with primary links, secondary list,
    and an accessible "Xem thêm" (More) popover menu at the bottom-left.
--}}

@php
$currentUser = auth()->user();
$isAdmin = $currentUser && ($currentUser->can('review_verification') || $currentUser->can('manage_reports'));

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
@endphp

<nav
    class="ue-shell__sidebar hidden lg:flex flex-col py-5 px-4 justify-between h-100dvh sticky top-0"
    aria-label="Điều hướng chính"
    role="navigation"
    x-data="{ moreOpen: false }"
>
    <div class="flex flex-col gap-6 flex-1 min-h-0">
        {{-- Logo --}}
        <div class="px-2">
            <a href="{{ route('dashboard') }}" class="flex items-center ue-focus-ring rounded-lg" aria-label="UEConnect - Trang chủ">
                <x-brand.logo variant="horizontal" size="sm" />
            </a>
        </div>

    {{-- Navigation items --}}
    @php
        $navItems = $navItems ?? array_merge($primaryNav, $secondaryNav);
    @endphp

    <ul class="flex flex-col gap-1 flex-1" role="list">
        @foreach($navItems as $item)
            <li role="listitem">
                <a
                    href="{{ $item['href'] }}"
                    class="ue-nav-link"
                    @if($item['active']) aria-current="page" @endif
                >
                    <x-ui.icon :name="$item['icon']" size="md" aria-hidden="true" class="flex-shrink-0" />
                    <span>{{ $item['label'] }}</span>
                </a>
            </li>
        @endforeach
    </ul>

    {{-- Admin section --}}
    @php
        $canAccessAdminSidebar = auth()->user()?->can('view_admin_dashboard')
            || auth()->user()?->can('review_verification')
            || auth()->user()?->can('manage_users')
            || auth()->user()?->can('manage_reports')
            || auth()->user()?->can('manage_mentor_access')
            || auth()->user()?->can('manage_communities')
            || auth()->user()?->can('manage', \App\Models\Announcement::class)
            || auth()->user()?->can('manage_permissions')
            || auth()->user()?->can('view_audit_log');

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
                'icon' => 'settings-2',
                'label' => 'System Settings',
                'href' => route('admin.system-settings.index'),
                'active' => request()->routeIs('admin.system-settings.*'),
                'placeholder' => false,
            ],
        ];
    @endphp

    @if($canAccessAdminSidebar)
        <div class="mt-6 border-t border-ue-border pt-4 flex flex-col gap-1">
            <p class="px-3 text-2xs font-bold uppercase tracking-wider text-ue-text-muted">
                Quản trị
            </p>

            @foreach($adminNavItems as $item)
                @php
                    // Việt hóa label trên đường đi thay vì thay đổi dữ liệu route
                    $labelVnMap = [
                        'Dashboard' => 'Tổng quan quản trị',
                        'Verification' => 'Duyệt xác thực',
                        'Users' => 'Người dùng',
                        'Moderation' => 'Kiểm duyệt',
                        'Reports' => 'Báo cáo',
                        'Communities' => 'Quản lý cộng đồng',
                        'Mentor Access' => 'Quản lý Mentor',
                        'Announcements' => 'Thông báo',
                        'Roles & Permissions' => 'Vai trò & Quyền',
                        'Audit Logs' => 'Nhật ký thao tác',
                        'Notifications' => 'Thông báo hệ thống',
                        'Analytics' => 'Phân tích',
                        'System Settings' => 'Cài đặt hệ thống',
                    ];

                    $label = $labelVnMap[$item['label']] ?? $item['label'];
                @endphp

                <a
                    href="{{ $item['href'] }}"
                    class="ue-nav-link {{ $item['placeholder'] ? 'pointer-events-none opacity-60' : '' }}"
                    @if($item['active']) aria-current="page" @endif
                    @if($item['placeholder']) aria-disabled="true" tabindex="-1" @endif
                >
                    <x-ui.icon :name="$item['icon']" size="md" />
                    <span>{{ $label }}</span>
                </a>
            @endforeach
        </div>
    @endif

    {{-- Profile link + Logout at bottom --}}
    <div class="border-t border-ue-border pt-3 mt-3 flex flex-col gap-1">
        <a href="{{ route('profile') }}" class="ue-nav-link" @if(request()->routeIs('profile')) aria-current="page" @endif>
            <x-ui.avatar size="sm" />
            <span>Hồ sơ</span>
        </a>

            {{-- Logout --}}
            <div class="border-t border-ue-border/60 my-1.5"></div>
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
