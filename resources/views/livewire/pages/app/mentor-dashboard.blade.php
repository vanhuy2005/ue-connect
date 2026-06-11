<?php

use App\Enums\MentorFeedbackLevel;
use App\Models\MentorAccessRequest;
use App\Models\MentorFeedback;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;

new class extends Component {
    public function with(): array
    {
        $user = Auth::user();
        $profile = $user->mentorProfile()->where('is_active', true)->first();

        // Ensure availability status always reflects the real pending count
        if ($profile) {
            $profile->syncAvailabilityFromPendingCount();
        }

        // Anonymous feedback stats — không load relation student để bảo vệ ẩn danh
        $feedbackQuery = MentorFeedback::where('mentor_id', $user->id)->whereNull('deleted_at');
        $feedbackStats = [
            'total'            => (clone $feedbackQuery)->count(),
            'helpful'          => (clone $feedbackQuery)->where('helpfulness_level', MentorFeedbackLevel::Helpful->value)->count(),
            'somewhat_helpful' => (clone $feedbackQuery)->where('helpfulness_level', MentorFeedbackLevel::SomewhatHelpful->value)->count(),
            'not_helpful'      => (clone $feedbackQuery)->where('helpfulness_level', MentorFeedbackLevel::NotHelpful->value)->count(),
        ];
        $recentAnonymousFeedbacks = (clone $feedbackQuery)
            ->whereNotNull('feedback_text')
            ->latest()
            ->limit(5)
            ->get(['id', 'helpfulness_level', 'feedback_text', 'created_at']);

        return [
            'profile'                  => $profile,
            'received'                 => $user->receivedMentorRequests()->latest()->limit(10)->get(),
            'pendingRequest'           => MentorAccessRequest::where('user_id', $user->id)
                ->whereIn('status', ['submitted', 'under_review', 'need_more_info'])
                ->latest()
                ->first(),
            'activePendingCount'       => $profile
                ? $profile->mentorRequests()
                    ->whereIn('status', ['submitted', 'accepted', 'need_more_info', 'updated_by_student'])
                    ->count()
                : 0,
            'completedCount'           => $profile
                ? $profile->mentorRequests()
                    ->where('status', 'completed')
                    ->count()
                : 0,
            'feedbackStats'            => $feedbackStats,
            'recentAnonymousFeedbacks' => $recentAnonymousFeedbacks,
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
        @php
            $maxPending = $profile->max_pending_requests ?: 1;
            $fillPct    = min(100, (int) round($activePendingCount / $maxPending * 100));
            $barColor   = $fillPct >= 100 ? 'bg-red-500' : ($fillPct >= 70 ? 'bg-amber-400' : 'bg-emerald-500');
            $textColor  = $fillPct >= 100 ? 'text-red-600' : ($fillPct >= 70 ? 'text-amber-600' : 'text-slate-900');
        @endphp
        <div class="mt-6 grid gap-4 md:grid-cols-3">
            {{-- Card 1: Trạng thái (giữ nguyên) --}}
            <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
                <p class="text-xs font-semibold text-slate-500">Trạng thái</p>
                <p class="mt-2 text-lg font-bold text-slate-900">{{ $profile->availability_status->label() }}</p>
            </div>

            {{-- Card 2: Đang xử lý (X/max + progress bar) --}}
            <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
                <p class="text-xs font-semibold text-slate-500">Đang xử lý</p>
                <div class="mt-2 flex items-end gap-1.5">
                    <p class="text-lg font-bold {{ $textColor }}">{{ $activePendingCount }}</p>
                    <p class="mb-0.5 text-sm font-semibold text-slate-400">/ {{ $maxPending }}</p>
                </div>
                <div class="mt-2 h-1.5 w-full rounded-full bg-slate-100">
                    <div class="h-1.5 rounded-full transition-all {{ $barColor }}" style="width: {{ $fillPct }}%"></div>
                </div>
                @if ($fillPct >= 100)
                    <p class="mt-1.5 text-xs font-bold text-red-500 hidden md:block">Đã đạt giới hạn — không nhận yêu cầu mới</p>
                @else
                    <p class="mt-1.5 text-xs text-slate-400 hidden md:block">{{ $maxPending - $activePendingCount }} lượt còn trống</p>
                @endif
            </div>

            {{-- Card 3: Đã hoàn thành --}}
            <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
                <p class="text-xs font-semibold text-slate-500">Đã hoàn thành</p>
                <p class="mt-2 text-lg font-bold text-slate-900">{{ $completedCount }}</p>
                <p class="mt-1.5 text-xs text-slate-400 hidden md:block">phiên tư vấn</p>
            </div>
        </div>
    @elseif (!$pendingRequest)
        <div class="mt-6 rounded-lg border border-slate-200 bg-white p-6 text-sm text-slate-600">
            Bạn chưa có hồ sơ mentor. 
            <a href="{{ route('mentor.apply') }}" class="font-semibold text-ue-brand hover:underline">Gửi đăng ký ngay</a> hoặc chờ quản trị viên duyệt.
        </div>
    @endif

    <div class="mt-6 grid gap-6 md:grid-cols-3">
        <section class="{{ $feedbackStats['total'] > 0 ? 'md:col-span-2' : 'md:col-span-3' }} rounded-lg border border-slate-200 bg-white shadow-sm">
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

        {{-- Anonymous Feedback Section --}}
        @if ($feedbackStats['total'] > 0)
            <section class="md:col-span-1 rounded-lg border border-slate-200 bg-white shadow-sm overflow-hidden">
                <div class="border-b border-slate-100 px-4 py-3 flex items-center gap-2">
                    <span class="text-sm font-bold text-slate-900">Phản hồi</span>
                    <span class="ml-auto text-xs font-bold text-slate-400">{{ $feedbackStats['total'] }}</span>
                </div>

                {{-- Helpfulness breakdown --}}
                <div class="px-4 py-3 space-y-2 border-b border-slate-100">
                    @php
                        $total = $feedbackStats['total'];
                        $levels = [
                            ['label' => 'Hữu ích',         'count' => $feedbackStats['helpful'],          'color' => 'emerald'],
                            ['label' => 'Khá hữu ích',     'count' => $feedbackStats['somewhat_helpful'], 'color' => 'amber'],
                            ['label' => 'Chưa hữu ích',    'count' => $feedbackStats['not_helpful'],      'color' => 'red'],
                        ];
                    @endphp
                    @foreach ($levels as $level)
                        @php
                            $bgColor = match($level['color']) {
                                'emerald' => 'bg-emerald-50 border-emerald-200',
                                'amber'   => 'bg-amber-50 border-amber-200',
                                'red'     => 'bg-red-50 border-red-200',
                            };
                            $textColor = match($level['color']) {
                                'emerald' => 'text-emerald-700',
                                'amber'   => 'text-amber-600',
                                'red'     => 'text-red-600',
                            };
                        @endphp
                        <div class="flex items-center gap-3 rounded-xl border {{ $bgColor }} px-4 py-3">
                            <span class="text-xl font-bold {{ $textColor }}">{{ $level['count'] }}</span>
                            <span class="text-sm font-semibold {{ $textColor }}">{{ $level['label'] }}</span>
                        </div>
                    @endforeach
                </div>

                {{-- Anonymous comments --}}
                @if ($recentAnonymousFeedbacks->isNotEmpty())
                    <div class="divide-y divide-slate-50">
                        @foreach ($recentAnonymousFeedbacks as $fb)
                            @php
                                $levelColor = match($fb->helpfulness_level) {
                                    \App\Enums\MentorFeedbackLevel::Helpful         => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                    \App\Enums\MentorFeedbackLevel::SomewhatHelpful => 'bg-amber-50 text-amber-700 border-amber-200',
                                    \App\Enums\MentorFeedbackLevel::NotHelpful      => 'bg-red-50 text-red-700 border-red-200',
                                };
                            @endphp
                            <div class="px-4 py-3">
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="text-[10px] font-bold px-2.5 py-1 rounded-xl border {{ $levelColor }}">{{ $fb->helpfulness_level->label() }}</span>
                                    <span class="text-[10px] text-slate-400">{{ $fb->created_at->diffForHumans() }}</span>
                                </div>
                                <p class="text-sm text-slate-600 italic leading-relaxed">"{{ $fb->feedback_text }}"</p>
                            </div>
                        @endforeach
                    </div>
                    @if ($feedbackStats['total'] > 5)
                        <div class="px-4 py-3 border-t border-slate-100">
                            <button class="text-xs font-bold text-ue-brand hover:underline">Xem tất cả</button>
                        </div>
                    @endif
                @endif
            </section>
        @endif
    </div>
</div>
