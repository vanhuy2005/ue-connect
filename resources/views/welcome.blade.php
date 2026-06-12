@php
    $isAuthenticated = auth()->check();
    $dashboardRoute = route('dashboard');
@endphp
<!DOCTYPE html>
<html lang="vi" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#124874">

    <title>UEConnect — Kết nối cộng đồng HCMUE</title>
    <meta name="description" content="UEConnect là mạng xã hội học tập và kết nối dành riêng cho sinh viên, giảng viên và cựu sinh viên HCMUE.">

    {{-- PWA Meta Tags --}}
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="UEConnect">
    <link rel="apple-touch-icon" href="{{ asset('icons/apple-touch-icon.png') }}">

    {{-- Open Graph --}}
    <meta property="og:title" content="UEConnect — Kết nối cộng đồng HCMUE">
    <meta property="og:description" content="Kết nối sinh viên, cựu sinh viên và giảng viên HCMUE trong môi trường an toàn, xác thực.">
    <meta property="og:type" content="website">

    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600;700;900&display=swap" rel="stylesheet">

    {{-- Favicon --}}
    <link rel="icon" type="image/png" href="{{ asset('images/brand/ueconnect-mark-nobg.png') }}">

    {{-- Assets --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="font-sans antialiased h-[100dvh] w-full overflow-hidden flex flex-col bg-slate-50 text-slate-900" x-data="{
    activeSlide: 0,
    totalSlides: 5,
    touchStartX: 0,
    touchEndX: 0,
    next() { this.activeSlide = (this.activeSlide + 1) % this.totalSlides; },
    prev() { this.activeSlide = (this.activeSlide - 1 + this.totalSlides) % this.totalSlides; },
    handleSwipe() {
        if (this.touchEndX < this.touchStartX - 50) this.next();
        if (this.touchEndX > this.touchStartX + 50) this.prev();
    }
}" @keydown.right.window="next()" @keydown.left.window="prev()"
@touchstart="touchStartX = $event.changedTouches[0].screenX"
@touchend="touchEndX = $event.changedTouches[0].screenX; handleSwipe()">

    {{-- Skip to content (accessibility) --}}
    <a href="#main-content" class="skip-link sr-only focus:not-sr-only focus:fixed focus:top-4 focus:left-4 focus:z-50 focus:px-4 focus:py-2 focus:bg-white focus:text-ue-brand focus:rounded-lg focus:text-sm focus:font-semibold">
        Bỏ qua và đến nội dung chính
    </a>

    {{-- ============================================================ --}}
    {{-- HEADER --}}
    {{-- ============================================================ --}}
    <header class="bg-white/85 backdrop-blur-md border-b border-slate-200/80 flex-shrink-0 z-50" role="banner">
        <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between gap-4" aria-label="Điều hướng chính">
            <a href="{{ route('landing') }}" aria-label="Trang chủ UEConnect" class="flex-shrink-0">
                <x-brand.logo variant="horizontal" size="md" />
            </a>

            {{-- Nav menu (Desktop-only) --}}
            <div class="hidden lg:flex items-center gap-1" role="list">
                @foreach([
                    [0, 'Tổng quan'],
                    [1, 'Xác thực'],
                    [2, 'Cộng đồng'],
                    [3, 'Mentoring'],
                    [4, 'Chatbot AI']
                ] as [$slide, $label])
                    <button
                        @click="activeSlide = {{ $slide }}"
                        role="listitem"
                        class="px-3.5 py-1.5 rounded-lg text-xs font-semibold tracking-tight transition-colors"
                        :class="activeSlide === {{ $slide }} ? 'text-ue-brand bg-ue-brand-soft' : 'text-slate-600 hover:text-slate-950 hover:bg-slate-100'"
                    >{{ $label }}</button>
                @endforeach
            </div>

            {{-- Actions --}}
            <div class="flex items-center gap-2 flex-shrink-0">
                <a href="{{ route('pwa.install') }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-600 hover:text-slate-950 px-2.5 py-1.5 rounded-lg hover:bg-slate-100 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-ue-brand" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                    Cài đặt App
                </a>
                @if($isAuthenticated)
                    <x-ui.button
                        href="{{ $dashboardRoute }}"
                        variant="primary"
                        size="sm"
                        class="font-bold text-xs"
                    >
                        Vào UEConnect
                    </x-ui.button>
                @else
                    <x-ui.button
                        href="{{ route('login') }}"
                        variant="ghost"
                        size="sm"
                        class="text-slate-700 hover:bg-slate-100 border-0 hidden sm:inline-flex font-bold text-xs"
                    >
                        Đăng nhập
                    </x-ui.button>
                    <x-ui.button
                        href="{{ route('register') }}"
                        variant="primary"
                        size="sm"
                        class="font-bold text-xs"
                    >
                        Đăng ký
                    </x-ui.button>
                @endif
            </div>
        </nav>
    </header>

    {{-- ============================================================ --}}
    {{-- MAIN CONTENT CAROUSEL --}}
    {{-- ============================================================ --}}
    <main id="main-content" tabindex="-1" class="flex-1 relative overflow-hidden group">
        {{-- Carousel Track --}}
        <div class="flex h-full w-full transition-transform duration-700 ease-in-out" :style="`transform: translateX(-${activeSlide * 100}%)`">
            
            {{-- Slide 0: Main Hero --}}
            <div class="w-full h-full flex-shrink-0 flex items-center justify-center px-4 sm:px-6 lg:px-8 py-8 overflow-y-auto">
                <div class="max-w-7xl w-full grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center">
                    {{-- 1. Left side: Copy --}}
                    <div class="lg:col-span-6 flex flex-col justify-center text-left">
                        <h1 class="flex flex-col md:flex-row gap-2 md:gap-4 lg:gap-5 mb-8 md:items-stretch w-full md:w-auto">
                            <!-- Left Column (Labels) -->
                            <div class="flex flex-col justify-start md:justify-between gap-1 md:gap-0 md:pr-3 lg:pr-5 text-left md:text-right py-1 md:py-2 lg:py-4 xl:py-5 border-b-2 border-slate-200 md:border-b-0 pb-3 md:pb-0 mb-1 md:mb-0">
                                <span class="block text-[clamp(1rem,1.5vw,1.5rem)] xl:text-[1.75rem] font-black uppercase text-slate-600 tracking-tight leading-[1.1] whitespace-nowrap">
                                    THIẾT KẾ CHO
                                </span>
                                <span class="block text-[clamp(1rem,1.5vw,1.5rem)] xl:text-[1.75rem] font-black uppercase text-slate-600 tracking-tight leading-[1.1] whitespace-nowrap">
                                    XÂY DỰNG BỞI
                                </span>
                            </div>

                            <!-- Right Column (Headline) -->
                            <div class="flex flex-col justify-center gap-y-1 sm:gap-y-2 text-left">
                                <span class="block text-[clamp(2.5rem,6vw,3.5rem)] xl:text-[4rem] font-black uppercase tracking-tighter leading-[1.0] whitespace-nowrap">
                                    <span class="text-ue-brand">SINH</span> <span class="text-[#A61D37]">VIÊN</span>
                                </span>
                                <span class="block text-[clamp(2.5rem,6vw,3.5rem)] xl:text-[4rem] font-black uppercase tracking-tighter leading-[1.0] whitespace-nowrap">
                                    <span class="text-ue-brand">SƯ</span> <span class="text-[#A61D37]">PHẠM.</span>
                                </span>
                            </div>
                        </h1>
                        <p class="text-sm sm:text-base text-slate-600 max-w-lg mb-8 leading-relaxed">
                            UEConnect là mạng xã hội nội bộ dành riêng cho sinh viên, giảng viên và cựu sinh viên Đại học Sư phạm TP.HCM (HCMUE). Nền tảng kết nối học thuật, định hướng nghề nghiệp và tham gia cộng đồng an toàn.
                        </p>
                        <div class="flex flex-col sm:flex-row gap-3 w-full sm:w-auto justify-center md:justify-start">
                            @if($isAuthenticated)
                                <x-ui.button href="{{ $dashboardRoute }}" variant="primary" size="lg" icon="arrow-right" icon-position="right" class="font-bold w-full sm:w-auto text-sm">
                                    Vào Bảng điều khiển
                                </x-ui.button>
                            @else
                                <x-ui.button href="{{ route('login') }}" variant="primary" size="lg" icon="microsoft" class="font-bold w-full sm:w-auto text-sm">
                                    Đăng nhập Entra ID
                                </x-ui.button>
                                <x-ui.button href="{{ route('register') }}" variant="secondary" size="lg" icon="user-plus" class="border border-slate-300 text-slate-700 bg-white hover:bg-slate-50 w-full sm:w-auto font-bold text-sm">
                                    Tham gia ngay
                                </x-ui.button>
                            @endif
                        </div>
                    </div>
                    {{-- 2. Right side: Hero Image --}}
                    <div class="lg:col-span-6 flex justify-center lg:justify-end relative">
                        <img src="{{ asset('images/brand/hero-img-ue-connect.png') }}" alt="UEConnect Showcase" class="w-full max-w-[500px] lg:max-w-[560px] xl:max-w-[750px] h-auto object-contain rounded-2xl drop-shadow-2xl hover:-translate-y-2 transition-transform duration-500">
                    </div>
                </div>
            </div>

            {{-- Slide 1: Verification & Safety --}}
            <div class="w-full h-full flex-shrink-0 flex items-center justify-center px-4 sm:px-6 lg:px-8 py-8 lg:py-12 bg-white overflow-y-auto">
                <div class="max-w-6xl w-full mx-auto grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-12 items-center pb-16 lg:pb-0">
                    <div class="order-2 lg:order-1 flex justify-center">
                        <img src="{{ asset('images/brand/feature-verification.png') }}" onerror="this.src='https://placehold.co/600x400/F0F5FA/124874?text=Xác+Thực'" alt="Identity Verification" class="w-full max-w-[500px] h-auto rounded-xl drop-shadow-xl border border-slate-100 object-cover aspect-video sm:aspect-auto">
                    </div>
                    <div class="order-1 lg:order-2">
                        <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-ue-brand-soft text-ue-brand text-[10px] sm:text-xs font-bold uppercase tracking-wider mb-4 sm:mb-6">
                            <span class="w-2 h-2 rounded-full bg-ue-brand animate-pulse"></span> Identity Verification
                        </div>
                        <h2 class="text-2xl sm:text-3xl lg:text-4xl font-black text-slate-900 leading-tight mb-3 sm:mb-4">
                            Môi trường <span class="text-ue-brand">xác thực 100%</span>
                        </h2>
                        <p class="text-sm sm:text-base lg:text-lg text-slate-600 mb-6">
                            Đảm bảo cộng đồng an toàn với quy trình xác minh qua công nghệ AI OCR nhận diện sinh viên. Nền tảng "Verified campus social platform" chính thức.
                        </p>
                        <ul class="space-y-3 sm:space-y-4">
                            <li class="flex items-start gap-3">
                                <x-ui.icon name="trash" class="w-5 h-5 sm:w-6 sm:h-6 text-ue-brand flex-shrink-0 mt-0.5" />
                                <span class="text-xs sm:text-sm lg:text-base text-slate-700"><strong>Tự động xóa minh chứng:</strong> Dữ liệu thẻ sinh viên lập tức bị hủy sau khi phê duyệt để đảm bảo riêng tư.</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <x-ui.icon name="microsoft" class="w-5 h-5 sm:w-6 sm:h-6 text-ue-brand flex-shrink-0 mt-0.5" />
                                <span class="text-xs sm:text-sm lg:text-base text-slate-700"><strong>Đăng nhập một chạm:</strong> Hỗ trợ xác thực SSO qua tài khoản email giáo dục Microsoft Entra ID.</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <x-ui.icon name="permissions" class="w-5 h-5 sm:w-6 sm:h-6 text-ue-brand flex-shrink-0 mt-0.5" />
                                <span class="text-xs sm:text-sm lg:text-base text-slate-700"><strong>Cơ chế Role-based:</strong> Phân quyền chặt chẽ giữa Sinh viên, Giảng viên và Cựu sinh viên.</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            {{-- Slide 2: Communities & Social --}}
            <div class="w-full h-full flex-shrink-0 flex items-center justify-center px-4 sm:px-6 lg:px-8 py-8 lg:py-12 bg-slate-50 overflow-y-auto">
                <div class="max-w-6xl w-full mx-auto grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-12 items-center pb-16 lg:pb-0">
                    <div>
                        <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-ue-brand-soft text-ue-brand text-[10px] sm:text-xs font-bold uppercase tracking-wider mb-4 sm:mb-6">
                            <span class="w-2 h-2 rounded-full bg-ue-brand"></span> Social Layer
                        </div>
                        <h2 class="text-2xl sm:text-3xl lg:text-4xl font-black text-slate-900 leading-tight mb-3 sm:mb-4">
                            Giao lưu & <span class="text-ue-brand">Cộng đồng CLB</span>
                        </h2>
                        <p class="text-sm sm:text-base lg:text-lg text-slate-600 mb-6">
                            Dễ dàng khám phá bạn bè cùng ngành qua tính năng Discovery. Tham gia các cộng đồng, câu lạc bộ và tham gia thảo luận các môn học.
                        </p>
                        <ul class="space-y-3 sm:space-y-4">
                            <li class="flex items-start gap-3">
                                <x-ui.icon name="users" class="w-5 h-5 sm:w-6 sm:h-6 text-ue-brand flex-shrink-0 mt-0.5" />
                                <span class="text-xs sm:text-sm lg:text-base text-slate-700"><strong>Mạng lưới Câu lạc bộ:</strong> Tạo hoặc tham gia không gian sinh hoạt chung của Đoàn - Hội và các CLB.</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <x-ui.icon name="message-square" class="w-5 h-5 sm:w-6 sm:h-6 text-ue-brand flex-shrink-0 mt-0.5" />
                                <span class="text-xs sm:text-sm lg:text-base text-slate-700"><strong>Tin nhắn thời gian thực:</strong> Trò chuyện 1:1 siêu tốc qua hệ thống Laravel Reverb WebSocket.</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <x-ui.icon name="file-text" class="w-5 h-5 sm:w-6 sm:h-6 text-ue-brand flex-shrink-0 mt-0.5" />
                                <span class="text-xs sm:text-sm lg:text-base text-slate-700"><strong>Chia sẻ tài nguyên học thuật:</strong> Đăng bài chia sẻ kỹ năng, slide bài giảng với CDN tốc độ cao.</span>
                            </li>
                        </ul>
                    </div>
                    <div class="flex justify-center">
                        <img src="{{ asset('images/brand/feature-community.png') }}" onerror="this.src='https://placehold.co/600x400/F0F5FA/124874?text=Cộng+Đồng'" alt="Communities and Social" class="w-full max-w-[500px] h-auto rounded-xl drop-shadow-xl border border-slate-200 object-cover aspect-video sm:aspect-auto">
                    </div>
                </div>
            </div>

            {{-- Slide 3: Mentoring --}}
            <div class="w-full h-full flex-shrink-0 flex items-center justify-center px-4 sm:px-6 lg:px-8 py-8 lg:py-12 bg-white overflow-y-auto">
                <div class="max-w-6xl w-full mx-auto grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-12 items-center pb-16 lg:pb-0">
                    <div class="order-2 lg:order-1 flex justify-center">
                        <img src="{{ asset('images/brand/feature-mentor.png') }}" onerror="this.src='https://placehold.co/600x400/F0F5FA/124874?text=Mentoring'" alt="Mentoring" class="w-full max-w-[500px] h-auto rounded-xl drop-shadow-xl border border-slate-100 object-cover aspect-video sm:aspect-auto">
                    </div>
                    <div class="order-1 lg:order-2">
                        <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-ue-brand-soft text-ue-brand text-[10px] sm:text-xs font-bold uppercase tracking-wider mb-4 sm:mb-6">
                            <span class="w-2 h-2 rounded-full bg-ue-brand"></span> Career Pathway
                        </div>
                        <h2 class="text-2xl sm:text-3xl lg:text-4xl font-black text-slate-900 leading-tight mb-3 sm:mb-4">
                            Định hướng cùng <span class="text-ue-brand">Mentor & Alumni</span>
                        </h2>
                        <p class="text-sm sm:text-base lg:text-lg text-slate-600 mb-6">
                            Xây dựng hành trình phát triển vững chắc thông qua việc nhận hướng dẫn từ các giảng viên, cố vấn chuyên môn và cựu sinh viên đi trước.
                        </p>
                        <ul class="space-y-3 sm:space-y-4">
                            <li class="flex items-start gap-3">
                                <x-ui.icon name="mentor" class="w-5 h-5 sm:w-6 sm:h-6 text-ue-brand flex-shrink-0 mt-0.5" />
                                <span class="text-xs sm:text-sm lg:text-base text-slate-700"><strong>Kết nối cố vấn chuyên môn:</strong> Định hướng tham gia các đề tài NCKH, phương pháp dạy học.</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <x-ui.icon name="book-open" class="w-5 h-5 sm:w-6 sm:h-6 text-ue-brand flex-shrink-0 mt-0.5" />
                                <span class="text-xs sm:text-sm lg:text-base text-slate-700"><strong>Thực tế học tập & Giảng dạy:</strong> Học hỏi từ các sinh viên K42, K44 đang đứng lớp thực tế.</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <x-ui.icon name="briefcase" class="w-5 h-5 sm:w-6 sm:h-6 text-ue-brand flex-shrink-0 mt-0.5" />
                                <span class="text-xs sm:text-sm lg:text-base text-slate-700"><strong>Khám phá cơ hội nghề nghiệp:</strong> Việc làm part-time, thông tin thực tập được xác thực rõ ràng.</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            {{-- Slide 4: AI Chatbot --}}
            <div class="w-full h-full flex-shrink-0 flex items-center justify-center px-4 sm:px-6 lg:px-8 py-8 lg:py-12 bg-slate-50 overflow-y-auto">
                <div class="max-w-6xl w-full mx-auto grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-12 items-center pb-16 lg:pb-0">
                    <div>
                        <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-ue-brand-soft text-ue-brand text-[10px] sm:text-xs font-bold uppercase tracking-wider mb-4 sm:mb-6">
                            <span class="w-2 h-2 rounded-full bg-ue-brand animate-pulse"></span> AI Assistant
                        </div>
                        <h2 class="text-2xl sm:text-3xl lg:text-4xl font-black text-slate-900 leading-tight mb-3 sm:mb-4">
                            Hỏi đáp học vụ với <span class="text-ue-brand">HCMUE Chatbot</span>
                        </h2>
                        <p class="text-sm sm:text-base lg:text-lg text-slate-600 mb-6">
                            Trải nghiệm sức mạnh của Generative AI và kiến trúc RAG. Chatbot nắm vững quy chế, chuẩn đầu ra và sổ tay sinh viên của HCMUE.
                        </p>
                        <ul class="space-y-3 sm:space-y-4">
                            <li class="flex items-start gap-3">
                                <x-ui.icon name="message-circle" class="w-5 h-5 sm:w-6 sm:h-6 text-ue-brand flex-shrink-0 mt-0.5" />
                                <span class="text-xs sm:text-sm lg:text-base text-slate-700"><strong>Giải đáp 24/7:</strong> Hỗ trợ sinh viên lập kế hoạch học tập, điều kiện ra trường và quy định đào tạo.</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <x-ui.icon name="database" class="w-5 h-5 sm:w-6 sm:h-6 text-ue-brand flex-shrink-0 mt-0.5" />
                                <span class="text-xs sm:text-sm lg:text-base text-slate-700"><strong>Tri thức độc quyền:</strong> Liên tục trích xuất dữ liệu từ Qdrant Vector DB & CSDL SQL Server của trường.</span>
                            </li>
                            <li class="flex items-start gap-3">
                                <x-ui.icon name="sparkles" class="w-5 h-5 sm:w-6 sm:h-6 text-ue-brand flex-shrink-0 mt-0.5" />
                                <span class="text-xs sm:text-sm lg:text-base text-slate-700"><strong>Đa mô hình AI:</strong> Ứng dụng Gemini 2.0 Flash kết hợp Local LLM với cơ chế ngăn chặn ảo giác.</span>
                            </li>
                        </ul>
                    </div>
                    <div class="flex justify-center">
                        <img src="{{ asset('images/brand/feature-chatbot.png') }}" onerror="this.src='https://placehold.co/600x400/F0F5FA/124874?text=Chatbot+AI'" alt="HCMUE Chatbot" class="w-full max-w-[500px] h-auto rounded-xl drop-shadow-xl border border-slate-200 object-cover aspect-video sm:aspect-auto">
                    </div>
                </div>
            </div>

        </div>

        {{-- Slide Navigation Indicators (Floating at Bottom) --}}
        <div class="absolute bottom-6 left-1/2 -translate-x-1/2 flex items-center gap-3 sm:gap-6 bg-white/80 backdrop-blur-md px-4 sm:px-6 py-2 sm:py-3 rounded-full shadow-lg border border-slate-200/50 z-10">
            <button @click="prev()" class="p-1 sm:p-0 text-slate-400 hover:text-slate-900 transition-colors" aria-label="Slide trước">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
            </button>
            <div class="flex gap-2">
                <template x-for="i in totalSlides" :key="i">
                    <button @click="activeSlide = i - 1" 
                            class="h-2 sm:h-2.5 rounded-full transition-all duration-300"
                            :class="activeSlide === i - 1 ? 'w-6 sm:w-8 bg-ue-brand' : 'w-2 sm:w-2.5 bg-slate-300 hover:bg-slate-400'"
                            :aria-label="`Đến slide ${i}`"></button>
                </template>
            </div>
            <button @click="next()" class="p-1 sm:p-0 text-slate-400 hover:text-slate-900 transition-colors" aria-label="Slide tiếp">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
            </button>
        </div>
    </main>

    {{-- ============================================================ --}}
    {{-- FOOTER --}}
    {{-- ============================================================ --}}
    <footer class="bg-white border-t border-slate-200 py-4 text-xs text-slate-500 font-medium flex-shrink-0 z-50" role="contentinfo">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row justify-between items-center gap-2">
            <div class="flex items-center gap-2">
                <x-brand.logo variant="mark" size="sm" class="opacity-80" />
                <span>© {{ date('Y') }} UEConnect. Bảo lưu mọi quyền.</span>
            </div>
            <div class="flex gap-x-6 gap-y-1 flex-wrap">
                <a href="{{ route('pwa.install') }}" class="hover:text-ue-brand transition-colors font-bold">Cài đặt App (PWA)</a>
                <a href="#" class="hover:text-ue-brand transition-colors">Điều khoản</a>
                <a href="#" class="hover:text-ue-brand transition-colors">Bảo mật</a>
                <a href="#" class="hover:text-ue-brand transition-colors">Tiêu chuẩn cộng đồng</a>
            </div>
        </div>
    </footer>

    @livewireScripts
</body>
</html>
