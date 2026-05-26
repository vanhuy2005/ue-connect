{{--
    UEConnect Field Error Component
    Source: docs/04-design/12-component-primitives.md §7

    Supports two modes:
        1. Named field (reads from Laravel session validation):
           <x-ui.field-error name="email" />

        2. Direct message:
           <x-ui.field-error message="Email không hợp lệ." />

        3. With id for aria-describedby:
           <x-ui.field-error name="email" id="email-error" />

    The rendered error text uses role="alert" so screen readers announce it.
--}}

@props([
    'name'    => null,
    'message' => null,
])

@if($name)
    @error($name)
        <p
            {{ $attributes->class([
                'flex items-center gap-1 mt-1',
                'text-xs font-medium text-ue-danger leading-snug',
            ]) }}
            role="alert"
        >
            <x-ui.icon name="alert-circle" size="xs" aria-hidden="true" class="flex-shrink-0" />
            <span>{{ $message }}</span>
        </p>
    @enderror
@elseif($message)
    <p
        {{ $attributes->class([
            'flex items-center gap-1 mt-1',
            'text-xs font-medium text-ue-danger leading-snug',
        ]) }}
        role="alert"
    >
        <x-ui.icon name="alert-circle" size="xs" aria-hidden="true" class="flex-shrink-0" />
        <span>{{ $message }}</span>
    </p>
@endif
