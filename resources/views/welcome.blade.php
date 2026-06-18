@php
    $isAuthenticated = auth()->check();
    $dashboardRoute = route('dashboard');
    
    $slides = [
        [
            'id' => 'hero',
            'nav_label' => 'Tổng quan',
            'is_hero' => true,
        ],
        [
            'id' => 'verification',
            'nav_label' => 'Xác thực',
            'eyebrow' => 'Identity Verification',
            'title' => 'Môi trường <span class="text-ue-brand">xác thực 100%</span>',
            'description' => 'Đảm bảo cộng đồng an toàn với quy trình xác minh qua công nghệ AI OCR nhận diện sinh viên. Nền tảng "Verified campus social platform" chính thức.',
            'image' => asset('images/brand/feature-verification.png'),
            'features' => [
                ['icon' => 'trash', 'text' => '<strong>Tự động xóa minh chứng:</strong> Dữ liệu thẻ sinh viên lập tức bị hủy sau khi phê duyệt để đảm bảo riêng tư.'],
                ['icon' => 'microsoft', 'text' => '<strong>Đăng nhập một chạm:</strong> Hỗ trợ xác thực SSO qua tài khoản email giáo dục Microsoft Entra ID.'],
                ['icon' => 'permissions', 'text' => '<strong>Cơ chế Role-based:</strong> Phân quyền chặt chẽ giữa Sinh viên, Giảng viên và Cựu sinh viên.']
            ]
        ],
        [
            'id' => 'social',
            'nav_label' => 'Cộng đồng',
            'eyebrow' => 'Social Layer',
            'title' => 'Giao lưu & <span class="text-ue-brand">Cộng đồng CLB</span>',
            'description' => 'Dễ dàng khám phá bạn bè cùng ngành qua tính năng Discovery. Tham gia các cộng đồng, câu lạc bộ và tham gia thảo luận các môn học.',
            'image' => asset('images/brand/feature-community.png'),
            'features' => [
                ['icon' => 'users', 'text' => '<strong>Mạng lưới Câu lạc bộ:</strong> Tạo hoặc tham gia không gian sinh hoạt chung của Đoàn - Hội và các CLB.'],
                ['icon' => 'message-square', 'text' => '<strong>Tin nhắn thời gian thực:</strong> Trò chuyện 1:1 siêu tốc qua hệ thống Laravel Reverb WebSocket.'],
                ['icon' => 'file-text', 'text' => '<strong>Chia sẻ tài nguyên học thuật:</strong> Đăng bài chia sẻ kỹ năng, slide bài giảng với CDN tốc độ cao.']
            ]
        ],
        [
            'id' => 'mentoring',
            'nav_label' => 'Mentoring',
            'eyebrow' => 'Career Pathway',
            'title' => 'Định hướng cùng <span class="text-ue-brand">Mentor & Alumni</span>',
            'description' => 'Xây dựng hành trình phát triển vững chắc thông qua việc nhận hướng dẫn từ các giảng viên, cố vấn chuyên môn và cựu sinh viên đi trước.',
            'image' => asset('images/brand/feature-mentor.png'),
            'features' => [
                ['icon' => 'mentor', 'text' => '<strong>Kết nối cố vấn chuyên môn:</strong> Định hướng tham gia các đề tài NCKH, phương pháp dạy học.'],
                ['icon' => 'book-open', 'text' => '<strong>Thực tế học tập & Giảng dạy:</strong> Học hỏi từ các sinh viên K42, K44 đang đứng lớp thực tế.'],
                ['icon' => 'briefcase', 'text' => '<strong>Khám phá cơ hội nghề nghiệp:</strong> Việc làm part-time, thông tin thực tập được xác thực rõ ràng.']
            ]
        ],
        [
            'id' => 'ai',
            'nav_label' => 'Chatbot AI',
            'eyebrow' => 'AI Assistant',
            'title' => 'Hỏi đáp học vụ với <span class="text-ue-brand">HCMUE Chatbot</span>',
            'description' => 'Trải nghiệm sức mạnh của Generative AI và kiến trúc RAG. Chatbot nắm vững quy chế, chuẩn đầu ra và sổ tay sinh viên của HCMUE.',
            'image' => asset('images/brand/feature-chatbot.png'),
            'features' => [
                ['icon' => 'message-circle', 'text' => '<strong>Giải đáp 24/7:</strong> Hỗ trợ sinh viên lập kế hoạch học tập, điều kiện ra trường và quy định đào tạo.'],
                ['icon' => 'database', 'text' => '<strong>Tri thức độc quyền:</strong> Liên tục trích xuất dữ liệu từ Qdrant Vector DB & CSDL SQL Server của trường.'],
                ['icon' => 'sparkles', 'text' => '<strong>Đa mô hình AI:</strong> Ứng dụng Gemini 2.5 Flash kết hợp Local LLM với cơ chế ngăn chặn ảo giác.']
            ]
        ]
    ];
@endphp
<!DOCTYPE html>
<html lang="vi" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
    <meta name="theme-color" content="#ffffff">

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
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    {{-- Favicon --}}
    <link rel="icon" type="image/png" href="{{ asset('images/brand/ueconnect-mark-nobg.png') }}">

    {{-- Realtime Meta Config --}}
    @include('partials.realtime-meta')

    {{-- Assets --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    
    <style>
        html { scroll-behavior: smooth; }

        body {
            overflow-x: clip;
            overflow-y: visible;
        }

        @supports not (overflow-x: clip) {
            body {
                overflow-x: hidden;
            }
        }
        
        .reveal-right {
            opacity: 0;
            transform: translateX(60px);
            transition: all 1s cubic-bezier(0.2, 0.8, 0.2, 1);
        }
        .reveal-left {
            opacity: 0;
            transform: translateX(-60px);
            transition: all 1s cubic-bezier(0.2, 0.8, 0.2, 1);
        }
        .reveal-up {
            opacity: 0;
            transform: translateY(60px);
            transition: all 1s cubic-bezier(0.2, 0.8, 0.2, 1);
        }
        .is-revealed {
            opacity: 1 !important;
            transform: translate(0, 0) !important;
        }
        .delay-100 { transition-delay: 100ms; }
        .delay-200 { transition-delay: 200ms; }
        .delay-300 { transition-delay: 300ms; }
    </style>
    <script>
        if (window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone) {
            document.documentElement.classList.add('is-standalone');
        }
    </script>
</head>
<body class="font-sans antialiased text-slate-900 selection:bg-ue-brand/20 selection:text-ue-brand bg-white" 
x-data="{
    init() {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-revealed');
                    // Không unobserve nếu muốn hiệu ứng lặp lại khi cuộn lên/xuống.
                    // Ở đây ta unobserve để hiệu ứng chỉ chạy 1 lần cho tự nhiên.
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.1, rootMargin: '0px 0px -50px 0px' });
        
        document.querySelectorAll('.reveal-right, .reveal-left, .reveal-up').forEach(el => observer.observe(el));
    }
}">

    {{-- Skip to content (accessibility) --}}
    <a href="#main-content" class="skip-link sr-only focus:not-sr-only focus:fixed focus:top-4 focus:left-4 focus:z-50 focus:px-4 focus:py-2 focus:bg-white focus:text-ue-brand focus:rounded-xl focus:shadow-lg focus:outline-none focus:ring-2 focus:ring-ue-brand focus:text-sm focus:font-semibold">
        Bỏ qua và đến nội dung chính
    </a>

    {{-- ============================================================ --}}
    {{-- HEADER --}}
    {{-- ============================================================ --}}
    <header class="fixed top-0 left-0 right-0 bg-white/80 backdrop-blur-xl border-b border-slate-200/60 z-50 transition-all duration-300" role="banner">
        <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-14 md:h-16 flex items-center justify-between gap-4" aria-label="Điều hướng chính">
            <a href="{{ route('landing') }}" aria-label="Trang chủ UEConnect" class="flex-shrink-0 focus:outline-none focus-visible:ring-2 focus-visible:ring-ue-brand rounded-lg">
                <x-brand.logo variant="horizontal" size="sm" class="md:hidden" />
                <x-brand.logo variant="horizontal" size="md" class="hidden md:block" />
            </a>

            {{-- Nav menu (Desktop-only) --}}
            <div class="hidden lg:flex items-center gap-1.5 p-1 bg-slate-100/50 rounded-xl border border-slate-200/50" role="list" 
                 x-data="{ activeSection: 'hero' }"
                 @scroll.window="
                    let current = 'hero';
                    document.querySelectorAll('section[id]').forEach(section => {
                        if (window.scrollY >= section.offsetTop - 200) {
                            current = section.getAttribute('id');
                        }
                    });
                    activeSection = current;
                 ">
                @foreach($slides as $slide)
                    <a href="#{{ $slide['id'] }}"
                        role="listitem"
                        class="px-4 py-1.5 rounded-lg text-xs font-semibold tracking-wide transition-all duration-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-ue-brand"
                        :class="activeSection === '{{ $slide['id'] }}' ? 'text-ue-brand bg-white shadow-sm ring-1 ring-slate-200/50' : 'text-slate-500 hover:text-slate-900 hover:bg-slate-200/50'"
                    >{{ $slide['nav_label'] }}</a>
                @endforeach
            </div>

            {{-- Actions --}}
            <div class="flex items-center gap-2 sm:gap-3 flex-shrink-0">
                <a href="{{ route('pwa.install') }}" class="hidden sm:inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-slate-900 px-3 py-2 rounded-lg hover:bg-slate-100 transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-ue-brand" title="Cài đặt PWA">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-ue-brand" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                    <span>Cài đặt App</span>
                </a>
                <a href="{{ route('pwa.install') }}" class="sm:hidden inline-flex items-center justify-center w-8 h-8 rounded-lg text-slate-500 hover:bg-slate-100 hover:text-ue-brand transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-ue-brand" aria-label="Cài đặt App">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                </a>

                <div class="h-4 w-px bg-slate-200 hidden sm:block"></div>

                @if($isAuthenticated)
                    <x-ui.button
                        href="{{ $dashboardRoute }}"
                        variant="primary"
                        size="sm"
                        class="font-bold text-xs shadow-sm hover:shadow"
                    >
                        Vào UEConnect
                    </x-ui.button>
                @else
                    <a href="{{ route('login') }}" class="text-xs font-bold text-slate-700 hover:text-ue-brand transition-colors px-2 py-2 focus:outline-none focus-visible:ring-2 focus-visible:ring-ue-brand rounded-lg">
                        Đăng nhập
                    </a>
                    <x-ui.button
                        href="{{ route('register') }}"
                        variant="primary"
                        size="sm"
                        class="font-bold text-xs shadow-sm hover:shadow"
                    >
                        Đăng ký
                    </x-ui.button>
                @endif
            </div>
        </nav>
    </header>

    {{-- ============================================================ --}}
    {{-- MAIN CONTENT (VERTICAL SCROLL) --}}
    {{-- ============================================================ --}}
    <main id="main-content" class="w-full flex flex-col focus:outline-none">
        
        @foreach($slides as $index => $slide)
            
            @if($slide['is_hero'] ?? false)
                {{-- ================================================= --}}
                {{-- SECTION: HERO --}}
                {{-- ================================================= --}}
                <section id="{{ $slide['id'] }}" class="w-full relative min-h-screen pt-24 md:pt-32 pb-16 lg:pb-0 flex items-center bg-white overflow-hidden">
                    <div class="max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-16 items-center">
                        
                        {{-- Left side: Copy --}}
                        <div class="lg:col-span-6 flex flex-col justify-center text-center md:text-left reveal-up">
                            
                            {{-- Mobile Eyebrow --}}
                            <div class="md:hidden mx-auto mb-4 inline-flex items-center px-3 py-1 bg-white/80 shadow-sm border border-slate-200/80 rounded-full">
                                <span class="text-[9px] font-bold uppercase tracking-[0.2em] text-slate-500">Thiết kế cho & Xây dựng bởi</span>
                            </div>

                            <h1 class="flex flex-col md:flex-row gap-2 md:gap-5 mb-5 md:mb-8 md:items-stretch w-full md:w-auto">
                                {{-- Desktop Labels --}}
                                <div class="hidden md:flex flex-col justify-start md:pr-4 lg:pr-5 text-right md:py-2 lg:py-3 xl:py-4 border-r-2 border-slate-200/60">
                                    <span class="block text-[1.1rem] lg:text-[1.3rem] xl:text-[1.5rem] font-black uppercase text-slate-400 tracking-tight leading-[1.2] whitespace-nowrap">
                                        THIẾT KẾ CHO
                                    </span>
                                    <span class="block text-[1.1rem] lg:text-[1.3rem] xl:text-[1.5rem] font-black uppercase text-slate-400 tracking-tight leading-[1.2] whitespace-nowrap">
                                        XÂY DỰNG BỞI
                                    </span>
                                </div>

                                {{-- Headline --}}
                                <div class="flex flex-col justify-center gap-y-0 sm:gap-y-1">
                                    <span class="block text-[12vw] sm:text-[4rem] lg:text-[4.5rem] xl:text-[5.5rem] font-black uppercase tracking-tighter leading-[0.9] whitespace-nowrap drop-shadow-sm">
                                        <span class="text-ue-brand">SINH</span> <span class="text-[#A61D37]">VIÊN</span>
                                    </span>
                                    <span class="block text-[12vw] sm:text-[4rem] lg:text-[4.5rem] xl:text-[5.5rem] font-black uppercase tracking-tighter leading-[0.9] whitespace-nowrap drop-shadow-sm">
                                        <span class="text-ue-brand">SƯ</span> <span class="text-[#A61D37]">PHẠM.</span>
                                    </span>
                                </div>
                            </h1>
                            
                            <p class="text-[15px] sm:text-base lg:text-lg text-slate-600/90 max-w-xl mx-auto md:mx-0 mb-8 lg:mb-10 leading-relaxed font-medium">
                                Mạng xã hội nội bộ dành riêng cho sinh viên, giảng viên và cựu sinh viên Đại học Sư phạm TP.HCM. Nền tảng kết nối học thuật, định hướng nghề nghiệp và tham gia cộng đồng an toàn.
                            </p>
                            
                            <div class="flex flex-col sm:flex-row gap-3 w-full sm:w-auto justify-center md:justify-start">
                                @if($isAuthenticated)
                                    <x-ui.button href="{{ $dashboardRoute }}" variant="primary" size="lg" icon="arrow-right" icon-position="right" class="font-bold w-full sm:w-auto text-sm sm:text-base shadow-md hover:shadow-lg focus:ring-offset-2">
                                        Vào Bảng điều khiển
                                    </x-ui.button>
                                @else
                                    <x-ui.button href="{{ route('login') }}" variant="primary" size="lg" icon="microsoft" class="font-bold w-full sm:w-auto text-sm sm:text-base shadow-md hover:shadow-lg focus:ring-offset-2 transition-all">
                                        Đăng nhập Entra ID
                                    </x-ui.button>
                                    <x-ui.button href="{{ route('register') }}" variant="secondary" size="lg" icon="user-plus" class="border border-slate-300 text-slate-700 bg-white hover:bg-slate-50 w-full sm:w-auto font-bold text-sm sm:text-base shadow-sm focus:ring-offset-2 transition-all">
                                        Tham gia ngay
                                    </x-ui.button>
                                @endif
                            </div>
                        </div>
                        
                        {{-- Right side: Hero Image --}}
                        <div class="lg:col-span-6 flex justify-center lg:justify-end relative mt-12 lg:mt-0 reveal-right delay-200">
                            <div class="relative w-full max-w-[320px] sm:max-w-[480px] lg:max-w-[640px] xl:max-w-[720px] aspect-[4/5] sm:aspect-auto">
                                {{-- Soft glow behind image --}}
                                <div class="absolute -inset-4 bg-gradient-to-tr from-blue-100 to-indigo-50 blur-2xl opacity-50 rounded-full"></div>
                                <img src="{{ asset('images/brand/hero-img-ue-connect.png') }}" alt="UEConnect Showcase" class="relative z-10 w-full h-full object-contain rounded-[2rem] drop-shadow-2xl hover:-translate-y-2 transition-transform duration-500 will-change-transform">
                            </div>
                        </div>

                    </div>
                </section>
                
            @else
                {{-- ================================================= --}}
                {{-- SECTION: FEATURE --}}
                {{-- ================================================= --}}
                <section id="{{ $slide['id'] }}" class="w-full py-20 md:py-32 bg-white overflow-hidden">
                    <div class="max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-16 items-center">
                        
                        {{-- Image --}}
                        <div class="lg:col-span-6 {{ $index % 2 === 0 ? 'lg:order-2 reveal-right' : 'reveal-left' }} flex justify-center w-full">
                            <div class="relative w-full max-w-sm sm:max-w-md lg:max-w-lg xl:max-w-xl">
                                <div class="absolute -inset-4 bg-slate-100/50 blur-2xl rounded-[3rem] -z-10"></div>
                                <img src="{{ $slide['image'] }}" onerror="this.src='https://placehold.co/800x600/F0F5FA/124874?text={{ urlencode($slide['nav_label']) }}'" alt="{{ strip_tags($slide['title']) }}" class="w-full h-auto rounded-2xl shadow-xl border border-slate-200/50 object-cover bg-white hover:-translate-y-1 hover:shadow-2xl transition-all duration-500">
                            </div>
                        </div>
                        
                        {{-- Text --}}
                        <div class="lg:col-span-6 {{ $index % 2 === 0 ? 'lg:order-1 lg:pr-8 reveal-left delay-100' : 'lg:pl-8 reveal-right delay-100' }}">
                            <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-ue-brand/10 text-ue-brand text-[10px] sm:text-xs font-bold uppercase tracking-wider mb-5">
                                @if($index === 1 || $index === 4)
                                    <span class="w-1.5 h-1.5 rounded-full bg-ue-brand animate-pulse"></span> 
                                @else
                                    <span class="w-1.5 h-1.5 rounded-full bg-ue-brand/80"></span> 
                                @endif
                                {{ $slide['eyebrow'] }}
                            </div>
                            
                            <h2 class="text-3xl sm:text-4xl lg:text-5xl font-black text-slate-900 leading-[1.15] mb-4 sm:mb-6 tracking-tight">
                                {!! $slide['title'] !!}
                            </h2>
                            
                            <p class="text-base sm:text-lg text-slate-600 mb-8 leading-relaxed font-medium">
                                {{ $slide['description'] }}
                            </p>
                            
                            <ul class="space-y-4 sm:space-y-5">
                                @foreach($slide['features'] as $featureIndex => $feature)
                                <li class="flex items-start gap-4 p-3 -ml-3 rounded-xl hover:bg-slate-50 transition-colors {{ $index % 2 === 0 ? 'reveal-left' : 'reveal-right' }}" style="transition-delay: {{ 200 + ($featureIndex * 100) }}ms">
                                    <div class="bg-white shadow-sm border border-slate-100 p-2 rounded-lg flex-shrink-0 text-ue-brand mt-0.5 group-hover:scale-110 transition-transform">
                                        <x-ui.icon name="{{ $feature['icon'] }}" class="w-5 h-5 sm:w-6 sm:h-6" />
                                    </div>
                                    <p class="text-sm sm:text-base text-slate-700 leading-relaxed pt-1">
                                        {!! $feature['text'] !!}
                                    </p>
                                </li>
                                @endforeach
                            </ul>
                        </div>

                    </div>
                </section>
            @endif

        @endforeach

        @include('partials.landing.cta-final')

    </main>

    {{-- ============================================================ --}}
    {{-- FOOTER --}}
    {{-- ============================================================ --}}
    <footer class="bg-white border-t border-slate-200/80 py-8 sm:py-10 text-sm text-slate-500 font-medium z-40 relative reveal-up" role="contentinfo">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col md:flex-row justify-between items-center gap-6">
            <div class="flex flex-col md:flex-row items-center gap-4">
                <x-brand.logo variant="mark" size="sm" class="opacity-80 grayscale hover:grayscale-0 transition-all duration-300" />
                <span class="tracking-wide">© {{ date('Y') }} UEConnect. <span class="hidden sm:inline">Bảo lưu mọi quyền.</span></span>
            </div>
            <div class="flex gap-x-6 gap-y-3 flex-wrap justify-center">
                <a href="{{ route('pwa.install') }}" class="hover:text-ue-brand transition-colors font-semibold sm:hidden">Cài đặt App</a>
                <a href="{{ route('terms') }}" wire:navigate class="hover:text-slate-900 transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-ue-brand rounded">Điều khoản</a>
                <a href="{{ route('privacy') }}" wire:navigate class="hover:text-slate-900 transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-ue-brand rounded">Bảo mật & Quyền riêng tư</a>
                <a href="{{ route('community-standards') }}" wire:navigate class="hover:text-slate-900 transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-ue-brand rounded">Tiêu chuẩn cộng đồng</a>
            </div>
        </div>
    </footer>

    @livewireScripts
</body>
</html>
