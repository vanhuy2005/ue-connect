<?php

use App\Services\CareerPathwaySearchService;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    public string $q = '';

    public string $type = 'all';

    protected $queryString = [
        'q' => ['except' => ''],
        'type' => ['except' => 'all'],
    ];

    public function updatingQ(): void
    {
        $this->resetPage();
    }

    public function updatingType(): void
    {
        $this->resetPage();
    }

    public function with(CareerPathwaySearchService $searchService): array
    {
        $filters = [];
        if ($this->type !== 'all') {
            $filters['type'] = $this->type;
        }

        return [
            'results' => $searchService->search($this->q, $filters, 15, $this->getPage()),
            'typeLabels' => [
                'all' => 'Tất cả',
                'course' => 'Môn học',
                'program' => 'Chương trình',
                'position' => 'Vị trí nghề nghiệp',
                'senior_pathway' => 'Hành trình',
                'skill' => 'Kỹ năng',
                'contribution' => 'Tài nguyên',
            ],
        ];
    }
}; ?>

<x-career-pathway.shell
    title="Tìm trong Career Pathway"
    subtitle="Tìm môn học, kỹ năng, vị trí nghề nghiệp, hành trình và tài nguyên đang được mở cho sinh viên."
>
    <div class="space-y-6">
        <section class="rounded-2xl border border-ue-border bg-white p-5 shadow-sm">
            <div class="grid gap-3 lg:grid-cols-[1fr_220px]">
                <div>
                    <label for="career-pathway-search" class="text-xs font-bold text-slate-600">Từ khóa</label>
                    <div class="relative mt-2">
                        <x-ui.icon name="search" size="sm" class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" />
                        <input id="career-pathway-search" wire:model.live.debounce.500ms="q" type="search" class="w-full rounded-xl border border-ue-border bg-slate-50 py-3 pl-9 pr-3 text-sm font-medium text-slate-800 placeholder:text-slate-400 focus:border-ue-brand focus:bg-white focus:ring-2 focus:ring-ue-brand/15" placeholder="Tìm môn học, kỹ năng, vị trí nghề nghiệp, hành trình...">
                    </div>
                </div>
                <div>
                    <label for="career-pathway-type" class="text-xs font-bold text-slate-600">Loại nội dung</label>
                    <select id="career-pathway-type" wire:model.live="type" class="mt-2 w-full rounded-xl border border-ue-border bg-slate-50 py-3 text-sm font-bold text-slate-700 focus:border-ue-brand focus:bg-white focus:ring-2 focus:ring-ue-brand/15">
                        @foreach($typeLabels as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="mt-4 flex flex-wrap gap-2 text-[11px] font-bold text-slate-500">
                <span class="rounded-full bg-slate-100 px-2.5 py-1">Khóa</span>
                <span class="rounded-full bg-slate-100 px-2.5 py-1">Khoa</span>
                <span class="rounded-full bg-slate-100 px-2.5 py-1">Ngành</span>
                <span class="rounded-full bg-slate-100 px-2.5 py-1">Trạng thái công khai</span>
            </div>
        </section>

        <section class="rounded-2xl border border-ue-border bg-white p-5 shadow-sm">
            <div class="flex flex-col gap-1 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h2 class="text-base font-extrabold text-slate-900">Kết quả phù hợp</h2>
                    <p class="text-xs font-medium text-slate-500">Không hiển thị chương trình lỗi trạng thái, đóng góp bị ẩn hoặc hành trình riêng tư.</p>
                </div>
                <span class="text-xs font-bold text-slate-400">{{ $results->total() }} kết quả</span>
            </div>

            @if($results->isEmpty())
                <div class="mt-5 rounded-2xl border border-dashed border-slate-200 bg-slate-50 p-6">
                    <h3 class="text-sm font-extrabold text-slate-900">Chưa tìm thấy nội dung phù hợp</h3>
                    <p class="mt-2 text-sm font-medium leading-6 text-slate-500">Thử dùng mã môn, tên ngành, kỹ năng cụ thể hoặc mở rộng loại nội dung về “Tất cả”.</p>
                    @if($q !== '')
                        <button wire:click="$set('q', '')" class="mt-4 inline-flex items-center gap-2 rounded-xl border border-ue-border bg-white px-4 py-2 text-sm font-bold text-slate-700 transition hover:bg-slate-50">
                            Xóa từ khóa
                        </button>
                    @endif
                </div>
            @else
                <div class="mt-5 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                    @foreach($results as $result)
                        <livewire:career-pathway.search-result-card :result="$result" :key="$result['type'].'-'.$result['id']" />
                    @endforeach
                </div>
                <div class="mt-6">{{ $results->links() }}</div>
            @endif
        </section>
    </div>
</x-career-pathway.shell>
