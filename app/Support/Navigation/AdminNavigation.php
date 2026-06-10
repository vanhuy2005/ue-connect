<?php

namespace App\Support\Navigation;

use Illuminate\Support\Facades\Gate;

class AdminNavigation
{
    public static function getGroups(): array
    {
        return [
            'overview' => [
                'label' => 'Overview',
                'vn_label' => 'Tổng quan',
                'icon' => 'shield',
                'description' => 'Sức khỏe hệ thống, analytics và các chỉ báo vận hành.',
                'items' => [
                    [
                        'key' => 'dashboard',
                        'label' => 'Tổng quan quản trị',
                        'route' => 'admin.dashboard',
                        'icon' => 'shield',
                        'permission' => null,
                        'description' => 'Trang tổng quan hoạt động hệ thống',
                    ],
                    [
                        'key' => 'analytics',
                        'label' => 'Phân tích',
                        'route' => 'admin.analytics.index',
                        'icon' => 'bar-chart-3',
                        'permission' => null,
                        'description' => 'Xem thống kê và phân tích số liệu',
                    ],
                ],
            ],
            'people-access' => [
                'label' => 'People & Access',
                'vn_label' => 'Người dùng & Quyền',
                'icon' => 'users',
                'description' => 'Xác thực, người dùng, mentor access và phân quyền.',
                'items' => [
                    [
                        'key' => 'verification',
                        'label' => 'Duyệt xác thực',
                        'route' => 'admin.verifications.queue',
                        'icon' => 'shield-check',
                        'permission' => 'review_verification',
                        'description' => 'Xem xét các yêu cầu xác thực danh tính sinh viên',
                    ],
                    [
                        'key' => 'users',
                        'label' => 'Người dùng',
                        'route' => 'admin.users.index',
                        'icon' => 'users',
                        'permission' => 'manage_users',
                        'description' => 'Quản lý tài khoản người dùng, đình chỉ hoặc mở khoá',
                    ],
                    [
                        'key' => 'mentors',
                        'label' => 'Quản lý Mentor',
                        'route' => 'admin.mentors.index',
                        'icon' => 'graduation-cap',
                        'permission' => 'manage_mentor_access',
                        'description' => 'Duyệt và quản lý quyền cố vấn học tập',
                    ],
                    [
                        'key' => 'permissions',
                        'label' => 'Vai trò & Quyền',
                        'route' => 'admin.permissions.index',
                        'icon' => 'key-round',
                        'permission' => 'manage_permissions',
                        'description' => 'Quản lý vai trò và cấp phát quyền hạn',
                    ],
                ],
            ],
            'content-safety' => [
                'label' => 'Content & Safety',
                'vn_label' => 'An toàn & Nội dung',
                'icon' => 'shield-check',
                'description' => 'Kiểm duyệt, báo cáo, cộng đồng và thông báo cộng đồng.',
                'items' => [
                    [
                        'key' => 'moderation',
                        'label' => 'Kiểm duyệt',
                        'route' => 'admin.moderation.index',
                        'icon' => 'flag',
                        'permission' => 'manage_reports',
                        'description' => 'Công cụ kiểm duyệt nội dung toàn hệ thống',
                    ],
                    [
                        'key' => 'reports',
                        'label' => 'Báo cáo',
                        'route' => 'admin.reports.index',
                        'icon' => 'alert-triangle',
                        'permission' => 'manage_reports',
                        'description' => 'Danh sách các báo cáo vi phạm từ người dùng',
                    ],
                    [
                        'key' => 'opportunities',
                        'label' => 'Quản lý cơ hội',
                        'route' => 'admin.opportunities.queue',
                        'icon' => 'briefcase',
                        'permission' => 'manage_reports',
                        'description' => 'Kiểm duyệt các tin đăng chia sẻ cơ hội việc làm và thực tập',
                    ],
                    [
                        'key' => 'communities',
                        'label' => 'Quản lý cộng đồng',
                        'route' => 'admin.communities.index',
                        'icon' => 'building-2',
                        'permission' => 'manage_communities',
                        'description' => 'Quản lý danh sách các cộng đồng/nhóm sinh viên',
                    ],
                    [
                        'key' => 'community-suggestions',
                        'label' => 'Đề xuất cộng đồng',
                        'route' => 'admin.community-suggestions.index',
                        'icon' => 'file-text',
                        'permission' => 'manage_communities',
                        'description' => 'Duyệt các đề xuất thành lập cộng đồng/CLB từ sinh viên',
                    ],
                    [
                        'key' => 'announcements',
                        'label' => 'Thông báo cộng đồng',
                        'route' => 'admin.announcements.index',
                        'icon' => 'megaphone',
                        'permission' => 'manage_announcements',
                        'description' => 'Tạo và gửi thông báo chung cho toàn trường',
                    ],
                ],
            ],
            'system' => [
                'label' => 'Platform Operations',
                'vn_label' => 'Hệ thống',
                'icon' => 'settings-2',
                'description' => 'Audit, thông báo vận hành, media và cấu hình hệ thống.',
                'items' => [
                    [
                        'key' => 'audit-logs',
                        'label' => 'Nhật ký thao tác',
                        'route' => 'admin.audit-logs.index',
                        'icon' => 'history',
                        'permission' => 'view_audit_logs',
                        'description' => 'Xem nhật ký hoạt động của ban quản trị',
                    ],
                    [
                        'key' => 'notifications',
                        'label' => 'Thông báo hệ thống',
                        'route' => 'admin.notifications.index',
                        'icon' => 'bell',
                        'permission' => 'manage_system_settings',
                        'description' => 'Xem và quản lý thông báo vận hành hệ thống',
                    ],
                    [
                        'key' => 'media',
                        'label' => 'Quản lý Media',
                        'route' => 'admin.media.index',
                        'icon' => 'image',
                        'permission' => 'manage_media',
                        'description' => 'Quản lý tệp tin tải lên, dung lượng và đồng bộ Cloudinary',
                    ],
                    [
                        'key' => 'system-settings',
                        'label' => 'Cài đặt hệ thống',
                        'route' => 'admin.system-settings.index',
                        'icon' => 'settings-2',
                        'permission' => 'manage_system_settings',
                        'description' => 'Cấu hình các tham số vận hành, sao lưu và khôi phục cài đặt',
                    ],
                    [
                        'key' => 'ai-chat-logs',
                        'label' => 'Giám sát AI Chatbot',
                        'route' => 'admin.ai-chat-logs.index',
                        'icon' => 'message-square',
                        'permission' => 'manage_system_settings',
                        'description' => 'Theo dõi nhật ký câu hỏi AI, phản hồi, cấu trúc truy vấn và đánh giá từ sinh viên',
                    ],
                ],
            ],
        ];
    }

    public static function getVisibleGroups(): array
    {
        $user = auth()->user();
        if (! $user) {
            return [];
        }

        $allGroups = self::getGroups();
        $filtered = [];

        foreach ($allGroups as $groupKey => $group) {
            $filteredItems = [];
            foreach ($group['items'] as $item) {
                if ($item['permission'] === null) {
                    $filteredItems[] = $item;
                } elseif (Gate::allows($item['permission'])) {
                    $filteredItems[] = $item;
                } elseif ($item['permission'] === 'view_audit_logs' && (Gate::allows('view_audit_logs') || Gate::allows('view_audit_log'))) {
                    $filteredItems[] = $item;
                }
            }

            if (! empty($filteredItems)) {
                $group['items'] = $filteredItems;
                $filtered[$groupKey] = $group;
            }
        }

        return $filtered;
    }
}
