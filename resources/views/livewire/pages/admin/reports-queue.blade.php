<?php

use App\Models\Report;
use App\Enums\ReportStatus;
use App\Enums\ReportReason;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new class extends Component {
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
        <h1 class="text-2xl font-bold text-slate-800">Kiểm duyệt báo cáo vi phạm</h1>
        <p class="text-sm text-slate-500 mt-1">Xử lý báo cáo nội dung vi phạm tiêu chuẩn cộng đồng trường học từ các UEers.</p>
    </div>

    {{-- Filters --}}
    <x-ui.card class="mb-6 p-4">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            {{-- Status --}}
            <div>
                <x-ui.label class="text-xs font-semibold text-slate-500" for="status">Trạng thái</x-ui.label>
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
                <x-ui.label class="text-xs font-semibold text-slate-500" for="targetType">Loại nội dung</x-ui.label>
                <x-ui.select wire:model.live="targetType" id="targetType" class="mt-1 h-9 text-xs py-1">
                    <option value="">-- Tất cả --</option>
                    <option value="post">Bài viết (Post)</option>
                    <option value="comment">Bình luận (Comment)</option>
                </x-ui.select>
            </div>

            {{-- Reason --}}
            <div>
                <x-ui.label class="text-xs font-semibold text-slate-500" for="reason">Lý do báo cáo</x-ui.label>
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
        <div class="bg-white border border-slate-200 rounded-2xl p-12 text-center shadow-sm">
            <div class="w-16 h-16 rounded-full bg-slate-50 border border-slate-100 flex items-center justify-center mx-auto mb-4">
                <x-ui.icon name="shield" size="lg" class="text-slate-400" />
            </div>
            <h3 class="text-base font-bold text-slate-800 mb-2">Hàng chờ sạch sẽ!</h3>
            <p class="text-sm text-slate-500 max-w-sm mx-auto">
                Không có báo cáo nào khớp với bộ lọc hiện tại. Cảm ơn các UEers đã bảo vệ cộng đồng an toàn.
            </p>
        </div>
    @else
        {{-- DESKTOP TABLE --}}
        <div class="hidden md:block bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-sm">
            <table class="min-w-full divide-y divide-slate-100 text-left text-xs">
                <thead class="bg-slate-50 text-slate-500 font-semibold uppercase tracking-wider">
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
                <tbody class="divide-y divide-slate-100 text-slate-700 font-medium">
                    @foreach ($reports as $report)
                        <tr>
                            <td class="px-6 py-4 text-slate-400">#{{ $report->id }}</td>
                            <td class="px-6 py-4">
                                <div class="font-bold text-slate-800">{{ $report->reporter->name }}</div>
                                <div class="text-xxs text-slate-400 font-medium">{{ $report->reporter->email }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-0.5 rounded-lg text-xxs font-bold uppercase {{ $report->target_type === 'post' ? 'bg-blue-50 text-blue-700 border border-blue-100' : 'bg-purple-50 text-purple-700 border border-purple-100' }}">
                                    {{ $report->target_type === 'post' ? 'Bài viết' : 'Bình luận' }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="font-semibold text-slate-800">
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
                            <td class="px-6 py-4 text-slate-400 font-medium">
                                {{ $report->created_at->format('H:i d/m/Y') }}
                            </td>
                            <td class="px-6 py-4">
                                @switch($report->status->value)
                                    @case('pending')
                                        <span class="px-2 py-0.5 rounded-full bg-yellow-50 text-yellow-700 font-bold border border-yellow-100 text-xxs">Chờ xử lý</span>
                                        @break
                                    @case('reviewed')
                                        <span class="px-2 py-0.5 rounded-full bg-blue-50 text-blue-700 font-bold border border-blue-100 text-xxs">Đang xem xét</span>
                                        @break
                                    @case('dismissed')
                                        <span class="px-2 py-0.5 rounded-full bg-slate-100 text-slate-500 font-bold border border-slate-200 text-xxs">Đã bỏ qua</span>
                                        @break
                                    @case('action_taken')
                                        <span class="px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 font-bold border border-emerald-100 text-xxs">Đã ẩn/Xử lý</span>
                                        @break
                                @endswitch
                            </td>
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('admin.reports.show', $report) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-ue-brand-soft text-ue-brand text-xs font-bold rounded-xl hover:bg-ue-brand hover:text-white transition-all shadow-sm">
                                    <x-ui.icon name="eye" size="xs" />
                                    <span>Chi tiết</span>
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- MOBILE CARDS --}}
        <div class="md:hidden space-y-4">
            @foreach ($reports as $report)
                <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-slate-400 text-xs font-bold">#{{ $report->id }}</span>
                        @switch($report->status->value)
                            @case('pending')
                                <span class="px-2 py-0.5 rounded-full bg-yellow-50 text-yellow-700 font-bold border border-yellow-100 text-xxs">Chờ xử lý</span>
                                @break
                            @case('reviewed')
                                <span class="px-2 py-0.5 rounded-full bg-blue-50 text-blue-700 font-bold border border-blue-100 text-xxs">Đang xem xét</span>
                                @break
                            @case('dismissed')
                                <span class="px-2 py-0.5 rounded-full bg-slate-100 text-slate-500 font-bold border border-slate-200 text-xxs">Đã bỏ qua</span>
                                @break
                            @case('action_taken')
                                <span class="px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 font-bold border border-emerald-100 text-xxs">Đã ẩn/Xử lý</span>
                                @break
                        @endswitch
                    </div>

                    <div class="space-y-1">
                        <div class="text-xs font-semibold text-slate-400">Người báo cáo:</div>
                        <div class="text-sm font-bold text-slate-800">{{ $report->reporter->name }}</div>
                        <div class="text-xxs text-slate-400 font-medium">{{ $report->reporter->email }}</div>
                    </div>

                    <div class="grid grid-cols-2 gap-4 pt-2 border-t border-slate-100 text-xs">
                        <div>
                            <span class="text-slate-400 block mb-0.5">Loại mục tiêu</span>
                            <span class="px-2 py-0.5 rounded-lg text-xxs font-bold uppercase {{ $report->target_type === 'post' ? 'bg-blue-50 text-blue-700 border border-blue-100' : 'bg-purple-50 text-purple-700 border border-purple-100' }}">
                                {{ $report->target_type === 'post' ? 'Bài viết' : 'Bình luận' }}
                            </span>
                        </div>
                        <div>
                            <span class="text-slate-400 block mb-0.5">Lý do</span>
                            <span class="font-semibold text-slate-800">
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

                    <div class="pt-3 border-t border-slate-100 flex items-center justify-between">
                        <span class="text-xxs text-slate-400 font-semibold">{{ $report->created_at->format('H:i d/m/Y') }}</span>
                        <a href="{{ route('admin.reports.show', $report) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-ue-brand-soft text-ue-brand text-xs font-bold rounded-xl hover:bg-ue-brand hover:text-white transition-all shadow-sm">
                            <x-ui.icon name="eye" size="xs" />
                            <span>Chi tiết</span>
                        </a>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Pagination --}}
        <div class="mt-6">
            {{ $reports->links() }}
        </div>
    @endif
</div>
