{{--
    App Sidebar Partial (v2)
    Source: docs/04-design/18-responsive-rules.md, 19-design-token-documentation.md
    
    Threads-inspired navigation shell with primary links, secondary list,
    and an accessible "Xem thêm" (More) popover menu at the bottom-left.
--}}

@php
$currentUser = auth()->user();
$isAdmin = $currentUser && ($currentUser->can('review_verification') || $currentUser->can('manage_reports'));

$primaryNav = [
    [
        'icon'   => 'home',
        'label'  => 'Trang chủ',
        'href'   => route('dashboard'),
        'active' => request()->routeIs('dashboard'),
    ],
    [
        'icon'   => 'users',
        'label'  => 'Khám phá',
        'href'   => route('discovery.index'),
        'active' => request()->routeIs('discovery.*'),
    ],
    [
        'icon'   => 'message',
        'label'  => 'Tin nhắn',
        'href'   => route('messages.index'),
        'active' => request()->routeIs('messages.*'),
    ],
    [
        'icon'   => 'bell',
        'label'  => 'Thông báo',
        'href'   => '#',
        'active' => false,
    ],
    [
        'icon'   => 'user',
        'label'  => 'Hồ sơ',
        'href'   => route('profile'),
        'active' => request()->routeIs('profile'),
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
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>

        {{-- Secondary Navigation Divider & List --}}
        <div class="border-t border-ue-border/60 pt-4 flex flex-col gap-1.5">
            <p class="px-3 text-[10px] font-bold uppercase tracking-wider text-ue-text-muted/80">
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
            class="absolute left-0 bottom-full mb-2 w-64 bg-white border border-ue-border rounded-xl shadow-lg py-2 z-dropdown"
            style="display: none;"
            @keydown.escape.window="moreOpen = false"
        >
            {{-- User info summary --}}
            <div class="px-4 py-2 border-b border-ue-border/60">
                <p class="text-ue-text font-bold truncate text-xs">{{ $currentUser->name }}</p>
                <p class="text-[10px] text-ue-text-muted truncate mt-0.5">{{ $currentUser->email }}</p>
            </div>

            {{-- General options --}}
            <button type="button" class="w-full text-left flex items-center gap-3 px-4 py-2 text-xs font-semibold text-ue-text-secondary hover:bg-ue-surface-hover hover:text-ue-brand-active transition-colors">
                <x-ui.icon name="eye" size="sm" class="text-ue-text-muted" />
                <span>Giao diện</span>
            </button>

            <a href="#" class="flex items-center gap-3 px-4 py-2 text-xs font-semibold text-ue-text-secondary hover:bg-ue-surface-hover hover:text-ue-brand-active transition-colors">
                <x-ui.icon name="settings" size="sm" class="text-ue-text-muted" />
                <span>Cài đặt</span>
            </a>

            <a href="{{ route('posts.saved') }}" class="flex items-center gap-3 px-4 py-2 text-xs font-semibold text-ue-text-secondary hover:bg-ue-surface-hover hover:text-ue-brand-active transition-colors">
                <x-ui.icon name="bookmark" size="sm" class="text-ue-text-muted" />
                <span>Bài viết đã lưu</span>
            </a>

            <a href="#" class="flex items-center gap-3 px-4 py-2 text-xs font-semibold text-ue-text-secondary hover:bg-ue-surface-hover hover:text-ue-brand-active transition-colors">
                <x-ui.icon name="help-circle" size="sm" class="text-ue-text-muted" />
                <span>Trung tâm hỗ trợ</span>
            </a>

            <a href="#" class="flex items-center gap-3 px-4 py-2 text-xs font-semibold text-ue-text-secondary hover:bg-ue-surface-hover hover:text-ue-brand-active transition-colors">
                <x-ui.icon name="alert-triangle" size="sm" class="text-ue-text-muted" />
                <span>Báo cáo sự cố</span>
            </a>

            {{-- Admin items --}}
            @if ($isAdmin)
                <div class="border-t border-ue-border/60 my-1.5"></div>
                <p class="px-4 py-1 text-[9px] font-bold text-ue-text-muted uppercase tracking-wider">Quản lý hệ thống</p>
                
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
