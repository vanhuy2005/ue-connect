<?php

use Livewire\Volt\Component;

new class extends Component
{
    public array $result;
}; ?>

@php
    $typeLabels = [
        'course' => 'Môn học',
        'program' => 'Chương trình đào tạo',
        'position' => 'Vị trí nghề nghiệp',
        'senior_pathway' => 'Hành trình',
        'skill' => 'Kỹ năng',
        'contribution' => 'Tài nguyên',
    ];
@endphp

<a href="{{ $result['url'] ?? route('app.career-pathway.search') }}" wire:navigate.hover class="block rounded-2xl border border-ue-border bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:border-ue-brand/30 hover:shadow-md">
    <div class="flex items-start justify-between gap-3">
        <h3 class="text-sm font-extrabold leading-5 text-slate-900">{{ $result['title'] }}</h3>
        <span class="shrink-0 rounded-full bg-ue-brand-soft px-2 py-1 text-[11px] font-bold text-ue-brand-active">{{ $typeLabels[$result['type']] ?? 'Nội dung' }}</span>
    </div>

    @if(! empty($result['subtitle']))
        <p class="mt-2 text-xs font-bold text-slate-500">{{ $result['subtitle'] }}</p>
    @endif

    @if(! empty($result['description']))
        <p class="mt-3 line-clamp-3 text-xs font-medium leading-5 text-slate-500">{{ $result['description'] }}</p>
    @endif

    @if(! empty($result['badges']))
        <div class="mt-4 flex flex-wrap gap-2">
            @foreach(array_filter($result['badges']) as $badge)
                <span class="rounded-full bg-slate-100 px-2 py-1 text-[11px] font-bold text-slate-500">{{ $badge }}</span>
            @endforeach
        </div>
    @endif

    <div class="mt-4 inline-flex items-center gap-1 text-xs font-bold text-ue-brand-active">
        Mở chi tiết
        <x-ui.icon name="arrow-right" size="xs" />
    </div>
</a>
