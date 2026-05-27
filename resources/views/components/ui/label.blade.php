{{--
    UEConnect Label Component
    Source: docs/04-design/12-component-primitives.md §7

    Usage:
        <x-ui.label for="email">Email HCMUE</x-ui.label>
        <x-ui.label for="bio" :required="true">Giới thiệu bản thân</x-ui.label>
--}}

@props([
    'for'      => null,
    'required' => false,
])

<label
    @if($for) for="{{ $for }}" @endif
    {{ $attributes->class([
        'block text-md font-semibold text-ue-text leading-snug',
        'mb-1',
    ]) }}
>
    {{ $slot }}
    @if($required)
        <span class="text-ue-danger ml-0.5" aria-hidden="true">*</span>
    @endif
</label>
