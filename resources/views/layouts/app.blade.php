<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="theme-color" content="#124874">

        <title>{{ config('app.name', 'UEConnect') }}{{ isset($title) ? ' — ' . $title : '' }}</title>

        <meta name="description" content="{{ $description ?? 'UEConnect — Kết nối cộng đồng sinh viên HCMUE.' }}">

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

        {{-- Additional head slots --}}
        @stack('head')
    </head>

    <body class="font-sans antialiased h-full">

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
                                        $currentTitle = 'Dashboard';
                                        foreach(\App\Support\Navigation\AdminNavigation::getGroups() as $g) {
                                            foreach($g['items'] as $i) {
                                                if(request()->routeIs($i['route']) || (str_contains($i['route'], '.') && request()->routeIs(explode('.', $i['route'])[0] . '.*'))) {
                                                    $currentTitle = $i['label'];
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
                                            <nav class="flex flex-col gap-5 text-sm">
                                                @foreach(\App\Support\Navigation\AdminNavigation::getVisibleGroups() as $groupKey => $group)
                                                    <div class="flex flex-col gap-1.5">
                                                        <h3 class="text-3xs font-bold uppercase tracking-wider text-ue-text-muted/60">
                                                            {{ $group['vn_label'] }}
                                                        </h3>
                                                        <ul class="flex flex-col gap-0.5" role="list">
                                                            @foreach($group['items'] as $item)
                                                                @php
                                                                    $active = request()->routeIs($item['route']) || ($item['route'] === 'admin.dashboard' && request()->routeIs('admin.dashboard'));
                                                                    $active = $active || (str_contains($item['route'], '.') && request()->routeIs(explode('.', $item['route'])[0] . '.*'));
                                                                @endphp
                                                                <li role="listitem">
                                                                    <a
                                                                        href="{{ route($item['route']) }}"
                                                                        @click="adminDrawerOpen = false"
                                                                        class="flex items-center gap-2.5 px-2 py-1.5 rounded-lg font-semibold transition-colors duration-150 {{ $active ? 'bg-ue-brand-soft text-ue-brand-active' : 'text-ue-text-secondary hover:bg-ue-surface-hover hover:text-ue-brand-active' }}"
                                                                    >
                                                                        <x-ui.icon :name="$item['icon']" size="sm" class="{{ $active ? 'text-ue-brand-active' : 'text-ue-text-muted' }}" />
                                                                        <span class="text-xs">{{ $item['label'] }}</span>
                                                                    </a>
                                                                </li>
                                                            @endforeach
                                                        </ul>
                                                    </div>
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

    </body>
</html>
