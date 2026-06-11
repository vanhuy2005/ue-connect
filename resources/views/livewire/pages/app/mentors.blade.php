<?php

use App\Enums\MentorAvailabilityStatus;
use App\Models\MentorProfile;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Url;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    public string $search = '';

    #[Url(as: 'topic', history: true)]
    public array $selectedTopics = [];

    #[Url(as: 'avail', history: true)]
    public string $availabilityFilter = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function selectTopic(?string $topic): void
    {
        if ($topic === null) {
            return;
        }

        $this->selectedTopics = in_array($topic, $this->selectedTopics, true)
            ? array_values(array_filter($this->selectedTopics, fn ($t) => $t !== $topic))
            : [...$this->selectedTopics, $topic];

        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->selectedTopics = [];
        $this->availabilityFilter = '';
        $this->resetPage();
    }

    public function with(): array
    {
        $user = Auth::user();
        $query = MentorProfile::query()
            ->with('user.profile')
            ->discoverable()
            ->where('user_id', '!=', $user->id);

        if ($this->search !== '') {
            $query->searchFulltext($this->search);
        }

        if (! empty($this->selectedTopics)) {
            $query->where(function ($q) {
                foreach ($this->selectedTopics as $topic) {
                    $q->whereJsonContains('expertise_topics', $topic)
                        ->orWhereJsonContains('preferred_request_types', $topic);
                }
            });
        }

        if ($this->availabilityFilter === 'available') {
            $query->where('availability_status', MentorAvailabilityStatus::Available);
        }

        return [
            'mentors' => $query->latest('updated_at')->paginate(12),
            'ownMentorProfile' => $user->mentorProfile()->first(),
            'expertiseTopics' => MentorProfile::discoverable()
                ->get()
                ->flatMap(fn ($p) => $p->expertise_topics ?? [])
                ->unique()
                ->sort()
                ->values()
                ->toArray(),
            'helpTopics' => MentorProfile::discoverable()
                ->get()
                ->flatMap(fn ($p) => $p->preferred_request_types ?? [])
                ->unique()
                ->values()
                ->toArray(),
        ];
    }
};
?>

<div class="mx-auto max-w-6xl px-4 py-6 sm:px-6 lg:px-8" x-data="{ showFilters: false }">
    {{-- Header --}}
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Mentor Connection</h1>
            <p class="mt-1 text-sm text-slate-500">Tìm mentor phù hợp và gửi yêu cầu cố vấn có cấu trúc.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            @unless ($ownMentorProfile && $ownMentorProfile->is_active)
                <a href="{{ route('mentor.requests.index') }}" wire:navigate class="inline-flex items-center justify-center rounded-lg border border-ue-brand bg-white px-4 py-2 text-sm font-semibold text-ue-brand hover:bg-ue-brand-soft">
                    Yêu cầu của tôi
                </a>
            @endunless
            @if ($ownMentorProfile && $ownMentorProfile->is_active)
                <a href="{{ route('mentor.setup') }}" wire:navigate class="inline-flex items-center justify-center rounded-lg bg-ue-brand px-4 py-2 text-sm font-semibold text-white hover:bg-ue-brand-dark">
                    Cập nhật hồ sơ mentor
                </a>
                <a href="{{ route('mentor.dashboard') }}" wire:navigate class="inline-flex items-center justify-center rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                    Mentor dashboard
                </a>
            @else
                <a href="{{ route('mentor.apply') }}" wire:navigate class="inline-flex items-center justify-center rounded-lg bg-ue-brand px-4 py-2 text-sm font-semibold text-white hover:bg-ue-brand-dark">
                    Đăng ký làm mentor
                </a>
            @endif
        </div>
    </div>

    @if ($ownMentorProfile && $ownMentorProfile->is_active)
        <div class="mb-5 rounded-2xl border border-blue-100 bg-blue-50 px-4 py-3 text-sm text-blue-900">
            <span class="font-bold">Bạn đã là mentor.</span>
            <span>Muốn đổi ảnh, chủ đề hỗ trợ, trạng thái nhận yêu cầu hoặc visibility thì vào thiết lập hồ sơ mentor.</span>
        </div>
    @endif

    {{-- Search --}}
    <div class="mb-5 space-y-4">
        <div class="relative">
            <svg class="pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
            <input
                wire:model.live.debounce.300ms="search"
                type="search"
                placeholder="Tìm mentor theo tên, chuyên môn, chủ đề..."
                class="w-full rounded-xl border-slate-200 pl-10 pr-4 py-2.5 text-sm focus:border-ue-brand focus:ring-ue-brand/20"
            />
        </div>

        {{-- Filter toggle --}}
        <button @click="showFilters = !showFilters" class="flex items-center gap-2 text-xs font-bold text-ue-brand">
            <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 3H2l8 9.46V19l4 2v-8.54L22 3z"/></svg>
            Bộ lọc
            @php $filterCount = count($selectedTopics) + ($availabilityFilter === 'available' ? 1 : 0); @endphp
            @if ($filterCount > 0)
                <span class="inline-flex items-center justify-center bg-ue-brand text-white rounded-full px-1.5 py-0.5 text-[10px] font-bold min-w-[18px]">{{ $filterCount }}</span>
            @endif
        </button>

        {{-- Filter chips --}}
        <div
            x-show="showFilters"
            class="flex flex-wrap items-center gap-2"
        >


            @if (! empty($expertiseTopics))
                <div class="w-full flex flex-wrap items-center gap-1.5">
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mr-1">Chuyên môn</span>
                    @foreach ($expertiseTopics as $topic)
                        <button
                            wire:click="selectTopic('{{ $topic }}')"
                            class="rounded-full px-3 py-1.5 text-xs font-semibold border transition
                            {{ in_array($topic, $this->selectedTopics) ? 'bg-ue-brand border-ue-brand text-white font-bold' : 'bg-white border-slate-200 text-slate-600 hover:bg-slate-50' }}"
                        >
                            {{ $topic }}
                        </button>
                    @endforeach
                </div>
            @endif

@php
    $preferredRequestOptions = [
        'cv_review' => 'Review CV / Portfolio',
        'career_advice' => 'Định hướng nghề nghiệp',
        'academic_guidance' => 'Định hướng học thuật',
        'subject_support' => 'Hỗ trợ môn học',
        'research_guidance' => 'Nghiên cứu khoa học',
        'interview_prep' => 'Chuẩn bị phỏng vấn',
        'internship_experience' => 'Kinh nghiệm thực tập',
        'other' => 'Khác',
    ];
@endphp
            @if (! empty($helpTopics))
                <div class="w-full flex flex-wrap items-center gap-1.5">
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mr-1">Hỗ trợ</span>
                    @foreach ($helpTopics as $type)
                        @if (isset($preferredRequestOptions[$type]))
                            <button
                                wire:click="selectTopic('{{ $type }}')"
                                class="rounded-full px-3 py-1.5 text-xs font-semibold border transition
                                {{ in_array($type, $this->selectedTopics) ? 'bg-ue-brand border-ue-brand text-white font-bold' : 'bg-white border-slate-200 text-slate-600 hover:bg-slate-50' }}"
                            >
                                {{ $preferredRequestOptions[$type] }}
                            </button>
                        @endif
                    @endforeach
                </div>
            @endif

            @if ($selectedTopics || $availabilityFilter || $search)
                <button
                    wire:click="clearFilters"
                    class="text-xs font-bold text-red-500 hover:underline ml-1 whitespace-nowrap"
                >
                    Xoá bộ lọc
                </button>
            @endif
        </div>

        <p class="text-xs text-slate-400 font-medium">
            @if ($mentors->total() > 0)
                {{ $mentors->total() }} mentor{{ $mentors->total() !== 1 ? 's' : '' }} phù hợp
            @endif
        </p>
    </div>

    {{-- Skeleton loading --}}
    <div wire:loading.delay wire:target="search,selectTopic,availabilityFilter,nextPage,previousPage,gotoPage" class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        @foreach (range(1, 6) as $i)
            <div class="rounded-xl border border-slate-200 bg-white p-4 animate-pulse">
                <div class="flex items-start gap-3">
                    <div class="h-10 w-10 rounded-full bg-slate-200 flex-shrink-0"></div>
                    <div class="flex-1 space-y-2">
                        <div class="h-4 w-24 bg-slate-200 rounded"></div>
                        <div class="h-3 w-16 bg-slate-100 rounded"></div>
                    </div>
                </div>
                <div class="mt-4 space-y-2">
                    <div class="h-4 w-3/4 bg-slate-200 rounded"></div>
                    <div class="h-3 w-full bg-slate-100 rounded"></div>
                </div>
                <div class="mt-3 flex gap-1.5">
                    <div class="h-5 w-14 bg-slate-100 rounded-full"></div>
                    <div class="h-5 w-20 bg-slate-100 rounded-full"></div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Mentor grid --}}
    <div
        wire:loading.delay.remove
        wire:target="search,selectTopic,availabilityFilter,nextPage,previousPage,gotoPage"
        class="grid gap-4 md:grid-cols-2 xl:grid-cols-3"
    >
        @forelse ($mentors as $mentor)
            @php
                $mentorUserProfileUrl = route('profile.show', $mentor->user);
            @endphp
            <article class="ue-loadable-card rounded-xl border border-slate-200 bg-white p-4 shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all">
                <div class="flex items-start gap-3">
                    <a href="{{ $mentorUserProfileUrl }}" wire:navigate class="block rounded-full focus:outline-none focus:ring-2 focus:ring-ue-brand/30 flex-shrink-0" aria-label="Xem trang cá nhân của {{ $mentor->user->name }}">
                        <x-ui.avatar :user="$mentor->user" size="md" />
                    </a>
                    <div class="min-w-0 flex-1">
                        <a href="{{ $mentorUserProfileUrl }}" wire:navigate class="block truncate text-sm font-bold text-slate-900 hover:text-ue-brand">
                            {{ $mentor->user->name }}
                        </a>
                        <div class="mt-0.5 flex items-center gap-1.5">
                            <span class="inline-block w-2 h-2 rounded-full {{ $mentor->availability_status === \App\Enums\MentorAvailabilityStatus::Available ? 'bg-emerald-500' : 'bg-slate-300' }}"></span>
                            <span class="text-xs font-semibold {{ $mentor->availability_status === \App\Enums\MentorAvailabilityStatus::Available ? 'text-emerald-700' : 'text-slate-400' }}">
                                {{ $mentor->availability_status->label() }}
                            </span>
                        </div>
                    </div>
                </div>

                <p class="mt-3 text-sm font-bold text-slate-800 line-clamp-2">{{ $mentor->headline ?: 'Mentor UEConnect' }}</p>

                <p class="mt-1.5 line-clamp-2 text-sm text-slate-500">{{ $mentor->bio ?: 'Sẵn sàng hỗ trợ định hướng học tập và nghề nghiệp.' }}</p>

                <div class="mt-3 flex flex-wrap gap-1.5">
                    @foreach (array_slice($mentor->expertise_topics ?? [], 0, 3) as $topic)
                        <button
                            wire:click="selectTopic('{{ $topic }}')"
                            title="Lọc theo chủ đề {{ $topic }}"
                            class="rounded-full px-2.5 py-1 text-xs font-semibold border transition-colors
                            {{ in_array($topic, $this->selectedTopics) ? 'bg-ue-brand/10 border-ue-brand/30 text-ue-brand ring-2 ring-ue-brand/40' : 'bg-slate-100 border-transparent text-slate-600 hover:bg-ue-brand-soft hover:text-ue-brand' }}"
                        >
                            {{ $topic }}
                        </button>
                    @endforeach
                    @if (count($mentor->expertise_topics ?? []) > 3)
                        <span class="text-xs text-slate-400 font-medium self-center">+{{ count($mentor->expertise_topics) - 3 }}</span>
                    @endif
                </div>

                <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between">
                    <span class="text-xs text-slate-400 font-medium">
                        @if ($mentor->user->profile?->faculty)
                            {{ Str::limit($mentor->user->profile->faculty, 22) }}
                        @else
                            Mentor
                        @endif
                    </span>
                    <a href="{{ route('mentor.show', $mentor) }}" wire:navigate class="text-sm font-bold text-ue-brand hover:underline inline-flex items-center gap-1">
                        Xem hồ sơ
                        <svg class="h-3.5 w-3.5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
                    </a>
                </div>
            </article>
        @empty
            <div class="col-span-full rounded-xl border border-dashed border-slate-200 bg-white p-10 text-center">
                <svg class="mx-auto h-12 w-12 text-slate-300" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c0 1.1 2.7 3 6 3s6-1.9 6-3v-5"/></svg>
                <p class="mt-4 text-sm font-bold text-slate-700">Không tìm thấy mentor phù hợp</p>
                <p class="mt-1 text-sm text-slate-500 max-w-sm mx-auto">
                    @if ($search || $selectedTopics || $availabilityFilter)
                        Thử thay đổi bộ lọc hoặc tìm với từ khóa khác nhé.
                    @else
                        Hiện tại chưa có mentor khả dụng. Quay lại sau nhé!
                    @endif
                </p>
                @if ($search || $selectedTopics || $availabilityFilter)
                    <button
                        wire:click="clearFilters"
                        class="mt-4 inline-flex items-center justify-center rounded-xl bg-ue-brand px-4 py-2 text-sm font-bold text-white hover:bg-ue-brand-dark transition"
                    >
                        Xoá tất cả bộ lọc
                    </button>
                @endif
            </div>
        @endforelse
    </div>

    <div class="mt-6">{{ $mentors->links() }}</div>
</div>
