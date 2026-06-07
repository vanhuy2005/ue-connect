{{--
    App Sidebar Partial (v2)
    Source: docs/04-design/18-responsive-rules.md, 19-design-token-documentation.md
    
    Threads-inspired navigation shell with primary links, secondary list,
    and an accessible "Xem thêm" (More) popover menu at the bottom-left.
--}}

@php
$currentUser = auth()->user();
$metrics = app(\App\Support\Navigation\UserNavigationMetrics::class)->forUser($currentUser);
$unreadNotificationsCount = $metrics['unread_notifications'];
$unreadMessagesCount = $metrics['unread_messages'];
$canReviewVerification = $currentUser?->can('review_verification') ?? false;
$canManageReports = $currentUser?->can('manage_reports') ?? false;
$isAdmin = $currentUser?->hasRole('admin') || ($currentUser?->canAny([
    'view_admin_dashboard',
    'review_verification',
    'manage_reports',
    'manage_media',
]) ?? false);

$primaryNav = [
    [
        'icon' => 'home',
        'label' => 'Trang chủ',
        'href' => route('dashboard'),
        'active' => request()->routeIs('dashboard'),
        'badge' => 0,
    ],
    [
        'icon' => 'users',
        'label' => 'Khám phá',
        'href' => route('discovery.index'),
        'active' => request()->routeIs('discovery.*'),
        'badge' => 0,
    ],
    [
        'icon' => 'message',
        'label' => 'Tin nhắn',
        'href' => route('messages.index'),
        'active' => request()->routeIs('messages.*'),
        'badge' => $unreadMessagesCount,
    ],
    [
        'icon' => 'heart',
        'label' => 'Hoạt động',
        'href' => route('notifications.index'),
        'active' => request()->routeIs('notifications.*'),
        'badge' => $unreadNotificationsCount,
    ],
    [
        'icon' => 'user',
        'label' => 'Hồ sơ',
        'href' => route('profile'),
        'active' => request()->routeIs('profile'),
        'badge' => 0,
    ],
];

$secondaryNav = [
    [
        'icon' => 'community',
        'label' => 'Cộng đồng',
        'href' => route('community.index'),
        'active' => request()->routeIs('community.*'),
    ],
    [
        'icon' => 'graduation-cap',
        'label' => 'Mentor',
        'href' => route('mentor.discovery'),
        'active' => request()->routeIs('mentor.*'),
    ],
    [
        'icon' => 'bookmark',
        'label' => 'Đã lưu',
        'href' => route('posts.saved'),
        'active' => request()->routeIs('posts.saved'),
    ],
];
@endphp

@if(isset($shell) && $shell === 'admin')
<nav
    class="ue-shell__sidebar hidden lg:flex flex-col py-4 px-4 justify-between h-100dvh sticky top-0 border-r border-ue-border/80"
    aria-label="Điều hướng quản trị"
    role="navigation"
    x-data="{ moreOpen: false }"
>
    <div class="flex flex-col gap-5 flex-1 min-h-0 overflow-y-auto pr-1">
        {{-- Logo --}}
        <div class="px-1">
            <a href="{{ route('dashboard') }}" wire:navigate.hover class="inline-flex items-center ue-focus-ring rounded-lg" aria-label="UEConnect - Trang chủ">
                <x-brand.logo variant="horizontal" size="lg" class="h-8 w-auto" />
            </a>
        </div>

        {{-- Back to App Link --}}
        <div class="px-1 flex flex-col gap-1">
            <a href="{{ route('dashboard') }}" wire:navigate.hover class="inline-flex items-center gap-1.5 text-xs font-semibold text-ue-brand-active hover:underline">
                <x-ui.icon name="arrow-left" size="xs" />
                Quay lại ứng dụng
            </a>
            <h2 class="text-sm font-bold text-ue-text mt-1.5 leading-tight">Quản trị UEConnect</h2>
            <p class="text-[10px] text-ue-text-muted mt-1 leading-normal">Vận hành, kiểm duyệt và bảo mật hệ thống</p>
        </div>

        {{-- Category Admin Navigation --}}
        <div class="flex flex-col gap-1 pt-1">
            @foreach(\App\Support\Navigation\AdminNavigation::getVisibleGroups() as $groupKey => $group)
                @php
                    $active = request()->routeIs('admin.console')
                        && (request()->route('group') ?? array_key_first(\App\Support\Navigation\AdminNavigation::getVisibleGroups())) === $groupKey;

                    if (! $active) {
                        foreach ($group['items'] as $item) {
                            $routeParts = explode('.', $item['route']);
                            $baseRouteName = count($routeParts) >= 2
                                ? $routeParts[0] . '.' . $routeParts[1]
                                : $item['route'];

                            if (request()->routeIs($item['route']) || request()->routeIs($baseRouteName . '.*')) {
                                $active = true;
                                break;
                            }
                        }
                    }
                @endphp
                <a
                    href="{{ route('admin.console', ['group' => $groupKey]) }}"
                    wire:navigate.hover
                    class="ue-admin-nav-link {{ $active ? 'active' : '' }}"
                    @if($active) aria-current="page" @endif
                >
                    <x-ui.icon :name="$group['icon']" size="sm" class="flex-shrink-0" />
                    <span class="min-w-0 flex-1 truncate">{{ $group['vn_label'] }}</span>
                    <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-bold text-slate-500">{{ count($group['items']) }}</span>
                </a>
            @endforeach
        </div>
    </div>

    {{-- Bottom Account trigger --}}
    <div class="relative mt-auto pt-3 border-t border-ue-border/60">
        <button
            type="button"
            @click="moreOpen = !moreOpen"
            @click.away="moreOpen = false"
            class="ue-admin-nav-link w-full flex items-center justify-between"
            :class="moreOpen ? 'bg-ue-brand-soft text-ue-brand-active' : ''"
            aria-haspopup="true"
            :aria-expanded="moreOpen"
            aria-label="Xem thêm menu"
        >
            <div class="flex items-center gap-2.5">
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
            <a href="{{ route('settings') }}" wire:navigate.hover class="flex items-center gap-3 px-4 py-2 text-xs font-semibold text-ue-text-secondary hover:bg-ue-surface-hover hover:text-ue-brand-active transition-colors">
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
    class="ue-shell__sidebar hidden lg:flex flex-col py-5 px-3 justify-between h-100dvh sticky top-0 border-r border-ue-border/80"
    :class="collapsed ? 'ue-shell__sidebar--collapsed' : 'ue-shell__sidebar--expanded'"
    aria-label="Điều hướng chính"
    role="navigation"
    x-data="{
        moreOpen: false,
        collapsed: true
    }"
    @mouseenter="collapsed = false"
    @mouseleave="collapsed = true"
>
    <div class="flex flex-col gap-7 flex-1 min-h-0 overflow-y-auto pr-1">
        {{-- Logo --}}
        <div class="pl-1.5">
            <a href="{{ route('dashboard') }}" wire:navigate.hover class="inline-flex items-center gap-2.5 ue-focus-ring rounded-lg" aria-label="UEConnect - Trang chủ">
                <x-brand.logo variant="mark" size="lg" class="h-9 w-9 flex-shrink-0" />
                <span x-show="!collapsed" x-transition:enter="transition ease-out duration-200 delay-75" x-transition:enter-start="opacity-0 translate-x-[-8px]" x-transition:enter-end="opacity-100 translate-x-0" x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="font-bold text-lg text-ue-brand tracking-tight whitespace-nowrap">UEConnect</span>
            </a>
        </div>

        {{-- Primary Navigation --}}
        <div class="flex flex-col gap-1.5">
            <ul class="flex flex-col gap-1" role="list">
                @foreach($primaryNav as $item)
                    <li role="listitem">
                        <a
                            href="{{ $item['href'] }}"
                            @if($item['href'] !== '#') wire:navigate.hover @endif
                            class="ue-nav-link {{ $item['active'] ? 'active' : '' }}"
                            @if($item['active']) aria-current="page" @endif
                            :title="collapsed ? '{{ $item['label'] }}' : ''"
                        >
                            <div class="relative flex items-center justify-center">
                                <x-ui.icon :name="$item['icon']" size="md" aria-hidden="true" class="flex-shrink-0" />
                                @if (!empty($item['badge']) && $item['badge'] > 0)
                                    <span x-show="collapsed" class="absolute -top-1 -right-1 flex h-2 w-2 rounded-full bg-red-500 ring-2 ring-white"></span>
                                @endif
                            </div>
                            <span x-show="!collapsed" x-transition:enter="transition ease-out duration-200 delay-75" x-transition:enter-start="opacity-0 translate-x-[-8px]" x-transition:enter-end="opacity-100 translate-x-0" x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="whitespace-nowrap">{{ $item['label'] }}</span>
                            @if (!empty($item['badge']) && $item['badge'] > 0)
                                <span x-show="!collapsed" x-transition:enter="transition ease-out duration-200 delay-75" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="ml-auto px-2 py-0.5 rounded-full bg-ue-brand text-white text-[10px] font-bold">
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
            <p
                :class="collapsed ? 'opacity-0 pointer-events-none' : 'opacity-100'"
                class="px-3 text-[10px] font-bold uppercase tracking-wider text-ue-text-muted/70 whitespace-nowrap transition-opacity duration-200 delay-75"
            >
                Mở rộng
            </p>
            <ul class="flex flex-col gap-1" role="list">
                @foreach($secondaryNav as $item)
                    <li role="listitem">
                        <a
                            href="{{ $item['href'] }}"
                            @if($item['href'] !== '#') wire:navigate.hover @endif
                            class="ue-nav-link {{ $item['active'] ? 'active' : '' }}"
                            @if($item['active']) aria-current="page" @endif
                            :title="collapsed ? '{{ $item['label'] }}' : ''"
                        >
                            <div class="relative flex items-center justify-center">
                                <x-ui.icon :name="$item['icon']" size="md" aria-hidden="true" class="flex-shrink-0" />
                            </div>
                            <span x-show="!collapsed" x-transition:enter="transition ease-out duration-200 delay-75" x-transition:enter-start="opacity-0 translate-x-[-8px]" x-transition:enter-end="opacity-100 translate-x-0" x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="whitespace-nowrap">{{ $item['label'] }}</span>
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>

    {{-- Bottom Toggle & More Trigger & Menu --}}
    <div class="relative mt-auto pt-4 flex flex-col gap-1.5">
        {{-- More Trigger --}}
        <button
            type="button"
            @click="moreOpen = !moreOpen"
            @click.away="moreOpen = false"
            class="ue-nav-link w-full flex items-center justify-between"
            :class="moreOpen ? 'bg-ue-brand-soft text-ue-brand-active' : ''"
            aria-haspopup="true"
            :aria-expanded="moreOpen"
            aria-label="Xem thêm menu"
            :title="collapsed ? 'Xem thêm' : ''"
        >
            <div class="flex items-center gap-3">
                <x-ui.icon name="menu" size="md" class="flex-shrink-0" />
                <span x-show="!collapsed" x-transition:enter="transition ease-out duration-200 delay-75" x-transition:enter-start="opacity-0 translate-x-[-8px]" x-transition:enter-end="opacity-100 translate-x-0" x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="whitespace-nowrap">Xem thêm</span>
            </div>
            <x-ui.icon name="chevron-up" size="xs" x-show="!collapsed" x-transition:enter="transition ease-out duration-200 delay-75" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="text-ue-text-muted/60 transition-transform duration-150" x-bind:class="moreOpen ? 'rotate-180' : ''" />
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
            class="absolute bottom-full mb-2 w-64 bg-white rounded-2xl shadow-lg ring-1 ring-black/5 py-2 z-dropdown"
            :class="collapsed ? 'left-2' : 'left-0'"
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

            <a href="{{ route('settings') }}" wire:navigate.hover class="flex items-center gap-3 px-4 py-2 text-xs font-semibold text-ue-text-secondary hover:bg-ue-surface-hover hover:text-ue-brand-active transition-colors">
                <x-ui.icon name="settings" size="sm" class="text-ue-text-muted" />
                <span>Cài đặt</span>
            </a>

            <a href="{{ route('posts.saved') }}" wire:navigate.hover class="flex items-center gap-3 px-4 py-2 text-xs font-semibold text-ue-text-secondary hover:bg-ue-surface-hover hover:text-ue-brand-active transition-colors">
                <x-ui.icon name="bookmark" size="sm" class="text-ue-text-muted" />
                <span>Bài viết đã lưu</span>
            </a>

            <a href="{{ route('settings', ['section' => 'support']) }}" wire:navigate.hover class="flex items-center gap-3 px-4 py-2 text-xs font-semibold text-ue-text-secondary hover:bg-ue-surface-hover hover:text-ue-brand-active transition-colors">
                <x-ui.icon name="help-circle" size="sm" class="text-ue-text-muted" />
                <span>Trung tâm hỗ trợ</span>
            </a>

            <a href="{{ route('settings', ['section' => 'support']) }}" wire:navigate.hover class="flex items-center gap-3 px-4 py-2 text-xs font-semibold text-ue-text-secondary hover:bg-ue-surface-hover hover:text-ue-brand-active transition-colors">
                <x-ui.icon name="alert-triangle" size="sm" class="text-ue-text-muted" />
                <span>Báo cáo sự cố</span>
            </a>

            {{-- Admin items --}}
            @if ($isAdmin)
                <div class="h-2"></div>
                <p class="px-4 py-1 text-[9px] font-bold text-ue-text-muted uppercase tracking-wider">Quản trị</p>
                
                <a href="{{ route('admin.dashboard') }}" wire:navigate.hover class="flex items-center gap-3 px-4 py-2 text-xs font-semibold text-ue-text-secondary hover:bg-ue-surface-hover hover:text-ue-brand-active transition-colors">
                    <x-ui.icon name="shield" size="sm" class="text-ue-text-muted" />
                    <span>Tổng quan quản trị</span>
                </a>

                @if($canReviewVerification)
                    <a href="{{ route('admin.verifications.queue') }}" wire:navigate.hover class="flex items-center gap-3 px-4 py-2 text-xs font-semibold text-ue-text-secondary hover:bg-ue-surface-hover hover:text-ue-brand-active transition-colors">
                        <x-ui.icon name="shield-check" size="sm" class="text-ue-text-muted" />
                        <span>Duyệt xác thực</span>
                    </a>
                @endif

                @if($canManageReports)
                    <a href="{{ route('admin.reports.index') }}" wire:navigate.hover class="flex items-center gap-3 px-4 py-2 text-xs font-semibold text-ue-text-secondary hover:bg-ue-surface-hover hover:text-ue-brand-active transition-colors">
                        <x-ui.icon name="flag" size="sm" class="text-ue-text-muted" />
                        <span>Báo cáo vi phạm</span>
                    </a>
                @endif
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
