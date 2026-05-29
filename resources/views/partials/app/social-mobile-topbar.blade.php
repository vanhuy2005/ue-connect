{{--
    Social Shell — Mobile Topbar (Threads-style)
    Source: docs/04-design/18-responsive-rules.md

    Visible ONLY on mobile (lg:hidden).
    Desktop navigation is handled by the sidebar.

    Layout:
        [≡ Xem thêm]  [UEConnect Logo]  [🔍 Tìm kiếm]
        left           center             right
--}}

<header
    class="lg:hidden ue-shell__topbar relative px-4 bg-white border-b border-slate-100 flex items-center justify-between"
    role="banner"
    x-data="{ moreMenuOpen: false }"
>
    {{-- Left: Hamburger / "Xem thêm" --}}
    <button
        type="button"
        @click="moreMenuOpen = !moreMenuOpen"
        class="flex items-center justify-center w-10 h-10 rounded-full text-slate-500 hover:bg-slate-50 transition-colors focus:outline-none"
        aria-label="Xem thêm tùy chọn"
        :aria-expanded="moreMenuOpen.toString()"
    >
        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <line x1="3" y1="6"  x2="21" y2="6"  />
            <line x1="3" y1="12" x2="21" y2="12" />
            <line x1="3" y1="18" x2="21" y2="18" />
        </svg>
    </button>

    {{-- Center: Logo mark --}}
    <a
        href="{{ route('dashboard') }}"
        class="absolute left-1/2 -translate-x-1/2 flex items-center justify-center focus:outline-none"
        aria-label="UEConnect — Trang chủ"
    >
        <x-brand.logo variant="mark" size="sm" />
    </a>

    {{-- Right: Search --}}
    <a
        href="{{ route('discovery.index') }}"
        class="flex items-center justify-center w-10 h-10 rounded-full text-slate-500 hover:bg-slate-50 transition-colors focus:outline-none"
        aria-label="Tìm kiếm"
    >
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <circle cx="11" cy="11" r="8" />
            <line x1="21" y1="21" x2="16.65" y2="16.65" />
        </svg>
    </a>

    {{-- "Xem thêm" slide-down menu (Alpine) --}}
    <div
        x-show="moreMenuOpen"
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 -translate-y-2"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 -translate-y-2"
        @click.outside="moreMenuOpen = false"
        @keydown.escape.window="moreMenuOpen = false"
        class="absolute top-full left-0 right-0 bg-white border-b border-slate-100 shadow-md z-40 py-2 px-4"
        role="menu"
        aria-label="Menu thêm"
        style="display: none;"
    >
        <a
            href="{{ route('profile') }}"
            class="flex items-center gap-3 px-2 py-3 text-sm font-semibold text-slate-700 hover:text-ue-brand transition-colors"
            role="menuitem"
        >
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            Hồ sơ của tôi
        </a>
        <a
            href="{{ route('profile.setup') }}"
            class="flex items-center gap-3 px-2 py-3 text-sm font-semibold text-slate-700 hover:text-ue-brand transition-colors"
            role="menuitem"
        >
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
            Chỉnh sửa hồ sơ
        </a>
        <div class="border-t border-slate-100 my-1"></div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button
                type="submit"
                class="w-full flex items-center gap-3 px-2 py-3 text-sm font-semibold text-red-600 hover:text-red-700 transition-colors text-left"
                role="menuitem"
            >
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                Đăng xuất
            </button>
        </form>
    </div>
</header>
