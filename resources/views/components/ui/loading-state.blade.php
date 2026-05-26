{{--
    UEConnect Loading State Component
    Source: docs/04-design/12-component-primitives.md §EmptyState/Skeleton/Spinner

    Usage:
        <x-ui.loading-state />
        <x-ui.loading-state variant="spinner" message="Đang tải..." />
        <x-ui.loading-state variant="skeleton" />
        <x-ui.loading-state variant="dots" size="sm" />

    Props:
        variant (string) spinner|skeleton|dots (default: spinner)
        size    (string) sm|md|lg (default: md)
        message (string) optional loading message
--}}

@props([
    'variant' => 'spinner',
    'size'    => 'md',
    'message' => null,
])

@php
$spinnerSize = match($size) {
    'sm' => 'w-5 h-5',
    'lg' => 'w-8 h-8',
    default => 'w-6 h-6',
};
@endphp

<div
    {{ $attributes->class(['flex flex-col items-center justify-center gap-3 py-8 px-4']) }}
    role="status"
    aria-label="{{ $message ?? 'Đang tải...' }}"
    aria-live="polite"
>
    @switch($variant)
        @case('skeleton')
            {{-- Skeleton lines --}}
            <div class="w-full space-y-3" aria-hidden="true">
                <div class="ue-skeleton h-4 w-3/4 rounded"></div>
                <div class="ue-skeleton h-4 w-full rounded"></div>
                <div class="ue-skeleton h-4 w-5/6 rounded"></div>
                <div class="ue-skeleton h-4 w-2/3 rounded"></div>
            </div>
            @break

        @case('dots')
            {{-- Three bouncing dots --}}
            <div class="flex gap-1.5" aria-hidden="true">
                @foreach([0, 1, 2] as $i)
                    <span
                        class="w-2 h-2 bg-ue-brand rounded-full animate-bounce"
                        style="animation-delay: {{ $i * 150 }}ms"
                    ></span>
                @endforeach
            </div>
            @break

        @default
            {{-- Spinner --}}
            <span
                class="ue-spinner text-ue-brand {{ $spinnerSize }}"
                aria-hidden="true"
            ></span>
    @endswitch

    @if($message)
        <p class="text-sm text-ue-text-muted">{{ $message }}</p>
    @else
        <span class="sr-only">Đang tải...</span>
    @endif
</div>
