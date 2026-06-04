<?php

use App\Enums\PostStatus;
use App\Enums\PostType;
use App\Models\OpportunityDetail;
use App\Models\Post;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new #[Layout('layouts.app', ['shell' => 'admin'])] class extends Component
{
    use WithPagination;

    public string $search = '';
    public string $status = 'pending_review';

    protected $queryString = [
        'search' => ['except' => ''],
        'status' => ['except' => ''],
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatus(): void
    {
        $this->resetPage();
    }

    public function getPostsProperty()
    {
        $query = Post::with(['user.profile', 'opportunityDetail'])
            ->where('post_type', PostType::OPPORTUNITY->value)
            ->latest('created_at');

        if ($this->status === 'pending_review') {
            $query->where('status', PostStatus::PENDING_REVIEW->value);
        } elseif ($this->status === 'approved') {
            $query->whereIn('status', [PostStatus::PUBLISHED->value, PostStatus::EDITED->value]);
        } elseif ($this->status === 'rejected') {
            $query->where('status', PostStatus::REJECTED->value);
        }

        if ($this->search) {
            $search = $this->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('opportunityDetail', function ($od) use ($search) {
                    $od->where('company', 'like', "%{$search}%")
                        ->orWhere('position', 'like', "%{$search}%");
                })
                ->orWhere('body', 'like', "%{$search}%")
                ->orWhereHas('user', function ($u) use ($search) {
                    $u->where('name', 'like', "%{$search}%");
                });
            });
        }

        return $query->paginate(15);
    }
}; ?>
<div class="max-w-5xl mx-auto py-8 px-4">
    <div class="mb-6">
        <h1 class="text-xl font-bold text-slate-800">Duyệt cơ hội việc làm</h1>
        <p class="text-xs text-slate-400 font-medium mt-0.5">Kiểm duyệt bài viết cơ hội từ cựu sinh viên trước khi public</p>
    </div>

    <x-ui.toast />

    {{-- Filters --}}
    <div class="flex flex-wrap items-center gap-3 mb-6">
        <div class="relative flex-1 min-w-[200px] max-w-xs">
            <x-ui.icon name="search" size="xs" class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" />
            <input
                type="text"
                wire:model.live.debounce.300ms="search"
                placeholder="Tìm kiếm công ty, vị trí, người đăng..."
                class="w-full pl-9 pr-4 py-2 text-xs rounded-xl border border-slate-200 focus:border-ue-brand focus:ring-ue-brand-soft bg-white"
            />
        </div>
        <div class="flex items-center gap-2">
            <button type="button" wire:click="$set('status', 'pending_review')" class="px-3 py-1.5 text-xs font-bold rounded-lg border transition-colors {{ $status === 'pending_review' ? 'bg-ue-brand-soft text-ue-brand border-ue-brand/20' : 'bg-white text-slate-500 border-slate-200 hover:bg-slate-50' }}">
                Chờ duyệt
            </button>
            <button type="button" wire:click="$set('status', 'approved')" class="px-3 py-1.5 text-xs font-bold rounded-lg border transition-colors {{ $status === 'approved' ? 'bg-green-50 text-green-700 border-green-200' : 'bg-white text-slate-500 border-slate-200 hover:bg-slate-50' }}">
                Đã duyệt
            </button>
            <button type="button" wire:click="$set('status', 'rejected')" class="px-3 py-1.5 text-xs font-bold rounded-lg border transition-colors {{ $status === 'rejected' ? 'bg-red-50 text-red-700 border-red-200' : 'bg-white text-slate-500 border-slate-200 hover:bg-slate-50' }}">
                Từ chối
            </button>
        </div>
    </div>

    {{-- Posts List --}}
    <div class="space-y-3">
        @forelse ($this->posts as $post)
            @php $opp = $post->opportunityDetail; @endphp
            <div class="bg-white border border-slate-200 rounded-xl p-4" wire:key="opp-{{ $post->id }}">
                <div class="flex items-start justify-between gap-4">
                    <div class="min-w-0 flex-1 space-y-2">
                        {{-- Header: author + status --}}
                        <div class="flex items-center gap-2">
                            <x-ui.avatar :user="$post->user" size="xs" />
                            <span class="text-xs font-bold text-slate-700">{{ $post->user->name }}</span>
                            @if ($post->status->value === 'pending_review')
                                <span class="px-2 py-0.5 bg-amber-50 text-amber-700 border border-amber-200 rounded-full text-[9px] font-bold leading-none">Chờ duyệt</span>
                            @elseif ($post->status->value === 'published' || $post->status->value === 'edited')
                                <span class="px-2 py-0.5 bg-green-50 text-green-700 border border-green-200 rounded-full text-[9px] font-bold leading-none">Đã duyệt</span>
                            @elseif ($post->status->value === 'rejected')
                                <span class="px-2 py-0.5 bg-red-50 text-red-700 border border-red-200 rounded-full text-[9px] font-bold leading-none">Từ chối</span>
                            @endif
                        </div>

                        {{-- Opportunity detail summary --}}
                        @if ($opp)
                            <div class="flex flex-wrap gap-x-4 gap-y-1 text-xs text-slate-600">
                                <span class="font-bold text-slate-800">{{ $opp->company }}</span>
                                <span>— {{ $opp->position }}</span>
                                @if ($opp->location)
                                    <span class="flex items-center gap-1 text-slate-400">
                                        <x-ui.icon name="map-pin" size="xs" />
                                        {{ $opp->location }}
                                    </span>
                                @endif
                                @if ($opp->application_deadline)
                                    <span class="flex items-center gap-1 text-slate-400">
                                        <x-ui.icon name="calendar" size="xs" />
                                        Hạn: {{ $opp->application_deadline->format('d/m/Y') }}
                                    </span>
                                @endif
                            </div>
                            @if ($opp->field_tags)
                                <div class="flex flex-wrap gap-1">
                                    @foreach ($opp->field_tags as $tag)
                                        <span class="px-2 py-0.5 bg-slate-100 text-slate-600 text-[9px] font-bold rounded-full leading-none">{{ $tag }}</span>
                                    @endforeach
                                </div>
                            @endif
                        @endif

                        {{-- Body excerpt --}}
                        <p class="text-xs text-slate-500 line-clamp-2">{{ $post->body }}</p>
                    </div>

                    {{-- Actions --}}
                    <div class="flex flex-col items-stretch gap-2 flex-shrink-0 min-w-[120px]">
                        <a href="{{ route('admin.opportunities.detail', $post) }}" class="inline-flex items-center justify-center gap-1.5 px-3 py-2 text-xs font-bold text-ue-brand bg-ue-brand-soft border border-ue-brand/10 rounded-lg hover:bg-ue-brand hover:text-white transition-all">
                            <x-ui.icon name="eye" size="xs" />
                            Chi tiết
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center py-12">
                <x-ui.empty-state
                    icon="briefcase"
                    title="Không có cơ hội nào"
                    description="{{ $status === 'pending_review' ? 'Chưa có cơ hội mới nào cần duyệt.' : ($status === 'approved' ? 'Chưa có cơ hội nào được duyệt.' : 'Không có cơ hội bị từ chối.') }}"
                />
            </div>
        @endforelse

        <div class="pt-4">
            {{ $this->posts->links() }}
        </div>
    </div>
</div>
