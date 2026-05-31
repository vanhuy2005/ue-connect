<?php

use App\Models\MentorAccess;
use App\Models\User;
use Livewire\Volt\Component;
use Illuminate\Support\Facades\DB;
use App\Services\AuditLogService;

new class extends Component {
    public int $id;
    public ?MentorAccess $request = null;
    public string $action = 'approve';
    public string $reason = '';

    public function mount(int $id): void
    {
        $this->id = $id;
        $this->load();
    }

    public function load(): void
    {
        $this->request = MentorAccess::with('user')->findOrFail($this->id);
    }

    public function process(): void
    {
        if (!$this->request) return;

        $this->validate([
            'reason' => ['required', 'string', 'min:5', 'max:1000'],
        ]);

        DB::transaction(function () {
            $before = $this->request->toArray();

            if ($this->action === 'approve') {
                $this->request->status = 'approved';
                $this->request->reviewed_by = auth()->id();
                $this->request->reviewed_at = now();
                $this->request->save();

                // grant mentor role or flag
                $user = $this->request->user;
                if ($user) {
                    $user->assignRole('mentor');
                }

                AuditLogService::log(
                    actorId: auth()->id(),
                    actorType: 'admin',
                    actionKey: 'mentor.approve',
                    targetType: 'mentor_access',
                    targetId: $this->request->id,
                    beforeSnapshot: $before,
                    afterSnapshot: $this->request->toArray(),
                    reason: $this->reason
                );
            } else {
                $this->request->status = 'rejected';
                $this->request->reviewed_by = auth()->id();
                $this->request->reviewed_at = now();
                $this->request->save();

                AuditLogService::log(
                    actorId: auth()->id(),
                    actorType: 'admin',
                    actionKey: 'mentor.reject',
                    targetType: 'mentor_access',
                    targetId: $this->request->id,
                    beforeSnapshot: $before,
                    afterSnapshot: $this->request->toArray(),
                    reason: $this->reason
                );
            }

            session()->flash('success', 'Đã xử lý yêu cầu.');
            $this->load();
        });
    }
};
?>

<div class="max-w-4xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    <h1 class="text-2xl font-bold text-ue-text">Yêu cầu Mentor #{{ $this->id }}</h1>
    <p class="text-sm text-ue-text-secondary mt-1">Người yêu cầu: {{ $this->request->user?->name ?? 'N/A' }} — {{ $this->request->user?->email ?? '' }}</p>

    <x-ui.card class="mt-6">
        <div>
            <h3 class="font-semibold">Lý do / Ghi chú</h3>
            <p class="mt-2 text-ue-text-muted">{{ $this->request->note }}</p>
        </div>

        <form wire:submit.prevent="process" class="mt-4 grid grid-cols-1 gap-4">
            <div>
                <label class="block text-sm font-semibold text-ue-text mb-1">Hành động</label>
                <select wire:model.live="action" class="w-full px-3 py-2 border rounded-lg">
                    <option value="approve">Phê duyệt</option>
                    <option value="reject">Từ chối</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-semibold text-ue-text mb-1">Lý do (bắt buộc)</label>
                <textarea wire:model.live="reason" class="w-full px-3 py-2 border rounded-lg" rows="4"></textarea>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="px-4 py-2 bg-ue-brand text-white rounded-lg">Xác nhận</button>
            </div>
        </form>
    </x-ui.card>
</div>