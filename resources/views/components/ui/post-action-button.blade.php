@props([
    'icon',
    'activeIcon' => null,
    'label',
    'count' => null,
    'selected' => false,
    'loading' => false,
    'disabled' => false,
    'danger' => false,
    'wireClick' => null,
])

<button
    type="button"
    @if($wireClick) wire:click="{{ $wireClick }}" @endif
    @if($disabled || $loading) disabled @endif
    aria-pressed="{{ $selected ? 'true' : 'false' }}"
    aria-label="{{ $label }}{{ $count !== null ? ': ' . $count : '' }}"
    {{ $attributes->class([
        'ue-action-button',
        'ue-action-button--selected' => $selected,
        'ue-action-button--loading' => $loading,
        'ue-action-button--danger' => $danger,
    ]) }}
    @if(!$loading && !$disabled && $wireClick)
        onclick="if (window.UEOptimistic) { window.UEOptimistic.toggle(this, { selectedClass: 'ue-action-button--selected' }); }"
    @endif
>
    @php
        $currentIcon = ($selected && $activeIcon) ? $activeIcon : $icon;
    @endphp
    
    @if($loading)
        <span class="ue-spinner text-current" aria-hidden="true"></span>
    @else
        <x-ui.icon :name="$currentIcon" size="md" class="ue-action-button__icon" />
    @endif
    
    @if($count !== null)
        <span class="ue-action-button__count" data-count>{{ $count }}</span>
    @else
        <span class="ue-action-button__label ml-1 hidden sm:inline">{{ $label }}</span>
    @endif
</button>
