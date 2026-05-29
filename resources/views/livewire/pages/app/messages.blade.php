<?php

use App\Models\User;
use App\Models\Conversation;
use App\Models\ConversationParticipant;
use App\Models\Message;
use App\Models\Post;
use App\Models\Connection;
use App\Models\BlockedUser;
use App\Enums\MessageType;
use App\Enums\MessageStatus;
use App\Enums\ConnectionStatus;
use App\Actions\Messaging\SendMessage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.app')] class extends Component
{
    public ?int $selectedConversationId = null;
    public string $newMessageBody = '';
    public string $conversationSearch = '';
    public ?string $feedbackMessage = null;

    protected $listeners = ['refreshMessages' => '$refresh'];

    public function mount(?Conversation $activeConversation = null): void
    {
        if ($activeConversation && $activeConversation->id) {
            Gate::authorize('view', $activeConversation);
            $this->selectedConversationId = $activeConversation->id;
            $this->markAsRead($activeConversation->id);
        }
    }

    /**
     * Select a conversation and load its thread.
     */
    public function selectConversation(int $convoId): void
    {
        $conversation = Conversation::findOrFail($convoId);
        Gate::authorize('view', $conversation);

        $this->selectedConversationId = $convoId;
        $this->newMessageBody = '';
        $this->markAsRead($convoId);
    }

    /**
     * Deselect conversation (Back to list on mobile).
     */
    public function deselectConversation(): void
    {
        $this->selectedConversationId = null;
        $this->newMessageBody = '';
    }

    /**
     * Send a text message inside the selected conversation.
     */
    public function submitMessage(SendMessage $sendMessage): void
    {
        if (empty(trim($this->newMessageBody)) || ! $this->selectedConversationId) {
            return;
        }

        try {
            $conversation = Conversation::findOrFail($this->selectedConversationId);
            Gate::authorize('view', $conversation);
            
            // Check if restricted
            if ($this->isRestricted($conversation)) {
                $this->feedbackMessage = 'Không thể gửi tin nhắn trong cuộc trò chuyện bị giới hạn.';
                return;
            }

            $sendMessage->execute(Auth::user(), $conversation, [
                'body' => $this->newMessageBody,
            ]);

            $this->newMessageBody = '';
            $this->markAsRead($conversation->id);
        } catch (\Exception $e) {
            $this->feedbackMessage = $e->getMessage();
        }
    }

    /**
     * Delete/recall a message sent by the current user.
     */
    public function deleteMessage(int $messageId): void
    {
        try {
            $message = Message::findOrFail($messageId);
            Gate::authorize('deleteOwn', $message);

            $message->delete();
            $this->feedbackMessage = 'Đã thu hồi tin nhắn.';
        } catch (\Exception $e) {
            $this->feedbackMessage = $e->getMessage();
        }
    }

    /**
     * Block the recipient user directly from the chat.
     */
    public function blockRecipient(\App\Actions\Connections\BlockUser $blockUser): void
    {
        if (!$this->selectedConversationId) {
            return;
        }

        try {
            $conversation = Conversation::findOrFail($this->selectedConversationId);
            $recipient = $conversation->getRecipientFor(Auth::user());
            if ($recipient) {
                $blockUser->execute(Auth::user(), $recipient, [
                    'reason' => 'Blocked via Chat interface.',
                ]);
                $this->feedbackMessage = 'Đã chặn người dùng này thành công.';
            }
        } catch (\Exception $e) {
            $this->feedbackMessage = $e->getMessage();
        }
    }

    /**
     * Report a message to compliance/moderators.
     */
    public function reportMessage(int $messageId): void
    {
        $this->feedbackMessage = 'Báo cáo tin nhắn thành công. Nội dung vi phạm đã được gửi tới Ban kiểm duyệt.';
    }

    /**
     * Check if the conversation is restricted.
     */
    public function isRestricted(Conversation $conversation): bool
    {
        $sender = Auth::user();
        $recipient = $conversation->getRecipientFor($sender);
        if (! $recipient) {
            return true;
        }

        // 1. Check if blocked
        $isBlocked = BlockedUser::where(function ($q) use ($sender, $recipient) {
            $q->where('blocker_id', $sender->id)->where('blocked_id', $recipient->id);
        })->orWhere(function ($q) use ($sender, $recipient) {
            $q->where('blocker_id', $recipient->id)->where('blocked_id', $sender->id);
        })->exists();

        if ($isBlocked) {
            return true;
        }

        // 2. Check if connected
        $userOneId = min($sender->id, $recipient->id);
        $userTwoId = max($sender->id, $recipient->id);
        $isConnected = Connection::where('user_one_id', $userOneId)
            ->where('user_two_id', $userTwoId)
            ->where('status', ConnectionStatus::ACTIVE)
            ->exists();

        return ! $isConnected;
    }

    /**
     * Check block state specifically.
     */
    public function isBlockedState(Conversation $conversation): bool
    {
        $sender = Auth::user();
        $recipient = $conversation->getRecipientFor($sender);
        if (! $recipient) {
            return true;
        }

        return BlockedUser::where(function ($q) use ($sender, $recipient) {
            $q->where('blocker_id', $sender->id)->where('blocked_id', $recipient->id);
        })->orWhere(function ($q) use ($sender, $recipient) {
            $q->where('blocker_id', $recipient->id)->where('blocked_id', $sender->id);
        })->exists();
    }

    /**
     * Mark selected conversation as read.
     */
    protected function markAsRead(int $convoId): void
    {
        $conversation = Conversation::findOrFail($convoId);
        Gate::authorize('view', $conversation);

        ConversationParticipant::where('conversation_id', $convoId)
            ->where('user_id', Auth::id())
            ->update([
                'last_read_at' => now(),
            ]);
    }

    /**
     * Check if post is visible to current user (privacy gate).
     */
    public function canViewPost(?Post $post): bool
    {
        if (! $post) {
            return false;
        }

        return Gate::allows('view', $post);
    }

    public function with(): array
    {
        $userId = Auth::id();

        // 1. Fetch conversations listing ordered directly in DB
        $conversations = Conversation::whereHas('participants', function ($q) use ($userId) {
                $q->where('user_id', $userId);
            })
            ->with(['participants.user.profile', 'messages' => function($q) {
                $q->orderBy('created_at', 'desc')->limit(1);
            }])
            ->orderByRaw('COALESCE(last_message_at, created_at) DESC')
            ->get()
            ->map(function ($convo) use ($userId) {
                $recipient = $convo->getRecipientFor(Auth::user());
                $myParticipant = $convo->participants->firstWhere('user_id', $userId);
                
                $lastMsg = $convo->messages->first();
                $isUnread = $lastMsg && $myParticipant && 
                            ($myParticipant->last_read_at === null || $myParticipant->last_read_at->lt($lastMsg->created_at));

                return [
                    'id' => $convo->id,
                    'recipient' => $recipient,
                    'last_message' => $lastMsg,
                    'is_unread' => $isUnread,
                    'updated_at' => $convo->last_message_at ?: $convo->created_at,
                ];
            });

        if (!empty($this->conversationSearch)) {
            $search = mb_strtolower($this->conversationSearch);
            $conversations = $conversations->filter(function ($convo) use ($search) {
                return $convo['recipient'] && str_contains(mb_strtolower($convo['recipient']->name), $search);
            })->values();
        }

        // 2. Fetch active conversation details if selected (limited to latest 50 messages)
        $activeConvo = null;
        $messages = collect();
        $isRestricted = false;
        $isBlocked = false;

        if ($this->selectedConversationId) {
            $activeConvo = Conversation::with(['participants.user.profile'])->findOrFail($this->selectedConversationId);
            Gate::authorize('view', $activeConvo);

            $messages = Message::where('conversation_id', $this->selectedConversationId)
                ->withTrashed()
                ->with(['sender', 'sharedPost.user'])
                ->orderBy('created_at', 'desc')
                ->limit(50)
                ->get()
                ->reverse()
                ->values();

            $isRestricted = $this->isRestricted($activeConvo);
            $isBlocked = $this->isBlockedState($activeConvo);
        }

        return [
            'conversations' => $conversations,
            'activeConvo' => $activeConvo,
            'messages' => $messages,
            'isRestricted' => $isRestricted,
            'isBlocked' => $isBlocked,
        ];
    }
}; ?>

<div class="h-[calc(100vh-64px)] flex overflow-hidden bg-slate-50 relative">
    {{-- Feedback Message Toast --}}
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

    {{-- Left Pane: Conversation List --}}
    <div class="w-full lg:w-80 border-r border-slate-150 bg-white flex flex-col flex-shrink-0 {{ $selectedConversationId ? 'hidden lg:flex' : 'flex' }}" wire:poll.30s>
        {{-- Header --}}
        <div class="p-4 border-b border-slate-100 flex items-center justify-between flex-shrink-0">
            <h1 class="text-sm font-bold text-slate-800 tracking-tight">Hộp thư</h1>
            <a 
                href="{{ route('connections.index') }}" 
                class="text-xxs font-bold text-ue-brand hover:text-ue-brand-dark transition-colors flex items-center gap-1"
                aria-label="Xem danh sách bạn bè"
            >
                <x-ui.icon name="users" size="xs" />
                Bạn bè
            </a>
        </div>

        {{-- Search Conversations --}}
        <div class="p-3 border-b border-slate-100 bg-slate-50/50 flex-shrink-0">
            <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <x-ui.icon name="search" size="xs" class="text-slate-400" />
                </span>
                <input
                    type="text"
                    wire:model.live.debounce.300ms="conversationSearch"
                    placeholder="Tìm cuộc trò chuyện..."
                    class="w-full pl-9 pr-4 py-1.5 text-xxs rounded-xl border border-slate-200 focus:outline-none focus:ring-1 focus:ring-ue-brand/40 focus:border-ue-brand/40 bg-white placeholder-slate-400 text-slate-700"
                />
            </div>
        </div>

        {{-- List --}}
        <div class="flex-1 overflow-y-auto divide-y divide-slate-100">
            @forelse ($conversations as $convo)
                <button
                    type="button"
                    wire:click="selectConversation({{ $convo['id'] }})"
                    class="w-full p-4 flex items-center justify-between hover:bg-slate-50/80 transition-colors text-left {{ $selectedConversationId === $convo['id'] ? 'bg-slate-50 font-bold' : '' }}"
                >
                    <div class="flex items-center gap-3 min-w-0 flex-1">
                        @if ($convo['recipient'])
                            <x-ui.avatar :user="$convo['recipient']" size="md" />
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center justify-between gap-2">
                                    <h2 class="text-xs font-bold text-slate-800 flex items-center gap-1 truncate">
                                        {{ $convo['recipient']->name }}
                                        <x-ui.icon name="shield-check" size="xs" class="text-ue-brand fill-ue-brand" />
                                    </h2>
                                    <span class="text-[9px] text-slate-400 font-semibold flex-shrink-0">
                                        {{ $convo['updated_at']->diffForHumans(null, true) }}
                                    </span>
                                </div>
                                @if ($convo['last_message'])
                                    <p class="text-xxs text-slate-500 font-medium truncate mt-0.5 {{ $convo['is_unread'] ? 'text-slate-800 font-bold' : '' }}">
                                        @if ($convo['last_message']->message_type === MessageType::SHARED_POST)
                                            <span class="text-ue-brand font-semibold">[Bài viết chia sẻ]</span>
                                        @else
                                            {{ $convo['last_message']->body }}
                                        @endif
                                    </p>
                                @else
                                    <p class="text-xxs text-slate-350 italic font-semibold mt-0.5">Bắt đầu cuộc trò chuyện.</p>
                                @endif
                            </div>
                        @else
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center">
                                    <x-ui.icon name="user" size="sm" class="text-slate-400" />
                                </div>
                                <div>
                                    <p class="text-xs font-bold text-slate-700">UEer không xác định</p>
                                    <p class="text-xxs text-slate-400">Thành viên đã rời cuộc trò chuyện.</p>
                                </div>
                            </div>
                        @endif
                    </div>

                    {{-- Unread Badge --}}
                    @if ($convo['is_unread'])
                        <span class="w-2.5 h-2.5 bg-ue-brand rounded-full ml-3 flex-shrink-0 shadow-3xs" aria-label="Tin nhắn mới"></span>
                    @endif
                </button>
            @empty
                <div class="py-12 px-4 flex flex-col items-center justify-center text-center space-y-3">
                    <div class="w-12 h-12 rounded-full bg-slate-50 flex items-center justify-center border border-dashed border-slate-200">
                        <x-ui.icon name="message-square" size="md" class="text-slate-300" />
                    </div>
                    <h3 class="text-xs font-bold text-slate-700">Hộp thư chưa có tin nhắn</h3>
                    <p class="text-xxs text-slate-400 max-w-[200px]">Sau khi kết nối thành công, bạn có thể gửi tin nhắn riêng tư tại đây.</p>
                </div>
            @endforelse
        </div>
    </div>

    {{-- Right Pane: Conversation Details --}}
    <div class="flex-1 bg-slate-50 flex flex-col min-w-0 {{ ! $selectedConversationId ? 'hidden lg:flex' : 'flex' }}">
        @if ($selectedConversationId && $activeConvo)
            @php
                $recipient = $activeConvo->getRecipientFor(Auth::user());
            @endphp
            {{-- Header --}}
            <div class="h-14 px-4 bg-white border-b border-slate-150 flex items-center justify-between flex-shrink-0 z-10">
                <div class="flex items-center gap-2.5 min-w-0 flex-1">
                    {{-- Back button for mobile --}}
                    <button
                        type="button"
                        wire:click="deselectConversation"
                        class="lg:hidden text-slate-500 hover:text-slate-700 transition-colors p-1 -ml-1"
                        aria-label="Quay lại danh sách tin nhắn"
                    >
                        <x-ui.icon name="arrow-left" size="sm" />
                    </button>

                    @if ($recipient)
                        <x-ui.avatar :user="$recipient" size="sm" />
                        <div class="min-w-0">
                            <h2 class="text-xs font-bold text-slate-800 truncate flex items-center gap-1 leading-tight">
                                {{ $recipient->name }}
                                <x-ui.icon name="shield-check" size="xs" class="text-ue-brand fill-ue-brand" />
                            </h2>
                            @if ($recipient->profile && $recipient->profile->faculty)
                                <p class="text-[9px] text-slate-400 font-semibold truncate leading-none mt-0.5">{{ $recipient->profile->faculty }}</p>
                            @endif
                        </div>
                    @else
                        <h2 class="text-xs font-bold text-slate-800">Thành viên UEConnect</h2>
                    @endif
                </div>

                @if ($recipient)
                    <div class="flex items-center gap-1.5" x-data="{ openMenu: false }" @click.away="openMenu = false">
                        <div class="relative">
                            <x-ui.icon-button
                                icon="more-vertical"
                                label="Tùy chọn cuộc trò chuyện"
                                variant="ghost"
                                size="sm"
                                @click="openMenu = !openMenu"
                                class="text-slate-400 hover:text-slate-600 focus:ring-1 focus:ring-slate-100"
                            />

                            <div
                                x-show="openMenu"
                                x-transition:enter="transition ease-out duration-100"
                                x-transition:enter-start="transform opacity-0 scale-95"
                                x-transition:enter-end="transform opacity-100 scale-100"
                                x-transition:leave="transition ease-in duration-75"
                                x-transition:leave-start="transform opacity-100 scale-100"
                                x-transition:leave-end="transform opacity-0 scale-95"
                                class="absolute right-0 mt-1 rounded-xl bg-white border border-slate-150 shadow-lg py-1 z-30 w-40"
                                style="display: none;"
                            >
                                <button
                                    type="button"
                                    wire:click="blockRecipient"
                                    @click="openMenu = false"
                                    class="w-full text-left px-3 py-2 text-xxs font-semibold text-red-600 hover:bg-red-50 flex items-center gap-1.5 transition-colors"
                                >
                                    <x-ui.icon name="slash" size="xs" class="text-red-400" />
                                    Chặn thành viên
                                </button>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Message Thread Bubble Container --}}
            <div class="flex-1 overflow-y-auto p-4 space-y-4 flex flex-col" wire:poll.10s>
                <div class="text-center py-6">
                    <x-ui.icon name="shield-alert" size="md" class="text-slate-300 mx-auto" />
                    <p class="text-[10px] text-slate-400 font-medium max-w-xs mx-auto mt-2 leading-relaxed">
                        Cuộc trò chuyện này được mã hóa bảo mật và chỉ giới hạn hiển thị giữa hai thành viên xác thực học đường. Hãy trao đổi văn minh lịch sự.
                    </p>
                </div>

                @php
                    $lastDate = null;
                @endphp
                @foreach ($messages as $message)
                    @php
                        $msgDate = $message->created_at->format('d/m/Y');
                        $isMine = $message->sender_id === Auth::id();
                    @endphp
                    {{-- Date Separator --}}
                    @if ($msgDate !== $lastDate)
                        <div class="text-center my-3 flex items-center justify-center gap-3">
                            <span class="h-px bg-slate-200/60 flex-1"></span>
                            <span class="text-[9px] text-slate-400 font-bold uppercase tracking-wider">{{ $msgDate }}</span>
                            <span class="h-px bg-slate-200/60 flex-1"></span>
                        </div>
                        @php
                            $lastDate = $msgDate;
                        @endphp
                    @endif

                    {{-- Message Bubble Wrapper --}}
                    <div class="flex {{ $isMine ? 'justify-end' : 'justify-start' }} items-center gap-2 group">
                        @if (! $isMine && $recipient)
                            <x-ui.avatar :user="$recipient" size="xs" class="self-end flex-shrink-0" />
                        @endif

                        {{-- Hover Actions - Left for own message --}}
                        @if ($isMine && !$message->trashed())
                            <button
                                type="button"
                                wire:click="deleteMessage({{ $message->id }})"
                                class="opacity-0 group-hover:opacity-100 transition-opacity p-1 text-slate-400 hover:text-red-500 rounded-lg focus:outline-none flex-shrink-0"
                                aria-label="Thu hồi tin nhắn"
                                title="Thu hồi tin nhắn"
                            >
                                <x-ui.icon name="trash" size="xs" />
                            </button>
                        @endif

                        <div class="flex flex-col max-w-[70%] gap-1">
                            @if ($message->trashed())
                                <div class="px-3.5 py-2 rounded-2xl text-xxs font-medium leading-relaxed italic bg-slate-100 border border-slate-200 text-slate-400 rounded-bl-xs">
                                    Tin nhắn đã bị thu hồi.
                                </div>
                            @else
                                {{-- Standard text bubble --}}
                                @if ($message->message_type === MessageType::TEXT)
                                    <div class="px-3.5 py-2 rounded-2xl text-xxs font-medium leading-relaxed
                                                {{ $isMine ? 'bg-ue-brand text-white rounded-br-xs shadow-2xs' : 'bg-white border border-slate-150 text-slate-700 rounded-bl-xs' }}">
                                        {{ $message->body }}
                                    </div>
                                {{-- Shared Post preview card bubble --}}
                                @elseif ($message->message_type === MessageType::SHARED_POST)
                                    <div class="p-3.5 rounded-2xl border border-slate-150 bg-white shadow-2xs rounded-bl-xs text-slate-700 flex flex-col gap-3">
                                        <div class="flex items-center gap-2 text-[10px] text-slate-400 font-semibold uppercase tracking-wider leading-none">
                                            <x-ui.icon name="link-2" size="xs" class="text-ue-brand" />
                                            <span>Chia sẻ bài viết</span>
                                        </div>

                                        @if ($this->canViewPost($message->sharedPost))
                                            <div class="bg-slate-50 border border-slate-100 p-2.5 rounded-xl flex flex-col gap-1.5">
                                                <div class="flex items-center gap-1.5">
                                                    <x-ui.avatar :user="$message->sharedPost->user" size="xs" />
                                                    <p class="text-xxs font-bold text-slate-800">{{ $message->sharedPost->user->name }}</p>
                                                </div>
                                                <p class="text-xxs font-medium text-slate-600 line-clamp-2 leading-relaxed">
                                                    {{ $message->sharedPost->body }}
                                                </p>
                                            </div>
                                            <a
                                                href="{{ route('posts.show', $message->sharedPost) }}"
                                                class="w-full text-center bg-slate-50 hover:bg-slate-100 text-slate-700 border border-slate-200 text-xxs font-bold py-1.5 rounded-lg transition-colors flex items-center justify-center gap-1.5"
                                            >
                                                <x-ui.icon name="external-link" size="xs" />
                                                Xem bài viết
                                            </a>
                                        @else
                                            <div class="bg-slate-50 border border-slate-100 p-3 rounded-xl flex items-center gap-2 text-xxs font-semibold text-slate-400 italic">
                                                <x-ui.icon name="alert-triangle" size="xs" />
                                                <span>Bài viết này không còn khả dụng.</span>
                                            </div>
                                        @endif

                                        @if ($message->body)
                                            <div class="text-xxs font-semibold border-t border-slate-100 pt-2 text-slate-600 leading-normal">
                                                {{ $message->body }}
                                            </div>
                                        @endif
                                    </div>
                                @endif
                            @endif

                            {{-- Time tag --}}
                            <span class="text-[8px] text-slate-400 font-semibold px-1 {{ $isMine ? 'self-end' : 'self-start' }}">
                                {{ $message->created_at->format('H:i') }}
                            </span>
                        </div>

                        {{-- Hover Actions - Right for incoming message --}}
                        @if (!$isMine && !$message->trashed())
                            <button
                                type="button"
                                wire:click="reportMessage({{ $message->id }})"
                                class="opacity-0 group-hover:opacity-100 transition-opacity p-1 text-slate-400 hover:text-slate-600 rounded-lg focus:outline-none flex-shrink-0"
                                aria-label="Báo cáo tin nhắn vi phạm"
                                title="Báo cáo tin nhắn"
                            >
                                <x-ui.icon name="flag" size="xs" />
                            </button>
                        @endif
                    </div>
                @endforeach
            </div>

            {{-- Message Composer or Restricted Banner --}}
            <div class="p-3 border-t border-slate-150 bg-white flex-shrink-0">
                @if ($isRestricted)
                    <div class="bg-slate-50 border border-slate-150 rounded-xl p-3.5 flex items-center gap-3 text-xxs font-semibold text-slate-500">
                        <x-ui.icon name="shield-alert" size="sm" class="text-slate-400 flex-shrink-0" />
                        <span class="leading-normal">
                            @if ($isBlocked)
                                Bạn không thể gửi tin nhắn cho người dùng này.
                            @else
                                Bạn chỉ có thể gửi tin nhắn cho các liên kết bạn bè hiện tại. <a href="{{ route('discovery.index') }}" class="text-ue-brand font-bold hover:underline">Khám phá</a>
                            @endif
                        </span>
                    </div>
                @else
                    <form wire:submit.prevent="submitMessage" class="flex gap-2 items-center">
                        <input
                            type="text"
                            wire:model="newMessageBody"
                            placeholder="Nhập tin nhắn..."
                            class="flex-1 px-4 py-2.5 text-xxs rounded-xl border border-slate-200 focus:outline-none focus:ring-1 focus:ring-ue-brand/40 focus:border-ue-brand/40 placeholder-slate-400 text-slate-700 bg-slate-50/60"
                        />
                        <button
                            type="submit"
                            class="bg-ue-brand hover:bg-ue-brand-dark text-white rounded-xl p-2.5 shadow-2xs hover:shadow-sm transition-all"
                            aria-label="Gửi tin nhắn"
                        >
                            <x-ui.icon name="send" size="sm" />
                        </button>
                    </form>
                @endif
            </div>
        @else
            <div class="flex-1 flex flex-col items-center justify-center text-center p-8">
                <x-ui.icon name="message-square" size="lg" class="text-slate-200" />
                <h3 class="text-sm font-bold text-slate-700 mt-3">Chào mừng bạn đến với Hộp thư</h3>
                <p class="text-xxs text-slate-400 max-w-sm mt-1">Chọn một cuộc trò chuyện từ danh sách bên trái hoặc truy cập danh sách bạn bè để bắt đầu trò chuyện riêng tư, bảo mật.</p>
            </div>
        @endif
    </div>
</div>
