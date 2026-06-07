<?php

use App\Actions\Mentor\CreateMentorRequestAction;
use App\Actions\Reports\CreateReport;
use App\Enums\MentorAvailabilityStatus;
use App\Models\MentorProfile;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;

new class extends Component
{
    public MentorProfile $mentorProfile;

    public string $topic = '';

    public string $goal = '';

    public string $question = '';

    public string $urgency = 'normal';

    public string $context = '';

    public string $expected_outcome = '';

    public bool $showReportModal = false;

    public string $reportReason = '';

    public string $reportDescription = '';

    public function getIsAvailableProperty(): bool
    {
        return $this->mentorProfile->availability_status === MentorAvailabilityStatus::Available
            && $this->mentorProfile->is_active
            && $this->mentorProfile->mentor_visibility;
    }

    public function submitRequest(CreateMentorRequestAction $action): void
    {
        $this->validate([
            'topic' => ['required', 'string', 'max:255'],
            'goal' => ['required', 'string', 'max:5000'],
            'question' => ['required', 'string', 'max:5000'],
            'urgency' => ['required', 'string', 'in:low,normal,high,time_sensitive'],
            'context' => ['nullable', 'string', 'max:5000'],
            'expected_outcome' => ['nullable', 'string', 'max:1000'],
        ]);

        try {
            $mentorRequest = $action->execute(Auth::user(), $this->mentorProfile, [
                'topic' => $this->topic,
                'goal' => $this->goal,
                'question' => $this->question,
                'urgency' => $this->urgency,
                'context' => $this->context ?: null,
                'expected_outcome' => $this->expected_outcome ?: null,
            ]);

            $this->reset('topic', 'goal', 'question', 'urgency', 'context', 'expected_outcome');

            $this->dispatch('mentor-request-sent', mentorRequestId: $mentorRequest->id);
        } catch (Exception $e) {
            $this->addError('submit', $e->getMessage());
        }
    }

    public function submitReport(): void
    {
        $this->validate([
            'reportReason' => ['required', 'string', 'in:spam,harassment,inappropriate_content,misinformation,privacy_violation,other'],
            'reportDescription' => ['nullable', 'string', 'max:2000'],
        ]);

        try {
            app(CreateReport::class)->execute(Auth::user(), $this->mentorProfile, [
                'reason' => $this->reportReason,
                'description' => $this->reportDescription,
            ]);

            $this->reset('reportReason', 'reportDescription', 'showReportModal');
            $this->dispatch('report-submitted');
        } catch (\Exception $e) {
            $this->addError('reportReason', $e->getMessage());
        }
    }
};
?>

<div class="mx-auto max-w-4xl px-4 py-6 sm:px-6 lg:px-8">
    <div class="flex items-center justify-between mb-2">
        <a href="{{ route('mentor.discovery') }}" class="text-sm font-semibold text-ue-brand hover:underline">← Quay lại danh sách mentor</a>
        <button
            wire:click="$set('showReportModal', true)"
            class="text-xs font-semibold text-red-400 hover:text-red-600 transition flex items-center gap-1"
            title="Báo cáo mentor"
        >
            <svg class="h-3.5 w-3.5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"/><line x1="4" y1="22" x2="4" y2="15"/></svg>
            Báo cáo
        </button>
    </div>

    <section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div class="flex items-start gap-4">
                <a href="{{ route('profile.show', $mentorProfile->user) }}" class="block rounded-full focus:outline-none focus:ring-2 focus:ring-ue-brand/30 flex-shrink-0" aria-label="Xem trang cá nhân của {{ $mentorProfile->user->name }}">
                    <x-ui.avatar :user="$mentorProfile->user" size="lg" />
                </a>
                <div>
                    <a href="{{ route('profile.show', $mentorProfile->user) }}" class="text-2xl font-bold text-slate-900 hover:text-ue-brand hover:underline">{{ $mentorProfile->user->name }}</a>
                    <p class="mt-3 text-base font-semibold text-slate-800 break-words">{{ $mentorProfile->headline ?: 'Mentor UEConnect' }}</p>
                </div>
            </div>
            <div class="flex flex-col items-end gap-2">
                <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 text-sm font-bold border
                    {{ $this->isAvailable
                        ? 'bg-emerald-50 border-emerald-300 text-emerald-800'
                        : 'bg-slate-50 border-slate-300 text-slate-500' }}">
                    <span class="inline-block w-2.5 h-2.5 rounded-full {{ $this->isAvailable ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                    {{ $mentorProfile->availability_status->label() }}
                </span>
            </div>
        </div>

        <p class="mt-6 break-words whitespace-pre-line text-sm leading-6 text-slate-600">{{ $mentorProfile->bio ?: 'Mentor chưa cập nhật phần giới thiệu.' }}</p>

        <div class="mt-6 grid gap-4 sm:grid-cols-2">
            <div>
                <h2 class="text-sm font-bold text-slate-900">Chuyên môn</h2>
                <div class="mt-2 flex flex-wrap gap-2">
                    @foreach ($mentorProfile->expertise_topics ?? [] as $topic)
                        <span class="max-w-full truncate rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600" title="{{ $topic }}">{{ $topic }}</span>
                    @endforeach
                </div>
            </div>
            <div>
                <h2 class="text-sm font-bold text-slate-900">Có thể hỗ trợ</h2>
                <div class="mt-2 flex flex-wrap gap-2">
                    @foreach ($mentorProfile->help_topics ?? [] as $topic)
                        <span class="max-w-full truncate rounded-full bg-ue-brand-soft px-2.5 py-1 text-xs font-semibold text-ue-brand" title="{{ $topic }}">{{ $topic }}</span>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Request form --}}
        <div>
            <form wire:submit.prevent="submitRequest" class="mt-8 space-y-4 rounded-xl border border-slate-200 bg-slate-50 p-5">
                @csrf
                <h2 class="text-base font-bold text-slate-900">Gửi yêu cầu cố vấn</h2>

                @if (! $this->isAvailable)
                    <div class="rounded-lg bg-slate-100 px-4 py-3 text-sm text-slate-500">
                        Mentor này hiện không nhận yêu cầu mới.
                    </div>
                @endif

                <div>
                    <input wire:model.live="topic" maxlength="255" placeholder="Chủ đề *" class="w-full rounded-lg border-slate-200 text-sm focus:border-ue-brand focus:ring-ue-brand/20">
                    @error('topic') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    <p class="mt-1 text-right text-[10px] text-slate-400">{{ strlen($topic) }}/255</p>
                </div>
                <div>
                    <textarea wire:model.live="goal" rows="2" maxlength="5000" placeholder="Mục tiêu của bạn *" class="w-full rounded-lg border-slate-200 text-sm focus:border-ue-brand focus:ring-ue-brand/20"></textarea>
                    @error('goal') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    <p class="mt-1 text-right text-[10px] text-slate-400">{{ strlen($goal) }}/5000</p>
                </div>
                <div>
                    <textarea wire:model.live="question" rows="3" maxlength="5000" placeholder="Câu hỏi cụ thể muốn mentor hỗ trợ *" class="w-full rounded-lg border-slate-200 text-sm focus:border-ue-brand focus:ring-ue-brand/20"></textarea>
                    @error('question') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    <p class="mt-1 text-right text-[10px] text-slate-400">{{ strlen($question) }}/5000</p>
                </div>
                <div>
                    <select wire:model.live="urgency" class="w-full rounded-lg border-slate-200 text-sm focus:border-ue-brand focus:ring-ue-brand/20">
                        <option value="normal">Bình thường</option>
                        <option value="low">Không gấp</option>
                        <option value="high">Gấp</option>
                        <option value="time_sensitive">Có hạn định / Gấp</option>
                    </select>
                </div>
                <div>
                    <textarea wire:model.live="context" rows="2" maxlength="5000" placeholder="Bối cảnh bổ sung (không bắt buộc)" class="w-full rounded-lg border-slate-200 text-sm focus:border-ue-brand focus:ring-ue-brand/20"></textarea>
                    <p class="mt-1 text-right text-[10px] text-slate-400">{{ strlen($context) }}/5000</p>
                </div>
                <div>
                    <textarea wire:model.live="expected_outcome" rows="2" maxlength="1000" placeholder="Kết quả mong đợi (không bắt buộc)" class="w-full rounded-lg border-slate-200 text-sm focus:border-ue-brand focus:ring-ue-brand/20"></textarea>
                    <p class="mt-1 text-right text-[10px] text-slate-400">{{ strlen($expected_outcome) }}/1000</p>
                </div>

                @error('submit')
                    <div class="rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700">{{ $message }}</div>
                @enderror

                <button
                    type="submit"
                    @if (! $this->isAvailable) disabled @endif
                    class="w-full rounded-xl bg-ue-brand px-4 py-2.5 text-sm font-bold text-white hover:bg-ue-brand-dark transition
                    {{ ! $this->isAvailable ? 'opacity-50 cursor-not-allowed' : '' }}"
                >
                    Gửi yêu cầu
                </button>
            </form>
        </div>
    </section>

    {{-- Report modal --}}
    @if ($showReportModal)
        <div class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs ue-animate-fade-in" role="dialog" aria-modal="true" aria-labelledby="report-modal-title">
            <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-xl" @click.away="showReportModal = false">
                <div class="flex items-center justify-between mb-4">
                    <h3 id="report-modal-title" class="text-sm font-bold text-slate-800 flex items-center gap-2">
                        <svg class="h-4 w-4 text-red-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"/><line x1="4" y1="22" x2="4" y2="15"/></svg>
                        Báo cáo mentor
                    </h3>
                    <button wire:click="$set('showReportModal', false)" class="text-slate-400 hover:text-slate-600">
                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    </button>
                </div>

                <form wire:submit.prevent="submitReport" class="space-y-4">
                    <select wire:model.live="reportReason" class="w-full rounded-lg border-slate-200 text-sm">
                        <option value="">Chọn lý do *</option>
                        <option value="spam">Spam</option>
                        <option value="harassment">Quấy rối</option>
                        <option value="inappropriate_content">Nội dung không phù hợp</option>
                        <option value="misinformation">Thông tin sai lệch</option>
                        <option value="privacy_violation">Vi phạm quyền riêng tư</option>
                        <option value="other">Khác</option>
                    </select>
                    @error('reportReason') <p class="text-xs text-red-500">{{ $message }}</p> @enderror

                    <textarea wire:model.live="reportDescription" rows="3" maxlength="2000" placeholder="Mô tả chi tiết (không bắt buộc)" class="w-full rounded-lg border-slate-200 text-sm"></textarea>

                    <div class="flex gap-3">
                        <button type="button" wire:click="$set('showReportModal', false)" class="flex-1 rounded-xl border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-50 transition">Huỷ</button>
                        <button type="submit" class="flex-1 rounded-xl bg-red-500 px-4 py-2 text-sm font-bold text-white hover:bg-red-600 transition">Gửi báo cáo</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

</div>
