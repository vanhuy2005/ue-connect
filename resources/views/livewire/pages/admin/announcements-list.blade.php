<?php

use App\Models\Announcement;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    public string $search = '';
    public string $status = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'status' => ['except' => ''],
    ];

    public function updatingSearch(): void { $this->resetPage(); }
    public function updatingStatus(): void { $this->resetPage(); }

    public function getAnnouncementsProperty()
    {
        $q = Announcement::latest('created_at');
        if ($this->search) {
            $q->where('title', 'like', '%' . $this->search . '%');
        }
        if ($this->status) {
            $q->where('status', $this->status);
        }
        return $q->paginate(20);
    }
};
?>

<div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-ue-text">Thông báo hệ thống</h1>
            <p class="text-sm text-ue-text-secondary mt-1">Tạo, chỉnh sửa và quản lý các thông báo đến người dùng.</p>
        </div>
        <a href="{{ route('admin.announcements.create') }}" class="inline-flex items-center px-4 py-2 bg-ue-brand text-white rounded-lg">Tạo thông báo</a>
    </div>

    <x-ui.card class="mb-6">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <input wire:model.live="search" class="w-full px-3 py-2 border rounded-lg" placeholder="Tìm tiêu đề">
            <select wire:model.live="status" class="w-full px-3 py-2 border rounded-lg">
                <option value="">-- Trạng thái --</option>
                <option value="draft">Draft</option>
                <option value="published">Published</option>
                <option value="expired">Expired</option>
            </select>
        </div>
    </x-ui.card>

    <x-ui.card padding="none" class="overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-ue-border text-sm">
                <thead class="bg-ue-surface-subtle text-ue-text-muted text-xs uppercase">
                    <tr>
                        <th class="px-6 py-3">Tiêu đề</th>
                        <th class="px-6 py-3">Loại</th>
                        <th class="px-6 py-3">Trạng thái</th>
                        <th class="px-6 py-3">Người tạo</th>
                        <th class="px-6 py-3 text-right">Hành động</th>
                    </tr>
                </thead>
                <tbody class="bg-ue-surface divide-y divide-ue-border">
                    @forelse ($this->announcements as $a)
                        <tr class="hover:bg-ue-surface-hover">
                            <td class="px-6 py-3">{{ $a->title }}<div class="text-xs text-ue-text-muted">{{ $a->starts_at?->format('d/m/Y') ?? '' }} - {{ $a->expires_at?->format('d/m/Y') ?? '' }}</div></td>
                            <td class="px-6 py-3">{{ $a->type }}</td>
                            <td class="px-6 py-3">{{ ucfirst($a->status) }}</td>
                            <td class="px-6 py-3">{{ $a->creator?->name ?? 'System' }}</td>
                            <td class="px-6 py-3 text-right">
                                <a href="#" class="text-ue-text-muted text-xs">Edit</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-6 py-8 text-center text-ue-text-muted">Chưa có thông báo nào</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 bg-ue-surface-subtle border-t border-ue-border">{{ $this->announcements->links('pagination::simple-tailwind') }}</div>
    </x-ui.card>
</div>
