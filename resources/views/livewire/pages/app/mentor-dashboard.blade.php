<?php

use App\Models\MentorAccessRequest;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;

new class extends Component {
    public function with(): array
    {
        $user = Auth::user();

        return [
            'profile' => $user->mentorProfile()->first(),
            'received' => $user->receivedMentorRequests()->latest()->limit(10)->get(),
            'pendingRequest' => MentorAccessRequest::where('user_id', $user->id)
                ->whereIn('status', ['submitted', 'under_review', 'need_more_info'])
                ->latest()
                ->first(),
        ];
    }
};
?>

<div class="mx-auto max-w-5xl px-4 py-6 sm:px-6 lg:px-8">
    @if (session('status'))
        <div class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800 shadow-sm">
            <p class="font-bold">{{ session('status') }}</p>
        </div>
    @endif

    @if ($pendingRequest && !$profile)
        <div class="mb-6 rounded-2xl border border-blue-100 bg-blue-50 p-5 text-sm text-blue-900 shadow-sm">
            <p class="font-bold text-base">Yêu cầu mentor của bạn: {{ $pendingRequest->status->label() }}</p>
            @if ($pendingRequest->status === \App\Enums\MentorAccessStatus::NeedMoreInfo)
                <p class="mt-2 text-amber-800 font-medium">Quản trị viên yêu cầu bổ sung thông tin trước khi xét duyệt.</p>
                @if ($pendingRequest->review_reason)
                    <div class="mt-3 rounded-xl border border-amber-200 bg-amber-50 p-4 text-amber-900">
                        <p class="font-bold">Lý do:</p>
                        <p class="mt-1 font-medium">{{ $pendingRequest->review_reason }}</p>
                    </div>
                @endif
                <a href="{{ route('mentor.apply') }}" class="mt-4 inline-flex items-center justify-center rounded-xl bg-amber-600 px-5 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-amber-700 transition-all">
                    Chỉnh sửa đơn đăng ký
                </a>
            @else
                <p class="mt-2 text-blue-800">Ban quản trị đang xét duyệt yêu cầu của bạn. Bạn sẽ nhận được thông báo khi có kết quả.</p>
            @endif
        </div>
    @endif

    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-slate-900">Mentor dashboard</h1>
        @if ($profile)
            <a href="{{ route('mentor.setup') }}" class="text-sm font-semibold text-ue-brand hover:underline">Cập nhật hồ sơ</a>
        @endif
    </div>

    @if ($profile)
        <div class="mt-6 grid gap-4 md:grid-cols-3">
            <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
                <p class="text-xs font-semibold text-slate-500">Trạng thái</p>
                <p class="mt-2 text-lg font-bold text-slate-900">{{ $profile->availability_status->label() }}</p>
            </div>
            <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
                <p class="text-xs font-semibold text-slate-500">Độ hoàn thiện</p>
                <p class="mt-2 text-lg font-bold text-slate-900">{{ $profile->getProfileCompletenessScore() }}%</p>
            </div>
            <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
                <p class="text-xs font-semibold text-slate-500">Giới hạn đang chờ</p>
                <p class="mt-2 text-lg font-bold text-slate-900">{{ $profile->max_pending_requests }}</p>
            </div>
        </div>
    @elseif (!$pendingRequest)
        <div class="mt-6 rounded-lg border border-slate-200 bg-white p-6 text-sm text-slate-600">
            Bạn chưa có hồ sơ mentor. 
            <a href="{{ route('mentor.apply') }}" class="font-semibold text-ue-brand hover:underline">Gửi đăng ký ngay</a> hoặc chờ quản trị viên duyệt.
        </div>
    @endif

    <section class="mt-6 rounded-lg border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-100 px-4 py-3 text-sm font-bold text-slate-900">Yêu cầu mới nhất</div>
        <div class="divide-y divide-slate-100">
            @forelse ($received as $request)
                <a href="{{ route('mentor.requests.show', $request) }}" class="block px-4 py-3 text-sm hover:bg-slate-50">
                    <span class="font-semibold text-slate-900">{{ $request->topic }}</span>
                    <span class="ml-2 text-xs text-slate-500">{{ $request->status->label() }}</span>
                </a>
            @empty
                <p class="px-4 py-6 text-sm text-slate-500">Chưa có yêu cầu cố vấn.</p>
            @endforelse
        </div>
    </section>
</div>
