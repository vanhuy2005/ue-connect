{{--
    UEConnect Verified Badge Component
    Source: docs/04-design/03-color-system.md §14.3

    A standalone "Đã xác thực" badge for verified HCMUE student accounts.
    Wraps x-ui.badge with verified variant.

    Usage:
        <x-brand.verified-badge />
        <x-brand.verified-badge size="sm" />
        <x-brand.verified-badge label="UEer đã xác thực" />
--}}

@props([
    'size'  => 'md',
    'label' => 'Đã xác thực',
])

<x-ui.badge variant="verified" :size="$size" {{ $attributes }}>
    {{ $label }}
</x-ui.badge>
