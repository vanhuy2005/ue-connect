<?php

use App\Actions\Media\GenerateMediaUrlAction;
use App\Models\Media;
use App\Models\MentorAccessRequest;
use Livewire\Volt\Component;

new class extends Component {
    public int $id;

    public function with(): array
    {
        $request = MentorAccessRequest::with(['user.profile.media', 'reviewer'])->findOrFail($this->id);

        $avatarUrl = null;
        $evidenceUrl = null;
        $evidenceFilename = null;

        if ($request->user?->profile) {
            $avatarMedia = $request->user->profile->avatar()->first();
            if ($avatarMedia) {
                $avatarUrl = app(GenerateMediaUrlAction::class)->execute($avatarMedia, 'thumb', request()->user());
            }
        }

        if ($request->evidence_media_id) {
            $evidenceMedia = Media::find($request->evidence_media_id);
            if ($evidenceMedia) {
                $evidenceUrl = app(GenerateMediaUrlAction::class)->execute($evidenceMedia, 'preview', request()->user());
                $evidenceFilename = $evidenceMedia->original_filename;
            }
        }

        return [
            'request' => $request,
            'avatarUrl' => $avatarUrl,
            'evidenceUrl' => $evidenceUrl,
            'evidenceFilename' => $evidenceFilename,
        ];
    }
};
?>

<div class="mx-auto max-w-5xl px-4 py-8 sm:px-6 lg:px-8">
    @if (session('status'))
        <div class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800 shadow-sm">
            <p class="font-bold">{{ session('status') }}</p>
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-700 shadow-sm">
            <p class="font-bold text-red-900">Lỗi xử lý:</p>
            <ul class="mt-2 list-disc space-y-1 pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <a href="{{ route('admin.mentors.index') }}" class="text-sm font-semibold text-ue-brand hover:underline">← Quay lại hàng đợi</a>

    <div class="mt-4 grid gap-6 lg:grid-cols-[1fr_320px]">
        <div class="space-y-6">
            {{-- Thông tin hồ sơ --}}
            <section class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex items-start justify-between gap-4">
                    <div class="flex items-center gap-4">
                        @if ($avatarUrl)
                            <a href="{{ $avatarUrl }}" target="_blank" rel="noopener noreferrer">
                                <img src="{{ $avatarUrl }}" class="h-14 w-14 rounded-full border-2 border-slate-200 object-cover shrink-0 hover:opacity-80 transition-opacity" alt="Avatar" title="Bấm để xem ảnh lớn" />
                            </a>
                        @else
                            <div class="h-14 w-14 rounded-full bg-slate-100 flex items-center justify-center shrink-0">
                                <x-ui.icon name="user" size="lg" class="text-slate-400" />
                            </div>
                        @endif
                        <div>
                            <h1 class="text-2xl font-bold text-slate-900">Yêu cầu Mentor #{{ $request->id }}</h1>
                            <p class="mt-0.5 text-sm text-slate-500">{{ $request->user?->name }} · {{ $request->user?->email }}</p>
                        </div>
                    </div>
                    <span class="shrink-0 rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-700">{{ $request->status->label() }}</span>
                </div>
            </section>

            {{-- Headline & Bio --}}
            @if ($request->headline || $request->bio)
                <section class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
                    <h2 class="text-sm font-bold text-slate-900 border-b border-slate-100 pb-2">Thông tin hồ sơ công khai</h2>
                    <dl class="mt-4 space-y-4 text-sm">
                        @if ($request->headline)
                            <div>
                                <dt class="font-bold text-slate-700">Headline</dt>
                                <dd class="mt-1 text-slate-600">{{ $request->headline }}</dd>
                            </div>
                        @endif
                        @if ($request->bio)
                            <div>
                                <dt class="font-bold text-slate-700">Giới thiệu</dt>
                                <dd class="mt-1 whitespace-pre-line text-slate-600">{{ $request->bio }}</dd>
                            </div>
                        @endif
                    </dl>
                </section>
            @endif

            {{-- Năng lực & Chuyên môn --}}
            <section class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-sm font-bold text-slate-900 border-b border-slate-100 pb-2">Năng lực & Chuyên môn</h2>
                <dl class="mt-4 space-y-5 text-sm">
                    <div>
                        <dt class="font-bold text-slate-700">Vai trò đăng ký</dt>
                        <dd class="mt-1 text-slate-600">{{ $request->requested_role_context }}</dd>
                    </div>

                    @if ($request->expertise_topics)
                        <div>
                            <dt class="font-bold text-slate-700">Chủ đề chuyên môn</dt>
                            <dd class="mt-2 flex flex-wrap gap-2">
                                @foreach ($request->expertise_topics as $topic)
                                    <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600">{{ $topic }}</span>
                                @endforeach
                            </dd>
                        </div>
                    @endif

                    @if ($request->help_topics)
                        <div>
                            <dt class="font-bold text-slate-700">Nội dung hỗ trợ</dt>
                            <dd class="mt-2 flex flex-wrap gap-2">
                                @foreach ($request->help_topics as $topic)
                                    <span class="rounded-full bg-blue-50 px-2.5 py-1 text-xs font-semibold text-blue-700">{{ $topic }}</span>
                                @endforeach
                            </dd>
                        </div>
                    @endif

                    @if ($request->career_paths)
                        <div>
                            <dt class="font-bold text-slate-700">Lộ trình sự nghiệp</dt>
                            <dd class="mt-2 flex flex-wrap gap-2">
                                @foreach ($request->career_paths as $path)
                                    <span class="rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700">{{ $path }}</span>
                                @endforeach
                            </dd>
                        </div>
                    @endif

                    @if ($request->skills)
                        <div>
                            <dt class="font-bold text-slate-700">Kỹ năng</dt>
                            <dd class="mt-1 text-slate-600">{{ implode(', ', $request->skills) }}</dd>
                        </div>
                    @endif
                </dl>
            </section>

            {{-- Phạm vi hỗ trợ & Kỳ vọng --}}
            <section class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-sm font-bold text-slate-900 border-b border-slate-100 pb-2">Phạm vi hỗ trợ & Kỳ vọng</h2>
                <dl class="mt-4 space-y-4 text-sm">
                    @if ($request->preferred_request_types)
                        <div>
                            <dt class="font-bold text-slate-700">Loại yêu cầu nhận được</dt>
                            <dd class="mt-1 text-slate-600">{{ implode(', ', $request->preferred_request_types) }}</dd>
                        </div>
                    @endif

                    @if ($request->response_expectation_text)
                        <div>
                            <dt class="font-bold text-slate-700">Thời gian phản hồi</dt>
                            <dd class="mt-1 text-slate-600">{{ $request->response_expectation_text }}</dd>
                        </div>
                    @endif

                    @if ($request->office_hours_text)
                        <div>
                            <dt class="font-bold text-slate-700">Khung giờ hỗ trợ</dt>
                            <dd class="mt-1 text-slate-600">{{ $request->office_hours_text }}</dd>
                        </div>
                    @endif

                    @if ($request->portfolio_link)
                        <div>
                            <dt class="font-bold text-slate-700">Portfolio</dt>
                            <dd class="mt-1"><a href="{{ $request->portfolio_link }}" target="_blank" rel="noopener noreferrer" class="text-ue-brand hover:underline">{{ $request->portfolio_link }}</a></dd>
                        </div>
                    @endif

                    @if ($request->availability_note)
                        <div>
                            <dt class="font-bold text-slate-700">Lưu ý lịch làm việc</dt>
                            <dd class="mt-1 text-slate-600">{{ $request->availability_note }}</dd>
                        </div>
                    @endif
                </dl>
            </section>

            {{-- Động lực & Kinh nghiệm --}}
            <section class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="text-sm font-bold text-slate-900 border-b border-slate-100 pb-2">Động lực & Kinh nghiệm</h2>
                <dl class="mt-4 space-y-4 text-sm">
                    <div>
                        <dt class="font-bold text-slate-700">Mục tiêu / Động lực</dt>
                        <dd class="mt-1 whitespace-pre-line text-slate-600">{{ $request->motivation }}</dd>
                    </div>
                    @if ($request->experience_summary)
                        <div>
                            <dt class="font-bold text-slate-700">Kinh nghiệm thực tế</dt>
                            <dd class="mt-1 whitespace-pre-line text-slate-600">{{ $request->experience_summary }}</dd>
                        </div>
                    @endif
                </dl>
            </section>

            {{-- Minh chứng --}}
            @if ($evidenceUrl || $request->policy_agreed)
                <section class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
                    <h2 class="text-sm font-bold text-slate-900 border-b border-slate-100 pb-2">Minh chứng & Cam kết</h2>
                    <dl class="mt-4 space-y-4 text-sm">
                        @if ($evidenceUrl)
                            <div>
                                <dt class="font-bold text-slate-700">File minh chứng</dt>
                                <dd class="mt-2">
                                    <a href="{{ $evidenceUrl }}" target="_blank" rel="noopener noreferrer">
                                        <img src="{{ $evidenceUrl }}" class="max-h-60 rounded-lg border border-slate-200 shadow-sm object-contain" alt="{{ $evidenceFilename ?? 'Minh chứng' }}" title="{{ $evidenceFilename ?? 'Minh chứng' }}" />
                                    </a>
                                    @if ($evidenceFilename)
                                        <p class="mt-1.5 text-xs text-slate-500">{{ $evidenceFilename }}</p>
                                    @endif
                                </dd>
                            </div>
                        @endif
                        @if ($request->policy_agreed)
                            <div>
                                <dt class="font-bold text-slate-700">Chính sách</dt>
                                <dd class="mt-1 text-emerald-700 font-semibold flex items-center gap-1">
                                    <x-ui.icon name="check-circle" size="sm" />
                                    Đã đồng ý với chính sách bảo mật và an toàn cộng đồng
                                </dd>
                            </div>
                        @endif
                    </dl>
                </section>
            @endif
        </div>

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
                    <textarea name="reason" required rows="3" placeholder="Lý do xử lý (tối thiểu 10 ký tự)" class="w-full rounded-lg border-slate-200 text-sm"></textarea>
                    <textarea name="instruction" rows="3" placeholder="Ghi chú nội bộ hoặc hướng dẫn bổ sung" class="w-full rounded-lg border-slate-200 text-sm"></textarea>
                    <button class="w-full rounded-lg bg-ue-brand px-4 py-2 text-sm font-semibold text-white hover:bg-ue-brand-dark">Xác nhận</button>
                </form>
            </div>

            <div class="rounded-lg border border-slate-200 bg-white p-4 text-sm shadow-sm">
                <h2 class="font-bold text-slate-900">Review</h2>
                <p class="mt-2 text-slate-500">Người duyệt: {{ $request->reviewer?->name ?? 'Chưa duyệt' }}</p>
                <p class="mt-1 text-slate-500">Thời điểm: {{ $request->reviewed_at?->format('d/m/Y H:i') ?? 'N/A' }}</p>
                <p class="mt-1 text-slate-500">Lý do: {{ $request->review_reason ?? 'N/A' }}</p>
                <p class="mt-1 text-slate-500">Ghi chú: {{ $request->admin_notes ?? 'N/A' }}</p>
            </div>
        </aside>
    </div>
</div>
