<?php

use App\Models\Report;
use App\Enums\ReportStatus;
use App\Enums\ReportReason;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new #[Layout('layouts.app', ['shell' => 'admin'])] class extends Component {
    use WithPagination;

    public string $status = 'pending'; // default to pending
    public string $targetType = '';
    public string $reason = '';

    protected $queryString = [
        'status' => ['except' => ''],
        'targetType' => ['except' => ''],
        'reason' => ['except' => ''],
    ];

    public function updatingStatus(): void
    {
        $this->resetPage();
    }

    public function updatingTargetType(): void
    {
        $this->resetPage();
    }

    public function updatingReason(): void
    {
        $this->resetPage();
    }

    public function getReportsProperty()
    {
        $query = Report::with(['reporter']);

        if ($this->status) {
            $query->where('status', $this->status);
        }

        if ($this->targetType) {
            $query->where('target_type', $this->targetType);
        }

        if ($this->reason) {
            $query->where('reason', $this->reason);
        }

        return $query->latest()->paginate(15);
    }
}; ?>

<div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    {{-- Header --}}
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-ue-text">Kiểm duyệt báo cáo vi phạm</h1>
        <p class="text-sm text-ue-text-secondary mt-1">Xử lý báo cáo nội dung vi phạm tiêu chuẩn cộng đồng trường học từ các UEers.</p>
    </div>

    {{-- Filters --}}
    <x-ui.card class="mb-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            {{-- Status --}}
            <div>
                <x-ui.label class="text-xs" for="status">Trạng thái</x-ui.label>
                <x-ui.select wire:model.live="status" id="status" class="mt-1 h-9 text-xs py-1">
                    <option value="">-- Tất cả --</option>
                    <option value="pending">Chờ xử lý (Mới)</option>
                    <option value="reviewed">Đang xem xét</option>
                    <option value="dismissed">Đã bỏ qua</option>
                    <option value="action_taken">Đã ẩn/Xử lý</option>
                </x-ui.select>
            </div>

            {{-- Target Type --}}
            <div>
                <x-ui.label class="text-xs" for="targetType">Loại nội dung</x-ui.label>
                <x-ui.select wire:model.live="targetType" id="targetType" class="mt-1 h-9 text-xs py-1">
                    <option value="">-- Tất cả --</option>
                    <option value="post">Bài viết (Post)</option>
                    <option value="comment">Bình luận (Comment)</option>
                </x-ui.select>
            </div>

            {{-- Reason --}}
            <div>
                <x-ui.label class="text-xs" for="reason">Lý do báo cáo</x-ui.label>
                <x-ui.select wire:model.live="reason" id="reason" class="mt-1 h-9 text-xs py-1">
                    <option value="">-- Tất cả --</option>
                    <option value="spam">Tin rác / Spam</option>
                    <option value="harassment">Quấy rối / Công kích</option>
                    <option value="inappropriate_content">Nội dung không phù hợp</option>
                    <option value="misinformation">Thông tin sai lệch</option>
                    <option value="privacy_violation">Xâm phạm quyền riêng tư</option>
                    <option value="other">Lý do khác</option>
                </x-ui.select>
            </div>
        </div>
    </x-ui.card>

    {{-- Grid / Table --}}
    @php $reports = $this->reports; @endphp
    
    @if ($reports->isEmpty())
        {{-- EMPTY STATE --}}
        <x-ui.card class="p-12 text-center" variant="admin">
            <div class="w-16 h-16 rounded-full bg-ue-surface-subtle border border-ue-border flex items-center justify-center mx-auto mb-4">
                <x-ui.icon name="shield" size="lg" class="text-ue-text-muted" />
            </div>
            <h3 class="text-base font-bold text-ue-text mb-2">Hàng chờ sạch sẽ!</h3>
            <p class="text-sm text-ue-text-muted max-w-sm mx-auto">
                Không có báo cáo nào khớp với bộ lọc hiện tại. Cảm ơn các UEers đã bảo vệ cộng đồng an toàn.
            </p>
        </x-ui.card>
    @else
        {{-- DESKTOP TABLE --}}
        <x-ui.card padding="none" class="hidden md:block overflow-hidden" variant="admin">
            <table class="min-w-full divide-y divide-ue-border text-left text-xs">
                <thead class="bg-ue-surface-subtle text-ue-text-muted font-bold uppercase tracking-wider">
                    <tr>
                        <th class="px-6 py-4">ID</th>
                        <th class="px-6 py-4">Người báo cáo</th>
                        <th class="px-6 py-4">Loại mục tiêu</th>
                        <th class="px-6 py-4">Lý do</th>
                        <th class="px-6 py-4">Ngày báo cáo</th>
                        <th class="px-6 py-4">Trạng thái</th>
                        <th class="px-6 py-4 text-right">Thao tác</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-ue-border bg-ue-surface text-ue-text font-medium text-sm">
                    @foreach ($reports as $report)
                        @php
                            $badgeVariant = match($report->status->value) {
                                'pending' => 'pending',
                                'reviewed' => 'info',
                                'dismissed' => 'neutral',
                                'action_taken' => 'success',
                                default => 'neutral',
                            };
                            $targetBadgeVariant = $report->target_type === 'post' ? 'info' : 'neutral';
                        @endphp
                        <tr class="hover:bg-ue-surface-hover transition-colors">
                            <td class="px-6 py-4 text-ue-text-muted">#{{ $report->id }}</td>
                            <td class="px-6 py-4">
                                <div class="font-bold text-ue-text">{{ $report->reporter->name }}</div>
                                <div class="text-xs text-ue-text-muted mt-0.5">{{ $report->reporter->email }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <x-ui.badge :variant="$targetBadgeVariant" size="sm">
                                    {{ $report->target_type === 'post' ? 'Bài viết' : 'Bình luận' }}
                                </x-ui.badge>
                            </td>
                            <td class="px-6 py-4">
                                <span class="font-semibold text-ue-text">
                                    @switch($report->reason->value)
                                        @case('spam') Tin rác @break
                                        @case('harassment') Quấy rối / Công kích @break
                                        @case('inappropriate_content') Nội dung không phù hợp @break
                                        @case('misinformation') Tin sai lệch @break
                                        @case('privacy_violation') Riêng tư @break
                                        @default Khác
                                    @endswitch
                                </span>
                            </td>
                            <td class="px-6 py-4 text-ue-text-muted whitespace-nowrap text-xs">
                                {{ $report->created_at->format('H:i d/m/Y') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <x-ui.badge :variant="$badgeVariant">
                                    {{ match($report->status->value) {
                                        'pending' => 'Chờ xử lý',
                                        'reviewed' => 'Đang xem xét',
                                        'dismissed' => 'Đã bỏ qua',
                                        'action_taken' => 'Đã xử lý',
                                        default => $report->status->value,
                                    } }}
                                </x-ui.badge>
                            </td>
                            <td class="px-6 py-4 text-right whitespace-nowrap">
                                <x-ui.button href="{{ route('admin.reports.show', $report) }}" variant="secondary" size="sm" icon="eye">
                                    Chi tiết
                                </x-ui.button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </x-ui.card>

        {{-- MOBILE CARDS --}}
        <div class="md:hidden space-y-4">
            @foreach ($reports as $report)
                @php
                    $badgeVariant = match($report->status->value) {
                        'pending' => 'pending',
                        'reviewed' => 'info',
                        'dismissed' => 'neutral',
                        'action_taken' => 'success',
                        default => 'neutral',
                    };
                    $targetBadgeVariant = $report->target_type === 'post' ? 'info' : 'neutral';
                @endphp
                <x-ui.card class="space-y-3" variant="admin">
                    <div class="flex items-center justify-between">
                        <span class="text-ue-text-muted text-xs font-bold">#{{ $report->id }}</span>
                        <x-ui.badge :variant="$badgeVariant">
                            {{ match($report->status->value) {
                                'pending' => 'Chờ xử lý',
                                'reviewed' => 'Đang xem xét',
                                'dismissed' => 'Đã bỏ qua',
                                'action_taken' => 'Đã xử lý',
                                default => $report->status->value,
                            } }}
                        </x-ui.badge>
                    </div>

                    <div class="space-y-1">
                        <div class="text-[10px] font-semibold uppercase tracking-wider text-ue-text-muted">Người báo cáo:</div>
                        <div class="text-sm font-bold text-ue-text">{{ $report->reporter->name }}</div>
                        <div class="text-xs text-ue-text-muted">{{ $report->reporter->email }}</div>
                    </div>

                    <div class="grid grid-cols-2 gap-4 pt-2 border-t border-ue-border text-xs">
                        <div>
                            <span class="text-ue-text-muted block mb-1 font-semibold">Loại mục tiêu</span>
                            <x-ui.badge :variant="$targetBadgeVariant" size="sm">
                                {{ $report->target_type === 'post' ? 'Bài viết' : 'Bình luận' }}
                            </x-ui.badge>
                        </div>
                        <div>
                            <span class="text-ue-text-muted block mb-1 font-semibold">Lý do</span>
                            <span class="font-semibold text-ue-text">
                                @switch($report->reason->value)
                                    @case('spam') Tin rác @break
                                    @case('harassment') Quấy rối / Công kích @break
                                    @case('inappropriate_content') Nội dung không phù hợp @break
                                    @case('misinformation') Tin sai lệch @break
                                    @case('privacy_violation') Riêng tư @break
                                    @default Khác
                                @endswitch
                            </span>
                        </div>
                    </div>

                    <div class="pt-3 border-t border-ue-border flex items-center justify-between">
                        <span class="text-xs text-ue-text-muted">{{ $report->created_at->format('H:i d/m/Y') }}</span>
                        <x-ui.button href="{{ route('admin.reports.show', $report) }}" variant="secondary" size="sm" icon="eye">
                            Chi tiết
                        </x-ui.button>
                    </div>
                </x-ui.card>
            @endforeach
        </div>

        {{-- Pagination --}}
        <div class="mt-6">
            {{ $reports->links() }}
        </div>
    @endif
</div>
