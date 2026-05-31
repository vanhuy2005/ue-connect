<?php

use App\Models\User;
use Livewire\Volt\Component;
use Illuminate\Support\Facades\DB;
use App\Services\AuditLogService;

new class extends Component {
    public User $user;
    public string $action = 'suspend';
    public string $reason = '';

    public function mount(User $user): void
    {
        $this->user = $user;
    }

    public function process(): void
    {
        $this->validate([
            'reason' => ['required', 'string', 'min:5', 'max:1000'],
            'action' => ['required', 'string'],
        ]);

        DB::transaction(function () {
            $before = $this->user->toArray();

            if ($this->action === 'suspend') {
                $this->user->account_status = 'suspended';
                $this->user->account_status_reason = $this->reason;
                $this->user->save();

                AuditLogService::log(
                    actorId: auth()->id(),
                    actorType: 'admin',
                    actionKey: 'user.suspend',
                    targetType: 'user',
                    targetId: $this->user->id,
                    beforeSnapshot: $before,
                    afterSnapshot: $this->user->toArray(),
                    reason: $this->reason
                );
            } elseif ($this->action === 'ban') {
                $this->user->account_status = 'banned';
                $this->user->account_status_reason = $this->reason;
                $this->user->save();

                AuditLogService::log(
                    actorId: auth()->id(),
                    actorType: 'admin',
                    actionKey: 'user.ban',
                    targetType: 'user',
                    targetId: $this->user->id,
                    beforeSnapshot: $before,
                    afterSnapshot: $this->user->toArray(),
                    reason: $this->reason
                );
            } elseif ($this->action === 'reactivate') {
                $this->user->account_status = 'active';
                $this->user->account_status_reason = $this->reason;
                $this->user->save();

                AuditLogService::log(
                    actorId: auth()->id(),
                    actorType: 'admin',
                    actionKey: 'user.reactivate',
                    targetType: 'user',
                    targetId: $this->user->id,
                    beforeSnapshot: $before,
                    afterSnapshot: $this->user->toArray(),
                    reason: $this->reason
                );
            }

            session()->flash('success', 'Đã thực hiện hành động.');
            $this->user->refresh();
        });
    }
};

?>

<div class="max-w-4xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    <h1 class="text-2xl font-bold text-ue-text">Chi tiết tài khoản — {{ $this->user->name }}</h1>
    <p class="text-sm text-ue-text-secondary mt-1">ID: {{ $this->user->id }} — Email: {{ $this->user->email }}</p>

    <x-ui.card class="mt-6">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <h3 class="font-semibold">Thông tin cơ bản</h3>
                <div class="mt-2 text-ue-text-muted text-sm">
                    Vai trò: {{ $this->user->roles->first()?->name ?? '—' }}<br />
                    Trạng thái: {{ $this->user->account_status ?? '—' }}<br />
                    Lý do hiện tại: {{ $this->user->account_status_reason ?? '—' }}
                </div>
            </div>

            <div>
                <h3 class="font-semibold">Hành động quản trị</h3>
                <form wire:submit.prevent="process" class="mt-2 grid grid-cols-1 gap-3">
                    <div>
                        <label class="block text-sm font-semibold text-ue-text mb-1">Hành động</label>
                        <select wire:model.live="action" class="w-full px-3 py-2 border rounded-lg">
                            <option value="suspend">Tạm khóa</option>
                            <option value="ban">Cấm</option>
                            <option value="reactivate">Kích hoạt lại</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-ue-text mb-1">Lý do (bắt buộc)</label>
                        <textarea wire:model.live="reason" class="w-full px-3 py-2 border rounded-lg" rows="4"></textarea>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" class="px-4 py-2 bg-ue-brand text-white rounded-lg">Thực hiện</button>
                    </div>
                </form>
            </div>
        </div>
    </x-ui.card>
</div>
<?php

use App\Models\User;
use App\Models\VerificationRequest;
use Livewire\Component;

new class extends Component {
    public User $user;

    public function mount(User $user): void
    {
        $this->user = $user->load('roles', 'permissions');
    }

    public function getVerificationStatusProperty()
    {
        return VerificationRequest::where('user_id', $this->user->id)
            ->latest('submitted_at')
            ->first();
    }

    public function getRolesListProperty()
    {
        return $this->user->roles->pluck('name')->toArray();
    }
}; ?>

<x-app-layout>
    <x-slot name="title">Chi tiết tài khoản - {{ $this->user->name }}</x-slot>

    <div class="max-w-4xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        {{-- Header --}}
        <div class="mb-8 flex items-center justify-between">
            <div>
                <a href="{{ route('admin.users.index') }}" class="text-sm text-ue-brand hover:underline mb-2 flex items-center gap-1">
                    <x-ui.icon name="arrow-left" size="xs" />
                    Quay lại danh sách
                </a>
                <h1 class="text-3xl font-bold text-ue-text">{{ $this->user->name }}</h1>
                <p class="text-sm text-ue-text-muted mt-1">{{ $this->user->email }}</p>
            </div>
            @php
                $statusColor = match($this->user->account_status ?? 'active') {
                    'active' => 'success',
                    'suspended' => 'warning',
                    'banned' => 'danger',
                    'registered' => 'info',
                    default => 'neutral',
                };
                $statusLabel = match($this->user->account_status ?? 'active') {
                    'active' => 'Hoạt động',
                    'suspended' => 'Bị tạm khóa',
                    'banned' => 'Bị cấm',
                    'registered' => 'Đăng ký',
                    default => $this->user->account_status,
                };
            @endphp
            <x-ui.badge :variant="$statusColor" size="lg">{{ $statusLabel }}</x-ui.badge>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Main Content --}}
            <div class="lg:col-span-2 space-y-6">
                {{-- Account Information --}}
                <x-ui.card>
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-lg font-semibold text-ue-text">Thông tin tài khoản</h2>
                    </div>
                    <div class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-xs font-semibold text-ue-text-muted uppercase">User ID</p>
                                <p class="mt-1 font-mono text-sm text-ue-text">{{ $this->user->id }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-semibold text-ue-text-muted uppercase">Trạng thái Email</p>
                                <p class="mt-1 text-sm text-ue-text">
                                    @if ($this->user->email_verified_at)
                                        <span class="text-green-600 font-semibold">✓ Đã xác thực</span>
                                        <span class="text-xs text-ue-text-muted block">{{ $this->user->email_verified_at->format('H:i d/m/Y') }}</span>
                                    @else
                                        <span class="text-amber-600 font-semibold">○ Chưa xác thực</span>
                                    @endif
                                </p>
                            </div>
                            <div>
                                <p class="text-xs font-semibold text-ue-text-muted uppercase">Ngày tạo</p>
                                <p class="mt-1 text-sm text-ue-text">{{ $this->user->created_at->format('H:i d/m/Y') }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-semibold text-ue-text-muted uppercase">Đăng nhập cuối</p>
                                <p class="mt-1 text-sm text-ue-text">{{ $this->user->last_login_at?->format('H:i d/m/Y') ?? 'Chưa đăng nhập' }}</p>
                            </div>
                        </div>
                    </div>
                </x-ui.card>

                {{-- Roles & Permissions --}}
                <x-ui.card>
                    <h2 class="text-lg font-semibold text-ue-text mb-4">Vai trò và quyền hạn</h2>
                    <div class="space-y-4">
                        {{-- Roles --}}
                        <div>
                            <p class="text-sm font-semibold text-ue-text-muted mb-2">Vai trò hệ thống</p>
                            <div class="flex flex-wrap gap-2">
                                @forelse ($this->rolesList as $role)
                                    <x-ui.badge variant="{{ $role === 'admin' ? 'danger' : 'info' }}">
                                        {{ ucfirst($role) }}
                                    </x-ui.badge>
                                @empty
                                    <span class="text-sm text-ue-text-muted">Không có vai trò</span>
                                @endforelse
                            </div>
                        </div>

                        {{-- Permissions --}}
                        @if ($this->user->permissions->count() > 0)
                            <div class="border-t border-ue-border pt-4">
                                <p class="text-sm font-semibold text-ue-text-muted mb-2">Quyền hạn cấp độ người dùng</p>
                                <div class="flex flex-wrap gap-2">
                                    @foreach ($this->user->permissions as $permission)
                                        <x-ui.badge variant="secondary">
                                            {{ $permission->name }}
                                        </x-ui.badge>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </x-ui.card>

                {{-- Verification Status --}}
                @if ($this->verificationStatus)
                    <x-ui.card>
                        <h2 class="text-lg font-semibold text-ue-text mb-4">Trạng thái xác thực</h2>
                        <div class="space-y-3">
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-ue-text-muted">Vai trò yêu cầu</span>
                                <x-ui.badge variant="info">{{ ucfirst($this->verificationStatus->role_requested) }}</x-ui.badge>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-ue-text-muted">Trạng thái hồ sơ</span>
                                @php
                                    $verifyBadge = match($this->verificationStatus->status) {
                                        'approved' => 'success',
                                        'rejected' => 'danger',
                                        'pending_review' => 'warning',
                                        'needs_more_information' => 'warning',
                                        default => 'neutral',
                                    };
                                @endphp
                                <x-ui.badge :variant="$verifyBadge">
                                    {{ match($this->verificationStatus->status) {
                                        'approved' => 'Đã duyệt',
                                        'rejected' => 'Bị từ chối',
                                        'pending_review' => 'Chờ duyệt',
                                        'needs_more_information' => 'Cần thêm thông tin',
                                        default => $this->verificationStatus->status,
                                    } }}
                                </x-ui.badge>
                            </div>
                            <div class="flex justify-between items-start">
                                <span class="text-sm text-ue-text-muted">Thời gian gửi</span>
                                <span class="text-sm text-ue-text text-right">{{ $this->verificationStatus->submitted_at?->format('H:i d/m/Y') ?? 'N/A' }}</span>
                            </div>
                            <a href="{{ route('admin.verifications.detail', ['id' => $this->verificationStatus->id]) }}" class="text-sm font-semibold text-ue-brand hover:underline mt-4 inline-block">
                                Xem chi tiết hồ sơ →
                            </a>
                        </div>
                    </x-ui.card>
                @endif
            </div>

            {{-- Sidebar Actions --}}
            <div>
                <x-ui.card class="sticky top-6">
                    <h3 class="text-lg font-semibold text-ue-text mb-4">Hành động</h3>
                    <div class="space-y-3">
                        @if ($this->user->account_status === 'active')
                            <button class="w-full flex items-center justify-center gap-2 px-4 py-2 h-10 rounded-lg bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400 hover:bg-amber-200 dark:hover:bg-amber-900/50 font-semibold text-sm transition-colors">
                                <x-ui.icon name="lock" size="sm" />
                                Tạm khóa tài khoản
                            </button>
                            <button class="w-full flex items-center justify-center gap-2 px-4 py-2 h-10 rounded-lg bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400 hover:bg-red-200 dark:hover:bg-red-900/50 font-semibold text-sm transition-colors">
                                <x-ui.icon name="ban" size="sm" />
                                Cấm tài khoản
                            </button>
                        @elseif ($this->user->account_status === 'suspended')
                            <button class="w-full flex items-center justify-center gap-2 px-4 py-2 h-10 rounded-lg bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400 hover:bg-green-200 dark:hover:bg-green-900/50 font-semibold text-sm transition-colors">
                                <x-ui.icon name="check-circle" size="sm" />
                                Mở khóa tài khoản
                            </button>
                        @elseif ($this->user->account_status === 'banned')
                            <button class="w-full flex items-center justify-center gap-2 px-4 py-2 h-10 rounded-lg bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400 hover:bg-green-200 dark:hover:bg-green-900/50 font-semibold text-sm transition-colors">
                                <x-ui.icon name="check-circle" size="sm" />
                                Khôi phục tài khoản
                            </button>
                        @endif

                        <hr class="border-ue-border my-3" />

                        <button class="w-full flex items-center justify-center gap-2 px-4 py-2 h-10 rounded-lg bg-ue-surface-hover text-ue-text hover:bg-ue-surface-focus font-semibold text-sm transition-colors">
                            <x-ui.icon name="key" size="sm" />
                            Quản lý quyền hạn
                        </button>

                        <button class="w-full flex items-center justify-center gap-2 px-4 py-2 h-10 rounded-lg bg-ue-surface-hover text-ue-text hover:bg-ue-surface-focus font-semibold text-sm transition-colors">
                            <x-ui.icon name="file-text" size="sm" />
                            Xem nhật ký kiểm toán
                        </button>
                    </div>
                </x-ui.card>
            </div>
        </div>
    </div>
</x-app-layout>
