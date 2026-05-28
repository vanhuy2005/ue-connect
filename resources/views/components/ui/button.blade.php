{{--
    UEConnect Button Component
    Source: docs/04-design/12-component-primitives.md §4, 13-component-variants.md §4

    Usage:
        <x-ui.button>Đăng nhập</x-ui.button>
        <x-ui.button variant="secondary" size="lg">Để sau</x-ui.button>
        <x-ui.button variant="danger" type="submit">Xóa bài viết</x-ui.button>
        <x-ui.button variant="ghost" icon="arrow-right" icon-position="right">Khám phá</x-ui.button>
        <x-ui.button :loading="true">Đang xử lý...</x-ui.button>

    Props:
        variant      (string)  primary|secondary|outline|ghost|danger|danger-outline|link|inverse
        size         (string)  xs|sm|md|lg|xl
        type         (string)  button|submit|reset
        disabled     (bool)
        loading      (bool)
        icon         (string)  icon name for x-ui.icon
        icon-position(string)  left|right

    All other HTML attributes (wire:click, @click, id, etc.) are forwarded.
--}}

@props([
    'variant'      => 'primary',
    'size'         => 'md',
    'type'         => 'button',
    'disabled'     => false,
    'loading'      => false,
    'icon'         => null,
    'iconPosition' => 'left',
    'href'         => null,
])

@php
/** Size tokens: height, padding, font-size */
$sizeClasses = match($size) {
    'xs' => 'h-7 px-2.5 text-xs gap-1',
    'sm' => 'h-8 px-3 text-md gap-1.5',
    'md' => 'h-10 px-4 text-base gap-2',
    'lg' => 'h-12 px-5 text-lg gap-2',
    'xl' => 'h-14 px-6 text-xl gap-2',
    default => 'h-10 px-4 text-base gap-2',
};

/** Icon size based on button size */
$iconSize = match($size) {
    'xs', 'sm' => 'sm',
    'lg', 'xl' => 'lg',
    default    => 'md',
};

/** Variant visual tokens */
$variantClasses = match($variant) {
    'primary' =>
        'bg-ue-brand text-white border-transparent ' .
        'hover:bg-ue-brand-hover hover:text-white ' .
        'active:bg-ue-brand-active active:text-white ' .
        'focus-visible:bg-ue-brand focus-visible:text-white',

    'secondary' =>
        'bg-ue-surface-hover text-ue-text border border-ue-border ' .
        'hover:bg-ue-surface-pressed hover:border-ue-border-strong hover:text-ue-text ' .
        'active:bg-ue-surface-pressed active:text-ue-text',

    'outline' =>
        'bg-white text-ue-brand border border-ue-brand-border ' .
        'hover:bg-ue-brand-soft hover:border-ue-brand/30 hover:text-ue-brand-active ' .
        'active:bg-ue-brand-soft-hover active:text-ue-brand-active',

    'ghost' =>
        'bg-transparent text-ue-text-secondary border border-transparent ' .
        'hover:bg-ue-brand-soft hover:text-ue-brand-active ' .
        'active:bg-ue-brand-soft-hover active:text-ue-brand-active',

    'soft' =>
        'bg-ue-brand-soft text-ue-brand border border-transparent ' .
        'hover:bg-ue-brand-soft-hover hover:text-ue-brand-active ' .
        'active:bg-ue-brand-soft-hover active:text-ue-brand-active',

    'danger' =>
        'bg-ue-danger text-white border-transparent ' .
        'hover:bg-red-700 hover:text-white ' .
        'active:bg-red-800 active:text-white',

    'danger-outline' =>
        'bg-white text-ue-danger border border-ue-danger ' .
        'hover:bg-red-50 hover:text-red-700 hover:border-red-600 ' .
        'active:bg-red-100 active:text-red-800 active:border-red-700',

    'link' =>
        'bg-transparent text-ue-brand border-transparent underline-offset-2 ' .
        'hover:underline hover:text-ue-brand-hover ' .
        'px-0 h-auto',

    'inverse' =>
        'bg-white text-ue-brand border-transparent ' .
        'hover:bg-ue-brand-soft hover:text-ue-brand ' .
        'active:bg-ue-brand-soft-hover active:text-ue-brand',

    default =>
        'bg-ue-brand text-white border-transparent ' .
        'hover:bg-ue-brand-hover hover:text-white ' .
        'active:bg-ue-brand-active active:text-white',
};

$isDisabled   = $disabled || $loading;
$loadingLabel = $loading ? 'true' : null;
@endphp

@if($href)
<a
    href="{{ $href }}"
    @if($isDisabled) aria-disabled="true" @endif
    {{ $attributes->class([
        /* Base */
        'inline-flex items-center justify-center font-semibold leading-snug',
        'select-none whitespace-nowrap border rounded-lg',
        'transition-colors duration-sm ease-out',
        'ue-focus-ring',
        /* Minimum touch target */
        'min-h-touch',
        /* Size */
        $sizeClasses,
        /* Variant */
        $variantClasses,
        /* Disabled state */
        'opacity-50 cursor-not-allowed pointer-events-none' => $isDisabled,
    ]) }}
>
    @if($icon && $iconPosition === 'left')
        <x-ui.icon :name="$icon" :size="$iconSize" aria-hidden="true" />
    @endif

    <span>{{ $slot }}</span>

    @if($icon && $iconPosition === 'right')
        <x-ui.icon :name="$icon" :size="$iconSize" aria-hidden="true" />
    @endif
</a>
@else
<button
    type="{{ $type }}"
    {{ $isDisabled ? 'disabled' : '' }}
    @if($loading) aria-busy="true" @endif
    {{ $attributes->class([
        /* Base */
        'inline-flex items-center justify-center font-semibold leading-snug',
        'select-none whitespace-nowrap border rounded-lg',
        'transition-colors duration-sm ease-out',
        'ue-focus-ring',
        /* Minimum touch target */
        'min-h-touch',
        /* Size */
        $sizeClasses,
        /* Variant */
        $variantClasses,
        /* Disabled state */
        'opacity-50 cursor-not-allowed pointer-events-none' => $isDisabled,
        /* Loading cursor */
        'cursor-wait' => $loading && !$disabled,
    ]) }}
>
    {{-- Loading spinner (left side) --}}
    @if($loading)
        <span class="ue-spinner" aria-hidden="true"></span>
    @elseif($icon && $iconPosition === 'left')
        <x-ui.icon :name="$icon" :size="$iconSize" aria-hidden="true" />
    @endif

    {{-- Label --}}
    <span>{{ $slot }}</span>

    {{-- Right icon --}}
    @if(!$loading && $icon && $iconPosition === 'right')
        <x-ui.icon :name="$icon" :size="$iconSize" aria-hidden="true" />
    @endif
</button>
@endif
