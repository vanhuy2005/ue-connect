{{--
    UEConnect Card Component
    Source: docs/04-design/12-component-primitives.md §14, 13-component-variants.md §13

    Usage:
        <x-ui.card>Content</x-ui.card>
        <x-ui.card variant="interactive" as="article">Clickable card</x-ui.card>
        <x-ui.card variant="elevated" padding="lg">Auth form container</x-ui.card>

        With named slots:
        <x-ui.card>
            <x-slot:header>Card title</x-slot:header>
            Body content
            <x-slot:footer>Footer actions</x-slot:footer>
        </x-ui.card>

    Props:
        variant  (string) default|interactive|elevated|outlined|soft|danger|success|warning|admin
        padding  (string) none|sm|md|lg
        as       (string) div|article|section|li (default: div)
--}}

@props([
    'variant' => 'default',
    'padding' => 'md',
    'as'      => 'div',
])

@php
$paddingClasses = match($padding) {
    'none' => '',
    'sm'   => 'p-3',
    'md'   => 'p-4 sm:p-5',
    'lg'   => 'p-5 sm:p-6',
    default => 'p-4 sm:p-5',
};

$variantClasses = match($variant) {
    'default' =>
        'ue-card shadow-sm',

    'interactive' =>
        'ue-card shadow-sm cursor-pointer ' .
        'hover:shadow-md hover:border-ue-border-strong ' .
        'transition-shadow duration-md ease-out ' .
        'focus-within:ring-2 focus-within:ring-[var(--ue-border-focus)]',

    'elevated' =>
        'bg-ue-surface border border-ue-border rounded-xl shadow-md',

    'outlined' =>
        'bg-transparent border border-ue-border rounded-xl shadow-none',

    'soft' =>
        'bg-ue-surface-subtle border border-ue-border-subtle rounded-xl shadow-none',

    'danger' =>
        'bg-[var(--danger-bg-soft)] border border-[var(--danger-border)] rounded-xl shadow-none',

    'success' =>
        'bg-[var(--success-bg-soft)] border border-[var(--success-border)] rounded-xl shadow-none',

    'warning' =>
        'bg-[var(--warning-bg-soft)] border border-[var(--warning-border)] rounded-xl shadow-none',

    'admin' =>
        'bg-ue-surface border border-ue-border rounded-lg shadow-sm',

    default =>
        'ue-card shadow-sm',
};
@endphp

<{{ $as }} {{ $attributes->class([$variantClasses]) }}>
    @if(isset($header))
        <div class="px-4 sm:px-5 py-3 border-b border-ue-border">
            {{ $header }}
        </div>
    @endif

    @if($padding !== 'none')
        <div class="{{ $paddingClasses }}">
            {{ $slot }}
        </div>
    @else
        {{ $slot }}
    @endif

    @if(isset($footer))
        <div class="px-4 sm:px-5 py-3 border-t border-ue-border bg-ue-surface-subtle rounded-b-xl">
            {{ $footer }}
        </div>
    @endif
</{{ $as }}>
