{{--
    App Sidebar Partial (Desktop)
    Source: docs/04-design/18-responsive-rules.md, 19-design-token-documentation.md §16

    Desktop sidebar with main navigation.
    Hidden on mobile — use mobile-bottom-nav instead.

    NOTE: All nav links use safe placeholder hrefs for unimplemented routes.
    TODO: Replace href="#" with route() helpers as pages are implemented.

    Active state: uses request()->is() on safe routes only (dashboard is real).
--}}

@php
$navItems = [
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
        'icon'   => 'community',
        'label'  => 'Cộng đồng',
        'href'   => '#',
        'active' => false,
        // TODO: route('communities.index')
    ],
    [
        'icon'   => 'graduation-cap',
        'label'  => 'Mentor',
        'href'   => '#',
        'active' => false,
        // TODO: route('mentor.index')
    ],
    [
        'icon'   => 'bell',
        'label'  => 'Thông báo',
        'href'   => '#',
        'active' => false,
        // TODO: route('notifications.index')
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
    class="ue-shell__sidebar hidden lg:flex flex-col py-4 px-3"
    aria-label="Điều hướng chính"
    role="navigation"
>
    {{-- Logo --}}
    <div class="px-2 mb-6">
        <a href="{{ route('dashboard') }}" class="flex items-center ue-focus-ring rounded-lg" aria-label="UEConnect - Trang chủ">
            <x-brand.logo variant="horizontal" size="sm" />
        </a>
    </div>

    {{-- Navigation items --}}
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
    @if(auth()->user()->can('review_verification') || auth()->user()->can('manage_reports'))
        <div class="mt-6 border-t border-ue-border pt-4 flex flex-col gap-1">
            <p class="px-3 text-2xs font-bold uppercase tracking-wider text-ue-text-muted">
                Quản trị
            </p>

            <a
                href="{{ route('admin.dashboard') }}"
                class="ue-nav-link"
                @if(request()->routeIs('admin.dashboard')) aria-current="page" @endif
            >
                <x-ui.icon name="shield" size="md" />
                <span>Tổng quan quản trị</span>
            </a>

            @can('review_verification')
                <a
                    href="{{ route('admin.verifications.queue') }}"
                    class="ue-nav-link"
                    @if(request()->routeIs('admin.verifications.*')) aria-current="page" @endif
                >
                    <x-ui.icon name="shield-check" size="md" />
                    <span>Duyệt xác thực</span>
                </a>
            @endcan

            @can('manage_reports')
                <a
                    href="{{ route('admin.reports.index') }}"
                    class="ue-nav-link"
                    @if(request()->routeIs('admin.reports.*')) aria-current="page" @endif
                >
                    <x-ui.icon name="flag" size="md" />
                    <span>Báo cáo vi phạm</span>
                </a>
            @endcan
        </div>
    @endif

    {{-- Profile link + Logout at bottom --}}
    <div class="border-t border-ue-border pt-3 mt-3 flex flex-col gap-1">
        <a href="{{ route('profile') }}" class="ue-nav-link" @if(request()->routeIs('profile')) aria-current="page" @endif>
            <x-ui.avatar size="sm" />
            <span>Hồ sơ</span>
        </a>

        {{-- Logout --}}
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button
                type="submit"
                class="ue-nav-link w-full text-left text-ue-text-secondary hover:text-danger"
                aria-label="Đăng xuất"
            >
                <x-ui.icon name="log-out" size="md" aria-hidden="true" class="flex-shrink-0" />
                <span>Đăng xuất</span>
            </button>
        </form>
    </div>
</nav>
