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
                <x-ui.label for="user_id" class="text-xs font-semibold">Người nhận (ID người dùng)</x-ui.label>
                <x-ui.input type="number" id="user_id" wire:model.live="user_id" class="mt-1 h-9 text-xs" placeholder="Nhập user_id của club manager" />
                <p class="text-[10px] text-ue-text-muted mt-1">Nếu chưa biết, mở trang admin Người dùng và xem cột ID.</p>
            </div>

            <div>
                <x-ui.label for="permission_key" class="text-xs font-semibold">Mã quyền</x-ui.label>
                <x-ui.select id="permission_key" wire:model.live="permission_key" class="mt-1 h-9 text-xs py-1">
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
                </x-ui.select>
                <p class="text-[10px] text-ue-text-muted mt-1">Để cấp club manager, chọn <strong>manage_club</strong> và dùng phạm vi <strong>Cộng đồng</strong>.</p>
            </div>

            <div>
                <x-ui.label for="scope_type" class="text-xs font-semibold">Loại phạm vi</x-ui.label>
                <x-ui.select id="scope_type" wire:model.live="scope_type" class="mt-1 h-9 text-xs py-1">
                    <option value="">Toàn cục</option>
                    <option value="community">Cộng đồng</option>
                </x-ui.select>
                <p class="text-[10px] text-ue-text-muted mt-1">Chọn Cộng đồng và nhập ID cộng đồng bên dưới nếu muốn cấp quyền scoped.</p>
            </div>

            <div>
                <x-ui.label for="scope_id" class="text-xs font-semibold">ID phạm vi</x-ui.label>
                <x-ui.input type="number" id="scope_id" wire:model.live="scope_id" class="mt-1 h-9 text-xs" placeholder="Nhập community_id nếu chọn Cộng đồng" />
                <p class="text-[10px] text-ue-text-muted mt-1">Ví dụ: nếu cấp quản lý cho CLB A, nhập ID của cộng đồng đó.</p>
            </div>

            <div>
                <x-ui.label for="reason" class="text-xs font-semibold">Lý do</x-ui.label>
                <x-ui.textarea id="reason" wire:model.live="reason" class="mt-1 text-sm" rows="4" placeholder="Giải thích lý do cấp quyền..." />
            </div>

            <div class="flex justify-end">
                <x-ui.button type="submit" variant="primary">
                    Cấp quyền
                </x-ui.button>
            </div>
        </form>
    </x-ui.card>
</div>