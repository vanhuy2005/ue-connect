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
}
