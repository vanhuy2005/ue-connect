<?php

use Livewire\Component;
use App\Actions\Admin\BuildAdminDashboardAction;

new class extends Component {
    public function getDataProperty(): array
    {
        return app(BuildAdminDashboardAction::class)->execute();
    }
};
?>

<div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    {{-- Header --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8 pb-5 border-b border-ue-border/80">
        <div>
            <h1 class="text-2xl md:text-3xl font-bold text-ue-text tracking-tight">Tổng quan quản trị</h1>
            <p class="mt-1 text-xs md:text-sm text-ue-text-muted">Trung tâm điều hành và kiểm duyệt thông tin của UEConnect</p>
        </div>
        <div class="mt-4 md:mt-0 flex flex-col md:items-end text-xs text-ue-text-muted/80 font-medium gap-1">
            <div>Môi trường: <span class="font-semibold text-ue-text capitalize">{{ app()->environment() }}</span></div>
            <div>Cập nhật: <span class="font-semibold text-ue-text">{{ now()->format('H:i:s d/m/Y') }}</span></div>
        </div>
    </div>

    @php
        $data = $this->data;
        $snapshot = $data['snapshot'];
        $priorityQueue = $data['priority_queue'];
        $systemHealth = $data['system_health'];
        $recentActivity = $data['recent_activity'];
        $trends = $data['trends'];

        $getSeverityClasses = function($level) {
            return match($level) {
                'info' => [
                    'badgeBg' => 'bg-blue-50 text-blue-600 dark:bg-blue-950/40 dark:text-blue-400',
                    'icon' => 'blue',
                ],
                'warning' => [
                    'badgeBg' => 'bg-amber-50 text-amber-600 dark:bg-amber-950/40 dark:text-amber-400',
                    'icon' => 'amber',
                ],
                'critical' => [
                    'badgeBg' => 'bg-rose-50 text-rose-600 dark:bg-rose-950/40 dark:text-rose-400',
                    'icon' => 'rose',
                ],
                'success' => [
                    'badgeBg' => 'bg-emerald-50 text-emerald-600 dark:bg-emerald-950/40 dark:text-emerald-400',
                    'icon' => 'emerald',
                ],
                default => [
                    'badgeBg' => 'bg-slate-50 text-slate-600 dark:bg-slate-800 dark:text-slate-400',
                    'icon' => 'slate',
                ],
            };
        };
    @endphp

    {{-- Section 1: Operations Snapshot --}}
    <div class="mb-10">
        <h2 class="text-sm font-bold text-ue-text-muted/85 uppercase tracking-wider mb-4">Tình trạng vận hành</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
            {{-- Pending Verification --}}
            @php
                $vLevel = $snapshot['pending_verification'] > 0 ? 'info' : 'neutral';
                $vClasses = $getSeverityClasses($vLevel);
            @endphp
            <x-ui.card padding="none" class="bg-white dark:bg-slate-900 border border-ue-border hover:shadow-md transition-shadow p-6 flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-ue-text-muted uppercase">Chờ duyệt xác thực</span>
                        <div class="p-2 rounded-xl {{ $vClasses['badgeBg'] }}">
                            <x-ui.icon name="clipboard-check" size="md" />
                        </div>
                    </div>
                    <div class="mt-4">
                        <span class="text-3xl font-extrabold text-ue-text tracking-tight">{{ $snapshot['pending_verification'] }}</span>
                        <p class="text-xs text-ue-text-muted mt-1">Hồ sơ sinh viên cần xem xét</p>
                    </div>
                </div>
                @if($snapshot['pending_verification'] > 0)
                    <div class="mt-5 pt-3 border-t border-ue-border/60 flex justify-end">
                        <a href="{{ route('admin.verifications.queue') }}?status=pending_review" class="text-xs font-bold text-ue-brand-active hover:underline flex items-center gap-1">
                            Xem hàng đợi
                            <x-ui.icon name="arrow-right" size="xs" />
                        </a>
                    </div>
                @endif
            </x-ui.card>

            {{-- Open Reports --}}
            @php
                $rLevel = $snapshot['open_reports'] > 0 ? 'warning' : 'neutral';
                $rClasses = $getSeverityClasses($rLevel);
            @endphp
            <x-ui.card padding="none" class="bg-white dark:bg-slate-900 border border-ue-border hover:shadow-md transition-shadow p-6 flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-ue-text-muted uppercase">Báo cáo đang mở</span>
                        <div class="p-2 rounded-xl {{ $rClasses['badgeBg'] }}">
                            <x-ui.icon name="flag" size="md" />
                        </div>
                    </div>
                    <div class="mt-4">
                        <span class="text-3xl font-extrabold text-ue-text tracking-tight">{{ $snapshot['open_reports'] }}</span>
                        <p class="text-xs text-ue-text-muted mt-1">Báo cáo vi phạm chưa xử lý</p>
                    </div>
                </div>
                @if($snapshot['open_reports'] > 0)
                    <div class="mt-5 pt-3 border-t border-ue-border/60 flex justify-end">
                        <a href="{{ route('admin.reports.index') }}" class="text-xs font-bold text-ue-brand-active hover:underline flex items-center gap-1">
                            Xử lý báo cáo
                            <x-ui.icon name="arrow-right" size="xs" />
                        </a>
                    </div>
                @endif
            </x-ui.card>

            {{-- Critical Reports / Conflicts --}}
            @php
                $cLevel = $snapshot['critical_reports'] > 0 ? 'critical' : 'neutral';
                $cClasses = $getSeverityClasses($cLevel);
            @endphp
            <x-ui.card padding="none" class="bg-white dark:bg-slate-900 border {{ $snapshot['critical_reports'] > 0 ? 'border-rose-200 dark:border-rose-950' : 'border-ue-border' }} hover:shadow-md transition-shadow p-6 flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-ue-text-muted uppercase">Vấn đề nghiêm trọng</span>
                        <div class="p-2 rounded-xl {{ $cClasses['badgeBg'] }}">
                            <x-ui.icon name="alert-triangle" size="md" />
                        </div>
                    </div>
                    <div class="mt-4">
                        <span class="text-3xl font-extrabold text-ue-text tracking-tight">{{ $snapshot['critical_reports'] }}</span>
                        <p class="text-xs text-ue-text-muted mt-1">Yêu cầu trùng lặp/xung đột MSSV</p>
                    </div>
                </div>
                @if($snapshot['critical_reports'] > 0)
                    <div class="mt-5 pt-3 border-t border-ue-border/60 flex justify-end">
                        <a href="{{ route('admin.verifications.queue') }}?status=conflict" class="text-xs font-bold text-red-600 dark:text-red-400 hover:underline flex items-center gap-1">
                            Giải quyết xung đột
                            <x-ui.icon name="arrow-right" size="xs" />
                        </a>
                    </div>
                @endif
            </x-ui.card>

            {{-- Pending Moderation (Hidden Content) --}}
            @php
                $mLevel = $snapshot['pending_moderation'] > 0 ? 'warning' : 'neutral';
                $mClasses = $getSeverityClasses($mLevel);
            @endphp
            <x-ui.card padding="none" class="bg-white dark:bg-slate-900 border border-ue-border hover:shadow-md transition-shadow p-6 flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-ue-text-muted uppercase">Bài viết chờ duyệt</span>
                        <div class="p-2 rounded-xl {{ $mClasses['badgeBg'] }}">
                            <x-ui.icon name="eye-off" size="md" />
                        </div>
                    </div>
                    <div class="mt-4">
                        <span class="text-3xl font-extrabold text-ue-text tracking-tight">{{ $snapshot['pending_moderation'] }}</span>
                        <p class="text-xs text-ue-text-muted mt-1">Bài viết/bình luận bị hệ thống ẩn</p>
                    </div>
                </div>
                @if($snapshot['pending_moderation'] > 0)
                    <div class="mt-5 pt-3 border-t border-ue-border/60 flex justify-end">
                        <a href="{{ route('admin.moderation.index') }}" class="text-xs font-bold text-ue-brand-active hover:underline flex items-center gap-1">
                            Mở kiểm duyệt
                            <x-ui.icon name="arrow-right" size="xs" />
                        </a>
                    </div>
                @endif
            </x-ui.card>

            {{-- Restricted Users --}}
            @php
                $uClasses = $getSeverityClasses('neutral');
            @endphp
            <x-ui.card padding="none" class="bg-white dark:bg-slate-900 border border-ue-border hover:shadow-md transition-shadow p-6 flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-ue-text-muted uppercase">Tài khoản bị hạn chế</span>
                        <div class="p-2 rounded-xl {{ $uClasses['badgeBg'] }}">
                            <x-ui.icon name="lock" size="md" />
                        </div>
                    </div>
                    <div class="mt-4">
                        <span class="text-3xl font-extrabold text-ue-text tracking-tight">{{ $snapshot['restricted_users'] }}</span>
                        <p class="text-xs text-ue-text-muted mt-1">Tài khoản đang bị khóa/đình chỉ</p>
                    </div>
                </div>
                @if($snapshot['restricted_users'] > 0)
                    <div class="mt-5 pt-3 border-t border-ue-border/60 flex justify-end">
                        <a href="{{ route('admin.users.index') }}" class="text-xs font-bold text-ue-brand-active hover:underline flex items-center gap-1">
                            Xem danh sách
                            <x-ui.icon name="arrow-right" size="xs" />
                        </a>
                    </div>
                @endif
            </x-ui.card>

            {{-- Media Storage Usage --}}
            @php
                $medLevel = $snapshot['media_usage_warning_level'];
                $medClasses = $getSeverityClasses($medLevel);
            @endphp
            <x-ui.card padding="none" class="bg-white dark:bg-slate-900 border {{ $medLevel === 'critical' ? 'border-rose-200 dark:border-rose-950' : ($medLevel === 'warning' ? 'border-amber-200 dark:border-amber-950' : 'border-ue-border') }} hover:shadow-md transition-shadow p-6 flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-ue-text-muted uppercase">Dung lượng Media</span>
                        <div class="p-2 rounded-xl {{ $medClasses['badgeBg'] }}">
                            <x-ui.icon name="image" size="md" />
                        </div>
                    </div>
                    <div class="mt-4">
                        <span class="text-3xl font-extrabold text-ue-text tracking-tight">
                            @if(\Illuminate\Support\Facades\Schema::hasTable('media'))
                                {{ $snapshot['media_usage_percent'] }}%
                            @else
                                N/A
                            @endif
                        </span>
                        <p class="text-xs text-ue-text-muted mt-1">
                            @if(\Illuminate\Support\Facades\Schema::hasTable('media'))
                                {{ number_format($snapshot['media_total_files']) }} tệp · {{ round($snapshot['media_usage_bytes'] / 1024 / 1024, 1) }} MB
                            @else
                                Chưa kích hoạt bảng media
                            @endif
                        </p>
                    </div>
                </div>
                <div class="mt-5 pt-3 border-t border-ue-border/60 flex justify-end">
                    <a href="{{ route('admin.media.usage') }}" class="text-xs font-bold text-ue-brand-active hover:underline flex items-center gap-1">
                        Xem chi tiết
                        <x-ui.icon name="arrow-right" size="xs" />
                    </a>
                </div>
            </x-ui.card>
        </div>
    </div>

    {{-- Section 2: Priority Queue & System Health --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-10">
        {{-- Priority Queue --}}
        <div class="min-w-0">
            <h2 class="text-sm font-bold text-ue-text-muted/85 uppercase tracking-wider mb-4">Việc cần xử lý gấp</h2>
            <x-ui.card padding="none" class="bg-white dark:bg-slate-900 border border-ue-border shadow-sm overflow-hidden h-full flex flex-col justify-between">
                <div class="divide-y divide-ue-border">
                    @forelse($priorityQueue as $item)
                        @php
                            $sevClasses = match($item['severity']) {
                                'critical' => 'bg-rose-500 text-rose-500',
                                'warning' => 'bg-amber-500 text-amber-500',
                                'info' => 'bg-blue-500 text-blue-500',
                                default => 'bg-slate-400 text-slate-400',
                            };
                        @endphp
                        <div class="p-4 flex items-center justify-between gap-4 hover:bg-ue-surface-hover/30 transition-colors">
                            <div class="flex items-center gap-3 min-w-0">
                                <span class="w-2 h-2 rounded-full flex-shrink-0 {{ $sevClasses }}" aria-hidden="true"></span>
                                <div class="min-w-0">
                                    <div class="flex items-center gap-2">
                                        <span class="px-1.5 py-0.5 rounded text-[10px] font-bold uppercase border border-ue-border/60 bg-ue-surface-subtle text-ue-text-muted">{{ $item['type'] }}</span>
                                        <span class="text-xxs text-ue-text-muted">{{ $item['created_at'] instanceof \Carbon\Carbon ? $item['created_at']->diffForHumans() : \Carbon\Carbon::parse($item['created_at'])->diffForHumans() }}</span>
                                    </div>
                                    <p class="text-xs font-semibold text-ue-text truncate mt-1">{{ $item['title'] }}</p>
                                </div>
                            </div>
                            <a href="{{ $item['cta_url'] }}" class="flex-shrink-0 inline-flex items-center gap-0.5 text-xs font-bold text-ue-brand-active hover:underline">
                                {{ $item['cta_label'] }}
                                <x-ui.icon name="chevron-right" size="xs" />
                            </a>
                        </div>
                    @empty
                        <div class="py-12 px-4 text-center">
                            <x-ui.icon name="check-circle" size="lg" class="text-emerald-500 mx-auto mb-3" />
                            <p class="text-sm font-semibold text-ue-text">Không có việc khẩn cấp</p>
                            <p class="text-xs text-ue-text-muted mt-1">Hệ thống hiện không có hàng đợi ưu tiên cần xử lý.</p>
                        </div>
                    @endforelse
                </div>
            </x-ui.card>
        </div>

        {{-- System Health --}}
        <div class="min-w-0">
            <h2 class="text-sm font-bold text-ue-text-muted/85 uppercase tracking-wider mb-4">Tình trạng hệ thống</h2>
            <x-ui.card padding="none" class="bg-white dark:bg-slate-900 border border-ue-border shadow-sm p-4 h-full">
                <div class="flex flex-col gap-4">
                    @foreach($systemHealth as $service)
                        @php
                            $statusIndicator = match($service['status']) {
                                'healthy' => 'bg-emerald-500',
                                'degraded' => 'bg-amber-500',
                                'down' => 'bg-rose-500',
                                'disabled' => 'bg-slate-350',
                                default => 'bg-slate-400',
                            };
                            $statusText = match($service['status']) {
                                'healthy' => 'Hoạt động',
                                'degraded' => 'Sự cố nhẹ',
                                'down' => 'Ngoại tuyến',
                                'disabled' => 'Chưa kích hoạt',
                                default => 'Không rõ',
                            };
                        @endphp
                        <div class="flex items-start gap-3 p-3 rounded-xl hover:bg-ue-surface-hover/30 transition-colors border border-ue-border/40">
                            <div class="mt-1 flex-shrink-0 flex items-center justify-center">
                                <span class="w-2.5 h-2.5 rounded-full {{ $statusIndicator }} inline-block" aria-hidden="true"></span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between gap-2">
                                    <span class="text-xs font-bold text-ue-text">{{ $service['name'] }}</span>
                                    <span class="text-[10px] font-bold px-1.5 py-0.5 rounded uppercase {{ $service['status'] === 'healthy' ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/30 dark:text-emerald-400' : ($service['status'] === 'degraded' ? 'bg-amber-50 text-amber-700 dark:bg-amber-950/30' : 'bg-slate-50 text-slate-600') }}">{{ $statusText }}</span>
                                </div>
                                <p class="text-xxs text-ue-text-muted mt-1 leading-normal">{{ $service['message'] }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </x-ui.card>
        </div>
    </div>

    {{-- Section 3: Trends & Recent Activity --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        {{-- Trends --}}
        <div class="lg:col-span-1 min-w-0">
            <h2 class="text-sm font-bold text-ue-text-muted/85 uppercase tracking-wider mb-4">Hoạt động 7 ngày gần đây</h2>
            <x-ui.card padding="none" class="bg-white dark:bg-slate-900 border border-ue-border shadow-sm p-5 space-y-4">
                <div class="flex items-center justify-between border-b border-ue-border/60 pb-3">
                    <span class="text-xs font-semibold text-ue-text">Thành viên mới</span>
                    <span class="text-sm font-bold text-ue-text">{{ number_format($trends['new_users']) }}</span>
                </div>
                <div class="flex items-center justify-between border-b border-ue-border/60 pb-3">
                    <span class="text-xs font-semibold text-ue-text">Bài viết mới</span>
                    <span class="text-sm font-bold text-ue-text">{{ number_format($trends['posts']) }}</span>
                </div>
                <div class="flex items-center justify-between border-b border-ue-border/60 pb-3">
                    <span class="text-xs font-semibold text-ue-text">Bình luận mới</span>
                    <span class="text-sm font-bold text-ue-text">{{ number_format($trends['comments']) }}</span>
                </div>
                <div class="flex items-center justify-between border-b border-ue-border/60 pb-3">
                    <span class="text-xs font-semibold text-ue-text">Báo cáo vi phạm</span>
                    <span class="text-sm font-bold text-ue-text">{{ number_format($trends['reports']) }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-ue-text">Đã duyệt xác thực</span>
                    <span class="text-sm font-bold text-ue-text">{{ number_format($trends['verifications_approved']) }}</span>
                </div>
            </x-ui.card>
        </div>

        {{-- Recent Admin Activity --}}
        <div class="lg:col-span-2 min-w-0">
            <h2 class="text-sm font-bold text-ue-text-muted/85 uppercase tracking-wider mb-4">Hoạt động quản trị gần đây</h2>
            <x-ui.card padding="none" class="bg-white dark:bg-slate-900 border border-ue-border shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-ue-border">
                        <thead class="bg-ue-surface-subtle">
                            <tr>
                                <th scope="col" class="px-5 py-3 text-left text-xxs font-bold text-ue-text-muted uppercase tracking-wider">Hành động</th>
                                <th scope="col" class="px-5 py-3 text-left text-xxs font-bold text-ue-text-muted uppercase tracking-wider">Quản trị viên</th>
                                <th scope="col" class="px-5 py-3 text-left text-xxs font-bold text-ue-text-muted uppercase tracking-wider">Chi tiết mục tiêu</th>
                                <th scope="col" class="px-5 py-3 text-left text-xxs font-bold text-ue-text-muted uppercase tracking-wider">Thời gian</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-ue-border text-xs font-medium">
                            @forelse($recentActivity as $log)
                                @php
                                    $actionName = $log->action ?? ($log->action_key ?? 'Hành động khác');
                                    $actorName = $log->actor?->name ?? ($log->actor_type === 'system' ? 'Hệ thống' : 'Quản trị viên');
                                    $targetInfo = isset($log->target_type, $log->target_id) ? "{$log->target_type} #{$log->target_id}" : 'N/A';
                                @endphp
                                <tr class="hover:bg-ue-surface-hover/30 transition-colors">
                                    <td class="px-5 py-4">
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold border border-ue-border/80 bg-ue-surface-subtle text-ue-text">
                                            {{ ucfirst(str_replace(['_', '-'], ' ', $actionName)) }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-4 text-ue-text font-semibold">{{ $actorName }}</td>
                                    <td class="px-5 py-4 text-ue-text-muted">{{ $targetInfo }}</td>
                                    <td class="px-5 py-4 text-ue-text-muted text-xxs">
                                        {{ $log->created_at instanceof \Carbon\Carbon ? $log->created_at->diffForHumans() : \Carbon\Carbon::parse($log->created_at)->diffForHumans() }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-5 py-8 text-center text-ue-text-muted text-xs">Chưa có hoạt động quản trị nào.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-ui.card>
        </div>
    </div>
</div>
