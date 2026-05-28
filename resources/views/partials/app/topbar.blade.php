{{--
    App Topbar Partial
    Source: docs/04-design/18-responsive-rules.md, 19-design-token-documentation.md §16

    Shell topbar — sticky, neutral surface, border-bottom.
    Contains: Mobile brand mark, Desktop search, Notifications, and Alpine.js Unified Profile Dropdown.
--}}

@php
    $currentUser = auth()->user();
    $isAdmin = $currentUser && ($currentUser->can('review_verification') || $currentUser->can('manage_reports'));
@endphp

<header
    class="ue-shell__topbar px-4 lg:px-6 bg-white border-b border-slate-150 h-16 flex items-center justify-between sticky top-0 z-30"
    role="banner"
    x-data="{ userMenuOpen: false }"
>
    {{-- Left Section --}}
    <div class="flex items-center gap-3">
        {{-- Mobile only: brand logo mark --}}
        <a href="{{ route('dashboard') }}" class="lg:hidden flex items-center ue-focus-ring rounded-lg" aria-label="UEConnect - Trang chủ">
            <x-brand.logo variant="mark" size="sm" />
        </a>
        
        {{-- Desktop: left side spacer (no duplicate logo since sidebar has it) --}}
        <div class="hidden lg:block w-4"></div>
    </div>

    {{-- Center Section: Desktop Search --}}
    <div class="hidden md:flex flex-1 max-w-md mx-4 lg:mx-8">
        <div class="relative w-full">
            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                <x-ui.icon name="search" size="sm" aria-hidden="true" />
            </div>
            <input
                type="search"
                placeholder="Tìm UEers, cộng đồng, mentor..."
                class="w-full h-10 pl-9 pr-4 text-xs font-medium rounded-xl border-slate-200 focus:border-ue-brand focus:ring-ue-brand-soft bg-slate-50/50"
                aria-label="Tìm kiếm"
            />
        </div>
    </div>

    {{-- Right Section: Actions + Unified User Menu --}}
    <div class="flex items-center gap-2">
        {{-- Mobile search trigger --}}
        <x-ui.icon-button
            icon="search"
            label="Mở tìm kiếm"
            variant="ghost"
            class="md:hidden"
            size="sm"
        />

        {{-- Notifications --}}
        <x-ui.icon-button
            icon="bell"
            label="Thông báo"
            variant="ghost"
            size="sm"
        />

        {{-- Avatar & Unified Menu via Alpine.js --}}
        @if ($currentUser)
            <div class="relative ml-1">
                <button
                    type="button"
                    @click="userMenuOpen = !userMenuOpen"
                    @click.away="userMenuOpen = false"
                    class="flex items-center gap-1.5 focus:outline-none ue-focus-ring rounded-full p-0.5 hover:bg-slate-50 transition-colors"
                    aria-haspopup="true"
                    :aria-expanded="userMenuOpen"
                    aria-label="Menu tài khoản"
                >
                    <div class="w-8 h-8 rounded-full bg-ue-brand-soft border border-slate-100 flex items-center justify-center font-bold text-ue-brand text-xs shadow-xs select-none flex-shrink-0">
                        {{ mb_substr($currentUser->name, 0, 2) }}
                    </div>
                    <x-ui.icon name="chevron-down" size="xs" class="text-slate-400 hidden sm:block transition-transform duration-150" ::class="userMenuOpen ? 'rotate-180' : ''" />
                </button>

                {{-- Dropdown --}}
                <div
                    x-show="userMenuOpen"
                    x-transition:enter="transition ease-out duration-100"
                    x-transition:enter-start="transform opacity-0 scale-95"
                    x-transition:enter-end="transform opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-75"
                    x-transition:leave-start="transform opacity-100 scale-100"
                    x-transition:leave-end="transform opacity-0 scale-95"
                    class="absolute right-0 mt-2 w-48 rounded-xl bg-white border border-slate-150 shadow-lg py-1.5 z-40 text-xs font-semibold text-slate-700"
                    style="display: none;"
                >
                    {{-- User Identity Summary --}}
                    <div class="px-4 py-2 border-b border-slate-100">
                        <p class="text-slate-800 font-bold truncate">{{ $currentUser->name }}</p>
                        <p class="text-[10px] text-slate-400 font-medium truncate">{{ $currentUser->email }}</p>
                    </div>

                    {{-- Core Links --}}
                    <a href="{{ route('profile') }}" class="flex items-center gap-2 px-4 py-2 hover:bg-slate-50 hover:text-ue-brand transition-colors">
                        <x-ui.icon name="user" size="xs" class="text-slate-400" />
                        <span>Hồ sơ</span>
                    </a>
                    
                    <a href="{{ route('posts.saved') }}" class="flex items-center gap-2 px-4 py-2 hover:bg-slate-50 hover:text-ue-brand transition-colors">
                        <x-ui.icon name="bookmark" size="xs" class="text-slate-400" />
                        <span>Bài viết đã lưu</span>
                    </a>

                    <a href="{{ route('profile') }}" class="flex items-center gap-2 px-4 py-2 hover:bg-slate-50 hover:text-ue-brand transition-colors">
                        <x-ui.icon name="shield" size="xs" class="text-slate-400" />
                        <span>Cài đặt tài khoản</span>
                    </a>

                    {{-- Admin Links --}}
                    @if ($isAdmin)
                        <div class="border-t border-slate-100 my-1"></div>
                        <p class="px-4 py-1 text-[9px] font-bold text-slate-400 uppercase tracking-wider">Quản lý hệ thống</p>
                        
                        <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2 px-4 py-2 hover:bg-slate-50 hover:text-ue-brand transition-colors">
                            <x-ui.icon name="grid" size="xs" class="text-slate-400" />
                            <span>Tổng quan quản trị</span>
                        </a>

                        @can('review_verification')
                            <a href="{{ route('admin.verifications.queue') }}" class="flex items-center gap-2 px-4 py-2 hover:bg-slate-50 hover:text-ue-brand transition-colors">
                                <x-ui.icon name="shield-check" size="xs" class="text-slate-400" />
                                <span>Duyệt xác thực</span>
                            </a>
                        @endcan

                        @can('manage_reports')
                            <a href="{{ route('admin.reports.index') }}" class="flex items-center gap-2 px-4 py-2 hover:bg-slate-50 hover:text-ue-brand transition-colors">
                                <x-ui.icon name="flag" size="xs" class="text-slate-400" />
                                <span>Báo cáo vi phạm</span>
                            </a>
                        @endcan
                    @endif

                    {{-- Logout --}}
                    <div class="border-t border-slate-100 my-1.5"></div>
                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <button
                            type="submit"
                            class="w-full text-left flex items-center gap-2 px-4 py-2 text-red-600 hover:bg-red-50 transition-colors"
                        >
                            <x-ui.icon name="log-out" size="xs" class="text-red-400" />
                            <span>Đăng xuất</span>
                        </button>
                    </form>
                </div>
            </div>
        @endif
    </div>
</header>
