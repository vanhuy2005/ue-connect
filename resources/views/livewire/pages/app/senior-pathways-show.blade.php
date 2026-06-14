<?php

use App\Models\CareerUserPathway;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;

new class extends Component
{
    public CareerUserPathway $pathway;

    public function mount(CareerUserPathway $pathway): void
    {
        $this->pathway = $pathway->load(['user', 'program', 'position', 'items.target']);
    }

    public function savePathway()
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        $save = $this->pathway->saves()->where('user_id', Auth::id())->first();
        if ($save) {
            $save->delete();
            $this->pathway->decrement('saves_count');
        } else {
            $this->pathway->saves()->create(['user_id' => Auth::id()]);
            $this->pathway->increment('saves_count');
        }
    }
}; ?>

<x-career-pathway.shell
    :title="$pathway->title"
    :subtitle="$pathway->story ?: 'Hành trình học tập được chia sẻ bởi sinh viên/cựu sinh viên UE-Connect.'"
    eyebrow="Hành trình anh/chị khóa trước"
>
    <div class="space-y-6">
        <section class="rounded-2xl border border-ue-border bg-white p-5 shadow-sm">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div class="flex flex-wrap gap-2 text-xs font-bold">
                    <span class="rounded-full bg-ue-brand-soft px-3 py-1 text-ue-brand-active">{{ $pathway->program?->name ?? 'Chương trình chưa gắn' }}</span>
                    @if($pathway->position)<span class="rounded-full bg-slate-100 px-3 py-1 text-slate-600">{{ $pathway->position->title }}</span>@endif
                    <span class="rounded-full bg-slate-100 px-3 py-1 text-slate-600">Tác giả: sinh viên/cựu sinh viên UE-Connect</span>
                </div>
                <div class="flex flex-wrap gap-2">
                    @if(Auth::id() === $pathway->user_id)
                        <a href="{{ route('app.career-pathway.senior-pathways.edit', ['pathway' => $pathway->slug]) }}" wire:navigate.hover class="rounded-xl border border-ue-border px-4 py-2 text-sm font-bold text-slate-700 transition hover:bg-slate-50">Chỉnh sửa</a>
                    @endif
                    <button wire:click="savePathway" class="rounded-xl bg-ue-brand px-4 py-2 text-sm font-bold text-white transition hover:bg-ue-brand-active">Lưu ({{ $pathway->saves_count }})</button>
                </div>
            </div>
        </section>

        <section class="rounded-2xl border border-ue-border bg-white p-5 shadow-sm">
            <h2 class="text-base font-extrabold text-slate-900">Tổng quan câu chuyện</h2>
            <p class="mt-2 text-sm font-medium leading-6 text-slate-500">{{ $pathway->story ?: 'Tác giả chưa bổ sung câu chuyện tổng quan.' }}</p>
        </section>

        <section class="rounded-2xl border border-ue-border bg-white p-5 shadow-sm">
            <h2 class="text-base font-extrabold text-slate-900">Timeline theo học kỳ</h2>
            <div class="mt-5 space-y-3">
                @forelse($pathway->items as $item)
                    <article class="rounded-2xl bg-slate-50 p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-xs font-bold text-ue-brand-active">Học kỳ {{ $item->semester_number }}</p>
                                <h3 class="mt-1 text-sm font-extrabold text-slate-900">{{ $item->title ?? 'Ghi chú học kỳ' }}</h3>
                            </div>
                            <span class="rounded-full bg-white px-2 py-1 text-[11px] font-bold text-slate-500">{{ $item->item_type->value }}</span>
                        </div>
                        @if($item->note)<p class="mt-2 text-sm font-medium leading-6 text-slate-500">{{ $item->note }}</p>@endif
                    </article>
                @empty
                    <div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 p-5">
                        <h3 class="text-sm font-extrabold text-slate-900">Chưa có ghi chú theo học kỳ</h3>
                        <p class="mt-2 text-sm font-medium leading-6 text-slate-500">Khi tác giả thêm môn học, project, tài nguyên hoặc mốc quan trọng, timeline sẽ hiển thị ở đây.</p>
                    </div>
                @endforelse
            </div>
        </section>

        <section class="grid gap-4 lg:grid-cols-3">
            @foreach(['Môn học đã gắn', 'Project và tài nguyên', 'Sai lầm, lời khuyên và cột mốc'] as $label)
                <div class="rounded-2xl border border-ue-border bg-white p-5 shadow-sm">
                    <h2 class="text-sm font-extrabold text-slate-900">{{ $label }}</h2>
                    <p class="mt-2 text-xs font-medium leading-5 text-slate-500">Nội dung này được tách khỏi dữ liệu chính thức để người đọc hiểu đây là kinh nghiệm cá nhân.</p>
                </div>
            @endforeach
        </section>
    </div>
</x-career-pathway.shell>
