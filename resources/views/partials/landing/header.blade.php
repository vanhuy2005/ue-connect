<header class="sticky top-0 z-[var(--z-header,50)] bg-white/95 backdrop-blur-sm border-b border-ue-border" role="banner">
    <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between gap-4" aria-label="Điều hướng chính">

        {{-- Logo --}}
        <a href="{{ route('landing') }}" aria-label="Trang chủ UEConnect" class="flex-shrink-0">
            <x-brand.logo variant="horizontal" size="md" />
        </a>

        {{-- Desktop Nav --}}
        <div class="hidden lg:flex items-center gap-1" role="list">
            @foreach([
                ['#hero',              'Giới thiệu'],
                ['#features',          'Tính năng'],
                ['#community-preview', 'Cộng đồng'],
                ['#mentor-teaser',     'Mentor'],
                ['#safety',            'An toàn'],
            ] as [$href, $label])
                <a
                    href="{{ $href }}"
                    role="listitem"
                    class="px-3 py-2 rounded-lg text-sm font-medium text-ue-text-secondary hover:text-ue-text hover:bg-ue-surface-hover transition-colors"
                >{{ $label }}</a>
            @endforeach
        </div>

        {{-- Actions --}}
        <div class="flex items-center gap-2 flex-shrink-0">
            @if($isAuthenticated)
                <x-ui.button
                    href="{{ $dashboardRoute }}"
                    size="sm"
                    icon="home"
                >
                    Vào UEConnect
                </x-ui.button>
            @else
                <x-ui.button
                    href="{{ route('login') }}"
                    variant="ghost"
                    size="sm"
                    class="hidden sm:inline-flex"
                >
                    Đăng nhập
                </x-ui.button>
                <x-ui.button
                    href="{{ route('register') }}"
                    size="sm"
                >
                    Bắt đầu
                </x-ui.button>
            @endif
        </div>

    </nav>
</header>
