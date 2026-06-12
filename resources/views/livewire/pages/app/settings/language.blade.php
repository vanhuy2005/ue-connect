<?php

use Livewire\Volt\Component;

new class extends Component
{
    public string $language = 'vi';
    public ?string $feedbackMessage = null;

    public function saveLanguage(): void
    {
        // Future: Save to user profile or session
        $this->feedbackMessage = 'Đã lưu ngôn ngữ hiển thị.';
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

    <div>
        <h2 class="text-sm font-bold text-slate-800">Ngôn ngữ hiển thị</h2>
        <p class="text-xxs text-slate-400 font-medium mt-0.5">Thay đổi ngôn ngữ giao diện của hệ thống.</p>
    </div>

    <form wire:submit.prevent="saveLanguage" class="bg-white border border-slate-150 rounded-2xl p-5 shadow-2xs space-y-4">
        <div class="space-y-2">
            <label class="flex items-center gap-3 cursor-pointer p-3 border border-slate-100 rounded-xl hover:bg-slate-50 transition-colors">
                <input type="radio" wire:model="language" value="vi" class="h-4 w-4 border-slate-200 text-ue-brand focus:ring-ue-brand">
                <div class="flex-1">
                    <span class="text-xxs font-bold text-slate-800 block">Tiếng Việt (Mặc định)</span>
                    <span class="text-[10px] text-slate-400 block">Ngôn ngữ chính thức của UEConnect.</span>
                </div>
            </label>
            <label class="flex items-center gap-3 cursor-pointer p-3 border border-slate-100 rounded-xl opacity-60">
                <input type="radio" disabled class="h-4 w-4 border-slate-200 text-slate-400 focus:ring-0">
                <div class="flex-1">
                    <span class="text-xxs font-bold text-slate-800 block flex items-center gap-2">
                        English (US)
                        <span class="bg-slate-100 text-slate-500 text-[8px] font-bold px-1.5 py-0.5 rounded uppercase tracking-wider">Coming soon</span>
                    </span>
                    <span class="text-[10px] text-slate-400 block">Đang trong quá trình biên dịch.</span>
                </div>
            </label>
        </div>

        <div class="flex justify-end pt-2">
            <button type="submit" wire:loading.attr="disabled" class="bg-ue-brand hover:bg-ue-brand-hover disabled:opacity-50 text-white text-xxs font-bold px-4 py-2 rounded-xl shadow-xs transition-all flex items-center gap-2">
                <span wire:loading.remove wire:target="saveLanguage">Lưu cài đặt</span>
                <span wire:loading wire:target="saveLanguage">Đang lưu...</span>
            </button>
        </div>
    </form>
</div>
