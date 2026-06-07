<?php

use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;

new class extends Component {
    public function with(): array
    {
        $user = Auth::user();

        return [
            'profile' => $user->mentorProfile()->first(),
            'received' => $user->receivedMentorRequests()->latest()->limit(10)->get(),
        ];
    }
};
?>

<div class="mx-auto max-w-5xl px-4 py-6 sm:px-6 lg:px-8">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-slate-900">Mentor dashboard</h1>
        <a href="{{ route('mentor.setup') }}" class="text-sm font-semibold text-ue-brand hover:underline">Cập nhật hồ sơ</a>
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
    @else
        <div class="mt-6 rounded-lg border border-slate-200 bg-white p-6 text-sm text-slate-600">Bạn chưa có hồ sơ mentor. Hãy gửi đăng ký hoặc chờ quản trị viên duyệt.</div>
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
