<?php

use App\Enums\CareerPositionImportanceLevel;
use App\Enums\CareerPositionItemType;
use App\Enums\CareerPositionSectionType;
use App\Enums\CareerPositionSourceType;
use App\Enums\CareerPositionStatus;
use App\Enums\CareerPositionVisibility;
use App\Models\CareerCohort;
use App\Models\CareerCourse;
use App\Models\CareerFaculty;
use App\Models\CareerMajor;
use App\Models\CareerPosition;
use App\Models\CareerPositionSection;
use App\Models\CareerProgram;
use App\Models\CareerProgramCourse;
use App\Models\CareerSkill;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Volt\Component;

new class extends Component
{
    public ?CareerPosition $position = null;

    public int $step = 1;

    public string $title = '';

    public string $description = '';

    public string $industry = '';

    public string $targetAudience = '';

    public string $selectedCohortId = '';

    public string $selectedFacultyId = '';

    public string $selectedMajorId = '';

    public string $relatedProgramId = '';

    public string $selectedCourseId = '';

    public string $selectedSkillId = '';

    public string $itemTitle = '';

    public string $itemDescription = '';

    public string $itemImportance = 'recommended';

    public string $resourceUrl = '';

    public function mount(?CareerPosition $position = null): void
    {
        if ($position && $position->exists) {
            $this->position = $position->load('sections.items.target');
            $this->title = $position->title;
            $this->description = $position->description ?? '';
            $this->industry = $position->industry ?? '';
            $this->targetAudience = $position->target_audience ?? '';
            $this->relatedProgramId = (string) ($position->related_program_id ?? '');

            if ($position->program) {
                $this->selectedCohortId = (string) $position->program->cohort_id;
                $this->selectedFacultyId = (string) $position->program->faculty_id;
                $this->selectedMajorId = (string) $position->program->major_id;
            }
        }
    }

    public function with(): array
    {
        $cohorts = CareerCohort::query()
            ->whereHas('programs', fn ($query) => $query->publicReady())
            ->orderByDesc('start_year')
            ->orderBy('name')
            ->get(['id', 'name']);

        $faculties = CareerFaculty::query()
            ->whereHas('programs', function ($query): void {
                $query->publicReady()
                    ->when($this->selectedCohortId !== '', fn ($inner) => $inner->where('cohort_id', $this->selectedCohortId));
            })
            ->orderBy('name')
            ->get(['id', 'name']);

        $majors = CareerMajor::query()
            ->whereHas('programs', function ($query): void {
                $query->publicReady()
                    ->when($this->selectedCohortId !== '', fn ($inner) => $inner->where('cohort_id', $this->selectedCohortId))
                    ->when($this->selectedFacultyId !== '', fn ($inner) => $inner->where('faculty_id', $this->selectedFacultyId));
            })
            ->orderBy('name')
            ->get(['id', 'name']);

        $programs = CareerProgram::query()
            ->with(['cohort', 'faculty', 'major'])
            ->publicReady()
            ->when($this->selectedCohortId !== '', fn ($query) => $query->where('cohort_id', $this->selectedCohortId))
            ->when($this->selectedFacultyId !== '', fn ($query) => $query->where('faculty_id', $this->selectedFacultyId))
            ->when($this->selectedMajorId !== '', fn ($query) => $query->where('major_id', $this->selectedMajorId))
            ->orderBy('name')
            ->get();

        $courses = $this->relatedProgramId !== ''
            ? CareerProgramCourse::query()
                ->with('course')
                ->where('program_id', $this->relatedProgramId)
                ->get()
                ->pluck('course')
                ->filter()
                ->unique('id')
                ->sortBy('code')
                ->values()
            : collect();

        $skills = CareerSkill::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->limit(120)
            ->get(['id', 'name']);

        $sections = $this->position
            ? $this->position->fresh(['sections.items.target'])->sections
            : collect();

        return compact('cohorts', 'faculties', 'majors', 'programs', 'courses', 'skills', 'sections');
    }

    public function updatedSelectedCohortId(): void
    {
        $this->reset('selectedFacultyId', 'selectedMajorId', 'relatedProgramId', 'selectedCourseId');
    }

    public function updatedSelectedFacultyId(): void
    {
        $this->reset('selectedMajorId', 'relatedProgramId', 'selectedCourseId');
    }

    public function updatedSelectedMajorId(): void
    {
        $this->reset('relatedProgramId', 'selectedCourseId');
    }

    public function updatedRelatedProgramId(): void
    {
        $this->reset('selectedCourseId');
    }

    public function goToStep(int $step): void
    {
        if ($step < 1 || $step > 5) {
            return;
        }

        $this->step = $step;
    }

    public function savePositionInfo(): void
    {
        $validated = $this->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:2000',
            'industry' => 'nullable|string|max:255',
            'targetAudience' => 'nullable|string|max:255',
            'relatedProgramId' => 'nullable|exists:career_programs,id',
        ]);

        $payload = [
            'title' => $validated['title'],
            'description' => $validated['description'],
            'industry' => $validated['industry'] ?: null,
            'target_audience' => $validated['targetAudience'] ?: null,
            'related_program_id' => $validated['relatedProgramId'] ?: null,
        ];

        if ($this->position && $this->position->exists) {
            if ($this->position->title !== $payload['title']) {
                $payload['slug'] = $this->uniqueSlug($payload['title'], $this->position->id);
            }

            $this->position->update($payload);
        } else {
            $this->position = CareerPosition::create(array_merge($payload, [
                'created_by' => auth()->id(),
                'slug' => $this->uniqueSlug($payload['title']),
                'status' => CareerPositionStatus::DRAFT->value,
                'visibility' => CareerPositionVisibility::PUBLIC->value,
            ]));
        }

        $this->position = $this->position->fresh(['sections.items.target']);
        $this->step = 2;

        session()->flash('message', 'Đã lưu thông tin vị trí. Tiếp tục gắn môn học để lộ trình có nền tảng từ chương trình đào tạo.');
    }

    public function addBuilderItem(): void
    {
        $this->ensurePositionExists();

        $validated = $this->validate($this->itemRules());
        [$sectionType, $itemType, $sectionTitle] = $this->sectionConfigForStep();

        $targetType = null;
        $targetId = null;
        $sourceType = CareerPositionSourceType::USER_CREATED;
        $title = $validated['itemTitle'] ?: null;

        if ($itemType === CareerPositionItemType::COURSE && $validated['selectedCourseId']) {
            $course = CareerCourse::findOrFail($validated['selectedCourseId']);
            $targetType = CareerCourse::class;
            $targetId = $course->id;
            $sourceType = CareerPositionSourceType::OFFICIAL_COURSE;
            $title = $title ?: "{$course->code} - {$course->name}";
        }

        if ($itemType === CareerPositionItemType::SKILL && $validated['selectedSkillId']) {
            $skill = CareerSkill::findOrFail($validated['selectedSkillId']);
            $targetType = CareerSkill::class;
            $targetId = $skill->id;
            $title = $title ?: $skill->name;
        }

        $section = $this->ensureSection($sectionType, $sectionTitle);

        $this->position->items()->create([
            'section_id' => $section->id,
            'item_type' => $itemType->value,
            'target_type' => $targetType,
            'target_id' => $targetId,
            'title' => $title,
            'description' => $validated['itemDescription'] ?: null,
            'importance_level' => $validated['itemImportance'],
            'source_type' => $sourceType->value,
            'order_index' => $section->items()->count(),
            'metadata_json' => [
                'resource_url' => $validated['resourceUrl'] ?: null,
            ],
        ]);

        $this->reset('selectedCourseId', 'selectedSkillId', 'itemTitle', 'itemDescription', 'resourceUrl');
        $this->itemImportance = 'recommended';
        $this->position = $this->position->fresh(['sections.items.target']);

        session()->flash('message', 'Đã thêm mục vào lộ trình. Bạn có thể thêm tiếp hoặc chuyển sang bước kế tiếp.');
    }

    public function publish(): mixed
    {
        $this->ensurePositionExists();

        if ($this->position->sections()->count() === 0 || $this->position->items()->count() === 0) {
            $this->step = 2;
            session()->flash('message', 'Lộ trình cần ít nhất một môn học, kỹ năng hoặc project trước khi xuất bản.');

            return null;
        }

        $this->position->update([
            'status' => CareerPositionStatus::PUBLISHED->value,
            'visibility' => CareerPositionVisibility::PUBLIC->value,
            'published_at' => $this->position->published_at ?? now(),
        ]);

        return redirect()->route('app.career-pathway.positions.show', ['position' => $this->position->slug]);
    }

    private function uniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($title) ?: 'lo-trinh-nghe-nghiep';
        $slug = $baseSlug;
        $counter = 1;

        while (CareerPosition::query()
            ->where('slug', $slug)
            ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
            ->exists()) {
            $slug = "{$baseSlug}-{$counter}";
            $counter++;
        }

        return $slug;
    }

    private function ensurePositionExists(): void
    {
        if (! $this->position) {
            $this->savePositionInfo();
        }
    }

    private function itemRules(): array
    {
        return [
            'relatedProgramId' => $this->step === 2 ? 'required|exists:career_programs,id' : 'nullable|exists:career_programs,id',
            'selectedCourseId' => $this->step === 2 ? 'required|exists:career_courses,id' : 'nullable',
            'selectedSkillId' => $this->step === 3 ? 'nullable|exists:career_skills,id' : 'nullable',
            'itemTitle' => [
                match ($this->step) {
                    2 => 'nullable',
                    3 => 'required_without:selectedSkillId',
                    default => 'required',
                },
                'string',
                'max:255',
            ],
            'itemDescription' => 'nullable|string|max:1000',
            'itemImportance' => ['required', Rule::enum(CareerPositionImportanceLevel::class)],
            'resourceUrl' => 'nullable|url|max:500',
        ];
    }

    private function sectionConfigForStep(): array
    {
        return match ($this->step) {
            2 => [CareerPositionSectionType::RECOMMENDED_COURSES, CareerPositionItemType::COURSE, 'Môn trong chương trình nên chú ý'],
            3 => [CareerPositionSectionType::REQUIRED_SKILLS, CareerPositionItemType::SKILL, 'Kỹ năng cần xây'],
            4 => [CareerPositionSectionType::PROJECTS, CareerPositionItemType::PROJECT, 'Project, tài nguyên và advice'],
            default => [CareerPositionSectionType::ADVICE, CareerPositionItemType::ADVICE, 'Advice từ người đi trước'],
        };
    }

    private function ensureSection(CareerPositionSectionType $sectionType, string $title): CareerPositionSection
    {
        return $this->position->sections()->firstOrCreate(
            ['section_type' => $sectionType->value],
            [
                'title' => $title,
                'description' => 'Nhóm nội dung do cộng đồng UE-Connect xây dựng cho lộ trình này.',
                'order_index' => $this->position->sections()->count(),
            ],
        );
    }
}; ?>

<x-career-pathway.shell
    :title="$position ? 'Chỉnh sửa lộ trình nghề nghiệp' : 'Tạo lộ trình nghề nghiệp'"
    subtitle="Dùng wizard để xây một hướng nghề từ thông tin vị trí, môn học, kỹ năng, project, tài nguyên và advice."
>
    <div class="grid gap-6 xl:grid-cols-[300px_1fr]">
        <aside class="rounded-2xl border border-ue-border bg-white p-5 shadow-sm">
            <h2 class="text-sm font-extrabold text-slate-900">Các bước xây lộ trình</h2>
            <div class="mt-4 space-y-3">
                @foreach([
                    1 => 'Thông tin vị trí',
                    2 => 'Gắn môn học',
                    3 => 'Gắn kỹ năng',
                    4 => 'Thêm project/tài nguyên/advice',
                    5 => 'Xem trước & xuất bản',
                ] as $index => $label)
                    <button
                        type="button"
                        wire:click="goToStep({{ $index }})"
                        @class([
                            'flex w-full items-center gap-3 rounded-xl px-3 py-3 text-left transition',
                            'bg-ue-brand-soft text-ue-brand-active' => $step === $index,
                            'bg-slate-50 text-slate-500 hover:bg-slate-100' => $step !== $index,
                        ])
                    >
                        <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-white text-xs font-extrabold">{{ $index }}</span>
                        <span class="text-xs font-extrabold leading-4">{{ $label }}</span>
                    </button>
                @endforeach
            </div>

            <div class="mt-5 rounded-2xl border border-dashed border-ue-border bg-white p-4">
                <p class="text-xs font-extrabold text-slate-900">Trạng thái bản nháp</p>
                <p class="mt-1 text-xs font-medium leading-5 text-slate-500">
                    {{ $position ? $sections->sum(fn ($section) => $section->items->count()).' mục đã gắn' : 'Chưa có bản nháp. Lưu bước 1 để bắt đầu.' }}
                </p>
            </div>
        </aside>

        <section class="rounded-2xl border border-ue-border bg-white p-5 shadow-sm">
            @if (session()->has('message'))
                <div class="mb-4 rounded-xl border border-emerald-200 bg-white px-4 py-3 text-sm font-bold text-emerald-700">{{ session('message') }}</div>
            @endif

            @if($step === 1)
                <form wire:submit="savePositionInfo" class="space-y-5">
                    <div class="grid gap-4 lg:grid-cols-2">
                        <div class="lg:col-span-2">
                            <label class="text-xs font-bold text-slate-700">Tên vị trí</label>
                            <input wire:model="title" type="text" placeholder="Ví dụ: Backend Developer cho sản phẩm giáo dục" class="mt-2 w-full rounded-xl border border-ue-border bg-white px-3 py-2.5 text-sm font-medium focus:border-ue-brand focus:ring-2 focus:ring-ue-brand/15">
                            @error('title') <span class="mt-1 block text-xs font-bold text-red-600">{{ $message }}</span> @enderror
                        </div>

                        <div class="lg:col-span-2">
                            <label class="text-xs font-bold text-slate-700">Tổng quan vị trí</label>
                            <textarea wire:model="description" rows="5" placeholder="Mô tả vị trí này làm gì, phù hợp với ai và sinh viên nên chuẩn bị từ đâu." class="mt-2 w-full rounded-xl border border-ue-border bg-white px-3 py-2.5 text-sm font-medium focus:border-ue-brand focus:ring-2 focus:ring-ue-brand/15"></textarea>
                            @error('description') <span class="mt-1 block text-xs font-bold text-red-600">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="text-xs font-bold text-slate-700">Nhóm nghề</label>
                            <input wire:model="industry" type="text" placeholder="Ví dụ: Sản phẩm giáo dục, dữ liệu, nội dung số" class="mt-2 w-full rounded-xl border border-ue-border bg-white px-3 py-2.5 text-sm font-medium focus:border-ue-brand focus:ring-2 focus:ring-ue-brand/15">
                        </div>

                        <div>
                            <label class="text-xs font-bold text-slate-700">Phù hợp với ai</label>
                            <input wire:model="targetAudience" type="text" placeholder="Ví dụ: Sinh viên thích xây sản phẩm và làm việc với API" class="mt-2 w-full rounded-xl border border-ue-border bg-white px-3 py-2.5 text-sm font-medium focus:border-ue-brand focus:ring-2 focus:ring-ue-brand/15">
                        </div>

                        <div class="lg:col-span-2 rounded-2xl border border-ue-border bg-white p-4">
                            <div class="flex flex-col gap-1">
                                <h3 class="text-sm font-extrabold text-slate-900">Chương trình đào tạo liên quan</h3>
                                <p class="text-xs font-medium leading-5 text-slate-500">Chọn lần lượt khóa, khoa, ngành rồi chọn đúng chương trình. Môn học ở bước sau chỉ lấy từ chương trình này.</p>
                            </div>

                            <div class="mt-4 grid gap-3 lg:grid-cols-4">
                                <div>
                                    <label class="text-xs font-bold text-slate-700">Khóa</label>
                                    <select wire:model.live="selectedCohortId" class="mt-2 w-full rounded-xl border border-ue-border bg-white px-3 py-2.5 text-sm font-bold text-slate-700 focus:border-ue-brand focus:ring-2 focus:ring-ue-brand/15">
                                        <option value="">Chọn khóa</option>
                                        @foreach($cohorts as $cohort)
                                            <option value="{{ $cohort->id }}">{{ $cohort->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label class="text-xs font-bold text-slate-700">Khoa</label>
                                    <select wire:model.live="selectedFacultyId" @disabled($selectedCohortId === '') class="mt-2 w-full rounded-xl border border-ue-border bg-white px-3 py-2.5 text-sm font-bold text-slate-700 disabled:bg-slate-50 disabled:text-slate-400 focus:border-ue-brand focus:ring-2 focus:ring-ue-brand/15">
                                        <option value="">Chọn khoa</option>
                                        @foreach($faculties as $faculty)
                                            <option value="{{ $faculty->id }}">{{ $faculty->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label class="text-xs font-bold text-slate-700">Ngành</label>
                                    <select wire:model.live="selectedMajorId" @disabled($selectedFacultyId === '') class="mt-2 w-full rounded-xl border border-ue-border bg-white px-3 py-2.5 text-sm font-bold text-slate-700 disabled:bg-slate-50 disabled:text-slate-400 focus:border-ue-brand focus:ring-2 focus:ring-ue-brand/15">
                                        <option value="">Chọn ngành</option>
                                        @foreach($majors as $major)
                                            <option value="{{ $major->id }}">{{ $major->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label class="text-xs font-bold text-slate-700">Chương trình</label>
                                    <select wire:model.live="relatedProgramId" @disabled($selectedMajorId === '') class="mt-2 w-full rounded-xl border border-ue-border bg-white px-3 py-2.5 text-sm font-bold text-slate-700 disabled:bg-slate-50 disabled:text-slate-400 focus:border-ue-brand focus:ring-2 focus:ring-ue-brand/15">
                                        <option value="">Chọn chương trình</option>
                                        @foreach($programs as $program)
                                            <option value="{{ $program->id }}">{{ $program->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            @error('relatedProgramId') <span class="mt-3 block text-xs font-bold text-red-600">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-ue-brand px-4 py-2.5 text-sm font-extrabold text-white transition hover:bg-ue-brand-active">
                            Lưu và gắn môn học
                            <x-ui.icon name="arrow-right" size="sm" />
                        </button>
                    </div>
                </form>
            @elseif($step >= 2 && $step <= 4)
                @php
                    $stepCopy = [
                        2 => ['title' => 'Gắn môn học từ chương trình', 'hint' => 'Chọn những môn nên chú ý nếu sinh viên muốn đi theo vị trí này.'],
                        3 => ['title' => 'Gắn kỹ năng cần xây', 'hint' => 'Thêm kỹ năng cụ thể, ưu tiên kỹ năng có thể luyện qua môn học hoặc project.'],
                        4 => ['title' => 'Thêm project, tài nguyên và advice', 'hint' => 'Gợi ý project, nguồn học thêm hoặc lời khuyên thực tế để người học có việc để làm tiếp.'],
                    ][$step];
                @endphp

                <div class="grid gap-6 lg:grid-cols-[1fr_340px]">
                    <form wire:submit="addBuilderItem" class="space-y-4">
                        <div>
                            <h2 class="text-lg font-extrabold text-slate-900">{{ $stepCopy['title'] }}</h2>
                            <p class="mt-1 text-sm font-medium leading-6 text-slate-500">{{ $stepCopy['hint'] }}</p>
                        </div>

                        @if($step === 2)
                            @if($relatedProgramId === '')
                                <div class="rounded-2xl border border-dashed border-ue-border bg-white p-5">
                                    <h3 class="text-sm font-extrabold text-slate-900">Chưa chọn chương trình đào tạo</h3>
                                    <p class="mt-2 text-sm font-medium leading-6 text-slate-500">Hãy quay lại bước 1 để chọn Khóa, Khoa, Ngành và Chương trình. Môn học của lộ trình sẽ được lấy đúng từ chương trình đó.</p>
                                    <button type="button" wire:click="goToStep(1)" class="mt-4 inline-flex items-center gap-2 rounded-xl border border-ue-border px-4 py-2 text-sm font-extrabold text-slate-700 transition hover:bg-ue-brand-soft hover:text-ue-brand-active">
                                        Quay lại chọn chương trình
                                    </button>
                                </div>
                            @else
                                <div>
                                    <label class="text-xs font-bold text-slate-700">Môn học trong chương trình đã chọn</label>
                                    <select wire:model="selectedCourseId" class="mt-2 w-full rounded-xl border border-ue-border bg-white px-3 py-2.5 text-sm font-medium focus:border-ue-brand focus:ring-2 focus:ring-ue-brand/15">
                                        <option value="">Chọn môn từ chương trình đào tạo</option>
                                        @foreach($courses as $course)
                                            <option value="{{ $course->id }}">{{ $course->code }} · {{ $course->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('selectedCourseId') <span class="mt-1 block text-xs font-bold text-red-600">{{ $message }}</span> @enderror
                                    @if($courses->isEmpty())
                                        <p class="mt-2 text-xs font-medium leading-5 text-slate-500">Chương trình này chưa có môn học được import. Hãy chọn chương trình khác hoặc kiểm tra dữ liệu chương trình đào tạo.</p>
                                    @endif
                                </div>
                            @endif
                        @endif

                        @if($step === 3)
                            <div>
                                <label class="text-xs font-bold text-slate-700">Kỹ năng đã có</label>
                                <select wire:model="selectedSkillId" class="mt-2 w-full rounded-xl border border-ue-border bg-white px-3 py-2.5 text-sm font-medium focus:border-ue-brand focus:ring-2 focus:ring-ue-brand/15">
                                    <option value="">Chưa chọn kỹ năng có sẵn</option>
                                    @foreach($skills as $skill)
                                        <option value="{{ $skill->id }}">{{ $skill->name }}</option>
                                    @endforeach
                                </select>
                                @error('selectedSkillId') <span class="mt-1 block text-xs font-bold text-red-600">{{ $message }}</span> @enderror
                                <p class="mt-2 text-xs font-medium leading-5 text-slate-500">Nếu danh sách chưa có kỹ năng phù hợp, nhập tên kỹ năng ở ô bên dưới.</p>
                            </div>
                        @endif

                        <div>
                            <label class="text-xs font-bold text-slate-700">{{ $step === 3 ? 'Tên kỹ năng mới nếu cần' : ($step === 4 ? 'Tiêu đề project/tài nguyên/advice' : 'Tiêu đề ghi chú nếu cần') }}</label>
                            <input wire:model="itemTitle" type="text" placeholder="{{ $step === 3 ? 'Ví dụ: Thiết kế REST API, viết test, đọc tài liệu tiếng Anh' : ($step === 4 ? 'Ví dụ: Làm API quản lý kế hoạch học tập cá nhân' : 'Ví dụ: Môn nền tảng nên học kỹ phần thực hành') }}" class="mt-2 w-full rounded-xl border border-ue-border bg-white px-3 py-2.5 text-sm font-medium focus:border-ue-brand focus:ring-2 focus:ring-ue-brand/15">
                            @error('itemTitle') <span class="mt-1 block text-xs font-bold text-red-600">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="text-xs font-bold text-slate-700">Mô tả ngắn</label>
                            <textarea wire:model="itemDescription" rows="4" placeholder="Nêu rõ vì sao mục này quan trọng và nên học hoặc làm theo hướng nào." class="mt-2 w-full rounded-xl border border-ue-border bg-white px-3 py-2.5 text-sm font-medium focus:border-ue-brand focus:ring-2 focus:ring-ue-brand/15"></textarea>
                            @error('itemDescription') <span class="mt-1 block text-xs font-bold text-red-600">{{ $message }}</span> @enderror
                        </div>

                        @if($step === 4)
                            <div>
                                <label class="text-xs font-bold text-slate-700">Link tham khảo nếu có</label>
                                <input wire:model="resourceUrl" type="url" placeholder="https://..." class="mt-2 w-full rounded-xl border border-ue-border bg-white px-3 py-2.5 text-sm font-medium focus:border-ue-brand focus:ring-2 focus:ring-ue-brand/15">
                                @error('resourceUrl') <span class="mt-1 block text-xs font-bold text-red-600">{{ $message }}</span> @enderror
                            </div>
                        @endif

                        <div>
                            <label class="text-xs font-bold text-slate-700">Mức ưu tiên</label>
                            <select wire:model="itemImportance" class="mt-2 w-full rounded-xl border border-ue-border bg-white px-3 py-2.5 text-sm font-medium focus:border-ue-brand focus:ring-2 focus:ring-ue-brand/15">
                                <option value="core">Cốt lõi</option>
                                <option value="important">Quan trọng</option>
                                <option value="recommended">Nên có</option>
                                <option value="optional">Tham khảo</option>
                            </select>
                            @error('itemImportance') <span class="mt-1 block text-xs font-bold text-red-600">{{ $message }}</span> @enderror
                        </div>

                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-ue-brand px-4 py-2.5 text-sm font-extrabold text-white transition hover:bg-ue-brand-active">
                                <x-ui.icon name="plus" size="sm" />
                                Thêm vào lộ trình
                            </button>
                            <button type="button" wire:click="goToStep({{ $step + 1 }})" class="inline-flex items-center gap-2 rounded-xl border border-ue-border px-4 py-2.5 text-sm font-extrabold text-slate-700 transition hover:bg-slate-50">
                                Sang bước tiếp theo
                                <x-ui.icon name="arrow-right" size="sm" />
                            </button>
                        </div>
                    </form>

                    <div class="rounded-2xl border border-ue-border bg-white p-4">
                        <h3 class="text-sm font-extrabold text-slate-900">Nội dung đã gắn</h3>
                        <div class="mt-3 space-y-3">
                            @forelse($sections as $section)
                                <div class="rounded-xl border border-slate-200 bg-white p-3">
                                    <p class="text-xs font-extrabold text-ue-brand-active">{{ $section->title }}</p>
                                    <div class="mt-2 space-y-2">
                                        @foreach($section->items as $item)
                                            <div class="rounded-lg bg-slate-50 px-3 py-2">
                                                <p class="text-xs font-extrabold text-slate-800">{{ $item->title ?: 'Mục lộ trình' }}</p>
                                                <p class="mt-1 line-clamp-2 text-[11px] font-medium leading-4 text-slate-500">{{ $item->description ?: 'Chưa có mô tả.' }}</p>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @empty
                                <div class="rounded-xl border border-dashed border-ue-border bg-white p-4">
                                    <p class="text-xs font-bold leading-5 text-slate-500">Chưa có mục nào. Thêm môn, kỹ năng hoặc project để lộ trình không bị rỗng.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            @else
                <div class="space-y-5">
                    <div>
                        <h2 class="text-lg font-extrabold text-slate-900">Xem trước trước khi xuất bản</h2>
                        <p class="mt-1 text-sm font-medium leading-6 text-slate-500">Kiểm tra lại phần tổng quan, môn học, kỹ năng và project. Đây là hướng dẫn cộng đồng, không thay thế tư vấn học vụ chính thức.</p>
                    </div>

                    <div class="rounded-2xl border border-ue-border bg-white p-5">
                        <p class="text-xs font-bold text-ue-brand-active">{{ $industry ?: 'Hướng nghề cộng đồng' }}</p>
                        <h3 class="mt-1 text-2xl font-extrabold text-slate-900">{{ $title ?: 'Lộ trình nghề nghiệp chưa có tên' }}</h3>
                        <p class="mt-2 text-sm font-medium leading-6 text-slate-500">{{ $description ?: 'Chưa có mô tả tổng quan.' }}</p>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        @forelse($sections as $section)
                            <div class="rounded-2xl border border-ue-border bg-white p-4">
                                <h4 class="text-sm font-extrabold text-slate-900">{{ $section->title }}</h4>
                                <div class="mt-3 space-y-2">
                                    @foreach($section->items as $item)
                                        <div class="rounded-xl bg-slate-50 px-3 py-2">
                                            <p class="text-xs font-extrabold text-slate-800">{{ $item->title ?: 'Mục lộ trình' }}</p>
                                            <p class="mt-1 text-[11px] font-medium leading-4 text-slate-500">{{ $item->description ?: 'Chưa có mô tả.' }}</p>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @empty
                            <div class="md:col-span-2 rounded-2xl border border-dashed border-ue-border bg-white p-5">
                                <h3 class="text-sm font-extrabold text-slate-900">Lộ trình chưa đủ nội dung</h3>
                                <p class="mt-1 text-sm font-medium leading-6 text-slate-500">Hãy quay lại bước 2 để gắn ít nhất một môn học, kỹ năng hoặc project trước khi xuất bản.</p>
                            </div>
                        @endforelse
                    </div>

                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <button type="button" wire:click="goToStep(4)" class="inline-flex items-center gap-2 rounded-xl border border-ue-border px-4 py-2.5 text-sm font-extrabold text-slate-700 transition hover:bg-slate-50">
                            Quay lại bổ sung
                        </button>
                        <button type="button" wire:click="publish" class="inline-flex items-center gap-2 rounded-xl bg-ue-brand px-4 py-2.5 text-sm font-extrabold text-white transition hover:bg-ue-brand-active">
                            Xuất bản lộ trình
                            <x-ui.icon name="send" size="sm" />
                        </button>
                    </div>
                </div>
            @endif
        </section>
    </div>
</x-career-pathway.shell>
