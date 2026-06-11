<?php

use App\Models\Post;
use App\Enums\ModerationStatus;
use App\Actions\Posts\ModerateOpportunity;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Illuminate\Support\Facades\Auth;

new #[Layout('layouts.app', ['shell' => 'admin'])] class extends Component {
    public Post $post;
    public string $action = 'approve';
    public string $reason = '';
    public string $adminNotes = '';
    public ?string $feedbackMessage = null;

    public function mount(Post $post): void
    {
        $this->post = $post->load(['user.profile', 'opportunity']);
    }

    public function submitAction(ModerateOpportunity $moderateOpportunity): void
    {
        $this->validate([
            'action' => 'required|in:approve,reject',
            'reason' => 'nullable|string|max:1000',
        ], [
            'reason.required' => 'Vui lòng nhập lý do xử lý.',
        ]);

        try {
            if ($this->action === 'approve') {
                $moderateOpportunity->approve(Auth::user(), $this->post);
                $this->feedbackMessage = 'Đã duyệt cơ hội việc làm thành công. Bài viết đã được công khai lên bảng tin.';
            } else {
                $moderateOpportunity->reject(Auth::user(), $this->post, $this->reason);
                $this->feedbackMessage = 'Đã từ chối cơ hội việc làm.';
            }

            $this->post->refresh();
            $this->reason = '';
            $this->adminNotes = '';
        } catch (\Exception $e) {
            $this->feedbackMessage = 'Lỗi: ' . $e->getMessage();
        }
    }
}; ?>

<div class="max-w-4xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    {{-- Back link --}}
    <a href="{{ route('admin.opportunities.queue') }}" class="inline-flex items-center gap-2 text-sm text-ue-text-muted hover:text-ue-brand mb-6 transition-colors font-semibold">
        <x-ui.icon name="arrow-left" size="xs" />
        Quay lại hàng chờ cơ hội
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
        {{-- Left column: Content preview --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Target Content Card --}}
            <div class="bg-white border border-slate-200 rounded-xl p-6 shadow-sm">
                <h3 class="text-sm font-bold text-slate-800 border-b border-slate-100 pb-3 mb-4 flex items-center gap-2">
                    <x-ui.icon name="briefcase" size="xs" class="text-ue-brand" />
                    Xem trước cơ hội việc làm
                </h3>

                @php
                    $opp = $post->opportunity;
                    $author = $post->user;
                @endphp

                {{-- Author info --}}
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 rounded-full bg-ue-brand-soft border border-slate-100 flex items-center justify-center font-bold text-ue-brand text-sm select-none">
                        {{ mb_substr($author->name, 0, 2) }}
                    </div>
                    <div>
                        <div class="font-bold text-slate-800">{{ $author->name }}</div>
                        <div class="text-xxs text-slate-400 font-medium">{{ $author->email }} · {{ Str::ucfirst($author->profile?->role_type ?? 'User') }}</div>
                    </div>
                    <span class="ml-auto text-slate-400 font-medium text-xs">{{ $post->created_at->diffForHumans() }}</span>
                </div>

                {{-- Post body --}}
                <div class="text-slate-700 text-sm whitespace-pre-wrap leading-relaxed border-t border-slate-200/60 pt-3 mb-4">
                    {{ $post->body }}
                </div>

                {{-- Opportunity metadata --}}
                @if ($opp)
                    <div class="bg-slate-50 rounded-xl p-4 border border-slate-100 space-y-3">
                        <div class="text-xs">
                            <span class="text-slate-400 block mb-0.5">Danh mục</span>
                            <span class="text-slate-800 font-bold">
                                {{ $opp->category === 'pedagogy' ? 'Sư phạm' : ($opp->category === 'non_pedagogy' ? 'Ngoài sư phạm' : 'Khác') }}
                            </span>
                        </div>
                    </div>
                @else
                    <div class="bg-red-50 border border-red-100 text-red-800 rounded-xl p-4 text-xs font-semibold text-center flex flex-col items-center gap-2">
                        <x-ui.icon name="alert-triangle" size="sm" class="text-red-600" />
                        Dữ liệu chi tiết cơ hội (Opportunity) không tồn tại hoặc bị thiếu.
                    </div>
                @endif


            </div>
        </div>

        {{-- Right column: Action Panel --}}
        <div class="space-y-4">
            {{-- Xử lý card --}}
            <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
                <h2 class="text-sm font-bold text-slate-900">Xử lý</h2>

                @if ($post->moderation_status === ModerationStatus::PENDING)
                    <div class="mt-4 space-y-3">
                        <div>
                            <select wire:model="action" class="w-full rounded-lg border-slate-200 text-sm">
                                <option value="approve">Phê duyệt</option>
                                <option value="reject">Từ chối</option>
                            </select>
                        </div>

                        <div>
                            <textarea wire:model="reason" rows="3"
                                class="w-full rounded-lg border-slate-200 text-sm focus:ring-2 focus:ring-ue-brand focus:border-ue-brand"
                                placeholder="Lý do xử lý (không bắt buộc)"></textarea>
                            @error('reason') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <textarea wire:model="adminNotes" rows="3"
                                class="w-full rounded-lg border-slate-200 text-sm focus:ring-2 focus:ring-ue-brand focus:border-ue-brand"
                                placeholder="Ghi chú nội bộ hoặc hướng dẫn bổ sung"></textarea>
                        </div>

                        <button wire:click="submitAction"
                            class="w-full rounded-lg bg-ue-brand px-4 py-2 text-sm font-semibold text-white hover:bg-ue-brand-dark transition-colors">
                            Xác nhận
                        </button>
                    </div>
                @else
                    <div class="mt-4">
                        <p class="text-xs text-slate-400 font-semibold italic text-center py-2 bg-slate-50 rounded-xl border border-slate-150">
                            Cơ hội việc làm này đã được xử lý.
                        </p>
                    </div>
                @endif
            </div>

            {{-- Review card --}}
            <div class="rounded-lg border border-slate-200 bg-white p-4 text-sm shadow-sm">
                <h2 class="font-bold text-slate-900">Thông tin duyệt</h2>
                <p class="mt-2 text-slate-500">Người duyệt: {{ $post->moderation_status === ModerationStatus::PENDING ? 'Chưa duyệt' : Auth::user()->name }}</p>
                <p class="mt-1 text-slate-500">Thời điểm: {{ $post->moderation_status === ModerationStatus::PENDING ? 'N/A' : $post->updated_at->format('d/m/Y H:i') }}</p>
                <p class="mt-1 text-slate-500">Hành động: {{ $post->moderation_status === ModerationStatus::APPROVED ? 'Đã phê duyệt' : ($post->moderation_status === ModerationStatus::REJECTED ? 'Đã từ chối' : ($post->moderation_status === ModerationStatus::EXPIRED ? 'Đã hết hạn' : 'N/A')) }}</p>
            </div>
        </div>
    </div>
</div>
