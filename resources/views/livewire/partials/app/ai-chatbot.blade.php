<?php

use Livewire\Volt\Component;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\AI\HcmueChatbot\Chat\HcmueChatService;

new class extends Component {
    public bool $isOpen = false;
    public string $input = '';
    public array $messages = [
        ['role' => 'model', 'content' => 'Xin chào! Tôi là trợ lý AI của UE Connect. Tôi có thể giúp gì cho bạn hôm nay?']
    ];
    public bool $isTyping = false;
    public ?int $sessionId = null;

    protected $listeners = [
        'message-sent' => 'handleMessageSent'
    ];

    public function toggleChat(): void
    {
        $this->isOpen = !$this->isOpen;
    }

    public function sendMessage(): void
    {
        if (trim($this->input) === '') {
            return;
        }

        $userMessage = $this->input;
        $this->messages[] = ['role' => 'user', 'content' => $userMessage];
        $this->input = '';
        $this->isTyping = true;

        $this->dispatch('message-sent', userMessage: $userMessage);
    }

    public function handleMessageSent(string $userMessage, HcmueChatService $chatService): void
    {
        $user = Auth::user();
        if (!$user) {
            $this->messages[] = ['role' => 'model', 'content' => 'Vui lòng đăng nhập để sử dụng trợ lý AI.'];
            $this->isTyping = false;
            return;
        }

        try {
            // Resolve session for the user
            $session = $chatService->resolveSession($user, $this->sessionId);
            $this->sessionId = $session->id;

            // Process message through HcmueChatService RAG pipeline (only Qdrant is queried)
            $result = $chatService->chat($userMessage, $session, $user);

            $this->messages[] = ['role' => 'model', 'content' => $result['answer']];

        } catch (\Illuminate\Http\Client\RequestException $e) {
            $response = $e->response;
            $errorData = $response->json();
            $errorMessage = $errorData['error']['message'] ?? $response->body();
            $errorCode = $response->status();
            $errorStatus = $errorData['error']['status'] ?? 'UNKNOWN';

            Log::error("Gemini API Error [{$errorCode} - {$errorStatus}]: " . $response->body());

            $details = "Lỗi kết nối với AI (HTTP {$errorCode}): ";
            if ($errorCode == 429 || $errorStatus === 'RESOURCE_EXHAUSTED') {
                $details .= "Bạn đã vượt quá giới hạn tài nguyên (Quota Exceeded) của API Key hiện tại. Vui lòng kiểm tra lại hạn mức tài khoản Google AI Studio hoặc đổi sang Key khác.";
            } elseif ($errorCode == 400) {
                $details .= "API Key không hợp lệ hoặc thiếu. Vui lòng điền đúng GEMINI_API_KEY trong file .env và clear cache config.";
            } else {
                $details .= $errorMessage;
            }

            $this->messages[] = ['role' => 'model', 'content' => $details];
        } catch (\Exception $e) {
            Log::error('Chatbot Exception: ' . $e->getMessage());
            $this->messages[] = ['role' => 'model', 'content' => 'Hệ thống đang bận. Chi tiết: ' . $e->getMessage()];
        }

        $this->isTyping = false;
    }
};

?>

@php
    $isHiddenOnChat = request()->routeIs('chat.*') || request()->is('chat');
@endphp
<div class="fixed bottom-[calc(var(--layout-bottom-nav-h)+80px)] right-4 lg:bottom-[calc(2rem+64px)] lg:right-8 z-[999] flex flex-col items-end pointer-events-none {{ $isHiddenOnChat ? 'hidden' : '' }}">
    <!-- Chat Window -->
    @if($isOpen)
        <div class="bg-white dark:bg-zinc-900 w-80 sm:w-96 rounded-2xl shadow-2xl border border-zinc-200 dark:border-zinc-800 mb-4 overflow-hidden flex flex-col pointer-events-auto transition-all duration-300 transform origin-bottom-right" style="height: 500px; max-height: calc(100vh - 120px); width: 380px; max-width: 90vw; border-radius: 16px; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25); display: flex; flex-direction: column; overflow: hidden; margin-bottom: 16px;">
            <!-- Header -->
            <div class="bg-zinc-900 dark:bg-zinc-950 p-4 flex items-center justify-between shadow-sm z-10 rounded-t-2xl" style="background-color: #18181b; padding: 16px; display: flex; align-items: center; justify-content: space-between; color: #ffffff; border-top-left-radius: 16px; border-top-right-radius: 16px;">
                <div class="flex items-center gap-3" style="display: flex; align-items: center; gap: 12px;">
                    <div class="w-8 h-8 rounded-full bg-indigo-500 flex items-center justify-center text-white font-bold text-sm" style="width: 32px; height: 32px; border-radius: 9999px; background-color: #6366f1; display: flex; align-items: center; justify-content: center; font-weight: bold; color: #ffffff;">
                        AI
                    </div>
                    <div>
                        <h3 class="font-semibold text-white text-sm" style="font-weight: 600; font-size: 14px; margin: 0; color: #ffffff;">UE Connect Assistant</h3>
                        <p class="text-xs text-zinc-400" style="font-size: 12px; margin: 0; color: #a1a1aa;">Luôn sẵn sàng hỗ trợ</p>
                    </div>
                </div>
                <div class="flex items-center gap-2" style="display: flex; align-items: center; gap: 8px;">
                    <a href="/chat" wire:navigate class="text-zinc-400 hover:text-white transition-colors" title="Mở toàn màn hình" style="color: #a1a1aa; text-decoration: none;">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" style="width: 20px; height: 20px;">
                            <path d="M11 3a1 1 0 100 2h2.586l-6.293 6.293a1 1 0 101.414 1.414L15 6.414V9a1 1 0 102 0V4a1 1 0 00-1-1h-5z" />
                            <path d="M5 5a2 2 0 00-2 2v8a2 2 0 002 2h8a2 2 0 002-2v-3a1 1 0 10-2 0v3H5V7h3a1 1 0 000-2H5z" />
                        </svg>
                    </a>
                    <button wire:click="toggleChat" class="text-zinc-400 hover:text-white transition-colors" style="background: none; border: none; cursor: pointer; color: #a1a1aa;">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" style="width: 20px; height: 20px;">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Messages Area -->
            <div class="flex-1 overflow-y-auto p-4 space-y-4 bg-zinc-50 dark:bg-zinc-900 scroll-smooth" id="chatbot-messages" style="padding: 16px; background-color: #f8fafc; flex: 1; overflow-y: auto;">
                @foreach($messages as $msg)
                    <div class="flex {{ $msg['role'] === 'model' ? 'justify-start' : 'justify-end' }}" style="display: flex; {{ $msg['role'] === 'model' ? 'justify-content: flex-start;' : 'justify-content: flex-end;' }} margin-bottom: 16px;">
                        @if($msg['role'] === 'model')
                            <div class="w-6 h-6 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 font-bold text-[10px] mr-2 shrink-0 mt-1" style="width: 24px; height: 24px; border-radius: 9999px; background-color: #e0e7ff; color: #4f46e5; display: flex; align-items: center; justify-content: center; font-size: 10px; font-weight: bold; margin-right: 8px; flex-shrink: 0; margin-top: 4px;">
                                AI
                            </div>
                        @endif
                        <div class="max-w-[85%] rounded-2xl px-4 py-2 text-sm shadow-sm
                            {{ $msg['role'] === 'model' ? 'bg-white dark:bg-zinc-800 text-zinc-800 dark:text-zinc-200 border border-zinc-150 dark:border-zinc-700' : 'bg-indigo-600 text-white' }}"
                            style="max-width: 85%; border-radius: 16px; padding: 8px 16px; font-size: 14px; {{ $msg['role'] === 'model' ? 'background-color: #ffffff; color: #27272a; border: 1px solid #e4e4e7;' : 'background-color: #4f46e5; color: #ffffff;' }}">
                            @if($msg['role'] === 'model')
                                <x-ui.markdown :content="$msg['content']" />
                            @else
                                {!! nl2br(e($msg['content'])) !!}
                            @endif
                        </div>
                    </div>
                @endforeach
                
                @if($isTyping)
                    <div class="flex justify-start" style="display: flex; justify-content: flex-start; margin-bottom: 16px;">
                        <div class="w-6 h-6 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 font-bold text-[10px] mr-2 shrink-0 mt-1" style="width: 24px; height: 24px; border-radius: 9999px; background-color: #e0e7ff; color: #4f46e5; display: flex; align-items: center; justify-content: center; font-size: 10px; font-weight: bold; margin-right: 8px; flex-shrink: 0; margin-top: 4px;">
                            AI
                        </div>
                        <div class="bg-white dark:bg-zinc-800 border border-zinc-100 dark:border-zinc-700 rounded-2xl px-4 py-3 shadow-sm flex items-center" style="background-color: #ffffff; border: 1px solid #f4f4f5; border-radius: 16px; padding: 12px 16px; display: flex; align-items: center;">
                            <x-ui.loading-state variant="dots" class="!py-0 !px-0" />
                        </div>
                    </div>
                @endif
            </div>

            <!-- Input Area -->
            <div class="p-3 bg-white dark:bg-zinc-950 border-t border-zinc-200 dark:border-zinc-800 z-10" style="padding: 12px; background-color: #ffffff; border-top: 1px solid #e4e4e7; border-bottom-left-radius: 16px; border-bottom-right-radius: 16px;">
                <form wire:submit="sendMessage" class="relative flex items-center" style="position: relative; display: flex; align-items: center; width: 100%;">
                    <textarea 
                        wire:model="input" 
                        x-init="$watch('input', value => { if (!value) { $nextTick(() => autoGrowTextarea($el, 140)); } })"
                        rows="1"
                        placeholder="Nhập câu hỏi của bạn..." 
                        class="chat-widget-input w-full bg-zinc-100 dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 rounded-2xl py-3 pl-4 pr-12 text-sm focus:outline-none"
                        style="height: 46px;"
                        oninput="autoGrowTextarea(this, 140)"
                        @keydown.enter="
                            if (!$event.shiftKey) {
                                $event.preventDefault();
                                $el.closest('form')?.dispatchEvent(new Event('submit', { bubbles: true, cancelable: true }));
                            }
                        "
                        @if($isTyping) disabled @endif
                    ></textarea>
                    <button 
                        type="submit" 
                        class="absolute right-2 p-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-full transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                        style="position: absolute; right: 8px; width: 32px; height: 32px; background-color: #4f46e5; border: none; border-radius: 9999px; color: #ffffff; display: flex; align-items: center; justify-content: center; cursor: pointer; bottom: 7px;"
                        @if($isTyping) disabled @endif
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" style="width: 16px; height: 16px;">
                            <path d="M10.894 2.553a1 1 0 00-1.788 0l-7 14a1 1 0 001.169 1.409l5-1.429A1 1 0 009 15.571V11a1 1 0 112 0v4.571a1 1 0 00.725.962l5 1.428a1 1 0 001.17-1.408l-7-14z" />
                        </svg>
                    </button>
                </form>
            </div>
            
            <!-- Auto Scroll & Auto Grow Script -->
            <script>
                if (typeof window.autoGrowTextarea !== 'function') {
                    window.autoGrowTextarea = function(el, maxHeight = 180) {
                        el.style.height = 'auto';
                        const nextHeight = Math.min(el.scrollHeight, maxHeight);
                        el.style.height = nextHeight + 'px';
                        el.style.overflowY = el.scrollHeight > maxHeight ? 'auto' : 'hidden';
                    };
                }
                document.addEventListener('livewire:initialized', () => {
                    Livewire.hook('morph.updated', (el, component) => {
                        const container = document.getElementById('chatbot-messages');
                        if (container) {
                            container.scrollTop = container.scrollHeight;
                        }
                    });
                });
            </script>
        </div>
    @endif

    <!-- FAB Trigger -->
    <button 
        wire:click="toggleChat"
        class="pointer-events-auto flex items-center justify-center w-14 h-14 bg-zinc-900 dark:bg-white text-white dark:text-zinc-900 rounded-full shadow-lg hover:shadow-xl hover:scale-105 transition-all duration-300 focus:outline-none focus:ring-4 focus:ring-zinc-900/20 dark:focus:ring-white/20"
        style="width: 56px; height: 56px; border-radius: 9999px; background-color: #18181b; color: #ffffff; display: flex; align-items: center; justify-content: center; cursor: pointer; border: none; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); pointer-events: auto;"
    >
        @if($isOpen)
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width: 24px; height: 24px;">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
        @else
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width: 24px; height: 24px;">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
            </svg>
        @endif
    </button>
    <style>
    .chat-widget-input {
        color: #111827 !important;
        background-color: #ffffff !important;
        caret-color: #4f46e5 !important;
        -webkit-text-fill-color: #111827 !important;
        border: 1px solid #e4e4e7 !important;
        font-family: inherit;
        width: 100%;
        outline: none;
        resize: none;
        overflow-y: hidden;
        transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
        padding: 12px 48px 12px 16px !important;
        border-radius: 24px !important;
        font-size: 14px !important;
        min-height: 46px !important;
        max-height: 140px !important;
    }
    .chat-widget-input::placeholder {
        color: #9ca3af !important;
        -webkit-text-fill-color: #9ca3af !important;
        opacity: 1 !important;
    }
    .chat-widget-input:focus {
        border-color: #4f46e5 !important;
        box-shadow: 0 0 0 2px rgba(79, 70, 229, 0.1) !important;
        background-color: #ffffff !important;
    }
    
    /* Dark mode overrides */
    .dark .chat-widget-input {
        color: #f3f4f6 !important;
        background-color: #1f2937 !important;
        -webkit-text-fill-color: #f3f4f6 !important;
        border-color: #374151 !important;
    }
    .dark .chat-widget-input::placeholder {
        color: #6b7280 !important;
        -webkit-text-fill-color: #6b7280 !important;
    }
    .dark .chat-widget-input:focus {
        border-color: #6366f1 !important;
        box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.2) !important;
        background-color: #1f2937 !important;
    }
</style>
</div>
