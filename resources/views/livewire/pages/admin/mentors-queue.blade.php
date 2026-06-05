<?php

use App\Enums\MentorAccessStatus;
use App\Models\MentorAccessRequest;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    public string $search = '';
    public string $status = 'submitted';

    protected array $queryString = [
        'search' => ['except' => ''],
        'status' => ['except' => 'submitted'],
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
        $query = MentorAccessRequest::query()->with(['user.profile', 'reviewer'])->latest();

        if ($this->search !== '') {
            $term = '%'.$this->search.'%';
            $query->whereHas('user', fn ($userQuery) => $userQuery->where('name', 'like', $term)->orWhere('email', 'like', $term));
        }

        if ($this->status !== 'all') {
            $query->where('status', $this->status);
        }

        return [
            'requests' => $query->paginate(15),
            'statuses' => MentorAccessStatus::cases(),
        ];
    }
};
?>

<div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-ue-text">Quản lý Mentor</h1>
            <p class="mt-1 text-sm text-ue-text-secondary">Duyệt, theo dõi và quản trị vòng đời mentor.</p>
        </div>
    </div>

    <x-ui.card class="mb-6">
        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <x-ui.label for="search" class="text-xs">Tìm kiếm</x-ui.label>
                <x-ui.input type="search" id="search" wire:model.live.debounce.250ms="search" placeholder="Tìm kiếm tên hoặc email" class="mt-1 h-9 text-xs" />
            </div>
            <div>
                <x-ui.label for="status" class="text-xs">Trạng thái</x-ui.label>
                <x-ui.select id="status" wire:model.live="status" class="mt-1 h-9 text-xs py-1">
                    <option value="all">Tất cả trạng thái</option>
                    @foreach ($statuses as $statusCase)
                        <option value="{{ $statusCase->value }}">{{ $statusCase->label() }}</option>
                    @endforeach
                </x-ui.select>
            </div>
        </div>
    </x-ui.card>

    <x-ui.card padding="none" class="overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-ue-border text-sm text-left">
                <thead class="bg-ue-surface-subtle text-xs font-bold text-ue-text-muted uppercase tracking-wider">
                    <tr>
                        <th scope="col" class="px-6 py-4">Người yêu cầu</th>
                        <th scope="col" class="px-6 py-4">Vai trò</th>
                        <th scope="col" class="px-6 py-4">Trạng thái</th>
                        <th scope="col" class="px-6 py-4">Gửi lúc</th>
                        <th scope="col" class="px-6 py-4 text-right">Hành động</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-ue-border bg-ue-surface">
                    @forelse ($requests as $request)
                        @php
                            $badgeVariant = match($request->status) {
                                MentorAccessStatus::Submitted => 'pending',
                                MentorAccessStatus::Approved => 'success',
                                MentorAccessStatus::Rejected => 'rejected',
                                MentorAccessStatus::NeedMoreInfo => 'need-more-info',
                                default => 'neutral',
                            };
                        @endphp
                        <tr class="hover:bg-ue-surface-hover transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="font-bold text-ue-text">{{ $request->user?->name ?? 'N/A' }}</div>
                                <div class="text-xs text-ue-text-muted mt-0.5">{{ $request->user?->email }}</div>
                            </td>
                            <td class="px-6 py-4 text-ue-text-muted whitespace-nowrap">{{ $request->requested_role_context }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <x-ui.badge :variant="$badgeVariant">
                                    {{ $request->status->label() }}
                                </x-ui.badge>
                            </td>
                            <td class="px-6 py-4 text-xs text-ue-text-muted whitespace-nowrap">{{ $request->created_at?->format('H:i d/m/Y') }}</td>
                            <td class="px-6 py-4 text-right whitespace-nowrap">
                                <x-ui.button href="{{ route('admin.mentors.detail', $request->id) }}" variant="secondary" size="sm" icon="eye">
                                    Chi tiết
                                </x-ui.button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-ue-text-muted">
                                <x-ui.empty-state icon="graduation-cap" title="Không tìm thấy yêu cầu nào" description="Hiện tại không có yêu cầu mentor nào khớp với bộ lọc." />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="bg-ue-surface border-t border-ue-border px-6 py-4">
            {{ $requests->links() }}
        </div>
    </x-ui.card>
</div>
