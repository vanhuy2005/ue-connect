@php
    $currentUser = auth()->user();
    $adminRoles = ['admin'];
    $adminPermissions = [
        'view_admin_dashboard',
        'review_verification',
        'manage_reports',
        'manage_media',
    ];
    $isAdmin = $currentUser && (
        $currentUser->roles()->whereIn('name', $adminRoles)->exists()
        || $currentUser->permissions()->whereIn('name', $adminPermissions)->exists()
    );
@endphp

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
        class="absolute top-full left-0 right-0 bg-white border-b border-slate-100 shadow-lg z-40 py-2 px-4 max-h-[80vh] overflow-y-auto text-xs font-semibold text-slate-700"
        role="menu"
        aria-label="Menu thêm"
        style="display: none;"
    >
        {{-- User info summary --}}
        @if ($currentUser)
            <div class="px-2 py-2.5 border-b border-slate-100 mb-1.5">
                <p class="text-slate-800 font-bold text-xxs truncate">{{ $currentUser->name }}</p>
                <p class="text-[10px] text-slate-400 font-medium truncate mt-0.5">{{ $currentUser->email }}</p>
            </div>
        @endif

        {{-- Core Links --}}
        <a
            href="{{ route('profile') }}"
            class="flex items-center gap-3 px-2 py-2.5 hover:text-ue-brand transition-colors"
            role="menuitem"
        >
            <x-ui.icon name="user" size="sm" class="text-slate-400" />
            Hồ sơ của tôi
        </a>
        <a
            href="{{ route('profile.edit') }}"
            class="flex items-center gap-3 px-2 py-2.5 hover:text-ue-brand transition-colors"
            role="menuitem"
        >
            <x-ui.icon name="edit" size="sm" class="text-slate-400" />
            Chỉnh sửa hồ sơ
        </a>
        <a
            href="{{ route('community.index') }}"
            class="flex items-center gap-3 px-2 py-2.5 hover:text-ue-brand transition-colors"
            role="menuitem"
        >
            <x-ui.icon name="community" size="sm" class="text-slate-400" />
            Cộng đồng
        </a>
        <a
            href="{{ route('app.career-pathway.index') }}"
            class="flex items-center gap-3 px-2 py-2.5 hover:text-ue-brand transition-colors"
            role="menuitem"
        >
            <x-ui.icon name="map" size="sm" class="text-slate-400" />
            Bản đồ học tập
        </a>
        <a
            href="{{ route('mentor.discovery') }}"
            class="flex items-center gap-3 px-2 py-2.5 hover:text-ue-brand transition-colors"
            role="menuitem"
        >
            <x-ui.icon name="graduation-cap" size="sm" class="text-slate-400" />
            Mentor
        </a>
        <button
            type="button"
            class="w-full text-left flex items-center gap-3 px-2 py-2.5 hover:text-ue-brand transition-colors"
            role="menuitem"
        >
            <x-ui.icon name="eye" size="sm" class="text-slate-400" />
            Giao diện
        </button>
        <a
            href="{{ route('settings') }}"
            class="flex items-center gap-3 px-2 py-2.5 hover:text-ue-brand transition-colors"
            role="menuitem"
        >
            <x-ui.icon name="settings" size="sm" class="text-slate-400" />
            Cài đặt ứng dụng
        </a>
        <a
            href="{{ route('posts.saved') }}"
            class="flex items-center gap-3 px-2 py-2.5 hover:text-ue-brand transition-colors"
            role="menuitem"
        >
            <x-ui.icon name="bookmark" size="sm" class="text-slate-400" />
            Bài viết đã lưu
        </a>
        <a
            href="{{ route('settings', ['section' => 'support']) }}"
            class="flex items-center gap-3 px-2 py-2.5 hover:text-ue-brand transition-colors"
            role="menuitem"
        >
            <x-ui.icon name="help-circle" size="sm" class="text-slate-400" />
            Trung tâm hỗ trợ
        </a>
        <a
            href="{{ route('settings', ['section' => 'support']) }}"
            class="flex items-center gap-3 px-2 py-2.5 hover:text-ue-brand transition-colors"
            role="menuitem"
        >
            <x-ui.icon name="alert-triangle" size="sm" class="text-slate-400" />
            Báo cáo sự cố
        </a>

        {{-- Admin options --}}
        @if ($isAdmin)
            <div class="border-t border-slate-100 my-1.5"></div>
            <p class="px-2 py-1 text-[9px] font-bold text-slate-400 uppercase tracking-wider">Quản trị</p>
            
            <a
                href="{{ route('admin.dashboard') }}"
                class="flex items-center gap-3 px-2 py-2.5 hover:text-ue-brand transition-colors"
                role="menuitem"
            >
                <x-ui.icon name="shield" size="sm" class="text-slate-400" />
                Tổng quan quản trị
            </a>

            @can('review_verification')
                <a
                    href="{{ route('admin.verifications.queue') }}"
                    class="flex items-center gap-3 px-2 py-2.5 hover:text-ue-brand transition-colors"
                    role="menuitem"
                >
                    <x-ui.icon name="shield-check" size="sm" class="text-slate-400" />
                    Duyệt xác thực
                </a>
            @endcan

            @can('manage_reports')
                <a
                    href="{{ route('admin.reports.index') }}"
                    class="flex items-center gap-3 px-2 py-2.5 hover:text-ue-brand transition-colors"
                    role="menuitem"
                >
                    <x-ui.icon name="flag" size="sm" class="text-slate-400" />
                    Báo cáo vi phạm
                </a>
            @endcan
        @endif

        {{-- Logout --}}
        <div class="border-t border-slate-100 my-1.5"></div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button
                type="submit"
                class="w-full flex items-center gap-3 px-2 py-2.5 text-red-650 hover:text-red-700 hover:bg-red-50/60 rounded-lg transition-colors text-left"
                role="menuitem"
            >
                <x-ui.icon name="log-out" size="sm" class="text-red-500" />
                Đăng xuất
            </button>
        </form>
    </div>
</header>
