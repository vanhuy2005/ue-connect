<?php

use App\Models\Report;
use App\Models\Post;
use App\Models\Comment;
use App\Enums\ReportStatus;
use App\Enums\PostStatus;
use App\Enums\CommentStatus;
use App\Services\AuditLogService;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;

new class extends Component {
    public Report $report;
    public ?string $feedbackMessage = null;

    public function mount(Report $report): void
    {
        $this->report = $report;
    }

    /**
     * Dismiss the report (Idempotent).
     */
    public function dismissReport(): void
    {
        // Enforce manage_reports permission
        if (! Auth::user()->hasPermissionTo('manage_reports')) {
            abort(403, 'Bạn không có quyền thực hiện hành động này.');
        }

        // Idempotency check
        if ($this->report->status === ReportStatus::DISMISSED) {
            $this->feedbackMessage = 'Báo cáo này đã được bỏ qua trước đó.';
            return;
        }

        $beforeSnapshot = $this->report->toArray();

        // Update report status
        $this->report->status = ReportStatus::DISMISSED;
        $this->report->save();

        // Log action
        AuditLogService::log(
            actorId: Auth::id(),
            actorType: 'user',
            actionKey: 'report_dismissed',
            targetType: 'report',
            targetId: $this->report->id,
            beforeSnapshot: $beforeSnapshot,
            afterSnapshot: $this->report->toArray(),
            reason: 'Báo cáo vi phạm bị từ chối/bỏ qua bởi Admin.'
        );

        $this->feedbackMessage = 'Đã bỏ qua báo cáo vi phạm thành công.';
    }

    /**
     * Hide the reported target content (Idempotent).
     */
    public function hideTargetContent(): void
    {
        // Enforce manage_reports permission
        if (! Auth::user()->hasPermissionTo('manage_reports')) {
            abort(403, 'Bạn không có quyền thực hiện hành động này.');
        }

        $target = $this->report->target;

        // Verify target exists and is supported
        if (! $target) {
            // Target might be already deleted or missing. Handle safely
            $this->report->status = ReportStatus::ACTION_TAKEN;
            $this->report->save();
            $this->feedbackMessage = 'Nội dung mục tiêu không tồn tại hoặc đã bị xóa trước đó. Đã cập nhật trạng thái báo cáo.';
            return;
        }

        if (! ($target instanceof Post || $target instanceof Comment)) {
            $this->feedbackMessage = 'Loại nội dung mục tiêu không được hỗ trợ để ẩn kiểm duyệt.';
            return;
        }

        // Idempotency check
        if ($this->report->status === ReportStatus::ACTION_TAKEN && $target->status->value === 'hidden_by_moderation') {
            $this->feedbackMessage = 'Nội dung mục tiêu đã được ẩn trước đó.';
            return;
        }

        $targetBeforeSnapshot = $target->toArray();
        $reportBeforeSnapshot = $this->report->toArray();

        // Hide target content based on type (do not hard delete)
        if ($target instanceof Post) {
            $target->status = PostStatus::HIDDEN_BY_MODERATION;
        } else {
            $target->status = CommentStatus::HIDDEN_BY_MODERATION;
        }
        $target->save();

        // Update report status
        $this->report->status = ReportStatus::ACTION_TAKEN;
        $this->report->save();

        // Log actions
        AuditLogService::log(
            actorId: Auth::id(),
            actorType: 'user',
            actionKey: 'target_hidden',
            targetType: $this->report->target_type,
            targetId: $this->report->target_id,
            beforeSnapshot: $targetBeforeSnapshot,
            afterSnapshot: $target->toArray(),
            reason: 'Nội dung bị ẩn do vi phạm quy chuẩn cộng đồng.'
        );

        AuditLogService::log(
            actorId: Auth::id(),
            actorType: 'user',
            actionKey: 'report_resolved_hide',
            targetType: 'report',
            targetId: $this->report->id,
            beforeSnapshot: $reportBeforeSnapshot,
            afterSnapshot: $this->report->toArray(),
            reason: 'Báo cáo vi phạm đã được giải quyết bằng phương án ẩn nội dung.'
        );

        $this->feedbackMessage = 'Đã ẩn nội dung vi phạm và cập nhật báo cáo thành công.';
    }
}; ?>

<div class="max-w-4xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    {{-- Back button --}}
    <a href="{{ route('admin.reports.index') }}" class="inline-flex items-center gap-2 text-sm text-slate-500 hover:text-ue-brand mb-6 transition-colors font-semibold">
        <x-ui.icon name="arrow-left" size="xs" />
        Quay lại hàng chờ báo cáo
    </a>

    {{-- System feedback alerts --}}
    @if ($feedbackMessage)
        <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm flex items-start gap-2 shadow-sm animate-fadeIn" role="alert">
            <x-ui.icon name="check-circle" size="sm" class="text-emerald-600 mt-0.5 flex-shrink-0" />
            <div class="flex-1 font-semibold">{{ $feedbackMessage }}</div>
            <button type="button" wire:click="$set('feedbackMessage', null)" class="text-emerald-400 hover:text-emerald-600 transition-colors">
                <x-ui.icon name="x" size="xs" />
            </button>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Report details column --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Target Content Card --}}
            <x-ui.card class="p-6">
                <h3 class="text-sm font-bold text-slate-800 border-b border-slate-100 pb-3 mb-4 flex items-center gap-2">
                    <x-ui.icon name="file-text" size="xs" class="text-ue-brand" />
                    Xem trước nội dung bị báo cáo
                </h3>

                @php $target = $report->target; @endphp

                @if (! $target)
                    <div class="bg-red-50 border border-red-100 text-red-800 rounded-xl p-4 text-xs font-semibold text-center flex flex-col items-center gap-2">
                        <x-ui.icon name="alert-triangle" size="sm" class="text-red-600" />
                        Nội dung mục tiêu không tồn tại hoặc đã bị xóa trước đó.
                    </div>
                @else
                    @if ($report->target_type === 'post')
                        <div class="bg-slate-50 rounded-xl p-4 border border-slate-100 space-y-3">
                            <div class="flex items-center gap-2 text-xs font-bold text-slate-700">
                                <div class="w-8 h-8 rounded-full bg-ue-brand-soft border border-slate-100 flex items-center justify-center font-bold text-ue-brand text-xs select-none">
                                    {{ mb_substr($target->user->name, 0, 2) }}
                                </div>
                                <div>
                                    <div class="font-bold text-slate-800">{{ $target->user->name }}</div>
                                    <div class="text-xxs text-slate-400 font-medium">{{ $target->user->email }} · Tác giả bài viết</div>
                                </div>
                                <span class="ml-auto text-slate-400 font-medium">{{ $target->created_at->diffForHumans() }}</span>
                            </div>
                            <div class="text-slate-700 text-sm whitespace-pre-wrap leading-relaxed border-t border-slate-200/60 pt-3">
                                {{ $target->body }}
                            </div>
                            <div class="text-xxs font-semibold text-slate-400 pt-2 flex items-center gap-1.5">
                                <x-ui.icon name="eye" size="xxs" />
                                <span>Trạng thái hiện tại: <span class="uppercase font-bold text-slate-600">{{ $target->status->value }}</span></span>
                            </div>
                        </div>
                    @elseif ($report->target_type === 'comment')
                        <div class="bg-slate-50 rounded-xl p-4 border border-slate-100 space-y-3">
                            {{-- Comment author --}}
                            <div class="flex items-center gap-2 text-xs font-bold text-slate-700">
                                <div class="w-8 h-8 rounded-full bg-purple-50 border border-slate-100 flex items-center justify-center font-bold text-purple-700 text-xs select-none">
                                    {{ mb_substr($target->user->name, 0, 2) }}
                                </div>
                                <div>
                                    <div class="font-bold text-slate-800">{{ $target->user->name }}</div>
                                    <div class="text-xxs text-slate-400 font-medium">{{ $target->user->email }} · Tác giả bình luận</div>
                                </div>
                                <span class="ml-auto text-slate-400 font-medium">{{ $target->created_at->diffForHumans() }}</span>
                            </div>
                            <div class="text-slate-700 text-sm whitespace-pre-wrap leading-relaxed border-t border-slate-200/60 pt-3">
                                {{ $target->body }}
                            </div>
                            
                            {{-- Parent post context --}}
                            @if ($target->post)
                                <div class="bg-white border border-slate-200 rounded-lg p-3 text-xs mt-2 space-y-1">
                                    <span class="font-semibold text-slate-400 block">Thuộc bài viết:</span>
                                    <span class="text-slate-600 italic">"{{ Str::limit($target->post->body, 120) }}"</span>
                                </div>
                            @endif

                            <div class="text-xxs font-semibold text-slate-400 pt-2 flex items-center gap-1.5">
                                <x-ui.icon name="eye" size="xxs" />
                                <span>Trạng thái hiện tại: <span class="uppercase font-bold text-slate-600">{{ $target->status->value }}</span></span>
                            </div>
                        </div>
                    @endif
                @endif
            </x-ui.card>

            {{-- Reporter Info --}}
            <x-ui.card class="p-6">
                <h3 class="text-sm font-bold text-slate-800 border-b border-slate-100 pb-3 mb-4 flex items-center gap-2">
                    <x-ui.icon name="user" size="xs" class="text-ue-brand" />
                    Thông tin người báo cáo & Lý do
                </h3>

                <div class="space-y-4 text-xs font-semibold text-slate-600">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <span class="text-slate-400 block mb-0.5">Tên người báo cáo</span>
                            <span class="text-slate-800 text-sm font-bold">{{ $report->reporter->name }}</span>
                        </div>
                        <div>
                            <span class="text-slate-400 block mb-0.5">Email liên hệ</span>
                            <span class="text-slate-800 text-sm font-bold">{{ $report->reporter->email }}</span>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4 pt-2">
                        <div>
                            <span class="text-slate-400 block mb-0.5">Lý do vi phạm</span>
                            <span class="px-2 py-0.5 bg-red-50 text-red-700 font-bold border border-red-100 rounded-lg text-xxs uppercase">
                                @switch($report->reason->value)
                                    @case('spam') Tin rác / Spam @break
                                    @case('harassment') Quấy rối / Công kích @break
                                    @case('inappropriate_content') Nội dung không phù hợp @break
                                    @case('misinformation') Tin sai lệch @break
                                    @case('privacy_violation') Xâm phạm riêng tư @break
                                    @default Lý do khác
                                @endswitch
                            </span>
                        </div>
                        <div>
                            <span class="text-slate-400 block mb-0.5">Thời gian gửi báo cáo</span>
                            <span class="text-slate-800 text-xs font-bold">{{ $report->created_at->format('H:i d/m/Y') }}</span>
                        </div>
                    </div>

                    @if ($report->description)
                        <div class="pt-3 border-t border-slate-100">
                            <span class="text-slate-400 block mb-1">Mô tả bổ sung từ người dùng</span>
                            <div class="bg-slate-50 border border-slate-100 rounded-xl p-3 text-slate-700 font-medium leading-relaxed">
                                {{ $report->description }}
                            </div>
                        </div>
                    @endif
                </div>
            </x-ui.card>
        </div>

        {{-- Action Panel column --}}
        <div class="space-y-6">
            <x-ui.card class="p-6">
                <h3 class="text-sm font-bold text-slate-800 border-b border-slate-100 pb-3 mb-4 flex items-center gap-2">
                    <x-ui.icon name="shield" size="xs" class="text-ue-brand" />
                    Trạng thái & Quyết định
                </h3>

                <div class="space-y-4">
                    <div>
                        <span class="text-xs font-semibold text-slate-400 block mb-1">Trạng thái báo cáo</span>
                        @switch($report->status->value)
                            @case('pending')
                                <span class="inline-flex px-3 py-1 bg-yellow-50 text-yellow-700 border border-yellow-100 rounded-full font-bold text-xs">Chờ kiểm duyệt</span>
                                @break
                            @case('reviewed')
                                <span class="inline-flex px-3 py-1 bg-blue-50 text-blue-700 border border-blue-100 rounded-full font-bold text-xs">Đang xem xét</span>
                                @break
                            @case('dismissed')
                                <span class="inline-flex px-3 py-1 bg-slate-100 text-slate-500 border border-slate-200 rounded-full font-bold text-xs">Đã bỏ qua</span>
                                @break
                            @case('action_taken')
                                <span class="inline-flex px-3 py-1 bg-emerald-50 text-emerald-700 border border-emerald-100 rounded-full font-bold text-xs">Đã xử lý / Ẩn</span>
                                @break
                        @endswitch
                    </div>

                    {{-- Actions block --}}
                    @if ($report->status === ReportStatus::PENDING || $report->status === ReportStatus::REVIEWED)
                        <div class="pt-4 border-t border-slate-100 space-y-3">
                            <button 
                                type="button"
                                wire:click="hideTargetContent"
                                class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-red-600 hover:bg-red-700 text-white text-xs font-bold rounded-xl shadow-sm transition-all"
                                onclick="return confirm('Bạn có chắc chắn muốn ẩn nội dung này khỏi cộng đồng?') || event.stopImmediatePropagation()"
                            >
                                <x-ui.icon name="eye-off" size="xs" />
                                Phê duyệt & Ẩn nội dung
                            </button>

                            <button 
                                type="button"
                                wire:click="dismissReport"
                                class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl border border-slate-200 transition-all shadow-xs"
                                onclick="return confirm('Bạn muốn từ chối/bỏ qua báo cáo vi phạm này?') || event.stopImmediatePropagation()"
                            >
                                <x-ui.icon name="slash" size="xs" />
                                Từ chối / Bỏ qua báo cáo
                            </button>
                        </div>
                    @else
                        <div class="pt-4 border-t border-slate-100">
                            <p class="text-xs text-slate-400 font-semibold italic text-center py-2 bg-slate-50 rounded-xl border border-slate-150">
                                Báo cáo này đã được giải quyết và đóng hồ sơ.
                            </p>
                        </div>
                    @endif
                </div>
            </x-ui.card>
        </div>
    </div>
</div>
