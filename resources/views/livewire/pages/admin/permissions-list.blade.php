<?php

use App\Models\PermissionGrant;
use App\Models\User;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use App\Services\AuditLogService;

new class extends Component {
    use WithPagination;

    public string $search = '';
    public string $permission = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'permission' => ['except' => ''],
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingPermission(): void
    {
        $this->resetPage();
    }

    public function getGrantsProperty()
    {
        $query = PermissionGrant::with(['user', 'granter'])->latest('created_at');

        if ($this->search) {
            $query->whereHas('user', function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('email', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->permission) {
            $query->where('permission_key', $this->permission);
        }

        return $query->paginate(20);
    }

    public function revoke(int $id): void
    {
        $grant = PermissionGrant::find($id);
        if (!$grant) {
            session()->flash('error', 'Grant không tồn tại');
            return;
        }

        $before = $grant->toArray();
        $grant->status = 'revoked';
        $grant->revoked_at = now();
        $grant->save();

        AuditLogService::log(
            actorId: request()->user()?->id,
            actorType: 'admin',
            actionKey: 'permission.revoke',
            targetType: 'permission_grant',
            targetId: $grant->id,
            beforeSnapshot: $before,
            afterSnapshot: $grant->toArray(),
            reason: 'revoked by admin'
        );

        session()->flash('success', 'Đã thu hồi quyền.');
    }
};
?>

<div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-ue-text">Quyền & Cấp quyền</h1>
            <p class="text-sm text-ue-text-secondary mt-1">Quản lý các quyền đã cấp theo phạm vi toàn cục hoặc theo đối tượng.</p>
        </div>
        <a href="{{ route('admin.permissions.create') }}" class="inline-flex items-center px-4 py-2 bg-ue-brand text-white rounded-lg hover:bg-opacity-90 font-semibold text-sm">
            Tạo cấp quyền
        </a>
    </div>

    <x-ui.card class="mb-6">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <input type="text" wire:model.live="search" placeholder="Tìm người dùng" class="w-full px-3 py-2 border rounded-lg">
            <input type="text" wire:model.live="permission" placeholder="Mã quyền" class="w-full px-3 py-2 border rounded-lg">
        </div>
    </x-ui.card>

    <x-ui.card padding="none" class="overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-ue-border">
                <thead class="bg-ue-surface-subtle">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-ue-text-muted uppercase">Người dùng</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-ue-text-muted uppercase">Quyền</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-ue-text-muted uppercase">Phạm vi</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-ue-text-muted uppercase">Được cấp bởi</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-ue-text-muted uppercase">Trạng thái</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-ue-text-muted uppercase">Hành động</th>
                    </tr>
                </thead>
                <tbody class="bg-ue-surface divide-y divide-ue-border text-sm">
                    @forelse ($this->grants as $g)
                        <tr class="hover:bg-ue-surface-hover transition-colors">
                            <td class="px-6 py-3">{{ $g->user?->name ?? 'Không rõ' }}<div class="text-xs text-ue-text-muted">{{ $g->user?->email ?? '' }}</div></td>
                            <td class="px-6 py-3">{{ $g->permission_key }}</td>
                            <td class="px-6 py-3">{{ $g->scope_type ? $g->scope_type . ':' . $g->scope_id : 'toàn cục' }}</td>
                            <td class="px-6 py-3">{{ $g->granter?->name ?? 'Hệ thống' }}</td>
                            <td class="px-6 py-3">{{ ucfirst($g->status) }}</td>
                            <td class="px-6 py-3 text-right">
                                <button wire:click="revoke({{ $g->id }})" onclick="return confirm('Bạn có chắc muốn thu hồi quyền này?')" class="text-red-600 text-xs font-semibold">Thu hồi</button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-ue-text-muted">Chưa có quyền nào được cấp</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 bg-ue-surface-subtle border-t border-ue-border">
            {{ $this->grants->links('pagination::simple-tailwind') }}
        </div>
    </x-ui.card>
</div>