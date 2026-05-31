<?php

use App\Models\Community;
use App\Models\User;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    public string $search = '';
    public string $status = ''; // all | active | inactive | suspended | archived

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

    public function getCommunitiesProperty()
    {
        $query = Community::with(['creator'])->latest('created_at');

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('description', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->status && $this->status !== 'all') {
            $query->where('status', $this->status);
        }

        return $query->paginate(20);
    }

    public function getStatusesProperty()
    {
        return [
            ['value' => '', 'label' => 'Tất cả'],
            ['value' => 'active', 'label' => 'Hoạt động'],
            ['value' => 'inactive', 'label' => 'Không hoạt động'],
            ['value' => 'suspended', 'label' => 'Bị tạm khóa'],
            ['value' => 'archived', 'label' => 'Lưu trữ'],
        ];
    }
};
?>

<div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    {{-- Header --}}
    <div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-ue-text">Quản lý cộng đồng</h1>
            <p class="text-sm text-ue-text-secondary mt-1">Xem, tạo và quản lý các cộng đồng / câu lạc bộ UEConnect.</p>
        </div>
        <a href="{{ route('admin.communities.create') }}" class="inline-flex items-center px-4 py-2 bg-ue-brand text-white rounded-lg hover:bg-opacity-90 font-semibold text-sm">
            <x-ui.icon name="plus" size="xs" class="mr-2" />
            Tạo cộng đồng
        </a>
    </div>

    {{-- Filters --}}
    <x-ui.card class="mb-6">
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
            <div>
                <label for="search" class="block text-sm font-semibold text-ue-text mb-2">Tìm kiếm</label>
                <input type="text" id="search" wire:model.live="search" placeholder="Tên cộng đồng..." 
                    class="w-full px-3 py-2 border border-ue-border rounded-lg focus:outline-none focus:ring-2 focus:ring-ue-brand">
            </div>
            <div>
                <label for="status" class="block text-sm font-semibold text-ue-text mb-2">Trạng thái</label>
                <select id="status" wire:model.live="status" class="w-full px-3 py-2 border border-ue-border rounded-lg focus:outline-none focus:ring-2 focus:ring-ue-brand">
                    @foreach ($this->statuses as $stat)
                        <option value="{{ $stat['value'] }}">{{ $stat['label'] }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </x-ui.card>

    {{-- Communities Table --}}
    <x-ui.card padding="none" class="overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-ue-border">
                <thead class="bg-ue-surface-subtle">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-ue-text-muted uppercase">Tên cộng đồng</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-ue-text-muted uppercase">Người tạo</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-ue-text-muted uppercase">Trạng thái</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-ue-text-muted uppercase">Ngày tạo</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-semibold text-ue-text-muted uppercase">Hành động</th>
                    </tr>
                </thead>
                <tbody class="bg-ue-surface divide-y divide-ue-border text-sm">
                    @forelse ($this->communities as $community)
                        <tr class="hover:bg-ue-surface-hover transition-colors">
                            <td class="px-6 py-3 font-semibold text-ue-text">
                                <a href="{{ route('admin.communities.show', $community->id) }}" class="hover:text-ue-brand">
                                    {{ $community->name }}
                                </a>
                            </td>
                            <td class="px-6 py-3 text-ue-text">{{ $community->creator?->name ?? 'N/A' }}</td>
                            <td class="px-6 py-3">
                                @php
                                    $statusColors = [
                                        'active' => 'green',
                                        'inactive' => 'gray',
                                        'suspended' => 'red',
                                        'archived' => 'slate',
                                    ];
                                    $color = $statusColors[$community->status] ?? 'gray';
                                @endphp
                                <span class="px-2 py-1 rounded-full text-xs font-semibold bg-{{ $color }}-100 text-{{ $color }}-800">
                                    {{ ucfirst($community->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-3 text-ue-text-muted">{{ $community->created_at->format('d/m/Y') }}</td>
                            <td class="px-6 py-3 text-right">
                                <a href="{{ route('admin.communities.show', $community->id) }}" class="text-ue-brand hover:underline text-xs font-semibold">
                                    Chi tiết
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-ue-text-muted">
                                Không tìm thấy cộng đồng nào
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="px-6 py-4 bg-ue-surface-subtle border-t border-ue-border">
            {{ $this->communities->links('pagination::simple-tailwind') }}
        </div>
    </x-ui.card>
</div>
