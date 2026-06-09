{{--
    UEConnect Avatar Component
    Source: docs/04-design/12-component-primitives.md, 19-design-token-documentation.md §12

    Usage:
        <x-ui.avatar src="/images/user.jpg" alt="Nguyễn Văn A" />
        <x-ui.avatar fallback="NA" size="lg" />
        <x-ui.avatar src="/images/club.png" shape="rounded-square" size="xl" />

    Props:
        src      (string)  — image URL (optional)
        alt      (string)  — alt text for image
        size     (string)  xs|sm|md|lg|xl|2xl (default: md)
        shape    (string)  circle|rounded-square (default: circle)
        fallback (string)  — initials to show if no src (max 2 chars)
--}}

@props([
    'user'     => null,
    'src'      => null,
    'alt'      => '',
    'size'     => 'md',
    'shape'    => 'circle',
    'fallback' => null,
    'status'   => true,
])

@php
$displayName = '';
if ($user) {
    if (! $src) {
        $src = \App\Support\Media\MediaUrlResolver::avatarUrl($user, 'thumb');
    }
    $profile = $user->profile;
    $displayName = $profile?->display_name ?? $user->name ?? '';
    if (! $fallback) {
        // Build up to 2 initials from the name
        $parts = array_filter(explode(' ', trim($displayName)));
        $fallback = count($parts) >= 2
            ? mb_strtoupper(mb_substr($parts[0], 0, 1) . mb_substr(end($parts), 0, 1))
            : mb_strtoupper(mb_substr($displayName, 0, 2));
    }
    if (! $alt) {
        $alt = $user->name ?? 'Ảnh đại diện';
    }
} else {
    $displayName = $fallback ?? '';
    if (! $alt) {
        $alt = $fallback ? "Ảnh đại diện của {$fallback}" : 'Ảnh đại diện';
    }
}

$isOnline = false;
if ($user && auth()->check()) {
    $isOnline = auth()->user()->canSeePresenceOf($user) && $user->isOnline();
}

$dotSizeClasses = match($size) {
    'xs'  => 'h-1.5 w-1.5 ring-1',
    'sm'  => 'h-2 w-2 ring-1.5',
    'md'  => 'h-2.5 w-2.5 ring-2',
    'lg'  => 'h-3 w-3 ring-2',
    'xl'  => 'h-4 w-4 ring-2',
    '2xl' => 'h-5 w-5 ring-2.5',
    'full' => 'h-4 w-4 ring-2',
    default => 'h-2.5 w-2.5 ring-2',
};

$sizeClasses = match($size) {
    'xs'  => 'w-6 h-6 text-2xs',
    'sm'  => 'w-8 h-8 text-xs',
    'md'  => 'w-10 h-10 text-sm',
    'lg'  => 'w-12 h-12 text-md',
    'xl'  => 'w-16 h-16 text-base',
    '2xl' => 'w-20 h-20 text-lg',
    'full' => 'w-full h-full text-xl',
    default => 'w-10 h-10 text-sm',
};

$shapeClass = match($shape) {
    'rounded-square' => 'rounded-xl',
    default          => 'rounded-full',
};

// Deterministic color palette based on seed
$seed = $user?->email ?? $user?->id ?? $displayName ?? $fallback ?? 'guest';
$palettes = [
    ['bg' => '#EAEBED', 'fg' => '#8E98A5'], // Cool Slate (matches reference image exactly)
    ['bg' => '#EEF7FF', 'fg' => '#124874'], // Brand Cerulean
    ['bg' => '#EFF6FF', 'fg' => '#1D4ED8'], // Info Blue
    ['bg' => '#F0FDF4', 'fg' => '#16A34A'], // Success Green
    ['bg' => '#FFFDF5', 'fg' => '#B45309'], // Warning Amber
    ['bg' => '#F5F3FF', 'fg' => '#6D28D9'], // Soft Indigo/Purple
];
$paletteIndex = ($seed === 'guest') ? 0 : (abs(crc32((string) $seed)) % count($palettes));
$palette = $palettes[$paletteIndex];
@endphp


<div class="relative inline-flex flex-shrink-0 self-start @if($size === 'full') w-full h-full @endif">
    <span
        {{ $attributes->class([
            'inline-flex items-center justify-center flex-shrink-0 overflow-hidden',
            'bg-ue-brand-soft border border-ue-border',
            $sizeClasses,
            $shapeClass,
        ]) }}
        role="img"
        aria-label="{{ $alt ?: 'Ảnh đại diện' }}"
    >
        @if($src)
            <img
                src="{{ $src }}"
                alt="{{ $alt }}"
                class="w-full h-full object-cover"
                loading="lazy"
                decoding="async"
                onerror="this.style.display='none'; this.nextElementSibling.classList.remove('hidden');"
            />
            <svg viewBox="0 0 100 100" class="w-full h-full text-current hidden" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                <rect width="100" height="100" fill="{{ $palette['bg'] }}"/>
                <!-- Head (rounder vertical oval) -->
                <ellipse cx="50" cy="38" rx="19" ry="21" fill="{{ $palette['fg'] }}"/>
                <!-- Body (curved shoulders reaching bottom) -->
                <path d="M8 105 C8 76 25 63 50 63 C75 63 92 76 92 105 Z" fill="{{ $palette['fg'] }}"/>
            </svg>
        @else
            <svg viewBox="0 0 100 100" class="w-full h-full text-current" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                <rect width="100" height="100" fill="{{ $palette['bg'] }}"/>
                <!-- Head (rounder vertical oval) -->
                <ellipse cx="50" cy="38" rx="19" ry="21" fill="{{ $palette['fg'] }}"/>
                <!-- Body (curved shoulders reaching bottom) -->
                <path d="M8 105 C8 76 25 63 50 63 C75 63 92 76 92 105 Z" fill="{{ $palette['fg'] }}"/>
            </svg>
        @endif
    </span>
    @if ($isOnline && $status)
        <span class="absolute bottom-0 right-0 block rounded-full bg-green-500 ring-white {{ $dotSizeClasses }}" aria-label="Online"></span>
    @endif
</div>
