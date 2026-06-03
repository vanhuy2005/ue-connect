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

                <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-700 hover:border-slate-300 hover:bg-slate-50">
                    <x-ui.icon name="arrow-left" size="sm" />
                    Quay lại ứng dụng
                </a>
            </div>

            <nav class="mt-5 flex gap-2 overflow-x-auto pb-1" aria-label="Danh mục quản trị">
                @foreach($groups as $groupKey => $group)
                    @php $isSelected = $groupKey === $selectedGroupKey; @endphp
                    <a
                        href="{{ route('admin.console', ['group' => $groupKey]) }}"
                        class="inline-flex shrink-0 items-center gap-2 rounded-lg border px-3 py-2 text-sm font-semibold transition-colors {{ $isSelected ? 'border-ue-brand bg-white text-ue-brand-active shadow-sm' : 'border-slate-200 bg-white text-slate-600 hover:border-slate-300 hover:text-slate-950' }}"
                        @if($isSelected) aria-current="page" @endif
                    >
                        <x-ui.icon :name="$group['icon']" size="sm" />
                        {{ $group['vn_label'] }}
                        <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-bold text-slate-500">{{ count($group['items']) }}</span>
                    </a>
                @endforeach
            </nav>

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
