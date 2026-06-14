<?php

use App\Enums\CareerPositionStatus;
use App\Enums\CareerPositionVisibility;
use App\Enums\CareerUserPathwayItemType;
use App\Enums\CareerUserPathwayStatus;
use App\Enums\CareerUserPathwayVisibility;
use App\Models\CareerPosition;
use App\Models\CareerProgram;
use App\Models\CareerUserPathway;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Volt\Component;

new class extends Component
{
    public ?CareerUserPathway $pathway = null;

    public int $step = 1;

    public string $title = '';

    public string $story = '';

    public ?int $programId = null;

    public ?int $careerPositionId = null;

    public ?int $semesterNumber = 1;

    public string $itemType = 'semester_note';

    public string $itemTitle = '';

    public string $itemNote = '';

    public function mount(?CareerUserPathway $pathway): void
    {
        if ($pathway && $pathway->exists) {
            abort_if($pathway->user_id !== auth()->id() && ! auth()->user()?->is_admin, 403);

            $this->pathway = $pathway->load(['program', 'position', 'items']);
            $this->title = $pathway->title;
            $this->story = $pathway->story ?? '';
            $this->programId = $pathway->program_id;
            $this->careerPositionId = $pathway->career_position_id;
        }
    }

    public function with(): array
    {
        $programs = CareerProgram::query()
            ->with(['cohort', 'major'])
            ->publicReady()
            ->orderBy('name')
            ->limit(60)
            ->get();

        $positions = CareerPosition::query()
            ->where('status', CareerPositionStatus::PUBLISHED->value)
            ->where('visibility', CareerPositionVisibility::PUBLIC->value)
            ->orderBy('title')
            ->limit(60)
            ->get();

        $items = $this->pathway
            ? $this->pathway->items()->get()
            : collect();

        return compact('programs', 'positions', 'items');
    }

    public function goToStep(int $step): void
    {
        $this->step = max(1, min(5, $step));
    }

    public function saveDraft(): void
    {
        $this->persistPathway();
        session()->flash('message', 'Bản nháp hành trình đã được lưu.');
    }

    public function addTimelineItem(): void
    {
        $pathway = $this->persistPathway();

        $validated = $this->validate([
            'itemType' => ['required', Rule::enum(CareerUserPathwayItemType::class)],
            'semesterNumber' => 'nullable|integer|min:1|max:20',
            'itemTitle' => 'required|string|max:255',
            'itemNote' => 'nullable|string|max:2000',
        ]);

        $pathway->items()->create([
            'item_type' => $validated['itemType'],
            'semester_number' => $validated['semesterNumber'],
            'title' => $validated['itemTitle'],
            'note' => $validated['itemNote'] ?: null,
            'order_index' => $pathway->items()->count(),
        ]);

        $this->reset('itemTitle', 'itemNote');
        $this->pathway = $pathway->fresh(['items']);
        $this->step = 4;

        session()->flash('message', 'Đã thêm mốc vào hành trình.');
    }

    public function removeTimelineItem(int $itemId): void
    {
        if (! $this->pathway) {
            return;
        }

        $item = $this->pathway->items()->whereKey($itemId)->firstOrFail();
        $item->delete();
        $this->pathway = $this->pathway->fresh(['items']);
    }

    public function publish()
    {
        $pathway = $this->persistPathway();

        if ($pathway->items()->count() === 0) {
            $this->addError('timeline', 'Hãy thêm ít nhất một mốc theo học kỳ, project, tài nguyên hoặc lời khuyên trước khi xuất bản.');
            $this->step = 4;

            return null;
        }

        $pathway->update([
            'status' => CareerUserPathwayStatus::PUBLISHED->value,
            'visibility' => CareerUserPathwayVisibility::PUBLIC->value,
            'published_at' => $pathway->published_at ?? now(),
        ]);

        return redirect()->route('app.career-pathway.senior-pathways.show', ['pathway' => $pathway->slug]);
    }

    private function persistPathway(): CareerUserPathway
    {
        $validated = $this->validate([
            'title' => 'required|string|max:255',
            'story' => 'required|string|min:20',
            'programId' => 'nullable|integer|exists:career_programs,id',
            'careerPositionId' => 'nullable|integer|exists:career_positions,id',
        ]);

        $payload = [
            'title' => $validated['title'],
            'story' => $validated['story'],
            'program_id' => $validated['programId'],
            'career_position_id' => $validated['careerPositionId'],
            'status' => CareerUserPathwayStatus::DRAFT->value,
            'visibility' => CareerUserPathwayVisibility::PRIVATE->value,
        ];

        if ($this->pathway && $this->pathway->exists) {
            $this->pathway->update($payload);
            $this->pathway = $this->pathway->fresh(['items']);

            return $this->pathway;
        }

        $this->pathway = CareerUserPathway::create(array_merge($payload, [
            'slug' => $this->uniqueSlug($validated['title']),
            'user_id' => auth()->id(),
        ]));

        return $this->pathway;
    }

    private function uniqueSlug(string $title): string
    {
        $baseSlug = Str::slug($title) ?: 'hanh-trinh';
        $slug = $baseSlug;
        $counter = 1;

        while (CareerUserPathway::where('slug', $slug)->exists()) {
            $slug = $baseSlug.'-'.$counter;
            $counter++;
        }

        return $slug;
    }
}; ?>

<x-career-pathway.shell
    :title="$pathway ? 'Chỉnh sửa hành trình' : 'Chia sẻ hành trình của tôi'"
    subtitle="Kể lại lộ trình học tập theo chương trình, học kỳ, project, tài nguyên và các mốc chuẩn bị nghề nghiệp."
>
    @php
        $steps = [
            1 => 'Chọn chương trình đã học',
            2 => 'Chọn vị trí nghề nghiệp liên quan nếu có',
            3 => 'Viết câu chuyện tổng quan',
            4 => 'Thêm mốc hành trình',
            5 => 'Xem trước & xuất bản',
        ];

        $itemLabels = [
            'semester_note' => 'Ghi chú học kỳ',
            'course' => 'Môn học',
            'project' => 'Project',
            'skill' => 'Kỹ năng',
            'resource' => 'Tài nguyên',
            'internship' => 'Thực tập',
            'mistake' => 'Sai lầm rút kinh nghiệm',
            'advice' => 'Lời khuyên',
            'milestone' => 'Cột mốc',
            'custom' => 'Khác',
        ];
    @endphp

    <div class="grid gap-6 xl:grid-cols-[300px_1fr]">
        <aside class="rounded-2xl border border-ue-border bg-white p-5 shadow-sm xl:sticky xl:top-6 xl:self-start">
            <h2 class="text-sm font-extrabold text-slate-900">Các bước chia sẻ</h2>
            <div class="mt-4 space-y-2">
                @foreach($steps as $index => $stepLabel)
                    <button
                        type="button"
                        wire:click="goToStep({{ $index }})"
                        @class([
                            'flex w-full items-center gap-3 rounded-2xl px-3 py-2.5 text-left transition',
                            'bg-ue-brand-soft text-ue-brand-active ring-1 ring-ue-brand/20' => $step === $index,
                            'bg-white text-slate-500 hover:bg-ue-brand-soft/60 hover:text-ue-brand-active' => $step !== $index,
                        ])
                    >
                        <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-white text-xs font-extrabold shadow-sm">{{ $index }}</span>
                        <span class="text-xs font-extrabold leading-5">{{ $stepLabel }}</span>
                    </button>
                @endforeach
            </div>
        </aside>

        <section class="rounded-2xl border border-ue-border bg-white p-5 shadow-sm">
            @if (session()->has('message'))
                <div class="mb-4 rounded-xl border border-emerald-200 bg-white px-4 py-3 text-sm font-bold text-emerald-700">{{ session('message') }}</div>
            @endif

            <div class="space-y-6">
                @if($step === 1)
                    <div>
                        <p class="text-xs font-extrabold uppercase tracking-wider text-ue-brand-active">Bước 1</p>
                        <h2 class="mt-1 text-xl font-extrabold text-slate-900">Bạn đã học chương trình nào?</h2>
                        <p class="mt-2 text-sm font-medium leading-6 text-slate-500">Chọn chương trình giúp khóa sau hiểu câu chuyện của bạn đang nằm trong bối cảnh khoa, ngành và khóa nào.</p>
                    </div>

                    <div>
                        <label class="text-xs font-bold text-slate-700">Chương trình đào tạo</label>
                        <select wire:model="programId" class="mt-2 w-full rounded-xl border border-ue-border bg-white px-3 py-2.5 text-sm font-bold text-slate-700 focus:border-ue-brand focus:ring-2 focus:ring-ue-brand/15">
                            <option value="">Chưa chọn chương trình</option>
                            @foreach($programs as $program)
                                <option value="{{ $program->id }}">{{ $program->name }}{{ $program->major ? ' · '.$program->major->name : '' }}</option>
                            @endforeach
                        </select>
                        @error('programId') <span class="mt-1 block text-xs font-bold text-red-600">{{ $message }}</span> @enderror
                    </div>
                @endif

                @if($step === 2)
                    <div>
                        <p class="text-xs font-extrabold uppercase tracking-wider text-ue-brand-active">Bước 2</p>
                        <h2 class="mt-1 text-xl font-extrabold text-slate-900">Hành trình này liên quan hướng nghề nào?</h2>
                        <p class="mt-2 text-sm font-medium leading-6 text-slate-500">Nếu chưa chắc, bạn có thể bỏ trống. Phần này chỉ giúp người đọc nối câu chuyện với một vị trí nghề nghiệp cụ thể.</p>
                    </div>

                    <div>
                        <label class="text-xs font-bold text-slate-700">Vị trí nghề nghiệp liên quan</label>
                        <select wire:model="careerPositionId" class="mt-2 w-full rounded-xl border border-ue-border bg-white px-3 py-2.5 text-sm font-bold text-slate-700 focus:border-ue-brand focus:ring-2 focus:ring-ue-brand/15">
                            <option value="">Không gắn vị trí nghề nghiệp</option>
                            @foreach($positions as $position)
                                <option value="{{ $position->id }}">{{ $position->title }}</option>
                            @endforeach
                        </select>
                        @error('careerPositionId') <span class="mt-1 block text-xs font-bold text-red-600">{{ $message }}</span> @enderror
                    </div>
                @endif

                @if($step === 3)
                    <div>
                        <p class="text-xs font-extrabold uppercase tracking-wider text-ue-brand-active">Bước 3</p>
                        <h2 class="mt-1 text-xl font-extrabold text-slate-900">Viết câu chuyện tổng quan</h2>
                        <p class="mt-2 text-sm font-medium leading-6 text-slate-500">Tập trung vào quyết định học tập, project, tài nguyên và cách bạn chuẩn bị thực tập hoặc hướng nghề.</p>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <label class="text-xs font-bold text-slate-700">Tiêu đề câu chuyện</label>
                            <input wire:model="title" type="text" placeholder="Ví dụ: Từ ngành Sư phạm Tin đến thực tập Backend" class="mt-2 w-full rounded-xl border border-ue-border bg-white px-3 py-2.5 text-sm font-medium focus:border-ue-brand focus:ring-2 focus:ring-ue-brand/15">
                            @error('title') <span class="mt-1 block text-xs font-bold text-red-600">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="text-xs font-bold text-slate-700">Câu chuyện tổng quan</label>
                            <textarea wire:model="story" rows="7" placeholder="Bạn đã chọn môn, làm project, tìm tài liệu và chuẩn bị thực tập như thế nào?" class="mt-2 w-full rounded-xl border border-ue-border bg-white px-3 py-2.5 text-sm font-medium focus:border-ue-brand focus:ring-2 focus:ring-ue-brand/15"></textarea>
                            @error('story') <span class="mt-1 block text-xs font-bold text-red-600">{{ $message }}</span> @enderror
                        </div>
                    </div>
                @endif

                @if($step === 4)
                    <div>
                        <p class="text-xs font-extrabold uppercase tracking-wider text-ue-brand-active">Bước 4</p>
                        <h2 class="mt-1 text-xl font-extrabold text-slate-900">Thêm mốc hành trình</h2>
                        <p class="mt-2 text-sm font-medium leading-6 text-slate-500">Một mốc có thể là ghi chú học kỳ, môn học, project, kỹ năng, tài nguyên, thực tập hoặc lời khuyên. Không cần tách thành hai bước vì bản chất đều là một timeline.</p>
                    </div>

                    <form wire:submit="addTimelineItem" class="grid gap-4 lg:grid-cols-[150px_220px_1fr]">
                        <div>
                            <label class="text-xs font-bold text-slate-700">Học kỳ</label>
                            <input wire:model="semesterNumber" type="number" min="1" max="20" class="mt-2 w-full rounded-xl border border-ue-border bg-white px-3 py-2.5 text-sm font-bold focus:border-ue-brand focus:ring-2 focus:ring-ue-brand/15">
                            @error('semesterNumber') <span class="mt-1 block text-xs font-bold text-red-600">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="text-xs font-bold text-slate-700">Loại mốc</label>
                            <select wire:model="itemType" class="mt-2 w-full rounded-xl border border-ue-border bg-white px-3 py-2.5 text-sm font-bold text-slate-700 focus:border-ue-brand focus:ring-2 focus:ring-ue-brand/15">
                                @foreach($itemLabels as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('itemType') <span class="mt-1 block text-xs font-bold text-red-600">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="text-xs font-bold text-slate-700">Tiêu đề mốc</label>
                            <input wire:model="itemTitle" type="text" placeholder="Ví dụ: Làm project quản lý lớp học bằng Laravel" class="mt-2 w-full rounded-xl border border-ue-border bg-white px-3 py-2.5 text-sm font-medium focus:border-ue-brand focus:ring-2 focus:ring-ue-brand/15">
                            @error('itemTitle') <span class="mt-1 block text-xs font-bold text-red-600">{{ $message }}</span> @enderror
                        </div>
                        <div class="lg:col-span-3">
                            <label class="text-xs font-bold text-slate-700">Ghi chú</label>
                            <textarea wire:model="itemNote" rows="4" placeholder="Bạn học được gì, vấp ở đâu, nên chuẩn bị tài nguyên/project nào?" class="mt-2 w-full rounded-xl border border-ue-border bg-white px-3 py-2.5 text-sm font-medium focus:border-ue-brand focus:ring-2 focus:ring-ue-brand/15"></textarea>
                            @error('itemNote') <span class="mt-1 block text-xs font-bold text-red-600">{{ $message }}</span> @enderror
                        </div>
                        <div class="lg:col-span-3 flex justify-end">
                            <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-ue-brand px-4 py-2 text-sm font-extrabold text-white transition hover:bg-ue-brand-active">
                                <x-ui.icon name="plus" size="sm" />
                                Thêm mốc vào hành trình
                            </button>
                        </div>
                    </form>
                    @error('timeline') <span class="block text-sm font-bold text-red-600">{{ $message }}</span> @enderror
                @endif

                @if($step === 5)
                    <div>
                        <p class="text-xs font-extrabold uppercase tracking-wider text-ue-brand-active">Bước 5</p>
                        <h2 class="mt-1 text-xl font-extrabold text-slate-900">Xem trước & xuất bản</h2>
                        <p class="mt-2 text-sm font-medium leading-6 text-slate-500">Kiểm tra lại câu chuyện, timeline và quyền riêng tư trước khi chia sẻ công khai cho cộng đồng UE-Connect.</p>
                    </div>

                    <div class="rounded-2xl border border-ue-border bg-white p-4">
                        <p class="text-xs font-bold text-slate-500">Tiêu đề</p>
                        <h3 class="mt-1 text-lg font-extrabold text-slate-900">{{ $title ?: 'Chưa có tiêu đề' }}</h3>
                        <p class="mt-3 text-sm font-medium leading-6 text-slate-600">{{ $story ?: 'Chưa có câu chuyện tổng quan.' }}</p>
                    </div>
                @endif

                <div class="rounded-2xl border border-ue-border bg-white p-4">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <h3 class="text-sm font-extrabold text-slate-900">Timeline hành trình</h3>
                            <p class="mt-1 text-xs font-medium text-slate-500">{{ $items->count() }} mốc đã thêm</p>
                        </div>
                        <button type="button" wire:click="goToStep(4)" class="inline-flex items-center gap-2 rounded-xl border border-ue-border px-3 py-2 text-xs font-extrabold text-slate-700 transition hover:bg-ue-brand-soft hover:text-ue-brand-active">
                            <x-ui.icon name="plus" size="sm" />
                            Thêm mốc
                        </button>
                    </div>

                    <div class="mt-4 space-y-3">
                        @forelse($items as $item)
                            <article class="rounded-2xl border border-ue-border bg-white p-4">
                                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                    <div>
                                        <p class="text-[11px] font-extrabold uppercase tracking-wider text-ue-brand-active">
                                            Học kỳ {{ $item->semester_number ?? '?' }} · {{ $itemLabels[$item->item_type->value] ?? 'Mốc' }}
                                        </p>
                                        <h4 class="mt-1 text-sm font-extrabold text-slate-900">{{ $item->title }}</h4>
                                        @if($item->note)
                                            <p class="mt-2 text-sm font-medium leading-6 text-slate-500">{{ $item->note }}</p>
                                        @endif
                                    </div>
                                    <button type="button" wire:click="removeTimelineItem({{ $item->id }})" class="rounded-xl border border-ue-border px-3 py-2 text-xs font-extrabold text-slate-500 transition hover:border-red-200 hover:text-red-600">
                                        Xóa
                                    </button>
                                </div>
                            </article>
                        @empty
                            <div class="rounded-2xl border border-dashed border-ue-border bg-white p-5">
                                <h4 class="text-sm font-extrabold text-slate-900">Chưa có mốc nào trong hành trình</h4>
                                <p class="mt-2 text-sm font-medium leading-6 text-slate-500">Hãy thêm ít nhất một ghi chú theo học kỳ, project, tài nguyên hoặc lời khuyên để người đọc thấy được lộ trình thực tế.</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                <div class="rounded-2xl border border-dashed border-ue-border bg-white p-4">
                    <h3 class="text-sm font-extrabold text-slate-900">Quyền riêng tư</h3>
                    <p class="mt-1 text-xs font-medium leading-5 text-slate-500">Trang công khai chỉ nên dùng ngữ cảnh an toàn như chương trình, học kỳ, project và lời khuyên. Không cần chia sẻ thông tin cá nhân nhạy cảm.</p>
                </div>

                <div class="flex flex-wrap items-center justify-between gap-3">
                    <button type="button" wire:click="saveDraft" class="inline-flex items-center gap-2 rounded-xl bg-ue-brand px-4 py-2 text-sm font-extrabold text-white transition hover:bg-ue-brand-active">Lưu bản nháp</button>
                    <div class="flex flex-wrap gap-2">
                        <button type="button" wire:click="goToStep({{ max(1, $step - 1) }})" class="inline-flex items-center gap-2 rounded-xl border border-ue-border px-4 py-2 text-sm font-extrabold text-slate-700 transition hover:bg-ue-brand-soft hover:text-ue-brand-active">Quay lại</button>
                        @if($step < 5)
                            <button type="button" wire:click="goToStep({{ min(5, $step + 1) }})" class="inline-flex items-center gap-2 rounded-xl border border-ue-border px-4 py-2 text-sm font-extrabold text-slate-700 transition hover:bg-ue-brand-soft hover:text-ue-brand-active">
                                Tiếp tục
                                <x-ui.icon name="arrow-right" size="sm" />
                            </button>
                        @else
                            <button type="button" wire:click="publish" class="inline-flex items-center gap-2 rounded-xl border border-ue-border px-4 py-2 text-sm font-extrabold text-slate-700 transition hover:bg-ue-brand-soft hover:text-ue-brand-active">
                                Xuất bản hành trình
                                <x-ui.icon name="arrow-right" size="sm" />
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </section>
    </div>
</x-career-pathway.shell>
