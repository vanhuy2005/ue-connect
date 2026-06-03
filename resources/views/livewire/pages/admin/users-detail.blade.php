<?php

use App\Models\User;
use Livewire\Volt\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
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
                    actorId: Auth::id(),
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
                    actorId: Auth::id(),
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
                    actorId: Auth::id(),
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
