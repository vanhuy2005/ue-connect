{{--
    Mobile Bottom Navigation Partial
    Source: docs/04-design/18-responsive-rules.md, 19-design-token-documentation.md §16

    Fixed bottom navigation bar for mobile.
    Visible on mobile only (lg:hidden).
    5 core items. Touch target >= 44px per token.
    Safe-area inset for PWA installed experience.

    NOTE: Unimplemented routes use href="#" placeholders.
    TODO: Replace href="#" with route() helpers as pages are built.
--}}

@php
$currentUser = auth()->user();
$metrics = app(\App\Support\Navigation\UserNavigationMetrics::class)->forUser($currentUser);
$mobileNavItems = [
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
        'badge' => $metrics['unread_messages'],
    ],
    [
        'icon' => 'heart',
        'label' => 'Hoạt động',
        'href' => route('notifications.index'),
        'active' => request()->routeIs('notifications.*'),
        'badge' => $metrics['unread_notifications'],
    ],
    [
        'icon' => 'user',
        'label' => 'Hồ sơ',
        'href' => route('profile'),
        'active' => request()->routeIs('profile'),
        'badge' => 0,
    ],
];
@endphp

<nav
    class="ue-shell__bottom-nav lg:hidden flex items-stretch"
    aria-label="Điều hướng di động"
    role="navigation"
>
    @foreach($mobileNavItems as $item)
        <a
            href="{{ $item['href'] }}"
            wire:navigate
            class="flex-1 flex flex-col items-center justify-center gap-1 min-h-touch px-1 pt-2 pb-1
                   text-ue-text-muted text-2xs font-semibold leading-none
                   transition-colors duration-sm ease-out
                   hover:text-ue-text focus-visible:outline-none focus-visible:text-ue-brand"
            @if($item['active'])
                aria-current="page"
                style="color: var(--ue-brand);"
            @endif
        >
            <div class="relative">
                <x-ui.icon
                    :name="$item['icon']"
                    size="md"
                    aria-hidden="true"
                    class="{{ $item['active'] ? 'text-ue-brand' : 'text-current' }}"
                />
                <span class="absolute -top-1.5 -right-2 px-1 py-0.5 rounded-full bg-red-500 text-white text-[8px] font-bold min-w-[14px] text-center leading-none {{ !empty($item['badge']) && $item['badge'] > 0 ? '' : 'hidden' }} js-badge-count-{{ $item['icon'] }}">
                    {{ $item['badge'] }}
                </span>
            </div>
            <span class="{{ $item['active'] ? 'text-ue-brand' : '' }}">
                {{ $item['label'] }}
            </span>
        </a>
    @endforeach
</nav>
