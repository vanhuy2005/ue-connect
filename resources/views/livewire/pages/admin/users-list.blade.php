<?php

use App\Enums\AccountStatus;
use App\Enums\IdentityType;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;

new class extends Component {
    use WithPagination;

    public string $search = '';

    public string $role = '';

    public string $account_status = '';

    public string $identity_type = '';

    public string $trashed = 'active';

    public string $bulk_action = '';

    public string $bulk_reason = '';

    /** @var list<int> */
    public array $selectedUserIds = [];

    protected $queryString = [
        'search' => ['except' => ''],
        'role' => ['except' => ''],
        'account_status' => ['except' => ''],
        'identity_type' => ['except' => ''],
        'trashed' => ['except' => 'active'],
    ];

    public function updatingSearch(): void
    {
        $this->resetPageAndSelection();
    }

    public function updatingRole(): void
    {
        $this->resetPageAndSelection();
    }

    public function updatingAccountStatus(): void
    {
        $this->resetPageAndSelection();
    }

    public function updatingIdentityType(): void
    {
        $this->resetPageAndSelection();
    }

    public function updatingTrashed(): void
    {
        $this->resetPageAndSelection();
    }

    public function resetFilters(): void
    {
        $this->reset(['search', 'role', 'account_status', 'identity_type']);
        $this->trashed = 'active';
        $this->resetPageAndSelection();
    }

    public function deleteUser(int $userId): void
    {
        $this->authorize('manage_users');

        if (Auth::id() === $userId) {
            $this->addError('bulk_action', 'Bạn không thể xóa tài khoản của chính mình.');

            return;
        }

        $user = User::findOrFail($userId);
        $before = $user->toArray();

        DB::transaction(function () use ($user, $before): void {
            $user->delete();

            AuditLogService::log(
                actorId: Auth::id(),
                actorType: 'admin',
                actionKey: 'admin.user.delete',
                targetType: 'user',
                targetId: $user->id,
                beforeSnapshot: $before,
                afterSnapshot: $user->fresh()?->toArray(),
                reason: 'Soft delete tài khoản từ bảng quản trị.'
            );
        });

        $this->selectedUserIds = array_values(array_diff($this->selectedUserIds, [$userId]));
        session()->flash('success', 'Đã xóa mềm tài khoản.');
    }

    public function restoreUser(int $userId): void
    {
        $this->authorize('manage_users');

        $user = User::withTrashed()->findOrFail($userId);
        $before = $user->toArray();

        DB::transaction(function () use ($user, $before): void {
            $user->restore();

            AuditLogService::log(
                actorId: Auth::id(),
                actorType: 'admin',
                actionKey: 'admin.user.restore',
                targetType: 'user',
                targetId: $user->id,
                beforeSnapshot: $before,
                afterSnapshot: $user->fresh()?->toArray(),
                reason: 'Khôi phục tài khoản từ bảng quản trị.'
            );
        });

        session()->flash('success', 'Đã khôi phục tài khoản.');
    }

    public function applyBulkAction(): void
    {
        $this->authorize('manage_users');

        $this->validate([
            'bulk_action' => ['required', 'string', 'in:suspend,reactivate,delete,restore'],
            'bulk_reason' => ['nullable', 'string', 'max:1000'],
            'selectedUserIds' => ['required', 'array', 'min:1'],
            'selectedUserIds.*' => ['integer'],
        ]);

        $selectedIds = collect($this->selectedUserIds)
            ->map(fn ($id): int => (int) $id)
            ->unique()
            ->reject(fn (int $id): bool => $id === Auth::id())
            ->values();

        if ($selectedIds->isEmpty()) {
            $this->addError('bulk_action', 'Không có tài khoản hợp lệ để thao tác.');

            return;
        }

        $users = User::withTrashed()
            ->whereIn('id', $selectedIds)
            ->get();

        DB::transaction(function () use ($users): void {
            foreach ($users as $user) {
                $before = $user->toArray();
                $actionKey = 'admin.user.bulk_'.$this->bulk_action;

                if ($this->bulk_action === 'suspend') {
                    if ($user->trashed()) {
                        continue;
                    }

                    $user->forceFill([
                        'account_status' => AccountStatus::SUSPENDED,
                        'account_status_reason' => $this->bulk_reason ?: 'Tạm khóa hàng loạt từ bảng quản trị.',
                    ])->save();
                } elseif ($this->bulk_action === 'reactivate') {
                    if ($user->trashed()) {
                        continue;
                    }

                    $user->forceFill([
                        'account_status' => AccountStatus::ACTIVE,
                        'account_status_reason' => null,
                        'account_restricted_until' => null,
                    ])->save();
                } elseif ($this->bulk_action === 'delete') {
                    if (! $user->trashed()) {
                        $user->delete();
                    }
                } elseif ($this->bulk_action === 'restore') {
                    if ($user->trashed()) {
                        $user->restore();
                    }
                }

                AuditLogService::log(
                    actorId: Auth::id(),
                    actorType: 'admin',
                    actionKey: $actionKey,
                    targetType: 'user',
                    targetId: $user->id,
                    beforeSnapshot: $before,
                    afterSnapshot: User::withTrashed()->find($user->id)?->toArray(),
                    reason: $this->bulk_reason ?: 'Thao tác hàng loạt từ bảng quản trị.'
                );
            }
        });

        $this->reset(['bulk_action', 'bulk_reason', 'selectedUserIds']);
        session()->flash('success', 'Đã áp dụng thao tác hàng loạt.');
    }

    public function getUsersProperty()
    {
        return $this->baseQuery()->paginate(15);
    }

    public function getMetricsProperty(): array
    {
        return [
            'total' => User::withTrashed()->count(),
            'active' => User::where('account_status', AccountStatus::ACTIVE->value)->count(),
            'restricted' => User::whereIn('account_status', [
                AccountStatus::RESTRICTED->value,
                AccountStatus::SUSPENDED->value,
                AccountStatus::BANNED->value,
            ])->count(),
            'deleted' => User::onlyTrashed()->count(),
        ];
    }

    public function roleOptions()
    {
        return Role::query()
            ->where('guard_name', 'web')
            ->orderBy('name')
            ->pluck('name', 'name');
    }

    public function resetPageAndSelection(): void
    {
        $this->resetPage();
        $this->selectedUserIds = [];
    }

    protected function baseQuery()
    {
        $query = User::query()
            ->with('roles')
            ->latest('created_at');

        if ($this->trashed === 'deleted') {
            $query->onlyTrashed();
        } elseif ($this->trashed === 'all') {
            $query->withTrashed();
        }

        if ($this->search !== '') {
            $query->where(function ($q): void {
                $q->where('name', 'like', '%'.$this->search.'%')
                    ->orWhere('email', 'like', '%'.$this->search.'%')
                    ->orWhere('id', $this->search);
            });
        }

        if ($this->role !== '') {
            $query->whereHas('roles', function ($q): void {
                $q->where('name', $this->role);
            });
        }

        if ($this->account_status !== '') {
            $query->where('account_status', $this->account_status);
        }

        if ($this->identity_type !== '') {
            $query->where('intended_identity_type', $this->identity_type);
        }

        return $query;
    }

    public function statusLabel(mixed $status): string
    {
        $statusValue = $status instanceof AccountStatus ? $status->value : (string) $status;

        return match ($statusValue) {
            AccountStatus::ACTIVE->value => 'Hoạt động',
            AccountStatus::REGISTERED->value => 'Đăng ký',
            AccountStatus::PENDING_VERIFICATION->value => 'Chờ xác thực',
            AccountStatus::PROFILE_INCOMPLETE->value => 'Hồ sơ chưa hoàn tất',
            AccountStatus::RESTRICTED->value => 'Bị hạn chế',
            AccountStatus::SUSPENDED->value => 'Bị tạm khóa',
            AccountStatus::BANNED->value => 'Bị cấm',
            AccountStatus::DELETED->value => 'Đã xóa',
            default => $statusValue ?: 'Không rõ',
        };
    }

    public function statusVariant(mixed $status): string
    {
        $statusValue = $status instanceof AccountStatus ? $status->value : (string) $status;

        return match ($statusValue) {
            AccountStatus::ACTIVE->value => 'success',
            AccountStatus::REGISTERED->value,
            AccountStatus::PENDING_VERIFICATION->value => 'info',
            AccountStatus::PROFILE_INCOMPLETE->value,
            AccountStatus::RESTRICTED->value,
            AccountStatus::SUSPENDED->value => 'warning',
            AccountStatus::BANNED->value,
            AccountStatus::DELETED->value => 'danger',
            default => 'neutral',
        };
    }

    public function identityLabel(mixed $identityType): string
    {
        $identityValue = $identityType instanceof IdentityType ? $identityType->value : (string) $identityType;

        return match ($identityValue) {
            IdentityType::CURRENT_STUDENT->value => 'Sinh viên',
            IdentityType::TEACHER_ADVISOR->value => 'Giảng viên',
            IdentityType::ALUMNI->value => 'Cựu sinh viên',
            IdentityType::EXTERNAL_MENTOR->value => 'Mentor ngoài',
            default => 'Chưa xác định',
        };
    }
}; ?>

<div class="w-full max-w-full py-6 px-4 sm:px-5 lg:px-6">
    <div class="mb-5 flex flex-col gap-3 xl:flex-row xl:items-start xl:justify-between">
        <div>
            <p class="text-xs font-bold uppercase tracking-[0.08em] text-ue-text-muted">Người dùng & Quyền</p>
            <h1 class="ue-text-page-title mt-1">Quản lý tài khoản người dùng</h1>
            <p class="mt-1 text-sm text-ue-text-secondary">CRUD tài khoản, vai trò, trạng thái truy cập và vòng đời dữ liệu.</p>
        </div>

        <x-ui.button href="{{ route('admin.users.create') }}" variant="primary" size="sm" icon="user-plus">
            Tạo tài khoản
        </x-ui.button>
    </div>

    @if (session('success'))
        <div class="mb-4 rounded-lg border border-[var(--success-border)] bg-[var(--success-bg-soft)] px-4 py-3 text-sm font-semibold text-[var(--success-text)]">
            {{ session('success') }}
        </div>
    @endif

    @error('bulk_action')
        <div class="mb-4 rounded-lg border border-[var(--danger-border)] bg-[var(--danger-bg-soft)] px-4 py-3 text-sm font-semibold text-[var(--danger-text)]">
            {{ $message }}
        </div>
    @enderror

    <div class="mb-5 grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-4">
        <x-ui.card variant="admin" padding="sm">
            <div class="text-xs font-semibold text-ue-text-muted">Tổng tài khoản</div>
            <div class="mt-1 text-2xl font-bold text-ue-text">{{ number_format($this->metrics['total']) }}</div>
        </x-ui.card>
        <x-ui.card variant="admin" padding="sm">
            <div class="text-xs font-semibold text-ue-text-muted">Đang hoạt động</div>
            <div class="mt-1 text-2xl font-bold text-[var(--success-text)]">{{ number_format($this->metrics['active']) }}</div>
        </x-ui.card>
        <x-ui.card variant="admin" padding="sm">
            <div class="text-xs font-semibold text-ue-text-muted">Cần kiểm soát</div>
            <div class="mt-1 text-2xl font-bold text-[var(--warning-text)]">{{ number_format($this->metrics['restricted']) }}</div>
        </x-ui.card>
        <x-ui.card variant="admin" padding="sm">
            <div class="text-xs font-semibold text-ue-text-muted">Đã xóa mềm</div>
            <div class="mt-1 text-2xl font-bold text-[var(--danger-text)]">{{ number_format($this->metrics['deleted']) }}</div>
        </x-ui.card>
    </div>

    <x-ui.card variant="admin" class="mb-5" padding="lg">
        <div class="grid grid-cols-1 gap-4 lg:grid-cols-5">
            <div class="lg:col-span-2">
                <x-ui.label for="search" class="text-xs">Tìm kiếm</x-ui.label>
                <x-ui.input wire:model.live.debounce.300ms="search" id="search" placeholder="Tên, email hoặc ID" class="mt-1" />
            </div>

            <div>
                <x-ui.label for="role" class="text-xs">Vai trò</x-ui.label>
                <x-ui.select wire:model.live="role" id="role" class="mt-1">
                    <option value="">Tất cả</option>
                    @foreach($this->roleOptions() as $roleName)
                        <option value="{{ $roleName }}">{{ ucfirst($roleName) }}</option>
                    @endforeach
                </x-ui.select>
            </div>

            <div>
                <x-ui.label for="account_status" class="text-xs">Trạng thái</x-ui.label>
                <x-ui.select wire:model.live="account_status" id="account_status" class="mt-1">
                    <option value="">Tất cả</option>
                    @foreach(AccountStatus::cases() as $status)
                        <option value="{{ $status->value }}">{{ $this->statusLabel($status) }}</option>
                    @endforeach
                </x-ui.select>
            </div>

            <div>
                <x-ui.label for="identity_type" class="text-xs">Định danh</x-ui.label>
                <x-ui.select wire:model.live="identity_type" id="identity_type" class="mt-1">
                    <option value="">Tất cả</option>
                    @foreach(IdentityType::cases() as $identity)
                        <option value="{{ $identity->value }}">{{ $this->identityLabel($identity) }}</option>
                    @endforeach
                </x-ui.select>
            </div>
        </div>

        <div class="mt-4 flex flex-col gap-3 border-t border-ue-border pt-4 lg:flex-row lg:items-end lg:justify-between">
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:w-[460px]">
                <div>
                    <x-ui.label for="trashed" class="text-xs">Phạm vi dữ liệu</x-ui.label>
                    <x-ui.select wire:model.live="trashed" id="trashed" class="mt-1">
                        <option value="active">Đang tồn tại</option>
                        <option value="deleted">Đã xóa mềm</option>
                        <option value="all">Tất cả</option>
                    </x-ui.select>
                </div>
                <div class="flex items-end">
                    <x-ui.button type="button" variant="secondary" size="sm" icon="refresh-cw" wire:click="resetFilters" class="w-full">
                        Đặt lại lọc
                    </x-ui.button>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-2 sm:grid-cols-[180px_minmax(0,1fr)_auto] lg:min-w-[620px]">
                <x-ui.select wire:model.live="bulk_action" aria-label="Thao tác hàng loạt">
                    <option value="">Thao tác hàng loạt</option>
                    <option value="suspend">Tạm khóa</option>
                    <option value="reactivate">Kích hoạt lại</option>
                    <option value="delete">Xóa mềm</option>
                    <option value="restore">Khôi phục</option>
                </x-ui.select>
                <x-ui.input wire:model.live.debounce.300ms="bulk_reason" placeholder="Lý do thao tác" />
                <x-ui.button type="button" variant="outline" size="sm" icon="check" wire:click="applyBulkAction" wire:loading.attr="disabled" wire:target="applyBulkAction">
                    Áp dụng
                </x-ui.button>
            </div>
        </div>
    </x-ui.card>

    <x-ui.card variant="admin" padding="none" class="overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[1120px] table-fixed divide-y divide-ue-border text-left">
                <thead class="bg-ue-surface-subtle text-xs font-bold text-ue-text-muted uppercase tracking-[0.06em]">
                    <tr>
                        <th scope="col" class="w-[4%] px-4 py-3">
                            <span class="sr-only">Chọn</span>
                        </th>
                        <th scope="col" class="w-[20%] px-4 py-3">Người dùng</th>
                        <th scope="col" class="w-[22%] px-4 py-3">Email</th>
                        <th scope="col" class="w-[12%] px-4 py-3">Vai trò</th>
                        <th scope="col" class="w-[13%] px-4 py-3">Định danh</th>
                        <th scope="col" class="w-[13%] px-4 py-3">Trạng thái</th>
                        <th scope="col" class="w-[9%] px-4 py-3">Đăng nhập</th>
                        <th scope="col" class="w-[7%] px-4 py-3 text-right">Thao tác</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-ue-border bg-ue-surface text-sm">
                    @forelse ($this->users as $user)
                        @php
                            $primaryRole = $user->roles->first()?->name ?? 'none';
                            $isDeleted = $user->trashed();
                        @endphp
                        <tr class="transition-colors hover:bg-ue-surface-hover {{ $isDeleted ? 'opacity-70' : '' }}">
                            <td class="px-4 py-3 align-top">
                                <input
                                    type="checkbox"
                                    value="{{ $user->id }}"
                                    wire:model.live="selectedUserIds"
                                    class="rounded border-ue-border text-ue-brand focus:ring-ue-brand"
                                    @disabled(auth()->id() === $user->id)
                                    aria-label="Chọn {{ $user->name }}"
                                >
                            </td>
                            <td class="px-4 py-3 align-top">
                                <div class="truncate font-semibold text-ue-text" title="{{ $user->name }}">{{ $user->name }}</div>
                                <div class="mt-0.5 text-xs text-ue-text-muted">ID: {{ $user->id }}</div>
                                @if($isDeleted)
                                    <div class="mt-1 text-xs font-semibold text-[var(--danger-text)]">Đã xóa mềm</div>
                                @endif
                            </td>
                            <td class="px-4 py-3 align-top text-ue-text-muted">
                                <div class="truncate" title="{{ $user->email }}">{{ $user->email }}</div>
                                <div class="mt-0.5 text-xs">{{ $user->email_verified_at ? 'Đã xác minh email' : 'Chưa xác minh email' }}</div>
                            </td>
                            <td class="px-4 py-3 align-top">
                                <x-ui.badge variant="{{ $primaryRole === 'admin' ? 'admin' : ($primaryRole === 'student' ? 'student' : 'neutral') }}" :noIcon="true">
                                    {{ ucfirst($primaryRole) }}
                                </x-ui.badge>
                            </td>
                            <td class="px-4 py-3 align-top text-xs font-semibold text-ue-text-secondary">
                                {{ $this->identityLabel($user->intended_identity_type) }}
                            </td>
                            <td class="px-4 py-3 align-top">
                                <x-ui.badge :variant="$this->statusVariant($user->account_status)">
                                    {{ $this->statusLabel($user->account_status) }}
                                </x-ui.badge>
                                @if($user->account_status_reason)
                                    <div class="mt-1 truncate text-xs text-ue-text-muted" title="{{ $user->account_status_reason }}">
                                        {{ $user->account_status_reason }}
                                    </div>
                                @endif
                            </td>
                            <td class="px-4 py-3 align-top text-xs text-ue-text-muted">
                                {{ $user->last_login_at?->format('H:i d/m/Y') ?? 'Chưa có' }}
                            </td>
                            <td class="px-4 py-3 align-top">
                                <div class="flex justify-end gap-1">
                                    @if($isDeleted)
                                        <x-ui.button type="button" variant="outline" size="xs" icon="refresh-cw" wire:click="restoreUser({{ $user->id }})" wire:loading.attr="disabled" wire:target="restoreUser({{ $user->id }})">
                                            Khôi phục
                                        </x-ui.button>
                                    @else
                                        <x-ui.button href="{{ route('admin.users.show', ['user' => $user->id]) }}" variant="secondary" size="xs" icon="eye">
                                            Chi tiết
                                        </x-ui.button>
                                        <x-ui.button href="{{ route('admin.users.edit', ['user' => $user->id]) }}" variant="outline" size="xs" icon="edit">
                                            Sửa
                                        </x-ui.button>
                                        @if(auth()->id() !== $user->id)
                                            <x-ui.button type="button" variant="danger-outline" size="xs" icon="trash-2" wire:click="deleteUser({{ $user->id }})" wire:confirm="Xóa mềm tài khoản này?" wire:loading.attr="disabled" wire:target="deleteUser({{ $user->id }})">
                                                Xóa
                                            </x-ui.button>
                                        @endif
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center">
                                <x-ui.empty-state icon="users" title="Không tìm thấy người dùng nào" description="Không có tài khoản nào khớp với bộ lọc hiện tại." />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="border-t border-ue-border bg-ue-surface px-6 py-4">
            {{ $this->users->links() }}
        </div>
    </x-ui.card>
</div>
