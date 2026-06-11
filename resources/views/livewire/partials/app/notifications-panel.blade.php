<?php

use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;
use App\Actions\Connections\AcceptGreeting;
use App\Actions\Connections\DeclineGreeting;
use App\Enums\GreetingStatus;
use App\Support\Navigation\UserNavigationMetrics;

new class extends Component
{
    public string $activeTab = 'all'; // all, requests, messages, system
    public ?string $feedbackMessage = null;

    protected $listeners = ['refreshNotifications' => '$refresh'];

    public function readAndRedirect(string $id)
    {
        try {
            $notification = Auth::user()->notifications()->findOrFail($id);
            if (is_null($notification->read_at)) {
                $notification->markAsRead();
                app(UserNavigationMetrics::class)->forgetForUser(Auth::id());
                
                $metrics = app(UserNavigationMetrics::class)->forUser(Auth::user());
                $this->dispatch('ue-notifications-updated', count: $metrics['unread_notifications'] + $metrics['unread_messages']);
            }
            
            $actionUrl = $notification->data['action_url'] ?? route('dashboard');
            return redirect()->to($actionUrl);
        } catch (\Exception $e) {
            $this->feedbackMessage = $e->getMessage();
        }
    }

    public function markAsRead(string $id): void
    {
        try {
            $notification = Auth::user()->unreadNotifications()->findOrFail($id);
            $notification->markAsRead();
            app(UserNavigationMetrics::class)->forgetForUser(Auth::id());
            
            $metrics = app(UserNavigationMetrics::class)->forUser(Auth::user());
            $this->dispatch('ue-notifications-updated', count: $metrics['unread_notifications'] + $metrics['unread_messages']);

            $this->feedbackMessage = 'Đã đánh dấu là đã đọc.';
        } catch (\Exception $e) {
            $this->feedbackMessage = $e->getMessage();
        }
    }

    public function markAllAsRead(): void
    {
        try {
            Auth::user()->unreadNotifications->markAsRead();
            app(UserNavigationMetrics::class)->forgetForUser(Auth::id());
            
            $metrics = app(UserNavigationMetrics::class)->forUser(Auth::user());
            $this->dispatch('ue-notifications-updated', count: $metrics['unread_notifications'] + $metrics['unread_messages']);

            $this->feedbackMessage = 'Đã đánh dấu tất cả đã đọc.';
        } catch (\Exception $e) {
            $this->feedbackMessage = $e->getMessage();
        }
    }

    /**
     * Accept connection request.
     */
    public function acceptGreeting(int $greetingId, AcceptGreeting $acceptGreeting): void
    {
        try {
            $greeting = \App\Models\Greeting::findOrFail($greetingId);
            $acceptGreeting->execute(Auth::user(), $greeting);
            $this->feedbackMessage = 'Đã chấp nhận kết nối.';
        } catch (\Exception $e) {
            $this->feedbackMessage = $e->getMessage();
        }
    }

    /**
     * Decline connection request.
     */
    public function declineGreeting(int $greetingId, DeclineGreeting $declineGreeting): void
    {
        try {
            $greeting = \App\Models\Greeting::findOrFail($greetingId);
            $declineGreeting->execute(Auth::user(), $greeting);
            $this->feedbackMessage = 'Đã từ chối kết nối.';
        } catch (\Exception $e) {
            $this->feedbackMessage = $e->getMessage();
        }
    }

    public function with(): array
    {
        $user = Auth::user();
        if (!$user) {
            return [
                'notifications' => collect(),
                'unreadCount' => 0,
            ];
        }

        $query = $user->notifications();

        if ($this->activeTab === 'requests') {
            $query->where('data->type', 'greeting_received');
        } elseif ($this->activeTab === 'messages') {
            $query->where('data->type', 'message_received');
        } elseif ($this->activeTab === 'system') {
            $query->whereNotIn('data->type', ['greeting_received', 'message_received']);
        }

        // Side panel lists 20 most recent notifications
        $notifications = $query->reorder('created_at', 'desc')->take(20)->get();

        return [
            'notifications' => $notifications,
            'unreadCount' => $user->unreadNotifications()->count(),
        ];
    }
}; ?>

<div wire:poll.visible.30s class="flex flex-col h-full bg-white text-slate-800">
    {{-- Header --}}
    <div class="px-4 py-4 border-b border-slate-100 flex items-center justify-between">
        <div>
            <h2 class="text-sm font-bold text-slate-900 tracking-tight">Thông báo</h2>
        </div>
        <button 
            type="button" 
            @click="notificationsOpen = false" 
            class="p-1 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-50 transition-colors focus-visible:ring-1 focus-visible:ring-slate-300"
            aria-label="Đóng bảng thông báo"
        >
            <x-ui.icon name="x" size="sm" />
        </button>
    </div>

    {{-- Tabs --}}
    <div class="flex gap-1.5 px-4 py-2 overflow-x-auto border-b border-slate-100 scrollbar-none flex-shrink-0 bg-slate-50/40">
        <button
            type="button"
            wire:click="$set('activeTab', 'all')"
            class="px-3 py-1 rounded-full text-[10px] font-bold transition-all border whitespace-nowrap
                   {{ $activeTab === 'all' ? 'bg-ue-brand-soft text-ue-brand-active border-ue-brand-border' : 'bg-white text-slate-600 border-slate-200 hover:border-slate-350 hover:bg-slate-50' }}"
        >
            Tất cả
        </button>
        <button
            type="button"
            wire:click="$set('activeTab', 'requests')"
            class="px-3 py-1 rounded-full text-[10px] font-bold transition-all border whitespace-nowrap
                   {{ $activeTab === 'requests' ? 'bg-ue-brand-soft text-ue-brand-active border-ue-brand-border' : 'bg-white text-slate-600 border-slate-200 hover:border-slate-350 hover:bg-slate-50' }}"
        >
            Yêu cầu
        </button>
        <button
            type="button"
            wire:click="$set('activeTab', 'messages')"
            class="px-3 py-1 rounded-full text-[10px] font-bold transition-all border whitespace-nowrap
                   {{ $activeTab === 'messages' ? 'bg-ue-brand-soft text-ue-brand-active border-ue-brand-border' : 'bg-white text-slate-600 border-slate-200 hover:border-slate-350 hover:bg-slate-50' }}"
        >
            Tin nhắn
        </button>
        <button
            type="button"
            wire:click="$set('activeTab', 'system')"
            class="px-3 py-1 rounded-full text-[10px] font-bold transition-all border whitespace-nowrap
                   {{ $activeTab === 'system' ? 'bg-ue-brand-soft text-ue-brand-active border-ue-brand-border' : 'bg-white text-slate-600 border-slate-200 hover:border-slate-350 hover:bg-slate-50' }}"
        >
            Hệ thống
        </button>
    </div>

    {{-- Feedback Message --}}
    @if ($feedbackMessage)
        <div class="px-4 py-2 bg-slate-900 text-white text-[10px] font-semibold flex items-center justify-between gap-2 transition-all" x-data="{ show: true }" x-show="show" x-init="setTimeout(() => { show = false; $wire.set('feedbackMessage', null); }, 2500)">
            <span>{{ $feedbackMessage }}</span>
            <button @click="show = false" class="text-slate-400 hover:text-white">
                <x-ui.icon name="x" size="xs" />
            </button>
        </div>
    @endif

    {{-- Action Bar --}}
    @if ($unreadCount > 0)
        <div class="px-4 py-2 border-b border-slate-100 bg-slate-50/20 flex justify-between items-center flex-shrink-0">
            <span class="text-[10px] text-slate-450 font-semibold">Bạn có {{ $unreadCount }} thông báo mới</span>
            <button
                type="button"
                wire:click="markAllAsRead"
                wire:loading.attr="disabled"
                wire:target="markAllAsRead"
                class="text-[10px] font-bold text-ue-brand hover:text-ue-brand-dark transition-colors flex items-center gap-1"
            >
                <span wire:loading.remove wire:target="markAllAsRead">Đánh dấu đã đọc</span>
                <span wire:loading wire:target="markAllAsRead" class="ue-spinner"></span>
            </button>
        </div>
    @endif

    {{-- Content List --}}
    <div class="flex-1 overflow-y-auto divide-y divide-slate-100">
        @forelse ($notifications as $notification)
            @php
                $isUnread = is_null($notification->read_at);
                $type = $notification->data['type'] ?? 'default';
                $title = $notification->data['title'] ?? 'Thông báo hệ thống';
                $body = $notification->data['body'] ?? '';
                
                $greeting = null;
                if ($type === 'greeting_received' && isset($notification->data['greeting_id'])) {
                    $greeting = \App\Models\Greeting::find($notification->data['greeting_id']);
                }
            @endphp
            <div 
                class="p-4 flex flex-col gap-2 transition-all hover:bg-slate-50/50
                       {{ $isUnread ? 'bg-ue-brand-soft/5 border-l-2 border-ue-brand' : '' }}"
            >
                <div class="flex items-start gap-3">
                    {{-- User Avatar / Placeholder --}}
                    <div 
                        class="relative flex-shrink-0 cursor-pointer"
                        wire:click="readAndRedirect('{{ $notification->id }}')"
                    >
                        <span class="inline-flex items-center justify-center bg-ue-brand-soft border border-ue-border w-9 h-9 text-[10px] font-semibold rounded-full overflow-hidden">
                            @if (!empty($notification->data['sender_name']))
                                <span class="font-semibold text-ue-brand select-none leading-none">
                                    {{ mb_strtoupper(mb_substr($notification->data['sender_name'], 0, 2)) }}
                                </span>
                            @else
                                <span class="font-semibold text-ue-brand select-none leading-none">UE</span>
                            @endif
                        </span>
                        
                        {{-- Icon overlay --}}
                        <div class="absolute -bottom-1 -right-1 w-4.5 h-4.5 rounded-full border border-white flex items-center justify-center text-[8px] shadow-xs text-white
                                    {{ $type === 'message_received' ? 'bg-blue-500' : ($type === 'greeting_received' ? 'bg-purple-600' : 'bg-slate-400') }}">
                            @if ($type === 'message_received')
                                <x-ui.icon name="message-square" size="2xs" />
                            @elseif ($type === 'greeting_received')
                                <x-ui.icon name="user-plus" size="2xs" />
                            @else
                                <x-ui.icon name="bell" size="2xs" />
                            @endif
                        </div>
                    </div>

                    {{-- Text Column --}}
                    <div 
                        class="min-w-0 flex-1 space-y-0.5 cursor-pointer"
                        wire:click="readAndRedirect('{{ $notification->id }}')"
                    >
                        <div class="flex items-center gap-1.5 flex-wrap">
                            <h4 class="text-xxs font-bold text-slate-800 leading-tight">
                                {{ $notification->data['sender_name'] ?? 'UEConnect' }}
                            </h4>
                            <span class="text-slate-300 text-[8px] select-none">·</span>
                            <span class="text-[8px] text-slate-400 font-medium">
                                {{ $notification->created_at->diffForHumans(null, true) }}
                            </span>
                            @if ($isUnread)
                                <span class="w-1.5 h-1.5 bg-ue-brand rounded-full"></span>
                            @endif
                        </div>
                        <p class="text-[10px] text-slate-500 font-semibold truncate">{{ $title }}</p>
                        <p class="text-[10px] text-slate-500 font-medium leading-normal line-clamp-2">{{ $body }}</p>
                    </div>

                    {{-- Mark read button on the right --}}
                    @if ($isUnread && !$greeting)
                        <button
                            type="button"
                            wire:click="markAsRead('{{ $notification->id }}')"
                            wire:loading.attr="disabled"
                            wire:target="markAsRead('{{ $notification->id }}')"
                            class="p-1 text-slate-355 hover:text-ue-brand rounded-lg transition-colors flex-shrink-0 disabled:opacity-50 self-center"
                            aria-label="Đánh dấu là đã đọc"
                            title="Đánh dấu là đã đọc"
                        >
                            <x-ui.icon name="check" size="xs" />
                        </button>
                    @endif
                </div>

                {{-- Connection Buttons row --}}
                @if ($greeting && $greeting->status === \App\Enums\GreetingStatus::PENDING)
                    <div class="flex items-center gap-2 pl-12">
                        <button
                            type="button"
                            wire:click="declineGreeting({{ $greeting->id }})"
                            wire:loading.attr="disabled"
                            wire:target="declineGreeting({{ $greeting->id }})"
                            class="bg-slate-50 hover:bg-slate-100 text-slate-500 text-[9px] font-bold px-2.5 py-1.5 rounded-lg border border-slate-200 transition-colors disabled:opacity-60"
                        >
                            Ẩn
                        </button>
                        <button
                            type="button"
                            wire:click="acceptGreeting({{ $greeting->id }})"
                            wire:loading.attr="disabled"
                            wire:target="acceptGreeting({{ $greeting->id }})"
                            class="bg-ue-brand hover:bg-ue-brand-hover text-white text-[9px] font-bold px-2.5 py-1.5 rounded-lg transition-all disabled:opacity-60"
                        >
                            <span wire:loading.remove wire:target="acceptGreeting({{ $greeting->id }})">Xác nhận</span>
                            <span wire:loading wire:target="acceptGreeting({{ $greeting->id }})" class="ue-spinner w-3 h-3"></span>
                        </button>
                    </div>
                @endif
            </div>
        @empty
            <div class="py-16 px-4 flex flex-col items-center justify-center text-center space-y-3 bg-slate-50/50">
                <x-ui.icon name="bell" size="md" class="text-slate-300" />
                <h3 class="text-xxs font-bold text-slate-700">Chưa có thông báo nào</h3>
                <p class="text-[10px] text-slate-400 max-w-[200px]">Cập nhật mới từ vòng kết nối của bạn sẽ xuất hiện tại đây.</p>
            </div>
        @endforelse
    </div>
</div>
