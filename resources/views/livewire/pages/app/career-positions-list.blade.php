<?php

use App\Enums\CareerPositionItemType;
use App\Enums\CareerPositionStatus;
use App\Enums\CareerPositionVisibility;
use App\Models\CareerPosition;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    public string $search = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function with(): array
    {
        $baseQuery = CareerPosition::with(['creator', 'faculty', 'major', 'program'])
            ->withCount([
                'items as linked_courses_count' => fn ($query) => $query->where('item_type', CareerPositionItemType::COURSE->value),
                'items as skills_count' => fn ($query) => $query->where('item_type', CareerPositionItemType::SKILL->value),
                'items as project_ideas_count' => fn ($query) => $query->where('item_type', CareerPositionItemType::PROJECT->value),
            ])
            ->where('status', CareerPositionStatus::PUBLISHED->value)
            ->where('visibility', CareerPositionVisibility::PUBLIC->value);

        $positions = (clone $baseQuery)
            ->when($this->search !== '', fn ($query) => $query->where('title', 'like', "%{$this->search}%"))
            ->orderByDesc('upvotes_count')
            ->orderByDesc('published_at')
            ->paginate(12);

        return [
            'positions' => $positions,
            'featuredPositions' => (clone $baseQuery)->orderByDesc('upvotes_count')->limit(3)->get(),
            'recentPositions' => (clone $baseQuery)->orderByDesc('updated_at')->limit(4)->get(),
        ];
    }
}; ?>

<x-career-pathway.shell
    title="Vị trí nghề nghiệp"
    subtitle="Khám phá các hướng nghề được cộng đồng xây dựng từ môn học, kỹ năng, project và kinh nghiệm thực tế."
    :action-href="route('app.career-pathway.positions.create')"
    action-label="Tạo lộ trình nghề nghiệp"
    action-icon="plus"
>
    <div class="space-y-6">
        <section class="rounded-2xl border border-ue-border bg-white p-5 shadow-sm">
            <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h2 class="text-base font-extrabold text-slate-900">Khám phá theo môn học, kỹ năng và ngành</h2>
                    <p class="mt-1 text-xs font-medium text-slate-500">Mỗi lộ trình nên giúp sinh viên hiểu cần chú ý môn nào, xây kỹ năng gì và có thể làm project gì.</p>
                </div>
                <div class="relative w-full lg:w-96">
                    <x-ui.icon name="search" size="sm" class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" />
                    <input wire:model.live.debounce.400ms="search" type="search" placeholder="Tìm vị trí, kỹ năng, ngành..." class="w-full rounded-xl border border-ue-border bg-slate-50 py-2.5 pl-9 pr-3 text-sm font-medium text-slate-800 placeholder:text-slate-400 focus:border-ue-brand focus:bg-white focus:ring-2 focus:ring-ue-brand/15">
                </div>
            </div>
        </section>

        @if($featuredPositions->isNotEmpty())
            <section class="space-y-3">
                <div class="flex items-center justify-between">
                    <h2 class="text-base font-extrabold text-slate-900">Vị trí nổi bật</h2>
                    <span class="text-xs font-bold text-slate-400">Theo lượt ủng hộ cộng đồng</span>
                </div>
                <div class="grid gap-4 lg:grid-cols-3">
                    @foreach($featuredPositions as $position)
                        <a href="{{ route('app.career-pathway.positions.show', ['position' => $position->slug]) }}" wire:navigate.hover class="rounded-2xl border border-ue-border bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:border-ue-brand/30 hover:shadow-md">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="text-[11px] font-bold text-ue-brand-active">{{ $position->faculty?->name ?? $position->major?->name ?? 'Cộng đồng UE-Connect' }}</p>
                                    <h3 class="mt-1 text-base font-extrabold leading-6 text-slate-900">{{ $position->title }}</h3>
                                </div>
                                <span class="rounded-full bg-ue-brand-soft px-2 py-1 text-[11px] font-bold text-ue-brand-active">Công khai</span>
                            </div>
                            <p class="mt-3 line-clamp-2 text-xs font-medium leading-5 text-slate-500">{{ $position->description ?: 'Lộ trình cộng đồng đang được bổ sung thêm môn học, kỹ năng và project liên quan.' }}</p>
                            <div class="mt-4 grid grid-cols-3 gap-2 text-center">
                                <div class="rounded-xl bg-slate-50 p-2"><div class="font-extrabold text-slate-900">{{ $position->linked_courses_count }}</div><div class="text-[10px] font-bold text-slate-500">môn</div></div>
                                <div class="rounded-xl bg-slate-50 p-2"><div class="font-extrabold text-slate-900">{{ $position->skills_count }}</div><div class="text-[10px] font-bold text-slate-500">kỹ năng</div></div>
                                <div class="rounded-xl bg-slate-50 p-2"><div class="font-extrabold text-slate-900">{{ $position->project_ideas_count }}</div><div class="text-[10px] font-bold text-slate-500">project</div></div>
                            </div>
                            <div class="mt-4 flex items-center justify-between border-t border-slate-100 pt-3 text-xs font-bold text-slate-500">
                                <span>Nguồn: {{ $position->creator?->name ?? 'Cộng đồng' }}</span>
                                <span>Lưu</span>
                            </div>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif

        <section class="space-y-3">
            <div class="flex items-center justify-between">
                <h2 class="text-base font-extrabold text-slate-900">Mới cập nhật</h2>
                <span class="text-xs font-bold text-slate-400">Theo trạng thái xuất bản</span>
            </div>
            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                @forelse($positions as $position)
                    <a href="{{ route('app.career-pathway.positions.show', ['position' => $position->slug]) }}" wire:navigate.hover class="rounded-2xl border border-ue-border bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:border-ue-brand/30 hover:shadow-md">
                        <div class="flex items-start justify-between gap-3">
                            <h3 class="text-base font-extrabold leading-6 text-slate-900">{{ $position->title }}</h3>
                            <span class="shrink-0 rounded-full bg-slate-100 px-2 py-1 text-[11px] font-bold text-slate-500">{{ $position->upvotes_count }} ủng hộ</span>
                        </div>
                        <p class="mt-2 text-xs font-bold text-ue-brand-active">{{ collect([$position->faculty?->name, $position->major?->name, $position->program?->name])->filter()->join(' • ') ?: 'Liên ngành' }}</p>
                        <p class="mt-3 line-clamp-2 text-xs font-medium leading-5 text-slate-500">{{ $position->description ?: 'Lộ trình này đang chờ cộng đồng bổ sung thêm ngữ cảnh học tập.' }}</p>
                        <div class="mt-4 flex flex-wrap gap-2 text-[11px] font-bold text-slate-600">
                            <span class="rounded-full bg-slate-50 px-2 py-1">{{ $position->linked_courses_count }} môn liên kết</span>
                            <span class="rounded-full bg-slate-50 px-2 py-1">{{ $position->skills_count }} kỹ năng</span>
                            <span class="rounded-full bg-slate-50 px-2 py-1">{{ $position->project_ideas_count }} project</span>
                        </div>
                    </a>
                @empty
                    <div class="md:col-span-2 xl:col-span-3 rounded-2xl border border-dashed border-ue-border bg-white p-6 shadow-sm">
                        <h2 class="text-base font-extrabold text-slate-900">Chưa có lộ trình nghề nghiệp nào được cộng đồng xuất bản</h2>
                        <p class="mt-2 text-sm font-medium leading-6 text-slate-500">Hãy tạo lộ trình đầu tiên từ các môn học và kỹ năng trong chương trình đào tạo để giúp khóa sau hiểu hướng nghề này cần chuẩn bị gì.</p>
                        <a href="{{ route('app.career-pathway.positions.create') }}" wire:navigate.hover class="mt-5 inline-flex items-center gap-2 rounded-xl bg-ue-brand px-4 py-2 text-sm font-bold text-white transition hover:bg-ue-brand-active">
                            Tạo lộ trình nghề nghiệp
                            <x-ui.icon name="arrow-right" size="sm" />
                        </a>
                    </div>
                @endforelse
            </div>
            {{ $positions->links() }}
        </section>
    </div>
</x-career-pathway.shell>
