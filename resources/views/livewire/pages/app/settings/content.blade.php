<?php

use Illuminate\Support\Facades\Auth;
use App\Models\UserContentPreference;
use Livewire\Volt\Component;

new class extends Component
{
    public ?string $feedbackMessage = null;
    public ?string $errorMessage = null;

    public bool $filter_sensitive = true;
    public bool $auto_play_video = true;
    public string $feed_sorting = 'algorithm';

    public function mount(): void
    {
        $user = Auth::user();
        $pref = $user->contentPreference;
        if ($pref) {
            $this->filter_sensitive = (bool) ($pref->filter_sensitive ?? true);
            $this->auto_play_video = (bool) ($pref->auto_play_video ?? true);
            $this->feed_sorting = $pref->feed_sorting ?? 'algorithm';
        }
    }

    public function saveContentPrefs(): void
    {
        try {
            $user = Auth::user();
            UserContentPreference::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'filter_sensitive' => $this->filter_sensitive,
                    'auto_play_video' => $this->auto_play_video,
                    'feed_sorting' => $this->feed_sorting,
                ]
            );
            
            $this->feedbackMessage = 'Đã lưu tùy chọn nội dung.';
        } catch (\Exception $e) {
            $this->errorMessage = 'Không thể lưu thay đổi. Vui lòng thử lại.';
        }
    }
}; ?>

<div class="space-y-6">
    {{-- Toast feedback --}}
    @if ($feedbackMessage)
        <div 
            x-data="{ show: true }" 
            x-show="show" 
            x-init="setTimeout(() => { show = false; $wire.set('feedbackMessage', null); }, 3000)"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-2"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 translate-y-2"
            class="fixed bottom-20 left-4 right-4 md:left-auto md:right-8 md:w-96 z-50 bg-slate-900 text-white rounded-xl shadow-xl px-4 py-3 border border-slate-800 flex items-center gap-3"
        >
            <x-ui.icon name="shield-check" size="sm" class="text-emerald-500 flex-shrink-0" />
            <span class="text-xxs font-semibold flex-1 leading-normal">{{ $feedbackMessage }}</span>
            <button @click="show = false" class="text-slate-400 hover:text-white transition-colors">
                <x-ui.icon name="x" size="xs" />
            </button>
        </div>
    @endif

    @if ($errorMessage)
        <div 
            x-data="{ show: true }" 
            x-show="show" 
            x-init="setTimeout(() => { show = false; $wire.set('errorMessage', null); }, 3000)"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-2"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 translate-y-2"
            class="fixed bottom-20 left-4 right-4 md:left-auto md:right-8 md:w-96 z-50 bg-red-900 text-white rounded-xl shadow-xl px-4 py-3 border border-red-800 flex items-center gap-3"
        >
            <x-ui.icon name="alert-triangle" size="sm" class="text-red-400 flex-shrink-0" />
            <span class="text-xxs font-semibold flex-1 leading-normal">{{ $errorMessage }}</span>
            <button @click="show = false" class="text-slate-400 hover:text-white transition-colors">
                <x-ui.icon name="x" size="xs" />
            </button>
        </div>
    @endif

    <div>
        <h2 class="text-sm font-bold text-slate-800">Tùy chọn nội dung</h2>
        <p class="text-xxs text-slate-400 font-medium mt-0.5">Tùy chỉnh trải nghiệm hiển thị nội dung trên bảng tin của bạn.</p>
    </div>

    <form wire:submit.prevent="saveContentPrefs" class="space-y-6">
        <div class="bg-white border border-slate-150 rounded-2xl p-5 shadow-2xs divide-y divide-slate-100">
            <div class="py-3 flex items-center justify-between gap-4">
                <div class="flex-1 space-y-0.5">
                    <label for="filter-sensitive" class="text-xxs font-bold text-slate-800 block">Lọc nội dung nhạy cảm</label>
                    <span class="text-[10px] text-slate-400 block">Làm mờ các hình ảnh và nội dung có thể gây khó chịu trước khi hiển thị.</span>
                </div>
                <input type="checkbox" id="filter-sensitive" wire:model="filter_sensitive" class="h-4 w-4 rounded border-slate-200 text-ue-brand focus:ring-ue-brand" />
            </div>
            
            <div class="py-3 flex items-center justify-between gap-4">
                <div class="flex-1 space-y-0.5">
                    <label for="auto-play" class="text-xxs font-bold text-slate-800 block">Tự động phát Video</label>
                    <span class="text-[10px] text-slate-400 block">Video sẽ tự động phát khi bạn cuộn qua trên bảng tin.</span>
                </div>
                <input type="checkbox" id="auto-play" wire:model="auto_play_video" class="h-4 w-4 rounded border-slate-200 text-ue-brand focus:ring-ue-brand" />
            </div>

            <div class="py-3 space-y-2">
                <div class="space-y-0.5">
                    <label class="text-xxs font-bold text-slate-800 block">Sắp xếp bảng tin (Feed)</label>
                    <span class="text-[10px] text-slate-400 block">Chọn cách hệ thống hiển thị bài viết trên trang chủ.</span>
                </div>
                <div class="flex gap-4 pt-1">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" wire:model="feed_sorting" value="algorithm" class="h-4 w-4 border-slate-200 text-ue-brand focus:ring-ue-brand">
                        <span class="text-xxs font-semibold text-slate-700">Theo thuật toán đề xuất</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" wire:model="feed_sorting" value="chronological" class="h-4 w-4 border-slate-200 text-ue-brand focus:ring-ue-brand">
                        <span class="text-xxs font-semibold text-slate-700">Mới nhất xếp trước</span>
                    </label>
                </div>
            </div>
        </div>

        <div class="flex justify-end gap-3">
            <button type="submit" wire:loading.attr="disabled" class="bg-ue-brand hover:bg-ue-brand-hover disabled:opacity-50 text-white text-xxs font-bold px-4 py-2 rounded-xl shadow-xs transition-all flex items-center gap-2">
                <span wire:loading.remove wire:target="saveContentPrefs">Lưu thay đổi</span>
                <span wire:loading wire:target="saveContentPrefs">Đang lưu...</span>
            </button>
        </div>
    </form>
</div>
