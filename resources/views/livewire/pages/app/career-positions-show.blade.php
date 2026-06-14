<?php

use App\Models\CareerPosition;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;

new class extends Component
{
    public CareerPosition $position;

    public function mount(CareerPosition $position): void
    {
        $this->position = $position->load(['creator', 'faculty', 'major', 'program', 'sections.items.target']);
    }

    public function savePosition()
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        $save = $this->position->saves()->where('user_id', Auth::id())->first();
        if ($save) {
            $save->delete();
            $this->position->decrement('saves_count');
        } else {
            $this->position->saves()->create(['user_id' => Auth::id()]);
            $this->position->increment('saves_count');
        }
    }
}; ?>

<x-career-pathway.shell
    :title="$position->title"
    :subtitle="$position->description ?: 'Lộ trình nghề nghiệp do cộng đồng UE-Connect xây dựng từ môn học, kỹ năng và kinh nghiệm thực tế.'"
    eyebrow="Vị trí nghề nghiệp"
>
    <div class="space-y-6">
        <section class="rounded-2xl border border-ue-border bg-white p-5 shadow-sm">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div class="flex flex-wrap gap-2 text-xs font-bold">
                    <span class="rounded-full bg-ue-brand-soft px-3 py-1 text-ue-brand-active">{{ $position->faculty?->name ?? 'Liên ngành' }}</span>
                    @if($position->major)<span class="rounded-full bg-slate-100 px-3 py-1 text-slate-600">{{ $position->major->name }}</span>@endif
                    @if($position->program)<span class="rounded-full bg-slate-100 px-3 py-1 text-slate-600">{{ $position->program->name }}</span>@endif
                </div>
                <div class="flex flex-wrap gap-2">
                    @if(Auth::id() === $position->created_by)
                        <a href="{{ route('app.career-pathway.positions.edit', ['position' => $position->slug]) }}" wire:navigate.hover class="rounded-xl border border-ue-border px-4 py-2 text-sm font-bold text-slate-700 transition hover:bg-slate-50">Chỉnh sửa</a>
                    @endif
                    <button wire:click="savePosition" class="rounded-xl bg-ue-brand px-4 py-2 text-sm font-bold text-white transition hover:bg-ue-brand-active">Lưu ({{ $position->saves_count }})</button>
                </div>
            </div>
        </section>

        <section class="grid gap-4 lg:grid-cols-2">
            @foreach([
                'Tổng quan' => $position->description,
                'Môn trong chương trình nên chú ý' => null,
                'Kỹ năng cần xây' => null,
                'Project nên làm' => null,
                'Tài nguyên học thêm' => null,
                'Advice từ người đi trước' => null,
            ] as $title => $fallback)
                <article class="rounded-2xl border border-ue-border bg-white p-5 shadow-sm">
                    <h2 class="text-base font-extrabold text-slate-900">{{ $title }}</h2>
                    @php
                        $matchingItems = $position->sections->flatMap->items->filter(function ($item) use ($title) {
                            $type = $item->item_type->value ?? $item->item_type;
                            return str_contains(strtolower($title), 'môn') && $type === 'course'
                                || str_contains(strtolower($title), 'kỹ năng') && $type === 'skill'
                                || str_contains(strtolower($title), 'project') && $type === 'project'
                                || str_contains(strtolower($title), 'tài nguyên') && $type === 'resource'
                                || str_contains(strtolower($title), 'advice') && $type === 'advice';
                        });
                    @endphp
                    @if($fallback)
                        <p class="mt-2 text-sm font-medium leading-6 text-slate-500">{{ $fallback }}</p>
                    @elseif($matchingItems->isNotEmpty())
                        <div class="mt-3 space-y-2">
                            @foreach($matchingItems as $item)
                                <div class="rounded-xl bg-slate-50 p-3">
                                    <p class="text-sm font-bold text-slate-900">{{ $item->title ?? ($item->target->name ?? 'Mục lộ trình') }}</p>
                                    @if($item->description)<p class="mt-1 text-xs font-medium text-slate-500">{{ $item->description }}</p>@endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="mt-2 text-sm font-medium leading-6 text-slate-500">Phần này đang chờ tác giả hoặc cộng đồng bổ sung thêm nội dung.</p>
                    @endif
                </article>
            @endforeach
        </section>

        <section class="rounded-2xl border border-amber-200 bg-amber-50 p-5">
            <h2 class="text-sm font-extrabold text-amber-900">Lưu ý</h2>
            <p class="mt-2 text-sm font-medium leading-6 text-amber-800">Đây là hướng dẫn cộng đồng, không phải tư vấn học vụ chính thức. Khi cần quyết định học phần hoặc điều kiện tốt nghiệp, hãy đối chiếu với chương trình đào tạo và cố vấn học tập.</p>
        </section>
    </div>
</x-career-pathway.shell>
