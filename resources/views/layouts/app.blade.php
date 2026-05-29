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

                {{-- Topbar (Only for admin or custom shells, NOT social, conversation, guest, or auth) --}}
                @if(!in_array($shell, ['social', 'conversation', 'guest', 'auth']))
                    @include('partials.app.topbar')
                @endif

                {{-- Account status banner for restrictions --}}
                @if(in_array($shell, ['social', 'admin', 'conversation']))
                    <x-ui.account-status-banner />
                @endif

                {{-- Page content --}}
                <main
                    id="main-content"
                    class="flex-1 {{ in_array($shell, ['social', 'admin', 'conversation']) ? 'pb-16 lg:pb-0' : '' }}"
                    tabindex="-1"
                >
                    {{ $slot }}
                </main>

            </div>
        </div>

        {{-- Mobile bottom nav --}}
        @if(in_array($shell, ['social', 'admin', 'conversation']))
            @include('partials.app.mobile-bottom-nav')
        @endif

        {{-- Additional script slots --}}
        @stack('scripts')

    </body>
</html>
