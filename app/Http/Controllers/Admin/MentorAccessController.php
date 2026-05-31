<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\MentorAccessActionRequest;
use App\Models\MentorAccess;
use App\Services\AuditService;
use Illuminate\Support\Facades\Gate;

class MentorAccessController extends Controller
{
    public function index()
    {
        $user = request()->user();

        if (! $user || (! $user->hasRole('admin') && ! $user->hasRole('super_admin') && ! Gate::any(['manage_mentor_access', 'manage_permissions']))) {
            abort(403);
        }

        $requests = MentorAccess::latest()->paginate(25);

        return view('admin.mentors-queue', ['requests' => $requests]);
    }

    public function show($id)
    {
        $user = request()->user();

        if (! $user || (! $user->hasRole('admin') && ! $user->hasRole('super_admin') && ! Gate::any(['manage_mentor_access', 'manage_permissions']))) {
            abort(403);
        }

        $req = MentorAccess::findOrFail($id);

        return view('admin.mentors-detail', ['id' => $id, 'request' => $req]);
    }

    public function handle(MentorAccessActionRequest $request, MentorAccess $mentorAccess, AuditService $audit)
    {
        $data = $request->validated();

        $before = $mentorAccess->toArray();

        switch ($data['action']) {
            case 'approve':
            case 'grant':
                $mentorAccess->status = 'approved';
                break;
            case 'reject':
                $mentorAccess->status = 'rejected';
                break;
            case 'pause':
                $mentorAccess->status = 'paused';
                break;
            case 'request_more_info':
                $mentorAccess->status = 'need_more_information';
                $mentorAccess->note = $data['instruction'] ?? null;
                break;
            case 'revoke':
                $mentorAccess->status = 'revoked';
                break;
        }

        $mentorAccess->reviewed_by = request()->user()?->id;
        $mentorAccess->reviewed_at = now();
        $mentorAccess->save();

        $audit->log([
            'action' => 'mentor_access_action',
            'target_type' => 'mentor_access',
            'target_id' => $mentorAccess->id,
            'before_values' => $before,
            'after_values' => $mentorAccess->toArray(),
            'reason' => $data['reason'],
        ]);

        return back()->with('status', 'Mentor access action applied.');
    }
}
