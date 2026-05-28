{{--
    UEConnect Brand Logo Component
    Source: docs/04-design/11-logo-usage-system.md

    Usage:
        <x-brand.logo variant="horizontal" size="md" />
        <x-brand.logo variant="primary" size="lg" alt="UEConnect - Kết nối cộng đồng HCMUE" />
        <x-brand.logo variant="mark" size="sm" />

    Props:
        variant (string) mark|horizontal|primary|app-icon (default: horizontal)
        size    (string) xs|sm|md|lg|xl (default: md)
        alt     (string) override alt text
        tone    (string) default|white|mono (default: default, for compatibility)
--}}

@props([
    'variant' => 'horizontal',
    'size'    => 'md',
    'alt'     => null,
    'tone'    => 'default',
])

@php
/** Select canonical file name based on variant */
$assetFile = match($variant) {
    'mark'     => 'ueconnect-mark-nobg.png',
    'primary'  => 'primary-logo-nobg.png',
    'app-icon' => 'app-icon-nobg.png',
    default    => 'horizontal-nobg.png', // horizontal
};

$assetPath = asset('images/brand/' . $assetFile);

/** Recommended sizing maps */
$sizeClass = match([$variant, $size]) {
    ['mark', 'xs'] => 'h-4 w-4',
    ['mark', 'sm'] => 'h-5 w-5',
    ['mark', 'md'] => 'h-6 w-6',
    ['mark', 'lg'] => 'h-8 w-8',
    ['mark', 'xl'] => 'h-12 w-12',

    ['horizontal', 'xs'] => 'h-4',
    ['horizontal', 'sm'] => 'h-5',
    ['horizontal', 'md'] => 'h-7',
    ['horizontal', 'lg'] => 'h-9',
    ['horizontal', 'xl'] => 'h-12',

    ['primary', 'sm'] => 'h-10',
    ['primary', 'md'] => 'h-14',
    ['primary', 'lg'] => 'h-20',
    ['primary', 'xl'] => 'h-28',

    ['app-icon', 'sm'] => 'h-8 w-8',
    ['app-icon', 'md'] => 'h-12 w-12',
    ['app-icon', 'lg'] => 'h-16 w-16',
    ['app-icon', 'xl'] => 'h-24 w-24',

    default => match($variant) {
        'mark'     => 'h-6 w-6',
        'primary'  => 'h-14',
        'app-icon' => 'h-12 w-12',
        default    => 'h-7',
    },
};

/** Accessibility Alt Text Policies */
$defaultAlt = match($variant) {
    'primary'  => 'UEConnect - Kết nối cộng đồng HCMUE',
    'app-icon' => 'UEConnect app icon',
    default    => 'UEConnect',
};
$displayAlt = $alt ?? $defaultAlt;

/** Tonal compatibility classes */
$toneStyle = match($tone) {
    'white' => 'filter: brightness(0) invert(1);',
    'mono'  => 'filter: grayscale(1);',
    default => '',
};

/** Determine loading strategy (Navbar and above-the-fold hero are eager, other areas lazy) */
$loadingStrategy = ($variant === 'horizontal' && ($size === 'md' || $size === 'sm')) ? 'eager' : 'lazy';
@endphp

<img
    src="{{ $assetPath }}"
    alt="{{ $displayAlt }}"
    {{ $attributes->class([$sizeClass, 'object-contain']) }}
    @if($toneStyle) style="{{ $toneStyle }}" @endif
    loading="{{ $loadingStrategy }}"
    decoding="async"
/>
