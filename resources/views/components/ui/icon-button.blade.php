{{--
    UEConnect Icon Button Component
    Source: docs/04-design/12-component-primitives.md §5, 13-component-variants.md §5

    Usage:
        <x-ui.icon-button icon="more-horizontal" label="Mở menu" />
        <x-ui.icon-button icon="x" label="Đóng" variant="ghost" />
        <x-ui.icon-button icon="trash" label="Xóa tin nhắn" variant="danger" />
        <x-ui.icon-button icon="bookmark" label="Đã lưu" href="{{ route('posts.saved') }}" />

    Props:
        icon     (string, required) — icon name for x-ui.icon
        label    (string, required) — aria-label (accessibility requirement)
        variant  (string) ghost|soft|outline|brand|danger|inverse
        size     (string) sm|md|lg
        disabled (bool)
        type     (string) button|submit|reset
        href     (string, optional) — if provided, renders as anchor <a> element
--}}

@props([
    'icon'     => 'more-horizontal',
    'label'    => '',
    'variant'  => 'ghost',
    'size'     => 'md',
    'disabled' => false,
    'type'     => 'button',
    'href'     => null,
])

@php
$sizeClasses = match($size) {
    'sm' => 'w-8 h-8',
    'md' => 'w-10 h-10',
    'lg' => 'w-11 h-11',
    default => 'w-10 h-10',
};

$iconSize = match($size) {
    'sm'    => 'sm',
    'lg'    => 'lg',
    default => 'md',
};

$variantClasses = match($variant) {
    'ghost' =>
        'bg-transparent text-ue-text-secondary border-transparent ' .
        'hover:bg-ue-brand-soft hover:text-ue-brand-active ' .
        'active:bg-ue-brand-soft-hover active:text-ue-brand-active',

    'soft' =>
        'bg-ue-surface-hover text-ue-text-secondary border-transparent ' .
        'hover:bg-ue-surface-pressed hover:text-ue-text ' .
        'active:bg-ue-surface-pressed active:text-ue-text',

    'outline' =>
        'bg-white text-ue-text border border-ue-border ' .
        'hover:bg-ue-brand-soft hover:border-ue-brand hover:text-ue-brand-active ' .
        'active:bg-ue-brand-soft-hover active:text-ue-brand-active',

    'brand' =>
        'bg-ue-brand-soft text-ue-brand border-transparent ' .
        'hover:bg-ue-brand-soft-hover hover:text-ue-brand-active ' .
        'active:bg-ue-brand-soft-hover active:text-ue-brand-active',

    'danger' =>
        'bg-transparent text-ue-danger border border-transparent ' .
        'hover:bg-red-50 hover:text-red-700 ' .
        'active:bg-red-100 active:text-red-800',

    'inverse' =>
        'bg-white/20 text-white border-transparent ' .
        'hover:bg-white/30 hover:text-white ' .
        'active:bg-white/40 active:text-white',

    default =>
        'bg-transparent text-ue-text-secondary border-transparent ' .
        'hover:bg-ue-brand-soft hover:text-ue-brand-active ' .
        'active:bg-ue-brand-soft-hover active:text-ue-brand-active',
};

$sharedClasses = [
    'inline-flex items-center justify-center flex-shrink-0',
    'rounded-lg border',
    'transition-colors duration-sm ease-out',
    'ue-focus-ring',
    /* Min touch target */
    'min-h-touch min-w-touch',
    $sizeClasses,
    $variantClasses,
];
@endphp

@if ($href)
    <a
        href="{{ $href }}"
        aria-label="{{ $label }}"
        {{ $attributes->class(array_merge($sharedClasses, [
            'opacity-50 cursor-not-allowed pointer-events-none' => $disabled,
        ])) }}
    >
        <x-ui.icon :name="$icon" :size="$iconSize" aria-hidden="true" />
    </a>
@else
    <button
        type="{{ $type }}"
        aria-label="{{ $label }}"
        {{ $disabled ? 'disabled' : '' }}
        {{ $attributes->class(array_merge($sharedClasses, [
            'opacity-50 cursor-not-allowed pointer-events-none' => $disabled,
        ])) }}
    >
        <x-ui.icon :name="$icon" :size="$iconSize" aria-hidden="true" />
    </button>
@endif
