<?php

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use App\Actions\Connections\AcceptGreeting;
use App\Actions\Connections\DeclineGreeting;
use App\Enums\GreetingStatus;
use App\Support\Navigation\UserNavigationMetrics;

new #[Layout('layouts.app')] class extends Component
{
    public string $activeTab = 'all'; // all, requests, messages, system
    public ?string $feedbackMessage = null;

    /**
     * Mark a single notification as read and redirect to its action URL.
     */
    public function readAndRedirect(string $id)
    {
        try {
            $notification = Auth::user()->notifications()->findOrFail($id);
            $notification->markAsRead();
            app(UserNavigationMetrics::class)->forgetForUser(Auth::id());
            
            $actionUrl = $notification->data['action_url'] ?? route('dashboard');
            return redirect()->to($actionUrl);
        } catch (\Exception $e) {
            $this->feedbackMessage = $e->getMessage();
        }
    }

    /**
     * Mark a single notification as read without redirecting.
     */
    public function markAsRead(string $id): void
    {
        try {
            $notification = Auth::user()->unreadNotifications()->findOrFail($id);
            $notification->markAsRead();
            app(UserNavigationMetrics::class)->forgetForUser(Auth::id());
            $this->feedbackMessage = 'Đã đánh dấu là đã đọc.';
        } catch (\Exception $e) {
            $this->feedbackMessage = $e->getMessage();
        }
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllAsRead(): void
    {
        try {
            Auth::user()->unreadNotifications->markAsRead();
            app(UserNavigationMetrics::class)->forgetForUser(Auth::id());
            $this->feedbackMessage = 'Đã đánh dấu tất cả thông báo là đã đọc.';
        } catch (\Exception $e) {
            $this->feedbackMessage = $e->getMessage();
        }
    }

    /**
     * Accept a connection request inline.
     */
    public function acceptGreeting(int $greetingId, AcceptGreeting $acceptGreeting): void
    {
        try {
            $greeting = \App\Models\Greeting::findOrFail($greetingId);
            $acceptGreeting->execute(Auth::user(), $greeting);
            $this->feedbackMessage = 'Đã chấp nhận lời mời kết nối thành công.';
        } catch (\Exception $e) {
            $this->feedbackMessage = $e->getMessage();
        }
    }

    /**
     * Decline a connection request inline.
     */
    public function declineGreeting(int $greetingId, DeclineGreeting $declineGreeting): void
    {
        try {
            $greeting = \App\Models\Greeting::findOrFail($greetingId);
            $declineGreeting->execute(Auth::user(), $greeting);
            $this->feedbackMessage = 'Đã từ chối lời mời kết nối.';
        } catch (\Exception $e) {
            $this->feedbackMessage = $e->getMessage();
        }
    }

    public function with(): array
    {
        $user = Auth::user();
        
        $query = $user->notifications();
        
        if ($this->activeTab === 'requests') {
            $query->where('data->type', 'greeting_received');
        } elseif ($this->activeTab === 'messages') {
            $query->where('data->type', 'message_received');
        } elseif ($this->activeTab === 'system') {
            $query->whereNotIn('data->type', ['greeting_received', 'message_received']);
        }

        $notifications = $query->reorder('created_at', 'desc')->paginate(15);

        return [
            'notifications' => $notifications,
            'unreadCount' => $user->unreadNotifications()->count(),
        ];
    }
}; ?>

@push('scripts')
    @vite('resources/js/realtime.js')
@endpush

<div wire:poll.visible.30s class="py-6 px-4 max-w-4xl mx-auto space-y-6">
    {{-- Header --}}
    <div class="flex flex-col gap-1.5 border-b border-slate-100 pb-4">
        <h1 class="text-xl font-bold text-slate-800 tracking-tight">Hoạt động</h1>
        <p class="text-xs text-slate-400 font-medium">Theo dõi các cập nhật về xác thực tài khoản, lời mời kết nối và tin nhắn mới trong cộng đồng của bạn.</p>
    </div>

    {{-- Horizontally Scrollable Tabs exactly like Threads --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between border-b border-slate-150 gap-3 pb-2">
        <div class="flex gap-2 overflow-x-auto pb-1 scrollbar-none flex-1">
            <button
                type="button"
                wire:click="$set('activeTab', 'all')"
                class="px-4 py-1.5 rounded-full text-xxs font-bold transition-all border whitespace-nowrap
                       {{ $activeTab === 'all' ? 'bg-ue-brand-soft text-ue-brand-active border-ue-brand-border' : 'bg-white text-slate-600 border-slate-200 hover:border-slate-350 hover:bg-slate-50' }}"
            >
                Tất cả
            </button>
            <button
                type="button"
                wire:click="$set('activeTab', 'requests')"
                class="px-4 py-1.5 rounded-full text-xxs font-bold transition-all border whitespace-nowrap relative
                       {{ $activeTab === 'requests' ? 'bg-ue-brand-soft text-ue-brand-active border-ue-brand-border' : 'bg-white text-slate-600 border-slate-200 hover:border-slate-350 hover:bg-slate-50' }}"
            >
                Yêu cầu
            </button>
            <button
                type="button"
                wire:click="$set('activeTab', 'messages')"
                class="px-4 py-1.5 rounded-full text-xxs font-bold transition-all border whitespace-nowrap
                       {{ $activeTab === 'messages' ? 'bg-ue-brand-soft text-ue-brand-active border-ue-brand-border' : 'bg-white text-slate-600 border-slate-200 hover:border-slate-350 hover:bg-slate-50' }}"
            >
                Tin nhắn
            </button>
            <button
                type="button"
                wire:click="$set('activeTab', 'system')"
                class="px-4 py-1.5 rounded-full text-xxs font-bold transition-all border whitespace-nowrap
                       {{ $activeTab === 'system' ? 'bg-ue-brand-soft text-ue-brand-active border-ue-brand-border' : 'bg-white text-slate-600 border-slate-200 hover:border-slate-350 hover:bg-slate-50' }}"
            >
                Hệ thống
            </button>
        </div>

        @if ($unreadCount > 0)
            <button
                type="button"
                wire:click="markAllAsRead"
                wire:loading.attr="disabled"
                wire:target="markAllAsRead"
                class="text-xxs font-bold text-ue-brand hover:text-ue-brand-dark transition-colors self-start sm:self-center mb-2 sm:mb-0 flex items-center gap-1"
            >
                <span wire:loading.remove wire:target="markAllAsRead" class="flex items-center gap-1">
                    <x-ui.icon name="check-circle" size="xs" />
                    Đánh dấu tất cả đã đọc
                </span>
                <span wire:loading wire:target="markAllAsRead" class="flex items-center gap-1">
                    <span class="ue-spinner"></span>
                    Đang xử lý...
                </span>
            </button>
        @endif
    </div>

    {{-- Feedback Toast --}}
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
            <x-ui.icon name="info" size="sm" class="text-ue-brand flex-shrink-0" />
            <span class="text-xxs font-semibold flex-1 leading-normal">{{ $feedbackMessage }}</span>
            <button @click="show = false" class="text-slate-400 hover:text-white transition-colors">
                <x-ui.icon name="x" size="xs" />
            </button>
        </div>
    @endif

    {{-- List Container --}}
    <div class="space-y-3">
        @forelse ($notifications as $notification)
            @php
                $isUnread = is_null($notification->read_at);
                $type = $notification->data['type'] ?? 'default';
                $title = $notification->data['title'] ?? 'Thông báo hệ thống';
                $body = $notification->data['body'] ?? '';
                
                // Fetch greeting inline for received greetings
                $greeting = null;
                if ($type === 'greeting_received' && isset($notification->data['greeting_id'])) {
                    $greeting = \App\Models\Greeting::find($notification->data['greeting_id']);
                }
            @endphp
            <div 
                class="bg-white border rounded-2xl p-4 flex items-start justify-between gap-4 transition-all duration-sm hover:border-slate-350 hover:shadow-2xs
                       {{ $isUnread ? 'border-ue-brand-soft bg-ue-brand-soft/5' : 'border-slate-150' }}"
            >
                <div
                    class="flex items-start gap-3 min-w-0 flex-1 cursor-pointer"
                    wire:click="readAndRedirect('{{ $notification->id }}')"
                    wire:loading.class="opacity-60 pointer-events-none"
                    wire:target="readAndRedirect('{{ $notification->id }}')"
                >
                    {{-- Avatar with Overlap Badge --}}
                    <div class="relative flex-shrink-0">
                        <span class="inline-flex items-center justify-center bg-ue-brand-soft border border-ue-border w-10 h-10 text-xs font-semibold rounded-full overflow-hidden">
                            @if (!empty($notification->data['sender_name']))
                                <span class="font-semibold text-ue-brand select-none leading-none">
                                    {{ mb_strtoupper(mb_substr($notification->data['sender_name'], 0, 2)) }}
                                </span>
                            @else
                                <span class="font-semibold text-ue-brand select-none leading-none">UE</span>
                            @endif
                        </span>
                        
                        {{-- Overlap Badge --}}
                        <div class="absolute -bottom-1 -right-1 w-5 h-5 rounded-full border-2 border-white flex items-center justify-center text-[10px] shadow-xs
                                    {{ $type === 'message_received' ? 'bg-blue-500 text-white' : ($type === 'greeting_received' ? 'bg-purple-600 text-white' : 'bg-slate-400 text-white') }}">
                            @if ($type === 'message_received')
                                <x-ui.icon name="message-square" size="2xs" />
                            @elseif ($type === 'greeting_received')
                                <x-ui.icon name="user-plus" size="2xs" />
                            @else
                                <x-ui.icon name="bell" size="2xs" />
                            @endif
                        </div>
                    </div>

                    {{-- Text Content --}}
                    <div class="min-w-0 flex-1 space-y-1">
                        <div class="flex items-center gap-1.5 flex-wrap">
                            <h3 class="text-xs font-bold leading-tight {{ $isUnread ? 'text-slate-900' : 'text-slate-700' }}">
                                {{ $notification->data['sender_name'] ?? 'UEConnect' }}
                            </h3>
                            <span class="text-slate-300 select-none text-[9px] font-semibold">·</span>
                            <span class="text-[9px] text-slate-450 font-medium lowercase tracking-wide">
                                {{ $notification->created_at->diffForHumans() }}
                            </span>
                            @if ($isUnread)
                                <span class="w-1.5 h-1.5 bg-ue-brand rounded-full flex-shrink-0"></span>
                            @endif
                        </div>
                        
                        <p class="text-xxs font-medium leading-normal text-slate-400">
                            {{ $title }}
                        </p>
                        
                        <p class="text-xxs font-medium leading-relaxed {{ $isUnread ? 'text-slate-750' : 'text-slate-500' }}">
                            {{ $body }}
                        </p>
                    </div>
                </div>

                {{-- Action / Mark Read Button --}}
                @if ($greeting && $greeting->status === \App\Enums\GreetingStatus::PENDING)
                    <div class="flex items-center gap-2 flex-shrink-0 self-center">
                        <button
                            type="button"
                            wire:click="declineGreeting({{ $greeting->id }})"
                            wire:loading.attr="disabled"
                            wire:target="declineGreeting({{ $greeting->id }})"
                            class="bg-slate-50 hover:bg-slate-100 text-slate-500 text-[10px] font-bold px-3 py-1.5 rounded-lg border border-slate-200 transition-colors disabled:opacity-60"
                        >
                            Ẩn
                        </button>
                        <button
                            type="button"
                            wire:click="acceptGreeting({{ $greeting->id }})"
                            wire:loading.attr="disabled"
                            wire:target="acceptGreeting({{ $greeting->id }})"
                            class="bg-ue-brand hover:bg-ue-brand-hover text-white text-[10px] font-bold px-3 py-1.5 rounded-lg shadow-2xs hover:shadow-xs transition-all disabled:opacity-60"
                        >
                            <span wire:loading.remove wire:target="acceptGreeting({{ $greeting->id }})">Xác nhận</span>
                            <span wire:loading wire:target="acceptGreeting({{ $greeting->id }})">Đang xử lý...</span>
                        </button>
                    </div>
                @else
                    @if ($isUnread)
                        <button
                            type="button"
                            wire:click="markAsRead('{{ $notification->id }}')"
                            wire:loading.attr="disabled"
                            wire:target="markAsRead('{{ $notification->id }}')"
                            class="p-1.5 text-slate-400 hover:text-ue-brand hover:bg-slate-50 rounded-lg transition-colors flex-shrink-0 self-center disabled:opacity-50"
                            aria-label="Đánh dấu là đã đọc"
                            title="Đánh dấu là đã đọc"
                        >
                            <x-ui.icon name="check" size="xs" />
                        </button>
                    @endif
                @endif
            </div>
        @empty
            <div class="py-12 flex flex-col items-center justify-center text-center space-y-3 bg-slate-50 rounded-2xl border border-dashed border-slate-250">
                <x-ui.icon name="bell" size="lg" class="text-slate-300 animate-pulse" />
                <h3 class="text-sm font-bold text-slate-700">
                    {{ $activeTab === 'requests' ? 'Bạn không có lời mời kết nối nào' : ($activeTab === 'messages' ? 'Hộp thư tin nhắn hoàn toàn sạch sẽ' : 'Bạn chưa có thông báo nào') }}
                </h3>
                <p class="text-xxs text-slate-400 max-w-sm">
                    Mọi thông báo quan trọng về tài khoản và vòng kết nối của bạn sẽ xuất hiện tại đây.
                </p>
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if ($notifications->hasPages())
        <div class="mt-6">
            {{ $notifications->links() }}
        </div>
    @endif
</div>
