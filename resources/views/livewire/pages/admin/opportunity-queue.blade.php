<?php

use App\Enums\ModerationStatus;
use App\Enums\PostType;
use App\Models\Post;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new #[Layout('layouts.app', ['shell' => 'admin'])] class extends Component {
    use WithPagination;

    public string $search = '';
    public string $status = 'pending_review';

    protected array $queryString = [
        'search' => ['except' => ''],
        'status' => ['except' => 'pending_review'],
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatus(): void
    {
        $this->resetPage();
    }

    public function with(): array
    {
        $query = Post::with(['user.profile', 'opportunity'])
            ->where('post_type', PostType::OPPORTUNITY->value)
            ->latest('created_at');

        if ($this->status !== 'all') {
            if ($this->status === 'pending_review') {
                $query->where('moderation_status', ModerationStatus::PENDING->value);
            } elseif ($this->status === 'approved') {
                $query->where('moderation_status', ModerationStatus::APPROVED->value);
            } elseif ($this->status === 'rejected') {
                $query->where('moderation_status', ModerationStatus::REJECTED->value);
            }
        }

        if ($this->search !== '') {
            $term = '%'.$this->search.'%';
            $query->where(function ($q) use ($term) {
                $q->whereHas('opportunity', function ($od) use ($term) {
                    $od->where('category', 'like', $term);
                })
                ->orWhere('body', 'like', $term)
                ->orWhereHas('user', function ($u) use ($term) {
                    $u->where('name', 'like', $term)
                        ->orWhere('email', 'like', $term);
                });
            });
        }

        return [
            'posts' => $query->paginate(15),
        ];
    }
};
?>

<div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-ue-text">Duyệt cơ hội việc làm</h1>
            <p class="mt-1 text-sm text-ue-text-secondary">Kiểm duyệt các bài viết cơ hội việc làm trước khi đăng công khai lên bảng tin.</p>
        </div>
    </div>

    <x-ui.card class="mb-6">
        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <x-ui.label for="search" class="text-xs">Tìm kiếm</x-ui.label>
                <x-ui.input type="search" id="search" wire:model.live.debounce.250ms="search" placeholder="Tìm kiếm người đăng, nội dung..." class="mt-1 h-9 text-xs" />
            </div>
            <div>
                <x-ui.label for="status" class="text-xs">Trạng thái</x-ui.label>
                <x-ui.select id="status" wire:model.live="status" class="mt-1 h-9 text-xs py-1">
                    <option value="all">Tất cả trạng thái</option>
                    <option value="pending_review">Chờ duyệt</option>
                    <option value="approved">Đã duyệt</option>
                    <option value="rejected">Từ chối</option>
                </x-ui.select>
            </div>
        </div>
    </x-ui.card>

    <x-ui.card padding="none" class="overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-ue-border text-sm text-left">
                <thead class="bg-ue-surface-subtle text-xs font-bold text-ue-text-muted uppercase tracking-wider">
                    <tr>
                        <th scope="col" class="px-6 py-4">Người đăng</th>
                        <th scope="col" class="px-6 py-4">Danh mục</th>
                        <th scope="col" class="px-6 py-4">Trạng thái</th>
                        <th scope="col" class="px-6 py-4">Ngày đăng</th>
                        <th scope="col" class="px-6 py-4 text-right">Hành động</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-ue-border bg-ue-surface">
                    @forelse ($posts as $post)
                        @php
                            $opp = $post->opportunity;
                            $badgeVariant = match($post->moderation_status) {
                                ModerationStatus::PENDING => 'pending',
                                ModerationStatus::APPROVED => 'success',
                                ModerationStatus::REJECTED => 'rejected',
                                default => 'neutral',
                            };
                            $badgeIcon = match($post->moderation_status) {
                                ModerationStatus::PENDING => 'clock',
                                ModerationStatus::APPROVED => 'check-circle',
                                ModerationStatus::REJECTED => 'x-circle',
                                default => 'clock-x',
                            };
                            $statusLabel = match($post->moderation_status) {
                                ModerationStatus::PENDING => 'Chờ duyệt',
                                ModerationStatus::APPROVED => 'Đã duyệt',
                                ModerationStatus::REJECTED => 'Từ chối',
                                default => 'Hết hạn',
                            };
                        @endphp
                        <tr class="hover:bg-ue-surface-hover transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="font-bold text-ue-text">{{ $post->user?->name ?? 'N/A' }}</div>
                                <div class="text-xs text-ue-text-muted mt-0.5">{{ $post->user?->email }}</div>
                            </td>
                            <td class="px-6 py-4 text-ue-text whitespace-nowrap font-medium">
                                {{ $opp?->category === 'pedagogy' ? 'Sư phạm' : ($opp?->category === 'non_pedagogy' ? 'Ngoài sư phạm' : 'Khác') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <x-ui.badge :variant="$badgeVariant" :icon="$badgeIcon">
                                    {{ $statusLabel }}
                                </x-ui.badge>
                            </td>
                            <td class="px-6 py-4 text-xs text-ue-text-muted whitespace-nowrap">
                                {{ $post->created_at?->format('H:i d/m/Y') }}
                            </td>
                            <td class="px-6 py-4 text-right whitespace-nowrap">
                                <x-ui.button href="{{ route('admin.opportunities.detail', $post->id) }}" variant="secondary" size="sm" icon="eye">
                                    Chi tiết
                                </x-ui.button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-ue-text-muted">
                                <x-ui.empty-state icon="briefcase" title="Không tìm thấy cơ hội nào" description="Hiện tại không có cơ hội việc làm nào khớp với bộ lọc." />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="bg-ue-surface border-t border-ue-border px-6 py-4">
            {{ $posts->links() }}
        </div>
    </x-ui.card>
</div>
