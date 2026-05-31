<?php

use App\Models\AuditLog;
use App\Models\User;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    public string $search = '';
    public string $action = '';
    public string $actor_id = '';
    public string $target_type = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'action' => ['except' => ''],
        'actor_id' => ['except' => ''],
        'target_type' => ['except' => ''],
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingAction(): void
    {
        $this->resetPage();
    }

    public function updatingActorId(): void
    {
        $this->resetPage();
    }

    public function updatingTargetType(): void
    {
        $this->resetPage();
    }

    public function getActionsProperty()
    {
        return [
            'approve_verification' => 'Phê duyệt xác thực',
            'reject_verification' => 'Từ chối xác thực',
            'need_more_information' => 'Yêu cầu thêm thông tin',
            'suspend_user' => 'Tạm khóa tài khoản',
            'ban_user' => 'Cấm tài khoản',
            'reactivate_user' => 'Kích hoạt lại tài khoản',
            'grant_permission' => 'Cấp quyền hạn',
            'revoke_permission' => 'Thu hồi quyền hạn',
        ];
    }

    public function getAdminsProperty()
    {
        return User::whereHas('roles', function ($q) {
            $q->where('name', 'admin');
        })->get();
    }

    public function getLogsProperty()
    {
        $query = AuditLog::with('actor')
            ->latest('created_at');

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('target_id', 'like', '%' . $this->search . '%')
                  ->orWhere('action_key', 'like', '%' . $this->search . '%')
                  ->orWhereHas('actor', function ($q) {
                      $q->where('name', 'like', '%' . $this->search . '%');
                  });
            });
        }

        if ($this->action) {
            $query->where('action_key', $this->action);
        }

        if ($this->actor_id) {
            $query->where('actor_id', $this->actor_id);
        }

        if ($this->target_type) {
            $query->where('target_type', $this->target_type);
        }

        return $query->paginate(20);
    }
}; ?>

<div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    {{-- Header --}}
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-ue-text">Nhật ký kiểm toán (Audit Log)</h1>
        <p class="text-sm text-ue-text-secondary mt-1">Xem lịch sử tất cả hành động quản trị và hệ thống.</p>
    </div>

    {{-- Filters --}}
    <x-ui.card class="mb-6">
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
            {{-- Search --}}
            <div>
                <x-ui.label for="search" class="text-xs">Tìm kiếm</x-ui.label>
                <x-ui.input wire:model.live.debounce.300ms="search" id="search" placeholder="Target ID, action..." class="mt-1 h-9 text-xs" />
            </div>

            {{-- Action --}}
            <div>
                <x-ui.label for="action" class="text-xs">Hành động</x-ui.label>
                <x-ui.select wire:model.live="action" id="action" class="mt-1 h-9 text-xs py-1">
                    <option value="">-- Tất cả --</option>
                    @foreach ($this->actions as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </x-ui.select>
            </div>

            {{-- Admin --}}
            <div>
                <x-ui.label for="actor_id" class="text-xs">Admin</x-ui.label>
                <x-ui.select wire:model.live="actor_id" id="actor_id" class="mt-1 h-9 text-xs py-1">
                    <option value="">-- Tất cả --</option>
                    @foreach ($this->admins as $admin)
                        <option value="{{ $admin->id }}">{{ $admin->name }}</option>
                    @endforeach
                </x-ui.select>
            </div>

            {{-- Target Type --}}
            <div>
                <x-ui.label for="target_type" class="text-xs">Loại mục tiêu</x-ui.label>
                <x-ui.select wire:model.live="target_type" id="target_type" class="mt-1 h-9 text-xs py-1">
                    <option value="">-- Tất cả --</option>
                    <option value="user">User</option>
                    <option value="verification_request">Xác thực</option>
                    <option value="report">Báo cáo</option>
                    <option value="community">Cộng đồng</option>
                    <option value="permission_grant">Quyền hạn</option>
                </x-ui.select>
            </div>
        </div>
    </x-ui.card>

    {{-- Audit Logs Table --}}
    <x-ui.card padding="none" class="overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-ue-border text-left text-sm">
                <thead class="bg-ue-surface-subtle text-xs font-bold text-ue-text-muted uppercase tracking-wider">
                    <tr>
                        <th scope="col" class="px-6 py-4">Thời gian</th>
                        <th scope="col" class="px-6 py-4">Admin</th>
                        <th scope="col" class="px-6 py-4">Hành động</th>
                        <th scope="col" class="px-6 py-4">Mục tiêu</th>
                        <th scope="col" class="px-6 py-4">Lý do</th>
                        <th scope="col" class="px-6 py-4">IP Address</th>
                    </tr>
                </thead>
                <tbody class="bg-ue-surface divide-y divide-ue-border">
                    @forelse ($this->logs as $log)
                        <tr class="hover:bg-ue-surface-hover transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-xs font-semibold text-ue-text">{{ $log->created_at->format('H:i d/m/Y') }}</div>
                                <div class="text-xs text-ue-text-muted">{{ $log->created_at->diffForHumans() }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if ($log->actor)
                                    <div class="text-sm font-semibold text-ue-text">{{ $log->actor->name }}</div>
                                    <div class="text-xs text-ue-text-muted">{{ $log->actor->email }}</div>
                                @else
                                    <span class="text-xs text-ue-text-muted">{{ $log->actor_type === 'system' ? 'System' : 'Unknown' }}</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <x-ui.badge variant="info">
                                    {{ ucfirst(str_replace('_', ' ', $log->action_key)) }}
                                </x-ui.badge>
                            </td>
                            <td class="px-6 py-4 text-sm">
                                <div class="font-mono text-xs text-ue-text">{{ $log->target_type }}</div>
                                <div class="text-xs text-ue-text-muted">ID: {{ $log->target_id }}</div>
                            </td>
                            <td class="px-6 py-4 max-w-xs">
                                @if ($log->reason)
                                    <p class="text-sm text-ue-text truncate" title="{{ $log->reason }}">{{ $log->reason }}</p>
                                @else
                                    <span class="text-xs text-ue-text-disabled">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-xs font-mono text-ue-text-muted">
                                {{ $log->ip_address ?? 'N/A' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <x-ui.empty-state icon="shield" title="Không tìm thấy nhật ký nào" description="Hiện tại không có hoạt động kiểm toán nào khớp với bộ lọc của bạn." />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="bg-ue-surface border-t border-ue-border px-6 py-4">
            {{ $this->logs->links() }}
        </div>
    </x-ui.card>
</div>
