<?php

use App\Enums\CareerContributionStatus;
use App\Enums\CareerContributionType;
use App\Enums\CareerContributionVisibility;
use App\Models\CareerCohort;
use App\Models\CareerContribution;
use App\Models\CareerCourse;
use App\Models\CareerFaculty;
use App\Models\CareerMajor;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Url;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url(as: 'filter')]
    public string $activeFilter = 'all';

    #[Url(as: 'section')]
    public string $activeSection = 'official';

    #[Url(as: 'cohort')]
    public string $selectedCohortId = '';

    #[Url(as: 'faculty')]
    public string $selectedFacultyId = '';

    #[Url(as: 'major')]
    public string $selectedMajorId = '';

    public ?CareerCourse $course = null;

    public string $contributionType = 'experience';

    public string $contributionTitle = '';

    public string $contributionContent = '';

    private function publicContributionStatuses(): array
    {
        return [
            CareerContributionStatus::PUBLISHED->value,
            CareerContributionStatus::APPROVED->value,
            CareerContributionStatus::VERIFIED->value,
        ];
    }

    public function mount(?CareerCourse $course = null): void
    {
        $this->course = $course?->load(['courseDescriptions', 'skillEdges']);
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function setFilter(string $filter): void
    {
        if (! in_array($filter, ['all', 'cohort', 'faculty', 'major'], true)) {
            return;
        }

        $this->activeFilter = $filter;

        if ($filter !== 'cohort') {
            $this->selectedCohortId = '';
        }

        if ($filter !== 'faculty') {
            $this->selectedFacultyId = '';
        }

        if ($filter !== 'major') {
            $this->selectedMajorId = '';
        }

        $this->resetPage();
    }

    public function updatedSelectedCohortId(): void
    {
        $this->resetPage();
    }

    public function updatedSelectedFacultyId(): void
    {
        $this->resetPage();
    }

    public function updatedSelectedMajorId(): void
    {
        $this->resetPage();
    }

    public function setSection(string $section): void
    {
        if (! in_array($section, ['official', 'skill', 'project_idea', 'resource', 'experience', 'career_relevance'], true)) {
            return;
        }

        $this->activeSection = $section;
    }

    public function with(): array
    {
        $publicStatuses = $this->publicContributionStatuses();

        $courses = CareerCourse::query()
            ->when($this->selectedCohortId !== '', function ($query): void {
                $query->whereHas('programCourses.program', fn ($programQuery) => $programQuery->where('cohort_id', $this->selectedCohortId));
            })
            ->when($this->selectedFacultyId !== '', function ($query): void {
                $query->whereHas('programCourses.program', fn ($programQuery) => $programQuery->where('faculty_id', $this->selectedFacultyId));
            })
            ->when($this->selectedMajorId !== '', function ($query): void {
                $query->whereHas('programCourses.program', fn ($programQuery) => $programQuery->where('major_id', $this->selectedMajorId));
            })
            ->when($this->search !== '', function ($query): void {
                $term = "%{$this->search}%";

                $query->where(function ($inner) use ($term): void {
                    $inner->where('code', 'like', $term)
                        ->orWhere('name', 'like', $term)
                        ->orWhereHas('skillEdges.skill', fn ($skillQuery) => $skillQuery->where('name', 'like', $term))
                        ->orWhereHas('contributions', function ($contributionQuery) use ($term): void {
                            $contributionQuery->whereIn('status', $this->publicContributionStatuses())
                                ->where('visibility', CareerContributionVisibility::PUBLIC->value)
                                ->where(function ($inner) use ($term): void {
                                    $inner->where('title', 'like', $term)->orWhere('content', 'like', $term);
                                });
                        });
                });
            })
            ->orderBy('code')
            ->paginate(12);

        $cohorts = CareerCohort::query()
            ->whereHas('programs.programCourses')
            ->orderByDesc('start_year')
            ->orderBy('name')
            ->get(['id', 'name']);

        $faculties = CareerFaculty::query()
            ->whereHas('programs.programCourses')
            ->orderBy('name')
            ->get(['id', 'name']);

        $majors = CareerMajor::query()
            ->whereHas('programs.programCourses')
            ->orderBy('name')
            ->get(['id', 'name', 'faculty_id']);

        $communityCount = $this->course
            ? CareerContribution::query()
                ->where('target_type', CareerCourse::class)
                ->where('target_id', $this->course->id)
                ->whereIn('status', $publicStatuses)
                ->where('visibility', CareerContributionVisibility::PUBLIC->value)
                ->count()
            : 0;

        $sectionType = $this->activeSection === 'official' ? null : $this->activeSection;

        $communityContributions = $this->course && $sectionType
            ? CareerContribution::query()
                ->with('user')
                ->where('target_type', CareerCourse::class)
                ->where('target_id', $this->course->id)
                ->where('contribution_type', $sectionType)
                ->whereIn('status', $publicStatuses)
                ->where('visibility', CareerContributionVisibility::PUBLIC->value)
                ->orderByDesc('upvotes_count')
                ->orderByDesc('created_at')
                ->limit(12)
                ->get()
            : collect();

        $sectionCounts = $this->course
            ? CareerContribution::query()
                ->where('target_type', CareerCourse::class)
                ->where('target_id', $this->course->id)
                ->whereIn('status', $publicStatuses)
                ->where('visibility', CareerContributionVisibility::PUBLIC->value)
                ->selectRaw('contribution_type, count(*) as total')
                ->groupBy('contribution_type')
                ->pluck('total', 'contribution_type')
            : collect();

        return compact('courses', 'communityCount', 'communityContributions', 'sectionCounts', 'cohorts', 'faculties', 'majors');
    }

    public function saveContribution(): void
    {
        abort_unless($this->course, 404);

        $validated = $this->validate([
            'contributionType' => ['required', Rule::in([
                CareerContributionType::EXPERIENCE->value,
                CareerContributionType::PROJECT_IDEA->value,
                CareerContributionType::RESOURCE->value,
                CareerContributionType::CAREER_RELEVANCE->value,
                CareerContributionType::SKILL->value,
            ])],
            'contributionTitle' => 'nullable|string|max:255',
            'contributionContent' => 'required|string|max:5000',
        ]);

        $this->course->contributions()->create([
            'user_id' => auth()->id(),
            'contribution_type' => $validated['contributionType'],
            'title' => $validated['contributionTitle'] ?: null,
            'content' => $validated['contributionContent'],
            'status' => CareerContributionStatus::PUBLISHED->value,
            'visibility' => CareerContributionVisibility::PUBLIC->value,
        ]);

        $this->reset('contributionTitle', 'contributionContent');

        session()->flash('courseContributionSaved', 'Cảm ơn bạn đã chia sẻ. Nội dung đã được ghi nhận để giúp các khóa sau hiểu môn học này rõ hơn.');
    }
}; ?>

<x-career-pathway.shell
    title="Môn học & tri thức cộng đồng"
    subtitle="Tra cứu môn học theo mã, tên, kỹ năng hoặc project để hiểu môn này học để làm gì và liên quan đến hướng nghề nào."
>
    <div class="space-y-6" x-data="{ contributionDrawerOpen: false, selectedContribution: null }">
        <section class="rounded-2xl border border-ue-border bg-white p-5 shadow-sm">
            <label for="course-search" class="text-xs font-bold text-slate-600">Tìm trong môn học & tri thức</label>
            <div class="mt-2 flex flex-col gap-3 sm:flex-row">
                <div class="relative flex-1">
                    <x-ui.icon name="search" size="sm" class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" />
                    <input id="course-search" wire:model.live.debounce.400ms="search" type="search" placeholder="@if($activeFilter === 'cohort') Tìm môn trong khóa đã chọn... @elseif($activeFilter === 'faculty') Tìm môn trong khoa đã chọn... @elseif($activeFilter === 'major') Tìm môn trong ngành đã chọn... @else Tìm mã môn, tên môn, kỹ năng, project... @endif" class="w-full rounded-xl border border-ue-border bg-white py-2.5 pl-9 pr-3 text-sm font-medium text-slate-800 placeholder:text-slate-400 focus:border-ue-brand focus:ring-2 focus:ring-ue-brand/15">
                </div>
            </div>
            <div class="mt-3 flex flex-wrap gap-2">
                @foreach([
                    'all' => 'Tất cả',
                    'cohort' => 'Khóa',
                    'faculty' => 'Khoa',
                    'major' => 'Ngành',
                ] as $filter => $label)
                    <button
                        type="button"
                        wire:click="setFilter('{{ $filter }}')"
                        @class([
                            'rounded-full border px-3 py-1 text-[11px] font-extrabold transition',
                            'border-ue-brand/30 bg-ue-brand-soft text-ue-brand-active' => $activeFilter === $filter,
                            'border-ue-border bg-white text-slate-500 hover:border-ue-brand/30 hover:text-ue-brand-active' => $activeFilter !== $filter,
                        ])
                    >
                        {{ $label }}
                    </button>
                @endforeach
            </div>

            @if($activeFilter !== 'all')
                <div class="mt-4 rounded-2xl border border-ue-border bg-white p-4">
                    @if($activeFilter === 'cohort')
                        <label for="course-cohort-filter" class="text-xs font-bold text-slate-700">Chọn khóa để lọc môn học</label>
                        <select id="course-cohort-filter" wire:model.live="selectedCohortId" class="mt-2 w-full rounded-xl border border-ue-border bg-white px-3 py-2.5 text-sm font-bold text-slate-700 focus:border-ue-brand focus:ring-2 focus:ring-ue-brand/15">
                            <option value="">Tất cả khóa đang có chương trình</option>
                            @foreach($cohorts as $cohort)
                                <option value="{{ $cohort->id }}">{{ $cohort->name }}</option>
                            @endforeach
                        </select>
                        <p class="mt-2 text-xs font-medium leading-5 text-slate-500">Sau khi chọn khóa, danh sách bên dưới chỉ hiển thị môn thuộc các chương trình của khóa đó.</p>
                    @elseif($activeFilter === 'faculty')
                        <label for="course-faculty-filter" class="text-xs font-bold text-slate-700">Chọn khoa để lọc môn học</label>
                        <select id="course-faculty-filter" wire:model.live="selectedFacultyId" class="mt-2 w-full rounded-xl border border-ue-border bg-white px-3 py-2.5 text-sm font-bold text-slate-700 focus:border-ue-brand focus:ring-2 focus:ring-ue-brand/15">
                            <option value="">Tất cả khoa đang có chương trình</option>
                            @foreach($faculties as $faculty)
                                <option value="{{ $faculty->id }}">{{ $faculty->name }}</option>
                            @endforeach
                        </select>
                        <p class="mt-2 text-xs font-medium leading-5 text-slate-500">Dùng bộ lọc khoa khi bạn muốn xem môn học theo đơn vị đào tạo trước khi đi vào từng ngành.</p>
                    @elseif($activeFilter === 'major')
                        <label for="course-major-filter" class="text-xs font-bold text-slate-700">Chọn ngành để lọc môn học</label>
                        <select id="course-major-filter" wire:model.live="selectedMajorId" class="mt-2 w-full rounded-xl border border-ue-border bg-white px-3 py-2.5 text-sm font-bold text-slate-700 focus:border-ue-brand focus:ring-2 focus:ring-ue-brand/15">
                            <option value="">Tất cả ngành đang có chương trình</option>
                            @foreach($majors as $major)
                                <option value="{{ $major->id }}">{{ $major->name }}</option>
                            @endforeach
                        </select>
                        <p class="mt-2 text-xs font-medium leading-5 text-slate-500">Chọn ngành để thu hẹp môn học theo đúng chương trình sinh viên đang theo học.</p>
                    @endif
                </div>
            @endif
        </section>

        @if($course)
            <section class="rounded-2xl border border-ue-border bg-white p-5 shadow-sm">
                <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                    <div>
                        <p class="text-xs font-bold text-ue-brand-active">{{ $course->code }}</p>
                        <h2 class="mt-1 text-xl font-extrabold text-slate-900">{{ $course->name }}</h2>
                        <p class="mt-2 text-sm font-medium text-slate-500">Dữ liệu chính thức và chia sẻ cộng đồng được tách riêng để bạn biết đâu là curriculum, đâu là kinh nghiệm.</p>
                    </div>
                    <span class="rounded-full bg-ue-brand-soft px-3 py-1 text-xs font-bold text-ue-brand-active">{{ $communityCount }} chia sẻ công khai</span>
                </div>

                <div class="mt-5 grid gap-3 lg:grid-cols-6">
                    @foreach([
                        'official' => ['label' => 'Chính thức', 'hint' => 'Mô tả, tín chỉ và học kỳ từ chương trình.'],
                        'skill' => ['label' => 'Kỹ năng', 'hint' => 'Nội dung cộng đồng đã được duyệt.'],
                        'project_idea' => ['label' => 'Project', 'hint' => 'Ý tưởng project từ cộng đồng.'],
                        'resource' => ['label' => 'Tài liệu', 'hint' => 'Nguồn học thêm đã chia sẻ.'],
                        'experience' => ['label' => 'Kinh nghiệm', 'hint' => 'Ghi chú học môn từ người học.'],
                        'career_relevance' => ['label' => 'Liên quan nghề', 'hint' => 'Môn này nối với hướng nghề nào.'],
                    ] as $section => $tab)
                        <button type="button" wire:click="setSection('{{ $section }}')" @class([
                            'rounded-xl border p-3 text-left transition',
                            'border-ue-brand/30 bg-ue-brand-soft text-ue-brand-active' => $activeSection === $section,
                            'border-slate-200 bg-white hover:border-ue-brand/30 hover:bg-ue-brand-soft/40' => $activeSection !== $section,
                        ])>
                            <div class="flex items-center justify-between gap-2">
                                <p class="text-xs font-extrabold text-slate-800">{{ $tab['label'] }}</p>
                                @if($section !== 'official')
                                    <span class="rounded-full bg-white px-2 py-0.5 text-[10px] font-extrabold text-ue-brand-active">{{ $sectionCounts[$section] ?? 0 }}</span>
                                @endif
                            </div>
                            <p class="mt-1 text-[11px] font-medium leading-4 text-slate-500">
                                {{ $tab['hint'] }}
                            </p>
                        </button>
                    @endforeach
                </div>

                @if($activeSection === 'official')
                    <div class="mt-5 rounded-2xl border border-ue-border bg-white p-5">
                        <h3 class="text-sm font-extrabold text-slate-900">Dữ liệu chính thức</h3>
                        <p class="mt-2 text-sm font-medium leading-6 text-slate-500">Thông tin mã môn, tên môn và mô tả chính thức được lấy từ chương trình đào tạo. Nếu bạn thấy thiếu hoặc sai, hãy mở môn trong trang Chương trình đào tạo để gửi đề xuất cập nhật cho admin duyệt.</p>
                    </div>
                @elseif($communityContributions->isNotEmpty())
                    <div class="mt-5 grid gap-3 md:grid-cols-2">
                        @foreach($communityContributions as $contribution)
                            @php
                                $payload = [
                                    'title' => $contribution->title ?: 'Chia sẻ môn học',
                                    'type' => $contribution->contribution_type->value,
                                    'content' => $contribution->content,
                                    'author' => $contribution->user?->name ?? 'Thành viên UE-Connect',
                                    'created_at' => $contribution->created_at?->format('d/m/Y'),
                                    'votes' => $contribution->upvotes_count,
                                ];
                            @endphp
                            <button
                                type="button"
                                class="rounded-2xl border border-ue-border bg-white p-4 text-left shadow-sm transition hover:-translate-y-0.5 hover:border-ue-brand/30 hover:shadow-md"
                                @click="selectedContribution = @js($payload); contributionDrawerOpen = true"
                            >
                                <p class="text-xs font-bold text-ue-brand-active">{{ $contribution->contribution_type->value }}</p>
                                <h3 class="mt-1 line-clamp-2 text-sm font-extrabold text-slate-900">{{ $contribution->title ?: 'Chia sẻ môn học' }}</h3>
                                <p class="mt-2 line-clamp-3 text-xs font-medium leading-5 text-slate-500">{{ $contribution->content }}</p>
                                <p class="mt-3 text-[11px] font-bold text-slate-400">Mở tham khảo</p>
                            </button>
                        @endforeach
                    </div>
                @else
                    <div id="course-contribution-form" class="mt-5 rounded-2xl border border-dashed border-ue-border bg-white p-5">
                        <h3 class="text-sm font-extrabold text-slate-900">Chưa có chia sẻ cho môn này</h3>
                        <p class="mt-2 text-sm font-medium leading-6 text-slate-500">Bạn có thể là người đầu tiên giúp khóa sau hiểu môn này học để làm gì.</p>

                        @if (session()->has('courseContributionSaved'))
                            <div class="mt-4 rounded-xl border border-emerald-200 bg-white px-4 py-3 text-sm font-bold text-emerald-700">
                                {{ session('courseContributionSaved') }}
                            </div>
                        @endif

                        <form wire:submit="saveContribution" class="mt-4 grid gap-3 lg:grid-cols-[220px_1fr]">
                            <div>
                                <label class="text-xs font-bold text-slate-700">Loại chia sẻ</label>
                                <select wire:model="contributionType" class="mt-2 w-full rounded-xl border border-ue-border bg-white px-3 py-2.5 text-sm font-bold text-slate-700 focus:border-ue-brand focus:ring-2 focus:ring-ue-brand/15">
                                    <option value="experience">Kinh nghiệm học môn</option>
                                    <option value="project_idea">Ý tưởng project</option>
                                    <option value="resource">Tài liệu hữu ích</option>
                                    <option value="career_relevance">Liên quan nghề</option>
                                    <option value="skill">Kỹ năng cần xây</option>
                                </select>
                                @error('contributionType') <span class="mt-1 block text-xs font-bold text-red-600">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="text-xs font-bold text-slate-700">Tiêu đề ngắn</label>
                                <input wire:model="contributionTitle" type="text" placeholder="Ví dụ: Nên học môn này cùng project CRUD nhỏ" class="mt-2 w-full rounded-xl border border-ue-border bg-white px-3 py-2.5 text-sm font-medium focus:border-ue-brand focus:ring-2 focus:ring-ue-brand/15">
                                @error('contributionTitle') <span class="mt-1 block text-xs font-bold text-red-600">{{ $message }}</span> @enderror
                            </div>
                            <div class="lg:col-span-2">
                                <label class="text-xs font-bold text-slate-700">Nội dung chia sẻ</label>
                                <textarea wire:model="contributionContent" rows="4" placeholder="Bạn đã học, làm project, tìm tài liệu hoặc áp dụng môn này vào hướng nghề nào?" class="mt-2 w-full rounded-xl border border-ue-border bg-white px-3 py-2.5 text-sm font-medium focus:border-ue-brand focus:ring-2 focus:ring-ue-brand/15"></textarea>
                                @error('contributionContent') <span class="mt-1 block text-xs font-bold text-red-600">{{ $message }}</span> @enderror
                            </div>
                            <div class="lg:col-span-2 flex flex-wrap items-center justify-between gap-3">
                                <p class="text-xs font-medium leading-5 text-slate-500">Nội dung công khai nên tách kinh nghiệm cá nhân khỏi dữ liệu chính thức của chương trình.</p>
                                <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-ue-brand px-4 py-2 text-sm font-extrabold text-white transition hover:bg-ue-brand-active">
                                    <x-ui.icon name="plus" size="sm" />
                                    Thêm chia sẻ cho môn này
                                </button>
                            </div>
                        </form>
                    </div>
                @endif
            </section>
        @endif

        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            @forelse($courses as $item)
                <a href="{{ route('app.career-pathway.courses.show', $item) }}" wire:navigate.hover class="rounded-2xl border border-ue-border bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:border-ue-brand/30 hover:shadow-md">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-xs font-bold text-ue-brand-active">{{ $item->code }}</p>
                            <h2 class="mt-1 text-sm font-extrabold leading-5 text-slate-900">{{ $item->name }}</h2>
                        </div>
                        <span class="rounded-full bg-slate-100 px-2 py-1 text-[11px] font-bold text-slate-500">{{ $item->credits ?? 0 }} tín chỉ</span>
                    </div>
                    <p class="mt-4 text-xs font-medium leading-5 text-slate-500">Mở trang môn học để xem dữ liệu chính thức, kỹ năng liên quan, project, tài liệu và kinh nghiệm cộng đồng.</p>
                </a>
            @empty
                <div class="md:col-span-2 xl:col-span-3 rounded-2xl border border-dashed border-ue-border bg-white p-6 shadow-sm">
                    <h2 class="text-base font-extrabold text-slate-900">Chưa tìm thấy môn phù hợp</h2>
                    <p class="mt-2 text-sm font-medium leading-6 text-slate-500">Thử tìm bằng mã môn, tên môn hoặc quay lại chương trình đào tạo để chọn môn từ từng học kỳ.</p>
                </div>
            @endforelse
        </section>

        {{ $courses->links() }}

        <div x-show="contributionDrawerOpen" x-transition.opacity class="fixed inset-0 z-40 bg-slate-900/40 backdrop-blur-sm" @click="contributionDrawerOpen = false" x-cloak></div>
        <aside x-show="contributionDrawerOpen" x-transition class="fixed inset-y-0 right-0 z-50 flex w-full max-w-xl flex-col border-l border-ue-border bg-white shadow-2xl" x-cloak>
            <div class="flex items-center justify-between border-b border-ue-border px-5 py-4">
                <div>
                    <p class="text-xs font-bold uppercase tracking-wider text-ue-brand-active" x-text="selectedContribution?.type || 'Chia sẻ'"></p>
                    <h2 class="mt-1 text-lg font-extrabold text-slate-900">Nội dung tham khảo</h2>
                </div>
                <button type="button" @click="contributionDrawerOpen = false" class="rounded-full p-2 text-slate-500 transition hover:bg-slate-100">
                    <x-ui.icon name="x" size="sm" />
                </button>
            </div>
            <div class="flex-1 overflow-y-auto p-5">
                <h3 class="text-xl font-extrabold leading-8 text-slate-900" x-text="selectedContribution?.title"></h3>
                <p class="mt-2 text-xs font-bold text-slate-500">
                    <span x-text="selectedContribution?.author"></span>
                    <span> · </span>
                    <span x-text="selectedContribution?.created_at"></span>
                </p>
                <div class="mt-5 whitespace-pre-line rounded-2xl border border-ue-border bg-white p-5 text-sm font-medium leading-7 text-slate-600" x-text="selectedContribution?.content"></div>
                <div class="mt-5 rounded-2xl border border-dashed border-ue-border bg-white p-4">
                    <h4 class="text-sm font-extrabold text-slate-900">Lưu ý</h4>
                    <p class="mt-1 text-xs font-medium leading-5 text-slate-500">Đây là nội dung cộng đồng. Hãy đối chiếu với dữ liệu chính thức của chương trình khi dùng để lên kế hoạch học tập.</p>
                </div>
            </div>
        </aside>
    </div>
</x-career-pathway.shell>
