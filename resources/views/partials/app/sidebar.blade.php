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
        'href'   => '#',
        'active' => false,
        // TODO: route('discovery.index')
    ],
    [
        'icon'   => 'message',
        'label'  => 'Tin nhắn',
        'href'   => '#',
        'active' => false,
        // TODO: route('messages.index')
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
            <x-brand.logo variant="horizontal" size="md" />
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

    {{-- Profile link at bottom --}}
    <div class="border-t border-ue-border pt-3 mt-3">
        {{-- TODO: route('profile.show', auth()->user()) --}}
        <a href="{{ route('profile') }}" class="ue-nav-link" @if(request()->routeIs('profile')) aria-current="page" @endif>
            <x-ui.avatar size="sm" />
            <span>Hồ sơ</span>
        </a>
    </div>
</nav>
