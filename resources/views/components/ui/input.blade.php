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
    'sm' => 'h-9 px-3 ue-text-input',
    'md' => 'h-11 px-3.5 ue-text-input',
    'lg' => 'h-13 px-4 ue-text-input',
    default => 'h-11 px-3.5 ue-text-input',
};

$hasError = $hasError || ($name && $errors->has($name));
@endphp

@if($type === 'password')
<div x-data="{ show: false }" class="relative w-full">
    <input
        @if($id) id="{{ $id }}" @endif
        @if($name) name="{{ $name }}" @endif
        :type="show ? 'text' : 'password'"
        @if($disabled) disabled @endif
        @if($readonly) readonly @endif
        @if($hasError && $id) aria-describedby="{{ $id }}-error" @endif
        @if($hasError) aria-invalid="true" @endif
        {{ $attributes->class([
            'ue-input block w-full pr-10',
            $sizeClasses,
            'ue-input--error' => $hasError,
            'cursor-not-allowed' => $disabled,
            'cursor-default bg-ue-surface-subtle' => $readonly && !$disabled,
        ]) }}
    />
    <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 pr-3 flex items-center text-ue-text-muted hover:text-ue-text transition-colors" tabindex="-1">
        <x-ui.icon name="eye" x-show="!show" class="w-4 h-4" />
        <x-ui.icon name="eye-off" x-show="show" class="w-4 h-4" x-cloak />
    </button>
</div>
@else
<input
    @if($id) id="{{ $id }}" @endif
    @if($name) name="{{ $name }}" @endif
    type="{{ $type }}"
    @if($disabled) disabled @endif
    @if($readonly) readonly @endif
    @if($hasError && $id) aria-describedby="{{ $id }}-error" @endif
    @if($hasError) aria-invalid="true" @endif
    {{ $attributes->class([
        'ue-input block w-full',
        $sizeClasses,
        'ue-input--error' => $hasError,
        'cursor-not-allowed' => $disabled,
        'cursor-default bg-ue-surface-subtle' => $readonly && !$disabled,
    ]) }}
/>
@endif
