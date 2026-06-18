<?php

use Livewire\Volt\Component;
use App\Actions\Admin\BuildAdminDashboardAction;

new class extends Component {
    public function with(): array
    {
        return [
            'data' => app(BuildAdminDashboardAction::class)->execute(),
        ];
    }
};
?>

@php
    $snapshot      = $data['snapshot'];
    $priorityQueue = $data['priority_queue'];
    $systemHealth  = $data['system_health'];
    $recentActivity = $data['recent_activity'];
    $trends        = $data['trends'];

    // ── Overall system health status ──────────────────────────────────────
    $hasDown     = collect($systemHealth)->contains('status', 'down');
    $hasDegraded = collect($systemHealth)->contains('status', 'degraded');
    $overallStatus = $hasDown ? 'critical' : ($hasDegraded ? 'degraded' : 'healthy');

    // ── Humanize audit action keys ─────────────────────────────────────────
    $actionMap = [
        'admin.evidence.preview'               => 'Admin đã xem minh chứng xác thực',
        'admin.evidence.download'              => 'Admin đã tải minh chứng xác thực',
        'verification.start_review'            => 'Bắt đầu xét duyệt hồ sơ xác thực',
        'verification.start review'            => 'Bắt đầu xét duyệt hồ sơ xác thực',
        'verification.ai_analysis_completed'   => 'AI đã hoàn tất phân tích hồ sơ',
        'verification.ai analysis completed'   => 'AI đã hoàn tất phân tích hồ sơ',
        'verification.approved'                => 'Đã phê duyệt xác thực sinh viên',
        'verification.rejected'                => 'Đã từ chối yêu cầu xác thực',
        'verification.needs_more_info'         => 'Yêu cầu bổ sung thông tin xác thực',
        'verification.conflict_flagged'        => 'Gắn cờ xung đột MSSV',
        'verification.conflict_resolved'       => 'Đã giải quyết xung đột MSSV',
        'report.reviewed'                      => 'Đã xử lý báo cáo vi phạm',
        'report.dismissed'                     => 'Đã bỏ qua báo cáo',
        'user.restricted'                      => 'Đã hạn chế tài khoản người dùng',
        'user.suspended'                       => 'Đã đình chỉ tài khoản người dùng',
        'user.reactivated'                     => 'Đã kích hoạt lại tài khoản',
        'post.hidden'                          => 'Đã ẩn bài viết vi phạm',
        'post.restored'                        => 'Đã khôi phục bài viết',
        'media.deleted'                        => 'Đã xoá tệp media',
        'media.quarantined'                    => 'Đã cách ly tệp media',
        'admin.login'                          => 'Quản trị viên đăng nhập',
        'admin.logout'                         => 'Quản trị viên đăng xuất',
        'system.cache_cleared'                 => 'Hệ thống đã xoá cache',
        'system.queue_restarted'               => 'Hệ thống đã khởi động lại queue',
        'announcement.published'               => 'Đã đăng thông báo mới',
        'community.suspended'                  => 'Đã đình chỉ cộng đồng',
        'community.reactivated'                => 'Đã khôi phục cộng đồng',
        'test_key'                             => 'Test key',
    ];

    /**
     * Humanize a target type string from the audit log.
     * e.g. "verification_evidence" → "Minh chứng xác thực"
     */
    $targetTypeMap = [
        'verification_evidence'   => 'Minh chứng xác thực',
        'verification_request'    => 'Yêu cầu xác thực',
        'verification_requests'   => 'Yêu cầu xác thực',
        'report'                  => 'Báo cáo',
        'reports'                 => 'Báo cáo',
        'user'                    => 'Người dùng',
        'users'                   => 'Người dùng',
        'post'                    => 'Bài viết',
        'posts'                   => 'Bài viết',
        'media'                   => 'Tệp media',
        'community'               => 'Cộng đồng',
        'announcement'            => 'Thông báo',
    ];
@endphp

<div class="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6">

    {{-- ─── 1. HEADER ────────────────────────────────────────────────────────── --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3
                motion-safe:animate-in motion-safe:fade-in motion-safe:slide-in-from-bottom-2 motion-safe:duration-200">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 leading-tight">Trung tâm quản trị</h1>
            <p class="text-sm text-slate-500 mt-0.5">Theo dõi kiểm duyệt, xác thực và tình trạng hệ thống</p>
        </div>

        <div class="flex flex-wrap items-center gap-2 text-xs">
            {{-- Environment badge --}}
            <span class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-md font-bold uppercase tracking-wide
                         bg-slate-100 text-slate-600 border border-slate-200">
                <span class="w-1.5 h-1.5 rounded-full bg-slate-400 inline-block"></span>
                {{ app()->environment() }}
            </span>

            {{-- Last updated --}}
            <span class="text-slate-400 font-medium">Cập nhật {{ now()->format('H:i · d/m/Y') }}</span>

            {{-- Overall status pill --}}
            @if($overallStatus === 'critical')
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full font-bold text-red-700 bg-red-50 border border-red-200">
                    <span class="w-1.5 h-1.5 rounded-full bg-red-500 inline-block animate-pulse"></span>
                    Có sự cố
                </span>
            @elseif($overallStatus === 'degraded')
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full font-bold text-amber-700 bg-amber-50 border border-amber-200">
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500 inline-block"></span>
                    Cần chú ý
                </span>
            @else
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full font-bold text-emerald-700 bg-emerald-50 border border-emerald-200">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 inline-block"></span>
                    Ổn định
                </span>
            @endif
        </div>
    </div>

    {{-- ─── 2. PRIORITY METRICS STRIP ─────────────────────────────────────────── --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-6 gap-3
                motion-safe:animate-in motion-safe:fade-in motion-safe:slide-in-from-bottom-2 motion-safe:duration-200 motion-safe:delay-75">

        {{-- Metric: Chờ duyệt xác thực --}}
        <a href="{{ route('admin.verifications.queue') }}?status=pending"
           class="group relative flex flex-col gap-1 rounded-xl border border-slate-200 bg-white px-4 py-3 shadow-sm
                  transition-all duration-150 ease-out hover:-translate-y-0.5 hover:border-ue-brand hover:shadow-md
                  focus:outline-none focus:ring-2 focus:ring-ue-brand/40 focus:ring-offset-1">
            <span class="text-xs font-semibold text-slate-500 leading-none">Chờ duyệt xác thực</span>
            <span class="text-2xl font-bold text-slate-900 leading-none mt-1">{{ $snapshot['pending_verification'] }}</span>
            <span class="text-[11px] text-ue-brand font-medium mt-auto pt-1
                         opacity-0 group-hover:opacity-100 transition-opacity duration-150">
                Xem hàng đợi →
            </span>
            @if($snapshot['pending_verification'] === 0)
                <span class="text-[11px] text-slate-400 font-medium mt-auto pt-1">Không có hồ sơ</span>
            @endif
        </a>

        {{-- Metric: Báo cáo đang mở --}}
        <a href="{{ route('admin.reports.index') }}"
           class="group relative flex flex-col gap-1 rounded-xl border border-slate-200 bg-white px-4 py-3 shadow-sm
                  transition-all duration-150 ease-out hover:-translate-y-0.5 hover:border-ue-brand hover:shadow-md
                  focus:outline-none focus:ring-2 focus:ring-ue-brand/40 focus:ring-offset-1">
            <span class="text-xs font-semibold text-slate-500 leading-none">Báo cáo đang mở</span>
            <span class="text-2xl font-bold text-slate-900 leading-none mt-1">{{ $snapshot['open_reports'] }}</span>
            <span class="text-[11px] text-ue-brand font-medium mt-auto pt-1
                         opacity-0 group-hover:opacity-100 transition-opacity duration-150">
                Xử lý →
            </span>
            @if($snapshot['open_reports'] === 0)
                <span class="text-[11px] text-slate-400 font-medium mt-auto pt-1">Không có báo cáo</span>
            @endif
        </a>

        {{-- Metric: Vấn đề nghiêm trọng --}}
        @if($snapshot['critical_reports'] > 0)
            <a href="{{ route('admin.verifications.queue') }}?status=conflict"
               class="group relative flex flex-col gap-1 rounded-xl border border-slate-200 bg-white px-4 py-3 shadow-sm
                      transition-all duration-150 ease-out hover:-translate-y-0.5 hover:border-red-400 hover:shadow-md
                      focus:outline-none focus:ring-2 focus:ring-red-400 focus:ring-offset-1">
                <span class="text-xs font-semibold text-red-600 leading-none">Vấn đề nghiêm trọng</span>
                <span class="text-2xl font-bold text-red-600 leading-none mt-1">{{ $snapshot['critical_reports'] }}</span>
                <span class="text-[11px] text-red-600 font-medium mt-auto pt-1
                             opacity-0 group-hover:opacity-100 transition-opacity duration-150">
                    Giải quyết ngay →
                </span>
            </a>
        @else
            <div class="flex flex-col gap-1 rounded-xl border border-slate-200 bg-white px-4 py-3 shadow-sm">
                <span class="text-xs font-semibold text-slate-500 leading-none">Vấn đề nghiêm trọng</span>
                <span class="text-2xl font-bold text-emerald-600 leading-none mt-1">0</span>
                <span class="text-[11px] text-slate-400 font-medium mt-auto pt-1">Không có sự cố</span>
            </div>
        @endif

        {{-- Metric: Bài viết bị ẩn --}}
        <a href="{{ route('admin.moderation.index') }}"
           class="group relative flex flex-col gap-1 rounded-xl border border-slate-200 bg-white px-4 py-3 shadow-sm
                  transition-all duration-150 ease-out hover:-translate-y-0.5 hover:border-ue-brand hover:shadow-md
                  focus:outline-none focus:ring-2 focus:ring-ue-brand/40 focus:ring-offset-1">
            <span class="text-xs font-semibold text-slate-500 leading-none">Bài viết bị ẩn</span>
            <span class="text-2xl font-bold text-slate-900 leading-none mt-1">{{ $snapshot['pending_moderation'] }}</span>
            <span class="text-[11px] text-ue-brand font-medium mt-auto pt-1
                         opacity-0 group-hover:opacity-100 transition-opacity duration-150">
                Kiểm duyệt →
            </span>
            @if($snapshot['pending_moderation'] === 0)
                <span class="text-[11px] text-slate-400 font-medium mt-auto pt-1">Không có bài</span>
            @endif
        </a>

        {{-- Metric: Tài khoản bị hạn chế --}}
        <a href="{{ route('admin.users.index') }}"
           class="group relative flex flex-col gap-1 rounded-xl border border-slate-200 bg-white px-4 py-3 shadow-sm
                  transition-all duration-150 ease-out hover:-translate-y-0.5 hover:border-ue-brand hover:shadow-md
                  focus:outline-none focus:ring-2 focus:ring-ue-brand/40 focus:ring-offset-1">
            <span class="text-xs font-semibold text-slate-500 leading-none">Tài khoản hạn chế</span>
            <span class="text-2xl font-bold text-slate-900 leading-none mt-1">{{ $snapshot['restricted_users'] }}</span>
            <span class="text-[11px] text-ue-brand font-medium mt-auto pt-1
                         opacity-0 group-hover:opacity-100 transition-opacity duration-150">
                Xem danh sách →
            </span>
            @if($snapshot['restricted_users'] === 0)
                <span class="text-[11px] text-slate-400 font-medium mt-auto pt-1">Không có tài khoản</span>
            @endif
        </a>

        {{-- Metric: Media --}}
        <a href="{{ route('admin.media.usage') }}"
           class="group relative flex flex-col gap-1 rounded-xl border border-slate-200 bg-white px-4 py-3 shadow-sm
                  transition-all duration-150 ease-out hover:-translate-y-0.5 hover:border-ue-brand hover:shadow-md
                  focus:outline-none focus:ring-2 focus:ring-ue-brand/40 focus:ring-offset-1">
            <span class="text-xs font-semibold text-slate-500 leading-none">Media</span>
            <span class="text-2xl font-bold text-slate-900 leading-none mt-1">
                @if(\Illuminate\Support\Facades\Schema::hasTable('media'))
                    {{ round($snapshot['media_usage_bytes'] / 1024 / 1024, 1) }} MB
                @else
                    N/A
                @endif
            </span>
            @if(\Illuminate\Support\Facades\Schema::hasTable('media'))
                <div class="w-full bg-slate-100 h-1 rounded-full overflow-hidden mt-1">
                    <div class="{{ $snapshot['media_usage_warning_level'] === 'critical' ? 'bg-red-500' : ($snapshot['media_usage_warning_level'] === 'warning' ? 'bg-amber-500' : 'bg-ue-brand') }} h-full rounded-full transition-all duration-300"
                         style="width: {{ $snapshot['media_usage_percent'] ?? 0 }}%"></div>
                </div>
                <span class="text-[11px] text-slate-400 mt-1">{{ number_format($snapshot['media_total_files']) }} tệp · {{ $snapshot['media_usage_percent'] }}% đã dùng</span>
            @else
                <span class="text-[11px] text-slate-400 mt-auto pt-1">Chưa cấu hình</span>
            @endif
        </a>
    </div>

    {{-- ─── 3. MAIN CONTENT GRID ───────────────────────────────────────────────── --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        {{-- ───────── LEFT / MAIN AREA (8 cols) ──────────────────────────────── --}}
        <div class="lg:col-span-8 space-y-6">

            {{-- A. TRIAGE QUEUE ────────────────────────────────────────────────── --}}
            <div class="motion-safe:animate-in motion-safe:fade-in motion-safe:slide-in-from-bottom-2 motion-safe:duration-200 motion-safe:delay-100">
                <div class="flex items-center justify-between mb-3">
                    <h2 class="text-xs font-bold text-slate-400 uppercase tracking-widest">Việc cần xử lý ngay</h2>
                    @if(count($priorityQueue) > 5)
                        <a href="{{ route('admin.verifications.queue') }}"
                           class="text-xs font-semibold text-blue-600 hover:text-blue-700 transition-colors">
                            Xem tất cả {{ count($priorityQueue) }} việc →
                        </a>
                    @endif
                </div>

                <div class="rounded-xl border border-slate-200 bg-white shadow-sm overflow-hidden divide-y divide-slate-100">
                    @forelse(array_slice($priorityQueue, 0, 5) as $item)
                        @php
                            $severityBorder = match($item['severity']) {
                                'critical' => 'border-l-red-500',
                                'warning'  => 'border-l-amber-500',
                                default    => 'border-l-blue-400',
                            };
                            $dotColor = match($item['severity']) {
                                'critical' => 'bg-red-500',
                                'warning'  => 'bg-amber-500',
                                default    => 'bg-blue-400',
                            };
                            $typeBg = match($item['type_key'] ?? 'other') {
                                'verification' => 'bg-blue-50 text-blue-700 border-blue-200',
                                'report'       => 'bg-amber-50 text-amber-700 border-amber-200',
                                'system'       => 'bg-slate-50 text-slate-600 border-slate-200',
                                default        => 'bg-slate-50 text-slate-600 border-slate-200',
                            };
                        @endphp
                        <div class="group flex items-center gap-4 px-5 py-3.5 border-l-4 {{ $severityBorder }}
                                    transition-colors duration-150 hover:bg-slate-50/60
                                    focus-within:ring-2 focus-within:ring-inset focus-within:ring-blue-400">
                            {{-- Severity dot --}}
                            <span class="w-2 h-2 rounded-full flex-shrink-0 {{ $dotColor }}
                                         {{ $item['severity'] === 'critical' ? 'animate-pulse' : '' }}"></span>

                            {{-- Content --}}
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 mb-0.5">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase border {{ $typeBg }}">
                                        {{ $item['type'] }}
                                    </span>
                                    <span class="text-[11px] text-slate-400 font-medium">
                                        {{ \Carbon\Carbon::parse($item['created_at'])->diffForHumans() }}
                                    </span>
                                </div>
                                <p class="text-sm font-semibold text-slate-800 truncate leading-snug">
                                    {{ $item['title'] }}
                                </p>
                            </div>

                            {{-- CTA --}}
                            @php
                                $isPrimary = $item['type_key'] === 'verification';
                            @endphp
                            <a href="{{ $item['cta_url'] }}"
                               class="flex-shrink-0 inline-flex items-center gap-1 px-3 py-1.5 rounded-lg
                                      text-xs font-bold transition-all duration-150 focus:outline-none focus:ring-2 focus:ring-offset-1
                                      opacity-80 group-hover:opacity-100
                                      {{ $isPrimary ? 'bg-ue-brand text-white hover:bg-ue-brand-active focus:ring-ue-brand/40 shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200 border border-slate-200 focus:ring-slate-300' }}">
                                {{ $item['cta_label'] }}
                                <x-ui.icon name="chevron-right" size="xs" />
                            </a>
                        </div>
                    @empty
                        <div class="flex flex-col items-center justify-center py-10 px-4 text-center">
                            <div class="w-10 h-10 rounded-full bg-emerald-50 flex items-center justify-center mb-3">
                                <x-ui.icon name="check-circle" size="md" class="text-emerald-500" />
                            </div>
                            <p class="text-sm font-semibold text-slate-700">Không có việc khẩn cấp</p>
                            <p class="text-xs text-slate-400 mt-1">Hệ thống đang vận hành ổn định.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- B. WEEKLY INSIGHTS ─────────────────────────────────────────────── --}}
            <div class="motion-safe:animate-in motion-safe:fade-in motion-safe:slide-in-from-bottom-2 motion-safe:duration-200 motion-safe:delay-150">
                <h2 class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-3">Insight 7 ngày gần đây</h2>
                @php
                    $totalActivity = $trends['new_users'] + $trends['posts'] + $trends['comments'];
                    $insightText = '';
                    if ($trends['reports'] >= 5) {
                        $insightText = 'Báo cáo vi phạm tăng cao — nên ưu tiên kiểm duyệt nội dung.';
                    } elseif ($trends['verifications_approved'] > 0 && $snapshot['pending_verification'] > $trends['verifications_approved']) {
                        $insightText = 'Xác thực đang tăng, hàng đợi tích lũy — nên tăng tốc phê duyệt.';
                    } elseif ($trends['reports'] === 0 && $totalActivity > 0) {
                        $insightText = 'Báo cáo thấp, hệ thống vận hành bình thường.';
                    }
                @endphp

                <div class="rounded-xl border border-slate-200 bg-white shadow-sm p-5">
                    @if($insightText)
                        <div class="flex items-start gap-2.5 mb-4 p-3 rounded-lg bg-blue-50 border border-blue-100">
                            <x-ui.icon name="info" size="sm" class="text-blue-500 flex-shrink-0 mt-0.5" />
                            <p class="text-xs font-semibold text-blue-800">{{ $insightText }}</p>
                        </div>
                    @endif

                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
                        @php
                            $trendItems = [
                                ['label' => 'Thành viên mới',   'value' => $trends['new_users'],               'color' => 'bg-blue-500'],
                                ['label' => 'Bài viết mới',      'value' => $trends['posts'],                  'color' => 'bg-slate-400'],
                                ['label' => 'Bình luận mới',     'value' => $trends['comments'],               'color' => 'bg-slate-400'],
                                ['label' => 'Báo cáo vi phạm',   'value' => $trends['reports'],                'color' => $trends['reports'] >= 5 ? 'bg-red-500' : 'bg-amber-400'],
                                ['label' => 'Đã duyệt xác thực', 'value' => $trends['verifications_approved'], 'color' => 'bg-emerald-500'],
                            ];
                            $maxVal = max(max(array_column($trendItems, 'value') ?: [0]), 1);
                        @endphp
                        @foreach($trendItems as $ti)
                            <div class="flex flex-col gap-1.5">
                                <span class="text-xs font-medium text-slate-500 leading-tight">{{ $ti['label'] }}</span>
                                <span class="text-2xl font-bold text-slate-900 leading-none">{{ number_format($ti['value']) }}</span>
                                <div class="w-full bg-slate-100 h-1 rounded-full overflow-hidden mt-0.5">
                                    <div class="{{ $ti['color'] }} h-full rounded-full transition-all duration-500"
                                         style="width: {{ $ti['value'] > 0 ? min(100, max(8, ($ti['value'] / $maxVal) * 100)) : 0 }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- C. ADMIN ACTIVITY TIMELINE ────────────────────────────────────── --}}
            <div class="motion-safe:animate-in motion-safe:fade-in motion-safe:slide-in-from-bottom-2 motion-safe:duration-200 motion-safe:delay-200">
                <div class="flex items-center justify-between mb-3">
                    <h2 class="text-xs font-bold text-slate-400 uppercase tracking-widest">Hoạt động quản trị gần đây</h2>
                    <a href="{{ route('admin.audit-logs.index') }}"
                       class="text-xs font-semibold text-blue-600 hover:text-blue-700 transition-colors">
                        Xem nhật ký đầy đủ →
                    </a>
                </div>

                <div class="rounded-xl border border-slate-200 bg-white shadow-sm overflow-hidden">
                    @forelse($recentActivity as $log)
                        @php
                            $rawKey        = strtolower($log['action_key'] ?? $log['action'] ?? '');
                            $humanAction   = $actionMap[$rawKey] ?? $actionMap[$log['action'] ?? ''] ?? null;
                            $isUnknown     = $humanAction === null;
                            $displayAction = $humanAction ?? 'Hoạt động quản trị';

                            $actorName = $log['actor_name']
                                ?? ($log['actor_type'] === 'system' ? 'Hệ thống' : 'Quản trị viên');

                            $rawTarget  = $log['target_type'] ?? null;
                            $targetId   = $log['target_id'] ?? null;
                            $targetLabel = isset($rawTarget)
                                ? ($targetTypeMap[strtolower($rawTarget)] ?? ucfirst(str_replace('_', ' ', $rawTarget)))
                                : null;
                            $targetText = $targetLabel && $targetId
                                ? "{$targetLabel} #{$targetId}"
                                : ($targetLabel ?? null);
                        @endphp
                        <div class="flex items-start gap-3 px-5 py-3.5 border-b border-slate-100 last:border-b-0
                                    transition-colors duration-150 hover:bg-slate-50/60 group">
                            {{-- Avatar initials --}}
                            <div class="flex-shrink-0 w-7 h-7 rounded-full bg-slate-100 flex items-center justify-center
                                        text-[10px] font-bold text-slate-600 mt-0.5">
                                {{ mb_strtoupper(mb_substr($actorName, 0, 2)) }}
                            </div>

                            {{-- Content --}}
                            <div class="flex-1 min-w-0">
                                <div class="flex items-start justify-between gap-2">
                                    <div>
                                        <p class="text-sm text-slate-800 leading-snug">
                                            <span class="font-semibold">{{ $actorName }}</span>
                                            <span class="text-slate-600"> — {{ $displayAction }}</span>
                                        </p>
                                        @if($targetText)
                                            <span class="text-xs text-slate-400 font-medium mt-0.5 inline-block">
                                                {{ $targetText }}
                                            </span>
                                        @endif
                                    </div>
                                    <span class="text-[11px] text-slate-400 font-medium flex-shrink-0 mt-0.5">
                                        {{ \Carbon\Carbon::parse($log['created_at'])->diffForHumans() }}
                                    </span>
                                </div>
                                {{-- Raw action in tooltip for unknown keys --}}
                                @if($isUnknown && !empty($rawKey))
                                    <span class="text-[10px] text-slate-300 font-mono mt-0.5 inline-block"
                                          title="Raw action key: {{ $rawKey }}">
                                        debug: {{ $rawKey }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="py-10 text-center">
                            <p class="text-sm text-slate-500 font-medium">Chưa có hoạt động quản trị nào.</p>
                        </div>
                    @endforelse
                </div>
            </div>

        </div>{{-- end main area --}}

        {{-- ───────── RIGHT SIDEBAR (4 cols) ─────────────────────────────────── --}}
        <div class="lg:col-span-4 space-y-5 lg:sticky lg:top-6 lg:self-start">

            {{-- SYSTEM HEALTH ──────────────────────────────────────────────────── --}}
            <div class="motion-safe:animate-in motion-safe:fade-in motion-safe:slide-in-from-bottom-2 motion-safe:duration-200 motion-safe:delay-100">
                <h2 class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-3">Tình trạng hệ thống</h2>
                <div class="rounded-xl border border-slate-200 bg-white shadow-sm overflow-hidden divide-y divide-slate-100">
                    @foreach($systemHealth as $service)
                        @php
                            $dotClass = match($service['status']) {
                                'healthy'  => 'bg-emerald-500',
                                'degraded' => 'bg-amber-500',
                                'down'     => 'bg-red-500 animate-pulse',
                                default    => 'bg-slate-300',
                            };
                            $badgeClass = match($service['status']) {
                                'healthy'  => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                'degraded' => 'bg-amber-50 text-amber-700 border-amber-200',
                                'down'     => 'bg-red-50 text-red-700 border-red-200',
                                default    => 'bg-slate-50 text-slate-500 border-slate-200',
                            };
                            $badgeText = match($service['status']) {
                                'healthy'  => 'Hoạt động',
                                'degraded' => 'Sự cố nhẹ',
                                'down'     => 'Ngoại tuyến',
                                'disabled' => 'Chưa bật',
                                default    => 'Không rõ',
                            };
                        @endphp
                        <div class="flex items-center gap-3 px-4 py-3 transition-colors duration-150 hover:bg-slate-50/60">
                            <span class="w-2 h-2 rounded-full flex-shrink-0 {{ $dotClass }}"></span>
                            <div class="flex-1 min-w-0">
                                <p class="text-xs font-semibold text-slate-700 truncate">{{ $service['name'] }}</p>
                                <p class="text-[11px] text-slate-400 font-medium mt-0.5 leading-tight truncate">{{ $service['message'] }}</p>
                            </div>
                            <span class="flex-shrink-0 inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold border {{ $badgeClass }}">
                                {{ $badgeText }}
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- QUICK ACTIONS ───────────────────────────────────────────────────── --}}
            <div class="motion-safe:animate-in motion-safe:fade-in motion-safe:slide-in-from-bottom-2 motion-safe:duration-200 motion-safe:delay-150">
                <h2 class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-3">Thao tác nhanh</h2>
                <div class="rounded-xl border border-slate-200 bg-white shadow-sm overflow-hidden divide-y divide-slate-100">
                    @php
                        $quickActions = [
                            ['label' => 'Duyệt xác thực', 'icon' => 'shield-check', 'url' => route('admin.verifications.queue'), 'badge' => $snapshot['pending_verification'] > 0 ? $snapshot['pending_verification'] : null, 'badgeColor' => 'bg-blue-100 text-blue-700'],
                            ['label' => 'Xử lý báo cáo', 'icon' => 'flag', 'url' => route('admin.reports.index'), 'badge' => $snapshot['open_reports'] > 0 ? $snapshot['open_reports'] : null, 'badgeColor' => 'bg-amber-100 text-amber-700'],
                            ['label' => 'Tài khoản hạn chế', 'icon' => 'user-x', 'url' => route('admin.users.index'), 'badge' => $snapshot['restricted_users'] > 0 ? $snapshot['restricted_users'] : null, 'badgeColor' => 'bg-red-100 text-red-700'],
                            ['label' => 'Kiểm duyệt nội dung', 'icon' => 'eye', 'url' => route('admin.moderation.index'), 'badge' => $snapshot['pending_moderation'] > 0 ? $snapshot['pending_moderation'] : null, 'badgeColor' => 'bg-slate-100 text-slate-600'],
                            ['label' => 'Quản lý Media', 'icon' => 'folder', 'url' => route('admin.media.index'), 'badge' => null, 'badgeColor' => ''],
                            ['label' => 'Nhật ký hoạt động', 'icon' => 'list', 'url' => route('admin.audit-logs.index'), 'badge' => null, 'badgeColor' => ''],
                        ];
                    @endphp
                    @foreach($quickActions as $action)
                        <a href="{{ $action['url'] }}"
                           class="group flex items-center gap-3 px-4 py-3
                                  text-sm font-semibold text-slate-600
                                  transition-colors duration-150 hover:bg-slate-50 hover:text-slate-900
                                  focus:outline-none focus:ring-2 focus:ring-inset focus:ring-blue-400">
                            <x-ui.icon name="{{ $action['icon'] }}" size="sm" class="text-slate-400 group-hover:text-slate-600 transition-colors flex-shrink-0" />
                            <span class="flex-1">{{ $action['label'] }}</span>
                            @if($action['badge'])
                                <span class="text-[10px] font-bold px-1.5 py-0.5 rounded {{ $action['badgeColor'] }}">
                                    {{ $action['badge'] }}
                                </span>
                            @else
                                <x-ui.icon name="chevron-right" size="xs" class="text-slate-300 group-hover:text-slate-400 transition-colors flex-shrink-0" />
                            @endif
                        </a>
                    @endforeach
                </div>
            </div>

            {{-- VERIFICATIONS BREAKDOWN ──────────────────────────────────────── --}}
            @if($snapshot['pending_verification'] > 0 || $snapshot['needs_info_verification'] > 0 || $snapshot['conflicts_verification'] > 0)
                <div class="motion-safe:animate-in motion-safe:fade-in motion-safe:slide-in-from-bottom-2 motion-safe:duration-200 motion-safe:delay-200">
                    <h2 class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-3">Phân loại xác thực</h2>
                    <div class="rounded-xl border border-slate-200 bg-white shadow-sm p-4 space-y-2.5">
                        @if($snapshot['pending_verification'] > 0)
                            <div class="flex items-center justify-between text-xs">
                                <div class="flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-blue-400 flex-shrink-0"></span>
                                    <span class="font-medium text-slate-600">Chờ phê duyệt</span>
                                </div>
                                <span class="font-bold text-slate-900">{{ $snapshot['pending_verification'] }}</span>
                            </div>
                        @endif
                        @if($snapshot['needs_info_verification'] > 0)
                            <div class="flex items-center justify-between text-xs">
                                <div class="flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-amber-400 flex-shrink-0"></span>
                                    <span class="font-medium text-slate-600">Cần bổ sung</span>
                                </div>
                                <span class="font-bold text-slate-900">{{ $snapshot['needs_info_verification'] }}</span>
                            </div>
                        @endif
                        @if($snapshot['conflicts_verification'] > 0)
                            <div class="flex items-center justify-between text-xs">
                                <div class="flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-red-500 flex-shrink-0"></span>
                                    <span class="font-medium text-slate-600">Xung đột MSSV</span>
                                </div>
                                <span class="font-bold text-red-700">{{ $snapshot['conflicts_verification'] }}</span>
                            </div>
                        @endif
                        @if($snapshot['approved_today'] > 0)
                            <div class="pt-2 border-t border-slate-100 flex items-center justify-between text-xs">
                                <div class="flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-emerald-500 flex-shrink-0"></span>
                                    <span class="font-medium text-slate-500">Đã duyệt hôm nay</span>
                                </div>
                                <span class="font-bold text-emerald-700">{{ $snapshot['approved_today'] }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

        </div>{{-- end sidebar --}}

    </div>{{-- end main grid --}}

</div>{{-- end wrapper --}}
