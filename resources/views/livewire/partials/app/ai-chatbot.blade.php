<?php

use function Livewire\Volt\{state, action, layout};
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\AI\HcmueChatbot\Chat\HcmueChatService;

state([
    'isOpen' => false,
    'input' => '',
    'messages' => [
        ['role' => 'model', 'content' => 'Xin chào! Tôi là trợ lý AI của UE Connect. Tôi có thể giúp gì cho bạn hôm nay?']
    ],
    'isTyping' => false,
    'sessionId' => null,
]);

$toggleChat = action(function () {
    $this->isOpen = !$this->isOpen;
});

$sendMessage = action(function (HcmueChatService $chatService) {
    if (trim($this->input) === '') {
        return;
    }

    $userMessage = $this->input;
    $this->messages[] = ['role' => 'user', 'content' => $userMessage];
    $this->input = '';
    $this->isTyping = true;

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
});

?>

<div class="fixed bottom-24 right-4 lg:bottom-28 lg:right-8 z-[999] flex flex-col items-end pointer-events-none" style="position: fixed; bottom: 96px; right: 16px; z-index: 9999; display: flex; flex-direction: column; align-items: flex-end;">
    <!-- Chat Window -->
    @if($isOpen)
        <div class="bg-white dark:bg-zinc-900 w-80 sm:w-96 rounded-2xl shadow-2xl border border-zinc-200 dark:border-zinc-800 mb-4 overflow-hidden flex flex-col pointer-events-auto transition-all duration-300 transform origin-bottom-right" style="height: 500px; max-height: calc(100vh - 120px); width: 380px; max-width: 90vw; border-radius: 16px; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25); display: flex; flex-direction: column; overflow: hidden; margin-bottom: 16px;">
            <!-- Header -->
            <div class="bg-zinc-900 dark:bg-zinc-950 p-4 flex items-center justify-between shadow-sm z-10" style="background-color: #18181b; padding: 16px; display: flex; align-items: center; justify-content: space-between; color: #ffffff;">
                <div class="flex items-center gap-3" style="display: flex; align-items: center; gap: 12px;">
                    <div class="w-8 h-8 rounded-full bg-indigo-500 flex items-center justify-center text-white font-bold text-sm" style="width: 32px; height: 32px; border-radius: 9999px; background-color: #6366f1; display: flex; align-items: center; justify-content: center; font-weight: bold; color: #ffffff;">
                        AI
                    </div>
                    <div>
                        <h3 class="font-semibold text-white text-sm" style="font-weight: 600; font-size: 14px; margin: 0; color: #ffffff;">UE Connect Assistant</h3>
                        <p class="text-xs text-zinc-400" style="font-size: 12px; margin: 0; color: #a1a1aa;">Luôn sẵn sàng hỗ trợ</p>
                    </div>
                </div>
                <button wire:click="toggleChat" class="text-zinc-400 hover:text-white transition-colors" style="background: none; border: none; cursor: pointer; color: #a1a1aa;">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" style="width: 20px; height: 20px;">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                </button>
            </div>

            <!-- Messages Area -->
            <div class="flex-1 overflow-y-auto p-4 space-y-4 bg-zinc-50 dark:bg-zinc-900" id="chatbot-messages-container" style="flex: 1; overflow-y: auto; padding: 16px; display: flex; flex-direction: column; gap: 16px;">
                @foreach($messages as $message)
                    @if($message['role'] === 'model')
                        <div class="flex gap-3 max-w-[85%]" style="display: flex; gap: 12px; max-width: 85%;">
                            <div class="w-6 h-6 rounded-full bg-indigo-100 dark:bg-indigo-900/50 flex-shrink-0 flex items-center justify-center text-indigo-600 dark:text-indigo-400 text-xs font-bold mt-1" style="width: 24px; height: 24px; border-radius: 9999px; background-color: #e0e7ff; color: #4f46e5; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: bold; flex-shrink: 0;">
                                AI
                            </div>
                            <div class="bg-white dark:bg-zinc-800 border border-zinc-100 dark:border-zinc-700 p-3 rounded-2xl rounded-tl-sm shadow-sm text-sm text-zinc-700 dark:text-zinc-300 prose prose-sm dark:prose-invert" style="padding: 12px; border-radius: 16px 16px 16px 4px; box-shadow: 0 1px 2px 0 rgba(0,0,0,0.05); font-size: 14px; line-height: 1.5;">
                                {!! Str::markdown($message['content']) !!}
                            </div>
                        </div>
                    @else
                        <div class="flex justify-end gap-3" style="display: flex; justify-content: flex-end; gap: 12px;">
                            <div class="bg-indigo-600 p-3 rounded-2xl rounded-tr-sm shadow-sm text-sm text-white max-w-[85%]" style="background-color: #4f46e5; color: #ffffff; padding: 12px; border-radius: 16px 16px 4px 16px; box-shadow: 0 1px 2px 0 rgba(0,0,0,0.05); font-size: 14px; max-width: 85%; line-height: 1.5;">
                                {{ $message['content'] }}
                            </div>
                        </div>
                    @endif
                @endforeach

                @if($isTyping)
                    <div class="flex gap-3 max-w-[85%]" style="display: flex; gap: 12px; max-width: 85%;">
                        <div class="w-6 h-6 rounded-full bg-indigo-100 dark:bg-indigo-900/50 flex-shrink-0 flex items-center justify-center text-indigo-600 dark:text-indigo-400 text-xs font-bold mt-1" style="width: 24px; height: 24px; border-radius: 9999px; background-color: #e0e7ff; color: #4f46e5; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: bold; flex-shrink: 0;">
                            AI
                        </div>
                        <div class="bg-white dark:bg-zinc-800 border border-zinc-100 dark:border-zinc-700 p-4 rounded-2xl rounded-tl-sm shadow-sm flex items-center gap-1" style="padding: 16px; border-radius: 16px 16px 16px 4px; display: flex; align-items: center; gap: 4px;">
                            <div class="w-1.5 h-1.5 bg-zinc-400 rounded-full animate-bounce" style="width: 6px; height: 6px; background-color: #a1a1aa; border-radius: 9999px; animation: bounce 1s infinite; animation-delay: 0ms"></div>
                            <div class="w-1.5 h-1.5 bg-zinc-400 rounded-full animate-bounce" style="width: 6px; height: 6px; background-color: #a1a1aa; border-radius: 9999px; animation: bounce 1s infinite; animation-delay: 150ms"></div>
                            <div class="w-1.5 h-1.5 bg-zinc-400 rounded-full animate-bounce" style="width: 6px; height: 6px; background-color: #a1a1aa; border-radius: 9999px; animation: bounce 1s infinite; animation-delay: 300ms"></div>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Input Area -->
            <div class="p-3 bg-white dark:bg-zinc-950 border-t border-zinc-100 dark:border-zinc-800 z-10" style="padding: 12px;">
                <form wire:submit="sendMessage" class="relative flex items-center" style="position: relative; display: flex; align-items: center;">
                    <input 
                        wire:model="input" 
                        type="text" 
                        placeholder="Nhập câu hỏi của bạn..." 
                        class="w-full bg-zinc-100 dark:bg-zinc-900 border-none rounded-full py-3 pl-4 pr-12 text-sm text-zinc-900 dark:text-zinc-100 focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-400 transition-all placeholder-zinc-400 dark:placeholder-zinc-500"
                        style="width: 100%; padding: 12px 48px 12px 16px; border: none; border-radius: 9999px; font-size: 14px; outline: none;"
                        @if($isTyping) disabled @endif
                    >
                    <button 
                        type="submit" 
                        class="absolute right-2 p-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-full transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                        style="position: absolute; right: 8px; width: 32px; height: 32px; background-color: #4f46e5; border: none; border-radius: 9999px; color: #ffffff; display: flex; align-items: center; justify-content: center; cursor: pointer;"
                        @if($isTyping) disabled @endif
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" style="width: 16px; height: 16px;">
                            <path d="M10.894 2.553a1 1 0 00-1.788 0l-7 14a1 1 0 001.169 1.409l5-1.429A1 1 0 009 15.571V11a1 1 0 112 0v4.571a1 1 0 00.725.962l5 1.428a1 1 0 001.17-1.408l-7-14z" />
                        </svg>
                    </button>
                </form>
            </div>
            
            <!-- Auto Scroll Script -->
            <script>
                document.addEventListener('livewire:initialized', () => {
                    Livewire.hook('morph.updated', (el, component) => {
                        const container = document.getElementById('chatbot-messages-container');
                        if (container) {
                            container.scrollTop = container.scrollHeight;
                        }
                    });
                });
            </script>
        </div>
    @endif

    <!-- Toggle Button -->
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
</div>
