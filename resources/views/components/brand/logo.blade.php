{{--
    UEConnect Logo Component
    Source: docs/04-design/11-logo-usage-system.md, 19-design-token-documentation.md §12

    Usage:
        <x-brand.logo />
        <x-brand.logo variant="mark" size="sm" />
        <x-brand.logo variant="horizontal" tone="white" size="lg" />

    Props:
        variant (string) horizontal|mark|wordmark (default: horizontal)
        tone    (string) default|white|mono (default: default)
        size    (string) sm|md|lg (default: md)

    Brand asset paths (copied to public/images/brand/ from docs/04-design/):
        horizontal: public/images/brand/ueconnect-logo-horizontal.png
        mark:       public/images/brand/ueconnect-mark.png
        primary:    public/images/brand/ueconnect-logo.png

    Fallback: If image is missing, renders text-based logo mark.
--}}

@props([
    'variant' => 'horizontal',
    'tone'    => 'default',
    'size'    => 'md',
])

@php
/** Height tokens from logo usage system */
$heightClass = match([$variant, $size]) {
    ['mark', 'sm']  => 'h-6 w-6',
    ['mark', 'md']  => 'h-8 w-8',
    ['mark', 'lg']  => 'h-12 w-12',
    ['horizontal', 'sm'] => 'h-7',
    ['horizontal', 'md'] => 'h-8',
    ['horizontal', 'lg'] => 'h-10',
    ['wordmark', 'sm']   => 'h-6',
    ['wordmark', 'md']   => 'h-8',
    ['wordmark', 'lg']   => 'h-10',
    default => 'h-8',
};

$assetPath = match($variant) {
    'mark'      => asset('images/brand/ueconnect-mark.png'),
    'wordmark'  => asset('images/brand/ueconnect-logo.png'),
    default     => asset('images/brand/ueconnect-logo-horizontal.png'),
};

/**
 * Check if the public asset file exists.
 * Falls back to text mark if image is missing (prevents 404 in layout).
 */
$assetFile = match($variant) {
    'mark'     => public_path('images/brand/ueconnect-mark.png'),
    'wordmark' => public_path('images/brand/ueconnect-logo.png'),
    default    => public_path('images/brand/ueconnect-logo-horizontal.png'),
};

$hasAsset = file_exists($assetFile);

/** Tonal filter for white/mono variants (CSS filter approach) */
$toneStyle = match($tone) {
    'white' => 'filter: brightness(0) invert(1);',
    'mono'  => 'filter: grayscale(1);',
    default => '',
};

$textColor = match($tone) {
    'white' => 'text-white',
    'mono'  => 'text-ue-neutral-700',
    default => 'text-ue-brand',
};
@endphp

@if($hasAsset)
    <img
        src="{{ $assetPath }}"
        alt="UEConnect - Kết nối cộng đồng HCMUE"
        {{ $attributes->class([$heightClass, 'object-contain']) }}
        @if($toneStyle) style="{{ $toneStyle }}" @endif
        loading="eager"
        decoding="async"
    />
@else
    {{--
        Text fallback when brand image is not yet available.
        This prevents broken images in layout.
        TODO: Replace with confirmed asset path when final logo is committed.
    --}}
    <span
        {{ $attributes->class([
            'inline-flex items-center gap-2 font-bold leading-none select-none',
            $textColor,
            match($size) {
                'sm' => 'text-base',
                'lg' => 'text-2xl',
                default => 'text-xl',
            },
        ]) }}
        role="img"
        aria-label="UEConnect - Kết nối cộng đồng HCMUE"
    >
        @if($variant !== 'wordmark')
            {{-- Icon mark fallback: blue square with "U" --}}
            <span
                class="inline-flex items-center justify-center rounded-lg bg-ue-brand text-white font-bold {{ match($size) { 'sm' => 'w-6 h-6 text-xs', 'lg' => 'w-10 h-10 text-lg', default => 'w-8 h-8 text-sm' } }}"
                aria-hidden="true"
            >U</span>
        @endif

        @if($variant !== 'mark')
            <span>UEConnect</span>
        @endif
    </span>
@endif
