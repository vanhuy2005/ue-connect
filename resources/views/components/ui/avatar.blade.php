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
])

@php
// When a User model is provided, resolve src and fallback automatically.
if ($user) {
    $profile = $user->profile;
    $avatarMedia = $profile?->avatar ?? null;
    if ($avatarMedia && $avatarMedia->path) {
        $src = \Illuminate\Support\Facades\Storage::url($avatarMedia->path);
    }
    if (! $fallback) {
        $displayName = $profile?->display_name ?? $user->name ?? '';
        // Build up to 2 initials from the name
        $parts = array_filter(explode(' ', trim($displayName)));
        $fallback = count($parts) >= 2
            ? mb_strtoupper(mb_substr($parts[0], 0, 1) . mb_substr(end($parts), 0, 1))
            : mb_strtoupper(mb_substr($displayName, 0, 2));
    }
    if (! $alt) {
        $alt = $user->name ?? 'Ảnh đại diện';
    }
}

$sizeClasses = match($size) {
    'xs'  => 'w-6 h-6 text-2xs',
    'sm'  => 'w-8 h-8 text-xs',
    'md'  => 'w-10 h-10 text-sm',
    'lg'  => 'w-12 h-12 text-md',
    'xl'  => 'w-16 h-16 text-base',
    '2xl' => 'w-20 h-20 text-lg',
    default => 'w-10 h-10 text-sm',
};

$shapeClass = match($shape) {
    'rounded-square' => 'rounded-xl',
    default          => 'rounded-full',
};

$initials = $fallback
    ? strtoupper(mb_substr($fallback, 0, 2))
    : null;
@endphp


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
        />
    @elseif($initials)
        <span
            class="font-semibold text-ue-brand select-none leading-none"
            aria-hidden="true"
        >
            {{ $initials }}
        </span>
    @else
        {{-- Default user icon fallback --}}
        <x-ui.icon name="user" size="{{ $size === 'xs' || $size === 'sm' ? 'xs' : 'sm' }}" class="text-ue-brand opacity-70" aria-hidden="true" />
    @endif
</span>
