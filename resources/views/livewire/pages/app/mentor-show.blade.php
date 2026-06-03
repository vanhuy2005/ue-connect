<?php

use App\Models\MentorProfile;
use Livewire\Volt\Component;

new class extends Component {
    public MentorProfile $mentorProfile;
};
?>

<div class="mx-auto max-w-4xl px-4 py-6 sm:px-6 lg:px-8">
    <a href="{{ route('mentor.discovery') }}" class="text-sm font-semibold text-ue-brand hover:underline">← Quay lại danh sách mentor</a>

    <section class="mt-4 rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div class="flex items-start gap-4">
                <a href="{{ route('profile.show', $mentorProfile->user) }}" class="block rounded-full focus:outline-none focus:ring-2 focus:ring-ue-brand/30" aria-label="Xem trang cá nhân của {{ $mentorProfile->user->name }}">
                    <x-ui.avatar :user="$mentorProfile->user" size="lg" />
                </a>
                <div>
                    <a href="{{ route('profile.show', $mentorProfile->user) }}" class="text-2xl font-bold text-slate-900 hover:text-ue-brand hover:underline">{{ $mentorProfile->user->name }}</a>
                    <p class="mt-1 text-sm font-semibold text-emerald-700">{{ $mentorProfile->availability_status->label() }}</p>
                    <p class="mt-3 text-base font-semibold text-slate-800 break-words">{{ $mentorProfile->headline ?: 'Mentor UEConnect' }}</p>
                </div>
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

        <form method="POST" action="{{ route('mentor.requests.store') }}" class="mt-8 space-y-4 rounded-lg border border-slate-200 bg-slate-50 p-4">
            @csrf
            <input type="hidden" name="mentor_profile_id" value="{{ $mentorProfile->id }}">
            <h2 class="text-base font-bold text-slate-900">Gửi yêu cầu cố vấn</h2>
            <input name="topic" required maxlength="255" placeholder="Chủ đề" class="w-full rounded-lg border-slate-200 text-sm">
            <textarea name="goal" required rows="2" placeholder="Mục tiêu của bạn" class="w-full rounded-lg border-slate-200 text-sm"></textarea>
            <textarea name="question" required rows="3" placeholder="Câu hỏi cụ thể muốn mentor hỗ trợ" class="w-full rounded-lg border-slate-200 text-sm"></textarea>
            <select name="urgency" class="w-full rounded-lg border-slate-200 text-sm">
                <option value="normal">Bình thường</option>
                <option value="low">Không gấp</option>
                <option value="high">Gấp</option>
            </select>
            <textarea name="context" rows="2" placeholder="Bối cảnh bổ sung (không bắt buộc)" class="w-full rounded-lg border-slate-200 text-sm"></textarea>
            <button class="rounded-lg bg-ue-brand px-4 py-2 text-sm font-semibold text-white hover:bg-ue-brand-dark">Gửi yêu cầu</button>
        </form>
    </section>
</div>
