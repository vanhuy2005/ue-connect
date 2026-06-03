<?php

use App\Actions\Mentor\RequestMentorAccessAction;
use App\Enums\MentorAccessStatus;
use App\Models\MentorAccessRequest;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;

new class extends Component {
    public function with(): array
    {
        $user = Auth::user();
        $openRequest = MentorAccessRequest::where('user_id', $user->id)
            ->whereIn('status', [
                MentorAccessStatus::Submitted->value,
                MentorAccessStatus::UnderReview->value,
                MentorAccessStatus::Approved->value,
                MentorAccessStatus::NeedMoreInfo->value,
            ])
            ->latest()
            ->first();

        return [
            'eligibleRoleContexts' => RequestMentorAccessAction::eligibleRoleContextsFor($user),
            'openRequest' => $openRequest,
            'mentorProfile' => $user->mentorProfile()->first(),
            'profileRoleType' => $user->profile?->role_type,
        ];
    }
};
?>

<div class="mx-auto max-w-3xl px-4 py-6 sm:px-6 lg:px-8">
    <h1 class="text-2xl font-bold text-slate-900">Đăng ký trở thành mentor</h1>
    <p class="mt-1 text-sm text-slate-500">Chia sẻ kinh nghiệm của bạn để ban quản trị xét duyệt quyền mentor.</p>

    @if ($openRequest)
        <div class="mt-6 rounded-lg border border-blue-100 bg-blue-50 p-5 text-sm text-blue-900">
            <p class="font-semibold">Bạn đã có yêu cầu mentor: {{ $openRequest->status->label() }}</p>
            @if ($mentorProfile || $openRequest->status === MentorAccessStatus::Approved)
                <p class="mt-1 text-blue-800">Yêu cầu của bạn đã được duyệt. Bước tiếp theo là hoàn thiện hồ sơ mentor công khai để người học có thể tin tưởng khi gửi yêu cầu.</p>
                <div class="mt-4 flex flex-wrap gap-2">
                    <a href="{{ route('mentor.setup') }}" class="inline-flex items-center justify-center rounded-lg bg-ue-brand px-4 py-2 text-sm font-bold text-white hover:bg-ue-brand-dark">
                        Thiết lập hồ sơ mentor
                    </a>
                    <a href="{{ route('mentor.dashboard') }}" class="inline-flex items-center justify-center rounded-lg border border-blue-200 bg-white px-4 py-2 text-sm font-bold text-blue-900 hover:bg-blue-100">
                        Mentor dashboard
                    </a>
                </div>
            @else
                <p class="mt-1 text-blue-800">Ban quản trị sẽ xét duyệt yêu cầu này. Nếu trạng thái là cần thêm thông tin, hãy bổ sung theo phản hồi từ quản trị viên.</p>
            @endif
        </div>
    @elseif (empty($eligibleRoleContexts))
        <div class="mt-6 rounded-lg border border-amber-100 bg-amber-50 p-5 text-sm text-amber-900">
            <p class="font-semibold">Hồ sơ hiện tại chưa đủ điều kiện đăng ký mentor.</p>
            <p class="mt-1 text-amber-800">
                Hãy hoàn tất xác thực tài khoản và hồ sơ vai trò. Mentor hiện hỗ trợ cựu sinh viên, cố vấn/giảng viên và sinh viên nổi bật khi hệ thống bật xét duyệt ngoại lệ.
            </p>
        </div>
    @else
        @if ($errors->any())
            <div class="mt-6 rounded-lg border border-red-100 bg-red-50 p-4 text-sm text-red-700">
                <p class="font-semibold">Chưa gửi được đăng ký.</p>
                <ul class="mt-2 list-disc space-y-1 pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('mentor.apply.store') }}" class="mt-6 space-y-4 rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
            @csrf
            <label class="block">
                <span class="text-xs font-semibold uppercase tracking-wide text-slate-500">Vai trò đăng ký</span>
                <select name="requested_role_context" class="mt-1 w-full rounded-lg border-slate-200 text-sm">
                    @foreach ($eligibleRoleContexts as $value => $label)
                        <option value="{{ $value }}" @selected(old('requested_role_context', array_key_first($eligibleRoleContexts)) === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </label>

            <label class="block">
                <span class="text-xs font-semibold uppercase tracking-wide text-slate-500">Lý do đăng ký</span>
                <textarea name="motivation" required rows="4" placeholder="Vì sao bạn muốn trở thành mentor?" class="mt-1 w-full rounded-lg border-slate-200 text-sm">{{ old('motivation') }}</textarea>
            </label>

            <label class="block">
                <span class="text-xs font-semibold uppercase tracking-wide text-slate-500">Kinh nghiệm liên quan</span>
                <textarea name="experience_summary" rows="4" placeholder="Tóm tắt kinh nghiệm liên quan" class="mt-1 w-full rounded-lg border-slate-200 text-sm">{{ old('experience_summary') }}</textarea>
            </label>

            <label class="block">
                <span class="text-xs font-semibold uppercase tracking-wide text-slate-500">Chủ đề chuyên môn</span>
                <input name="expertise_topics[]" value="{{ old('expertise_topics.0') }}" placeholder="Ví dụ: CV, thực tập, nghiên cứu" class="mt-1 w-full rounded-lg border-slate-200 text-sm">
            </label>

            <label class="block">
                <span class="text-xs font-semibold uppercase tracking-wide text-slate-500">Lộ trình nghề nghiệp</span>
                <input name="career_paths[]" value="{{ old('career_paths.0') }}" placeholder="Ví dụ: EdTech, HR, Data" class="mt-1 w-full rounded-lg border-slate-200 text-sm">
            </label>

            <button class="rounded-lg bg-ue-brand px-4 py-2 text-sm font-semibold text-white hover:bg-ue-brand-dark">Gửi đăng ký</button>
        </form>
    @endif
</div>
