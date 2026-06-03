<?php

use App\Models\MentorRequest;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;

new class extends Component {
    public MentorRequest $mentorRequest;
};
?>

<div class="mx-auto max-w-4xl px-4 py-6 sm:px-6 lg:px-8">
    <a href="{{ route('mentor.requests.index') }}" class="text-sm font-semibold text-ue-brand hover:underline">← Danh sách yêu cầu</a>

    @if (session('status'))
        <div class="mt-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">
            {{ session('status') }}
        </div>
    @endif

    @error('mentor_request')
        <div class="mt-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-800">
            {{ $message }}
        </div>
    @enderror

    <article class="mt-4 rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
        <!-- Progress Pipeline Bar -->
        @php
            $status = $mentorRequest->status;
            $step1 = true;
            $step2 = in_array($status, [\App\Enums\MentorRequestStatus::Submitted, \App\Enums\MentorRequestStatus::NeedMoreInfo]);
            $step3 = in_array($status, [\App\Enums\MentorRequestStatus::Accepted, \App\Enums\MentorRequestStatus::Completed, \App\Enums\MentorRequestStatus::Declined, \App\Enums\MentorRequestStatus::Cancelled, \App\Enums\MentorRequestStatus::Closed, \App\Enums\MentorRequestStatus::Reported]);
            
            $step3Color = 'text-slate-400 border-slate-200';
            if ($step3) {
                if (in_array($status, [\App\Enums\MentorRequestStatus::Accepted, \App\Enums\MentorRequestStatus::Completed])) {
                    $step3Color = 'text-emerald-600 border-emerald-600 bg-emerald-50';
                } elseif (in_array($status, [\App\Enums\MentorRequestStatus::Declined, \App\Enums\MentorRequestStatus::Reported])) {
                    $step3Color = 'text-red-600 border-red-600 bg-red-50';
                } else {
                    $step3Color = 'text-slate-600 border-slate-600 bg-slate-50';
                }
            }
        @endphp
        <div class="mb-8 border-b border-slate-100 pb-6">
            <div class="flex items-center justify-between text-xs font-semibold text-slate-500">
                <div class="flex flex-col items-center gap-1.5">
                    <span class="flex h-6 w-6 items-center justify-center rounded-full border-2 border-ue-brand bg-ue-brand-soft text-ue-brand">1</span>
                    <span>Gửi đơn</span>
                </div>
                <div class="h-0.5 flex-1 bg-slate-200 mx-4 {{ $step2 || $step3 ? 'bg-ue-brand' : '' }}"></div>
                <div class="flex flex-col items-center gap-1.5">
                    <span class="flex h-6 w-6 items-center justify-center rounded-full border-2 {{ $step2 ? 'border-ue-brand bg-ue-brand-soft text-ue-brand' : ($step3 ? 'border-slate-300 bg-slate-100 text-slate-500' : 'border-slate-200 text-slate-300') }}">2</span>
                    <span>Đang duyệt</span>
                </div>
                <div class="h-0.5 flex-1 bg-slate-200 mx-4 {{ $step3 ? 'bg-ue-brand' : '' }}"></div>
                <div class="flex flex-col items-center gap-1.5">
                    <span class="flex h-6 w-6 items-center justify-center rounded-full border-2 {{ $step3Color }}">3</span>
                    <span>Kết quả</span>
                </div>
            </div>
        </div>

        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-slate-900 break-words">{{ $mentorRequest->topic }}</h1>
                <div class="mt-2 flex flex-wrap items-center gap-2 text-sm">
                    <span class="rounded-full bg-{{ $mentorRequest->status->color() }}-100 px-2.5 py-0.5 text-xs font-bold text-{{ $mentorRequest->status->color() }}-800">
                        {{ $mentorRequest->status->label() }}
                    </span>
                    <span class="text-slate-300">•</span>
                    <span class="font-semibold text-slate-600">Độ khẩn cấp: {{ $mentorRequest->urgency->label() }}</span>
                </div>
            </div>
            @if ($mentorRequest->conversation_id)
                <a href="{{ route('messages.index', ['conversation' => $mentorRequest->conversation_id]) }}" class="inline-flex items-center justify-center rounded-lg bg-ue-brand px-4 py-2 text-sm font-bold text-white shadow-sm hover:bg-ue-brand-dark transition">
                    Mở trò chuyện
                </a>
            @endif
        </div>

        @if ($mentorRequest->status === \App\Enums\MentorRequestStatus::Declined && $mentorRequest->decline_reason)
            <div class="mt-6 rounded-xl border border-dashed border-red-200 bg-red-50 p-4 text-sm text-red-900">
                <p class="font-bold text-red-950">Lý do từ chối cố vấn:</p>
                <p class="mt-1 leading-relaxed text-red-800 break-words">{{ $mentorRequest->decline_reason }}</p>
                <p class="mt-3 text-xs text-red-600 font-medium">Bạn có thể điều chỉnh lại nguyện vọng và hồ sơ hoặc gửi yêu cầu đến các mentor khác phù hợp hơn.</p>
            </div>
        @endif

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
            @if ($mentorRequest->more_info_question)
                <div class="rounded-xl border border-amber-200 bg-amber-50 p-4">
                    <dt class="font-bold text-amber-950 flex items-center gap-1.5">
                        Mentor yêu cầu thêm thông tin:
                    </dt>
                    <dd class="mt-1 text-amber-900 leading-relaxed break-words">{{ $mentorRequest->more_info_question }}</dd>
                </div>
            @endif
            @if ($mentorRequest->mentor_response)
                <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4">
                    <dt class="font-bold text-emerald-950">Phản hồi từ mentor:</dt>
                    <dd class="mt-1 text-emerald-900 leading-relaxed break-words">{{ $mentorRequest->mentor_response }}</dd>
                </div>
            @endif
        </dl>

        <div x-data="{ activeAction: null }" class="mt-8 pt-6 border-t border-slate-100">
            <!-- Mentor Actions -->
            @if ($mentorRequest->mentor_id === Auth::id() && in_array($mentorRequest->status, [\App\Enums\MentorRequestStatus::Submitted, \App\Enums\MentorRequestStatus::NeedMoreInfo], true))
                <div class="space-y-4">
                    <!-- Action buttons -->
                    <div x-show="activeAction === null" class="flex flex-wrap gap-2">
                        <button @click="activeAction = 'accept'" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-bold text-white shadow-sm hover:bg-emerald-700 transition">
                            Chấp nhận
                        </button>
                        <button @click="activeAction = 'decline'" class="rounded-lg bg-red-600 px-4 py-2 text-sm font-bold text-white shadow-sm hover:bg-red-700 transition">
                            Từ chối
                        </button>
                        @if ($mentorRequest->status === \App\Enums\MentorRequestStatus::Submitted)
                            <button @click="activeAction = 'ask_more_info'" class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-bold text-slate-700 hover:bg-slate-50 transition">
                                Cần thêm thông tin
                            </button>
                        @endif
                    </div>

                    <!-- Accept Form -->
                    <form x-show="activeAction === 'accept'" method="POST" action="{{ route('mentor.requests.accept', $mentorRequest) }}" class="space-y-3 p-4 rounded-xl border border-emerald-100 bg-emerald-50/50">
                        @csrf
                        <label class="block">
                            <span class="text-sm font-bold text-emerald-950">Phản hồi của bạn (không bắt buộc)</span>
                            <textarea name="mentor_response" rows="3" placeholder="Nhập lời chào hoặc phản hồi ban đầu của bạn gửi đến sinh viên..." class="mt-2 w-full rounded-xl border-emerald-200 bg-white text-sm focus:border-emerald-500 focus:ring-emerald-500/20"></textarea>
                        </label>
                        <div class="flex gap-2">
                            <button type="submit" class="rounded-lg bg-emerald-600 px-3 py-2 text-xs font-bold text-white hover:bg-emerald-700">Xác nhận chấp nhận</button>
                            <button type="button" @click="activeAction = null" class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-50">Hủy</button>
                        </div>
                    </form>

                    <!-- Decline Form -->
                    <form x-show="activeAction === 'decline'" method="POST" action="{{ route('mentor.requests.decline', $mentorRequest) }}" class="space-y-3 p-4 rounded-xl border border-red-100 bg-red-50/50">
                        @csrf
                        <label class="block">
                            <span class="text-sm font-bold text-red-950">Lý do từ chối (không bắt buộc)</span>
                            <textarea name="decline_reason" rows="3" placeholder="Nhập lý do từ chối (giúp sinh viên cải thiện nguyện vọng tốt hơn)..." class="mt-2 w-full rounded-xl border-red-200 bg-white text-sm focus:border-red-500 focus:ring-red-500/20"></textarea>
                        </label>
                        <div class="flex gap-2">
                            <button type="submit" class="rounded-lg bg-red-600 px-3 py-2 text-xs font-bold text-white hover:bg-red-700">Xác nhận từ chối</button>
                            <button type="button" @click="activeAction = null" class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-50">Hủy</button>
                        </div>
                    </form>

                    <!-- Ask More Info Form -->
                    <form x-show="activeAction === 'ask_more_info'" method="POST" action="{{ route('mentor.requests.ask-more-info', $mentorRequest) }}" class="space-y-3 p-4 rounded-xl border border-slate-200 bg-slate-50">
                        @csrf
                        <label class="block">
                            <span class="text-sm font-bold text-slate-800">Nội dung bạn cần sinh viên làm rõ (bắt buộc)</span>
                            <textarea name="more_info_question" required rows="3" placeholder="Ví dụ: Em hãy chia sẻ thêm về các dự án cũ đã làm, hoặc mục tiêu cụ thể sau kỳ thực tập..." class="mt-2 w-full rounded-xl border-slate-200 bg-white text-sm focus:border-ue-brand focus:ring-ue-brand/20"></textarea>
                        </label>
                        <div class="flex gap-2">
                            <button type="submit" class="rounded-lg bg-ue-brand px-3 py-2 text-xs font-bold text-white hover:bg-ue-brand-dark">Gửi yêu cầu thông tin</button>
                            <button type="button" @click="activeAction = null" class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-50">Hủy</button>
                        </div>
                    </form>
                </div>
            @endif

            <!-- Student Actions -->
            <div x-show="activeAction === null" class="flex flex-wrap gap-2">
                @if ($mentorRequest->student_id === Auth::id())
                    @if ($mentorRequest->status === \App\Enums\MentorRequestStatus::NeedMoreInfo)
                        <button @click="activeAction = 'update'" class="rounded-lg bg-ue-brand px-4 py-2 text-sm font-bold text-white shadow-sm hover:bg-ue-brand-dark transition">
                            Cập nhật thông tin yêu cầu
                        </button>
                    @endif
                    @if (in_array($mentorRequest->status, [\App\Enums\MentorRequestStatus::Submitted, \App\Enums\MentorRequestStatus::NeedMoreInfo], true))
                        <form method="POST" action="{{ route('mentor.requests.cancel', $mentorRequest) }}">
                            @csrf
                            <button type="submit" onclick="return confirm('Bạn có chắc chắn muốn hủy yêu cầu này?')" class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-bold text-slate-700 hover:bg-slate-50 transition">
                                Hủy yêu cầu
                            </button>
                        </form>
                    @endif
                @endif

                @if ($mentorRequest->isParticipant(Auth::user()) && $mentorRequest->status === \App\Enums\MentorRequestStatus::Accepted)
                    <form method="POST" action="{{ route('mentor.requests.complete', $mentorRequest) }}">
                        @csrf
                        <button type="submit" class="rounded-lg bg-ue-brand px-4 py-2 text-sm font-bold text-white shadow-sm hover:bg-ue-brand-dark transition">
                            Đánh dấu hoàn thành
                        </button>
                    </form>
                @endif
            </div>

            <!-- Student Update Form -->
            @if ($mentorRequest->student_id === Auth::id() && $mentorRequest->status === \App\Enums\MentorRequestStatus::NeedMoreInfo)
                <form x-show="activeAction === 'update'" method="POST" action="{{ route('mentor.requests.update', $mentorRequest) }}" class="mt-4 space-y-4 p-4 rounded-xl border border-slate-200 bg-slate-50">
                    @csrf
                    @method('PATCH')
                    <h3 class="text-sm font-bold text-slate-900">Cập nhật yêu cầu cố vấn của bạn</h3>
                    
                    <label class="block">
                        <span class="text-xs font-bold text-slate-700">Chủ đề</span>
                        <input name="topic" required value="{{ old('topic', $mentorRequest->topic) }}" class="mt-1 w-full rounded-lg border-slate-200 text-sm">
                    </label>

                    <label class="block">
                        <span class="text-xs font-bold text-slate-700">Mục tiêu của bạn</span>
                        <textarea name="goal" required rows="2" class="mt-1 w-full rounded-lg border-slate-200 text-sm">{{ old('goal', $mentorRequest->goal) }}</textarea>
                    </label>

                    <label class="block">
                        <span class="text-xs font-bold text-slate-700">Câu hỏi cụ thể muốn mentor hỗ trợ</span>
                        <textarea name="question" required rows="3" class="mt-1 w-full rounded-lg border-slate-200 text-sm">{{ old('question', $mentorRequest->question) }}</textarea>
                    </label>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <label class="block">
                            <span class="text-xs font-bold text-slate-700">Độ khẩn cấp</span>
                            <select name="urgency" class="mt-1 w-full rounded-lg border-slate-200 text-sm">
                                <option value="normal" @selected($mentorRequest->urgency->value === 'normal')>Bình thường</option>
                                <option value="low" @selected($mentorRequest->urgency->value === 'low')>Không gấp</option>
                                <option value="high" @selected($mentorRequest->urgency->value === 'high')>Gấp</option>
                            </select>
                        </label>

                        <label class="block">
                            <span class="text-xs font-bold text-slate-700">Kết quả mong đợi (không bắt buộc)</span>
                            <input name="expected_outcome" value="{{ old('expected_outcome', $mentorRequest->expected_outcome) }}" class="mt-1 w-full rounded-lg border-slate-200 text-sm">
                        </label>
                    </div>

                    <label class="block">
                        <span class="text-xs font-bold text-slate-700">Bối cảnh bổ sung (không bắt buộc)</span>
                        <textarea name="context" rows="2" class="mt-1 w-full rounded-lg border-slate-200 text-sm">{{ old('context', $mentorRequest->context) }}</textarea>
                    </label>

                    <div class="flex gap-2">
                        <button type="submit" class="rounded-lg bg-ue-brand px-3 py-2 text-xs font-bold text-white hover:bg-ue-brand-dark">Lưu và Gửi lại</button>
                        <button type="button" @click="activeAction = null" class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-50">Hủy</button>
                    </div>
                </form>
            @endif
        </div>
    </article>
</div>
