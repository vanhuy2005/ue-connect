{{--
    UEConnect Icon Button Component
    Source: docs/04-design/12-component-primitives.md §5, 13-component-variants.md §5

    Usage:
        <x-ui.icon-button icon="more-horizontal" label="Mở menu" />
        <x-ui.icon-button icon="x" label="Đóng" variant="ghost" />
        <x-ui.icon-button icon="trash" label="Xóa tin nhắn" variant="danger" />

    Props:
        icon     (string, required) — icon name for x-ui.icon
        label    (string, required) — aria-label (accessibility requirement)
        variant  (string) ghost|soft|outline|brand|danger|inverse
        size     (string) sm|md|lg
        disabled (bool)
--}}

@props([
    'icon'     => 'more-horizontal',
    'label'    => '',
    'variant'  => 'ghost',
    'size'     => 'md',
    'disabled' => false,
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
        'hover:bg-ue-surface-hover hover:text-ue-text',

    'soft' =>
        'bg-ue-surface-hover text-ue-text-secondary border-transparent ' .
        'hover:bg-ue-surface-pressed hover:text-ue-text',

    'outline' =>
        'bg-transparent text-ue-text border border-ue-border ' .
        'hover:bg-ue-surface-hover hover:border-ue-border-strong',

    'brand' =>
        'bg-ue-brand-soft text-ue-brand border-transparent ' .
        'hover:bg-ue-brand-soft-hover',

    'danger' =>
        'bg-transparent text-ue-danger border-transparent ' .
        'hover:bg-[rgba(220,38,38,0.08)] hover:text-[#B91C1C]',

    'inverse' =>
        'bg-white/20 text-white border-transparent ' .
        'hover:bg-white/30',

    default =>
        'bg-transparent text-ue-text-secondary border-transparent ' .
        'hover:bg-ue-surface-hover hover:text-ue-text',
};
@endphp

<button
    type="button"
    aria-label="{{ $label }}"
    {{ $disabled ? 'disabled' : '' }}
    {{ $attributes->class([
        'inline-flex items-center justify-center flex-shrink-0',
        'rounded-lg border',
        'transition-colors duration-sm ease-out',
        'ue-focus-ring',
        /* Min touch target */
        'min-h-touch min-w-touch',
        $sizeClasses,
        $variantClasses,
        'opacity-50 cursor-not-allowed pointer-events-none' => $disabled,
    ]) }}
>
    <x-ui.icon :name="$icon" :size="$iconSize" aria-hidden="true" />
</button>
