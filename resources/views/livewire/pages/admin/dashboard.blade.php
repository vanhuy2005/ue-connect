<?php

use Livewire\Component;
use App\Actions\Admin\BuildAdminDashboardAction;

new class extends Component {
    protected BuildAdminDashboardAction $builder;

    public function mount(): void
    {
        $this->builder = app(BuildAdminDashboardAction::class);
    }

    public function getDataProperty()
    {
        return $this->builder->execute();
    }

    public function getVerificationStatsProperty()
    {
        return $this->data['verification'];
    }

    public function getSafetyStatsProperty()
    {
        return array_merge($this->data['safety'], ['critical_reports' => 0]);
    }

    public function getEngagementStatsProperty()
    {
        return $this->data['engagement'];
    }

    public function getCommunityStatsProperty()
    {
        return $this->data['community'];
    }

    public function getRecentAuditProperty()
    {
        return collect($this->data['recent_audit'])->map(fn ($log) => [
            'action' => $log->action ?? ($log->action_key ?? null),
            'actor_name' => $log->actor?->name ?? ($log->actor_type === 'system' ? 'System' : 'Unknown'),
            'target' => isset($log->target_type, $log->target_id) ? "{$log->target_type}: {$log->target_id}" : null,
            'created_at' => $log->created_at ?? now(),
        ])->all();
    }
};
?>

<div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h1 class="text-3xl font-bold text-ue-text">Tổng quan quản trị</h1>
                    <p class="mt-2 text-sm text-ue-text-muted">Trung tâm điều hành và kiểm duyệt thông tin của UEConnect</p>
                </div>
                <div class="text-sm text-ue-text-muted">
                    {{ now()->format('H:i:s') }}
                </div>
            </div>

            <!-- Trust & Identity Section -->
            <div class="mb-8">
                <h2 class="text-lg font-semibold text-ue-text mb-4">Tin cậy & Định danh</h2>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <!-- Pending Verification -->
                    <x-ui.card class="bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-900/20 dark:to-blue-800/20 border-blue-200 dark:border-blue-800">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs font-semibold text-blue-600 dark:text-blue-400 uppercase">Chờ duyệt (Mới)</p>
                                <p class="mt-2 text-3xl font-bold text-blue-900 dark:text-blue-100">{{ $this->verificationStats['pending'] }}</p>
                            </div>
                            <div class="p-3 bg-blue-200 dark:bg-blue-900/50 rounded-lg">
                                <x-ui.icon name="clipboard-check" size="lg" class="text-blue-600 dark:text-blue-400" />
                            </div>
                        </div>
                        <a href="{{ route('admin.verifications.queue') }}?status=pending_review" class="mt-4 text-xs font-semibold text-blue-600 dark:text-blue-400 hover:underline flex items-center gap-1">
                            Xem danh sách
                            <x-ui.icon name="arrow-right" size="xs" />
                        </a>
                    </x-ui.card>

                    <!-- Needs More Info -->
                    <x-ui.card class="bg-gradient-to-br from-amber-50 to-amber-100 dark:from-amber-900/20 dark:to-amber-800/20 border-amber-200 dark:border-amber-800">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs font-semibold text-amber-600 dark:text-amber-400 uppercase">Cần thêm thông tin</p>
                                <p class="mt-2 text-3xl font-bold text-amber-900 dark:text-amber-100">{{ $this->verificationStats['needs_info'] }}</p>
                            </div>
                            <div class="p-3 bg-amber-200 dark:bg-amber-900/50 rounded-lg">
                                <x-ui.icon name="alert-circle" size="lg" class="text-amber-600 dark:text-amber-400" />
                            </div>
                        </div>
                        <a href="{{ route('admin.verifications.queue') }}?status=needs_more_information" class="mt-4 text-xs font-semibold text-amber-600 dark:text-amber-400 hover:underline flex items-center gap-1">
                            Xem danh sách
                            <x-ui.icon name="arrow-right" size="xs" />
                        </a>
                    </x-ui.card>

                    <!-- Conflicts -->
                    <x-ui.card class="bg-gradient-to-br from-red-50 to-red-100 dark:from-red-900/20 dark:to-red-800/20 border-red-200 dark:border-red-800">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs font-semibold text-red-600 dark:text-red-400 uppercase">Xung đột MSSV</p>
                                <p class="mt-2 text-3xl font-bold text-red-900 dark:text-red-100">{{ $this->verificationStats['conflicts'] }}</p>
                            </div>
                            <div class="p-3 bg-red-200 dark:bg-red-900/50 rounded-lg">
                                <x-ui.icon name="alert-triangle" size="lg" class="text-red-600 dark:text-red-400" />
                            </div>
                        </div>
                        <a href="{{ route('admin.verifications.queue') }}?status=conflict" class="mt-4 text-xs font-semibold text-red-600 dark:text-red-400 hover:underline flex items-center gap-1">
                            Xem danh sách
                            <x-ui.icon name="arrow-right" size="xs" />
                        </a>
                    </x-ui.card>

                    <!-- Approved Today -->
                    <x-ui.card class="bg-gradient-to-br from-green-50 to-green-100 dark:from-green-900/20 dark:to-green-800/20 border-green-200 dark:border-green-800">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs font-semibold text-green-600 dark:text-green-400 uppercase">Phê duyệt hôm nay</p>
                                <p class="mt-2 text-3xl font-bold text-green-900 dark:text-green-100">{{ $this->verificationStats['approved_today'] }}</p>
                            </div>
                            <div class="p-3 bg-green-200 dark:bg-green-900/50 rounded-lg">
                                <x-ui.icon name="check-circle" size="lg" class="text-green-600 dark:text-green-400" />
                            </div>
                        </div>
                    </x-ui.card>
                </div>
            </div>

            <!-- Safety & Moderation Section -->
            <div class="mb-8">
                <h2 class="text-lg font-semibold text-ue-text mb-4">An toàn & Kiểm duyệt</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- Pending Reports -->
                    <x-ui.card class="bg-gradient-to-br from-orange-50 to-orange-100 dark:from-orange-900/20 dark:to-orange-800/20 border-orange-200 dark:border-orange-800">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs font-semibold text-orange-600 dark:text-orange-400 uppercase">Báo cáo chờ xử lý</p>
                                <p class="mt-2 text-3xl font-bold text-orange-900 dark:text-orange-100">{{ $this->safetyStats['pending_reports'] }}</p>
                            </div>
                            <div class="p-3 bg-orange-200 dark:bg-orange-900/50 rounded-lg">
                                <x-ui.icon name="flag" size="lg" class="text-orange-600 dark:text-orange-400" />
                            </div>
                        </div>
                        <a href="{{ route('admin.reports.index') }}" class="mt-4 text-xs font-semibold text-orange-600 dark:text-orange-400 hover:underline flex items-center gap-1">
                            Xem danh sách
                            <x-ui.icon name="arrow-right" size="xs" />
                        </a>
                    </x-ui.card>

                    <!-- Critical Reports -->
                    <x-ui.card class="bg-gradient-to-br from-rose-50 to-rose-100 dark:from-rose-900/20 dark:to-rose-800/20 border-rose-200 dark:border-rose-800">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs font-semibold text-rose-600 dark:text-rose-400 uppercase">Báo cáo nghiêm trọng</p>
                                <p class="mt-2 text-3xl font-bold text-rose-900 dark:text-rose-100">{{ $this->safetyStats['critical_reports'] }}</p>
                            </div>
                            <div class="p-3 bg-rose-200 dark:bg-rose-900/50 rounded-lg">
                                <x-ui.icon name="alert-circle" size="lg" class="text-rose-600 dark:text-rose-400" />
                            </div>
                        </div>
                        <a href="{{ route('admin.reports.index') }}" class="mt-4 text-xs font-semibold text-rose-600 dark:text-rose-400 hover:underline flex items-center gap-1">
                            Xem danh sách
                            <x-ui.icon name="arrow-right" size="xs" />
                        </a>
                    </x-ui.card>

                    <!-- Suspended Users -->
                    <x-ui.card class="bg-gradient-to-br from-slate-50 to-slate-100 dark:from-slate-900/20 dark:to-slate-800/20 border-slate-200 dark:border-slate-800">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase">Tài khoản bị tạm khóa</p>
                                <p class="mt-2 text-3xl font-bold text-slate-900 dark:text-slate-100">{{ $this->safetyStats['suspended_users'] }}</p>
                            </div>
                            <div class="p-3 bg-slate-200 dark:bg-slate-900/50 rounded-lg">
                                <x-ui.icon name="lock" size="lg" class="text-slate-600 dark:text-slate-400" />
                            </div>
                        </div>
                    </x-ui.card>
                </div>
            </div>

            <!-- Engagement Section -->
            <div class="mb-8">
                <h2 class="text-lg font-semibold text-ue-text mb-4">Hoạt động & Giao tiếp</h2>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <x-ui.card>
                        <p class="text-xs font-semibold text-ue-text-muted uppercase">Bài viết hôm nay</p>
                        <p class="mt-2 text-3xl font-bold text-ue-text">{{ $this->engagementStats['daily_posts'] }}</p>
                    </x-ui.card>

                    <x-ui.card>
                        <p class="text-xs font-semibold text-ue-text-muted uppercase">Bình luận hôm nay</p>
                        <p class="mt-2 text-3xl font-bold text-ue-text">{{ $this->engagementStats['daily_comments'] }}</p>
                    </x-ui.card>

                    <x-ui.card>
                        <p class="text-xs font-semibold text-ue-text-muted uppercase">Lời chào hôm nay</p>
                        <p class="mt-2 text-3xl font-bold text-ue-text">{{ $this->engagementStats['daily_greetings'] }}</p>
                    </x-ui.card>

                    <x-ui.card class="bg-gradient-to-br from-purple-50 to-purple-100 dark:from-purple-900/20 dark:to-purple-800/20 border-purple-200 dark:border-purple-800">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs font-semibold text-purple-600 dark:text-purple-400 uppercase">Yêu cầu mentor</p>
                                <p class="mt-2 text-3xl font-bold text-purple-900 dark:text-purple-100">{{ $this->engagementStats['mentor_requests_pending'] }}</p>
                            </div>
                            <div class="p-3 bg-purple-200 dark:bg-purple-900/50 rounded-lg">
                                <x-ui.icon name="user-check" size="lg" class="text-purple-600 dark:text-purple-400" />
                            </div>
                        </div>
                    </x-ui.card>
                </div>
            </div>

            <!-- Community & Communications Section -->
            <div class="mb-8">
                <h2 class="text-lg font-semibold text-ue-text mb-4">Cộng đồng & Truyền thông</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <x-ui.card class="bg-gradient-to-br from-teal-50 to-teal-100 dark:from-teal-900/20 dark:to-teal-800/20 border-teal-200 dark:border-teal-800">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs font-semibold text-teal-600 dark:text-teal-400 uppercase">Cộng đồng hoạt động</p>
                                <p class="mt-2 text-3xl font-bold text-teal-900 dark:text-teal-100">{{ $this->communityStats['active_communities'] }}</p>
                            </div>
                            <div class="p-3 bg-teal-200 dark:bg-teal-900/50 rounded-lg">
                                <x-ui.icon name="users" size="lg" class="text-teal-600 dark:text-teal-400" />
                            </div>
                        </div>
                    </x-ui.card>

                    <x-ui.card class="bg-gradient-to-br from-indigo-50 to-indigo-100 dark:from-indigo-900/20 dark:to-indigo-800/20 border-indigo-200 dark:border-indigo-800">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 uppercase">Thông báo hệ thống</p>
                                <p class="mt-2 text-3xl font-bold text-indigo-900 dark:text-indigo-100">{{ $this->communityStats['announcements_active'] }}</p>
                            </div>
                            <div class="p-3 bg-indigo-200 dark:bg-indigo-900/50 rounded-lg">
                                <x-ui.icon name="megaphone" size="lg" class="text-indigo-600 dark:text-indigo-400" />
                            </div>
                        </div>
                    </x-ui.card>

                    <x-ui.card class="bg-gradient-to-br from-fuchsia-50 to-fuchsia-100 dark:from-fuchsia-900/20 dark:to-fuchsia-800/20 border-fuchsia-200 dark:border-fuchsia-800">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs font-semibold text-fuchsia-600 dark:text-fuchsia-400 uppercase">Bài viết bị ẩn</p>
                                <p class="mt-2 text-3xl font-bold text-fuchsia-900 dark:text-fuchsia-100">{{ $this->safetyStats['auto_hidden_content'] }}</p>
                            </div>
                            <div class="p-3 bg-fuchsia-200 dark:bg-fuchsia-900/50 rounded-lg">
                                <x-ui.icon name="eye-off" size="lg" class="text-fuchsia-600 dark:text-fuchsia-400" />
                            </div>
                        </div>
                    </x-ui.card>
                </div>
            </div>

            <!-- Recent Admin Actions -->
            <div>
                <h2 class="text-lg font-semibold text-ue-text mb-4">Hành động gần đây</h2>
                <x-ui.card padding="none" class="overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-ue-border">
                            <thead class="bg-ue-surface-subtle">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-ue-text-muted uppercase">Hành động</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-ue-text-muted uppercase">Admin</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-ue-text-muted uppercase">Mục tiêu</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-ue-text-muted uppercase">Thời gian</th>
                                </tr>
                            </thead>
                            <tbody class="bg-ue-surface divide-y divide-ue-border text-sm">
                                @forelse ($this->recentAudit as $audit)
                                    <tr class="hover:bg-ue-surface-hover transition-colors">
                                        <td class="px-6 py-3">
                                            <span class="px-3 py-1 rounded-full text-xs font-semibold bg-ue-brand-soft text-ue-brand">
                                                {{ ucfirst(str_replace(['_', '-'], ' ', $audit['action'])) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-3 text-ue-text">{{ $audit['actor_name'] }}</td>
                                        <td class="px-6 py-3 text-ue-text-muted">{{ $audit['target'] }}</td>
                                        <td class="px-6 py-3 text-ue-text-muted text-xs">{{ $audit['created_at']->diffForHumans() }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-6 py-8 text-center text-ue-text-muted">Không có hành động gần đây</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </x-ui.card>
            </div>
        </div>
    </div>
</div>
