<?php

use App\Models\MentorProfile;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    public string $search = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function with(): array
    {
        $user = Auth::user();
        $query = MentorProfile::query()
            ->with('user.profile')
            ->discoverable()
            ->where('user_id', '!=', $user->id)
            ->latest('updated_at');

        if ($this->search !== '') {
            $term = '%'.$this->search.'%';
            $query->where(function ($builder) use ($term) {
                $builder->where('headline', 'like', $term)
                    ->orWhere('bio', 'like', $term)
                    ->orWhereHas('user', fn ($userQuery) => $userQuery->where('name', 'like', $term));
            });
        }

        return [
            'mentors' => $query->paginate(12),
            'ownMentorProfile' => $user->mentorProfile()->first(),
        ];
    }
};
?>

<div class="mx-auto max-w-6xl px-4 py-6 sm:px-6 lg:px-8">
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Mentor Connection</h1>
            <p class="mt-1 text-sm text-slate-500">Tìm mentor phù hợp và gửi yêu cầu cố vấn có cấu trúc.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            @if ($ownMentorProfile)
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

    @if ($ownMentorProfile)
        <div class="mb-5 rounded-2xl border border-blue-100 bg-blue-50 px-4 py-3 text-sm text-blue-900">
            <span class="font-bold">Bạn đã là mentor.</span>
            <span>Muốn đổi ảnh, chủ đề hỗ trợ, trạng thái nhận yêu cầu hoặc visibility thì vào thiết lập hồ sơ mentor.</span>
        </div>
    @endif

    <div class="mb-5">
        <input wire:model.live.debounce.250ms="search" type="search" placeholder="Tìm theo tên, chuyên môn, chủ đề..." class="w-full rounded-lg border-slate-200 text-sm shadow-sm focus:border-ue-brand focus:ring-ue-brand/20">
    </div>

    <div>
        <div
            class="grid gap-4 md:grid-cols-2 xl:grid-cols-3"
            wire:loading.delay.class="ue-content-loading"
            wire:target="search,nextPage,previousPage,gotoPage"
            aria-busy="false"
        >
            @forelse ($mentors as $mentor)
                @php
                    $mentorUserProfileUrl = route('profile.show', $mentor->user);
                @endphp
                <article class="ue-loadable-card rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
                    <div class="flex items-start gap-3">
                        <a href="{{ $mentorUserProfileUrl }}" wire:navigate class="block rounded-full focus:outline-none focus:ring-2 focus:ring-ue-brand/30" aria-label="Xem trang cá nhân của {{ $mentor->user->name }}">
                            <x-ui.avatar :user="$mentor->user" size="md" />
                        </a>
                        <div class="min-w-0">
                            <a href="{{ $mentorUserProfileUrl }}" wire:navigate class="truncate text-sm font-bold text-slate-900 hover:text-ue-brand hover:underline">{{ $mentor->user->name }}</a>
                            <p class="mt-1 text-xs font-semibold text-emerald-700">{{ $mentor->availability_status->label() }}</p>
                        </div>
                    </div>
                    <p class="mt-4 text-sm font-semibold text-slate-800 break-words line-clamp-2">{{ $mentor->headline ?: 'Mentor UEConnect' }}</p>
                    <p class="mt-2 line-clamp-3 text-sm text-slate-500 break-words">{{ $mentor->bio ?: 'Sẵn sàng hỗ trợ định hướng học tập và nghề nghiệp.' }}</p>
                    <div class="mt-3 flex flex-wrap gap-1.5">
                        @foreach (array_slice($mentor->expertise_topics ?? [], 0, 4) as $topic)
                            <span class="max-w-full truncate rounded-full bg-slate-100 px-2 py-1 text-xs font-semibold text-slate-600" title="{{ $topic }}">{{ $topic }}</span>
                        @endforeach
                    </div>
                    <div class="mt-4 flex items-center justify-between">
                        <span class="text-xs text-slate-400">Hoàn thiện {{ $mentor->getProfileCompletenessScore() }}%</span>
                        <a href="{{ route('mentor.show', $mentor) }}" wire:navigate class="text-sm font-semibold text-ue-brand hover:underline">Xem hồ sơ</a>
                    </div>
                </article>
            @empty
                <div class="col-span-full rounded-lg border border-dashed border-slate-200 bg-white p-8 text-center text-sm text-slate-500">
                    Chưa có mentor khả dụng.
                </div>
            @endforelse
        </div>
    </div>

    <div class="mt-6">{{ $mentors->links() }}</div>
</div>
