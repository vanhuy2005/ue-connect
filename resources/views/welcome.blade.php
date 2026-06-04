@php
    $isAuthenticated = auth()->check();
    $dashboardRoute = route('dashboard');
@endphp
<!DOCTYPE html>
<html lang="vi" class="scroll-smooth min-h-screen">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#124874">

    <title>UEConnect — Kết nối cộng đồng HCMUE</title>
    <meta name="description" content="UEConnect là mạng xã hội học tập và kết nối dành riêng cho sinh viên, giảng viên và cựu sinh viên HCMUE.">

    {{-- Open Graph --}}
    <meta property="og:title" content="UEConnect — Kết nối cộng đồng HCMUE">
    <meta property="og:description" content="Kết nối sinh viên, cựu sinh viên và giảng viên HCMUE trong môi trường an toàn, xác thực.">
    <meta property="og:type" content="website">

    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600;700&display=swap" rel="stylesheet">

    {{-- Favicon --}}
    <link rel="icon" type="image/png" href="{{ asset('images/brand/ueconnect-mark-nobg.png') }}">

    {{-- Assets --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body x-data="{
    activeSlide: 0,
    totalSlides: 5,
    autoplay: true,
    interval: null,
    startAutoplay() {
        if (this.autoplay) {
            this.interval = setInterval(() => {
                this.next();
            }, 6000);
        }
    },
    stopAutoplay() {
        if (this.interval) {
            clearInterval(this.interval);
        }
    },
    next() {
        this.activeSlide = (this.activeSlide + 1) % this.totalSlides;
    },
    prev() {
        this.activeSlide = (this.activeSlide - 1 + this.totalSlides) % this.totalSlides;
    }
}" x-init="startAutoplay()" @keydown.right.window="next(); stopAutoplay()" @keydown.left.window="prev(); stopAutoplay()" class="font-sans antialiased min-h-screen flex flex-col justify-between bg-slate-50 text-slate-900">

    {{-- Skip to content (accessibility) --}}
    <a href="#main-content" class="skip-link sr-only focus:not-sr-only focus:fixed focus:top-4 focus:left-4 focus:z-50 focus:px-4 focus:py-2 focus:bg-white focus:text-ue-brand focus:rounded-lg focus:text-sm focus:font-semibold">
        Bỏ qua và đến nội dung chính
    </a>

    {{-- ============================================================ --}}
    {{-- HEADER --}}
    {{-- ============================================================ --}}
    <header class="bg-white/85 backdrop-blur-md border-b border-slate-200/80 flex-shrink-0 sticky top-0 z-50 animate-fade-in" role="banner">
        <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between gap-4" aria-label="Điều hướng chính">
            <a href="{{ route('landing') }}" aria-label="Trang chủ UEConnect" class="flex-shrink-0">
                <x-brand.logo variant="horizontal" size="md" />
            </a>

            {{-- Nav menu (Desktop-only, switches slides of the carousel) --}}
            <div class="hidden lg:flex items-center gap-1" role="list">
                @foreach([
                    [0, 'Giới thiệu'],
                    [1, 'Cộng đồng'],
                    [2, 'Mentor'],
                    [3, 'Alumni'],
                    [4, 'An toàn']
                ] as [$slide, $label])
                    <button
                        @click="activeSlide = {{ $slide }}; stopAutoplay()"
                        role="listitem"
                        class="px-3.5 py-1.5 rounded-lg text-xs font-semibold tracking-tight transition-colors"
                        :class="activeSlide === {{ $slide }} ? 'text-ue-brand bg-ue-brand-soft' : 'text-slate-600 hover:text-slate-950 hover:bg-slate-100'"
                    >{{ $label }}</button>
                @endforeach
            </div>

            {{-- Actions --}}
            <div class="flex items-center gap-2 flex-shrink-0">
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
    {{-- MAIN HERO & CAROUSEL VIEWPORT CONTAINER --}}
    {{-- ============================================================ --}}
    <main id="main-content" tabindex="-1" class="flex-1 flex items-center relative lg:overflow-hidden overflow-y-auto py-12 lg:py-0">
        {{-- Background texture --}}
        <div class="absolute inset-0 pointer-events-none"
             style="background-image: radial-gradient(circle at 1px 1px, rgba(10, 36, 63, 0.15) 1px, transparent 0); background-size: 28px 28px;"
             aria-hidden="true">
        </div>

        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center">
            
            {{-- 1. Left side: Copy (Hero info) --}}
            <div class="lg:col-span-5 flex flex-col justify-center text-left">
                <div class="flex flex-wrap gap-2 mb-4" aria-label="Đặc điểm">
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-slate-100 text-slate-700 text-[10px] font-semibold border border-slate-200">
                        <x-ui.icon name="check-circle" size="xxs" class="text-ue-brand" />
                        Chỉ dành cho HCMUE
                    </span>
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-slate-100 text-slate-700 text-[10px] font-semibold border border-slate-200">
                        <x-ui.icon name="check-circle" size="xxs" class="text-ue-brand" />
                        Xác thực danh tính
                    </span>
                </div>

                <h1 id="hero-heading" class="text-3xl sm:text-4xl lg:text-5xl font-black text-slate-900 leading-[1.1] tracking-tight mb-4">
                    Kết nối đúng người.<br>
                    Đúng cộng đồng.<br>
                    <span class="text-ue-brand">Đúng hành trình.</span>
                </h1>

                <p class="text-sm sm:text-base text-slate-600 max-w-lg mb-6 leading-relaxed">
                    UEConnect là mạng xã hội và không gian kết nối học thuật an toàn dành riêng cho sinh viên, giảng viên và cựu sinh viên Đại học Sư phạm TP.HCM.
                </p>

                <div class="flex flex-col sm:flex-row gap-3">
                    @if($isAuthenticated)
                        <x-ui.button
                            href="{{ $dashboardRoute }}"
                            variant="primary"
                            size="lg"
                            icon="arrow-right"
                            icon-position="right"
                            class="font-bold w-full sm:w-auto text-sm"
                        >
                            Vào Bảng điều khiển
                        </x-ui.button>
                    @else
                        <x-ui.button
                            href="{{ route('login') }}"
                            variant="primary"
                            size="lg"
                            icon="microsoft"
                            class="font-bold w-full sm:w-auto text-sm"
                        >
                            Đăng nhập bằng Entra ID (Office 365)
                        </x-ui.button>
                        <x-ui.button
                            href="{{ route('register') }}"
                            variant="secondary"
                            size="lg"
                            icon="user-plus"
                            class="border border-slate-300 text-slate-700 bg-white hover:bg-slate-50 w-full sm:w-auto font-bold text-sm"
                        >
                            Tham gia ngay
                        </x-ui.button>
                    @endif
                </div>

                {{-- Compact Stats --}}
                <div class="flex flex-wrap gap-x-5 gap-y-2 mt-8 pt-6 border-t border-slate-200 text-xs text-slate-500">
                    <div class="flex items-center gap-1.5">
                        <x-ui.icon name="check-circle" size="xs" class="text-ue-brand" />
                        <span>2.4K+ UEers đã tham gia</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <x-ui.icon name="check-circle" size="xs" class="text-ue-brand" />
                        <span>80+ Cộng đồng CLB</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <x-ui.icon name="check-circle" size="xs" class="text-ue-brand" />
                        <span>120+ Mentor & Alumni</span>
                    </div>
                </div>
            </div>

            {{-- 2. Right side: Interactive Carousel Card (Col span 7) --}}
            <div class="lg:col-span-7 flex justify-center lg:justify-end" @mouseenter="stopAutoplay()" @mouseleave="startAutoplay()">
                <div class="bg-white text-slate-800 rounded-3xl border border-slate-200 shadow-2xl p-6 sm:p-8 flex flex-col justify-between w-full max-w-[580px] min-h-[380px] sm:min-h-[410px] relative transition-all duration-300">
                    
                    {{-- Slide 0: General Introduction & Features --}}
                    <div x-show="activeSlide === 0" x-transition:enter="transition ease-out duration-300 transform" x-transition:enter-start="opacity-0 translate-x-4" x-transition:enter-end="opacity-100 translate-x-0" class="flex-1 flex flex-col justify-between">
                        <div>
                            <span class="inline-flex px-2.5 py-0.5 rounded-md bg-slate-100 text-slate-700 text-[10px] font-bold uppercase tracking-wider select-none">
                                Tính năng chính
                            </span>
                            <h2 class="text-xl sm:text-2xl font-bold text-slate-850 tracking-tight mt-3 mb-2">Không chỉ là mạng xã hội sinh viên</h2>
                            <p class="text-xs sm:text-sm text-slate-500 leading-relaxed mb-4">
                                UEConnect được xây dựng để tạo ra một không gian kết nối học thuật chuyên sâu và chia sẻ cơ hội lành mạnh.
                            </p>
                            <ul class="space-y-3">
                                <li class="flex items-start gap-3 text-xs text-slate-700">
                                    <div class="w-5 h-5 rounded-full bg-ue-brand-soft flex items-center justify-center flex-shrink-0 text-ue-brand mt-0.5">
                                        <x-ui.icon name="file-text" size="xs" />
                                    </div>
                                    <div>
                                        <span class="font-bold">Bảng tin & Chia sẻ tài liệu:</span>
                                        <span class="text-slate-500 font-normal"> Chia sẻ slide bài học, tài liệu và hoạt động thường nhật.</span>
                                    </div>
                                </li>
                                <li class="flex items-start gap-3 text-xs text-slate-700">
                                    <div class="w-5 h-5 rounded-full bg-ue-brand-soft flex items-center justify-center flex-shrink-0 text-ue-brand mt-0.5">
                                        <x-ui.icon name="message" size="xs" />
                                    </div>
                                    <div>
                                        <span class="font-bold">Trò chuyện bảo mật:</span>
                                        <span class="text-slate-500 font-normal"> Trò chuyện 1-1 riêng tư, văn minh sau khi được kết nối an toàn.</span>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>

                    {{-- Slide 1: Student Communities & Clubs --}}
                    <div x-show="activeSlide === 1" x-transition:enter="transition ease-out duration-300 transform" x-transition:enter-start="opacity-0 translate-x-4" x-transition:enter-end="opacity-100 translate-x-0" class="flex-1 flex flex-col justify-between" style="display: none;">
                        <div>
                            <span class="inline-flex px-2.5 py-0.5 rounded-md bg-blue-50 text-blue-700 border border-blue-100 text-[10px] font-bold uppercase tracking-wider select-none">
                                Cộng đồng học tập
                            </span>
                            <h2 class="text-xl sm:text-2xl font-bold text-slate-850 tracking-tight mt-3 mb-2">Thảo luận môn học và hoạt động CLB</h2>
                            <p class="text-xs sm:text-sm text-slate-500 leading-relaxed mb-4">
                                Tham gia các nhóm học thuật theo lớp/ngành để trao đổi đề tài, tài liệu học và cập nhật sự kiện ngoại khóa.
                            </p>
                            <ul class="space-y-3">
                                <li class="flex items-start gap-3 text-xs text-slate-700">
                                    <div class="w-5 h-5 rounded-full bg-blue-50 text-blue-700 flex items-center justify-center flex-shrink-0 mt-0.5">
                                        <x-ui.icon name="community" size="xs" />
                                    </div>
                                    <div>
                                        <span class="font-bold">Nhóm học tập chuyên ngành:</span>
                                        <span class="text-slate-500 font-normal"> Nơi trao đổi học thuật, slide bài giảng, bài tập lớn.</span>
                                    </div>
                                </li>
                                <li class="flex items-start gap-3 text-xs text-slate-700">
                                    <div class="w-5 h-5 rounded-full bg-blue-50 text-blue-700 flex items-center justify-center flex-shrink-0 mt-0.5">
                                        <x-ui.icon name="users" size="xs" />
                                    </div>
                                    <div>
                                        <span class="font-bold">Câu lạc bộ & Đội nhóm:</span>
                                        <span class="text-slate-500 font-normal"> Hoạt động ngoại khoá, Đoàn - Hội trực thuộc trường.</span>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>

                    {{-- Slide 2: Mentoring and Cố vấn học tập --}}
                    <div x-show="activeSlide === 2" x-transition:enter="transition ease-out duration-300 transform" x-transition:enter-start="opacity-0 translate-x-4" x-transition:enter-end="opacity-100 translate-x-0" class="flex-1 flex flex-col justify-between" style="display: none;">
                        <div>
                            <span class="inline-flex px-2.5 py-0.5 rounded-md bg-purple-50 text-purple-700 border border-purple-100 text-[10px] font-bold uppercase tracking-wider select-none">
                                Chương trình Mentor
                            </span>
                            <h2 class="text-xl sm:text-2xl font-bold text-slate-850 tracking-tight mt-3 mb-2">Định hướng nghiên cứu & học thuật</h2>
                            <p class="text-xs sm:text-sm text-slate-500 leading-relaxed mb-4">
                                Tìm kiếm sự hỗ trợ trực tiếp 1-1 từ giảng viên, cố vấn học tập có kinh nghiệm trong các đề tài nghiên cứu khoa học.
                            </p>
                            <ul class="space-y-3">
                                <li class="flex items-start gap-3 text-xs text-slate-700">
                                    <div class="w-5 h-5 rounded-full bg-purple-50 text-purple-700 flex items-center justify-center flex-shrink-0 mt-0.5">
                                        <x-ui.icon name="graduation-cap" size="xs" />
                                    </div>
                                    <div>
                                        <span class="font-bold">Giảng viên & Cố vấn chuyên môn:</span>
                                        <span class="text-slate-500 font-normal"> Định hướng nghiên cứu khoa học, phương pháp dạy học.</span>
                                    </div>
                                </li>
                                <li class="flex items-start gap-3 text-xs text-slate-700">
                                    <div class="w-5 h-5 rounded-full bg-purple-50 text-purple-700 flex items-center justify-center flex-shrink-0 mt-0.5">
                                        <x-ui.icon name="star" size="xs" />
                                    </div>
                                    <div>
                                        <span class="font-bold">Đề xuất 1-1 an toàn:</span>
                                        <span class="text-slate-500 font-normal"> Trò chuyện và trao đổi kế hoạch phát triển kỹ năng sư phạm.</span>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>

                    {{-- Slide 3: Alumni (Cựu sinh viên kết nối) --}}
                    <div x-show="activeSlide === 3" x-transition:enter="transition ease-out duration-300 transform" x-transition:enter-start="opacity-0 translate-x-4" x-transition:enter-end="opacity-100 translate-x-0" class="flex-1 flex flex-col justify-between" style="display: none;">
                        <div>
                            <span class="inline-flex px-2.5 py-0.5 rounded-md bg-amber-50 text-amber-700 border border-amber-100 text-[10px] font-bold uppercase tracking-wider select-none">
                                Mạng lưới Alumni
                            </span>
                            <h2 class="text-xl sm:text-2xl font-bold text-slate-850 tracking-tight mt-3 mb-2">Học hỏi kinh nghiệm từ các thế hệ đi trước</h2>
                            <p class="text-xs sm:text-sm text-slate-500 leading-relaxed mb-4">
                                Nhận tư vấn thực tập sư phạm, việc làm bán thời gian và cơ hội phát triển từ các cựu sinh viên K42, K44 thành đạt.
                            </p>
                            <ul class="space-y-3">
                                <li class="flex items-start gap-3 text-xs text-slate-700">
                                    <div class="w-5 h-5 rounded-full bg-amber-50 text-amber-700 flex items-center justify-center flex-shrink-0 mt-0.5">
                                        <x-ui.icon name="users" size="xs" />
                                    </div>
                                    <div>
                                        <span class="font-bold">Thực tế học tập & giảng dạy:</span>
                                        <span class="text-slate-500 font-normal"> Kinh nghiệm giảng dạy thực tế từ cựu sinh viên đi trước.</span>
                                    </div>
                                </li>
                                <li class="flex items-start gap-3 text-xs text-slate-700">
                                    <div class="w-5 h-5 rounded-full bg-amber-50 text-amber-700 flex items-center justify-center flex-shrink-0 mt-0.5">
                                        <x-ui.icon name="search" size="xs" />
                                    </div>
                                    <div>
                                        <span class="font-bold">Khám phá cơ hội thực tập:</span>
                                        <span class="text-slate-500 font-normal"> Việc làm, thực tập và các học bổng hỗ trợ học tập thiết thực.</span>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>

                    {{-- Slide 4: Verification & Safe Environment --}}
                    <div x-show="activeSlide === 4" x-transition:enter="transition ease-out duration-300 transform" x-transition:enter-start="opacity-0 translate-x-4" x-transition:enter-end="opacity-100 translate-x-0" class="flex-1 flex flex-col justify-between" style="display: none;">
                        <div>
                            <span class="inline-flex px-2.5 py-0.5 rounded-md bg-emerald-50 text-emerald-700 border border-emerald-100 text-[10px] font-bold uppercase tracking-wider select-none">
                                Cam kết An toàn & Bảo mật
                            </span>
                            <h2 class="text-xl sm:text-2xl font-bold text-slate-850 tracking-tight mt-3 mb-2">Quyền riêng tư được bảo vệ tuyệt đối</h2>
                            <p class="text-xs sm:text-sm text-slate-500 leading-relaxed mb-4">
                                Tại UEConnect, sự an toàn và quyền riêng tư của bạn là ưu tiên hàng đầu. Chúng tôi thiết lập các quy chuẩn bảo mật nghiêm ngặt nhất.
                            </p>
                            <ul class="space-y-3">
                                <li class="flex items-start gap-3 text-xs text-slate-700">
                                    <div class="w-5 h-5 rounded-full bg-emerald-50 text-emerald-700 flex items-center justify-center flex-shrink-0 mt-0.5">
                                        <x-ui.icon name="shield" size="xs" />
                                    </div>
                                    <div>
                                        <span class="font-bold">Bảo mật thông tin xác thực:</span>
                                        <span class="text-slate-500 font-normal"> Tự động xóa vĩnh viễn thẻ minh chứng của bạn ngay sau khi admin duyệt.</span>
                                    </div>
                                </li>
                                <li class="flex items-start gap-3 text-xs text-slate-700">
                                    <div class="w-5 h-5 rounded-full bg-emerald-50 text-emerald-700 flex items-center justify-center flex-shrink-0 mt-0.5">
                                        <x-ui.icon name="eye-off" size="xs" />
                                    </div>
                                    <div>
                                        <span class="font-bold">Quyền riêng tư danh tính:</span>
                                        <span class="text-slate-500 font-normal"> Tùy chọn hoạt động dưới tên thật hoặc biệt danh ẩn danh an toàn.</span>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>

                    {{-- Carousel Navigation Controls --}}
                    <div class="mt-6 pt-4 border-t border-slate-100 flex items-center justify-between">
                        {{-- Autoplay Toggle --}}
                        <button @click="autoplay = !autoplay; autoplay ? startAutoplay() : stopAutoplay()" class="p-2 border border-slate-200 rounded-xl hover:bg-slate-50 text-slate-500 transition flex items-center justify-center" :aria-label="autoplay ? 'Tạm dừng tự động chuyển' : 'Bật tự động chuyển'">
                            <span x-show="autoplay" x-cloak>
                                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z"/></svg>
                            </span>
                            <span x-show="!autoplay">
                                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                            </span>
                        </button>

                        {{-- Indicators --}}
                        <div class="flex items-center gap-1.5">
                            @foreach([0, 1, 2, 3, 4] as $slide)
                                <button
                                    @click="activeSlide = {{ $slide }}; stopAutoplay()"
                                    class="transition-all duration-300"
                                    :class="activeSlide === {{ $slide }} ? 'w-6 h-2 rounded-full bg-ue-brand' : 'w-2 h-2 rounded-full bg-slate-200 hover:bg-slate-300'"
                                    aria-label="Đến slide {{ $slide + 1 }}"
                                ></button>
                            @endforeach
                        </div>

                        {{-- Next/Prev Buttons --}}
                        <div class="flex items-center gap-1.5">
                            <button @click="prev(); stopAutoplay()" class="p-2 border border-slate-200 rounded-xl hover:bg-slate-50 text-slate-600 transition flex items-center justify-center" aria-label="Slide trước">
                                <x-ui.icon name="arrow-left" size="xs" />
                            </button>
                            <button @click="next(); stopAutoplay()" class="p-2 border border-slate-200 rounded-xl hover:bg-slate-50 text-slate-600 transition flex items-center justify-center" aria-label="Slide tiếp">
                                <x-ui.icon name="arrow-right" size="xs" />
                            </button>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </main>

    {{-- ============================================================ --}}
    {{-- FOOTER --}}
    {{-- ============================================================ --}}
    <footer class="bg-white border-t border-slate-200 py-6 text-xs text-slate-500 font-medium flex-shrink-0" role="contentinfo">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row justify-between items-center gap-3">
            <div class="flex items-center gap-2">
                <x-brand.logo variant="mark" size="sm" class="opacity-80" />
                <span>© {{ date('Y') }} UEConnect. Phát triển bởi đội ngũ kỹ thuật HCMUE.</span>
            </div>
            <div class="flex gap-x-6 gap-y-1 flex-wrap">
                <a href="#" class="hover:text-ue-brand transition-colors">Điều khoản</a>
                <a href="#" class="hover:text-ue-brand transition-colors">Bảo mật</a>
                <a href="#" class="hover:text-ue-brand transition-colors">Tiêu chuẩn</a>
                <span class="text-slate-300">|</span>
                <span class="text-slate-400">Phiên bản 1.0.0</span>
            </div>
        </div>
    </footer>

    @livewireScripts
</body>
</html>
