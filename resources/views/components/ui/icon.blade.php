{{--
    UEConnect Icon Component — minimal inline SVG set
    Source: docs/04-design/10-icon-system.md (Lucide-style rounded line icons)

    Usage:
        <x-ui.icon name="check" class="w-4 h-4" />
        <x-ui.icon name="alert" size="md" />

    Props:
        name (string)  — icon identifier
        size (string)  — xs|sm|md|lg|xl (default: md)
        class (string) — additional classes

    Supported icons: check, x, info, alert, warning, shield, user, search,
                     bell, home, message, users, book-open, loader, arrow-right,
                     arrow-left, more-horizontal, edit, trash, eye, send,
                     plus, minus, chevron-down, check-circle, x-circle,
                     clock, calendar, lock, star, link-external, upload,
                     log-out, user-circle, shield-x, microsoft, community,
                     graduation-cap, key-round, history, bar-chart-3, settings-2
--}}

@props([
    'name'  => 'circle',
    'size'  => 'md',
])

@php
$sizeClass = match($size) {
    'xxs' => 'w-2.5 h-2.5',
    'xs'  => 'w-3 h-3',
    'sm'  => 'w-4 h-4',
    'md'  => 'w-5 h-5',
    'lg'  => 'w-6 h-6',
    'xl'  => 'w-8 h-8',
    '2xl' => 'w-10 h-10',
    default => 'w-5 h-5',
};
@endphp

<svg
    {{ $attributes->class([$sizeClass]) }}
    xmlns="http://www.w3.org/2000/svg"
    viewBox="0 0 24 24"
    fill="none"
    stroke="currentColor"
    stroke-width="2"
    stroke-linecap="round"
    stroke-linejoin="round"
    aria-hidden="true"
    focusable="false"
>
    @switch($name)

        {{-- ✓ check --}}
        @case('check')
            <polyline points="20 6 9 17 4 12"></polyline>
            @break

        {{-- ✗ close/x --}}
        @case('x')
            <line x1="18" y1="6" x2="6" y2="18"></line>
            <line x1="6" y1="6" x2="18" y2="18"></line>
            @break

        {{-- ℹ info --}}
        @case('info')
        @case('info-circle')
            <circle cx="12" cy="12" r="10"></circle>
            <line x1="12" y1="16" x2="12" y2="12"></line>
            <line x1="12" y1="8" x2="12.01" y2="8"></line>
            @break

        {{-- ⚠ alert / alert-triangle --}}
        @case('alert')
        @case('alert-triangle')
        @case('reports')
            <path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"></path>
            <line x1="12" y1="9" x2="12" y2="13"></line>
            <line x1="12" y1="17" x2="12.01" y2="17"></line>
            @break

        {{-- ⚠ warning / octagon --}}
        @case('warning')
        @case('alert-circle')
            <circle cx="12" cy="12" r="10"></circle>
            <line x1="12" y1="8" x2="12" y2="12"></line>
            <line x1="12" y1="16" x2="12.01" y2="16"></line>
            @break

        {{-- 🛡 shield --}}
        @case('shield')
        @case('admin-dashboard')
            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
            @break

        {{-- 🛡 shield-check --}}
        @case('shield-check')
        @case('verification')
            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
            <polyline points="9 12 11 14 15 10"></polyline>
            @break

        @case('shield-alert')
            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
            <line x1="12" y1="8" x2="12" y2="12"></line>
            <line x1="12" y1="16" x2="12.01" y2="16"></line>
            @break

        {{-- 👤 user --}}
        @case('user')
            <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"></path>
            <circle cx="12" cy="7" r="4"></circle>
            @break

        {{-- 👤 user-plus --}}
        @case('user-plus')
            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
            <circle cx="9" cy="7" r="4"></circle>
            <line x1="19" y1="8" x2="19" y2="14"></line>
            <line x1="16" y1="11" x2="22" y2="11"></line>
            @break

        @case('user-minus')
            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
            <circle cx="9" cy="7" r="4"></circle>
            <line x1="16" y1="11" x2="22" y2="11"></line>
            @break

        {{-- 👥 users --}}
        @case('users')
            <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"></path>
            <circle cx="9" cy="7" r="4"></circle>
            <path d="M23 21v-2a4 4 0 00-3-3.87"></path>
            <path d="M16 3.13a4 4 0 010 7.75"></path>
            @break

        {{-- 🔍 search --}}
        @case('search')
            <circle cx="11" cy="11" r="8"></circle>
            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
            @break

        {{-- 🔔 bell --}}
        @case('bell')
        @case('system-notifications')
            <path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
            <path d="M13.73 21a2 2 0 01-3.46 0"></path>
            @break

        {{-- 🏠 home --}}
        @case('home')
            <path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"></path>
            <polyline points="9 22 9 12 15 12 15 22"></polyline>
            @break

        {{-- 💬 message --}}
        @case('message')
        @case('message-square')
            <path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"></path>
            @break

        {{-- ⚙ settings --}}
        @case('settings')
            <circle cx="12" cy="12" r="3"></circle>
            <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
            @break

        {{-- ❓ help-circle --}}
        @case('help-circle')
        @case('help')
            <circle cx="12" cy="12" r="10"></circle>
            <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path>
            <line x1="12" y1="17" x2="12.01" y2="17"></line>
            @break

        {{-- ☰ menu --}}
        @case('menu')
            <line x1="3" y1="12" x2="21" y2="12"></line>
            <line x1="3" y1="6" x2="21" y2="6"></line>
            <line x1="3" y1="18" x2="21" y2="18"></line>
            @break

        {{-- 📖 book-open --}}
        @case('book-open')
            <path d="M2 3h6a4 4 0 014 4v14a3 3 0 00-3-3H2z"></path>
            <path d="M22 3h-6a4 4 0 00-4 4v14a3 3 0 013-3h7z"></path>
            @break

        {{-- ⟳ loader --}}
        @case('loader')
            <line x1="12" y1="2" x2="12" y2="6"></line>
            <line x1="12" y1="18" x2="12" y2="22"></line>
            <line x1="4.93" y1="4.93" x2="7.76" y2="7.76"></line>
            <line x1="16.24" y1="16.24" x2="19.07" y2="19.07"></line>
            <line x1="2" y1="12" x2="6" y2="12"></line>
            <line x1="18" y1="12" x2="22" y2="12"></line>
            <line x1="4.93" y1="19.07" x2="7.76" y2="16.24"></line>
            <line x1="16.24" y1="7.76" x2="19.07" y2="4.93"></line>
            @break

        {{-- → arrow-right --}}
        @case('arrow-right')
            <line x1="5" y1="12" x2="19" y2="12"></line>
            <polyline points="12 5 19 12 12 19"></polyline>
            @break

        {{-- ← arrow-left --}}
        @case('arrow-left')
            <line x1="19" y1="12" x2="5" y2="12"></line>
            <polyline points="12 19 5 12 12 5"></polyline>
            @break

        {{-- ↓ arrow-down --}}
        @case('arrow-down')
            <line x1="12" y1="5" x2="12" y2="19"></line>
            <polyline points="19 12 12 19 5 12"></polyline>
            @break

        {{-- ↑ arrow-up --}}
        @case('arrow-up')
            <line x1="12" y1="19" x2="12" y2="5"></line>
            <polyline points="5 12 12 5 19 12"></polyline>
            @break

        {{-- ⋯ more-horizontal --}}
        @case('more-horizontal')
            <circle cx="12" cy="12" r="1"></circle>
            <circle cx="19" cy="12" r="1"></circle>
            <circle cx="5" cy="12" r="1"></circle>
            @break

        {{-- ✏ edit --}}
        @case('edit')
            <path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"></path>
            <path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"></path>
            @break

        {{-- 🗑 trash --}}
        @case('trash')
        @case('trash-2')
            <polyline points="3 6 5 6 21 6"></polyline>
            <path d="M19 6l-1 14H6L5 6"></path>
            <path d="M10 11v6"></path>
            <path d="M14 11v6"></path>
            <path d="M9 6V4h6v2"></path>
            @break

        {{-- 👁 eye --}}
        @case('eye')
        @case('appearance')
            <circle cx="12" cy="12" r="3"></circle>
            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
            @break

        {{-- ✈ send --}}
        @case('send')
            <line x1="22" y1="2" x2="11" y2="13"></line>
            <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
            @break

        {{-- 🔁 repost / repeat --}}
        @case('repost')
        @case('repeat-2')
            <path d="m17 2 4 4-4 4"></path>
            <path d="M3 11v-1a4 4 0 0 1 4-4h14"></path>
            <path d="m7 22-4-4 4-4"></path>
            <path d="M21 13v1a4 4 0 0 1-4 4H3"></path>
            @break

        {{-- + plus --}}
        @case('plus')
            <line x1="12" y1="5" x2="12" y2="19"></line>
            <line x1="5" y1="12" x2="19" y2="12"></line>
            @break

        {{-- − minus --}}
        @case('minus')
            <line x1="5" y1="12" x2="19" y2="12"></line>
            @break

        {{-- ⌄ chevron-down --}}
        @case('chevron-down')
            <polyline points="6 9 12 15 18 9"></polyline>
            @break

        {{-- ⌃ chevron-up --}}
        @case('chevron-up')
            <polyline points="18 15 12 9 6 15"></polyline>
            @break

        {{-- chevron-right --}}
        @case('chevron-right')
            <polyline points="9 18 15 12 9 6"></polyline>
            @break

        {{-- chevron-left --}}
        @case('chevron-left')
            <polyline points="15 18 9 12 15 6"></polyline>
            @break

        {{-- ✓ check-circle --}}
        @case('check-circle')
            <path d="M22 11.08V12a10 10 0 11-5.93-9.14"></path>
            <polyline points="22 4 12 14.01 9 11.01"></polyline>
            @break

        {{-- ✗ x-circle --}}
        @case('x-circle')
            <circle cx="12" cy="12" r="10"></circle>
            <line x1="15" y1="9" x2="9" y2="15"></line>
            <line x1="9" y1="9" x2="15" y2="15"></line>
            @break

        {{-- 🕐 clock --}}
        @case('clock')
            <circle cx="12" cy="12" r="10"></circle>
            <polyline points="12 6 12 12 16 14"></polyline>
            @break

        @case('dollar-sign')
            <line x1="12" y1="1" x2="12" y2="23"></line>
            <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
            @break

        @case('hourglass')
            <path d="M5 2h14"></path>
            <path d="M5 22h14"></path>
            <path d="M19 2v4c0 3.8-3.1 7-7 7s-7-3.2-7-7V2"></path>
            <path d="M5 22v-4c0-3.8 3.1-7 7-7s7 3.2 7 7v4"></path>
            @break

        {{-- 📅 calendar --}}
        @case('calendar')
            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
            <line x1="16" y1="2" x2="16" y2="6"></line>
            <line x1="8" y1="2" x2="8" y2="6"></line>
            <line x1="3" y1="10" x2="21" y2="10"></line>
            @break

        {{-- 🔒 lock --}}
        @case('lock')
            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
            <path d="M7 11V7a5 5 0 0110 0v4"></path>
            @break

        {{-- 🔓 unlock --}}
        @case('unlock')
        @case('lock-open')
            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
            <path d="M7 11V7a5 5 0 0 1 9.9-1"></path>
            @break

        {{-- ⭐ star --}}
        @case('star')
            <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
            @break

        {{-- 🔗 link --}}
        @case('link')
        @case('link-2')
            <path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"></path>
            <path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"></path>
            @break

        {{-- 📋 copy --}}
        @case('copy')
            <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
            <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
            @break

        {{-- 🔗 share / share-2 --}}
        @case('share-2')
        @case('share')
            <circle cx="18" cy="5" r="3"></circle>
            <circle cx="6" cy="12" r="3"></circle>
            <circle cx="18" cy="19" r="3"></circle>
            <line x1="8.59" y1="13.51" x2="15.42" y2="17.49"></line>
            <line x1="15.41" y1="6.51" x2="8.59" y2="10.49"></line>
            @break

        {{-- 🔗 link-external --}}
        @case('link-external')
        @case('external-link')
            <path d="M18 13v6a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2h6"></path>
            <polyline points="15 3 21 3 21 9"></polyline>
            <line x1="10" y1="14" x2="21" y2="3"></line>
            @break

        {{-- ⬆ upload --}}
        @case('upload')
        @case('upload-cloud')
            <polyline points="16 16 12 12 8 16"></polyline>
            <line x1="12" y1="12" x2="12" y2="21"></line>
            <path d="M20.39 18.39A5 5 0 0018 9h-1.26A8 8 0 103 16.3"></path>
            @break

        @case('download')
            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
            <polyline points="7 10 12 15 17 10"></polyline>
            <line x1="12" y1="15" x2="12" y2="3"></line>
            @break

        @case('image')
        @case('file-image')
        @case('media')
            <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
            <circle cx="8.5" cy="8.5" r="1.5"></circle>
            <polyline points="21 15 16 10 5 21"></polyline>
            @break

        {{-- 📷 camera --}}
        @case('camera')
            <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"></path>
            <circle cx="12" cy="13" r="4"></circle>
            @break

        {{-- 📎 paperclip --}}
        @case('paperclip')
            <path d="m21.44 11.05-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"></path>
            @break

        {{-- 🏘 community --}}
        @case('community')
        @case('building-2')
        @case('grid')
        @case('communities')
            <rect x="3" y="3" width="7" height="7"></rect>
            <rect x="14" y="3" width="7" height="7"></rect>
            <rect x="14" y="14" width="7" height="7"></rect>
            <rect x="3" y="14" width="7" height="7"></rect>
            @break

        {{-- 📊 bar-chart-3 --}}
        @case('bar-chart-3')
        @case('analytics')
        @case('bar-chart')
            <line x1="5" y1="20" x2="19" y2="20"></line>
            <rect x="6" y="12" width="3" height="8" rx="1"></rect>
            <rect x="11" y="8" width="3" height="12" rx="1"></rect>
            <rect x="16" y="4" width="3" height="16" rx="1"></rect>
            @break

        {{-- 🎓 graduation-cap / mentor --}}
        @case('graduation-cap')
        @case('award')
        @case('mentor')
            <circle cx="12" cy="8" r="6"></circle>
            <path d="M15.477 12.89L17 22l-5-3-5 3 1.523-9.11"></path>
            @break

        {{-- 🗝 key-round --}}
        @case('key-round')
        @case('key')
        @case('permissions')
            <path d="m21 2-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.778-7.778zm0 0L15.5 7.5m0 0 1.5 1.5M15.5 7.5 14 6"></path>
            @break

        {{-- 🕒 history --}}
        @case('history')
        @case('clock')
        @case('audit-logs')
            <circle cx="12" cy="12" r="10"></circle>
            <path d="M12 6v6l4 2"></path>
            @break

        {{-- ⚙ settings-2 --}}
        @case('settings-2')
        @case('settings')
        @case('system-settings')
            <circle cx="12" cy="12" r="3"></circle>
            <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
            @break

        {{-- ❖ microsoft --}}
        @case('microsoft')
            <rect x="2" y="2" width="9" height="9" fill="currentColor" stroke="none"></rect>
            <rect x="13" y="2" width="9" height="9" fill="currentColor" stroke="none"></rect>
            <rect x="2" y="13" width="9" height="9" fill="currentColor" stroke="none"></rect>
            <rect x="13" y="13" width="9" height="9" fill="currentColor" stroke="none"></rect>
            @break

        {{-- 💼 briefcase --}}
        @case('briefcase')
            <rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect>
            <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path>
            @break

        {{-- 📢 megaphone --}}
        @case('megaphone')
        @case('announcements')
            <path d="m3 11 18-5v12L3 14v-3z"></path>
            <path d="M11.6 16.8a3 3 0 1 1-5.8-1.6"></path>
            @break

        {{-- 🌐 globe --}}
        @case('globe')
            <circle cx="12" cy="12" r="10"></circle>
            <path d="M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20"></path>
            <path d="M2 12h20"></path>
            @break

        {{-- 📍 map-pin --}}
        @case('map-pin')
            <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"></path>
            <circle cx="12" cy="10" r="3"></circle>
            @break

        {{-- ✉ mail --}}
        @case('mail')
            <rect x="2" y="4" width="20" height="16" rx="2"></rect>
            <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"></path>
            @break

        {{-- 📞 phone --}}
        @case('phone')
            <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
            @break

        {{-- 🔑 key --}}
        @case('key')
            <path d="m21 2-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.778-7.778zm0 0L15.5 7.5m0 0 1.5 1.5M15.5 7.5 14 6"></path>
            @break

        {{-- 🚪 log-out --}}
        @case('log-out')
        @case('logout')
            <path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"></path>
            <polyline points="16 17 21 12 16 7"></polyline>
            <line x1="21" y1="12" x2="9" y2="12"></line>
            @break

        {{-- log-in --}}
        @case('log-in')
            <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"></path>
            <polyline points="10 17 15 12 10 7"></polyline>
            <line x1="15" y1="12" x2="3" y2="12"></line>
            @break

        {{-- 📌 pin --}}
        @case('pin')
            <line x1="12" y1="17" x2="12" y2="22"></line>
            <path d="M5 17h14v-1.76a2 2 0 0 0-.44-1.24l-2.78-3.55A2 2 0 0 1 15 9.24V5a1 1 0 0 0-1-1h-4a1 1 0 0 0-1 1v4.24a2 2 0 0 1-.78 1.21L5.44 14a2 2 0 0 0-.44 1.24V17Z"></path>
            @break

        {{-- ➔ forward --}}
        @case('forward')
            <polyline points="15 17 20 12 15 7"></polyline>
            <path d="M4 18v-2a4 4 0 0 1 4-4h12"></path>
            @break

        {{-- 👤 user-circle --}}
        @case('user-circle')
            <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"></path>
            <circle cx="12" cy="7" r="4"></circle>
            @break

        {{-- 🛡 shield-x --}}
        @case('shield-x')
            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
            <line x1="9.5" y1="9.5" x2="14.5" y2="14.5"></line>
            <line x1="14.5" y1="9.5" x2="9.5" y2="14.5"></line>
            @break

        @case('slash')
            <circle cx="12" cy="12" r="10"></circle>
            <line x1="4.93" y1="4.93" x2="19.07" y2="19.07"></line>
            @break

        {{-- 🚩 flag --}}
        @case('flag')
        @case('moderation')
            <path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"></path>
            <line x1="4" y1="22" x2="4" y2="15"></line>
            @break

        {{-- ⋮ more-vertical --}}
        @case('more-vertical')
            <circle cx="12" cy="12" r="1"></circle>
            <circle cx="12" cy="5" r="1"></circle>
            <circle cx="12" cy="19" r="1"></circle>
            @break

        {{-- ♥ heart --}}
        @case('heart')
            <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
            @break

        {{-- 🔖 bookmark --}}
        @case('bookmark')
        @case('saved')
            <path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"></path>
            @break

        {{-- ✏ edit-3 --}}
        @case('edit-3')
            <path d="M12 20h9"></path>
            <path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path>
            @break

        {{-- 💬 message-circle --}}
        @case('message-circle')
            <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path>
            @break

        {{-- ↩ reply --}}
        @case('reply')
            <polyline points="9 17 4 12 9 7"></polyline>
            <path d="M20 18v-2a4 4 0 0 0-4-4H4"></path>
            @break

        {{-- 👁 eye-off --}}
        @case('eye-off')
            <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
            <line x1="1" y1="1" x2="23" y2="23"></line>
            @break

        {{-- 📄 file-text --}}
        @case('file')
            <path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"></path>
            <path d="M14 2v4a2 2 0 0 0 2 2h4"></path>
            @break

        @case('file-text')
            <path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"></path>
            <path d="M14 2v4a2 2 0 0 0 2 2h4"></path>
            <path d="M10 9H8"></path>
            <path d="M16 13H8"></path>
            <path d="M16 17H8"></path>
            @break

        {{-- ✨ sparkles --}}
        @case('sparkles')
            <path d="m12 3-1.912 5.813a2 2 0 0 1-1.275 1.275L3 12l5.813 1.912a2 2 0 0 1 1.275 1.275L12 21l1.912-5.813a2 2 0 0 1 1.275-1.275L21 12l-5.813-1.912a2 2 0 0 1-1.275-1.275Z"></path>
            <path d="m5 3 1 2.5L8.5 6 6 7 5 9.5 4 7 1.5 6 4 5z"></path>
            <path d="m19 17 1 2.5 2.5.5-2.5 1-1 2.5-1-2.5-2.5-1 2.5-1z"></path>
            @break

        @case('inbox')
            <polyline points="22 12 16 12 14 15 10 15 8 12 2 12"></polyline>
            <path d="M5.45 5.11 2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z"></path>
            @break

        @case('corner-down-right')
            <polyline points="15 10 20 15 15 20"></polyline>
            <path d="M4 4v7a4 4 0 0 0 4 4h12"></path>
            @break

        @case('circle')
            <circle cx="12" cy="12" r="10"></circle>
            @break

        @case('circle-alert')
        @case('alert-octagon')
            <circle cx="12" cy="12" r="10"></circle>
            <line x1="12" y1="8" x2="12" y2="12"></line>
            <line x1="12" y1="16" x2="12.01" y2="16"></line>
            @break

        @case('refresh-cw')
            <path d="M3 12a9 9 0 0 1 9-9 9.75 9.75 0 0 1 6.74 2.74L21 8"></path>
            <polyline points="21 3 21 8 16 8"></polyline>
            <path d="M21 12a9 9 0 0 1-9 9 9.75 9.75 0 0 1-6.74-2.74L3 16"></path>
            <polyline points="3 21 3 16 8 16"></polyline>
            @break

        @case('database')
            <ellipse cx="12" cy="5" rx="9" ry="3"></ellipse>
            <path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"></path>
            <path d="M3 12c0 1.66 4 3 9 3s9-1.34 9-3"></path>
            @break

        @case('clipboard-check')
            <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"></path>
            <rect x="8" y="2" width="8" height="4" rx="1" ry="1"></rect>
            <polyline points="9 14 11 16 15 12"></polyline>
            @break

        @case('user-check')
            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
            <circle cx="9" cy="7" r="4"></circle>
            <polyline points="16 11 18 13 22 9"></polyline>
            @break
        {{-- 🕐✕ clock-x (expired / time's up) --}}
        @case('clock-x')
            <circle cx="12" cy="12" r="10"></circle>
            <polyline points="12 6 12 12 16 14"></polyline>
            <line x1="9" y1="9" x2="15" y2="15"></line>
            <line x1="15" y1="9" x2="9" y2="15"></line>
            @break

        {{-- Default fallback circle with warnings in local --}}
        @default
            @if(app()->environment('local', 'testing'))
                @php
                    logger()->warning("Missing icon in UI component: {$name}");
                @endphp
                <path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" stroke="#EF4444" stroke-width="3"></path>
                <line x1="12" y1="9" x2="12" y2="13" stroke="#EF4444" stroke-width="3"></line>
                <line x1="12" y1="17" x2="12.01" y2="17" stroke="#EF4444" stroke-width="3"></line>
            @else
                <circle cx="12" cy="12" r="10"></circle>
                <path d="M9.09 9a3 3 0 015.83 1c0 2-3 3-3 3"></path>
                <line x1="12" y1="17" x2="12.01" y2="17"></line>
            @endif

    @endswitch
</svg>
