<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
    <title>@yield('title') - {{ config('app.name', 'UEConnect') }}</title>
    
    {{-- Realtime Meta Config --}}
    @include('partials.realtime-meta')

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script>
        if (window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone) {
            document.documentElement.classList.add('is-standalone');
        }
    </script>
</head>
<body class="font-sans antialiased text-slate-800 bg-white min-h-screen flex flex-col selection:bg-ue-brand/20 selection:text-ue-brand">
    {{-- Header --}}
    <header class="fixed top-0 left-0 right-0 w-full bg-white/80 backdrop-blur-xl border-b border-slate-200/60 z-50">
        <div class="max-w-5xl mx-auto px-6 h-16 flex items-center justify-between relative">
            {{-- Logo (Trái ngoài cùng) --}}
            <a href="{{ route('landing') }}" wire:navigate class="flex items-center transition-transform hover:scale-[0.98]">
                <x-brand.logo variant="horizontal" size="sm" class="md:hidden" />
                <x-brand.logo variant="horizontal" size="md" class="hidden md:block" />
            </a>

            {{-- 3 Options (Chính xác ở giữa) --}}
            <nav class="hidden md:flex items-center gap-8 text-[13px] font-semibold tracking-wide uppercase absolute left-1/2 -translate-x-1/2">
                <a href="{{ route('terms') }}" wire:navigate class="{{ request()->routeIs('terms') ? 'text-slate-900' : 'text-slate-400 hover:text-slate-900' }} transition-colors">Điều khoản</a>
                <a href="{{ route('privacy') }}" wire:navigate class="{{ request()->routeIs('privacy') ? 'text-slate-900' : 'text-slate-400 hover:text-slate-900' }} transition-colors">Bảo mật</a>
                <a href="{{ route('community-standards') }}" wire:navigate class="{{ request()->routeIs('community-standards') ? 'text-slate-900' : 'text-slate-400 hover:text-slate-900' }} transition-colors">Tiêu chuẩn</a>
            </nav>

            {{-- Nút Quay về (Phải ngoài cùng) với mũi tên chỉa trái --}}
            <a href="{{ route('landing') }}" wire:navigate class="text-[13.5px] font-semibold text-slate-500 hover:text-ue-brand transition-colors flex items-center gap-2">
                <span aria-hidden="true">&larr;</span> Trang chủ
            </a>
        </div>
    </header>

    {{-- Main Content (Editorial Layout) --}}
    <main class="flex-1 w-full max-w-3xl mx-auto px-6 pt-32 pb-16 md:pt-40 md:pb-24">
        {{-- Page Header --}}
        <header class="mb-16 md:mb-20">
            <h1 class="text-4xl md:text-5xl lg:text-6xl font-extrabold text-slate-900 tracking-tight leading-[1.1] mb-6">
                @yield('title')
            </h1>
            @hasSection('subtitle')
                <p class="text-base md:text-lg text-slate-500 font-medium tracking-wide">@yield('subtitle')</p>
            @endif
        </header>
        
        {{-- Content Area --}}
        <article class="text-base md:text-[17px] leading-[1.8] text-slate-700">
            <style>
                /* Anti-slop Editorial Typography */
                .legal-content h2 { font-size: 1.5rem; font-weight: 800; color: #0f172a; margin-top: 3.5rem; margin-bottom: 1.25rem; letter-spacing: -0.02em; line-height: 1.3; }
                @media (min-width: 768px) { .legal-content h2 { font-size: 1.875rem; } }
                .legal-content h3 { font-size: 1.25rem; font-weight: 700; color: #1e293b; margin-top: 2.5rem; margin-bottom: 1rem; letter-spacing: -0.01em; }
                .legal-content p { margin-top: 1.5rem; margin-bottom: 1.5rem; }
                .legal-content ul { margin-top: 1.5rem; margin-bottom: 1.5rem; padding-left: 0; list-style-type: none; }
                .legal-content ol { margin-top: 1.5rem; margin-bottom: 1.5rem; padding-left: 0; list-style-type: none; counter-reset: item; }
                .legal-content li { margin-top: 0.75rem; margin-bottom: 0.75rem; position: relative; padding-left: 1.75rem; }
                .legal-content ul li::before { content: "—"; position: absolute; left: 0; color: #94a3b8; font-weight: 600; }
                .legal-content ol li { counter-increment: item; }
                .legal-content ol li::before { content: counter(item) "."; position: absolute; left: 0; color: #94a3b8; font-weight: 700; font-feature-settings: "tnum"; }
                .legal-content a { color: #0f172a; text-decoration: none; font-weight: 600; border-bottom: 1px solid #cbd5e1; transition: border-color 0.2s ease; padding-bottom: 1px; }
                .legal-content a:hover { border-color: #0f172a; }
                .legal-content strong { font-weight: 700; color: #0f172a; }
                .legal-content hr { border: 0; border-top: 1px solid #e2e8f0; margin-top: 4rem; margin-bottom: 4rem; }
                .legal-content code { font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace; font-size: 0.875em; padding: 0.2em 0.4em; background-color: #f1f5f9; border-radius: 0.375rem; color: #475569; }
            </style>
            <div class="legal-content">
                @yield('content')
            </div>
        </article>
    </main>

    {{-- Footer --}}
    <footer class="bg-white border-t border-slate-200 py-12 mt-auto">
        <div class="max-w-5xl mx-auto px-6 flex flex-col md:flex-row justify-between items-center gap-6">
            <x-brand.logo variant="horizontal" size="sm" class="grayscale opacity-50 hover:grayscale-0 hover:opacity-100 transition-all duration-300" />
            <p class="text-[13px] font-medium text-slate-400 tracking-wide">&copy; {{ date('Y') }} UEConnect. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>
