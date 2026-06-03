<?php

use App\Enums\PostStatus;
use App\Enums\PostType;
use App\Models\OpportunityDetail;
use App\Models\Post;
use App\Services\AuditLogService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.app', ['shell' => 'admin'])] class extends Component
{
    public Post $post;
    public ?OpportunityDetail $opportunityDetail = null;
    public string $action = 'approve';
    public string $reason = '';

    public function mount(Post $post): void
    {
        $this->post = $post->load(['user.profile', 'opportunityDetail']);
        $this->opportunityDetail = $this->post->opportunityDetail;

        if ($this->post->post_type?->value !== PostType::OPPORTUNITY->value) {
            abort(404);
        }
    }

    public function process(): void
    {
        $this->validate([
            'reason' => ['nullable', 'string', 'min:5', 'max:1000'],
        ]);

        DB::transaction(function () {
            $before = $this->post->toArray();

            if ($this->action === 'approve') {
                $this->post->update([
                    'status' => PostStatus::PUBLISHED,
                    'published_at' => now(),
                ]);

                $reviewNote = $this->reason ?: 'Đã kiểm duyệt và duyệt cơ hội việc làm.';

                AuditLogService::log(
                    actorId: auth()->id(),
                    actorType: 'admin',
                    actionKey: 'opportunity.approve',
                    targetType: 'posts',
                    targetId: $this->post->id,
                    beforeSnapshot: $before,
                    afterSnapshot: $this->post->fresh()->toArray(),
                    reason: $reviewNote
                );

                session()->flash('success', 'Đã duyệt cơ hội thành công.');
            } else {
                $this->post->update([
                    'status' => PostStatus::REJECTED,
                ]);

                $reviewNote = $this->reason ?: 'Đã từ chối cơ hội việc làm.';

                AuditLogService::log(
                    actorId: auth()->id(),
                    actorType: 'admin',
                    actionKey: 'opportunity.reject',
                    targetType: 'posts',
                    targetId: $this->post->id,
                    beforeSnapshot: $before,
                    afterSnapshot: $this->post->fresh()->toArray(),
                    reason: $reviewNote
                );

                session()->flash('success', 'Đã từ chối cơ hội.');
            }

            $this->post->refresh();
        });
    }
};
?>

<div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    {{-- Breadcrumb / Header --}}
    <div class="mb-6">
        <a href="{{ route('admin.opportunities.queue') }}" class="inline-flex items-center text-xs font-semibold text-ue-text-muted hover:text-ue-brand transition-colors mb-2">
            <x-ui.icon name="arrow-left" size="sm" class="mr-1" />
            Quay lại Danh sách chờ duyệt
        </a>
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-ue-text">Kiểm duyệt cơ hội việc làm</h1>
                <p class="text-sm text-ue-text-secondary mt-1">ID: #{{ $post->id }} — {{ $opportunityDetail?->position ?? 'Không có vị trí' }}</p>
            </div>
            <div>
                @php
                    $badgeVariant = match($post->status->value) {
                        PostStatus::PENDING_REVIEW->value => 'pending',
                        PostStatus::PUBLISHED->value, PostStatus::EDITED->value => 'success',
                        PostStatus::REJECTED->value => 'rejected',
                        default => 'neutral',
                    };
                    $badgeLabel = match($post->status->value) {
                        PostStatus::PENDING_REVIEW->value => 'Chờ duyệt',
                        PostStatus::PUBLISHED->value, PostStatus::EDITED->value => 'Đã duyệt',
                        PostStatus::REJECTED->value => 'Từ chối',
                        default => $post->status->value,
                    };
                @endphp
                <x-ui.badge :variant="$badgeVariant" size="md">{{ $badgeLabel }}</x-ui.badge>
            </div>
        </div>
    </div>

    {{-- Error messages --}}
    @if ($errors->has('general'))
        <div class="mb-6 p-4 bg-[var(--danger-bg-soft)] text-[var(--danger-text)] rounded-xl border border-[var(--danger-border)]">
            <div class="flex items-center gap-2">
                <x-ui.icon name="alert-circle" />
                <span class="font-bold text-sm">{{ $errors->first('general') }}</span>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Left & Middle Column (2/3): Post Content & Opportunity Details --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- Author Info --}}
            <x-ui.card>
                <h2 class="text-base font-bold text-ue-text border-b border-ue-border pb-3 mb-4">Thông tin người đăng</h2>
                <div class="flex items-center gap-3">
                    <x-ui.avatar :user="$post->user" size="md" />
                    <div>
                        <div class="font-bold text-sm text-ue-text">{{ $post->user->name }}</div>
                        <div class="text-xs text-ue-text-muted">{{ $post->user->email }}</div>
                        @if ($post->user->profile?->faculty)
                            <div class="text-[10px] text-slate-400 font-semibold mt-0.5">{{ $post->user->profile->faculty }}</div>
                        @endif
                    </div>
                </div>
                <div class="mt-3 text-xs text-ue-text-muted">
                    Đăng lúc: {{ $post->created_at->format('H:i d/m/Y') }}
                    @if ($post->published_at)
                        <span class="text-green-600 font-semibold ml-2">(Đã xuất bản {{ $post->published_at->format('H:i d/m/Y') }})</span>
                    @endif
                </div>
            </x-ui.card>

            {{-- Opportunity Details --}}
            @if ($opportunityDetail)
                <x-ui.card>
                    <h2 class="text-base font-bold text-ue-text border-b border-ue-border pb-3 mb-4">Thông tin cơ hội việc làm</h2>
                    <div class="space-y-3">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <div class="text-xs text-ue-text-muted font-semibold">Công ty</div>
                                <div class="font-bold text-ue-text mt-0.5">{{ $opportunityDetail->company }}</div>
                            </div>
                            <div>
                                <div class="text-xs text-ue-text-muted font-semibold">Vị trí</div>
                                <div class="font-bold text-ue-text mt-0.5">{{ $opportunityDetail->position }}</div>
                            </div>
                            @if ($opportunityDetail->location)
                                <div>
                                    <div class="text-xs text-ue-text-muted font-semibold">Địa điểm</div>
                                    <div class="font-bold text-ue-text mt-0.5 flex items-center gap-1">
                                        <x-ui.icon name="map-pin" size="xs" class="text-slate-400" />
                                        {{ $opportunityDetail->location }}
                                    </div>
                                </div>
                            @endif
                            @if ($opportunityDetail->application_url)
                                <div>
                                    <div class="text-xs text-ue-text-muted font-semibold">Link ứng tuyển</div>
                                    <div class="font-bold text-ue-text mt-0.5">
                                        <a href="{{ $opportunityDetail->application_url }}" target="_blank" class="text-ue-brand hover:underline break-all">
                                            {{ $opportunityDetail->application_url }}
                                        </a>
                                    </div>
                                </div>
                            @endif
                            @if ($opportunityDetail->application_deadline)
                                <div>
                                    <div class="text-xs text-ue-text-muted font-semibold">Hạn ứng tuyển</div>
                                    <div class="font-bold text-ue-text mt-0.5 flex items-center gap-1">
                                        <x-ui.icon name="calendar" size="xs" class="text-slate-400" />
                                        {{ $opportunityDetail->application_deadline->format('d/m/Y') }}
                                    </div>
                                </div>
                            @endif
                        </div>
                        @if ($opportunityDetail->field_tags && count($opportunityDetail->field_tags) > 0)
                            <div class="border-t border-ue-border pt-3">
                                <div class="text-xs text-ue-text-muted font-semibold mb-2">Lĩnh vực / Kỹ năng</div>
                                <div class="flex flex-wrap gap-1.5">
                                    @foreach ($opportunityDetail->field_tags as $tag)
                                        <span class="px-2.5 py-1 bg-slate-100 text-slate-700 text-[10px] font-bold rounded-full border border-slate-200">{{ $tag }}</span>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </x-ui.card>
            @endif

            {{-- Post Content --}}
            <x-ui.card>
                <h2 class="text-base font-bold text-ue-text border-b border-ue-border pb-3 mb-4">Nội dung đăng</h2>
                <div class="text-sm text-ue-text whitespace-pre-wrap leading-relaxed">{{ $post->body }}</div>
                @if ($post->media_url)
                    <div class="mt-4">
                        <img src="{{ $post->media_url }}" alt="Post media" class="max-w-full max-h-[400px] rounded-lg border border-ue-border" />
                    </div>
                @endif
            </x-ui.card>

            {{-- Audit Trail --}}
            <x-ui.card>
                <h2 class="text-base font-bold text-ue-text border-b border-ue-border pb-3 mb-3">Lịch sử kiểm duyệt</h2>
                <div class="relative pl-4 border-l-2 border-ue-border space-y-4 py-1 text-xs">
                    @php
                        $audits = \App\Models\AuditLog::where('target_type', 'posts')
                            ->where('target_id', $post->id)
                            ->whereIn('action', ['opportunity.approve', 'opportunity.reject'])
                            ->latest()
                            ->get();
                    @endphp
                    @forelse ($audits as $act)
                        @php
                            $dotColor = match($act->action) {
                                'opportunity.approve' => 'bg-green-500 ring-green-100',
                                'opportunity.reject' => 'bg-red-500 ring-red-100',
                                default => 'bg-slate-500 ring-slate-100',
                            };
                            $actLabel = match($act->action) {
                                'opportunity.approve' => 'Đã phê duyệt',
                                'opportunity.reject' => 'Đã từ chối',
                                default => $act->action,
                            };
                        @endphp
                        <div class="relative">
                            <div class="absolute -left-[21px] mt-0.5 w-2.5 h-2.5 rounded-full {{ $dotColor }} ring-4"></div>
                            <div class="font-bold text-ue-text">{{ $actLabel }}</div>
                            <div class="text-[10px] text-ue-text-muted mt-0.5">
                                Thực hiện bởi: <span class="font-bold">{{ $act->actor?->name ?? 'Hệ thống' }}</span>
                            </div>
                            <div class="text-[10px] text-ue-text-disabled">{{ $act->created_at->format('H:i d/m/Y') }} ({{ $act->created_at->diffForHumans() }})</div>
                            @if ($act->reason)
                                <div class="mt-1 bg-ue-surface border border-ue-border p-2 rounded text-ue-text-secondary leading-normal">
                                    "{{ $act->reason }}"
                                </div>
                            @endif
                        </div>
                    @empty
                        <div class="text-ue-text-muted italic py-1 pl-1">Chưa có lịch sử thao tác nào được lưu.</div>
                    @endforelse
                </div>
            </x-ui.card>
        </div>

        {{-- Right Column (1/3): Review Form Drawer --}}
        <div>
            <x-ui.card variant="elevated" class="sticky top-6">
                <h2 class="text-base font-bold text-ue-text border-b border-ue-border pb-3 mb-4">Xử lý cơ hội việc làm</h2>

                <form wire:submit.prevent="process" class="space-y-4">
                    {{-- Action Selection --}}
                    <div>
                        <label class="block text-sm font-semibold text-ue-text mb-1">Hành động</label>
                        <select wire:model.live="action" class="w-full px-3 py-2 border rounded-lg text-sm">
                            <option value="approve">Phê duyệt (Approve)</option>
                            <option value="reject">Từ chối (Reject)</option>
                        </select>
                    </div>

                    {{-- Dynamic guidance --}}
                    @if ($action === 'approve')
                        <div class="p-3 bg-green-50 text-green-800 border border-green-200 rounded-lg text-xs space-y-1">
                            <div class="font-bold flex items-center gap-1">
                                <x-ui.icon name="check-circle" size="xs" />
                                Phê duyệt cơ hội:
                            </div>
                            <div class="leading-relaxed font-semibold">Bài viết sẽ chuyển sang trạng thái <span class="underline">Đã duyệt</span> và hiển thị công khai.</div>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-ue-text mb-1">Ghi chú phê duyệt (Tùy chọn)</label>
                            <textarea wire:model="reason" class="w-full px-3 py-2 border rounded-lg text-sm" rows="3" placeholder="Nhập ghi chú phê duyệt..."></textarea>
                        </div>
                    @else
                        <div class="p-3 bg-red-50 text-red-800 border border-red-200 rounded-lg text-xs space-y-1">
                            <div class="font-bold flex items-center gap-1">
                                <x-ui.icon name="x-circle" size="xs" />
                                Từ chối cơ hội:
                            </div>
                            <div class="leading-relaxed font-semibold">Bài viết sẽ bị từ chối và không hiển thị công khai.</div>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-ue-text mb-1">Lý do từ chối (Tùy chọn)</label>
                            <textarea wire:model="reason" class="w-full px-3 py-2 border rounded-lg text-sm" rows="3" placeholder="Nhập lý do từ chối..."></textarea>
                        </div>
                    @endif

                    {{-- Submit Button --}}
                    <div class="border-t border-ue-border pt-4 flex gap-2">
                        @if ($action === 'approve')
                            <button type="submit" class="flex-1 flex items-center justify-center gap-2 px-4 py-2.5 bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold rounded-lg shadow-2xs hover:shadow-xs transition-all">
                                <x-ui.icon name="check" size="xs" />
                                Xác nhận duyệt
                            </button>
                        @else
                            <button type="submit" class="flex-1 flex items-center justify-center gap-2 px-4 py-2.5 bg-white hover:bg-slate-50 text-slate-600 text-xs font-bold rounded-lg border border-slate-200 transition-colors">
                                <x-ui.icon name="x" size="xs" />
                                Xác nhận từ chối
                            </button>
                        @endif
                    </div>
                </form>

                {{-- Quick actions (matching greeting style) --}}
                <div class="mt-4 pt-4 border-t border-ue-border">
                    <div class="text-[10px] text-ue-text-muted font-bold uppercase tracking-wider mb-2">Thao tác nhanh</div>
                    <div class="flex gap-2">
                        <button
                            type="button"
                            wire:click="$set('action', 'approve'); $set('reason', '');"
                            class="flex-1 bg-slate-900 hover:bg-slate-800 text-white text-[10px] font-bold px-3 py-2 rounded-lg shadow-2xs hover:shadow-xs transition-all"
                        >
                            Duyệt
                        </button>
                        <button
                            type="button"
                            wire:click="$set('action', 'reject'); $set('reason', '');"
                            class="flex-1 bg-slate-50 hover:bg-slate-100 text-slate-500 text-[10px] font-bold px-3 py-2 rounded-lg border border-slate-200 transition-colors"
                        >
                            Từ chối
                        </button>
                    </div>
                </div>
            </x-ui.card>
        </div>
    </div>
</div>
