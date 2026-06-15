<?php

use Illuminate\Support\Facades\Auth;
use App\Models\NotificationPreference;
use Livewire\Volt\Component;

new class extends Component
{
    public ?string $feedbackMessage = null;
    public ?string $errorMessage = null;

    public bool $push_mentions = true;
    public bool $push_comments = true;
    public bool $push_connections = true;
    public bool $push_messages = true;
    public bool $push_system = true; // System default, can't disable fully
    
    public bool $email_mentions = true;
    public bool $email_comments = false;
    public bool $email_connections = true;
    public bool $email_messages = false;
    public bool $email_system = true; // Security & Verify, can't disable
    public bool $email_marketing = false;

    public function mount(): void
    {
        $user = Auth::user();
        $pref = $user->notificationPreference;
        if ($pref) {
            $this->push_mentions = (bool) ($pref->push_mentions ?? true);
            $this->push_comments = (bool) ($pref->push_comments ?? true);
            $this->push_connections = (bool) ($pref->push_connections ?? true);
            $this->push_messages = (bool) ($pref->push_messages ?? true);
            $this->push_system = (bool) ($pref->push_system ?? true);

            $this->email_mentions = (bool) ($pref->email_mentions ?? true);
            $this->email_comments = (bool) ($pref->email_comments ?? false);
            $this->email_connections = (bool) ($pref->email_connections ?? true);
            $this->email_messages = (bool) ($pref->email_messages ?? false);
            $this->email_system = (bool) ($pref->email_system ?? true);
            $this->email_marketing = (bool) ($pref->email_marketing ?? false);
        }
    }

    public function saveNotifications(): void
    {
        // System / Security notifications cannot be fully disabled
        $this->push_system = true;
        $this->email_system = true;

        try {
            $user = Auth::user();
            NotificationPreference::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'push_mentions' => $this->push_mentions,
                    'push_comments' => $this->push_comments,
                    'push_connections' => $this->push_connections,
                    'push_messages' => $this->push_messages,
                    'push_system' => $this->push_system,

                    'email_mentions' => $this->email_mentions,
                    'email_comments' => $this->email_comments,
                    'email_connections' => $this->email_connections,
                    'email_messages' => $this->email_messages,
                    'email_system' => $this->email_system,
                    'email_marketing' => $this->email_marketing,
                ]
            );
            
            $this->feedbackMessage = 'Đã cập nhật tùy chọn thông báo.';
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
        <h2 class="text-sm font-bold text-slate-800">Tùy chọn thông báo</h2>
        <p class="text-xxs text-slate-400 font-medium mt-0.5">Lựa chọn loại thông báo bạn muốn nhận qua ứng dụng và email.</p>
    </div>

    <form wire:submit.prevent="saveNotifications" x-on:submit="if (window.Alpine && window.Alpine.store('pwa')) window.Alpine.store('pwa').subscribeToPushNotifications()" class="space-y-6">
        <div class="bg-white border border-slate-150 rounded-2xl p-5 shadow-2xs space-y-6">
            {{-- Push --}}
            <div class="space-y-4">
                <h3 class="text-xxs font-bold text-slate-850 flex items-center gap-1.5 uppercase tracking-wider text-[9px]">
                    <x-ui.icon name="bell" size="xs" class="text-ue-brand" /> Push Notifications
                </h3>
                <div class="space-y-4 pt-2">
                    <div class="flex items-center justify-between gap-4">
                        <div class="flex-1 space-y-0.5">
                            <label for="push-mentions" class="text-xxs font-bold text-slate-800 block">Lượt nhắc & Gắn thẻ</label>
                            <span class="text-[10px] text-slate-400 block">Khi có người @nhắc bạn hoặc gắn thẻ bạn.</span>
                        </div>
                        <input type="checkbox" id="push-mentions" wire:model="push_mentions" class="h-4 w-4 rounded border-slate-200 text-ue-brand focus:ring-ue-brand" />
                    </div>
                    <div class="flex items-center justify-between gap-4">
                        <div class="flex-1 space-y-0.5">
                            <label for="push-comments" class="text-xxs font-bold text-slate-800 block">Bình luận mới</label>
                            <span class="text-[10px] text-slate-400 block">Bình luận trên bài viết của bạn.</span>
                        </div>
                        <input type="checkbox" id="push-comments" wire:model="push_comments" class="h-4 w-4 rounded border-slate-200 text-ue-brand focus:ring-ue-brand" />
                    </div>
                    <div class="flex items-center justify-between gap-4">
                        <div class="flex-1 space-y-0.5">
                            <label for="push-connections" class="text-xxs font-bold text-slate-800 block">Yêu cầu kết nối</label>
                            <span class="text-[10px] text-slate-400 block">Ai đó gửi hoặc chấp nhận yêu cầu kết nối.</span>
                        </div>
                        <input type="checkbox" id="push-connections" wire:model="push_connections" class="h-4 w-4 rounded border-slate-200 text-ue-brand focus:ring-ue-brand" />
                    </div>
                </div>
            </div>

            <hr class="border-slate-100">

            {{-- Email --}}
            <div class="space-y-4">
                <h3 class="text-xxs font-bold text-slate-850 flex items-center gap-1.5 uppercase tracking-wider text-[9px]">
                    <x-ui.icon name="mail" size="xs" class="text-ue-brand" /> Email Notifications
                </h3>
                <div class="space-y-4 pt-2">
                    <div class="flex items-center justify-between gap-4 opacity-60">
                        <div class="flex-1 space-y-0.5">
                            <label class="text-xxs font-bold text-slate-800 block flex items-center gap-2">
                                Thông báo bảo mật & Hệ thống
                                <x-ui.icon name="lock" size="2xs" class="text-slate-400" />
                            </label>
                            <span class="text-[10px] text-slate-400 block">Thông báo bảo vệ tài khoản, đăng nhập mới, quy định. (Bắt buộc)</span>
                        </div>
                        <input type="checkbox" disabled checked class="h-4 w-4 rounded border-slate-200 text-slate-400 focus:ring-0 cursor-not-allowed" />
                    </div>
                    <div class="flex items-center justify-between gap-4">
                        <div class="flex-1 space-y-0.5">
                            <label for="email-mentions" class="text-xxs font-bold text-slate-800 block">Lượt nhắc quan trọng</label>
                            <span class="text-[10px] text-slate-400 block">Email tổng hợp khi bạn bị nhắc đến nhiều.</span>
                        </div>
                        <input type="checkbox" id="email-mentions" wire:model="email_mentions" class="h-4 w-4 rounded border-slate-200 text-ue-brand focus:ring-ue-brand" />
                    </div>
                    <div class="flex items-center justify-between gap-4">
                        <div class="flex-1 space-y-0.5">
                            <label for="email-marketing" class="text-xxs font-bold text-slate-800 block">Bản tin học đường & Hoạt động</label>
                            <span class="text-[10px] text-slate-400 block">Cập nhật tính năng mới và bản tin cộng đồng HCMUE.</span>
                        </div>
                        <input type="checkbox" id="email-marketing" wire:model="email_marketing" class="h-4 w-4 rounded border-slate-200 text-ue-brand focus:ring-ue-brand" />
                    </div>
                </div>
            </div>
        </div>

        <div class="flex justify-end gap-3">
            <button type="submit" wire:loading.attr="disabled" class="bg-ue-brand hover:bg-ue-brand-hover disabled:opacity-50 text-white text-xxs font-bold px-4 py-2 rounded-xl shadow-xs transition-all flex items-center gap-2">
                <span wire:loading.remove wire:target="saveNotifications">Lưu thay đổi</span>
                <span wire:loading wire:target="saveNotifications">Đang lưu...</span>
            </button>
        </div>
    </form>
</div>
