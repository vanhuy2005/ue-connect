<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full overflow-hidden">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="theme-color" content="#124874">

        <title>{{ config('app.name', 'UEConnect') }}{{ isset($title) ? ' — ' . $title : '' }}</title>

        <meta name="description" content="{{ $description ?? 'UEConnect — Kết nối cộng đồng sinh viên HCMUE.' }}">

        {{-- PWA Meta Tags --}}
        <link rel="manifest" href="{{ asset('manifest.json') }}">
        <meta name="mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="default">
        <meta name="apple-mobile-web-app-title" content="UEConnect">
        <link rel="apple-touch-icon" href="{{ asset('icons/apple-touch-icon.png') }}">

        {{-- Realtime Meta Config --}}
        <meta name="reverb-app-key" content="{{ config('reverb.apps.apps.0.key', env('REVERB_APP_KEY')) }}">
        <meta name="reverb-host" content="{{ env('REVERB_HOST', '127.0.0.1') }}">
        <meta name="reverb-port" content="{{ config('reverb.servers.reverb.port', env('REVERB_PORT')) }}">
        <meta name="reverb-scheme" content="{{ config('reverb.servers.reverb.scheme', env('REVERB_SCHEME')) }}">

        {{-- Fonts: Be Vietnam Pro — weights 400/500/600/700 only --}}
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
        @auth
            @vite('resources/js/realtime.js')
        @endauth

        {{-- Additional head slots --}}
        @stack('head')
        <script>
            if (window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone) {
                document.documentElement.classList.add('is-standalone');
            }
        </script>
    </head>

    <body class="font-sans antialiased h-full overflow-hidden bg-white dark:bg-zinc-950">
        <x-ui.page-transition />

        {{-- Default shell when not provided by caller --}}
        @php $shell = $shell ?? 'guest'; @endphp

        {{-- Skip to main content (accessibility) --}}
        <a href="#main-content" class="skip-link">Bỏ qua và đến nội dung chính</a>

        {{-- App shell --}}
        <div class="ue-shell ue-shell--{{ $shell }}">
            {{-- Desktop sidebar (only for authenticated layouts) --}}
            @if(in_array($shell, ['social', 'admin', 'conversation']))
                @include('partials.app.sidebar')
            @endif

            {{-- Main column --}}
            <div class="ue-shell__main flex flex-col h-full min-h-0 overflow-hidden relative">
                
                {{-- PWA Install Banner --}}
                <x-pwa.install-banner />

                {{-- Topbar (Only for custom shells, NOT social, admin, conversation, guest, or auth) --}}
                @if(!in_array($shell, ['social', 'admin', 'conversation', 'guest', 'auth']))
                    @include('partials.app.topbar')
                @endif

                {{-- Threads-style mobile topbar for social shell (desktop uses sidebar) --}}
                @if($shell === 'social')
                    @include('partials.app.social-mobile-topbar')
                @endif

                {{-- Account status banner for restrictions --}}
                @if(in_array($shell, ['social', 'admin', 'conversation']))
                    <x-ui.account-status-banner />
                @endif

                {{-- Page content --}}
                <main
                    id="main-content"
                    class="flex-1 flex flex-col h-full min-h-0 {{ in_array($shell, ['social']) ? 'pb-24 lg:pb-4 overflow-y-auto overflow-x-hidden' : (in_array($shell, ['conversation']) ? 'pb-16 lg:pb-0 overflow-hidden' : 'overflow-y-auto overflow-x-hidden') }}"
                    tabindex="-1"
                >
                    @if($shell === 'admin')
                        <div class="flex-1 flex flex-col min-w-0" x-data="{ adminDrawerOpen: false }">
                            {{-- Top Admin Bar - Mobile only --}}
                            <div class="lg:hidden bg-ue-surface border-b border-ue-border px-4 py-3 flex items-center justify-between sticky top-0 z-sticky">
                                <div class="flex items-center gap-2">
                                    <button type="button" @click="adminDrawerOpen = true" class="p-1 rounded-lg text-ue-text hover:bg-ue-surface-hover" aria-label="Mở menu quản trị">
                                        <x-ui.icon name="menu" size="md" />
                                    </button>
                                    @php
                                        $currentTitle = 'Tổng quan';
                                        foreach(\App\Support\Navigation\AdminNavigation::getGroups() as $groupKey => $g) {
                                            if (request()->routeIs('admin.console') && (request()->route('group') ?? 'overview') === $groupKey) {
                                                $currentTitle = $g['vn_label'];
                                            }

                                            foreach($g['items'] as $i) {
                                                $routeParts = explode('.', $i['route']);
                                                $baseRouteName = count($routeParts) >= 2
                                                    ? $routeParts[0] . '.' . $routeParts[1]
                                                    : $i['route'];

                                                if(request()->routeIs($i['route']) || request()->routeIs($baseRouteName . '.*')) {
                                                    $currentTitle = $g['vn_label'];
                                                }
                                            }
                                        }
                                    @endphp
                                    <span class="font-bold text-ue-text text-sm">Quản trị: {{ $currentTitle }}</span>
                                </div>
                                <a href="{{ route('dashboard') }}" class="text-xs font-semibold text-ue-brand-active flex items-center gap-1">
                                    <x-ui.icon name="arrow-left" size="xs" />
                                    Thoát
                                </a>
                            </div>

                            {{-- Mobile Drawer Menu --}}
                            <div
                                x-show="adminDrawerOpen"
                                class="relative z-[800] lg:hidden"
                                role="dialog"
                                aria-modal="true"
                                style="display: none;"
                            >
                                <div
                                    x-show="adminDrawerOpen"
                                    x-transition:enter="transition-opacity ease-linear duration-300"
                                    x-transition:enter-start="opacity-0"
                                    x-transition:enter-end="opacity-100"
                                    x-transition:leave="transition-opacity ease-linear duration-300"
                                    x-transition:leave-start="opacity-100"
                                    x-transition:leave-end="opacity-0"
                                    class="fixed inset-0 bg-black/40 backdrop-blur-sm"
                                    @click="adminDrawerOpen = false"
                                ></div>

                                <div class="fixed inset-0 flex">
                                    <div
                                        x-show="adminDrawerOpen"
                                        x-transition:enter="transition ease-in-out duration-300 transform"
                                        x-transition:enter-start="-translate-x-full"
                                        x-transition:enter-end="translate-x-0"
                                        x-transition:leave="transition ease-in-out duration-300 transform"
                                        x-transition:leave-start="translate-x-0"
                                        x-transition:leave-end="-translate-x-full"
                                        class="relative flex w-full max-w-xs flex-col bg-ue-surface p-6 shadow-xl focus:outline-none"
                                        @keydown.escape.window="adminDrawerOpen = false"
                                    >
                                        <div class="flex items-center justify-between mb-6">
                                            <h2 class="text-lg font-bold text-ue-text">Quản trị UEConnect</h2>
                                            <button type="button" @click="adminDrawerOpen = false" class="p-1 rounded-lg text-ue-text hover:bg-ue-surface-hover" aria-label="Đóng menu">
                                                <x-ui.icon name="x" size="md" />
                                            </button>
                                        </div>

                                        <div class="flex-1 overflow-y-auto pr-1">
                                            <nav class="flex flex-col gap-1 text-sm">
                                                @foreach(\App\Support\Navigation\AdminNavigation::getVisibleGroups() as $groupKey => $group)
                                                    @php
                                                        $active = request()->routeIs('admin.console') && (request()->route('group') ?? 'overview') === $groupKey;

                                                        if (! $active) {
                                                            foreach ($group['items'] as $item) {
                                                                $routeParts = explode('.', $item['route']);
                                                                $baseRouteName = count($routeParts) >= 2
                                                                    ? $routeParts[0] . '.' . $routeParts[1]
                                                                    : $item['route'];

                                                                if (request()->routeIs($item['route']) || request()->routeIs($baseRouteName . '.*')) {
                                                                    $active = true;
                                                                    break;
                                                                }
                                                            }
                                                        }
                                                    @endphp
                                                    <a
                                                        href="{{ route('admin.console', ['group' => $groupKey]) }}"
                                                        @click="adminDrawerOpen = false"
                                                        class="ue-admin-nav-link {{ $active ? 'active' : '' }}"
                                                        @if($active) aria-current="page" @endif
                                                    >
                                                        <x-ui.icon :name="$group['icon']" size="sm" class="flex-shrink-0" />
                                                        <span class="min-w-0 flex-1 truncate">{{ $group['vn_label'] }}</span>
                                                    </a>
                                                @endforeach
                                            </nav>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Content Area --}}
                            <div class="flex-1 min-w-0">
                                @if(isset($slot))
                                    {!! $slot !!}
                                @else
                                    {!! $__env->yieldContent('content') !!}
                                @endif
                            </div>
                        </div>
                    @else
                        @if(isset($slot))
                            {!! $slot !!}
                        @else
                            {!! $__env->yieldContent('content') !!}
                        @endif
                    @endif
                </main>

            </div>
        </div>

        {{-- Mobile bottom nav --}}
        @if(in_array($shell, ['social', 'conversation']))
            @include('partials.app.mobile-bottom-nav')
        @endif

        {{-- Additional script slots --}}
        @stack('scripts')

        @auth
            <script>
                document.addEventListener('alpine:init', () => {
                    Alpine.data('mentionComposer', (config) => ({
                        showDropdown: false,
                        suggestions: [],
                        searchQuery: '',
                        cursorPosition: 0,
                        selectedIndex: 0,
                        activeIndex: 0,
                        taggedUsers: [],
                        localBody: '',
                        openUpward: false,
                        dropdownTop: '100%',
                        scrollHandler: null,
                        resizeHandler: null,
                        init() {
                            this.localBody = (this.$wire && this.$wire[config.wireModel]) || '';
                            if (this.$wire) {
                                this.$watch('$wire.' + config.wireModel, value => {
                                    this.localBody = value || '';
                                    if (!value) {
                                        this.taggedUsers = [];
                                    }
                                });
                            }
                            
                            if (this.localBody) {
                                const matches = this.localBody.match(/@([^\s@#?!.,:;"]+(?:\s+[^\s@#?!.,:;"]+){0,4})/g);
                                if (matches) {
                                    matches.forEach(m => {
                                        const name = m.substring(1).trim();
                                        if (name && !this.taggedUsers.includes(name)) {
                                            this.taggedUsers.push(name);
                                        }
                                    });
                                }
                            }

                            if (config.initialMention && !this.taggedUsers.includes(config.initialMention)) {
                                this.taggedUsers.push(config.initialMention);
                            }

                            this.scrollHandler = () => {
                                if (this.showDropdown) {
                                    const textarea = this.$refs.textarea;
                                    if (textarea) {
                                        const rect = textarea.getBoundingClientRect();
                                        if (rect.bottom < 0 || rect.top > window.innerHeight || rect.right < 0 || rect.left > window.innerWidth) {
                                            this.closeDropdown();
                                        } else {
                                            this.adjustDropdownPosition();
                                        }
                                    }
                                }
                            };

                            this.resizeHandler = () => {
                                if (this.showDropdown) {
                                    this.adjustDropdownPosition();
                                }
                            };

                            window.addEventListener('scroll', this.scrollHandler, true);
                            window.addEventListener('resize', this.resizeHandler, true);
                        },
                        destroy() {
                            if (this.scrollHandler) {
                                window.removeEventListener('scroll', this.scrollHandler, true);
                            }
                            if (this.resizeHandler) {
                                window.removeEventListener('resize', this.resizeHandler, true);
                            }
                        },
                        adjustDropdownPosition() {
                            const textarea = this.$refs.textarea;
                            if (!textarea) return;

                            const style = window.getComputedStyle(textarea);
                            const lineHeight = parseInt(style.lineHeight) || 20;
                            const paddingTop = parseInt(style.paddingTop) || 0;
                            
                            const textBeforeCursor = textarea.value.slice(0, this.cursorPosition);
                            const lines = textBeforeCursor.split('\n').length;
                            const topOffset = paddingTop + (lines * lineHeight) - textarea.scrollTop + 6;

                            const rect = textarea.getBoundingClientRect();
                            const spaceBelow = window.innerHeight - rect.bottom;
                            const spaceAbove = rect.top;
                            
                            if (spaceBelow < 200 && spaceAbove > spaceBelow) {
                                this.openUpward = true;
                                this.dropdownTop = 'auto';
                            } else {
                                this.openUpward = false;
                                this.dropdownTop = Math.max(paddingTop, topOffset) + 'px';
                            }
                        },
                        handleInput(event) {
                            const textarea = event.target;
                            const value = textarea.value;
                            this.localBody = value;
                            const pos = textarea.selectionStart;
                            this.cursorPosition = pos;

                            if (value === '') {
                                this.taggedUsers = [];
                            }

                            const textBeforeCursor = value.slice(0, pos);
                            const lastAt = textBeforeCursor.lastIndexOf('@');

                            if (lastAt !== -1 && (lastAt === 0 || /\s/.test(textBeforeCursor[lastAt - 1]))) {
                                const query = textBeforeCursor.slice(lastAt + 1);
                                if (query.length >= 0 && query.length <= 50 && !/\s{2,}/.test(query) && !/^\s/.test(query)) {
                                    this.searchQuery = query;
                                    if (this.$wire) {
                                        this.$wire.searchMentionUsers(query).then(results => {
                                            this.suggestions = results;
                                            this.showDropdown = results.length > 0;
                                            this.selectedIndex = 0;
                                            this.$nextTick(() => {
                                                this.adjustDropdownPosition();
                                            });
                                        });
                                    }
                                    return;
                                }
                            }
                            this.closeDropdown();
                        },
                        selectNext() {
                            if (!this.showDropdown) return;
                            this.selectedIndex = (this.selectedIndex + 1) % this.suggestions.length;
                        },
                        selectPrev() {
                            if (!this.showDropdown) return;
                            this.selectedIndex = (this.selectedIndex - 1 + this.suggestions.length) % this.suggestions.length;
                        },
                        confirmSelection() {
                            if (!this.showDropdown || this.suggestions.length === 0) return;
                            this.insertMention(this.suggestions[this.selectedIndex]);
                        },
                        insertMention(user) {
                            const textarea = this.$refs.textarea;
                            if (!textarea) return;
                            const value = textarea.value;
                            const pos = this.cursorPosition;
                            
                            const textBeforeCursor = value.slice(0, pos);
                            const lastAt = textBeforeCursor.lastIndexOf('@');
                            
                            const before = value.slice(0, lastAt);
                            const after = value.slice(pos);
                            
                            const mentionText = '@' + user.display_name + ' ';
                            const newValue = before + mentionText + after;
                            
                            textarea.value = newValue;
                            this.localBody = newValue;
                            if (!this.taggedUsers.includes(user.display_name)) {
                                this.taggedUsers.push(user.display_name);
                            }
                            if (this.$wire) {
                                this.$wire.set(config.wireModel, newValue);
                            }
                            textarea.dispatchEvent(new Event('input'));
                            
                            const newPos = lastAt + mentionText.length;
                            const setCaret = () => {
                                textarea.focus();
                                textarea.setSelectionRange(newPos, newPos);
                            };
                            
                            setCaret();
                            this.$nextTick(setCaret);
                            setTimeout(setCaret, 20);
                            setTimeout(setCaret, 50);
                            setTimeout(setCaret, 100);
                            
                            this.closeDropdown();
                        },
                        closeDropdown() {
                            this.showDropdown = false;
                            this.suggestions = [];
                            this.searchQuery = '';
                            this.selectedIndex = 0;
                        }
                    }));
                });
            </script>

            @php
                $metrics = app(\App\Support\Navigation\UserNavigationMetrics::class)->forUser(auth()->user());
                $initialUnreadCount = $metrics['unread_notifications'] + $metrics['unread_messages'];
            @endphp
            <script>
                window.ueInitialUnreadCount = {{ $initialUnreadCount }};

                document.addEventListener('DOMContentLoaded', () => {
                    function updateBadge(iconName, val) {
                        let dotEls = document.querySelectorAll(`.js-badge-dot-${iconName}`);
                        dotEls.forEach(el => el.classList.remove('hidden'));
                        
                        let countEls = document.querySelectorAll(`.js-badge-count-${iconName}`);
                        countEls.forEach(el => {
                            let currentVal = parseInt(el.textContent.trim()) || 0;
                            let newVal = currentVal + val;
                            el.textContent = newVal;
                            el.classList.remove('hidden');
                        });
                    }

                    if (window.Echo) {
                        window.Echo.private('user.{{ Auth::id() }}')
                            .listen('.UserNotificationCreated', (e) => {
                                updateBadge('heart', 1);
                                window.ueNotificationBadge?.increment(1);
                                if (window.Livewire) {
                                    Livewire.dispatch('refreshNotifications');
                                }
                            })
                            .listen('.ConversationUpdated', (e) => {
                                updateBadge('message', 1);
                                window.ueNotificationBadge?.increment(1);
                            });
                    }

                    // Event listener for custom manual badge decrements/resets
                    window.addEventListener('ue-notifications-updated', (e) => {
                        if (e.detail && typeof e.detail.count !== 'undefined') {
                            window.ueNotificationBadge?.setCount(e.detail.count);
                        }
                    });

                    // Presence Heartbeat Loop (throttled/visibility-aware & idle-aware)
                    let lastActiveTime = Date.now();
                    const idleLimit = 5 * 60 * 1000; // 5 minutes

                    function updateActivity() {
                        lastActiveTime = Date.now();
                    }

                    ['mousemove', 'keydown', 'scroll', 'click'].forEach(evt => {
                        window.addEventListener(evt, updateActivity, { passive: true });
                    });

                    function sendHeartbeat() {
                        // Sleep if the tab is hidden
                        if (document.visibilityState !== 'visible') return;

                        // Sleep if user is idle
                        if (Date.now() - lastActiveTime > idleLimit) {
                            return;
                        }

                        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                        if (!csrfToken) {
                            return;
                        }

                        fetch('{{ route('presence.heartbeat') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrfToken
                            }
                        }).catch(err => console.error('Heartbeat error:', err));
                    }

                    sendHeartbeat();
                    setInterval(sendHeartbeat, 45000);
                });
            </script>
        @endauth
        @auth
            @if(!in_array($shell, ['conversation']) && !request()->routeIs('profile.setup'))
                <livewire:partials.app.ai-chatbot />
            @endif
        @endauth

        <!-- Global Lightbox Modal -->
        <div
            x-data="{
                isOpen: false,
                images: [],
                selectedIndex: 0,
                get currentImage() {
                    return this.images[this.selectedIndex] || '';
                },
                open(images, index = 0) {
                    this.images = Array.isArray(images) ? images : [images];
                    this.selectedIndex = index;
                    this.isOpen = true;
                    document.body.style.overflow = 'hidden';
                },
                close() {
                    this.isOpen = false;
                    document.body.style.overflow = '';
                },
                next() {
                    if (this.images.length > 1) {
                        this.selectedIndex = (this.selectedIndex + 1) % this.images.length;
                    }
                },
                prev() {
                    if (this.images.length > 1) {
                        this.selectedIndex = (this.selectedIndex - 1 + this.images.length) % this.images.length;
                    }
                }
            }"
            x-on:open-lightbox.window="open($event.detail.images, $event.detail.index || 0)"
            x-on:keydown.escape.window="close()"
            x-on:keydown.arrow-right.window="next()"
            x-on:keydown.arrow-left.window="prev()"
            x-show="isOpen"
            class="fixed inset-0 z-[9999] flex items-center justify-center bg-black/90 backdrop-blur-xs select-none"
            style="display: none;"
            role="dialog"
            aria-modal="true"
        >
            <!-- Close Button -->
            <button
                type="button"
                @click="close()"
                class="absolute top-4 right-4 text-white/70 hover:text-white transition p-2 rounded-full hover:bg-white/10 z-[10000]"
                aria-label="Đóng"
            >
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-6 h-6">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>

            <!-- Prev Button -->
            <template x-if="images.length > 1">
                <button
                    type="button"
                    @click="prev()"
                    class="absolute left-4 top-1/2 -translate-y-1/2 text-white/70 hover:text-white transition p-3 rounded-full hover:bg-white/10 z-[10000] hidden md:block"
                    aria-label="Ảnh trước"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-6 h-6">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                    </svg>
                </button>
            </template>

            <!-- Next Button -->
            <template x-if="images.length > 1">
                <button
                    type="button"
                    @click="next()"
                    class="absolute right-4 top-1/2 -translate-y-1/2 text-white/70 hover:text-white transition p-3 rounded-full hover:bg-white/10 z-[10000] hidden md:block"
                    aria-label="Ảnh sau"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-6 h-6">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                    </svg>
                </button>
            </template>

            <!-- Main Content (Click backdrop to close) -->
            <div @click="close()" class="absolute inset-0 flex items-center justify-center p-4">
                <img
                    :src="currentImage"
                    @click.stop
                    class="max-w-[90vw] max-h-[90vh] object-contain rounded-md shadow-2xl transition-all duration-300"
                    alt="Phóng to hình ảnh"
                />
            </div>

            <!-- Mobile Swipe / Dots indicator -->
            <div class="absolute bottom-4 left-1/2 -translate-x-1/2 flex items-center gap-2 text-white/60 text-xs font-semibold bg-black/40 px-3 py-1.5 rounded-full" x-show="images.length > 1">
                <span x-text="(selectedIndex + 1) + ' / ' + images.length"></span>
            </div>
        </div>

    </body>
</html>
