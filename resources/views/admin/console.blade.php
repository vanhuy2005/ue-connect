<x-app-layout shell="admin">
    <x-slot name="title">{{ $groups[$selectedGroupKey]['vn_label'] ?? 'Bảng điều khiển quản trị' }}</x-slot>

    @php
        $selectedGroup = $groups[$selectedGroupKey];
        $totalItems = count($selectedGroup['items']);
    @endphp

    <div class="min-h-screen bg-[#f8fafc]">
        {{-- Banner & Title Section --}}
        <div class="bg-white border-b border-slate-100 py-8 shadow-sm relative overflow-hidden">
            {{-- Radial subtle background glow --}}
            <div class="absolute -right-20 -top-20 w-80 h-80 rounded-full bg-ue-brand/5 blur-3xl"></div>
            <div class="absolute -left-20 -bottom-20 w-80 h-80 rounded-full bg-cyan-400/5 blur-3xl"></div>

            <div class="mx-auto w-full max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between relative z-10">
                    <div>
                        {{-- Breadcrumb path --}}
                        <div class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-slate-400">
                            <span>Admin console</span>
                            <x-ui.icon name="chevron-right" size="xs" class="text-slate-350 w-3 h-3" />
                            <span class="text-ue-brand-active bg-ue-brand-soft px-2.5 py-0.5 rounded-full text-[10px]">{{ $selectedGroup['vn_label'] }}</span>
                        </div>

                        {{-- Main title and description --}}
                        <div class="flex items-center gap-3 mt-3">
                            <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-tr from-ue-brand to-cyan-500 text-white shadow-md shadow-ue-brand/10">
                                <x-ui.icon :name="$selectedGroup['icon']" size="md" />
                            </span>
                            <h1 class="text-2xl md:text-3xl font-extrabold tracking-tight text-slate-900">
                                {{ $selectedGroup['vn_label'] }}
                            </h1>
                        </div>
                        <p class="mt-2.5 max-w-3xl text-sm text-slate-500 leading-relaxed font-medium">
                            {{ $selectedGroup['description'] }}
                        </p>
                    </div>

                    {{-- Stats pill --}}
                    <div class="flex-shrink-0 self-start md:self-center">
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-50 px-3.5 py-1.5 text-xs font-bold text-slate-600 border border-slate-100 shadow-sm">
                            <span class="h-2 w-2 rounded-full bg-ue-brand animate-pulse"></span>
                            {{ $totalItems }} module khả dụng
                        </span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Grid of modules --}}
        <div class="mx-auto w-full max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            <section class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4" aria-label="Module trong {{ $selectedGroup['vn_label'] }}">
                @foreach($selectedGroup['items'] as $item)
                    <a href="{{ route($item['route']) }}" class="group flex min-h-[180px] flex-col justify-between rounded-2xl border border-slate-100 bg-white p-5 text-left transition-all duration-300 hover:-translate-y-1 hover:border-ue-brand/35 hover:shadow-xl hover:shadow-slate-150/40 focus:outline-none focus-visible:ring-2 focus-visible:ring-ue-brand/30">
                        <div>
                            <div class="flex items-start justify-between gap-3">
                                <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-slate-50 text-slate-500 border border-slate-100/50 transition-colors duration-300 group-hover:bg-ue-brand-soft group-hover:text-ue-brand-active group-hover:border-ue-brand/10">
                                    <x-ui.icon :name="$item['icon']" size="md" class="w-5 h-5" />
                                </span>
                                <span class="text-[9px] font-extrabold uppercase tracking-widest text-slate-400 bg-slate-50 border border-slate-100/30 px-2 py-0.5 rounded transition-colors duration-300 group-hover:bg-ue-brand-soft group-hover:text-ue-brand-active group-hover:border-ue-brand/10">{{ $item['key'] }}</span>
                            </div>

                            <h2 class="mt-4 text-base font-extrabold text-slate-900 group-hover:text-ue-brand-active transition-colors duration-200">{{ $item['label'] }}</h2>
                            <p class="mt-2 text-xs leading-relaxed text-slate-500 font-medium line-clamp-3">{{ $item['description'] }}</p>
                        </div>

                        <div class="mt-5 pt-3 border-t border-slate-55 flex items-center justify-between">
                            <span class="inline-flex items-center gap-1.5 text-xs font-bold text-ue-brand group-hover:text-cyan-600 transition-colors">
                                Mở module
                                <x-ui.icon name="arrow-right" size="xs" class="w-3.5 h-3.5 transition-transform duration-200 group-hover:translate-x-1" />
                            </span>
                            <span class="opacity-0 group-hover:opacity-100 transition-opacity duration-300 text-[10px] text-slate-400 font-semibold">Nhấp để truy cập</span>
                        </div>
                    </a>
                @endforeach
            </section>
        </div>
    </div>
</x-app-layout>
