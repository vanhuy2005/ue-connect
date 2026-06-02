<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CreateCommunityRequest;
use App\Http\Requests\Admin\UpdateCommunityRequest;
use App\Models\Community;
use App\Services\AuditService;
use Illuminate\Http\Request;

class CommunityController extends Controller
{
    public function index()
    {
        $this->authorize('manage_communities');

        $communities = Community::latest()->paginate(25);

        return view('admin.communities-list', ['communities' => $communities]);
    }

    public function create()
    {
        $this->authorize('manage_communities');

        return view('admin.communities-create');
    }

    public function store(CreateCommunityRequest $request, AuditService $audit)
    {
        $data = $request->validated();

        $community = Community::create([
            'name' => $data['name'],
            'slug' => $data['slug'] ?? null,
            'description' => $data['description'] ?? null,
            'status' => $data['status'] ?? 'draft',
        ]);

        $audit->log([
            'action' => 'create_community',
            'target_type' => 'community',
            'target_id' => $community->id,
            'after_values' => $community->toArray(),
            'reason' => $request->input('reason') ?? null,
        ]);

        return redirect()->route('admin.communities.index')->with('status', 'Community created.');
    }

    public function show(Community $community)
    {
        $this->authorize('manage_communities');

        return view('admin.communities-show', ['community' => $community]);
    }

    public function update(UpdateCommunityRequest $request, Community $community, AuditService $audit)
    {
        $before = $community->toArray();
        $community->update($request->validated());

        $audit->log([
            'action' => 'update_community',
            'target_type' => 'community',
            'target_id' => $community->id,
            'before_values' => $before,
            'after_values' => $community->toArray(),
            'reason' => $request->input('reason') ?? null,
        ]);

        return back()->with('status', 'Community updated.');
    }

    public function suspend(Request $request, Community $community, AuditService $audit)
    {
        $this->authorize('manage_communities');

        $before = $community->toArray();
        $community->status = 'suspended';
        $community->save();

        $audit->log([
            'action' => 'suspend_community',
            'target_type' => 'community',
            'target_id' => $community->id,
            'before_values' => $before,
            'after_values' => $community->toArray(),
            'reason' => $request->input('reason') ?? null,
        ]);

        return back()->with('status', 'Community suspended.');
    }

    public function reactivate(Request $request, Community $community, AuditService $audit)
    {
        $this->authorize('manage_communities');

        $before = $community->toArray();
        $community->status = 'active';
        $community->save();

        $audit->log([
            'action' => 'reactivate_community',
            'target_type' => 'community',
            'target_id' => $community->id,
            'before_values' => $before,
            'after_values' => $community->toArray(),
            'reason' => $request->input('reason') ?? null,
        ]);

        return back()->with('status', 'Community reactivated.');
    }

    public function addMember(Request $request, Community $community, AuditService $audit)
    {
        $this->authorize('manage_communities');

        $data = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'role' => ['nullable', 'string'],
        ]);

        // create or ignore duplicate
        $existing = \DB::table('community_members')
            ->where('community_id', $community->id)
            ->where('user_id', $data['user_id'])
            ->first();

        if ($existing) {
            return back()->with('status', 'User is already a member.');
        }

        $member = \App\Models\CommunityMember::create([
            'community_id' => $community->id,
            'user_id' => $data['user_id'],
            'role' => $data['role'] ?? 'member',
            'joined_at' => now(),
            'status' => 'active',
        ]);

        // increment cached counter
        $community->increment('members_count');

        $audit->log([
            'action' => 'add_community_member',
            'target_type' => 'community',
            'target_id' => $community->id,
            'after_values' => $member->toArray(),
            'reason' => $request->input('reason') ?? null,
        ]);

        return back()->with('status', 'Member added.');
    }

    public function removeMember(Request $request, Community $community, $userId, AuditService $audit)
    {
        $this->authorize('manage_communities');

        $member = \App\Models\CommunityMember::where('community_id', $community->id)->where('user_id', $userId)->first();

        if (! $member) {
            return back()->with('status', 'Member not found.');
        }

        $before = $member->toArray();
        $member->delete();

        // decrement cached counter but avoid negative
        if ($community->members_count > 0) {
            $community->decrement('members_count');
        }

        $audit->log([
            'action' => 'remove_community_member',
            'target_type' => 'community',
            'target_id' => $community->id,
            'before_values' => $before,
            'reason' => $request->input('reason') ?? null,
        ]);

        return back()->with('status', 'Member removed.');
    }
}

