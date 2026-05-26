{{--
    UEConnect Badge Component
    Source: docs/04-design/12-component-primitives.md, 13-component-variants.md §13 (badge/chip section)

    Usage:
        <x-ui.badge variant="verified">Đã xác thực</x-ui.badge>
        <x-ui.badge variant="pending">Đang chờ duyệt</x-ui.badge>
        <x-ui.badge variant="mentor">Mentor</x-ui.badge>
        <x-ui.badge variant="danger" icon="x-circle">Bị từ chối</x-ui.badge>

    Props:
        variant (string) verified|pending|rejected|need-more-info|student|alumni|
                         advisor|mentor|club|system|neutral|success|warning|danger|info
        size    (string) sm|md
        icon    (string) optional icon name
--}}

@props([
    'variant' => 'neutral',
    'size'    => 'md',
    'icon'    => null,
])

@php
$sizeClasses = match($size) {
    'sm' => 'text-2xs px-2 py-0.5 gap-0.5',
    'md' => 'text-xs px-2.5 py-1 gap-1',
    default => 'text-xs px-2.5 py-1 gap-1',
};

$iconSize = $size === 'sm' ? 'xs' : 'xs';

$variantClasses = match($variant) {
    /* UEConnect verification states */
    'verified' =>
        'bg-[var(--ue-brand-tint)] text-ue-brand border-[var(--ue-brand-border)]',

    'pending' =>
        'bg-[var(--warning-bg-soft)] text-[var(--warning-text)] border-[var(--warning-border)]',

    'rejected' =>
        'bg-[var(--danger-bg-soft)] text-[var(--danger-text)] border-[var(--danger-border)]',

    'need-more-info' =>
        'bg-[var(--warning-bg-soft)] text-[var(--warning-text)] border-[var(--warning-border)]',

    /* Role badges */
    'student' =>
        'bg-[var(--role-student-bg)] text-[var(--role-student-text)] border-[var(--ue-brand-border)]',

    'alumni' =>
        'bg-[var(--role-alumni-bg)] text-[var(--role-alumni-text)] border-transparent',

    'advisor' =>
        'bg-[var(--role-advisor-bg)] text-[var(--role-advisor-text)] border-transparent',

    'mentor' =>
        'bg-[var(--role-mentor-bg)] text-[var(--role-mentor-text)] border-[var(--mentor-border)]',

    'club' =>
        'bg-[var(--role-club-bg)] text-[var(--role-club-text)] border-transparent',

    'system', 'admin' =>
        'bg-[var(--role-admin-bg)] text-[var(--role-admin-text)] border-transparent',

    /* Semantic states */
    'success' =>
        'bg-[var(--success-bg-soft)] text-[var(--success-text)] border-[var(--success-border)]',

    'warning' =>
        'bg-[var(--warning-bg-soft)] text-[var(--warning-text)] border-[var(--warning-border)]',

    'danger' =>
        'bg-[var(--danger-bg-soft)] text-[var(--danger-text)] border-[var(--danger-border)]',

    'info' =>
        'bg-[var(--info-bg-soft)] text-[var(--info-text)] border-[var(--info-border)]',

    /* Neutral/default */
    default =>
        'bg-ue-surface-hover text-ue-text-secondary border-ue-border',
};

/** Auto-assign icon if none provided and variant implies one */
$autoIcon = match($variant) {
    'verified'      => 'shield-check',
    'pending'       => 'clock',
    'rejected'      => 'x-circle',
    'need-more-info'=> 'alert-circle',
    'success'       => 'check-circle',
    'warning'       => 'alert-triangle',
    'danger'        => 'x-circle',
    'info'          => 'info',
    'mentor'        => 'star',
    default         => null,
};

$displayIcon = $icon ?? $autoIcon;
@endphp

<span {{ $attributes->class([
    'ue-badge border font-semibold',
    $sizeClasses,
    $variantClasses,
]) }}>
    @if($displayIcon)
        <x-ui.icon :name="$displayIcon" :size="$iconSize" aria-hidden="true" />
    @endif
    {{ $slot }}
</span>
