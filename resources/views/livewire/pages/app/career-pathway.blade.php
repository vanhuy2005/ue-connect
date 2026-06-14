<?php

use App\Models\CareerCohort;
use App\Models\CareerFaculty;
use App\Models\CareerMajor;
use App\Models\CareerProgram;
use App\Services\CareerPathway\CareerPathwayWorktreeService;
use Livewire\Attributes\Url;
use Livewire\Volt\Component;

new class extends Component
{
    #[Url]
    public ?int $cohortId = null;

    #[Url]
    public ?int $facultyId = null;

    #[Url]
    public ?int $majorId = null;

    public ?int $selectedProgramId = null;

    public function mount(): void
    {
        $this->evaluateSelection();
    }

    public function updated($property): void
    {
        if (in_array($property, ['cohortId', 'facultyId', 'majorId'], true)) {
            $this->evaluateSelection();
        }
    }

    protected function evaluateSelection(): void
    {
        $this->selectedProgramId = null;
        if ($this->cohortId && $this->facultyId && $this->majorId) {
            $programs = $this->getProgramsProperty();
            if ($programs->count() === 1) {
                $this->selectedProgramId = $programs->first()->id;
            }
        }
    }

    public function getCohortsProperty()
    {
        return CareerCohort::whereHas('programs', fn ($query) => $query->publicReady())->get();
    }

    public function getFacultiesProperty()
    {
        $query = CareerFaculty::whereHas('programs', fn ($programQuery) => $programQuery->publicReady());
        if ($this->cohortId) {
            $query->whereHas('programs', fn ($programQuery) => $programQuery->where('cohort_id', $this->cohortId)->publicReady());
        }

        return $query->get();
    }

    public function getMajorsProperty()
    {
        $query = CareerMajor::whereHas('programs', fn ($programQuery) => $programQuery->publicReady());
        if ($this->facultyId) {
            $query->whereHas('programs', fn ($programQuery) => $programQuery->where('faculty_id', $this->facultyId)->publicReady());
        }
        if ($this->cohortId) {
            $query->whereHas('programs', fn ($programQuery) => $programQuery->where('cohort_id', $this->cohortId)->publicReady());
        }

        return $query->get();
    }

    public function getProgramsProperty()
    {
        $query = CareerProgram::publicReady()->with(['cohort', 'faculty', 'major']);
        if ($this->cohortId) {
            $query->where('cohort_id', $this->cohortId);
        }
        if ($this->facultyId) {
            $query->where('faculty_id', $this->facultyId);
        }
        if ($this->majorId) {
            $query->where('major_id', $this->majorId);
        }

        return $query->get();
    }

    public function getWorktreeDataProperty()
    {
        if (! $this->selectedProgramId) {
            return null;
        }

        $worktree = app(CareerPathwayWorktreeService::class)->getWorktree($this->selectedProgramId);

        return $worktree ? collect($worktree) : null;
    }

    public function resetFilters(): void
    {
        $this->reset(['cohortId', 'facultyId', 'majorId', 'selectedProgramId']);
    }
}; ?>

<x-career-pathway.shell
    title="Chương trình đào tạo"
    subtitle="Xem chương trình chính thức theo khóa, khoa, ngành và từng học kỳ."
    eyebrow="Dữ liệu chính thức"
>
    <div class="space-y-6" x-data="{ selectedCourse: null, showDrawer: false }">
        @if (session('status'))
            <div class="rounded-2xl border border-emerald-200 bg-white px-4 py-3 text-sm font-bold text-emerald-700 shadow-sm">
                {{ session('status') }}
            </div>
        @endif

        <section class="relative overflow-hidden rounded-2xl border border-ue-border bg-white p-5 shadow-sm md:p-6">
            <div wire:loading.delay wire:target="cohortId, facultyId, majorId" class="absolute inset-0 z-10 flex items-center justify-center bg-white/60 backdrop-blur-sm">
                <x-ui.inline-spinner class="h-6 w-6 text-ue-brand drop-shadow-sm" />
            </div>

            <div class="mb-5 flex items-center justify-between">
                <div>
                    <h2 class="text-base font-extrabold text-slate-900">Bộ lọc chương trình</h2>
                    <p class="mt-1 text-xs font-medium text-slate-500">Chọn lần lượt để mở đúng worktree của chương trình.</p>
                </div>
                @if($cohortId || $facultyId || $majorId)
                    <button wire:click="resetFilters" class="text-xs font-bold text-ue-brand-active transition hover:underline">Xóa bộ lọc</button>
                @endif
            </div>

            <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
                <div class="space-y-2">
                    <label class="text-xs font-bold text-slate-700">Khóa</label>
                    <select wire:model.live="cohortId" class="w-full rounded-xl border border-ue-border bg-slate-50 px-3 py-2.5 text-sm font-medium focus:border-ue-brand focus:bg-white focus:ring-2 focus:ring-ue-brand/15">
                        <option value="">Chọn khóa</option>
                        @foreach($this->cohorts as $cohort)
                            <option value="{{ $cohort->id }}">{{ $cohort->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="space-y-2">
                    <label class="text-xs font-bold text-slate-700">Khoa</label>
                    <select wire:model.live="facultyId" @disabled(! $cohortId) class="w-full rounded-xl border border-ue-border bg-slate-50 px-3 py-2.5 text-sm font-medium focus:border-ue-brand focus:bg-white focus:ring-2 focus:ring-ue-brand/15 disabled:cursor-not-allowed disabled:opacity-50">
                        <option value="">Chọn khoa</option>
                        @foreach($this->faculties as $faculty)
                            <option value="{{ $faculty->id }}">{{ $faculty->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="space-y-2">
                    <label class="text-xs font-bold text-slate-700">Ngành</label>
                    <select wire:model.live="majorId" @disabled(! $facultyId) class="w-full rounded-xl border border-ue-border bg-slate-50 px-3 py-2.5 text-sm font-medium focus:border-ue-brand focus:bg-white focus:ring-2 focus:ring-ue-brand/15 disabled:cursor-not-allowed disabled:opacity-50">
                        <option value="">Chọn ngành</option>
                        @foreach($this->majors as $major)
                            <option value="{{ $major->id }}">{{ $major->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </section>

        <section class="relative min-h-[320px] transition-opacity duration-300" wire:loading.class="opacity-50 pointer-events-none" wire:target="cohortId, facultyId, majorId">
            @if($this->selectedProgramId && $this->worktreeData)
                <div class="space-y-6">
                    <x-career-pathway.summary-card :worktree="$this->worktreeData" />

                    <div class="flex flex-wrap items-center gap-4 rounded-2xl border border-ue-border bg-white px-5 py-3 text-xs shadow-sm">
                        <span class="font-bold text-slate-700">Chú giải</span>
                        <span class="inline-flex items-center gap-2 font-medium text-slate-600"><span class="h-3 w-3 rounded-full bg-ue-brand"></span>Bắt buộc</span>
                        <span class="inline-flex items-center gap-2 font-medium text-slate-600"><span class="h-3 w-3 rounded-full bg-amber-400"></span>Tự chọn</span>
                        <span class="inline-flex items-center gap-2 font-medium text-slate-600"><x-ui.icon name="alert-triangle" size="xs" class="text-amber-500" />Thiếu mô tả</span>
                    </div>

                    <div class="hidden md:block">
                        <x-career-pathway.desktop-worktree :worktree="$this->worktreeData" />
                    </div>
                    <div class="block md:hidden">
                        <x-career-pathway.mobile-accordion :worktree="$this->worktreeData" />
                    </div>
                </div>
            @elseif($cohortId && $facultyId && $majorId && $this->programs->count() === 0)
                <div class="rounded-2xl border border-dashed border-ue-border bg-white p-6 shadow-sm">
                    <h2 class="text-base font-extrabold text-slate-900">Không tìm thấy chương trình phù hợp</h2>
                    <p class="mt-2 text-sm font-medium leading-6 text-slate-500">Không có chương trình đào tạo công khai nào khớp với khóa, khoa và ngành bạn vừa chọn.</p>
                </div>
            @elseif($cohortId && $facultyId && $majorId && $this->programs->count() > 1)
                <div class="rounded-2xl border border-dashed border-amber-200 bg-amber-50 p-6 shadow-sm">
                    <h2 class="text-base font-extrabold text-amber-900">Có nhiều chương trình cùng khớp</h2>
                    <p class="mt-2 text-sm font-medium leading-6 text-amber-800">Dữ liệu hiện có {{ $this->programs->count() }} chương trình trùng bộ lọc. Vui lòng kiểm tra lại ở khu vực quản trị dữ liệu.</p>
                </div>
            @else
                <div class="rounded-2xl border border-dashed border-ue-border bg-white p-6 shadow-sm">
                    <div class="max-w-2xl">
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-ue-brand-soft text-ue-brand-active">
                            <x-ui.icon name="map" size="md" />
                        </div>
                        <h2 class="mt-4 text-base font-extrabold text-slate-900">Chọn chương trình để mở worktree học tập</h2>
                        <p class="mt-2 text-sm font-medium leading-6 text-slate-500">Bộ lọc phía trên sẽ giúp bạn xem môn học theo học kỳ, phân biệt môn bắt buộc/tự chọn và mở chi tiết môn khi cần.</p>
                    </div>
                </div>
            @endif
        </section>

        <x-career-pathway.course-detail />
    </div>
</x-career-pathway.shell>
