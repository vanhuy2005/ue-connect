<?php

use App\Actions\Community\ReviewCommunitySuggestionAction;
use App\Enums\CommunitySuggestionStatus;
use App\Models\CommunitySuggestion;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    public string $status = 'submitted';

    public bool $showReviewModal = false;
    public ?int $reviewId = null;
    public string $reviewAction = 'approve';
    public string $reviewReason = '';
    public string $reviewInstruction = '';
    public string $reviewCommunityName = '';

    public function mount(): void
    {
        $this->authorize('manage_communities');
    }

    public function getSuggestionsProperty()
    {
        $query = CommunitySuggestion::with('submitter', 'convertedCommunity')
            ->latest();

        if ($this->status && $this->status !== 'all') {
            $query->where('status', $this->status);
        }

        return $query->paginate(20);
    }

    public function getStatusesProperty(): array
    {
        return [
            ['value' => '', 'label' => 'Tất cả'],
            ['value' => 'submitted', 'label' => 'Đã gửi'],
            ['value' => 'under_review', 'label' => 'Đang xem xét'],
            ['value' => 'need_more_information', 'label' => 'Cần thêm thông tin'],
            ['value' => 'approved', 'label' => 'Đã chấp thuận'],
            ['value' => 'rejected', 'label' => 'Đã từ chối'],
            ['value' => 'converted_to_community', 'label' => 'Đã tạo cộng đồng'],
        ];
    }

    public function openReview(int $id, string $action): void
    {
        $this->authorize('manage_communities');

        $this->reviewId = $id;
        $this->reviewAction = $action;
        $this->showReviewModal = true;
    }

    public function confirmReview(ReviewCommunitySuggestionAction $action): void
    {
        $this->authorize('manage_communities');

        $this->validate([
            'reviewReason' => $this->reviewAction === 'reject' ? ['required', 'string', 'min:5'] : ['nullable', 'string'],
        ]);

        $suggestion = CommunitySuggestion::findOrFail($this->reviewId);

        $action->execute(auth()->user(), $suggestion, [
            'action' => $this->reviewAction,
            'reason' => $this->reviewReason,
            'instruction' => $this->reviewInstruction,
            'community_name' => $this->reviewCommunityName,
        ]);

        $this->showReviewModal = false;
        $this->reset(['reviewId', 'reviewAction', 'reviewReason', 'reviewInstruction', 'reviewCommunityName']);
        $this->dispatch('notify', type: 'success', message: 'Đề xuất đã được xử lý.');
    }
};
?>

<div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-ue-text">Đề xuất cộng đồng</h1>
        <p class="text-sm text-ue-text-secondary mt-1">Xét duyệt các đề xuất thành lập cộng đồng/CLB từ sinh viên.</p>
    </div>

    {{-- Status Filter --}}
    <div class="mb-4 flex flex-wrap gap-2">
        @foreach ($this->statuses as $s)
        <button wire:click="$set('status', '{{ $s['value'] }}')"
            class="px-3 py-1.5 rounded-full text-xs font-semibold border transition
                {{ $status === $s['value'] ? 'bg-ue-brand text-white border-ue-brand' : 'border-ue-border text-ue-text-muted hover:bg-ue-surface-hover' }}">
            {{ $s['label'] }}
        </button>
        @endforeach
    </div>

    {{-- List --}}
    <div class="space-y-4">
        @forelse ($this->suggestions as $suggestion)
        @php
            $sc = match ($suggestion->status?->value) {
                'submitted' => 'blue', 'under_review' => 'yellow', 'approved' => 'green',
                'rejected' => 'red', 'converted_to_community' => 'teal',
                'need_more_information' => 'orange', default => 'gray',
            };
        @endphp
        <x-ui.card>
            <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 flex-wrap mb-1">
                        <p class="font-bold text-ue-text">{{ $suggestion->suggested_name }}</p>
                        <span class="px-2 py-0.5 bg-{{ $sc }}-100 text-{{ $sc }}-800 rounded-full text-xs font-semibold">
                            {{ $suggestion->status?->label() }}
                        </span>
                    </div>
                    <p class="text-xs text-ue-text-muted mb-1">
                        {{ $suggestion->community_type }} · {{ $suggestion->submitter?->name }} · {{ $suggestion->created_at->format('d/m/Y') }}
                    </p>
                    <p class="text-sm text-ue-text-secondary line-clamp-2">{{ $suggestion->purpose }}</p>
                    @if ($suggestion->admin_instruction)
                    <p class="text-xs text-yellow-700 bg-yellow-50 rounded px-2 py-1 mt-2">{{ $suggestion->admin_instruction }}</p>
                    @endif
                </div>
                @if (in_array($suggestion->status?->value, ['submitted', 'under_review', 'need_more_information']))
                <div class="flex flex-wrap gap-2 md:flex-shrink-0">
                    <button wire:click="openReview({{ $suggestion->id }}, 'create_community')"
                        class="px-3 py-1.5 bg-teal-600 text-white rounded text-xs font-semibold hover:bg-teal-700">Tạo cộng đồng</button>
                    <button wire:click="openReview({{ $suggestion->id }}, 'need_more_information')"
                        class="px-3 py-1.5 bg-yellow-500 text-white rounded text-xs font-semibold hover:bg-yellow-600">Cần thêm TT</button>
                    <button wire:click="openReview({{ $suggestion->id }}, 'reject')"
                        class="px-3 py-1.5 bg-red-100 text-red-700 rounded text-xs font-semibold hover:bg-red-200">Từ chối</button>
                    <button wire:click="openReview({{ $suggestion->id }}, 'mark_duplicate')"
                        class="px-3 py-1.5 bg-gray-100 text-gray-700 rounded text-xs font-semibold hover:bg-gray-200">Trùng lặp</button>
                </div>
                @endif
            </div>
        </x-ui.card>
        @empty
        <div class="py-12 text-center text-ue-text-muted">Không có đề xuất nào.</div>
        @endforelse
        {{ $this->suggestions->links('pagination::simple-tailwind') }}
    </div>

    {{-- Review Modal --}}
    @if ($showReviewModal)
    <div class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
        <div class="bg-ue-surface rounded-xl shadow-2xl w-full max-w-md p-6 space-y-4">
            <h3 class="text-lg font-bold text-ue-text">
                @if ($reviewAction === 'create_community') Tạo cộng đồng từ đề xuất
                @elseif ($reviewAction === 'approve') Chấp thuận đề xuất
                @elseif ($reviewAction === 'reject') Từ chối đề xuất
                @elseif ($reviewAction === 'need_more_information') Yêu cầu thêm thông tin
                @else Đánh dấu trùng lặp
                @endif
            </h3>

            @if ($reviewAction === 'create_community')
            <div>
                <label class="block text-sm font-semibold text-ue-text mb-1">Tên cộng đồng</label>
                <input type="text" wire:model="reviewCommunityName"
                    class="w-full px-3 py-2 border border-ue-border rounded-lg text-sm"
                    placeholder="Để trống để dùng tên đề xuất">
            </div>
            @endif

            @if ($reviewAction === 'need_more_information')
            <div>
                <label class="block text-sm font-semibold text-ue-text mb-1">Hướng dẫn <span class="text-red-500">*</span></label>
                <textarea wire:model="reviewInstruction" rows="3"
                    class="w-full px-3 py-2 border border-ue-border rounded-lg text-sm"
                    placeholder="Hướng dẫn cho người đề xuất..."></textarea>
            </div>
            @endif

            <div>
                <label class="block text-sm font-semibold text-ue-text mb-1">
                    Lý do @if ($reviewAction === 'reject') <span class="text-red-500">*</span> @endif
                </label>
                <textarea wire:model="reviewReason" rows="2"
                    class="w-full px-3 py-2 border border-ue-border rounded-lg text-sm"
                    placeholder="Ghi chú nội bộ..."></textarea>
                @error('reviewReason') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="flex gap-3 justify-end">
                <button wire:click="$set('showReviewModal', false)"
                    class="px-4 py-2 border border-ue-border rounded-lg text-sm hover:bg-ue-surface-hover">Hủy</button>
                <button wire:click="confirmReview"
                    class="px-4 py-2 bg-ue-brand text-white rounded-lg text-sm font-semibold hover:bg-opacity-90">Xác nhận</button>
            </div>
        </div>
    </div>
    @endif

</div>
