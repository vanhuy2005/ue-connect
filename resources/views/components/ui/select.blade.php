{{--
    UEConnect Select Component
    Source: docs/04-design/12-component-primitives.md §10, 13-component-variants.md §9

    Usage:
        <x-ui.select name="faculty" id="faculty">
            <option value="">Chọn khoa</option>
            <option value="cs">Công nghệ thông tin</option>
        </x-ui.select>

    Props:
        name     (string)
        id       (string)
        size     (string) sm|md|lg
        hasError (bool)
        disabled (bool)
--}}

@props([
    'name'     => null,
    'id'       => null,
    'size'     => 'md',
    'hasError' => false,
    'disabled' => false,
])

@php
$hasError = $hasError || ($name && $errors->has($name));

$sizeClasses = match($size) {
    'sm' => 'h-9 pl-3 pr-8 text-md',
    'md' => 'h-11 pl-3.5 pr-9 text-base',
    'lg' => 'h-13 pl-4 pr-10 text-lg',
    default => 'h-11 pl-3.5 pr-9 text-base',
};
@endphp

<div class="relative">
    <select
        @if($id) id="{{ $id }}" @endif
        @if($name) name="{{ $name }}" @endif
        @if($disabled) disabled @endif
        @if($hasError && $id) aria-describedby="{{ $id }}-error" @endif
        @if($hasError) aria-invalid="true" @endif
        {{ $attributes->class([
            /* Base */
            'ue-input block w-full appearance-none cursor-pointer',
            $sizeClasses,
            /* Error */
            'ue-input--error' => $hasError,
            /* Disabled */
            'cursor-not-allowed' => $disabled,
        ]) }}
    >
        {{ $slot }}
    </select>

    {{-- Chevron icon --}}
    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-ue-text-muted">
        <x-ui.icon name="chevron-down" size="sm" aria-hidden="true" />
    </div>
</div>
