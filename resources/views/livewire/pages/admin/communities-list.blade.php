<?php

use App\Models\Community;
use App\Models\User;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Computed;

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

    #[Computed]
    public function communities()
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

    #[Computed]
    public function statuses()
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
                <x-ui.label for="search" class="text-xs">Tìm kiếm</x-ui.label>
                <x-ui.input type="text" id="search" wire:model.live="search" placeholder="Tên cộng đồng..." class="mt-1 h-9 text-xs" />
            </div>
            <div>
                <x-ui.label for="status" class="text-xs">Trạng thái</x-ui.label>
                <x-ui.select id="status" wire:model.live="status" class="mt-1 h-9 text-xs py-1">
                    @foreach ($this->statuses as $stat)
                        <option value="{{ $stat['value'] }}">{{ $stat['label'] }}</option>
                    @endforeach
                </x-ui.select>
            </div>
        </div>
    </x-ui.card>

    {{-- Communities Table --}}
    <x-ui.card padding="none" class="overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-ue-border text-left">
                <thead class="bg-ue-surface-subtle text-ue-text-muted text-xs font-bold uppercase tracking-wider">
                    <tr>
                        <th scope="col" class="px-6 py-4">ID</th>
                        <th scope="col" class="px-6 py-4">Tên cộng đồng</th>
                        <th scope="col" class="px-6 py-4">Người tạo</th>
                        <th scope="col" class="px-6 py-4">Trạng thái</th>
                        <th scope="col" class="px-6 py-4">Ngày tạo</th>
                        <th scope="col" class="px-6 py-4 text-right">Hành động</th>
                    </tr>
                </thead>
                <tbody class="bg-ue-surface divide-y divide-ue-border text-sm text-ue-text font-medium">
                    @forelse ($this->communities as $community)
                        @php
                            $badgeVariant = match($community->status->value) {
                                'active' => 'success',
                                'inactive' => 'neutral',
                                'suspended' => 'danger',
                                'archived' => 'neutral',
                                'draft' => 'warning',
                                'pending_review' => 'info',
                                'hidden_by_moderation' => 'warning',
                                default => 'neutral',
                            };
                        @endphp
                        <tr class="hover:bg-ue-surface-hover transition-colors">
                            <td class="px-6 py-4 text-ue-text-muted text-xs font-semibold">#{{ $community->id }}</td>
                            <td class="px-6 py-4 font-bold">
                                <a href="{{ route('admin.communities.show', $community->id) }}" class="hover:text-ue-brand-active">
                                    {{ $community->name }}
                                </a>
                            </td>
                            <td class="px-6 py-4">{{ $community->creator?->name ?? 'N/A' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <x-ui.badge :variant="$badgeVariant">
                                    {{ $community->status->label() }}
                                </x-ui.badge>
                            </td>
                            <td class="px-6 py-4 text-xs text-ue-text-muted whitespace-nowrap">{{ $community->created_at->format('d/m/Y') }}</td>
                            <td class="px-6 py-4 text-right whitespace-nowrap">
                                <x-ui.button href="{{ route('admin.communities.show', $community->id) }}" variant="secondary" size="sm" icon="eye">
                                    Chi tiết
                                </x-ui.button>
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
