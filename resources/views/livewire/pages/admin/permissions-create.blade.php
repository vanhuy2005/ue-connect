<?php

use App\Models\PermissionGrant;
use App\Models\User;
use Livewire\Volt\Component;
use Illuminate\Support\Facades\DB;
use App\Services\AuditLogService;

new class extends Component {
    public int $user_id = 0;
    public string $permission_key = '';
    public string $scope_type = '';
    public ?int $scope_id = null;
    public string $reason = '';

    public function submit()
    {
        $this->validate([
            'user_id' => ['required','integer','exists:users,id'],
            'permission_key' => ['required','string'],
            'reason' => ['required','string','min:5'],
        ]);

        DB::transaction(function () {
            $grant = PermissionGrant::create([
                'user_id' => $this->user_id,
                'permission_key' => $this->permission_key,
                'scope_type' => $this->scope_type ?: null,
                'scope_id' => $this->scope_id,
                'granted_by' => request()->user()?->id,
                'reason' => $this->reason,
                'status' => 'active',
            ]);

            AuditLogService::log(
                actorId: request()->user()?->id,
                actorType: 'admin',
                actionKey: 'permission.grant',
                targetType: 'permission_grants',
                targetId: $grant->id,
                beforeSnapshot: null,
                afterSnapshot: $grant->toArray(),
                reason: $this->reason
            );

            session()->flash('success', 'Đã tạo cấp quyền');
        });

        return redirect()->route('admin.permissions.index');
    }
};
?>

<div class="max-w-3xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    <h1 class="text-2xl font-bold text-ue-text mb-4">Tạo cấp quyền</h1>

    <x-ui.card>
        <form wire:submit.prevent="submit" class="grid grid-cols-1 gap-4">
            <div>
                <label class="block text-sm font-semibold text-ue-text mb-1">Người nhận (ID người dùng)</label>
                <input type="number" wire:model.live="user_id" class="w-full px-3 py-2 border rounded-lg" placeholder="Nhập user_id của club manager" />
                <p class="text-xs text-ue-text-secondary mt-1">Nếu chưa biết, mở trang admin Người dùng và xem cột ID.</p>
            </div>

            <div>
                <label class="block text-sm font-semibold text-ue-text mb-1">Mã quyền</label>
                <select wire:model.live="permission_key" class="w-full px-3 py-2 border rounded-lg">
                    <option value="">-- Chọn quyền --</option>
                    <option value="manage_club">manage_club — Quản lý club</option>
                    <option value="manage_community_members">manage_community_members — Quản lý thành viên cộng đồng</option>
                    <option value="manage_community_resources">manage_community_resources — Quản lý tài nguyên cộng đồng</option>
                    <option value="manage_communities">manage_communities — Quản lý cộng đồng</option>
                    <option value="manage_permissions">manage_permissions — Quản lý quyền</option>
                    <option value="manage_users">manage_users — Quản lý người dùng</option>
                    <option value="suspend_users">suspend_users — Tạm khóa người dùng</option>
                    <option value="ban_users">ban_users — Cấm người dùng</option>
                    <option value="review_verification">review_verification — Duyệt xác thực</option>
                    <option value="approve_verification">approve_verification — Phê duyệt xác thực</option>
                    <option value="manage_mentor_access">manage_mentor_access — Quản lý quyền mentor</option>
                    <option value="view_audit_log">view_audit_log — Xem nhật ký audit</option>
                </select>
                <p class="text-xs text-ue-text-secondary mt-1">Để cấp club manager, chọn <strong>manage_club</strong> và dùng phạm vi <strong>Cộng đồng</strong>.</p>
            </div>

            <div>
                <label class="block text-sm font-semibold text-ue-text mb-1">Loại phạm vi</label>
                <select wire:model.live="scope_type" class="w-full px-3 py-2 border rounded-lg">
                    <option value="">Toàn cục</option>
                    <option value="community">Cộng đồng</option>
                </select>
                <p class="text-xs text-ue-text-secondary mt-1">Chọn Cộng đồng và nhập ID cộng đồng bên dưới nếu muốn cấp quyền scoped.</p>
            </div>

            <div>
                <label class="block text-sm font-semibold text-ue-text mb-1">ID phạm vi</label>
                <input type="number" wire:model.live="scope_id" class="w-full px-3 py-2 border rounded-lg" placeholder="Nhập community_id nếu chọn Cộng đồng" />
                <p class="text-xs text-ue-text-secondary mt-1">Ví dụ: nếu cấp quản lý cho CLB A, nhập ID của cộng đồng đó.</p>
            </div>

            <div>
                <label class="block text-sm font-semibold text-ue-text mb-1">Lý do</label>
                <textarea wire:model.live="reason" class="w-full px-3 py-2 border rounded-lg" rows="4"></textarea>
            </div>

            <div class="flex justify-end">
                <button class="px-4 py-2 bg-ue-brand text-white rounded-lg">Cấp</button>
            </div>
        </form>
    </x-ui.card>
</div>