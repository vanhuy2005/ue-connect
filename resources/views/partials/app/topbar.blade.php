{{--
    App Topbar Partial
    Source: docs/04-design/18-responsive-rules.md, 19-design-token-documentation.md §16

    Shell topbar — sticky, neutral surface, border-bottom.
    Contains: logo, search placeholder, notification placeholder, avatar.

    NOTE: Route links use '#' placeholders for unimplemented routes.
    TODO: Replace href="#" with route() helpers once routes exist.
--}}

<header
    class="ue-shell__topbar px-4 lg:px-6"
    role="banner"
>
    {{-- Logo (mobile: mark, desktop: horizontal) --}}
    <div class="flex items-center gap-3 flex-shrink-0">
        <a href="/" class="flex items-center ue-focus-ring rounded-lg" aria-label="UEConnect - Trang chủ">
            {{-- Mobile: show mark --}}
            <span class="lg:hidden">
                <x-brand.logo variant="mark" size="sm" />
            </span>
            {{-- Desktop: show horizontal --}}
            <span class="hidden lg:block">
                <x-brand.logo variant="horizontal" size="md" />
            </span>
        </a>
    </div>

    {{-- Search bar (desktop) --}}
    {{-- TODO: Wire up to real search route when implemented --}}
    <div class="hidden md:flex flex-1 max-w-md mx-4 lg:mx-8">
        <div class="relative w-full">
            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-ue-text-muted">
                <x-ui.icon name="search" size="sm" aria-hidden="true" />
            </div>
            <input
                type="search"
                placeholder="Tìm UEers, cộng đồng, mentor..."
                class="ue-input h-10 pl-9 pr-4 text-md w-full"
                aria-label="Tìm kiếm"
            />
        </div>
    </div>

    {{-- Right actions --}}
    <div class="flex items-center gap-1 ml-auto">
        {{-- Mobile search trigger --}}
        {{-- TODO: Implement mobile search sheet --}}
        <x-ui.icon-button
            icon="search"
            label="Mở tìm kiếm"
            variant="ghost"
            class="md:hidden"
        />

        {{-- Notifications --}}
        {{-- TODO: Replace href with route('notifications.index') when route exists --}}
        <x-ui.icon-button
            icon="bell"
            label="Thông báo"
            variant="ghost"
        />

        {{-- Avatar / profile --}}
        <a
            href="{{ route('profile') }}"
            class="flex items-center ue-focus-ring rounded-full ml-1"
            aria-label="Hồ sơ của tôi"
        >
            <x-ui.avatar size="sm" />
        </a>

        {{-- Logout (visible on mobile where sidebar is hidden) --}}
        <form method="POST" action="{{ route('logout') }}" class="lg:hidden">
            @csrf
            <button
                type="submit"
                aria-label="Đăng xuất"
                class="inline-flex items-center justify-center w-10 h-10 min-h-touch min-w-touch
                       rounded-lg border border-transparent bg-transparent
                       text-ue-text-secondary transition-colors duration-sm ease-out
                       hover:bg-ue-surface-hover hover:text-ue-text ue-focus-ring"
            >
                <x-ui.icon name="log-out" size="md" aria-hidden="true" />
            </button>
        </form>

    </div>
</header>
