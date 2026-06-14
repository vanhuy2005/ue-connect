<?php

use App\AI\HcmueChatbot\Chat\HcmueChatService;
use App\Models\ChatSession;
use App\Models\AiQuestion;
use App\Models\AiAnswer;
use App\Models\AiFeedback;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.app', ['shell' => 'conversation'])] class extends Component
{
    public ?int $selectedSessionId = null;
    public string $input = '';
    public bool $isTyping = false;
    
    // Title edit state
    public bool $isEditingTitle = false;
    public string $editTitleInput = '';
    
    // Feedback state
    public array $activeFeedbackAnswerId = []; // key: answer_id, value: rating
    public array $feedbackComments = []; // key: answer_id, value: comment text
    public array $submittedFeedback = []; // key: answer_id, value: true

    protected $listeners = ['scroll-bottom' => 'scrollBottom'];

    public function mount(): void
    {
        $user = Auth::user();
        // Resolve latest session or create one
        $latestSession = ChatSession::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->first();

        if ($latestSession) {
            $this->selectedSessionId = $latestSession->id;
        } else {
            $this->createNewSession();
        }
        
        $this->loadSubmittedFeedback();
    }

    public function createNewSession(): void
    {
        $user = Auth::user();
        $session = ChatSession::create([
            'user_id' => $user->id,
            'title' => 'Cuộc hội thoại ' . now()->format('d/m H:i'),
        ]);

        $this->selectedSessionId = $session->id;
        $this->input = '';
        $this->cancelEditingTitle();
    }

    public function selectSession(int $id): void
    {
        $session = ChatSession::where('user_id', Auth::id())->findOrFail($id);
        $this->selectedSessionId = $session->id;
        $this->input = '';
        $this->loadSubmittedFeedback();
        $this->cancelEditingTitle();
        $this->dispatch('scroll-bottom');
    }

    public function deleteSession(int $id): void
    {
        ChatSession::where('user_id', Auth::id())->where('id', $id)->delete();

        if ($this->selectedSessionId === $id) {
            $latest = ChatSession::where('user_id', Auth::id())
                ->orderBy('created_at', 'desc')
                ->first();

            if ($latest) {
                $this->selectedSessionId = $latest->id;
            } else {
                $this->createNewSession();
            }
        }
        $this->loadSubmittedFeedback();
        $this->cancelEditingTitle();
    }

    public function startEditingTitle(): void
    {
        if (!$this->selectedSessionId) {
            return;
        }
        $session = ChatSession::where('user_id', Auth::id())->findOrFail($this->selectedSessionId);
        $this->editTitleInput = $session->title;
        $this->isEditingTitle = true;
    }

    public function cancelEditingTitle(): void
    {
        $this->isEditingTitle = false;
        $this->editTitleInput = '';
        $this->resetErrorBag('editTitleInput');
    }

    public function saveTitle(): void
    {
        if (!$this->selectedSessionId) {
            return;
        }
        $this->editTitleInput = trim($this->editTitleInput);
        if (empty($this->editTitleInput)) {
            $this->addError('editTitleInput', 'Tên hội thoại không được để trống.');
            return;
        }
        if (mb_strlen($this->editTitleInput) > 100) {
            $this->addError('editTitleInput', 'Tên hội thoại không được quá 100 ký tự.');
            return;
        }

        $session = ChatSession::where('user_id', Auth::id())->findOrFail($this->selectedSessionId);
        $session->update([
            'title' => $this->editTitleInput
        ]);

        $this->isEditingTitle = false;
        $this->editTitleInput = '';
        $this->resetErrorBag('editTitleInput');
    }

    public function sendMessage(HcmueChatService $chatService): void
    {
        $trimmed = trim($this->input);
        if (empty($trimmed)) {
            return;
        }

        $session = ChatSession::where('user_id', Auth::id())->findOrFail($this->selectedSessionId);
        
        // Auto rename session if it's the default name
        if (str_starts_with($session->title, 'Cuộc hội thoại ')) {
            $session->update([
                'title' => mb_substr($trimmed, 0, 30) . (mb_strlen($trimmed) > 30 ? '...' : '')
            ]);
        }

        $userMessage = $trimmed;
        $this->input = '';
        $this->isTyping = true;

        // Force UI update
        $this->dispatch('scroll-bottom');

        try {
            // Process query
            $chatService->chat($userMessage, $session, Auth::user());
        } catch (\Exception $e) {
            Log::error('Chatbot execution error: ' . $e->getMessage());
            // Log a question + safe answer manually to not break UI
            $question = AiQuestion::create([
                'session_id' => $session->id,
                'user_id' => Auth::id(),
                'original_question' => $userMessage,
                'normalized_question' => $userMessage,
                'intent' => 'unsupported',
                'source_route' => 'none',
                'confidence' => 1.0,
                'created_at' => now(),
            ]);

            $friendlyMessage = 'Đã xảy ra lỗi hệ thống khi xử lý câu hỏi: ' . $e->getMessage();
            if (str_contains($e->getMessage(), '429') || str_contains($e->getMessage(), 'RESOURCE_EXHAUSTED') || str_contains($e->getMessage(), 'quota')) {
                $friendlyMessage = 'Lỗi kết nối AI (HTTP 429): Tài khoản của bạn đã vượt quá giới hạn tài nguyên (Quota Exceeded) hoặc bị giới hạn vùng/quốc gia. Vui lòng kiểm tra lại hạn mức tài khoản Google AI Studio hoặc đổi sang API Key khác.';
            } elseif (str_contains($e->getMessage(), '400') || str_contains($e->getMessage(), 'API_KEY_INVALID') || str_contains($e->getMessage(), 'key not valid')) {
                $friendlyMessage = 'Lỗi kết nối AI (HTTP 400): API Key không hợp lệ hoặc thiếu. Vui lòng điền đúng GEMINI_API_KEY trong file .env và clear cache config.';
            }

            AiAnswer::create([
                'question_id' => $question->id,
                'answer_text' => $friendlyMessage,
                'model_provider' => 'system',
                'model_name' => 'fallback',
                'prompt_version' => '1.0',
                'latency_ms' => 0,
                'created_at' => now(),
            ]);
        }

        $this->isTyping = false;
        $this->dispatch('scroll-bottom');
    }

    public function submitRating(int $answerId, int $rating): void
    {
        $this->activeFeedbackAnswerId[$answerId] = $rating;
    }

    public function submitFeedback(int $answerId): void
    {
        $rating = $this->activeFeedbackAnswerId[$answerId] ?? 5;
        $comment = $this->feedbackComments[$answerId] ?? null;

        $answer = AiAnswer::findOrFail($answerId);
        // Security check
        if ($answer->question->session->user_id !== Auth::id()) {
            return;
        }

        AiFeedback::updateOrCreate(
            [
                'answer_id' => $answerId,
                'user_id' => Auth::id(),
            ],
            [
                'rating' => $rating,
                'comment' => $comment,
                'created_at' => now(),
            ]
        );

        $this->submittedFeedback[$answerId] = [
            'rating' => $rating,
            'comment' => $comment
        ];
    }

    private function loadSubmittedFeedback(): void
    {
        if (!$this->selectedSessionId) {
            return;
        }

        $session = ChatSession::find($this->selectedSessionId);
        if (!$session) {
            return;
        }

        $questionIds = $session->questions()->pluck('id');
        $answers = AiAnswer::whereIn('question_id', $questionIds)->get();

        foreach ($answers as $answer) {
            $fb = AiFeedback::where('answer_id', $answer->id)->where('user_id', Auth::id())->first();
            if ($fb) {
                $this->submittedFeedback[$answer->id] = [
                    'rating' => $fb->rating,
                    'comment' => $fb->comment
                ];
            }
        }
    }

    public function with(): array
    {
        $sessions = ChatSession::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get();

        $currentSession = null;
        $chatMessages = [];

        if ($this->selectedSessionId) {
            $currentSession = ChatSession::where('user_id', Auth::id())->find($this->selectedSessionId);
            if ($currentSession) {
                // Fetch questions with answers & retrieved chunks
                $questions = $currentSession->questions()
                    ->with(['answer.feedbacks', 'retrievedChunks'])
                    ->orderBy('created_at', 'asc')
                    ->get();

                foreach ($questions as $q) {
                    $sources = [];
                    if ($q->source_route === 'structured_db' || $q->source_route === 'hybrid') {
                        // Look up query log metadata
                        $structuredQuery = $q->structuredQueries()->first();
                        $sources[] = [
                            'type' => 'structured_db',
                            'title' => $structuredQuery->metadata_json['program_title'] ?? 'Dữ liệu Chương trình đào tạo',
                            'type_name' => 'Structured DB',
                        ];
                    }

                    foreach ($q->retrievedChunks as $chunk) {
                        $sources[] = [
                            'type' => 'rag',
                            'title' => $chunk->metadata_json['document_name'] ?? 'Tài liệu sổ tay',
                            'article' => $chunk->metadata_json['article'] ?? null,
                            'page' => $chunk->metadata_json['page_start'] ?? null,
                            'score' => $chunk->score,
                        ];
                    }

                    $chatMessages[] = [
                        'question_id' => $q->id,
                        'question_text' => $q->original_question,
                        'answer_id' => $q->answer?->id,
                        'answer_text' => $q->answer?->answer_text ?? 'Đang phản hồi...',
                        'route' => $q->source_route,
                        'intent' => $q->intent,
                        'sources' => $sources,
                        'created_at' => $q->created_at,
                    ];
                }
            }
        }

        return [
            'sessions' => $sessions,
            'currentSession' => $currentSession,
            'chatMessages' => $chatMessages,
        ];
    }
}
?>

<div class="flex h-[calc(100vh-64px)] lg:h-screen bg-slate-50 dark:bg-zinc-950 overflow-hidden" 
     x-data="{ sidebarOpen: false }"
     x-init="
        $wire.on('scroll-bottom', () => {
            $nextTick(() => {
                const el = document.getElementById('chat-messages-scroll');
                if (el) el.scrollTop = el.scrollHeight;
            });
        });
     ">
     
    <!-- Left Sidebar: Sessions List -->
    <aside class="w-80 bg-white dark:bg-zinc-900 border-r border-slate-200 dark:border-zinc-800 flex flex-col flex-shrink-0 transition-transform duration-300 lg:translate-x-0 lg:static fixed inset-y-0 left-0 z-40 transform"
           :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'">
        
        <!-- Sidebar Header -->
        <div class="p-4 border-b border-slate-200 dark:border-zinc-800 flex items-center justify-between">
            <h2 class="font-bold text-slate-800 dark:text-zinc-100 flex items-center gap-2">
                <x-ui.icon name="message-square" class="text-indigo-600 dark:text-indigo-400" />
                <span>Lịch sử hội thoại</span>
            </h2>
            <button @click="sidebarOpen = false" class="lg:hidden p-1 text-slate-500 hover:bg-slate-100 rounded-lg">
                <x-ui.icon name="x" size="sm" />
            </button>
        </div>

        <!-- New Chat Button -->
        <div class="p-4">
            <button type="button" wire:click="startNewConversation"
                    class="w-full py-2.5 px-4 bg-ue-brand hover:bg-ue-brand-hover text-white rounded-xl font-medium shadow-sm transition-all duration-150 flex items-center justify-center gap-2">
                <x-ui.icon name="plus" size="sm" />
                <span>Hội thoại mới</span>
            </button>
        </div>

        <!-- Sessions List -->
        <div class="flex-1 overflow-y-auto px-2 space-y-1 pb-4">
            @forelse($sessions as $s)
                <div class="group relative flex items-center rounded-xl transition-all duration-150
                            {{ $selectedSessionId === $s->id ? 'bg-ue-brand-soft text-ue-brand' : 'hover:bg-slate-100 dark:hover:bg-zinc-800 text-slate-700 dark:text-zinc-300' }}">
                    <button wire:click="selectSession({{ $s->id }})" 
                            class="flex-1 text-left px-3 py-3 pr-10 text-xs font-semibold truncate focus:outline-none">
                        {{ $s->title }}
                    </button>
                    <button wire:click="deleteSession({{ $s->id }})" 
                            class="absolute right-2 p-1.5 text-slate-400 hover:text-red-500 rounded-lg opacity-0 group-hover:opacity-100 transition-opacity duration-150"
                            title="Xóa hội thoại">
                        <x-ui.icon name="trash" size="xs" />
                    </button>
                </div>
            @empty
                <div class="text-center py-8 text-slate-400 dark:text-zinc-500 text-xs">
                    Chưa có hội thoại nào
                </div>
            @endforelse
        </div>
    </aside>

    <!-- Overlay for mobile sidebar -->
    <div x-show="sidebarOpen" 
         @click="sidebarOpen = false" 
         class="fixed inset-0 bg-black/40 backdrop-blur-sm z-30 lg:hidden"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
    </div>

    <!-- Right Chat Area -->
    <main class="flex-1 flex flex-col min-h-0 h-full min-w-0 bg-slate-50 dark:bg-zinc-950 relative">
        
        <!-- Header -->
        <header class="h-16 bg-white dark:bg-zinc-900 border-b border-slate-200 dark:border-zinc-800 px-4 flex items-center justify-between gap-3">
            <div class="flex items-center gap-3 flex-1 min-w-0">
                <button @click="sidebarOpen = true" class="lg:hidden p-2 text-slate-500 hover:bg-slate-100 dark:hover:bg-zinc-800 rounded-lg">
                    <x-ui.icon name="menu" />
                </button>
                @if($isEditingTitle)
                    <div class="flex flex-col flex-1 max-w-md">
                        <div class="flex items-center gap-2 w-full">
                            <input type="text" 
                                   wire:model.defer="editTitleInput" 
                                   wire:keydown.enter="saveTitle"
                                   wire:keydown.escape="cancelEditingTitle"
                                   class="bg-slate-50 dark:bg-zinc-800 border border-slate-300 dark:border-zinc-700 rounded-lg px-3 py-1.5 text-xs text-slate-800 dark:text-zinc-200 focus:outline-none focus:ring-1 focus:ring-indigo-500 w-full"
                                   placeholder="Tên hội thoại..."
                                   autofocus>
                            <button wire:click="saveTitle" class="px-3 py-1.5 bg-ue-brand hover:bg-ue-brand-hover text-white rounded-lg text-xs font-semibold flex-shrink-0">
                                Lưu
                            </button>
                            <button wire:click="cancelEditingTitle" class="px-3 py-1.5 bg-slate-200 hover:bg-slate-300 text-slate-700 dark:bg-zinc-800 dark:hover:bg-zinc-700 dark:text-zinc-300 rounded-lg text-xs font-semibold flex-shrink-0">
                                Hủy
                            </button>
                        </div>
                        @error('editTitleInput')
                            <span class="text-[10px] text-red-500 mt-1 font-semibold">{{ $message }}</span>
                        @enderror
                    </div>
                @else
                    <div class="flex items-center gap-2 min-w-0">
                        <div class="min-w-0">
                            <h1 class="font-bold text-slate-800 dark:text-zinc-100 text-sm leading-tight truncate">
                                {{ $currentSession ? $currentSession->title : 'AI Chatbot' }}
                            </h1>
                            <p class="text-[10px] text-slate-400 dark:text-zinc-500">Trợ lý học vụ ĐH Sư Phạm TPHCM</p>
                        </div>
                        @if($currentSession)
                            <button wire:click="startEditingTitle" class="p-1.5 text-slate-400 hover:text-slate-600 dark:text-zinc-500 dark:hover:text-zinc-300 rounded-lg transition-colors flex-shrink-0" title="Đổi tên hội thoại">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M12 20h9"/>
                                    <path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/>
                                </svg>
                            </button>
                        @endif
                    </div>
                @endif
            </div>
        </header>

        <!-- Messages scroll container -->
        <div id="chat-messages-scroll" class="flex-1 min-h-0 overflow-y-auto p-4 space-y-6 flex flex-col justify-start items-stretch">
            @if(empty($chatMessages))
                <!-- Starter / Welcome state -->
                <div class="max-w-2xl mx-auto py-12 px-4 text-center space-y-6">
                    <div class="w-16 h-16 bg-ue-brand-soft rounded-2xl flex items-center justify-center mx-auto text-ue-brand">
                        <x-ui.icon name="message-square" class="w-8 h-8" />
                    </div>
                    <div class="space-y-2">
                        <h2 class="text-lg font-bold text-slate-800 dark:text-zinc-100">Chào mừng bạn đến với HCMUE Academic Chatbot</h2>
                        <p class="text-xs text-slate-500 dark:text-zinc-400 max-w-md mx-auto">
                            Tôi có thể giúp bạn giải đáp thắc mắc về chương trình đào tạo, học kỳ, tín chỉ, môn học tự chọn cũng như các quy chế học vụ và sổ tay sinh viên.
                        </p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 max-w-xl mx-auto pt-6 text-left">
                        <button wire:click="$set('input', 'Ngành Công nghệ thông tin K51 có bao nhiêu tín chỉ?')" 
                                class="p-3 bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 rounded-xl hover:border-indigo-500 hover:shadow-sm text-xs font-semibold text-slate-700 dark:text-zinc-300 transition-all duration-150">
                            Ngành CNTT K51 có bao nhiêu tín chỉ?
                        </button>
                        <button wire:click="$set('input', 'Điều kiện tốt nghiệp là gì?')" 
                                class="p-3 bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 rounded-xl hover:border-indigo-500 hover:shadow-sm text-xs font-semibold text-slate-700 dark:text-zinc-300 transition-all duration-150">
                            Điều kiện tốt nghiệp của sinh viên là gì?
                        </button>
                        <button wire:click="$set('input', 'Học kỳ 1 ngành Công nghệ thông tin K51 có môn nào?')" 
                                class="p-3 bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 rounded-xl hover:border-indigo-500 hover:shadow-sm text-xs font-semibold text-slate-700 dark:text-zinc-300 transition-all duration-150">
                            Học kỳ 1 ngành CNTT K51 học những gì?
                        </button>
                        <button wire:click="$set('input', 'Nếu em không đạt học phần bắt buộc thì phải làm sao?')" 
                                class="p-3 bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 rounded-xl hover:border-indigo-500 hover:shadow-sm text-xs font-semibold text-slate-700 dark:text-zinc-300 transition-all duration-150">
                            Nếu không đạt môn bắt buộc thì thế nào?
                        </button>
                    </div>
                </div>
            @else
                <!-- Messages List -->
                <div class="max-w-4xl w-full mx-auto px-6 pt-12 pb-6 space-y-6">
                    @foreach($chatMessages as $msg)
                        <!-- User message -->
                        <div class="flex justify-end">
                            <div class="bg-ue-brand text-white rounded-2xl rounded-tr-md px-5 py-3 max-w-[70%] shadow-xs text-xs font-medium leading-relaxed">
                                {{ $msg['question_text'] }}
                            </div>
                        </div>

                        <!-- Bot response -->
                        <div class="space-y-2">
                            <div class="flex items-start gap-4">
                                <!-- Bot avatar -->
                                <div class="w-8 h-8 rounded-xl bg-ue-brand-soft flex-shrink-0 flex items-center justify-center text-ue-brand font-bold text-xs">
                                    AI
                                </div>

                                <!-- Response body card -->
                                <div class="flex-1 max-w-[760px] w-full bg-white dark:bg-zinc-900 border border-slate-100 dark:border-zinc-800 rounded-3xl rounded-tl-lg shadow-sm p-6 space-y-4">
                                    
                                    <!-- Route badge -->
                                    <div class="flex items-center gap-2">
                                        @if($msg['route'] === 'structured_db')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[9px] font-bold bg-blue-50 dark:bg-blue-950/40 text-blue-600 dark:text-blue-400 border border-blue-150 dark:border-blue-900/40">
                                                Cơ sở dữ liệu CTĐT
                                            </span>
                                        @elseif($msg['route'] === 'rag')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[9px] font-bold bg-purple-50 dark:bg-purple-950/40 text-purple-600 dark:text-purple-400 border border-purple-150 dark:border-purple-900/40">
                                                Quy chế học vụ / Sổ tay
                                            </span>
                                        @elseif($msg['route'] === 'hybrid')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[9px] font-bold bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 border border-emerald-150 dark:border-emerald-900/40">
                                                Tổng hợp CTĐT & Quy chế
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[9px] font-bold bg-slate-50 dark:bg-zinc-850 text-slate-600 dark:text-zinc-400 border border-slate-150 dark:border-zinc-800">
                                                Hệ thống
                                            </span>
                                        @endif

                                        <span class="text-[10px] text-slate-400 dark:text-zinc-500 font-medium">
                                            {{ $msg['created_at']->diffForHumans() }}
                                        </span>
                                    </div>

                                    <!-- Response text -->
                                    <div class="text-slate-700 dark:text-zinc-300 text-xs leading-relaxed prose prose-sm dark:prose-invert max-w-none">
                                        {!! Str::markdown(e($msg['answer_text'])) !!}
                                    </div>

                                    <!-- Citations / Sources collapsible panel -->
                                    @if(!empty($msg['sources']))
                                        <div x-data="{ open: false }" class="border-t border-slate-100 dark:border-zinc-850 pt-2.5">
                                            <button @click="open = !open" 
                                                    class="flex items-center justify-between w-full text-[10px] font-bold text-slate-450 hover:text-slate-600 dark:text-zinc-400 transition-colors">
                                                <span class="flex items-center gap-1">
                                                    <x-ui.icon name="book-open" size="2xs" />
                                                    Nguồn tài liệu tham khảo ({{ count($msg['sources']) }})
                                                </span>
                                                <x-ui.icon name="chevron-down" size="2xs" class="transition-transform duration-200" ::class="open ? 'rotate-180' : ''" />
                                            </button>
                                            
                                            <div x-show="open" x-collapse class="mt-2 space-y-1.5 pl-3 border-l-2 border-ue-brand/50">
                                                @foreach($msg['sources'] as $src)
                                                    <div class="text-[10px] text-slate-500 dark:text-zinc-400">
                                                        <span class="font-bold text-slate-700 dark:text-zinc-350">
                                                            {{ $src['document_name'] ?? $src['title'] ?? 'Tài liệu' }}
                                                        </span>
                                                        @if(!empty($src['article']))
                                                            - <span class="text-ue-brand">{{ $src['article'] }}</span>
                                                        @endif
                                                        @if(!empty($src['page']))
                                                            (Trang {{ $src['page'] }})
                                                        @endif
                                                        @if($src['type'] === 'rag')
                                                            <span class="ml-1 text-[9px] text-slate-400">Độ khớp: {{ round($src['score'] * 100) }}%</span>
                                                        @endif
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif

                                    <!-- Feedback rating section -->
                                    @if($msg['answer_id'])
                                        <div class="border-t border-slate-100 dark:border-zinc-850 pt-2.5 flex items-center justify-between">
                                            @if(isset($submittedFeedback[$msg['answer_id']]))
                                                <span class="text-[10px] text-emerald-600 dark:text-emerald-400 font-semibold flex items-center gap-1">
                                                    <x-ui.icon name="check-circle" size="2xs" />
                                                    Đã phản hồi ({{ $submittedFeedback[$msg['answer_id']]['rating'] }}⭐)
                                                </span>
                                            @else
                                                <div class="flex items-center gap-2">
                                                    <span class="text-[10px] text-slate-400">Độ hữu ích:</span>
                                                    <div class="flex items-center gap-1">
                                                        @for($i = 1; $i <= 5; $i++)
                                                            <button wire:click="submitRating({{ $msg['answer_id'] }}, {{ $i }})" 
                                                                    class="p-0.5 text-slate-350 hover:text-amber-500 transition-colors">
                                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4.5 w-4.5 fill-current" 
                                                                     :class="({{ $activeFeedbackAnswerId[$msg['answer_id']] ?? 0 }} >= {{ $i }}) ? 'text-amber-500' : 'text-slate-300 hover:text-amber-400'" 
                                                                     viewBox="0 0 20 20">
                                                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                                                </svg>
                                                            </button>
                                                        @endfor
                                                    </div>
                                                </div>

                                                <!-- If rating is selected, show optional comment field -->
                                                @if(isset($activeFeedbackAnswerId[$msg['answer_id']]))
                                                    <div class="flex items-center gap-2 flex-1 max-w-xs ml-4" x-data="{ comment: '' }">
                                                        <input type="text" 
                                                               placeholder="Nhận xét (không bắt buộc)..." 
                                                               wire:model.defer="feedbackComments.{{ $msg['answer_id'] }}"
                                                               class="flex-1 bg-slate-50 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 rounded-lg px-2 py-1 text-[10px] text-slate-800 dark:text-zinc-200 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                                                        
                                                        <button wire:click="submitFeedback({{ $msg['answer_id'] }})"
                                                                class="px-2.5 py-1 bg-ue-brand hover:bg-ue-brand-hover text-white rounded-lg text-[10px] font-bold transition-all">
                                                            Gửi
                                                        </button>
                                                    </div>
                                                @endif
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach

                    <!-- Typing loader indicator -->
                    @if($isTyping)
                        <div class="flex items-start gap-4">
                            <div class="w-8 h-8 rounded-xl bg-ue-brand-soft flex-shrink-0 flex items-center justify-center text-ue-brand font-bold text-xs">
                                AI
                            </div>
                            <div class="bg-white dark:bg-zinc-900 border border-slate-100 dark:border-zinc-800 rounded-3xl rounded-tl-lg shadow-sm p-4 flex items-center gap-1.5">
                                <div class="w-1.5 h-1.5 bg-ue-brand rounded-full animate-bounce" style="animation-delay: 0ms"></div>
                                <div class="w-1.5 h-1.5 bg-ue-brand rounded-full animate-bounce" style="animation-delay: 150ms"></div>
                                <div class="w-1.5 h-1.5 bg-ue-brand rounded-full animate-bounce" style="animation-delay: 300ms"></div>
                            </div>
                        </div>
                    @endif
                </div>
            @endif
        </div>

        <!-- Input Area at bottom -->
        <div class="w-full bg-slate-50 dark:bg-zinc-950 border-t border-slate-200/80 dark:border-zinc-850/80 flex-shrink-0 z-10">
            <form wire:submit.prevent="sendMessage" class="max-w-4xl mx-auto px-6 pt-3 pb-1">
                <div class="relative flex items-center bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 rounded-3xl shadow-sm focus-within:ring-2 focus-within:ring-ue-brand focus-within:border-transparent transition-all">
                    <textarea 
                        wire:model.defer="input" 
                        x-init="$watch('input', value => { if (!value) { $nextTick(() => autoGrowTextarea($el, 180)); } })"
                        rows="1"
                        placeholder="Nhập thắc mắc học vụ của bạn..."
                        class="chat-page-input w-full bg-transparent border-none pl-6 pr-[88px] text-sm text-slate-800 dark:text-zinc-200 focus:outline-none focus:ring-0 focus:border-transparent"
                        style="height: 48px;"
                        oninput="autoGrowTextarea(this, 180)"
                        @keydown.enter="
                            if (!$event.shiftKey) {
                                $event.preventDefault();
                                $el.closest('form')?.dispatchEvent(new Event('submit', { bubbles: true, cancelable: true }));
                            }
                        "
                        @if($isTyping) disabled @endif
                    ></textarea>
                    
                    <div class="absolute right-2 bottom-1 flex items-center gap-2">
                        <button type="button" class="p-1.5 text-slate-400 hover:text-slate-600 dark:text-zinc-400 dark:hover:text-zinc-300 transition-colors cursor-not-allowed opacity-70" title="Nhập bằng giọng nói (Sắp ra mắt)" disabled>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 2a3 3 0 0 0-3 3v7a3 3 0 0 0 6 0V5a3 3 0 0 0-3-3Z"/>
                                <path d="M19 10v2a7 7 0 0 1-14 0v-2"/>
                                <line x1="12" y1="19" x2="12" y2="22"/>
                            </svg>
                        </button>
                        
                        <button type="submit" 
                                class="w-10 h-10 flex items-center justify-center bg-ue-brand hover:bg-ue-brand-hover text-white rounded-full transition-all disabled:opacity-50 disabled:cursor-not-allowed"
                                @if($isTyping) disabled @endif>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="5" y1="12" x2="19" y2="12"/>
                                <polyline points="12 5 19 12 12 19"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </main>
</div>

<script>
    if (typeof window.autoGrowTextarea !== 'function') {
        window.autoGrowTextarea = function(el, maxHeight = 180) {
            el.style.height = 'auto';
            const nextHeight = Math.min(el.scrollHeight, maxHeight);
            el.style.height = nextHeight + 'px';
            el.style.overflowY = el.scrollHeight > maxHeight ? 'auto' : 'hidden';
        };
    }
</script>

<style>
    .chat-page-input {
        resize: none;
        overflow-y: hidden;
        font-family: inherit;
        min-height: 48px !important;
        max-height: 180px !important;
        padding-top: 14px !important;
        padding-bottom: 14px !important;
        line-height: 1.5 !important;
    }
    .chat-page-input::placeholder {
        color: #94a3b8 !important;
        opacity: 1 !important;
    }
    .dark .chat-page-input::placeholder {
        color: #4b5563 !important;
    }
</style>
