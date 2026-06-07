<?php

use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;

new class extends Component {
    public function with(): array
    {
        $user = Auth::user();
        $profile = $user->mentorProfile;

        return [
            'sent' => $user->sentMentorRequests()->with('mentor')->latest()->get(),
            'received' => $profile && $profile->is_active
                ? $user->receivedMentorRequests()->with('student')->latest()->get()
                : collect(),
            'isMentor' => $profile && $profile->is_active,
        ];
    }
}; ?>

<div class="mx-auto max-w-5xl px-4 py-6 sm:px-6 lg:px-8">
    <h1 class="text-2xl font-bold text-slate-900">Yêu cầu cố vấn</h1>

    <div class="mt-6 grid gap-6 {{ $isMentor ? 'lg:grid-cols-2' : 'lg:grid-cols-1 max-w-2xl' }}">
        @if ($isMentor)
            <section class="rounded-lg border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-100 px-4 py-3 text-sm font-bold text-slate-900">Yêu cầu nhận được</div>
                <div class="divide-y divide-slate-100">
                    @forelse ($received as $request)
                        <a href="{{ route('mentor.requests.show', $request) }}" class="block px-4 py-3 text-sm hover:bg-slate-50">
                            <span class="font-semibold text-slate-900">{{ $request->topic }}</span>
                            @if ($request->student)
                                <span class="ml-2 text-xs text-slate-400">từ {{ $request->student->name }} ({{ $request->student->email }})</span>
                            @endif
                            <span class="ml-2 text-xs text-slate-500">{{ $request->status->label() }}</span>
                        </a>
                    @empty
                        <p class="px-4 py-6 text-sm text-slate-500 text-center">Chưa có yêu cầu cố vấn nào.</p>
                    @endforelse
                </div>
            </section>
        @endif

        <section class="rounded-lg border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-4 py-3 text-sm font-bold text-slate-900">Yêu cầu đã gửi</div>
            <div class="divide-y divide-slate-100">
                @forelse ($sent as $request)
                    <a href="{{ route('mentor.requests.show', $request) }}" class="block px-4 py-3 text-sm hover:bg-slate-50">
                        <span class="font-semibold text-slate-900">{{ $request->topic }}</span>
                        @if ($request->mentor)
                            <span class="ml-2 text-xs text-slate-400">đến {{ $request->mentor->name }} ({{ $request->mentor->email }})</span>
                        @endif
                        <span class="ml-2 text-xs text-slate-500">{{ $request->status->label() }}</span>
                    </a>
                @empty
                    <p class="px-4 py-6 text-sm text-slate-500 text-center">Bạn chưa gửi yêu cầu cố vấn nào.</p>
                @endforelse
            </div>
        </section>
    </div>
</div>
