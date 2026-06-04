<x-app-layout shell="admin">
    <x-slot name="title">{{ $groups[$selectedGroupKey]['vn_label'] ?? 'Bảng điều khiển quản trị' }}</x-slot>

    @php
        $selectedGroup = $groups[$selectedGroupKey];
    @endphp

    <div class="min-h-full bg-slate-50/60">
        <div class="mx-auto w-full max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
            <div class="flex flex-col gap-4 border-b border-slate-200 pb-5 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.18em] text-slate-500">Admin console</p>
                    <h1 class="mt-2 text-2xl font-bold tracking-tight text-slate-950">{{ $selectedGroup['vn_label'] }}</h1>
                    <p class="mt-1 max-w-2xl text-sm leading-6 text-slate-600">{{ $selectedGroup['description'] }}</p>
                </div>
            </div>

            <section class="mt-6 grid gap-3 sm:grid-cols-2 xl:grid-cols-4" aria-label="Module trong {{ $selectedGroup['vn_label'] }}">
                @foreach($selectedGroup['items'] as $item)
                    <a href="{{ route($item['route']) }}" class="group flex min-h-36 flex-col justify-between rounded-lg border border-slate-200 bg-white p-4 text-left transition hover:-translate-y-0.5 hover:border-ue-brand/50 hover:shadow-sm focus:outline-none focus-visible:ring-2 focus-visible:ring-ue-brand/30">
                        <div>
                            <div class="flex items-start justify-between gap-3">
                                <span class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-slate-100 text-slate-700 group-hover:bg-ue-brand-soft group-hover:text-ue-brand-active">
                                    <x-ui.icon :name="$item['icon']" size="sm" />
                                </span>
                                <span class="text-[10px] font-bold uppercase tracking-[0.16em] text-slate-400">{{ $item['key'] }}</span>
                            </div>

                            <h2 class="mt-4 text-base font-bold text-slate-950">{{ $item['label'] }}</h2>
                            <p class="mt-2 text-sm leading-5 text-slate-600">{{ $item['description'] }}</p>
                        </div>

                        <span class="mt-5 inline-flex items-center gap-1 text-sm font-semibold text-ue-brand-active">
                            Mở module
                            <x-ui.icon name="arrow-right" size="xs" />
                        </span>
                    </a>
                @endforeach
            </section>
        </div>
    </div>
</x-app-layout>
