<?php

use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;

new class extends Component {
    public function with(): array
    {
        $user = Auth::user();

        return [
            'sent' => $user->sentMentorRequests()->with('mentor')->latest()->get(),
            'received' => $user->receivedMentorRequests()->with('student')->latest()->get(),
        ];
    }
};
?>

<div class="mx-auto max-w-5xl px-4 py-6 sm:px-6 lg:px-8">
    <h1 class="text-2xl font-bold text-slate-900">Yêu cầu cố vấn</h1>

    <div class="mt-6 grid gap-6 lg:grid-cols-2">
        @foreach (['received' => 'Yêu cầu nhận được', 'sent' => 'Yêu cầu đã gửi'] as $key => $title)
            <section class="rounded-lg border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-100 px-4 py-3 text-sm font-bold text-slate-900">{{ $title }}</div>
                <div class="divide-y divide-slate-100">
                    @forelse ($$key as $request)
                        <a href="{{ route('mentor.requests.show', $request) }}" class="block px-4 py-3 text-sm hover:bg-slate-50">
                            <span class="font-semibold text-slate-900">{{ $request->topic }}</span>
                            <span class="ml-2 text-xs text-slate-500">{{ $request->status->label() }}</span>
                        </a>
                    @empty
                        <p class="px-4 py-6 text-sm text-slate-500">Chưa có dữ liệu.</p>
                    @endforelse
                </div>
            </section>
        @endforeach
    </div>
</div>
