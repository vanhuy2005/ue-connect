{{--
    UEConnect Input Component
    Source: docs/04-design/12-component-primitives.md §8, 13-component-variants.md §7

    Usage:
        <x-ui.input id="email" name="email" type="email" autocomplete="email" required />
        <x-ui.input name="search" type="search" placeholder="Tìm kiếm..." :hasError="false" />
        <x-ui.input name="bio" :hasError="$errors->has('bio')" size="lg" />

    Props:
        id       (string)  — input id
        name     (string)  — input name
        type     (string)  text|email|password|search|url|number|date (default: text)
        size     (string)  sm|md|lg (default: md)
        hasError (bool)    — apply error styling
        disabled (bool)
        readonly (bool)

    All other HTML attributes are forwarded (placeholder, autocomplete, required, wire:model, etc.)
--}}

@props([
    'id'       => null,
    'name'     => null,
    'type'     => 'text',
    'size'     => 'md',
    'hasError' => false,
    'disabled' => false,
    'readonly' => false,
])

@php
$sizeClasses = match($size) {
    'sm' => 'h-9 px-3 text-md',
    'md' => 'h-11 px-3.5 text-base',
    'lg' => 'h-13 px-4 text-lg',
    default => 'h-11 px-3.5 text-base',
};

$hasError = $hasError || ($name && $errors->has($name));
@endphp

<input
    @if($id) id="{{ $id }}" @endif
    @if($name) name="{{ $name }}" @endif
    type="{{ $type }}"
    @if($disabled) disabled @endif
    @if($readonly) readonly @endif
    @if($hasError && $id) aria-describedby="{{ $id }}-error" @endif
    @if($hasError) aria-invalid="true" @endif
    {{ $attributes->class([
        'ue-input block',
        $sizeClasses,
        'ue-input--error' => $hasError,
        'cursor-not-allowed' => $disabled,
        'cursor-default bg-ue-surface-subtle' => $readonly && !$disabled,
    ]) }}
/>
