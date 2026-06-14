<?php

use App\Enums\CareerUserPathwayStatus;
use App\Enums\CareerUserPathwayVisibility;
use App\Models\CareerUserPathway;
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
        $baseQuery = CareerUserPathway::with(['user', 'program.major', 'position'])
            ->withCount('items')
            ->where('status', CareerUserPathwayStatus::PUBLISHED->value)
            ->where('visibility', CareerUserPathwayVisibility::PUBLIC->value);

        $pathways = (clone $baseQuery)
            ->when($this->search !== '', fn ($query) => $query->where('title', 'like', "%{$this->search}%"))
            ->orderByDesc('saves_count')
            ->orderByDesc('published_at')
            ->paginate(12);

        return [
            'pathways' => $pathways,
            'featuredPathways' => (clone $baseQuery)->orderByDesc('saves_count')->limit(3)->get(),
        ];
    }
}; ?>

<x-career-pathway.shell
    title="Hành trình anh/chị khóa trước"
    subtitle="Đọc cách sinh viên khóa trước học qua từng học kỳ, chọn hướng nghề, làm project và chuẩn bị thực tập."
    :action-href="route('app.career-pathway.senior-pathways.create')"
    action-label="Chia sẻ hành trình của tôi"
    action-icon="plus"
>
    <div class="space-y-6">
        <section class="rounded-2xl border border-ue-border bg-white p-5 shadow-sm">
            <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h2 class="text-base font-extrabold text-slate-900">Tìm theo ngành, vị trí nghề hoặc câu chuyện</h2>
                    <p class="mt-1 text-xs font-medium text-slate-500">Các câu chuyện được trình bày bằng ngữ cảnh học tập an toàn, không biến trải nghiệm cá nhân thành hồ sơ công khai quá mức.</p>
                </div>
                <div class="relative w-full lg:w-96">
                    <x-ui.icon name="search" size="sm" class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" />
                    <input wire:model.live.debounce.400ms="search" type="search" placeholder="Tìm hành trình, ngành, hướng nghề..." class="w-full rounded-xl border border-ue-border bg-slate-50 py-2.5 pl-9 pr-3 text-sm font-medium text-slate-800 placeholder:text-slate-400 focus:border-ue-brand focus:bg-white focus:ring-2 focus:ring-ue-brand/15">
                </div>
            </div>
        </section>

        @if($featuredPathways->isNotEmpty())
            <section class="space-y-3">
                <div class="flex items-center justify-between">
                    <h2 class="text-base font-extrabold text-slate-900">Hành trình nổi bật</h2>
                    <span class="text-xs font-bold text-slate-400">Theo lượt lưu của cộng đồng</span>
                </div>
                <div class="grid gap-4 lg:grid-cols-3">
                    @foreach($featuredPathways as $pathway)
                        <a href="{{ route('app.career-pathway.senior-pathways.show', ['pathway' => $pathway->slug]) }}" wire:navigate.hover class="rounded-2xl border border-ue-border bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:border-ue-brand/30 hover:shadow-md">
                            <p class="text-[11px] font-bold text-ue-brand-active">{{ $pathway->program?->major?->name ?? 'Chia sẻ cộng đồng' }}</p>
                            <h3 class="mt-1 text-base font-extrabold leading-6 text-slate-900">{{ $pathway->title }}</h3>
                            <p class="mt-3 line-clamp-3 text-xs font-medium leading-5 text-slate-500">{{ $pathway->story ?: 'Câu chuyện đang được tác giả bổ sung theo từng học kỳ.' }}</p>
                            <div class="mt-4 flex flex-wrap gap-2 text-[11px] font-bold text-slate-600">
                                <span class="rounded-full bg-slate-50 px-2 py-1">{{ $pathway->items_count }} ghi chú học kỳ</span>
                                @if($pathway->position)
                                    <span class="rounded-full bg-ue-brand-soft px-2 py-1 text-ue-brand-active">{{ $pathway->position->title }}</span>
                                @endif
                            </div>
                            <div class="mt-4 border-t border-slate-100 pt-3 text-xs font-bold text-slate-500">Tác giả: sinh viên/cựu sinh viên UE-Connect</div>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif

        <section class="space-y-3">
            <div class="flex items-center justify-between">
                <h2 class="text-base font-extrabold text-slate-900">Mới chia sẻ</h2>
                <span class="text-xs font-bold text-slate-400">Chỉ hiển thị hành trình công khai</span>
            </div>
            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                @forelse($pathways as $pathway)
                    <a href="{{ route('app.career-pathway.senior-pathways.show', ['pathway' => $pathway->slug]) }}" wire:navigate.hover class="rounded-2xl border border-ue-border bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:border-ue-brand/30 hover:shadow-md">
                        <div class="flex items-start justify-between gap-3">
                            <h3 class="text-base font-extrabold leading-6 text-slate-900">{{ $pathway->title }}</h3>
                            <span class="shrink-0 rounded-full bg-slate-100 px-2 py-1 text-[11px] font-bold text-slate-500">Lưu</span>
                        </div>
                        <p class="mt-2 text-xs font-bold text-ue-brand-active">{{ $pathway->program?->name ?? 'Chương trình chưa gắn' }}</p>
                        <p class="mt-3 line-clamp-2 text-xs font-medium leading-5 text-slate-500">{{ $pathway->story ?: 'Hành trình này đang chờ tác giả bổ sung thêm cột mốc, project và lời khuyên.' }}</p>
                        <div class="mt-4 grid grid-cols-2 gap-2 text-center">
                            <div class="rounded-xl bg-slate-50 p-2"><div class="font-extrabold text-slate-900">{{ $pathway->items_count }}</div><div class="text-[10px] font-bold text-slate-500">mốc học kỳ</div></div>
                            <div class="rounded-xl bg-slate-50 p-2"><div class="font-extrabold text-slate-900">{{ $pathway->saves_count }}</div><div class="text-[10px] font-bold text-slate-500">lượt lưu</div></div>
                        </div>
                    </a>
                @empty
                    <div class="md:col-span-2 xl:col-span-3 rounded-2xl border border-dashed border-ue-border bg-white p-6 shadow-sm">
                        <h2 class="text-base font-extrabold text-slate-900">Chưa có hành trình nào được chia sẻ</h2>
                        <p class="mt-2 text-sm font-medium leading-6 text-slate-500">Khi có người đi trước chia sẻ, bạn sẽ thấy cách họ học từng học kỳ và chuẩn bị cho hướng nghề của mình.</p>
                        <a href="{{ route('app.career-pathway.senior-pathways.create') }}" wire:navigate.hover class="mt-5 inline-flex items-center gap-2 rounded-xl bg-ue-brand px-4 py-2 text-sm font-bold text-white transition hover:bg-ue-brand-active">
                            Chia sẻ hành trình của tôi
                            <x-ui.icon name="arrow-right" size="sm" />
                        </a>
                    </div>
                @endforelse
            </div>
            {{ $pathways->links() }}
        </section>
    </div>
</x-career-pathway.shell>
