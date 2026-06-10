{{--
    UEConnect Textarea Component
    Source: docs/04-design/12-component-primitives.md §9, 13-component-variants.md §8

    Usage:
        <x-ui.textarea name="bio" placeholder="Giới thiệu bản thân..." />
        <x-ui.textarea name="greeting" variant="composer" :maxlength="500" :showCount="true" />
        <x-ui.textarea name="report" :hasError="true" rows="4" />

    Props:
        name       (string)
        id         (string)
        variant    (string) default|filled|composer (default: default)
        rows       (int)    default: 4
        maxlength  (int)    optional character limit
        showCount  (bool)   show character counter
        hasError   (bool)
        disabled   (bool)
        readonly   (bool)
--}}

@props([
    'name'      => null,
    'id'        => null,
    'variant'   => 'default',
    'rows'      => 4,
    'maxlength' => null,
    'showCount' => false,
    'hasError'  => false,
    'disabled'  => false,
    'readonly'  => false,
])

@php
$hasError = $hasError || ($name && $errors->has($name));

$variantClasses = match($variant) {
    'filled' =>
        'block w-full px-3.5 py-3 ue-text-input ' .
        'bg-ue-surface-subtle border border-ue-border rounded-xl ' .
        'text-ue-text placeholder-ue-text-disabled ' .
        'transition-colors duration-sm ease-out ' .
        'hover:border-ue-border-strong ' .
        'focus:outline-none focus:border-[var(--ue-border-focus)] focus:shadow-focus ' .
        'resize-y',

    'composer' =>
        'block w-full px-4 py-3 ue-text-input ' .
        'bg-ue-surface-subtle border border-transparent rounded-2xl ' .
        'text-ue-text placeholder-ue-text-disabled ' .
        'transition-colors duration-sm ease-out ' .
        'focus:outline-none focus:border-ue-border focus:bg-ue-surface ' .
        'resize-none',

    default =>
        'ue-input block w-full px-3.5 py-3 ue-text-input resize-y',
};

$errorClasses = $hasError ? 'border-[var(--danger)] focus:shadow-focus-danger' : '';
$disabledClasses = $disabled ? 'opacity-60 cursor-not-allowed resize-none' : '';
@endphp

<div class="relative">
    <textarea
        @if($id) id="{{ $id }}" @endif
        @if($name) name="{{ $name }}" @endif
        rows="{{ $rows }}"
        @if($maxlength) maxlength="{{ $maxlength }}" @endif
        @if($disabled) disabled @endif
        @if($readonly) readonly @endif
        @if($hasError && $id) aria-describedby="{{ $id }}-error" @endif
        @if($hasError) aria-invalid="true" @endif
        @if($maxlength && $showCount) x-data="{ count: $el?.value?.length ?? 0 }" x-on:input="count = $el.value.length" @endif
        {{ $attributes->class([
            $variantClasses,
            $errorClasses,
            $disabledClasses,
        ]) }}
    >{{ $slot }}</textarea>

    @if($maxlength && $showCount)
        <p class="mt-1 text-right text-xs text-ue-text-muted" aria-live="polite">
            <span x-text="count">0</span> / {{ $maxlength }}
        </p>
    @endif
</div>
