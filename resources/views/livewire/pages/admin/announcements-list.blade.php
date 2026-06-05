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
            <div>
                <x-ui.label for="search" class="text-xs">Tìm tiêu đề</x-ui.label>
                <x-ui.input id="search" wire:model.live="search" placeholder="Tìm tiêu đề..." class="mt-1 h-9 text-xs" />
            </div>
            <div>
                <x-ui.label for="status" class="text-xs">Trạng thái</x-ui.label>
                <x-ui.select id="status" wire:model.live="status" class="mt-1 h-9 text-xs py-1">
                    <option value="">-- Tất cả --</option>
                    <option value="draft">Draft (Nháp)</option>
                    <option value="published">Published (Đang phát hành)</option>
                    <option value="expired">Expired (Hết hạn)</option>
                </x-ui.select>
            </div>
        </div>
    </x-ui.card>

    <x-ui.card padding="none" class="overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-ue-border text-sm text-left">
                <thead class="bg-ue-surface-subtle text-ue-text-muted text-xs font-bold uppercase tracking-wider">
                    <tr>
                        <th scope="col" class="px-6 py-4">Tiêu đề</th>
                        <th scope="col" class="px-6 py-4">Loại</th>
                        <th scope="col" class="px-6 py-4">Trạng thái</th>
                        <th scope="col" class="px-6 py-4">Người tạo</th>
                        <th scope="col" class="px-6 py-4 text-right">Hành động</th>
                    </tr>
                </thead>
                <tbody class="bg-ue-surface divide-y divide-ue-border text-ue-text font-medium">
                    @forelse ($this->announcements as $a)
                        @php
                            $badgeVariant = match($a->status) {
                                'published' => 'success',
                                'expired' => 'neutral',
                                'draft' => 'warning',
                                default => 'neutral',
                            };
                            $typeLabel = match($a->type) {
                                'system_announcement' => 'Hệ thống',
                                'feature_update' => 'Tính năng',
                                'safety_notice' => 'Bảo mật',
                                default => $a->type,
                            };
                        @endphp
                        <tr class="hover:bg-ue-surface-hover transition-colors">
                            <td class="px-6 py-4">
                                <div class="font-bold">{{ $a->title }}</div>
                                <div class="text-xs text-ue-text-muted mt-0.5">
                                    {{ $a->starts_at?->format('d/m/Y') ?? 'Ngay' }} - {{ $a->expires_at?->format('d/m/Y') ?? 'Vô hạn' }}
                                </div>
                            </td>
                            <td class="px-6 py-4 text-xs text-ue-text-muted whitespace-nowrap">{{ $typeLabel }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <x-ui.badge :variant="$badgeVariant">
                                    {{ match($a->status) {
                                        'draft' => 'Nháp',
                                        'published' => 'Đã phát hành',
                                        'expired' => 'Hết hạn',
                                        default => $a->status,
                                    } }}
                                </x-ui.badge>
                            </td>
                            <td class="px-6 py-4 text-ue-text whitespace-nowrap">{{ $a->creator?->name ?? 'System' }}</td>
                            <td class="px-6 py-4 text-right whitespace-nowrap text-xs font-semibold">
                                <div class="flex justify-end items-center gap-2">
                                    {{-- Visually Disabled Edit Link to indicate incomplete feature --}}
                                    <span class="px-2.5 py-1.5 rounded-lg border border-ue-border opacity-40 cursor-not-allowed bg-ue-surface-subtle text-ue-text-muted select-none" title="Tính năng chỉnh sửa đang phát triển. Hãy xóa và tạo lại.">
                                        Sửa
                                    </span>

                                    @if ($a->status === 'draft')
                                        <form action="{{ route('admin.announcements.publish', $a) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="px-2.5 py-1.5 rounded-lg border border-ue-border bg-white hover:bg-ue-surface-hover text-green-600 transition-colors">
                                                Đăng
                                            </button>
                                        </form>
                                    @elseif ($a->status === 'published')
                                        <form action="{{ route('admin.announcements.expire', $a) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="px-2.5 py-1.5 rounded-lg border border-ue-border bg-white hover:bg-ue-surface-hover text-amber-600 transition-colors">
                                                Hết hạn
                                            </button>
                                        </form>
                                    @endif

                                    <form action="{{ route('admin.announcements.delete', $a) }}" method="POST" onsubmit="return confirm('Bạn có chắc muốn xóa thông báo này?');">
                                        @csrf
                                        <button type="submit" class="px-2.5 py-1.5 rounded-lg border border-ue-border bg-white hover:bg-red-50 hover:text-red-600 transition-colors">
                                            Xóa
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-ue-text-muted">
                                <x-ui.empty-state icon="bell" title="Chưa có thông báo nào" description="Hệ thống hiện tại chưa ghi nhận thông báo nào phù hợp." />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 bg-ue-surface-subtle border-t border-ue-border">{{ $this->announcements->links() }}</div>
    </x-ui.card>
</div>
