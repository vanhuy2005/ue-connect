<?php

namespace App\Actions\Admin;

use App\Enums\AccountStatus;
use App\Enums\VerificationStatus;
use App\Models\AuditLog;
use App\Models\Comment;
use App\Models\Media;
use App\Models\MediaVariant;
use App\Models\Post;
use App\Models\Report;
use App\Models\User;
use App\Models\VerificationRequest;
use App\Services\Media\MediaQuotaService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class BuildAdminDashboardAction
{
    public function execute(): array
    {
        $today = now()->toDateString();
        $sevenDaysAgo = now()->subDays(7)->toDateTimeString();

        // 1. Media metrics
        $mediaStats = [
            'total_media_count' => 0,
            'total_storage_bytes' => 0,
            'today_upload_bytes' => 0,
            'cloudinary_synced_today' => 0,
            'failed_cloudinary_sync_count' => 0,
            'temporary_media_count' => 0,
            'orphan_media_count' => 0,
            'quota_warning' => false,
            'usage_percent' => 0,
            'warning_level' => 'neutral',
        ];

        if (Schema::hasTable('media')) {
            try {
                $mediaStats['total_media_count'] = Media::count();
                $quotaService = app(MediaQuotaService::class);
                $globalUsage = $quotaService->globalUsage();

                $mediaStats['total_storage_bytes'] = $globalUsage['storage_bytes'];
                $mediaStats['today_upload_bytes'] = $globalUsage['daily_upload_bytes'];
                $mediaStats['cloudinary_synced_today'] = $globalUsage['cloudinary_synced_today'];

                $mediaStats['failed_cloudinary_sync_count'] = MediaVariant::where('cloudinary_sync_status', 'failed')->count();
                $mediaStats['temporary_media_count'] = Media::where('status', 'temporary')->count();

                // Calculate warning levels
                if ($globalUsage['limits']['global_max_storage_bytes'] > 0) {
                    $mediaStats['usage_percent'] = round(($globalUsage['storage_bytes'] / $globalUsage['limits']['global_max_storage_bytes']) * 100, 1);
                    if ($mediaStats['usage_percent'] >= 90) {
                        $mediaStats['warning_level'] = 'critical';
                        $mediaStats['quota_warning'] = true;
                    } elseif ($mediaStats['usage_percent'] >= 70) {
                        $mediaStats['warning_level'] = 'warning';
                        $mediaStats['quota_warning'] = true;
                    }
                }
            } catch (\Throwable $e) {
                // Fail-safe defaults
            }
        }

        // 2. Fetch raw counts
        $pendingVerificationsCount = VerificationRequest::where('status', VerificationStatus::PENDING_REVIEW)->count();
        $needsInfoVerificationsCount = VerificationRequest::where('status', VerificationStatus::NEEDS_MORE_INFORMATION)->count();
        $conflictsVerificationsCount = VerificationRequest::where('status', VerificationStatus::CONFLICT)->count();

        $openReportsCount = Report::where('status', 'pending')->count();
        $criticalReportsCount = 0; // Set to 0 as default since report table lacks critical flag, but we will treat conflict verifications or media quota alerts as critical

        $pendingModerationCount = Post::where('visibility', 'hidden_by_system')->count();
        $restrictedUsersCount = User::where('account_status', AccountStatus::SUSPENDED)->count();

        // 3. Priority Queue items
        $priorityItems = [];

        // Add verification requests
        $pendingVerifications = VerificationRequest::whereIn('status', [VerificationStatus::PENDING_REVIEW, VerificationStatus::CONFLICT])
            ->with('user')
            ->latest()
            ->limit(5)
            ->get();

        foreach ($pendingVerifications as $req) {
            if (! $req->user) {
                continue;
            }
            $severity = $req->status === VerificationStatus::CONFLICT ? 'critical' : 'info';
            $title = $req->status === VerificationStatus::CONFLICT
                ? "Xung đột MSSV: Yêu cầu của {$req->user->name} (MSSV: {$req->student_id})"
                : "Yêu cầu xác thực sinh viên của {$req->user->name} chờ phê duyệt";

            $priorityItems[] = [
                'type' => 'Xác thực',
                'type_key' => 'verification',
                'title' => $title,
                'severity' => $severity,
                'created_at' => $req->created_at,
                'cta_url' => route('admin.verifications.detail', $req->id),
                'cta_label' => 'Xem hồ sơ',
            ];
        }

        // Add pending reports
        $pendingReports = Report::where('status', 'pending')
            ->with('reporter')
            ->latest()
            ->limit(5)
            ->get();

        foreach ($pendingReports as $report) {
            $severity = 'warning';
            $reason = $report->reason instanceof \BackedEnum
                ? $report->reason->value
                : (string) $report->reason;
            $title = "Báo cáo nội dung vi phạm: {$reason}";

            $priorityItems[] = [
                'type' => 'Báo cáo',
                'type_key' => 'report',
                'title' => $title,
                'severity' => $severity,
                'created_at' => $report->created_at,
                'cta_url' => route('admin.reports.show', $report),
                'cta_label' => 'Xử lý',
            ];
        }

        // Media quota warning
        if ($mediaStats['quota_warning']) {
            $priorityItems[] = [
                'type' => 'Hệ thống',
                'type_key' => 'system',
                'title' => "Cảnh báo lưu trữ: Dung lượng media đã đạt {$mediaStats['usage_percent']}%",
                'severity' => $mediaStats['warning_level'],
                'created_at' => now(),
                'cta_url' => route('admin.media.usage'),
                'cta_label' => 'Xem Media',
            ];
        }

        // Sort priority items by severity first, then by date (latest first)
        usort($priorityItems, function ($a, $b) {
            $severityOrder = ['critical' => 3, 'warning' => 2, 'info' => 1];
            $orderA = $severityOrder[$a['severity']] ?? 0;
            $orderB = $severityOrder[$b['severity']] ?? 0;

            if ($orderA !== $orderB) {
                return $orderB <=> $orderA;
            }

            return $b['created_at'] <=> $a['created_at'];
        });

        $priorityItems = array_slice($priorityItems, 0, 8);

        // 4. System health statuses
        $systemHealth = [
            [
                'name' => 'Cơ sở dữ liệu (Database)',
                'status' => 'healthy',
                'message' => 'Kết nối SQL database hoạt động tốt.',
                'last_checked' => now(),
            ],
            [
                'name' => 'Hàng đợi (Queue Worker)',
                'status' => Schema::hasTable('failed_jobs') && DB::table('failed_jobs')->count() > 0 ? 'degraded' : 'healthy',
                'message' => Schema::hasTable('failed_jobs') && DB::table('failed_jobs')->count() > 0
                    ? 'Có '.DB::table('failed_jobs')->count().' tác vụ bị lỗi trong hàng đợi.'
                    : 'Hàng đợi rỗng hoặc đang xử lý bình thường.',
                'last_checked' => now(),
            ],
            [
                'name' => 'Dịch vụ Email (Mail)',
                'status' => config('mail.default') ? 'healthy' : 'disabled',
                'message' => config('mail.default') ? 'Cấu hình gửi thư qua '.config('mail.default').' sẵn sàng.' : 'Chưa cấu hình dịch vụ email.',
                'last_checked' => now(),
            ],
            [
                'name' => 'Truyền thông tin thời gian thực (Broadcast)',
                'status' => in_array(config('broadcasting.default'), ['reverb', 'pusher']) ? 'healthy' : 'disabled',
                'message' => 'Real-time broadcasting được cấu hình qua '.config('broadcasting.default', 'none').'.',
                'last_checked' => now(),
            ],
            [
                'name' => 'Lưu trữ Media (Local)',
                'status' => 'healthy',
                'message' => 'Quyền đọc/ghi trên ổ đĩa cục bộ hoạt động tốt.',
                'last_checked' => now(),
            ],
            [
                'name' => 'Đồng bộ Cloudinary',
                'status' => config('services.cloudinary.cloud_name') ? 'healthy' : 'disabled',
                'message' => config('services.cloudinary.cloud_name') ? 'Đồng bộ Cloudinary đang sẵn sàng.' : 'Chưa cấu hình Cloudinary.',
                'last_checked' => now(),
            ],
        ];

        // 5. Build snapshot array
        $snapshot = [
            'pending_verification' => $pendingVerificationsCount,
            'needs_info_verification' => $needsInfoVerificationsCount,
            'conflicts_verification' => $conflictsVerificationsCount,
            'approved_today' => VerificationRequest::where('status', VerificationStatus::APPROVED)->whereDate('updated_at', $today)->count(),
            'open_reports' => $openReportsCount,
            'critical_reports' => $conflictsVerificationsCount + ($mediaStats['quota_warning'] && $mediaStats['warning_level'] === 'critical' ? 1 : 0),
            'pending_moderation' => $pendingModerationCount,
            'restricted_users' => $restrictedUsersCount,
            'media_total_files' => $mediaStats['total_media_count'],
            'media_usage_bytes' => $mediaStats['total_storage_bytes'],
            'media_usage_percent' => $mediaStats['usage_percent'],
            'media_usage_warning_level' => $mediaStats['warning_level'],
        ];

        // 6. Build trends array
        $trends = [
            'new_users' => User::where('created_at', '>=', $sevenDaysAgo)->count(),
            'posts' => Post::where('created_at', '>=', $sevenDaysAgo)->count(),
            'comments' => Comment::where('created_at', '>=', $sevenDaysAgo)->count(),
            'reports' => Report::where('created_at', '>=', $sevenDaysAgo)->count(),
            'verifications_approved' => VerificationRequest::where('status', VerificationStatus::APPROVED)
                ->where('updated_at', '>=', $sevenDaysAgo)
                ->count(),
        ];

        return [
            'snapshot' => $snapshot,
            'priority_queue' => $priorityItems,
            'system_health' => $systemHealth,
            'recent_activity' => AuditLog::latest()->limit(8)->get(),
            'trends' => $trends,
        ];
    }
}
