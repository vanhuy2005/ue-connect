<?php

namespace App\Actions\Admin;

use App\Models\Announcement;
use App\Models\AuditLog;
use App\Models\Comment;
use App\Models\Community;
use App\Models\Greeting;
use App\Models\MentorAccess;
use App\Models\Post;
use App\Models\Report;
use App\Models\User;
use App\Models\VerificationRequest;

class BuildAdminDashboardAction
{
    public function execute(): array
    {
        $today = now()->toDateString();

        return [
            'verification' => [
                'pending' => VerificationRequest::where('status', 'pending_review')->count(),
                'needs_info' => VerificationRequest::where('status', 'need_more_information')->count(),
                'conflicts' => VerificationRequest::where('status', 'conflict')->count(),
                'approved_today' => VerificationRequest::where('status', 'approved')->whereDate('updated_at', $today)->count(),
            ],
            'safety' => [
                'pending_reports' => Report::where('status', 'pending')->count(),
                'suspended_users' => User::where('account_status', 'suspended')->count(),
                'auto_hidden_content' => Post::where('visibility', 'hidden_by_system')->count(),
            ],
            'engagement' => [
                'daily_posts' => Post::whereDate('created_at', $today)->count(),
                'daily_comments' => Comment::whereDate('created_at', $today)->count(),
                'daily_greetings' => Greeting::whereDate('created_at', $today)->count(),
                'mentor_requests_pending' => MentorAccess::where('status', 'requested')->whereDate('created_at', $today)->count(),
            ],
            'community' => [
                'active_communities' => Community::where('status', 'active')->count(),
                'announcements_active' => Announcement::where('status', 'published')->where('expires_at', '>=', now())->count(),
            ],
            'recent_audit' => AuditLog::latest()->limit(8)->get(),
        ];
    }
}
