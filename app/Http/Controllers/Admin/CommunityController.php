<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Community\ApproveJoinRequestAction;
use App\Actions\Community\ArchiveCommunityAction;
use App\Actions\Community\GrantClubManagerAction;
use App\Actions\Community\ReactivateCommunityAction;
use App\Actions\Community\RejectJoinRequestAction;
use App\Actions\Community\RemoveCommunityMemberAction;
use App\Actions\Community\ReviewCommunityResourceAction;
use App\Actions\Community\ReviewCommunitySuggestionAction;
use App\Actions\Community\RevokeClubManagerAction;
use App\Actions\Community\SuspendCommunityAction;
use App\Enums\CommunityMemberRole;
use App\Enums\CommunityMemberStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CreateCommunityRequest;
use App\Http\Requests\Admin\UpdateCommunityRequest;
use App\Models\Community;
use App\Models\CommunityJoinRequest;
use App\Models\CommunityMember;
use App\Models\CommunityResource;
use App\Models\CommunitySuggestion;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CommunityController extends Controller
{
    public function index(): View
    {
        $this->authorize('manage_communities');

        return view('admin.communities-list');
    }

    public function create(): View
    {
        $this->authorize('manage_communities');

        return view('admin.communities-create');
    }

    public function store(CreateCommunityRequest $request, AuditService $audit): RedirectResponse
    {
        $this->authorize('manage_communities');

        $data = $request->validated();

        $community = Community::create([
            'name' => $data['name'],
            'slug' => $data['slug'] ?? Str::slug($data['name']),
            'type' => $data['type'] ?? 'club',
            'description' => $data['description'] ?? null,
            'short_description' => $data['short_description'] ?? null,
            'visibility' => $data['visibility'] ?? 'public',
            'join_policy' => $data['join_policy'] ?? 'approval_required',
            'status' => $data['status'] ?? 'draft',
            'created_by' => $request->user()->id,
            'owner_id' => $data['owner_id'] ?? null,
            'related_faculty' => $data['related_faculty'] ?? null,
        ]);

        if ($community->owner_id) {
            CommunityMember::create([
                'community_id' => $community->id,
                'user_id' => $community->owner_id,
                'role' => CommunityMemberRole::Owner->value,
                'status' => CommunityMemberStatus::Active->value,
                'joined_at' => now(),
            ]);
            $community->increment('members_count');
        }

        $audit->log([
            'action' => 'create_community',
            'target_type' => 'community',
            'target_id' => $community->id,
            'after_values' => $community->toArray(),
            'reason' => $request->input('reason') ?? 'Admin created community',
        ]);

        return redirect()->route('admin.communities.show', $community->id)
            ->with('status', 'Cộng đồng đã được tạo.');
    }

    public function show(Community $community): View
    {
        $this->authorize('manage_communities');

        return view('admin.communities-show', [
            'community' => $community->load(['creator', 'owner']),
        ]);
    }

    public function update(UpdateCommunityRequest $request, Community $community, AuditService $audit): RedirectResponse
    {
        $this->authorize('manage_communities');

        $before = $community->toArray();
        $community->update($request->validated());

        $audit->log([
            'action' => 'update_community',
            'target_type' => 'community',
            'target_id' => $community->id,
            'before_values' => $before,
            'after_values' => $community->fresh()?->toArray(),
            'reason' => $request->input('reason') ?? null,
        ]);

        return back()->with('status', 'Cộng đồng đã được cập nhật.');
    }

    public function suspend(Request $request, Community $community, SuspendCommunityAction $action): RedirectResponse
    {
        $this->authorize('manage_communities');

        $action->execute($request->user(), $community, [
            'reason' => $request->input('reason', ''),
            'safe_reason' => $request->input('safe_reason', ''),
            'notify_members' => (bool) $request->boolean('notify_members'),
        ]);

        return back()->with('status', 'Cộng đồng đã bị tạm khóa.');
    }

    public function reactivate(Request $request, Community $community, ReactivateCommunityAction $action): RedirectResponse
    {
        $this->authorize('manage_communities');

        $action->execute($request->user(), $community, $request->input('reason'));

        return back()->with('status', 'Cộng đồng đã được kích hoạt lại.');
    }

    public function archive(Request $request, Community $community, ArchiveCommunityAction $action): RedirectResponse
    {
        $this->authorize('manage_communities');

        $action->execute($request->user(), $community, $request->input('reason', ''));

        return back()->with('status', 'Cộng đồng đã được lưu trữ.');
    }

    public function addMember(Request $request, Community $community, AuditService $audit): RedirectResponse
    {
        $this->authorize('manage_communities');

        $data = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'role' => ['nullable', 'string', 'in:member,moderator,manager,owner'],
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        $existing = CommunityMember::where('community_id', $community->id)
            ->where('user_id', $data['user_id'])
            ->first();

        if ($existing && $existing->status === 'active') {
            return back()->with('status', 'Người dùng đã là thành viên.');
        }

        $member = CommunityMember::updateOrCreate(
            ['community_id' => $community->id, 'user_id' => $data['user_id']],
            [
                'role' => $data['role'] ?? 'member',
                'status' => 'active',
                'joined_at' => now(),
                'removed_at' => null,
                'left_at' => null,
            ]
        );

        if (! $existing || $existing->status !== 'active') {
            $community->increment('members_count');
        }

        $audit->log([
            'action' => 'admin_add_community_member',
            'target_type' => 'community_member',
            'target_id' => $member->id,
            'context_type' => 'community',
            'context_id' => $community->id,
            'after_values' => $member->toArray(),
            'reason' => $data['reason'] ?? null,
        ]);

        return back()->with('status', 'Thành viên đã được thêm.');
    }

    public function removeMember(Request $request, Community $community, User $user, RemoveCommunityMemberAction $action): RedirectResponse
    {
        $this->authorize('manage_communities');

        $action->execute($request->user(), $community, $user, $request->input('reason', 'Admin removed member.'));

        return back()->with('status', 'Thành viên đã bị xóa.');
    }

    public function grantManager(Request $request, Community $community, User $user, GrantClubManagerAction $action): RedirectResponse
    {
        $this->authorize('manage_permissions');

        $action->execute($request->user(), $community, $user, $request->input('reason', ''));

        return back()->with('status', 'Quyền quản lý đã được cấp.');
    }

    public function revokeManager(Request $request, Community $community, User $user, RevokeClubManagerAction $action): RedirectResponse
    {
        $this->authorize('manage_permissions');

        $action->execute($request->user(), $community, $user, $request->input('reason', ''));

        return back()->with('status', 'Quyền quản lý đã bị thu hồi.');
    }

    public function approveJoinRequest(Request $request, CommunityJoinRequest $joinRequest, ApproveJoinRequestAction $action): RedirectResponse
    {
        $this->authorize('manage_communities');

        $action->execute($request->user(), $joinRequest, $request->input('reason'));

        return back()->with('status', 'Yêu cầu tham gia đã được chấp nhận.');
    }

    public function rejectJoinRequest(Request $request, CommunityJoinRequest $joinRequest, RejectJoinRequestAction $action): RedirectResponse
    {
        $this->authorize('manage_communities');

        $action->execute($request->user(), $joinRequest, $request->input('reason', ''));

        return back()->with('status', 'Yêu cầu tham gia đã bị từ chối.');
    }

    public function reviewResource(Request $request, CommunityResource $resource, ReviewCommunityResourceAction $action): RedirectResponse
    {
        $this->authorize('manage_communities');

        $action->execute($request->user(), $resource, [
            'action' => $request->input('action'),
            'reason' => $request->input('reason'),
        ]);

        return back()->with('status', 'Tài nguyên đã được xét duyệt.');
    }

    public function reviewSuggestion(Request $request, CommunitySuggestion $suggestion, ReviewCommunitySuggestionAction $action): RedirectResponse
    {
        $this->authorize('manage_communities');

        $action->execute($request->user(), $suggestion, $request->all());

        return back()->with('status', 'Đề xuất đã được xử lý.');
    }

    public function suggestions(): View
    {
        $this->authorize('manage_communities');

        return view('admin.communities-suggestions');
    }

    public function joinRequests(Community $community): View
    {
        $this->authorize('manage_communities');

        return view('admin.communities-join-requests', ['community' => $community]);
    }
}
