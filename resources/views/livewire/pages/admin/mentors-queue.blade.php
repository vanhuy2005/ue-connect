<?php

use App\Models\MentorAccess;
use App\Models\User;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    public string $search = '';
    public string $status = 'requested';

    protected $queryString = [
        'search' => ['except' => ''],
        'status' => ['except' => 'requested'],
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatus(): void
    {
        $this->resetPage();
    }

    public function getRequestsProperty()
    {
        $query = MentorAccess::with('user')->latest('created_at');

        if ($this->search) {
            $query->whereHas('user', function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('email', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->status) {
            $query->where('status', $this->status);
        }

        return $query->paginate(15);
    }
};
?>

<div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-ue-text">Yêu cầu Mentor</h1>
            <p class="text-sm text-ue-text-secondary mt-1">Duyệt các yêu cầu xin làm Mentor.</p>
        </div>
    </div>

    <x-ui.card class="mb-6">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <input type="text" wire:model.live="search" placeholder="Tìm kiếm tên hoặc email" class="w-full px-3 py-2 border rounded-lg">
            <select wire:model.live="status" class="w-full px-3 py-2 border rounded-lg">
                <option value="requested">Đã yêu cầu</option>
                <option value="under_review">Đang kiểm duyệt</option>
                <option value="approved">Đã duyệt</option>
                <option value="rejected">Từ chối</option>
            </select>
        </div>
    </x-ui.card>

    <x-ui.card padding="none" class="overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-ue-border">
                <thead class="bg-ue-surface-subtle">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-ue-text-muted uppercase">Người yêu cầu</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-ue-text-muted uppercase">Email</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-ue-text-muted uppercase">Lí do</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-ue-text-muted uppercase">Trạng thái</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-ue-text-muted uppercase">Hành động</th>
                    </tr>
                </thead>
                <tbody class="bg-ue-surface divide-y divide-ue-border text-sm">
                    @forelse ($this->requests as $r)
                        <tr class="hover:bg-ue-surface-hover transition-colors">
                            <td class="px-6 py-3">{{ $r->user?->name ?? 'N/A' }}</td>
                            <td class="px-6 py-3">{{ $r->user?->email ?? 'N/A' }}</td>
                            <td class="px-6 py-3">{{ Str::limit($r->note, 80) }}</td>
                            <td class="px-6 py-3">{{ ucfirst($r->status) }}</td>
                            <td class="px-6 py-3 text-right">
                                <a href="{{ route('admin.mentors.detail', $r->id) }}" class="text-ue-brand hover:underline text-xs font-semibold">Chi tiết</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-ue-text-muted">Không có yêu cầu</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 bg-ue-surface-subtle border-t border-ue-border">
            {{ $this->requests->links('pagination::simple-tailwind') }}
        </div>
    </x-ui.card>
</div>