{{--
    UEConnect Alert Component
    Source: docs/04-design/12-component-primitives.md, 13-component-variants.md

    Usage:
        <x-ui.alert variant="info" title="Xác thực tài khoản">
            Vui lòng xác thực email HCMUE để sử dụng đầy đủ tính năng.
        </x-ui.alert>

        <x-ui.alert variant="danger" title="Không thể gửi">
            Vui lòng thử lại sau ít phút.
            <x-slot:actions>
                <x-ui.button variant="outline" size="sm">Thử lại</x-ui.button>
            </x-slot:actions>
        </x-ui.alert>

        <x-ui.alert variant="success" :dismissible="true">
            Đã lưu thay đổi thành công.
        </x-ui.alert>

    Props:
        variant     (string) info|success|warning|danger (default: info)
        title       (string) optional title
        dismissible (bool)   show close button (Alpine.js)
--}}

@props([
    'variant'     => 'info',
    'title'       => null,
    'dismissible' => false,
])

@php
$config = match($variant) {
    'success' => [
        'icon'        => 'check-circle',
        'bg'          => 'bg-[var(--success-bg-soft)]',
        'border'      => 'border-[var(--success-border)]',
        'text'        => 'text-[var(--success-text)]',
        'icon_color'  => 'text-[var(--success)]',
        'title_color' => 'text-[var(--success-text)]',
    ],
    'warning' => [
        'icon'        => 'alert-triangle',
        'bg'          => 'bg-[var(--warning-bg-soft)]',
        'border'      => 'border-[var(--warning-border)]',
        'text'        => 'text-[var(--warning-text)]',
        'icon_color'  => 'text-[var(--warning)]',
        'title_color' => 'text-[var(--warning-text)]',
    ],
    'danger' => [
        'icon'        => 'x-circle',
        'bg'          => 'bg-[var(--danger-bg-soft)]',
        'border'      => 'border-[var(--danger-border)]',
        'text'        => 'text-[var(--danger-text)]',
        'icon_color'  => 'text-[var(--danger)]',
        'title_color' => 'text-[var(--danger-text)]',
    ],
    default => [ // info
        'icon'        => 'info',
        'bg'          => 'bg-[var(--info-bg-soft)]',
        'border'      => 'border-[var(--info-border)]',
        'text'        => 'text-[var(--info-text)]',
        'icon_color'  => 'text-[var(--info)]',
        'title_color' => 'text-[var(--info-text)]',
    ],
};
@endphp

<div
    role="alert"
    @if($dismissible) x-data="{ visible: true }" x-show="visible" @endif
    {{ $attributes->class([
        'flex gap-3 p-4 rounded-xl border',
        $config['bg'],
        $config['border'],
    ]) }}
>
    {{-- Status icon --}}
    <div class="flex-shrink-0 mt-0.5">
        <x-ui.icon
            :name="$config['icon']"
            size="md"
            :class="$config['icon_color']"
            aria-hidden="true"
        />
    </div>

    {{-- Content --}}
    <div class="flex-1 min-w-0">
        @if($title)
            <p class="{{ $config['title_color'] }} text-md font-semibold leading-snug mb-1">
                {{ $title }}
            </p>
        @endif

        <div class="{{ $config['text'] }} text-sm leading-normal">
            {{ $slot }}
        </div>

        @if(isset($actions))
            <div class="mt-3 flex items-center gap-2">
                {{ $actions }}
            </div>
        @endif
    </div>

    {{-- Dismiss button --}}
    @if($dismissible)
        <div class="flex-shrink-0">
            <x-ui.icon-button
                icon="x"
                label="Đóng thông báo"
                variant="ghost"
                size="sm"
                x-on:click="visible = false"
                :class="$config['text']"
            />
        </div>
    @endif
</div>
