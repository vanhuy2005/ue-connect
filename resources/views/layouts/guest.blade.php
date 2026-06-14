<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="theme-color" content="#124874">

        <title>{{ config('app.name', 'UEConnect') }}{{ isset($title) ? ' — ' . $title : '' }}</title>

        <meta name="description" content="Đăng nhập vào UEConnect — Kết nối cộng đồng sinh viên HCMUE.">

        {{-- PWA Meta Tags --}}
        <link rel="manifest" href="{{ asset('manifest.json') }}">
        <meta name="mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="default">
        <meta name="apple-mobile-web-app-title" content="UEConnect">
        <link rel="apple-touch-icon" href="{{ asset('icons/apple-touch-icon.png') }}">

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
        <script>
            if (window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone) {
                document.documentElement.classList.add('is-standalone');
            }
        </script>
    </head>

    <body class="font-sans antialiased h-full bg-slate-50 text-slate-900">
        {{-- Skip link --}}
        <a href="#main-content" class="skip-link sr-only focus:not-sr-only focus:fixed focus:top-4 focus:left-4 focus:z-50 focus:px-4 focus:py-2 focus:bg-white focus:text-ue-brand focus:rounded-lg focus:text-sm focus:font-semibold">
            Bỏ qua và đến nội dung chính
        </a>

        <div class="min-h-screen flex flex-col md:flex-row bg-slate-50 text-slate-900 animate-fade-in">
            
            {{-- Left Side: Premium Brand Showcase (50% on md+) --}}
            <div class="hidden md:flex md:w-1/2 bg-[#0A243F] text-white flex-col justify-between p-12 relative overflow-hidden select-none">
                {{-- Background texture --}}
                <div class="absolute inset-0 opacity-[0.03] pointer-events-none"
                     style="background-image: radial-gradient(circle at 1px 1px, #fff 1px, transparent 0); background-size: 28px 28px;">
                </div>
                
                {{-- Top logo --}}
                <div class="relative z-10">
                    <x-brand.logo variant="horizontal" size="md" class="brightness-0 invert" />
                </div>
                
                {{-- Mockup --}}
                <div class="flex-grow flex items-center justify-center py-6 relative z-10">
                    <div class="relative w-full max-w-[280px] aspect-[9/18.5] bg-slate-900 rounded-[40px] p-2.5 shadow-2xl border-[6px] border-slate-950 flex items-center justify-center">
                        <div class="absolute top-0 left-1/2 -translate-x-1/2 w-28 h-5 bg-slate-950 rounded-b-2xl z-20 flex items-center justify-center">
                            <div class="w-10 h-1 bg-slate-800 rounded-full mb-1"></div>
                        </div>
                        <div class="relative w-full h-full rounded-[30px] overflow-hidden bg-white">
                            <img src="{{ asset('images/auth-showcase.png') }}" alt="UEConnect App Preview" class="w-full h-full object-cover">
                        </div>
                    </div>
                </div>
                
                {{-- Bottom description --}}
                <div class="relative z-10 text-left">
                    <p class="text-sm text-slate-350 font-medium">Nền tảng kết nối, học tập và chia sẻ cơ hội an toàn dành riêng cho HCMUE.</p>
                </div>

                {{-- Soft glows --}}
                <div class="absolute w-64 h-64 rounded-full bg-blue-500/10 blur-3xl -top-10 -left-10"></div>
                <div class="absolute w-64 h-64 rounded-full bg-ue-brand/10 blur-3xl -bottom-10 -right-10"></div>
            </div>
            
            {{-- Right Side: Active Form Page (50% on md+) --}}
            <div class="flex-grow md:w-1/2 flex flex-col justify-between bg-white px-6 py-6 sm:px-12 md:px-16 lg:px-20">
                
                {{-- Top header link --}}
                <div class="flex justify-between items-center md:justify-end">
                    <a href="{{ route('landing') }}" class="md:hidden">
                        <x-brand.logo variant="horizontal" size="sm" />
                    </a>
                    <a href="{{ route('landing') }}" class="text-xs font-semibold text-slate-500 hover:text-ue-brand flex items-center gap-1 transition-colors">
                        <x-ui.icon name="arrow-left" size="xs" />
                        Quay lại trang chủ
                    </a>
                </div>
                
                {{-- Form content directly on white background --}}
                <main id="main-content" class="my-auto w-full max-w-[440px] mx-auto py-4">
                    {{ $slot }}
                </main>
                
                {{-- Footer --}}
                <footer class="text-2xs text-slate-400 text-center md:text-left mt-8 pt-4 border-t border-slate-100 flex flex-col sm:flex-row justify-between gap-2 font-medium">
                    <span>© {{ date('Y') }} UEConnect. HCMUE.</span>
                    <div class="flex gap-x-4 justify-center">
                        <a href="#" class="hover:text-ue-brand transition-colors">Điều khoản</a>
                        <a href="#" class="hover:text-ue-brand transition-colors">Bảo mật</a>
                        <a href="#" class="hover:text-ue-brand transition-colors">Trợ giúp</a>
                    </div>
                </footer>
                
            </div>
            
        </div>
    </body>
</html>
