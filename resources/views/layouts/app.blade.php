<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="theme-color" content="#124874">

        <title>{{ config('app.name', 'UEConnect') }}{{ isset($title) ? ' — ' . $title : '' }}</title>

        <meta name="description" content="{{ $description ?? 'UEConnect — Kết nối cộng đồng sinh viên HCMUE.' }}">

        {{-- Realtime Meta Config --}}
        <meta name="reverb-app-key" content="{{ config('reverb.apps.apps.0.key', env('REVERB_APP_KEY')) }}">
        <meta name="reverb-host" content="{{ config('reverb.servers.reverb.host', env('REVERB_HOST')) }}">
        <meta name="reverb-port" content="{{ config('reverb.servers.reverb.port', env('REVERB_PORT')) }}">
        <meta name="reverb-scheme" content="{{ config('reverb.servers.reverb.scheme', env('REVERB_SCHEME')) }}">

        {{-- Fonts: Be Vietnam Pro — weights 400/500/600/700 only --}}
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link
            href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600;700&display=swap"
            rel="stylesheet"
        >

        {{-- Favicon --}}
        <link rel="icon" type="image/png" href="{{ asset('images/brand/ueconnect-mark-nobg.png') }}">

        {{-- Vite Assets --}}
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @auth
            @vite('resources/js/realtime.js')
        @endauth

        {{-- Additional head slots --}}
        @stack('head')
    </head>

    <body class="font-sans antialiased h-full">
        <x-ui.page-transition />

        {{-- Default shell when not provided by caller --}}
        @php $shell = $shell ?? 'guest'; @endphp

        {{-- Skip to main content (accessibility) --}}
        <a href="#main-content" class="skip-link">Bỏ qua và đến nội dung chính</a>

        {{-- App shell --}}
        <div class="ue-shell ue-shell--{{ $shell }}">
            {{-- Desktop sidebar (only for authenticated layouts) --}}
            @if(in_array($shell, ['social', 'admin', 'conversation']))
                @include('partials.app.sidebar')
            @endif

            {{-- Main column --}}
            <div class="ue-shell__main flex flex-col min-h-full">

                {{-- Topbar (Only for custom shells, NOT social, admin, conversation, guest, or auth) --}}
                @if(!in_array($shell, ['social', 'admin', 'conversation', 'guest', 'auth']))
                    @include('partials.app.topbar')
                @endif

                {{-- Threads-style mobile topbar for social shell (desktop uses sidebar) --}}
                @if($shell === 'social')
                    @include('partials.app.social-mobile-topbar')
                @endif

                {{-- Account status banner for restrictions --}}
                @if(in_array($shell, ['social', 'admin', 'conversation']))
                    <x-ui.account-status-banner />
                @endif

                {{-- Page content --}}
                <main
                    id="main-content"
                    class="flex-1 {{ in_array($shell, ['social', 'conversation']) ? 'pb-16 lg:pb-0' : '' }}"
                    tabindex="-1"
                >
                    @if($shell === 'admin')
                        <div class="flex-1 flex flex-col min-w-0" x-data="{ adminDrawerOpen: false }">
                            {{-- Top Admin Bar - Mobile only --}}
                            <div class="lg:hidden bg-ue-surface border-b border-ue-border px-4 py-3 flex items-center justify-between sticky top-0 z-sticky">
                                <div class="flex items-center gap-2">
                                    <button type="button" @click="adminDrawerOpen = true" class="p-1 rounded-lg text-ue-text hover:bg-ue-surface-hover" aria-label="Mở menu quản trị">
                                        <x-ui.icon name="menu" size="md" />
                                    </button>
                                    @php
                                        $currentTitle = 'Tổng quan';
                                        foreach(\App\Support\Navigation\AdminNavigation::getGroups() as $groupKey => $g) {
                                            if (request()->routeIs('admin.console') && (request()->route('group') ?? 'overview') === $groupKey) {
                                                $currentTitle = $g['vn_label'];
                                            }

                                            foreach($g['items'] as $i) {
                                                $routeParts = explode('.', $i['route']);
                                                $baseRouteName = count($routeParts) >= 2
                                                    ? $routeParts[0] . '.' . $routeParts[1]
                                                    : $i['route'];

                                                if(request()->routeIs($i['route']) || request()->routeIs($baseRouteName . '.*')) {
                                                    $currentTitle = $g['vn_label'];
                                                }
                                            }
                                        }
                                    @endphp
                                    <span class="font-bold text-ue-text text-sm">Quản trị: {{ $currentTitle }}</span>
                                </div>
                                <a href="{{ route('dashboard') }}" class="text-xs font-semibold text-ue-brand-active flex items-center gap-1">
                                    <x-ui.icon name="arrow-left" size="xs" />
                                    Thoát
                                </a>
                            </div>

                            {{-- Mobile Drawer Menu --}}
                            <div
                                x-show="adminDrawerOpen"
                                class="relative z-[800] lg:hidden"
                                role="dialog"
                                aria-modal="true"
                                style="display: none;"
                            >
                                <div
                                    x-show="adminDrawerOpen"
                                    x-transition:enter="transition-opacity ease-linear duration-300"
                                    x-transition:enter-start="opacity-0"
                                    x-transition:enter-end="opacity-100"
                                    x-transition:leave="transition-opacity ease-linear duration-300"
                                    x-transition:leave-start="opacity-100"
                                    x-transition:leave-end="opacity-0"
                                    class="fixed inset-0 bg-black/40 backdrop-blur-sm"
                                    @click="adminDrawerOpen = false"
                                ></div>

                                <div class="fixed inset-0 flex">
                                    <div
                                        x-show="adminDrawerOpen"
                                        x-transition:enter="transition ease-in-out duration-300 transform"
                                        x-transition:enter-start="-translate-x-full"
                                        x-transition:enter-end="translate-x-0"
                                        x-transition:leave="transition ease-in-out duration-300 transform"
                                        x-transition:leave-start="translate-x-0"
                                        x-transition:leave-end="-translate-x-full"
                                        class="relative flex w-full max-w-xs flex-col bg-ue-surface p-6 shadow-xl focus:outline-none"
                                        @keydown.escape.window="adminDrawerOpen = false"
                                    >
                                        <div class="flex items-center justify-between mb-6">
                                            <h2 class="text-lg font-bold text-ue-text">Quản trị UEConnect</h2>
                                            <button type="button" @click="adminDrawerOpen = false" class="p-1 rounded-lg text-ue-text hover:bg-ue-surface-hover" aria-label="Đóng menu">
                                                <x-ui.icon name="x" size="md" />
                                            </button>
                                        </div>

                                        <div class="flex-1 overflow-y-auto pr-1">
                                            <nav class="flex flex-col gap-1 text-sm">
                                                @foreach(\App\Support\Navigation\AdminNavigation::getVisibleGroups() as $groupKey => $group)
                                                    @php
                                                        $active = request()->routeIs('admin.console') && (request()->route('group') ?? 'overview') === $groupKey;

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
                                                        @click="adminDrawerOpen = false"
                                                        class="ue-admin-nav-link {{ $active ? 'active' : '' }}"
                                                        @if($active) aria-current="page" @endif
                                                    >
                                                        <x-ui.icon :name="$group['icon']" size="sm" class="flex-shrink-0" />
                                                        <span class="min-w-0 flex-1 truncate">{{ $group['vn_label'] }}</span>
                                                        <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-bold text-slate-500">{{ count($group['items']) }}</span>
                                                    </a>
                                                @endforeach
                                            </nav>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Content Area --}}
                            <div class="flex-1 min-w-0">
                                @if(isset($slot))
                                    {!! $slot !!}
                                @else
                                    {!! $__env->yieldContent('content') !!}
                                @endif
                            </div>
                        </div>
                    @else
                        @if(isset($slot))
                            {!! $slot !!}
                        @else
                            {!! $__env->yieldContent('content') !!}
                        @endif
                    @endif
                </main>

            </div>
        </div>

        {{-- Mobile bottom nav --}}
        @if(in_array($shell, ['social', 'conversation']))
            @include('partials.app.mobile-bottom-nav')
        @endif

        {{-- Additional script slots --}}
        @stack('scripts')

        @auth
            <script>
                document.addEventListener('DOMContentLoaded', () => {
                    function updateBadge(iconName, val) {
                        let dotEls = document.querySelectorAll(`.js-badge-dot-${iconName}`);
                        dotEls.forEach(el => el.classList.remove('hidden'));
                        
                        let countEls = document.querySelectorAll(`.js-badge-count-${iconName}`);
                        countEls.forEach(el => {
                            let currentVal = parseInt(el.textContent.trim()) || 0;
                            let newVal = currentVal + val;
                            el.textContent = newVal;
                            el.classList.remove('hidden');
                        });
                    }

                    if (window.Echo) {
                        window.Echo.private('user.{{ Auth::id() }}')
                            .listen('.UserNotificationCreated', (e) => {
                                updateBadge('heart', 1);
                                if (window.Livewire) {
                                    Livewire.dispatch('refreshNotifications');
                                }
                            })
                            .listen('.ConversationUpdated', (e) => {
                                updateBadge('message', 1);
                            });
                    }

                    // Presence Heartbeat Loop (throttled/visibility-aware & idle-aware)
                    let lastActiveTime = Date.now();
                    const idleLimit = 5 * 60 * 1000; // 5 minutes

                    function updateActivity() {
                        lastActiveTime = Date.now();
                    }

                    ['mousemove', 'keydown', 'scroll', 'click'].forEach(evt => {
                        window.addEventListener(evt, updateActivity, { passive: true });
                    });

                    function sendHeartbeat() {
                        // Sleep if the tab is hidden
                        if (document.visibilityState !== 'visible') return;

                        // Sleep if user is idle
                        if (Date.now() - lastActiveTime > idleLimit) {
                            return;
                        }

                        fetch('{{ route('presence.heartbeat') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            }
                        }).catch(err => console.error('Heartbeat error:', err));
                    }

                    sendHeartbeat();
                    setInterval(sendHeartbeat, 45000);
                });
            </script>
        @endauth
        @auth
            <livewire:partials.app.ai-chatbot />
        @endauth

    </body>
</html>
