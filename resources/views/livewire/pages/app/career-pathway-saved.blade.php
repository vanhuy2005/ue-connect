<?php

use Livewire\Volt\Component;

new class extends Component
{
}; ?>

<x-career-pathway.shell
    title="Đã lưu"
    subtitle="Theo dõi các môn học, vị trí nghề nghiệp và hành trình bạn muốn quay lại sau."
>
    <section class="rounded-2xl border border-dashed border-ue-border bg-white p-6 shadow-sm">
        <div class="max-w-2xl">
            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-ue-brand-soft text-ue-brand-active">
                <x-ui.icon name="bookmark" size="md" />
            </div>
            <h2 class="mt-4 text-base font-extrabold text-slate-900">Chưa có mục Career Pathway nào được lưu</h2>
            <p class="mt-2 text-sm font-medium leading-6 text-slate-500">
                Khi bạn lưu một vị trí nghề nghiệp hoặc hành trình anh/chị khóa trước, các mục đó sẽ nằm ở đây để bạn tiếp tục so sánh với chương trình học của mình.
            </p>
            <div class="mt-5 flex flex-wrap gap-3">
                <a href="{{ route('app.career-pathway.positions.index') }}" wire:navigate.hover class="inline-flex items-center gap-2 rounded-xl bg-ue-brand px-4 py-2 text-sm font-bold text-white transition hover:bg-ue-brand-active">
                    Khám phá vị trí nghề nghiệp
                    <x-ui.icon name="arrow-right" size="sm" />
                </a>
                <a href="{{ route('app.career-pathway.senior-pathways.index') }}" wire:navigate.hover class="inline-flex items-center gap-2 rounded-xl border border-ue-border px-4 py-2 text-sm font-bold text-slate-700 transition hover:bg-slate-50">
                    Đọc hành trình khóa trước
                </a>
            </div>
        </div>
    </section>
</x-career-pathway.shell>
