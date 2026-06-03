<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Mentor\GrantMentorAccessAction;
use App\Actions\Mentor\ReviewMentorAccessAction;
use App\Actions\Mentor\RevokeMentorAccessAction;
use App\Enums\MentorAccessStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\MentorAccessActionRequest;
use App\Models\MentorAccessRequest;
use App\Models\MentorProfile;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

class MentorAccessController extends Controller
{
    public function index()
    {
        $user = request()->user();

        if (! $user || (! $user->hasRole('admin') && ! $user->hasRole('super_admin') && ! Gate::any(['manage_mentor_access', 'manage_permissions']))) {
            abort(403);
        }

        $requests = MentorAccessRequest::with(['user.profile', 'reviewer'])->latest()->paginate(25);

        return view('admin.mentors-queue', ['requests' => $requests]);
    }

    public function show(int $id)
    {
        $user = request()->user();

        if (! $user || (! $user->hasRole('admin') && ! $user->hasRole('super_admin') && ! Gate::any(['manage_mentor_access', 'manage_permissions']))) {
            abort(403);
        }

        $req = MentorAccessRequest::with(['user.profile', 'reviewer'])->findOrFail($id);

        return view('admin.mentors-detail', ['id' => $id, 'request' => $req]);
    }

    public function handle(MentorAccessActionRequest $request, MentorAccessRequest $mentorAccess, GrantMentorAccessAction $grant, ReviewMentorAccessAction $review, RevokeMentorAccessAction $revoke)
    {
        $data = $request->validated();
        $admin = $request->user();

        match ($data['action']) {
            'approve', 'grant' => $grant->execute($admin, $mentorAccess, [
                'reason' => $data['reason'] ?? null,
                'admin_notes' => $data['instruction'] ?? null,
            ]),
            'reject' => $review->execute($admin, $mentorAccess, [
                'action' => 'reject',
                'reason' => $data['reason'] ?? null,
                'admin_notes' => $data['instruction'] ?? null,
            ]),
            'request_more_info' => $review->execute($admin, $mentorAccess, [
                'action' => 'need_more_info',
                'reason' => $data['reason'] ?? null,
                'admin_notes' => $data['instruction'] ?? null,
            ]),
            'revoke' => $revoke->execute($admin, $mentorAccess->user->mentorProfile, [
                'reason' => $data['reason'],
                'admin_notes' => $data['instruction'] ?? null,
            ]),
            default => $review->execute($admin, $mentorAccess, [
                'action' => 'under_review',
                'reason' => $data['reason'] ?? null,
                'admin_notes' => $data['instruction'] ?? null,
            ]),
        };

        return back()->with('status', 'Mentor access action applied.');
    }

    public function approve(MentorAccessRequest $mentorAccessRequest, GrantMentorAccessAction $grant)
    {
        $grant->execute(request()->user(), $mentorAccessRequest, [
            'reason' => request('reason'),
            'admin_notes' => request('admin_notes'),
        ]);

        return back()->with('status', 'Mentor access approved.');
    }

    public function reject(MentorAccessRequest $mentorAccessRequest, ReviewMentorAccessAction $review)
    {
        $review->execute(request()->user(), $mentorAccessRequest, [
            'action' => 'reject',
            'reason' => request('reason'),
            'admin_notes' => request('admin_notes'),
        ]);

        return back()->with('status', 'Mentor access rejected.');
    }

    public function needMoreInfo(MentorAccessRequest $mentorAccessRequest, ReviewMentorAccessAction $review)
    {
        $review->execute(request()->user(), $mentorAccessRequest, [
            'action' => 'need_more_info',
            'reason' => request('reason'),
            'admin_notes' => request('admin_notes'),
        ]);

        return back()->with('status', 'More information requested.');
    }

    public function revoke(MentorProfile $mentorProfile, RevokeMentorAccessAction $revoke)
    {
        $revoke->execute(request()->user(), $mentorProfile, [
            'reason' => request('reason', 'Revoked by administrator.'),
            'admin_notes' => request('admin_notes'),
        ]);

        return back()->with('status', 'Mentor access revoked.');
    }

    public function grant(User $user, GrantMentorAccessAction $grant)
    {
        $accessRequest = MentorAccessRequest::firstOrCreate(
            ['user_id' => $user->id, 'status' => MentorAccessStatus::Submitted],
            [
                'requested_role_context' => $user->profile?->role_type ?? 'alumni',
                'motivation' => request('motivation', 'Direct grant by administrator.'),
                'experience_summary' => request('experience_summary'),
            ]
        );

        $grant->execute(request()->user(), $accessRequest, [
            'reason' => request('reason', 'Direct grant by administrator.'),
            'admin_notes' => request('admin_notes'),
        ]);

        return back()->with('status', 'Mentor access granted.');
    }
}
