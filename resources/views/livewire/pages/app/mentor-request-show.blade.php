<?php

use App\Actions\Mentor\CancelMentorRequestAction;
use App\Actions\Mentor\CompleteMentorRequestAction;
use App\Actions\Mentor\DeclineMentorRequestAction;
use App\Actions\Mentor\SubmitMentorFeedbackAction;
use App\Actions\Mentor\UpdateMentorRequestAction;
use App\Enums\MentorRequestStatus;
use App\Models\MentorRequest;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;

new class extends Component {
    public MentorRequest $mentorRequest;

    // Update form fields
    public string $updateTopic = '';
    public string $updateGoal = '';
    public string $updateQuestion = '';
    public string $updateUrgency = 'normal';
    public string $updateContext = '';
    public string $updateExpectedOutcome = '';

    // Feedback form fields
    public string $feedbackLevel = '';
    public string $feedbackText = '';
    public bool $isSubmittingFeedback = false;

    // Messages
    public ?string $statusMessage = null;
    public ?string $errorMessage = null;

    protected $listeners = ['refreshRequest' => '$refresh'];

    public function mount(): void
    {
        $this->updateTopic = $this->mentorRequest->topic;
        $this->updateGoal = $this->mentorRequest->goal;
        $this->updateQuestion = $this->mentorRequest->question;
        $this->updateUrgency = $this->mentorRequest->urgency->value;
        $this->updateContext = $this->mentorRequest->context ?? '';
        $this->updateExpectedOutcome = $this->mentorRequest->expected_outcome ?? '';
    }

    public function isStudent(): bool
    {
        return $this->mentorRequest->student_id === Auth::id();
    }

    public function isMentorOwner(): bool
    {
        return $this->mentorRequest->mentor_id === Auth::id();
    }

    public function hasFeedback(): bool
    {
        return $this->mentorRequest->feedback()->exists();
    }

    public function canAcceptOrDecline(): bool
    {
        return $this->isMentorOwner() && in_array($this->mentorRequest->status, [
            MentorRequestStatus::Submitted,
            MentorRequestStatus::NeedMoreInfo,
            MentorRequestStatus::UpdatedByStudent,
        ]);
    }

    public function canAskMoreInfo(): bool
    {
        return $this->isMentorOwner() && in_array($this->mentorRequest->status, [
            MentorRequestStatus::Submitted,
            MentorRequestStatus::UpdatedByStudent,
        ]);
    }

    public function canComplete(): bool
    {
        return $this->mentorRequest->status === MentorRequestStatus::Accepted
            && $this->mentorRequest->isParticipant(Auth::user());
    }

    public function canCancel(): bool
    {
        return $this->isStudent() && in_array($this->mentorRequest->status, [
            MentorRequestStatus::Submitted,
            MentorRequestStatus::NeedMoreInfo,
            MentorRequestStatus::UpdatedByStudent,
        ]);
    }

    public function canUpdate(): bool
    {
        return $this->isStudent() && $this->mentorRequest->status === MentorRequestStatus::NeedMoreInfo;
    }

    public function canGiveFeedback(): bool
    {
        return $this->isStudent()
            && $this->mentorRequest->status === MentorRequestStatus::Completed
            && ! $this->hasFeedback();
    }

    public function declineRequest(DeclineMentorRequestAction $action): void
    {
        try {
            $action->execute(Auth::user(), $this->mentorRequest);
            $this->mentorRequest->refresh();
            $this->statusMessage = 'Đã từ chối yêu cầu cố vấn.';
        } catch (\Exception $e) {
            $this->errorMessage = $e->getMessage();
        }
    }

    public function cancelRequest(CancelMentorRequestAction $action): void
    {
        try {
            $action->execute(Auth::user(), $this->mentorRequest);
            $this->mentorRequest->refresh();
            $this->statusMessage = 'Đã hủy yêu cầu cố vấn.';
        } catch (\Exception $e) {
            $this->errorMessage = $e->getMessage();
        }
    }

    public function completeRequest(CompleteMentorRequestAction $action): void
    {
        try {
            $action->execute(Auth::user(), $this->mentorRequest);
            $this->mentorRequest->refresh();
            $this->statusMessage = 'Yêu cầu cố vấn đã được đánh dấu hoàn thành.';
            if ($this->isStudent()) {
                $this->dispatch('open-feedback');
            }
        } catch (\Exception $e) {
            $this->errorMessage = $e->getMessage();
        }
    }

    public function updateRequest(UpdateMentorRequestAction $action): void
    {
        $this->validate([
            'updateTopic' => ['required', 'string', 'max:255'],
            'updateGoal' => ['required', 'string', 'max:5000'],
            'updateQuestion' => ['required', 'string', 'max:5000'],
            'updateUrgency' => ['required', 'string', 'in:low,normal,high,time_sensitive'],
            'updateContext' => ['nullable', 'string', 'max:5000'],
            'updateExpectedOutcome' => ['nullable', 'string', 'max:1000'],
        ], [
            'updateTopic.required' => 'Vui lòng nhập chủ đề.',
            'updateTopic.max' => 'Chủ đề không được vượt quá :max ký tự.',
            'updateGoal.required' => 'Vui lòng nhập mục tiêu của bạn.',
            'updateGoal.max' => 'Mục tiêu không được vượt quá :max ký tự.',
            'updateQuestion.required' => 'Vui lòng nhập câu hỏi cụ thể.',
            'updateQuestion.max' => 'Câu hỏi không được vượt quá :max ký tự.',
        ]);

        try {
            $action->execute(Auth::user(), $this->mentorRequest, [
                'topic' => $this->updateTopic,
                'goal' => $this->updateGoal,
                'question' => $this->updateQuestion,
                'urgency' => $this->updateUrgency,
                'context' => $this->updateContext ?: null,
                'expected_outcome' => $this->updateExpectedOutcome ?: null,
            ]);
            $this->mentorRequest->refresh();
            $this->statusMessage = 'Yêu cầu cố vấn đã được cập nhật và gửi lại.';
        } catch (\Exception $e) {
            $this->errorMessage = $e->getMessage();
        }
    }

    public function submitFeedback(SubmitMentorFeedbackAction $action): void
    {
        if ($this->isSubmittingFeedback || $this->hasFeedback()) {
            return;
        }

        $this->isSubmittingFeedback = true;

        $this->validate([
            'feedbackLevel' => ['required', 'string', 'in:helpful,somewhat_helpful,not_helpful'],
            'feedbackText' => ['nullable', 'string', 'max:2000'],
        ]);

        try {
            $action->execute(Auth::user(), $this->mentorRequest, [
                'helpfulness_level' => $this->feedbackLevel,
                'feedback_text' => $this->feedbackText ?: null,
            ]);
            $this->mentorRequest->refresh();
            $this->feedbackLevel = '';
            $this->feedbackText = '';
            $this->statusMessage = 'Cảm ơn bạn đã gửi phản hồi!';
            $this->dispatch('close-feedback-form');
        } catch (\Exception $e) {
            $this->errorMessage = $e->getMessage();
        } finally {
            $this->isSubmittingFeedback = false;
        }
    }
}; ?>

<div class="mx-auto max-w-4xl px-4 py-6 sm:px-6 lg:px-8" x-data="{ activeAction: null }" x-on:open-feedback.window="activeAction = 'feedback'" x-on:close-feedback-form.window="activeAction = null">
    <a href="{{ route('mentor.requests.index') }}" class="text-sm font-semibold text-ue-brand hover:underline">← Danh sách yêu cầu</a>

    @if ($statusMessage)
        <div class="mt-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800 flex items-center gap-2">
            <x-ui.icon name="check-circle" size="sm" class="text-emerald-600" />
            {{ $statusMessage }}
        </div>
    @endif

    @if ($errorMessage)
        <div class="mt-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-800">
            {{ $errorMessage }}
        </div>
    @endif

    {{-- Sender/Recipient Info --}}
    <div class="mt-4 rounded-lg border border-slate-200 bg-slate-50 p-4">
        <div class="flex items-center gap-3">
            @if ($this->isMentorOwner() && $mentorRequest->student)
                <x-ui.avatar :user="$mentorRequest->student" size="md" />
                <div class="min-w-0 flex-1">
                    <p class="font-bold text-slate-900 truncate">{{ $mentorRequest->student->name }}</p>
                    <p class="text-xs text-slate-500 truncate">{{ $mentorRequest->student->email }}</p>
                </div>
            @elseif ($this->isStudent() && $mentorRequest->mentor)
                <x-ui.avatar :user="$mentorRequest->mentor" size="md" />
                <div class="min-w-0 flex-1">
                    <p class="font-bold text-slate-900 truncate">{{ $mentorRequest->mentor->name }}</p>
                    <p class="text-xs text-slate-500 truncate">{{ $mentorRequest->mentor->email }}</p>
                </div>
            @endif
            <span class="text-xs text-slate-400 shrink-0">{{ $mentorRequest->created_at->diffForHumans() }}</span>
        </div>
    </div>

    <article class="mt-4 rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
        {{-- Progress Pipeline Bar --}}
        @php
            $status = $mentorRequest->status;
            $isSubmitted = $status === MentorRequestStatus::Submitted;
            $isNeedMoreInfo = $status === MentorRequestStatus::NeedMoreInfo;
            $isUpdatedByStudent = $status === MentorRequestStatus::UpdatedByStudent;
            $isAccepted = $status === MentorRequestStatus::Accepted;
            $isCompleted = $status === MentorRequestStatus::Completed;
            $isDeclined = $status === MentorRequestStatus::Declined;
            $isCancelled = $status === MentorRequestStatus::Cancelled;
            $isTerminal = $isCompleted || $isDeclined || $isCancelled;

            $step2Active = $isSubmitted || $isNeedMoreInfo || $isUpdatedByStudent;
            $step3Active = $isAccepted || $isCompleted;
            $step3Terminal = $isDeclined || $isCancelled;

            $step2Label = $isNeedMoreInfo ? 'Cần bổ sung' : ($isUpdatedByStudent ? 'Đã cập nhật' : 'Đang duyệt');
            $step2Color = $isNeedMoreInfo ? 'amber' : ($isUpdatedByStudent ? 'blue' : 'ue-brand');
            $step2Class = match($step2Color) {
                'amber' => 'border-amber-500 bg-amber-50 text-amber-600',
                'blue' => 'border-ue-brand bg-ue-brand-soft text-ue-brand',
                default => 'border-ue-brand bg-ue-brand-soft text-ue-brand',
            };
        @endphp
        <div class="mb-8 border-b border-slate-100 pb-6">
            <div class="relative max-w-xl mx-auto my-2">
                <!-- Progress line track container -->
                <div class="absolute left-12 right-12 top-3.5 h-0.5 -translate-y-1/2 z-0" aria-hidden="true">
                    <!-- Progress line background -->
                    <div class="absolute inset-0 bg-slate-200"></div>
                    
                    <!-- Progress line active -->
                    @php
                        $progressWidth = '0%';
                        if ($step3Active || $step3Terminal) {
                            $progressWidth = '100%';
                        } elseif ($step2Active) {
                            $progressWidth = '50%';
                        }
                    @endphp
                    <div class="absolute left-0 top-0 bottom-0 bg-ue-brand transition-all duration-500" 
                         style="width: {{ $progressWidth }};"></div>
                </div>

                <!-- Steps -->
                <div class="relative flex justify-between z-10 text-xs font-semibold text-slate-500">
                    <!-- Step 1 -->
                    <div class="w-24 flex flex-col items-center">
                        <span class="flex h-7 w-7 items-center justify-center rounded-full bg-ue-brand text-white font-bold text-xs shadow-xs ring-4 ring-white z-10">✓</span>
                        <span class="text-ue-brand font-bold text-[10px] uppercase tracking-wide mt-2 text-center">Gửi yêu cầu</span>
                    </div>
                    
                    <!-- Step-2 -->
                    <div class="w-24 flex flex-col items-center">
                        @php
                            $step2Completed = $step3Active || ($step3Terminal && !$isDeclined && !$isCancelled);
                            $step2Bg = 'bg-slate-200 text-slate-400';
                            $step2Text = 'text-slate-400';
                            if ($step2Active || $step3Active || $step3Terminal) {
                                $step2Bg = $isNeedMoreInfo ? 'bg-amber-500 text-white shadow-xs' : 'bg-ue-brand text-white shadow-xs';
                                $step2Text = $isNeedMoreInfo ? 'text-amber-600 font-bold' : 'text-ue-brand font-bold';
                            }
                        @endphp
                        <span class="flex h-7 w-7 items-center justify-center rounded-full text-xs font-bold ring-4 ring-white z-10 {{ $step2Bg }}">
                            {{ $step2Completed ? '✓' : '2' }}
                        </span>
                        <span class="text-[10px] uppercase tracking-wide mt-2 text-center {{ $step2Text }}">{{ $step2Label }}</span>
                    </div>
                    
                    <!-- Step 3 -->
                    <div class="w-24 flex flex-col items-center">
                        @php
                            $step3Bg = 'bg-slate-200 text-slate-400';
                            $step3Text = 'text-slate-400';
                            if ($step3Active || $step3Terminal) {
                                $step3Bg = $isCompleted ? 'bg-green-600 text-white shadow-xs' : ($isAccepted ? 'bg-emerald-600 text-white shadow-xs' : ($isDeclined ? 'bg-red-500 text-white shadow-xs' : 'bg-slate-400 text-white shadow-xs'));
                                $step3Text = $isCompleted ? 'text-green-700 font-bold' : ($isAccepted ? 'text-emerald-600 font-bold' : ($isDeclined ? 'text-red-600 font-bold' : 'text-slate-600 font-bold'));
                            }
                        @endphp
                        <span class="flex h-7 w-7 items-center justify-center rounded-full text-xs font-bold ring-4 ring-white z-10 {{ $step3Bg }}">
                            {{ $step3Active || $step3Terminal ? ($isCompleted ? '✓' : ($isAccepted ? '✓' : ($isDeclined ? '✕' : '–'))) : '3' }}
                        </span>
                        <span class="text-[10px] uppercase tracking-wide mt-2 text-center {{ $step3Text }}">
                            {{ $step3Active || $step3Terminal ? ($isCompleted ? 'Hoàn thành' : ($isAccepted ? 'Đã chấp nhận' : ($isDeclined ? 'Đã từ chối' : 'Đã hủy'))) : 'Kết quả' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-slate-900 break-words">{{ $mentorRequest->topic }}</h1>
                <div class="mt-2 flex flex-wrap items-center gap-2 text-sm">
                    @php
                        $badgeColor = $mentorRequest->status->color();
                        $badgeClasses = match($badgeColor) {
                            'blue' => 'bg-blue-100 text-blue-800',
                            'emerald' => 'bg-emerald-100 text-emerald-800',
                            'red' => 'bg-red-100 text-red-800',
                            'amber' => 'bg-amber-100 text-amber-800',
                            'green' => 'bg-green-100 text-green-800',
                            'orange' => 'bg-orange-100 text-orange-800',
                            'slate' => 'bg-slate-100 text-slate-800',
                            default => 'bg-slate-100 text-slate-800',
                        };
                    @endphp
                    <span class="rounded-full px-2.5 py-0.5 text-xs font-bold {{ $badgeClasses }}">
                        {{ $mentorRequest->status->label() }}
                    </span>

                </div>
            </div>
            @if ($mentorRequest->conversation_id)
                <a href="{{ route('messages.index', ['conversation' => $mentorRequest->conversation_id]) }}" class="inline-flex items-center justify-center rounded-lg bg-ue-brand px-4 py-2 text-sm font-bold text-white shadow-sm hover:bg-ue-brand-dark transition shrink-0">
                    <x-ui.icon name="send" size="sm" class="mr-1.5" />
                    Mở trò chuyện
                </a>
            @endif
        </div>

        @if ($isDeclined && $mentorRequest->decline_reason)
            <div class="mt-6 rounded-xl border border-dashed border-red-200 bg-red-50 p-4 text-sm text-red-900">
                <p class="font-bold text-red-950">Lý do từ chối cố vấn:</p>
                <p class="mt-1 leading-relaxed text-red-800 break-words">{{ $mentorRequest->decline_reason }}</p>
                <p class="mt-3 text-xs text-red-600 font-medium">Bạn có thể điều chỉnh lại nguyện vọng và hồ sơ hoặc gửi yêu cầu đến các mentor khác phù hợp hơn.</p>
            </div>
        @endif

        @unless ($this->canUpdate())
            <dl class="mt-6 space-y-4 text-sm">
                <div>
                    <dt class="font-bold text-slate-900">Mục tiêu của bạn</dt>
                    <dd class="mt-1 text-slate-600 leading-relaxed break-words">{{ $mentorRequest->goal }}</dd>
                </div>
                <div>
                    <dt class="font-bold text-slate-900">Câu hỏi cụ thể muốn mentor hỗ trợ</dt>
                    <dd class="mt-1 whitespace-pre-line text-slate-600 leading-relaxed break-words">{{ $mentorRequest->question }}</dd>
                </div>
                @if ($mentorRequest->context)
                    <div>
                        <dt class="font-bold text-slate-900">Bối cảnh bổ sung</dt>
                        <dd class="mt-1 whitespace-pre-line text-slate-600 leading-relaxed break-words">{{ $mentorRequest->context }}</dd>
                    </div>
                @endif
                @if ($mentorRequest->expected_outcome)
                    <div>
                        <dt class="font-bold text-slate-900">Kết quả mong đợi</dt>
                        <dd class="mt-1 text-slate-600 leading-relaxed break-words">{{ $mentorRequest->expected_outcome }}</dd>
                    </div>
                @endif

                {{-- Mentor response (when accepted) --}}
                @if ($mentorRequest->mentor_response)
                    <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4">
                        <dt class="font-bold text-emerald-950">Phản hồi từ mentor:</dt>
                        <dd class="mt-1 text-emerald-900 leading-relaxed break-words">{{ $mentorRequest->mentor_response }}</dd>
                    </div>
                @endif
            </dl>
        @endunless

        {{-- Mentor asked for more info (always visible for student) --}}
        @if ($mentorRequest->more_info_question)
            <div class="mt-6 rounded-xl border border-amber-200 bg-amber-50 p-4">
                <p class="font-bold text-amber-950 flex items-center gap-1.5">
                    Mentor yêu cầu thêm thông tin:
                </p>
                <p class="mt-1 text-amber-900 leading-relaxed break-words">{{ $mentorRequest->more_info_question }}</p>
                @if ($isUpdatedByStudent)
                    <div class="mt-3 flex items-center gap-1.5 text-xs font-bold text-emerald-700">
                        <x-ui.icon name="check-circle" size="xs" class="text-emerald-600" />
                        Sinh viên đã cập nhật thông tin
                    </div>
                @endif
            </div>
        @endif

        {{-- Actions --}}
        <div class="mt-6 pt-4 border-t border-slate-100 space-y-4">
            {{-- Mentor Actions --}}
            @if ($this->canAcceptOrDecline())
                <div x-show="activeAction === null" class="flex flex-wrap gap-2">
                    <button @click="activeAction = 'accept'"
                        class="rounded-lg bg-ue-brand px-4 py-2 text-sm font-bold text-white shadow-sm hover:bg-ue-brand-dark transition flex items-center gap-1.5">
                        <x-ui.icon name="check" size="xs" /> Chấp nhận
                    </button>
                    <button @click="activeAction = 'decline'"
                        class="rounded-lg bg-red-600 px-4 py-2 text-sm font-bold text-white shadow-sm hover:bg-red-700 transition flex items-center gap-1.5">
                        <x-ui.icon name="x" size="xs" /> Từ chối
                    </button>
                    @if ($this->canAskMoreInfo())
                        <button @click="activeAction = 'ask_more_info'"
                            class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-bold text-slate-700 hover:bg-slate-50 transition flex items-center gap-1.5">
                            <x-ui.icon name="help-circle" size="xs" /> Cần thêm thông tin
                        </button>
                    @endif
                </div>

                {{-- Accept Form --}}
                <form x-show="activeAction === 'accept'" method="POST" action="{{ route('mentor.requests.accept', $mentorRequest) }}" class="space-y-3 p-4 rounded-xl border border-blue-100 bg-blue-50/50">
                    @csrf
                    <h3 class="text-sm font-bold text-blue-950">Chấp nhận yêu cầu cố vấn</h3>
                    <label class="block">
                        <span class="text-xs font-bold text-blue-900">Phản hồi của bạn (không bắt buộc)</span>
                        <textarea name="mentor_response" rows="3" placeholder="Nhập lời chào hoặc phản hồi ban đầu của bạn gửi đến sinh viên..." class="mt-1 w-full rounded-xl border-blue-200 bg-white text-sm focus:border-ue-brand focus:ring-ue-brand/20"></textarea>
                    </label>
                    <div class="flex gap-2">
                        <button type="submit" class="rounded-lg bg-ue-brand px-4 py-2 text-sm font-bold text-white hover:bg-ue-brand-dark">Xác nhận chấp nhận</button>
                        <button type="button" @click="activeAction = null" class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-50">Hủy</button>
                    </div>
                </form>

                {{-- Decline Form --}}
                <form x-show="activeAction === 'decline'" method="POST" action="{{ route('mentor.requests.decline', $mentorRequest) }}" class="space-y-3 p-4 rounded-xl border border-red-100 bg-red-50/50">
                    @csrf
                    <h3 class="text-sm font-bold text-red-950">Từ chối yêu cầu cố vấn</h3>
                    <label class="block">
                        <span class="text-xs font-bold text-red-900">Lý do từ chối (không bắt buộc)</span>
                        <textarea name="decline_reason" rows="3" placeholder="Chia sẻ lý do giúp sinh viên hiểu rõ hơn..." class="mt-1 w-full rounded-xl border-red-200 bg-white text-sm focus:border-red-500 focus:ring-red-500/20"></textarea>
                    </label>
                    <div class="flex gap-2">
                        <button type="submit" class="rounded-lg bg-red-600 px-4 py-2 text-sm font-bold text-white hover:bg-red-700">Xác nhận từ chối</button>
                        <button type="button" @click="activeAction = null" class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-50">Hủy</button>
                    </div>
                </form>

                {{-- Ask More Info Form --}}
                @if ($this->canAskMoreInfo())
                    <form x-show="activeAction === 'ask_more_info'" method="POST" action="{{ route('mentor.requests.ask-more-info', $mentorRequest) }}" class="space-y-3 p-4 rounded-xl border border-slate-200 bg-slate-50">
                        @csrf
                        <h3 class="text-sm font-bold text-slate-800">Yêu cầu bổ sung thông tin</h3>
                        <label class="block">
                            <span class="text-xs font-bold text-slate-700">Nội dung cần làm rõ <span class="text-red-500">*</span></span>
                            <textarea name="more_info_question" required rows="3" placeholder="Ví dụ: Em hãy chia sẻ thêm về các dự án cũ đã làm, hoặc mục tiêu cụ thể sau kỳ thực tập..." class="mt-1 w-full rounded-xl border-slate-200 bg-white text-sm focus:border-ue-brand focus:ring-ue-brand/20"></textarea>
                        </label>
                        <div class="flex gap-2">
                            <button type="submit" class="rounded-lg bg-ue-brand px-4 py-2 text-sm font-bold text-white hover:bg-ue-brand-dark">Gửi yêu cầu thông tin</button>
                            <button type="button" @click="activeAction = null" class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-50">Hủy</button>
                        </div>
                    </form>
                @endif
            @endif

            {{-- Student Actions --}}
            <div x-show="activeAction === null" class="flex flex-wrap gap-2">
                @if ($this->canCancel() && ! $this->canUpdate())
                    <button wire:click="cancelRequest"
                        class="rounded-lg border border-red-200 bg-white px-4 py-2 text-sm font-bold text-red-600 hover:bg-red-50/50 transition flex items-center gap-1.5">
                        <x-ui.icon name="x-circle" size="xs" class="text-red-500" /> Hủy yêu cầu
                    </button>
                @endif

                @if ($this->canComplete())
                    <button wire:click="completeRequest"
                        class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-bold text-white shadow-sm hover:bg-emerald-700 transition flex items-center gap-1.5">
                        <x-ui.icon name="check-circle" size="xs" /> Đánh dấu hoàn thành
                    </button>
                @endif

                @if ($this->canGiveFeedback())
                    <button @click="activeAction = 'feedback'"
                        class="rounded-lg bg-ue-brand px-4 py-2 text-sm font-bold text-white shadow-sm hover:bg-ue-brand-dark transition flex items-center gap-1.5">
                        <x-ui.icon name="star" size="xs" /> Gửi phản hồi
                    </button>
                @endif

                @if ($this->hasFeedback())
                    <span class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-semibold text-emerald-700 bg-emerald-50 rounded-lg border border-emerald-200">
                        <x-ui.icon name="check-circle" size="xs" class="text-emerald-600" />
                        Đã gửi phản hồi
                    </span>
                @endif
            </div>

            {{-- Student Update Form --}}
            @if ($this->canUpdate())
                <div class="mt-6 space-y-5 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h3 class="text-sm font-bold text-slate-900 border-b border-slate-100 pb-3">Cập nhật yêu cầu cố vấn</h3>

                    <div class="space-y-4">
                        <label class="block">
                            <span class="text-xs font-bold text-slate-700">Chủ đề <span class="text-red-500">*</span></span>
                            <input type="text" wire:model.live="updateTopic" maxlength="255" class="mt-1 w-full rounded-lg border-slate-200 text-sm focus:border-ue-brand focus:ring-ue-brand/20">
                            @error('updateTopic') <p class="mt-1 text-xs text-red-600 font-semibold">{{ $message }}</p> @enderror
                        </label>

                        <label class="block">
                            <span class="text-xs font-bold text-slate-700">Mục tiêu của bạn <span class="text-red-500">*</span></span>
                            <textarea wire:model.live="updateGoal" rows="2" maxlength="5000" class="mt-1 w-full rounded-lg border-slate-200 text-sm focus:border-ue-brand focus:ring-ue-brand/20"></textarea>
                            @error('updateGoal') <p class="mt-1 text-xs text-red-600 font-semibold">{{ $message }}</p> @enderror
                        </label>

                        <label class="block">
                            <span class="text-xs font-bold text-slate-700">Câu hỏi cụ thể muốn mentor hỗ trợ <span class="text-red-500">*</span></span>
                            <textarea wire:model.live="updateQuestion" rows="3" maxlength="5000" class="mt-1 w-full rounded-lg border-slate-200 text-sm focus:border-ue-brand focus:ring-ue-brand/20"></textarea>
                            @error('updateQuestion') <p class="mt-1 text-xs text-red-600 font-semibold">{{ $message }}</p> @enderror
                        </label>
                    </div>

                    @error('updateRequest')
                        <div class="rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700">{{ $message }}</div>
                    @enderror

                    <div class="flex items-center justify-between gap-3 pt-4 border-t border-slate-100">
                        <div>
                            @if ($this->canCancel())
                                <button type="button" wire:click="cancelRequest"
                                    class="rounded-lg border border-red-200 bg-white px-4 py-2 text-sm font-bold text-red-600 hover:bg-red-50/50 transition flex items-center gap-1.5">
                                    <x-ui.icon name="x-circle" size="xs" class="text-red-500" /> Hủy yêu cầu
                                </button>
                            @endif
                        </div>
                        <button wire:click="updateRequest" wire:loading.attr="disabled"
                            class="rounded-lg bg-ue-brand px-5 py-2 text-sm font-bold text-white hover:bg-ue-brand-dark disabled:opacity-60">
                            <span wire:loading.remove wire:target="updateRequest">Lưu và Gửi lại</span>
                            <span wire:loading wire:target="updateRequest">Đang gửi...</span>
                        </button>
                    </div>
                </div>
            @endif

            {{-- Feedback Form --}}
            <div x-show="activeAction === 'feedback'" x-transition class="space-y-4 p-4 rounded-xl border border-slate-200 bg-white">
                <div class="flex items-center gap-2">
                    <x-ui.icon name="star" size="sm" class="text-amber-500" />
                    <h3 class="text-sm font-bold text-slate-900">Đánh giá buổi cố vấn</h3>
                </div>
                <p class="text-xs text-slate-600">Phản hồi của bạn sẽ được gửi ẩn danh đến mentor để giúp họ cải thiện chất lượng hỗ trợ.</p>

                <div>
                    <span class="text-xs font-bold text-slate-700">Mức độ hữu ích <span class="text-red-500">*</span></span>
                    <div class="mt-2 flex gap-2">
                        <label class="flex-1 cursor-pointer">
                            <input type="radio" wire:model.live="feedbackLevel" value="helpful" class="sr-only peer" />
                            <div class="flex flex-col items-center gap-1 p-3 rounded-xl border-2 text-sm font-bold transition
                                {{ $feedbackLevel === 'helpful' ? 'border-emerald-500 bg-emerald-50 text-emerald-700' : 'border-slate-200 text-slate-500 hover:border-slate-300' }}">
                                Hữu ích
                            </div>
                        </label>
                        <label class="flex-1 cursor-pointer">
                            <input type="radio" wire:model.live="feedbackLevel" value="somewhat_helpful" class="sr-only peer" />
                            <div class="flex flex-col items-center gap-1 p-3 rounded-xl border-2 text-sm font-bold transition
                                {{ $feedbackLevel === 'somewhat_helpful' ? 'border-amber-500 bg-amber-50 text-amber-700' : 'border-slate-200 text-slate-500 hover:border-slate-300' }}">
                                Tạm được
                            </div>
                        </label>
                        <label class="flex-1 cursor-pointer">
                            <input type="radio" wire:model.live="feedbackLevel" value="not_helpful" class="sr-only peer" />
                            <div class="flex flex-col items-center gap-1 p-3 rounded-xl border-2 text-sm font-bold transition
                                {{ $feedbackLevel === 'not_helpful' ? 'border-red-500 bg-red-50 text-red-700' : 'border-slate-200 text-slate-500 hover:border-slate-300' }}">
                                Chưa tốt
                            </div>
                        </label>
                    </div>
                    @error('feedbackLevel') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <label class="block">
                    <span class="text-xs font-bold text-slate-700">Chia sẻ thêm (không bắt buộc)</span>
                    <textarea wire:model.live="feedbackText" rows="3" placeholder="Điều gì đã tốt? Điều gì có thể cải thiện?"
                        class="mt-1 w-full rounded-xl border-slate-200 text-sm focus:border-ue-brand focus:ring-ue-brand/20"></textarea>
                </label>

                <div class="flex gap-2">
                    <button wire:click="submitFeedback" wire:loading.attr="disabled" wire:target="submitFeedback"
                        @disabled($isSubmittingFeedback)
                        class="rounded-lg bg-ue-brand px-4 py-2 text-sm font-bold text-white hover:bg-ue-brand-dark disabled:opacity-60">
                        <span wire:loading.remove wire:target="submitFeedback">Gửi phản hồi</span>
                        <span wire:loading wire:target="submitFeedback">Đang gửi...</span>
                    </button>
                    <button type="button" @click="activeAction = null" class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-50">Bỏ qua</button>
                </div>
            </div>
        </div>

        {{-- Mentor Feedback Indicator (ẩn danh — nội dung chi tiết xem tại Dashboard) --}}
        @if ($this->isMentorOwner() && $isCompleted && $this->hasFeedback())
            <div class="mt-6 p-4 rounded-xl border border-emerald-100 bg-emerald-50 flex items-center gap-2">
                <x-ui.icon name="check-circle" size="sm" class="text-emerald-500 flex-shrink-0" />
                <div>
                    <p class="text-sm font-bold text-emerald-800">Sinh viên đã gửi phản hồi</p>
                    <p class="text-xs text-emerald-600 mt-0.5">Phản hồi được ẩn danh để bảo vệ thông tin sinh viên. Xem tổng hợp tại <a href="{{ route('mentor.dashboard') }}" class="underline font-semibold">Mentor Dashboard</a>.</p>
                </div>
            </div>
        @endif
    </article>
</div>
