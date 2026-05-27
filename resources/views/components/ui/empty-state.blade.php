{{--
    UEConnect Empty State Component
    Source: docs/04-design/12-component-primitives.md §EmptyState

    Usage:
        <x-ui.empty-state
            icon="inbox"
            title="Chưa có nội dung"
            description="Khi có hoạt động mới, nội dung sẽ xuất hiện tại đây."
        />

        <x-ui.empty-state
            icon="users"
            title="Chưa có kết nối"
            description="Hãy bắt đầu bằng cách khám phá các UEers."
            action-label="Khám phá UEers"
            action-href="#"
        />

    Props:
        icon        (string) — icon name
        title       (string) — primary message
        description (string) — secondary helper text
        actionLabel (string) — CTA button text
        actionHref  (string) — CTA link target
--}}

@props([
    'icon'        => 'inbox',
    'title'       => '',
    'description' => '',
    'actionLabel' => null,
    'actionHref'  => null,
])

<div {{ $attributes->class(['flex flex-col items-center justify-center text-center py-12 px-4']) }}>
    {{-- Icon container --}}
    <div class="w-16 h-16 rounded-2xl bg-ue-brand-soft flex items-center justify-center mb-4">
        <x-ui.icon :name="$icon" size="2xl" class="text-ue-brand opacity-60" aria-hidden="true" />
    </div>

    {{-- Title --}}
    @if($title)
        <h3 class="text-lg font-semibold text-ue-text mb-2 leading-snug">
            {{ $title }}
        </h3>
    @endif

    {{-- Description --}}
    @if($description)
        <p class="text-sm text-ue-text-muted leading-relaxed max-w-xs">
            {{ $description }}
        </p>
    @endif

    {{-- Custom slot content --}}
    @if($slot->isNotEmpty())
        <div class="mt-4">{{ $slot }}</div>
    @endif

    {{-- CTA action --}}
    @if($actionLabel && $actionHref)
        <div class="mt-5">
            <x-ui.button
                variant="primary"
                size="md"
                tag="a"
                href="{{ $actionHref }}"
            >
                {{ $actionLabel }}
            </x-ui.button>
        </div>
    @endif
</div>
