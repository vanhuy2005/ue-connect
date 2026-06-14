@props([
    'title',
    'subtitle' => null,
    'eyebrow' => 'Career Pathway',
    'actionHref' => null,
    'actionLabel' => null,
    'actionIcon' => 'arrow-right',
])

<div data-career-pathway-shell class="min-h-full bg-white">
    <div class="flex w-full flex-col bg-white lg:flex-row">
        <x-career-pathway.sub-sidebar />

        <section class="min-w-0 flex-1 bg-white px-4 py-5 sm:px-6 lg:px-8 lg:py-8">
            <div class="mx-auto flex w-full max-w-6xl flex-col gap-6">
                <header class="rounded-2xl border border-ue-border/80 bg-white px-5 py-5 shadow-sm sm:px-6">
                    <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
                        <div class="max-w-3xl">
                            <p class="text-xs font-bold uppercase tracking-wider text-ue-brand-active">{{ $eyebrow }}</p>
                            <h1 class="mt-2 text-2xl font-extrabold tracking-tight text-slate-900 sm:text-3xl">{{ $title }}</h1>
                            @if($subtitle)
                                <p class="mt-2 max-w-3xl text-sm font-medium leading-6 text-slate-500">{{ $subtitle }}</p>
                            @endif
                        </div>

                        @if($actionHref && $actionLabel)
                            <a
                                href="{{ $actionHref }}"
                                wire:navigate.hover
                                class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-ue-brand px-4 py-2.5 text-sm font-bold text-white shadow-sm shadow-sky-900/10 transition hover:bg-ue-brand-active active:translate-y-px sm:w-auto"
                            >
                                <x-ui.icon :name="$actionIcon" size="sm" />
                                <span>{{ $actionLabel }}</span>
                            </a>
                        @endif
                    </div>
                </header>

                <div class="min-w-0">
                    {{ $slot }}
                </div>
            </div>
        </section>
    </div>
</div>
