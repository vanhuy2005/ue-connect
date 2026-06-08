<?php

use App\Actions\Community\ApproveJoinRequestAction;
use App\Actions\Community\ArchiveCommunityAction;
use App\Actions\Community\GrantClubManagerAction;
use App\Actions\Community\ReactivateCommunityAction;
use App\Actions\Community\RejectJoinRequestAction;
use App\Actions\Community\RemoveCommunityMemberAction;
use App\Actions\Community\ReviewCommunityResourceAction;
use App\Actions\Community\RevokeClubManagerAction;
use App\Actions\Community\SuspendCommunityAction;
use App\Enums\CommunityMemberStatus;
use App\Enums\CommunityResourceStatus;
use App\Enums\CommunityStatus;
use App\Models\Community;
use App\Models\CommunityJoinRequest;
use App\Models\CommunityMember;
use App\Models\CommunityResource;
use App\Models\User;
use Livewire\Volt\Component;

new class extends Component {
    public Community $community;

    // Suspend modal
    public bool $showSuspendModal = false;
    public string $suspendReason = '';
    public string $suspendSafeReason = '';
    public bool $suspendNotifyMembers = false;

    // Archive modal
    public bool $showArchiveModal = false;
    public string $archiveReason = '';

    // Reactivate modal
    public bool $showReactivateModal = false;
    public string $reactivateReason = '';

    // Remove member modal
    public bool $showRemoveModal = false;
    public ?int $removeMemberId = null;
    public string $removeMemberReason = '';

    // Add member modal
    public bool $showAddMemberModal = false;
    public int|string $addMemberUserId = '';
    public string $addMemberRole = 'member';

    // Grant manager modal
    public bool $showGrantManagerModal = false;
    public ?int $grantManagerUserId = null;
    public string $grantManagerReason = '';

    // Join request modals
    public bool $showApproveJoinRequestModal = false;
    public bool $showRejectJoinRequestModal = false;
    public ?int $reviewJoinRequestId = null;
    public string $joinRequestRejectReason = '';

    public function mount(Community $community): void
    {
        $this->authorize('manage_communities');
        $this->community = $community->load(['creator', 'owner']);
    }

    public function getActiveMembersProperty()
    {
        return CommunityMember::where('community_id', $this->community->id)
            ->where('status', CommunityMemberStatus::Active->value)
            ->with('user')
            ->latest('joined_at')
            ->paginate(20, pageName: 'membersPage');
    }

    public function getPendingJoinRequestsProperty()
    {
        return CommunityJoinRequest::where('community_id', $this->community->id)
            ->where('status', 'pending')
            ->with('user')
            ->latest()
            ->get();
    }

    public function getPendingResourcesProperty()
    {
        return CommunityResource::where('community_id', $this->community->id)
            ->where('status', CommunityResourceStatus::PendingReview->value)
            ->with('submitter')
            ->latest()
            ->get();
    }

    // ─── Suspend ─────────────────────────────────────────────────────────────

    public function openSuspendModal(): void
    {
        $this->showSuspendModal = true;
    }

    public function confirmSuspend(SuspendCommunityAction $action): void
    {
        $this->authorize('manage_communities');

        $this->validate([
            'suspendReason' => ['required', 'string', 'min:10'],
            'suspendSafeReason' => ['required', 'string', 'min:5'],
        ]);

        $action->execute(auth()->user(), $this->community, [
            'reason' => $this->suspendReason,
            'safe_reason' => $this->suspendSafeReason,
            'notify_members' => $this->suspendNotifyMembers,
        ]);

        $this->community->refresh();
        $this->showSuspendModal = false;
        $this->reset(['suspendReason', 'suspendSafeReason', 'suspendNotifyMembers']);
        $this->dispatch('notify', type: 'success', message: 'Cộng đồng đã bị tạm khóa.');
    }

    // ─── Reactivate ──────────────────────────────────────────────────────────

    public function openReactivateModal(): void
    {
        $this->showReactivateModal = true;
    }

    public function confirmReactivate(ReactivateCommunityAction $action): void
    {
        $this->authorize('manage_communities');

        $action->execute(auth()->user(), $this->community, $this->reactivateReason ?: null);
        $this->community->refresh();
        $this->showReactivateModal = false;
        $this->reset('reactivateReason');
        $this->dispatch('notify', type: 'success', message: 'Cộng đồng đã được kích hoạt lại.');
    }

    // ─── Archive ─────────────────────────────────────────────────────────────

    public function openArchiveModal(): void
    {
        $this->showArchiveModal = true;
    }

    public function confirmArchive(ArchiveCommunityAction $action): void
    {
        $this->authorize('manage_communities');

        $this->validate(['archiveReason' => ['required', 'string', 'min:5']]);

        $action->execute(auth()->user(), $this->community, $this->archiveReason);
        $this->community->refresh();
        $this->showArchiveModal = false;
        $this->reset('archiveReason');
        $this->dispatch('notify', type: 'success', message: 'Cộng đồng đã được lưu trữ.');
    }

    // ─── Remove Member ───────────────────────────────────────────────────────

    public function openRemoveMember(int $memberId): void
    {
        $this->removeMemberId = $memberId;
        $this->showRemoveModal = true;
    }

    public function confirmRemoveMember(RemoveCommunityMemberAction $action): void
    {
        $this->authorize('manage_communities');

        $this->validate(['removeMemberReason' => ['required', 'string', 'min:5']]);

        $member = CommunityMember::findOrFail($this->removeMemberId);
        $targetUser = User::findOrFail($member->user_id);

        $action->execute(auth()->user(), $this->community, $targetUser, $this->removeMemberReason);
        $this->community->refresh();
        $this->showRemoveModal = false;
        $this->reset(['removeMemberId', 'removeMemberReason']);
        $this->dispatch('notify', type: 'success', message: 'Thành viên đã bị xóa.');
    }

    // ─── Add Member ──────────────────────────────────────────────────────────

    public function openAddMemberModal(): void
    {
        $this->showAddMemberModal = true;
    }

    public function confirmAddMember(): void
    {
        $this->authorize('manage_communities');

        $this->validate([
            'addMemberUserId' => ['required', 'integer', 'exists:users,id'],
            'addMemberRole' => ['required', 'in:member,moderator,manager'],
        ]);

        $existing = CommunityMember::where('community_id', $this->community->id)
            ->where('user_id', $this->addMemberUserId)
            ->where('status', CommunityMemberStatus::Active->value)
            ->first();

        if ($existing) {
            $this->addError('addMemberUserId', 'Người dùng này đã là thành viên.');
            return;
        }

        CommunityMember::updateOrCreate(
            ['community_id' => $this->community->id, 'user_id' => $this->addMemberUserId],
            ['role' => $this->addMemberRole, 'status' => CommunityMemberStatus::Active->value, 'joined_at' => now()]
        );

        $this->community->increment('members_count');
        $this->community->refresh();
        $this->showAddMemberModal = false;
        $this->reset(['addMemberUserId', 'addMemberRole']);
        $this->dispatch('notify', type: 'success', message: 'Thành viên đã được thêm.');
    }

    // ─── Join Request Review ─────────────────────────────────────────────────

    public function openApproveJoinRequest(int $joinRequestId): void
    {
        $this->reviewJoinRequestId = $joinRequestId;
        $this->showApproveJoinRequestModal = true;
    }

    public function confirmApproveJoinRequest(ApproveJoinRequestAction $action): void
    {
        $this->authorize('manage_communities');

        $joinRequest = CommunityJoinRequest::findOrFail($this->reviewJoinRequestId);
        $action->execute(auth()->user(), $joinRequest);
        $this->community->refresh();
        $this->showApproveJoinRequestModal = false;
        $this->reset('reviewJoinRequestId');
        $this->dispatch('notify', type: 'success', message: 'Yêu cầu đã được chấp nhận.');
    }

    public function openRejectJoinRequest(int $joinRequestId): void
    {
        $this->reviewJoinRequestId = $joinRequestId;
        $this->showRejectJoinRequestModal = true;
    }

    public function confirmRejectJoinRequest(RejectJoinRequestAction $action): void
    {
        $this->authorize('manage_communities');

        $this->validate(['joinRequestRejectReason' => ['required', 'string', 'min:5']]);

        $joinRequest = CommunityJoinRequest::findOrFail($this->reviewJoinRequestId);
        $action->execute(auth()->user(), $joinRequest, $this->joinRequestRejectReason);
        $this->community->refresh();
        $this->showRejectJoinRequestModal = false;
        $this->reset(['reviewJoinRequestId', 'joinRequestRejectReason']);
        $this->dispatch('notify', type: 'success', message: 'Yêu cầu đã bị từ chối.');
    }

    // ─── Resource Review ─────────────────────────────────────────────────────

    public function approveResource(int $resourceId, ReviewCommunityResourceAction $action): void
    {
        $this->authorize('manage_communities');

        $resource = CommunityResource::findOrFail($resourceId);
        $action->execute(auth()->user(), $resource, ['action' => 'approve']);
        $this->dispatch('notify', type: 'success', message: 'Tài nguyên đã được phê duyệt.');
    }

    public function rejectResource(int $resourceId, ReviewCommunityResourceAction $action): void
    {
        $this->authorize('manage_communities');

        $resource = CommunityResource::findOrFail($resourceId);
        $action->execute(auth()->user(), $resource, [
            'action' => 'reject',
            'reason' => 'Tài nguyên không đáp ứng yêu cầu nội dung của cộng đồng.',
        ]);
        $this->dispatch('notify', type: 'success', message: 'Tài nguyên đã bị từ chối.');
    }
};
?>

<div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8 space-y-6">

    {{-- Header --}}
    <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-sm text-ue-text-muted mb-1">
                <a href="{{ route('admin.communities.index') }}" class="hover:text-ue-brand">Cộng đồng</a>
                <span>/</span>
                <span>{{ $community->name }}</span>
            </div>
            <h1 class="text-2xl font-bold text-ue-text">{{ $community->name }}</h1>
            <p class="text-sm text-ue-text-secondary mt-1">{{ $community->short_description ?? $community->description }}</p>
        </div>

        {{-- Status badge + actions --}}
        <div class="flex flex-wrap items-center gap-2">
            @php
                $statusColors = [
                    'active' => 'green', 'inactive' => 'gray', 'suspended' => 'red',
                    'archived' => 'slate', 'draft' => 'yellow', 'pending_review' => 'blue',
                ];
                $sc = $statusColors[$community->status?->value ?? $community->status] ?? 'gray';
            @endphp
            <span class="px-3 py-1 rounded-full text-xs font-semibold bg-{{ $sc }}-100 text-{{ $sc }}-800">
                {{ $community->status?->label() ?? ucfirst($community->status) }}
            </span>

            @if ($community->status?->value !== 'suspended' && $community->status?->value !== 'archived')
                <button wire:click="openSuspendModal"
                    class="px-3 py-1.5 bg-red-600 text-white rounded-lg text-xs font-semibold hover:bg-red-700 transition">
                    Tạm khóa
                </button>
            @endif

            @if ($community->status?->value === 'suspended')
                <button wire:click="openReactivateModal"
                    class="px-3 py-1.5 bg-green-600 text-white rounded-lg text-xs font-semibold hover:bg-green-700 transition">
                    Kích hoạt lại
                </button>
            @endif

            @if (!in_array($community->status?->value, ['archived']))
                <button wire:click="openArchiveModal"
                    class="px-3 py-1.5 bg-gray-500 text-white rounded-lg text-xs font-semibold hover:bg-gray-600 transition">
                    Lưu trữ
                </button>
            @endif
        </div>
    </div>

    {{-- Info Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        @foreach ([
            ['label' => 'Thành viên', 'value' => number_format($community->members_count)],
            ['label' => 'Bài đăng', 'value' => number_format($community->post_count)],
            ['label' => 'Tài nguyên', 'value' => number_format($community->resource_count)],
            ['label' => 'Loại', 'value' => $community->type?->label() ?? ucfirst($community->type)],
        ] as $stat)
            <x-ui.card class="text-center">
                <div class="text-2xl font-bold text-ue-text">{{ $stat['value'] }}</div>
                <div class="text-xs text-ue-text-muted mt-1">{{ $stat['label'] }}</div>
            </x-ui.card>
        @endforeach
    </div>

    {{-- Pending Join Requests --}}
    @if ($this->pendingJoinRequests->isNotEmpty())
    <x-ui.card>
        <h2 class="text-lg font-semibold text-ue-text mb-4">
            Yêu cầu tham gia đang chờ
            <span class="ml-1 px-2 py-0.5 bg-yellow-100 text-yellow-800 rounded-full text-xs">{{ $this->pendingJoinRequests->count() }}</span>
        </h2>
        <div class="space-y-3">
            @foreach ($this->pendingJoinRequests as $joinRequest)
            <div class="flex items-center justify-between p-3 bg-ue-surface-subtle rounded-lg">
                <div>
                    <p class="font-semibold text-sm text-ue-text">{{ $joinRequest->user?->name }}</p>
                    @if ($joinRequest->join_reason)
                    <p class="text-xs text-ue-text-muted mt-0.5">{{ Str::limit($joinRequest->join_reason, 80) }}</p>
                    @endif
                    <p class="text-xs text-ue-text-muted">{{ $joinRequest->created_at->diffForHumans() }}</p>
                </div>
                <div class="flex gap-2">
                    <button wire:click="openApproveJoinRequest({{ $joinRequest->id }})"
                        class="px-3 py-1 bg-green-600 text-white rounded text-xs font-semibold hover:bg-green-700">Chấp nhận</button>
                    <button wire:click="openRejectJoinRequest({{ $joinRequest->id }})"
                        class="px-3 py-1 bg-red-100 text-red-700 rounded text-xs font-semibold hover:bg-red-200">Từ chối</button>
                </div>
            </div>
            @endforeach
        </div>
    </x-ui.card>
    @endif

    {{-- Pending Resources --}}
    @if ($this->pendingResources->isNotEmpty())
    <x-ui.card>
        <h2 class="text-lg font-semibold text-ue-text mb-4">
            Tài nguyên chờ duyệt
            <span class="ml-1 px-2 py-0.5 bg-blue-100 text-blue-800 rounded-full text-xs">{{ $this->pendingResources->count() }}</span>
        </h2>
        <div class="space-y-3">
            @foreach ($this->pendingResources as $resource)
            <div class="flex items-center justify-between p-3 bg-ue-surface-subtle rounded-lg">
                <div>
                    <p class="font-semibold text-sm text-ue-text">{{ $resource->title }}</p>
                    <p class="text-xs text-ue-text-muted">{{ $resource->resource_type?->label() }} · {{ $resource->submitter?->name }}</p>
                </div>
                <div class="flex gap-2">
                    <button wire:click="approveResource({{ $resource->id }})"
                        class="px-3 py-1 bg-green-600 text-white rounded text-xs font-semibold hover:bg-green-700">Phê duyệt</button>
                    <button wire:click="rejectResource({{ $resource->id }})"
                        class="px-3 py-1 bg-red-100 text-red-700 rounded text-xs font-semibold hover:bg-red-200">Từ chối</button>
                </div>
            </div>
            @endforeach
        </div>
    </x-ui.card>
    @endif

    {{-- Members List --}}
    <x-ui.card>
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-ue-text">Thành viên đang hoạt động</h2>
            <button wire:click="openAddMemberModal"
                class="px-3 py-1.5 bg-ue-brand text-white rounded-lg text-xs font-semibold hover:bg-opacity-90">
                + Thêm thành viên
            </button>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-ue-border text-sm">
                <thead class="bg-ue-surface-subtle">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-ue-text-muted uppercase">Thành viên</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-ue-text-muted uppercase">Vai trò</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-ue-text-muted uppercase">Ngày tham gia</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-ue-text-muted uppercase">Hành động</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-ue-border">
                    @forelse ($this->activeMembers as $member)
                    <tr class="hover:bg-ue-surface-hover">
                        <td class="px-4 py-3 font-semibold text-ue-text">{{ $member->user?->name }}</td>
                        <td class="px-4 py-3 text-ue-text-muted">{{ $member->role?->label() ?? ucfirst($member->role) }}</td>
                        <td class="px-4 py-3 text-ue-text-muted">{{ $member->joined_at?->format('d/m/Y') }}</td>
                        <td class="px-4 py-3 text-right">
                            <button wire:click="openRemoveMember({{ $member->id }})"
                                class="text-red-600 hover:underline text-xs font-semibold">Xóa</button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-4 py-6 text-center text-ue-text-muted">Chưa có thành viên</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="pt-4">{{ $this->activeMembers->links('pagination::simple-tailwind') }}</div>
    </x-ui.card>

    {{-- Modals --}}

    {{-- Suspend --}}
    @if ($showSuspendModal)
    <div class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
        <div class="bg-ue-surface rounded-xl shadow-2xl w-full max-w-md p-6 space-y-4">
            <h3 class="text-lg font-bold text-ue-text">Tạm khóa cộng đồng</h3>
            <div>
                <label class="block text-sm font-semibold text-ue-text mb-1">Lý do nội bộ <span class="text-red-500">*</span></label>
                <textarea wire:model="suspendReason" rows="3"
                    class="w-full px-3 py-2 border border-ue-border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-red-500"
                    placeholder="Lý do tạm khóa (nội bộ, không hiển thị cho người dùng)..."></textarea>
                @error('suspendReason') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-semibold text-ue-text mb-1">Thông báo công khai <span class="text-red-500">*</span></label>
                <textarea wire:model="suspendSafeReason" rows="2"
                    class="w-full px-3 py-2 border border-ue-border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-red-500"
                    placeholder="Thông báo hiển thị cho thành viên..."></textarea>
                @error('suspendSafeReason') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <label class="flex items-center gap-2 text-sm text-ue-text">
                <input type="checkbox" wire:model="suspendNotifyMembers">
                Gửi thông báo cho thành viên
            </label>
            <div class="flex gap-3 justify-end">
                <button wire:click="$set('showSuspendModal', false)"
                    class="px-4 py-2 border border-ue-border rounded-lg text-sm hover:bg-ue-surface-hover">Hủy</button>
                <button wire:click="confirmSuspend"
                    class="px-4 py-2 bg-red-600 text-white rounded-lg text-sm font-semibold hover:bg-red-700">Tạm khóa</button>
            </div>
        </div>
    </div>
    @endif

    {{-- Reactivate --}}
    @if ($showReactivateModal)
    <div class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
        <div class="bg-ue-surface rounded-xl shadow-2xl w-full max-w-md p-6 space-y-4">
            <h3 class="text-lg font-bold text-ue-text">Kích hoạt lại cộng đồng</h3>
            <div>
                <label class="block text-sm font-semibold text-ue-text mb-1">Lý do (tùy chọn)</label>
                <input type="text" wire:model="reactivateReason"
                    class="w-full px-3 py-2 border border-ue-border rounded-lg text-sm"
                    placeholder="Lý do kích hoạt lại...">
            </div>
            <div class="flex gap-3 justify-end">
                <button wire:click="$set('showReactivateModal', false)"
                    class="px-4 py-2 border border-ue-border rounded-lg text-sm hover:bg-ue-surface-hover">Hủy</button>
                <button wire:click="confirmReactivate"
                    class="px-4 py-2 bg-green-600 text-white rounded-lg text-sm font-semibold hover:bg-green-700">Kích hoạt</button>
            </div>
        </div>
    </div>
    @endif

    {{-- Archive --}}
    @if ($showArchiveModal)
    <div class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
        <div class="bg-ue-surface rounded-xl shadow-2xl w-full max-w-md p-6 space-y-4">
            <h3 class="text-lg font-bold text-ue-text">Lưu trữ cộng đồng</h3>
            <div>
                <label class="block text-sm font-semibold text-ue-text mb-1">Lý do <span class="text-red-500">*</span></label>
                <textarea wire:model="archiveReason" rows="3"
                    class="w-full px-3 py-2 border border-ue-border rounded-lg text-sm"
                    placeholder="Lý do lưu trữ cộng đồng..."></textarea>
                @error('archiveReason') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div class="flex gap-3 justify-end">
                <button wire:click="$set('showArchiveModal', false)"
                    class="px-4 py-2 border border-ue-border rounded-lg text-sm hover:bg-ue-surface-hover">Hủy</button>
                <button wire:click="confirmArchive"
                    class="px-4 py-2 bg-gray-600 text-white rounded-lg text-sm font-semibold hover:bg-gray-700">Lưu trữ</button>
            </div>
        </div>
    </div>
    @endif

    {{-- Remove Member --}}
    @if ($showRemoveModal)
    <div class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
        <div class="bg-ue-surface rounded-xl shadow-2xl w-full max-w-md p-6 space-y-4">
            <h3 class="text-lg font-bold text-ue-text">Xóa thành viên</h3>
            <div>
                <label class="block text-sm font-semibold text-ue-text mb-1">Lý do <span class="text-red-500">*</span></label>
                <textarea wire:model="removeMemberReason" rows="2"
                    class="w-full px-3 py-2 border border-ue-border rounded-lg text-sm"
                    placeholder="Lý do xóa thành viên..."></textarea>
                @error('removeMemberReason') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div class="flex gap-3 justify-end">
                <button wire:click="$set('showRemoveModal', false)"
                    class="px-4 py-2 border border-ue-border rounded-lg text-sm hover:bg-ue-surface-hover">Hủy</button>
                <button wire:click="confirmRemoveMember"
                    class="px-4 py-2 bg-red-600 text-white rounded-lg text-sm font-semibold hover:bg-red-700">Xóa thành viên</button>
            </div>
        </div>
    </div>
    @endif

    {{-- Add Member --}}
    @if ($showAddMemberModal)
    <div class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
        <div class="bg-ue-surface rounded-xl shadow-2xl w-full max-w-md p-6 space-y-4">
            <h3 class="text-lg font-bold text-ue-text">Thêm thành viên</h3>
            <div>
                <label class="block text-sm font-semibold text-ue-text mb-1">User ID <span class="text-red-500">*</span></label>
                <input type="number" wire:model="addMemberUserId"
                    class="w-full px-3 py-2 border border-ue-border rounded-lg text-sm"
                    placeholder="Nhập user ID...">
                @error('addMemberUserId') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-semibold text-ue-text mb-1">Vai trò</label>
                <select wire:model="addMemberRole"
                    class="w-full px-3 py-2 border border-ue-border rounded-lg text-sm">
                    <option value="member">Thành viên</option>
                    <option value="moderator">Kiểm duyệt viên</option>
                    <option value="manager">Quản lý cộng đồng</option>
                </select>
            </div>
            <div class="flex gap-3 justify-end">
                <button wire:click="$set('showAddMemberModal', false)"
                    class="px-4 py-2 border border-ue-border rounded-lg text-sm hover:bg-ue-surface-hover">Hủy</button>
                <button wire:click="confirmAddMember"
                    class="px-4 py-2 bg-ue-brand text-white rounded-lg text-sm font-semibold hover:bg-opacity-90">Thêm</button>
            </div>
        </div>
    </div>
    @endif

    {{-- Approve Join Request --}}
    @if ($showApproveJoinRequestModal)
    <div class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
        <div class="bg-ue-surface rounded-xl shadow-2xl w-full max-w-md p-6 space-y-4">
            <h3 class="text-lg font-bold text-ue-text">Chấp nhận yêu cầu tham gia?</h3>
            <p class="text-sm text-ue-text-muted">Người dùng sẽ trở thành thành viên ngay lập tức.</p>
            <div class="flex gap-3 justify-end">
                <button wire:click="$set('showApproveJoinRequestModal', false)"
                    class="px-4 py-2 border border-ue-border rounded-lg text-sm hover:bg-ue-surface-hover">Hủy</button>
                <button wire:click="confirmApproveJoinRequest"
                    class="px-4 py-2 bg-green-600 text-white rounded-lg text-sm font-semibold hover:bg-green-700">Chấp nhận</button>
            </div>
        </div>
    </div>
    @endif

    {{-- Reject Join Request --}}
    @if ($showRejectJoinRequestModal)
    <div class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
        <div class="bg-ue-surface rounded-xl shadow-2xl w-full max-w-md p-6 space-y-4">
            <h3 class="text-lg font-bold text-ue-text">Từ chối yêu cầu tham gia</h3>
            <div>
                <label class="block text-sm font-semibold text-ue-text mb-1">Lý do <span class="text-red-500">*</span></label>
                <textarea wire:model="joinRequestRejectReason" rows="2"
                    class="w-full px-3 py-2 border border-ue-border rounded-lg text-sm"
                    placeholder="Lý do từ chối..."></textarea>
                @error('joinRequestRejectReason') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div class="flex gap-3 justify-end">
                <button wire:click="$set('showRejectJoinRequestModal', false)"
                    class="px-4 py-2 border border-ue-border rounded-lg text-sm hover:bg-ue-surface-hover">Hủy</button>
                <button wire:click="confirmRejectJoinRequest"
                    class="px-4 py-2 bg-red-600 text-white rounded-lg text-sm font-semibold hover:bg-red-700">Từ chối</button>
            </div>
        </div>
    </div>
    @endif

</div>
