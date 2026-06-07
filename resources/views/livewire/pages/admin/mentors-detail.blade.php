<?php

use App\Models\MentorAccessRequest;
use Livewire\Volt\Component;

new class extends Component {
    public int $id;

    public function with(): array
    {
        return [
            'request' => MentorAccessRequest::with(['user.profile', 'reviewer'])->findOrFail($this->id),
        ];
    }
};
?>

<div class="mx-auto max-w-5xl px-4 py-8 sm:px-6 lg:px-8">
    <a href="{{ route('admin.mentors.index') }}" class="text-sm font-semibold text-ue-brand hover:underline">← Quay lại hàng đợi</a>

    <div class="mt-4 grid gap-6 lg:grid-cols-[1fr_320px]">
        <section class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900">Yêu cầu Mentor #{{ $request->id }}</h1>
                    <p class="mt-1 text-sm text-slate-500">{{ $request->user?->name }} · {{ $request->user?->email }}</p>
                </div>
                <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-700">{{ $request->status->label() }}</span>
            </div>

            <dl class="mt-6 space-y-5 text-sm">
                <div>
                    <dt class="font-bold text-slate-900">Vai trò đăng ký</dt>
                    <dd class="mt-1 text-slate-600">{{ $request->requested_role_context }}</dd>
                </div>
                <div>
                    <dt class="font-bold text-slate-900">Động lực</dt>
                    <dd class="mt-1 whitespace-pre-line text-slate-600">{{ $request->motivation }}</dd>
                </div>
                <div>
                    <dt class="font-bold text-slate-900">Kinh nghiệm</dt>
                    <dd class="mt-1 whitespace-pre-line text-slate-600">{{ $request->experience_summary ?: 'Chưa cung cấp' }}</dd>
                </div>
                <div>
                    <dt class="font-bold text-slate-900">Chủ đề chuyên môn</dt>
                    <dd class="mt-2 flex flex-wrap gap-2">
                        @foreach ($request->expertise_topics ?? [] as $topic)
                            <span class="rounded-full bg-slate-100 px-2 py-1 text-xs font-semibold text-slate-600">{{ $topic }}</span>
                        @endforeach
                    </dd>
                </div>
            </dl>
        </section>

        <aside class="space-y-4">
            <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
                <h2 class="text-sm font-bold text-slate-900">Xử lý</h2>
                <form method="POST" action="{{ route('admin.mentors.action', $request) }}" class="mt-4 space-y-3">
                    @csrf
                    <select name="action" class="w-full rounded-lg border-slate-200 text-sm">
                        <option value="approve">Phê duyệt</option>
                        <option value="reject">Từ chối</option>
                        <option value="request_more_info">Cần thêm thông tin</option>
                        <option value="revoke">Thu hồi</option>
                    </select>
                    <textarea name="reason" required rows="3" placeholder="Lý do xử lý" class="w-full rounded-lg border-slate-200 text-sm"></textarea>
                    <textarea name="instruction" rows="3" placeholder="Ghi chú nội bộ hoặc hướng dẫn bổ sung" class="w-full rounded-lg border-slate-200 text-sm"></textarea>
                    <button class="w-full rounded-lg bg-ue-brand px-4 py-2 text-sm font-semibold text-white hover:bg-ue-brand-dark">Xác nhận</button>
                </form>
            </div>

            <div class="rounded-lg border border-slate-200 bg-white p-4 text-sm shadow-sm">
                <h2 class="font-bold text-slate-900">Review</h2>
                <p class="mt-2 text-slate-500">Người duyệt: {{ $request->reviewer?->name ?? 'Chưa duyệt' }}</p>
                <p class="mt-1 text-slate-500">Thời điểm: {{ $request->reviewed_at?->format('d/m/Y H:i') ?? 'N/A' }}</p>
                <p class="mt-1 text-slate-500">Lý do: {{ $request->review_reason ?? 'N/A' }}</p>
            </div>
        </aside>
    </div>
</div>
