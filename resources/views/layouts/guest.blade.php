<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="theme-color" content="#124874">

        <title>{{ config('app.name', 'UEConnect') }}{{ isset($title) ? ' — ' . $title : '' }}</title>

        <meta name="description" content="Đăng nhập vào UEConnect — Kết nối cộng đồng sinh viên HCMUE.">

        {{-- Fonts: Be Vietnam Pro --}}
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
    </head>

    <body class="font-sans antialiased">
        {{-- Skip link --}}
        <a href="#main-content" class="skip-link">Bỏ qua và đến nội dung chính</a>

        <div class="min-h-screen bg-ue-bg flex flex-col items-center justify-center py-12 px-4 sm:px-6">

            {{-- Brand logo --}}
            <div class="mb-8 text-center">
                <a
                    href="/"
                    wire:navigate
                    class="inline-flex items-center justify-center ue-focus-ring rounded-lg"
                    aria-label="UEConnect - Trang chủ"
                >
                    <x-brand.logo variant="mark" size="lg" />
                </a>
                <p class="mt-3 text-sm text-ue-text-muted font-medium tracking-wide">
                    Kết nối cộng đồng HCMUE
                </p>
            </div>

            {{-- Auth card --}}
            <main
                id="main-content"
                class="w-full max-w-md"
                tabindex="-1"
            >
                <div class="bg-ue-surface rounded-2xl border border-ue-border shadow-md px-6 py-8 sm:px-8">
                    {{ $slot }}
                </div>
            </main>

            {{-- Footer note --}}
            <p class="mt-8 text-xs text-ue-text-muted text-center">
                © {{ date('Y') }} UEConnect — Nền tảng sinh viên HCMUE
            </p>
        </div>
    </body>
</html>
