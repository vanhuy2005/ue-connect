<?php

use App\Models\AiQuestion;
use App\Models\AiAnswer;
use App\Models\AiFeedback;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    public string $search = '';
    public string $intent = '';
    public string $route = '';
    public string $feedbackFilter = ''; // all, negative (1-2), positive (4-5), unrated
    public bool $unansweredOnly = false;

    public ?int $activeQuestionId = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'intent' => ['except' => ''],
        'route' => ['except' => ''],
        'feedbackFilter' => ['except' => ''],
        'unansweredOnly' => ['except' => false],
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingIntent(): void
    {
        $this->resetPage();
    }

    public function updatingRoute(): void
    {
        $this->resetPage();
    }

    public function updatingFeedbackFilter(): void
    {
        $this->resetPage();
    }

    public function updatingUnansweredOnly(): void
    {
        $this->resetPage();
    }

    public function showDetail(int $questionId): void
    {
        $this->activeQuestionId = $questionId;
    }

    public function closeDetail(): void
    {
        $this->activeQuestionId = null;
    }

    public function getIntentsProperty(): array
    {
        return [
            'curriculum' => 'Chương trình đào tạo',
            'graduation' => 'Điều kiện tốt nghiệp',
            'policy' => 'Quy chế học vụ',
            'general' => 'Hỏi đáp chung',
            'clarification' => 'Yêu cầu làm rõ',
            'unsupported' => 'Ngoài phạm vi',
        ];
    }

    public function getRoutesProperty(): array
    {
        return [
            'structured_db' => 'Cơ sở dữ liệu CTĐT',
            'rag' => 'Quy chế (RAG)',
            'hybrid' => 'Tổng hợp (Hybrid)',
            'none' => 'Không tra cứu',
        ];
    }

    public function getQuestionsProperty()
    {
        $query = AiQuestion::with(['user', 'answer.feedbacks', 'retrievedChunks', 'structuredQueries'])
            ->latest('created_at');

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('original_question', 'like', '%' . $this->search . '%')
                  ->orWhere('normalized_question', 'like', '%' . $this->search . '%')
                  ->orWhereHas('answer', function ($sub) {
                      $sub->where('answer_text', 'like', '%' . $this->search . '%');
                  });
            });
        }

        if ($this->intent) {
            $query->where('intent', $this->intent);
        }

        if ($this->route) {
            $query->where('source_route', $this->route);
        }

        if ($this->unansweredOnly) {
            $query->whereIn('intent', ['unsupported', 'clarification']);
        }

        if ($this->feedbackFilter) {
            if ($this->feedbackFilter === 'unrated') {
                $query->whereHas('answer', function ($sub) {
                    $sub->whereDoesntHave('feedbacks');
                });
            } else {
                $query->whereHas('answer.feedbacks', function ($sub) {
                    if ($this->feedbackFilter === 'negative') {
                        $sub->where('rating', '<=', 2);
                    } elseif ($this->feedbackFilter === 'positive') {
                        $sub->where('rating', '>=', 4);
                    }
                });
            }
        }

        return $query->paginate(20);
    }

    public function getActiveQuestionProperty(): ?AiQuestion
    {
        if (!$this->activeQuestionId) {
            return null;
        }

        return AiQuestion::with(['user', 'answer.feedbacks.user', 'retrievedChunks', 'structuredQueries'])
            ->find($this->activeQuestionId);
    }
}; ?>

<div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    {{-- Header --}}
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-ue-text">Giám sát & Nhật ký AI Chatbot</h1>
        <p class="text-sm text-ue-text-secondary mt-1">
            Theo dõi chất lượng phản hồi từ chatbot, phân tích dữ liệu truy xuất RAG, kế hoạch truy vấn cơ sở dữ liệu và đánh giá từ sinh viên.
        </p>
    </div>

    {{-- Filters --}}
    <x-ui.card class="mb-6">
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-4 items-end">
            {{-- Search --}}
            <div class="col-span-1 sm:col-span-2 md:col-span-1">
                <x-ui.label for="search" class="text-xs">Tìm kiếm nội dung</x-ui.label>
                <x-ui.input wire:model.live.debounce.300ms="search" id="search" placeholder="Từ khóa câu hỏi/trả lời..." class="mt-1 h-9 text-xs" />
            </div>

            {{-- Route --}}
            <div>
                <x-ui.label for="route" class="text-xs">Đường dẫn truy xuất (Route)</x-ui.label>
                <x-ui.select wire:model.live="route" id="route" class="mt-1 h-9 text-xs py-1">
                    <option value="">-- Tất cả --</option>
                    @foreach ($this->routes as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </x-ui.select>
            </div>

            {{-- Intent --}}
            <div>
                <x-ui.label for="intent" class="text-xs">Ý định (Intent)</x-ui.label>
                <x-ui.select wire:model.live="intent" id="intent" class="mt-1 h-9 text-xs py-1">
                    <option value="">-- Tất cả --</option>
                    @foreach ($this->intents as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </x-ui.select>
            </div>

            {{-- Feedback --}}
            <div>
                <x-ui.label for="feedbackFilter" class="text-xs">Đánh giá người dùng</x-ui.label>
                <x-ui.select wire:model.live="feedbackFilter" id="feedbackFilter" class="mt-1 h-9 text-xs py-1">
                    <option value="">-- Tất cả --</option>
                    <option value="positive">Tích cực (4-5⭐)</option>
                    <option value="negative">Tiêu cực (1-2⭐)</option>
                    <option value="unrated">Chưa đánh giá</option>
                </x-ui.select>
            </div>

            {{-- Unanswered only --}}
            <div class="flex items-center pb-2.5">
                <label class="inline-flex items-center text-xs font-semibold text-slate-700 dark:text-zinc-300 cursor-pointer">
                    <input type="checkbox" wire:model.live="unansweredOnly" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 mr-2 h-4 w-4">
                    <span>Không trả lời được/Fallback</span>
                </label>
            </div>
        </div>
    </x-ui.card>

    {{-- Questions Table --}}
    <x-ui.card padding="none" class="overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-ue-border text-left text-sm">
                <thead class="bg-ue-surface-subtle text-xs font-bold text-ue-text-muted uppercase tracking-wider">
                    <tr>
                        <th scope="col" class="px-6 py-4">Thời gian / Sinh viên</th>
                        <th scope="col" class="px-6 py-4">Câu hỏi</th>
                        <th scope="col" class="px-6 py-4">Route</th>
                        <th scope="col" class="px-6 py-4">Ý định</th>
                        <th scope="col" class="px-6 py-4 text-center">Đánh giá</th>
                        <th scope="col" class="px-6 py-4 text-right">Thao tác</th>
                    </tr>
                </thead>
                <tbody class="bg-ue-surface divide-y divide-ue-border">
                    @forelse ($this->questions as $q)
                        @php
                            $ratingSum = $q->answer?->feedbacks->sum('rating') ?? 0;
                            $ratingCount = $q->answer?->feedbacks->count() ?? 0;
                            $avgRating = $ratingCount > 0 ? round($ratingSum / $ratingCount, 1) : null;
                        @endphp
                        <tr class="hover:bg-ue-surface-hover transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-xs font-semibold text-ue-text">{{ $q->created_at->format('H:i d/m/Y') }}</div>
                                <div class="text-xs text-ue-text-muted mt-0.5">
                                    {{ $q->user?->name ?? 'Khách' }}
                                </div>
                            </td>
                            <td class="px-6 py-4 max-w-md">
                                <p class="text-xs font-medium text-ue-text line-clamp-2" title="{{ $q->original_question }}">
                                    {{ $q->original_question }}
                                </p>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($q->source_route === 'structured_db')
                                    <x-ui.badge variant="info">Structured DB</x-ui.badge>
                                @elseif($q->source_route === 'rag')
                                    <x-ui.badge variant="warning">RAG Handbook</x-ui.badge>
                                @elseif($q->source_route === 'hybrid')
                                    <x-ui.badge variant="success">Hybrid</x-ui.badge>
                                @else
                                    <x-ui.badge variant="danger">None</x-ui.badge>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-100 dark:bg-zinc-800 text-slate-700 dark:text-zinc-300">
                                    {{ $q->intent }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                @if($avgRating)
                                    <span class="inline-flex items-center gap-0.5 text-xs font-bold text-amber-500 bg-amber-50 dark:bg-amber-950/40 px-2 py-0.5 rounded-full">
                                        {{ $avgRating }} ⭐
                                    </span>
                                @else
                                    <span class="text-xs text-ue-text-disabled">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <button wire:click="showDetail({{ $q->id }})" 
                                        class="text-xs font-bold text-indigo-600 hover:text-indigo-900 transition-colors">
                                    Xem chi tiết
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <div class="py-6 flex flex-col items-center justify-center text-center space-y-2">
                                    <x-ui.icon name="message-square" size="lg" class="text-slate-300" />
                                    <h3 class="text-sm font-semibold text-slate-700 dark:text-zinc-300">Không tìm thấy bản ghi nào</h3>
                                    <p class="text-xs text-slate-400">Không có câu hỏi chatbot nào khớp với bộ lọc của bạn.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="bg-ue-surface border-t border-ue-border px-6 py-4">
            {{ $this->questions->links() }}
        </div>
    </x-ui.card>

    {{-- Detail Drawer Modal (Managed by AlpineJS or Livewire state) --}}
    @if($this->activeQuestion)
        @php
            $activeQ = $this->activeQuestion;
        @endphp
        <div class="fixed inset-0 z-50 overflow-hidden" aria-labelledby="slide-over-title" role="dialog" aria-modal="true">
            <div class="absolute inset-0 overflow-hidden">
                <!-- Overlay background -->
                <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-xs transition-opacity" 
                     wire:click="closeDetail"></div>

                <div class="pointer-events-none fixed inset-y-0 right-0 flex max-w-full pl-10">
                    <div class="pointer-events-auto w-screen max-w-2xl transform transition-transform duration-300 ease-in-out sm:duration-500">
                        <div class="flex h-full flex-col overflow-y-scroll bg-white dark:bg-zinc-900 shadow-2xl">
                            
                            <!-- Drawer Header -->
                            <div class="bg-slate-50 dark:bg-zinc-950 px-6 py-6 border-b border-slate-200 dark:border-zinc-800 sm:flex sm:items-center sm:justify-between">
                                <div>
                                    <h2 class="text-base font-bold text-slate-900 dark:text-zinc-100" id="slide-over-title">
                                        Chi tiết câu hỏi #{{ $activeQ->id }}
                                    </h2>
                                    <p class="mt-1 text-xs text-slate-500 dark:text-zinc-400">
                                        Vấn tin lúc {{ $activeQ->created_at->format('H:i d/m/Y') }}
                                    </p>
                                </div>
                                <button type="button" 
                                        wire:click="closeDetail"
                                        class="rounded-md text-slate-400 hover:text-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                    <span class="sr-only">Đóng</span>
                                    <x-ui.icon name="x" size="md" />
                                </button>
                            </div>

                            <!-- Drawer Body -->
                            <div class="relative flex-1 p-6 space-y-6 text-xs text-slate-800 dark:text-zinc-200">
                                
                                <!-- User & General section -->
                                <div class="grid grid-cols-2 gap-4 bg-slate-50 dark:bg-zinc-850 p-4 rounded-xl">
                                    <div>
                                        <p class="font-bold text-slate-400 uppercase tracking-wide text-[9px]">Người hỏi</p>
                                        <p class="font-semibold text-slate-800 dark:text-zinc-200 mt-1">
                                            {{ $activeQ->user?->name ?? 'Sinh viên ẩn danh' }}
                                        </p>
                                        <p class="text-slate-500 dark:text-zinc-455">{{ $activeQ->user?->email ?? '-' }}</p>
                                    </div>
                                    <div>
                                        <p class="font-bold text-slate-400 uppercase tracking-wide text-[9px]">Phân loại kỹ thuật</p>
                                        <div class="mt-1 flex flex-wrap gap-1.5">
                                            <span class="px-2 py-0.5 rounded-full font-bold bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400">
                                                Route: {{ $activeQ->source_route }}
                                            </span>
                                            <span class="px-2 py-0.5 rounded-full font-bold bg-slate-100 dark:bg-zinc-800 text-slate-700 dark:text-zinc-300">
                                                Intent: {{ $activeQ->intent }}
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Questions comparison -->
                                <div class="space-y-3">
                                    <div>
                                        <h3 class="font-bold text-slate-750 dark:text-zinc-300">Câu hỏi gốc từ người dùng:</h3>
                                        <p class="mt-1.5 p-3 bg-slate-50 dark:bg-zinc-850 rounded-lg italic font-medium">
                                            "{{ $activeQ->original_question }}"
                                        </p>
                                    </div>
                                    <div>
                                        <h3 class="font-bold text-slate-750 dark:text-zinc-300">Câu hỏi sau chuẩn hóa (Normalization):</h3>
                                        <p class="mt-1.5 p-3 bg-slate-50 dark:bg-zinc-850 rounded-lg text-slate-700 dark:text-zinc-400 font-mono">
                                            {{ $activeQ->normalized_question }}
                                        </p>
                                    </div>
                                </div>

                                <!-- AI Answer section -->
                                <div class="space-y-2">
                                    <h3 class="font-bold text-slate-750 dark:text-zinc-300">Phản hồi của AI Assistant:</h3>
                                    <div class="p-4 border border-slate-200 dark:border-zinc-800 bg-slate-50 dark:bg-zinc-950 rounded-xl space-y-3">
                                        <div class="prose prose-sm dark:prose-invert max-w-none text-slate-800 dark:text-zinc-300 leading-relaxed font-sans">
                                            {!! Str::markdown(e($activeQ->answer?->answer_text ?? 'Không có phản hồi nào được ghi lại.')) !!}
                                        </div>
                                        
                                        @if($activeQ->answer)
                                            <div class="border-t border-slate-200 dark:border-zinc-850 pt-2.5 grid grid-cols-3 gap-2 text-[10px] text-slate-400 dark:text-zinc-500 font-mono">
                                                <div>Model: {{ $activeQ->answer->model_provider }} / {{ $activeQ->answer->model_name }}</div>
                                                <div>Độ trễ: {{ $activeQ->answer->latency_ms }} ms</div>
                                                <div>Tokens: {{ $activeQ->answer->total_tokens }} ({{ $activeQ->answer->input_tokens }}i / {{ $activeQ->answer->output_tokens }}o)</div>
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <!-- Retrieved Chunks (RAG) -->
                                <div class="space-y-3">
                                    <h3 class="font-bold text-slate-750 dark:text-zinc-300 flex items-center gap-1.5">
                                        <x-ui.icon name="book" size="xs" />
                                        <span>Dữ liệu RAG Chunks được tham chiếu ({{ $activeQ->retrievedChunks->count() }})</span>
                                    </h3>
                                    @forelse($activeQ->retrievedChunks as $chunk)
                                        <div class="border border-slate-150 dark:border-zinc-800 rounded-lg p-3 space-y-2 bg-white dark:bg-zinc-900">
                                            <div class="flex items-center justify-between text-[10px]">
                                                <span class="font-bold text-indigo-600 dark:text-indigo-400">
                                                    {{ $chunk->metadata_json['document_name'] ?? 'Tài liệu không tên' }}
                                                </span>
                                                <span class="font-mono text-slate-400">
                                                    Score: {{ round($chunk->score, 4) }}
                                                </span>
                                            </div>
                                            <div class="text-[10px] text-slate-455 flex gap-3">
                                                @if(!empty($chunk->metadata_json['article']))
                                                    <span>Điều khoản: {{ $chunk->metadata_json['article'] }}</span>
                                                @endif
                                                @if(!empty($chunk->metadata_json['page_start']))
                                                    <span>Trang: {{ $chunk->metadata_json['page_start'] }}</span>
                                                @endif
                                                @if(!empty($chunk->metadata_json['document_type']))
                                                    <span>Phân loại: {{ $chunk->metadata_json['document_type'] }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    @empty
                                        <p class="text-slate-400 dark:text-zinc-500 italic pl-2">Không sử dụng RAG retrieval cho câu hỏi này.</p>
                                    @endforelse
                                </div>

                                <!-- Structured Queries logs -->
                                <div class="space-y-3">
                                    <h3 class="font-bold text-slate-750 dark:text-zinc-300 flex items-center gap-1.5">
                                        <x-ui.icon name="database" size="xs" />
                                        <span>Truy vấn Structured DB kế hoạch ({{ $activeQ->structuredQueries->count() }})</span>
                                    </h3>
                                    @forelse($activeQ->structuredQueries as $sq)
                                        <div class="border border-slate-150 dark:border-zinc-800 rounded-lg p-3 space-y-2 bg-white dark:bg-zinc-900">
                                            <div class="flex items-center justify-between text-[10px]">
                                                <span class="font-bold text-blue-600 dark:text-blue-400">
                                                    Loại truy vấn: {{ $sq->query_type }}
                                                </span>
                                                <span class="font-mono text-slate-400">
                                                    Kết quả: {{ $sq->result_count }} bản ghi
                                                </span>
                                            </div>
                                            
                                            <!-- Filters JSON -->
                                            <div class="space-y-1">
                                                <p class="text-[10px] font-bold text-slate-400 uppercase">Bộ lọc áp dụng:</p>
                                                <pre class="bg-slate-50 dark:bg-zinc-950 p-2 rounded text-[10px] font-mono overflow-x-auto text-slate-600 dark:text-zinc-400">{!! json_encode($sq->filters_json, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) !!}</pre>
                                            </div>
                                        </div>
                                    @empty
                                        <p class="text-slate-400 dark:text-zinc-500 italic pl-2">Không thực hiện truy vấn DB trực tiếp cho câu hỏi này.</p>
                                    @endforelse
                                </div>

                                <!-- User feedback detailed -->
                                <div class="space-y-3">
                                    <h3 class="font-bold text-slate-750 dark:text-zinc-300">Đánh giá & Phản hồi từ sinh viên:</h3>
                                    @if($activeQ->answer && $activeQ->answer->feedbacks->isNotEmpty())
                                        @foreach($activeQ->answer->feedbacks as $fb)
                                            <div class="border border-slate-150 dark:border-zinc-800 rounded-lg p-3 bg-white dark:bg-zinc-900 space-y-1">
                                                <div class="flex items-center justify-between">
                                                    <span class="font-bold text-slate-700 dark:text-zinc-300">
                                                        Sinh viên: {{ $fb->user?->name ?? 'Ẩn danh' }}
                                                    </span>
                                                    <span class="font-bold text-amber-500">
                                                        {{ $fb->rating }} ⭐
                                                    </span>
                                                </div>
                                                @if($fb->comment)
                                                    <p class="text-slate-600 dark:text-zinc-400 mt-1 italic">
                                                        "{{ $fb->comment }}"
                                                    </p>
                                                @else
                                                    <p class="text-slate-400 dark:text-zinc-500 italic">Không có nhận xét bổ sung.</p>
                                                @endif
                                            </div>
                                        @endforeach
                                    @else
                                        <p class="text-slate-400 dark:text-zinc-500 italic pl-2">Sinh viên chưa đưa ra đánh giá cho phản hồi này.</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
