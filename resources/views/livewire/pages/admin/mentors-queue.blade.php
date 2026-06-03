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

    <div class="mb-6 rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
        <div class="grid gap-4 sm:grid-cols-2">
            <input type="search" wire:model.live.debounce.250ms="search" placeholder="Tìm kiếm tên hoặc email" class="w-full rounded-lg border-slate-200 text-sm">
            <select wire:model.live="status" class="w-full rounded-lg border-slate-200 text-sm">
                <option value="all">Tất cả trạng thái</option>
                @foreach ($statuses as $statusCase)
                    <option value="{{ $statusCase->value }}">{{ $statusCase->label() }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-4 py-3 text-left font-semibold text-slate-600">Người yêu cầu</th>
                    <th class="px-4 py-3 text-left font-semibold text-slate-600">Vai trò</th>
                    <th class="px-4 py-3 text-left font-semibold text-slate-600">Trạng thái</th>
                    <th class="px-4 py-3 text-left font-semibold text-slate-600">Gửi lúc</th>
                    <th class="px-4 py-3 text-right font-semibold text-slate-600">Hành động</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($requests as $request)
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3">
                            <div class="font-semibold text-slate-900">{{ $request->user?->name ?? 'N/A' }}</div>
                            <div class="text-xs text-slate-500">{{ $request->user?->email }}</div>
                        </td>
                        <td class="px-4 py-3 text-slate-600">{{ $request->requested_role_context }}</td>
                        <td class="px-4 py-3">
                            <span class="rounded-full bg-slate-100 px-2 py-1 text-xs font-semibold text-slate-700">{{ $request->status->label() }}</span>
                        </td>
                        <td class="px-4 py-3 text-slate-500">{{ $request->created_at?->format('d/m/Y H:i') }}</td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('admin.mentors.detail', $request->id) }}" class="font-semibold text-ue-brand hover:underline">Chi tiết</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-8 text-center text-slate-500">Không có yêu cầu mentor phù hợp.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="border-t border-slate-100 px-4 py-3">{{ $requests->links() }}</div>
    </div>
</div>
