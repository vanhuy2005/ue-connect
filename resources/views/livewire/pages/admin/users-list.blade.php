<?php

use App\Models\User;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    public string $search = '';
    public string $role = '';
    public string $account_status = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'role' => ['except' => ''],
        'account_status' => ['except' => ''],
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingRole(): void
    {
        $this->resetPage();
    }

    public function updatingAccountStatus(): void
    {
        $this->resetPage();
    }

    public function getUsersProperty()
    {
        $query = User::with('roles')
            ->latest('created_at');

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('email', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->role) {
            $query->whereHas('roles', function ($q) {
                $q->where('name', $this->role);
            });
        }

        if ($this->account_status) {
            // Assuming account_status is stored as a string in a column
            $query->where('account_status', $this->account_status);
        }

        return $query->paginate(15);
    }
}; ?>

<div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    {{-- Header --}}
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-ue-text">Quản lý tài khoản người dùng</h1>
        <p class="text-sm text-ue-text-secondary mt-1">Tìm kiếm, xem chi tiết và quản lý hành động cho tài khoản người dùng.</p>
    </div>

    {{-- Filters --}}
    <x-ui.card class="mb-6">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            {{-- Search --}}
            <div>
                <x-ui.label for="search" class="text-xs">Tìm kiếm</x-ui.label>
                <x-ui.input wire:model.live.debounce.300ms="search" id="search" placeholder="Tên, Email..." class="mt-1 h-9 text-xs" />
            </div>

            {{-- Role --}}
            <div>
                <x-ui.label for="role" class="text-xs">Vai trò</x-ui.label>
                <x-ui.select wire:model.live="role" id="role" class="mt-1 h-9 text-xs py-1">
                    <option value="">-- Tất cả --</option>
                    <option value="student">Sinh viên</option>
                    <option value="alumni">Cựu sinh viên</option>
                    <option value="teacher">Giảng viên</option>
                    <option value="admin">Admin</option>
                </x-ui.select>
            </div>

            {{-- Account Status --}}
            <div>
                <x-ui.label for="account_status" class="text-xs">Trạng thái tài khoản</x-ui.label>
                <x-ui.select wire:model.live="account_status" id="account_status" class="mt-1 h-9 text-xs py-1">
                    <option value="">-- Tất cả --</option>
                    <option value="active">Hoạt động</option>
                    <option value="suspended">Bị tạm khóa</option>
                    <option value="banned">Bị cấm</option>
                    <option value="registered">Đăng ký (Chưa xác thực)</option>
                </x-ui.select>
            </div>
        </div>
    </x-ui.card>

    {{-- Users Table --}}
    <x-ui.card padding="none" class="overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-ue-border text-left">
                <thead class="bg-ue-surface-subtle text-xs font-bold text-ue-text-muted uppercase tracking-wider">
                    <tr>
                        <th scope="col" class="px-6 py-4">Người dùng</th>
                        <th scope="col" class="px-6 py-4">Email</th>
                        <th scope="col" class="px-6 py-4">Vai trò</th>
                        <th scope="col" class="px-6 py-4">Trạng thái</th>
                        <th scope="col" class="px-6 py-4">Đăng nhập cuối</th>
                        <th scope="col" class="px-6 py-4 text-right">Thao tác</th>
                    </tr>
                </thead>
                <tbody class="bg-ue-surface divide-y divide-ue-border text-sm">
                    @forelse ($this->users as $user)
                        @php
                            $primaryRole = $user->roles->first()?->name ?? 'none';
                            $statusColor = match($user->account_status ?? 'active') {
                                'active' => 'success',
                                'suspended' => 'warning',
                                'banned' => 'danger',
                                'registered' => 'info',
                                default => 'neutral',
                            };
                            $statusLabel = match($user->account_status ?? 'active') {
                                'active' => 'Hoạt động',
                                'suspended' => 'Bị tạm khóa',
                                'banned' => 'Bị cấm',
                                'registered' => 'Đăng ký',
                                default => $user->account_status,
                            };
                        @endphp
                        <tr class="hover:bg-ue-surface-hover transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="font-semibold text-ue-text">{{ $user->name }}</div>
                                <div class="text-xs text-ue-text-muted mt-0.5">ID: {{ $user->id }}</div>
                            </td>
                            <td class="px-6 py-4 text-ue-text-muted">{{ $user->email }}</td>
                            <td class="px-6 py-4">
                                <x-ui.badge variant="{{ $primaryRole === 'admin' ? 'danger' : ($primaryRole === 'student' ? 'info' : 'neutral') }}">
                                    {{ ucfirst($primaryRole) }}
                                </x-ui.badge>
                            </td>
                            <td class="px-6 py-4">
                                <x-ui.badge :variant="$statusColor">{{ $statusLabel }}</x-ui.badge>
                            </td>
                            <td class="px-6 py-4 text-xs text-ue-text-muted">
                                {{ $user->last_login_at?->format('H:i d/m/Y') ?? 'Chưa đăng nhập' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <x-ui.button href="{{ route('admin.users.show', ['user' => $user->id]) }}" variant="secondary" size="sm" icon="eye">
                                    Chi tiết
                                </x-ui.button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <x-ui.empty-state icon="users" title="Không tìm thấy người dùng nào" description="Hiện tại không có tài khoản nào khớp với bộ lọc của bạn." />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="bg-ue-surface border-t border-ue-border px-6 py-4">
            {{ $this->users->links() }}
        </div>
    </x-ui.card>
</div>
