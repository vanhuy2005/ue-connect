<?php

use App\Enums\MentorAccessStatus;
use App\Enums\MentorAvailabilityStatus;
use App\Actions\Media\AttachMediaToModelAction;
use App\Actions\Media\DeleteMediaAction;
use App\Actions\Media\StoreTemporaryMediaAction;
use App\Models\MentorAccessRequest;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;

new class extends Component {
    use WithFileUploads;

    public $avatarFile;

    public ?string $avatarUploadMessage = null;

    public function updatedAvatarFile(): void
    {
        $maxAvatarKb = (int) config('media.limits.avatar_mb', 5) * 1024;

        $this->validate([
            'avatarFile' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:'.$maxAvatarKb],
        ]);

        $user = Auth::user();
        $profile = $user->profile;

        if (! $profile) {
            $this->addError('avatarFile', 'Bạn cần hoàn tất hồ sơ cá nhân trước khi tải ảnh đại diện.');

            return;
        }

        try {
            $deleteAction = app(DeleteMediaAction::class);
            $storeAction = app(StoreTemporaryMediaAction::class);
            $attachAction = app(AttachMediaToModelAction::class);

            $oldAvatar = $profile->avatar()->first();
            if ($oldAvatar) {
                $deleteAction->execute($oldAvatar);
            }

            $media = $storeAction->execute($user, $this->avatarFile, 'avatar', ['visibility' => 'public']);
            $attachAction->execute($user, $profile, [$media->id], 'avatar');

            $this->reset('avatarFile');
            $this->avatarUploadMessage = 'Ảnh đại diện đã được cập nhật. Bạn có thể lưu hồ sơ mentor.';
        } catch (Throwable $exception) {
            $this->addError('avatarFile', 'Không tải được ảnh đại diện: '.$exception->getMessage());
        }
    }

    public function with(): array
    {
        $user = Auth::user();
        $profile = $user->profile;
        $hasTrustedAvatar = (bool) ($profile?->avatar()->where('status', 'ready')->exists() || $profile?->avatar_media_file_id);

        return [
            'profile' => \App\Models\MentorProfile::where('user_id', $user->id)->first(),
            'latestRequest' => MentorAccessRequest::query()
                ->where('user_id', $user->id)
                ->latest()
                ->first(),
            'currentUser' => $user,
            'hasTrustedAvatar' => $hasTrustedAvatar,
        ];
    }
};
?>

@php
    $preferredRequestOptions = [
        'cv_review' => 'Review CV / Portfolio',
        'career_advice' => 'Định hướng nghề nghiệp',
        'academic_guidance' => 'Định hướng học thuật',
        'subject_support' => 'Hỗ trợ môn học',
        'research_guidance' => 'Nghiên cứu khoa học',
        'interview_prep' => 'Chuẩn bị phỏng vấn',
        'internship_experience' => 'Kinh nghiệm thực tập',
        'other' => 'Khác',
    ];

    $selectedRequestTypes = collect(old('preferred_request_types', $profile?->preferred_request_types ?? []))->values()->all();
    $customPreferredRequestVal = old('custom_preferred_request', '');
    if (empty($customPreferredRequestVal)) {
        foreach ($selectedRequestTypes as $key => $type) {
            if (! isset($preferredRequestOptions[$type]) && $type !== 'other') {
                $customPreferredRequestVal = $type;
                $selectedRequestTypes[$key] = 'other';
            }
        }
    }
    $selectedRequestTypes = array_values(array_unique($selectedRequestTypes));

    $availabilityValue = old('availability_status', $profile?->availability_status?->value ?? MentorAvailabilityStatus::Available->value);
    $visibilityValue = (bool) old('mentor_visibility', $profile?->mentor_visibility ?? true);
    $isDiscoverable = $profile
        && $profile->is_active
        && $hasTrustedAvatar
        && $visibilityValue
        && $availabilityValue === MentorAvailabilityStatus::Available->value;
@endphp

<div class="mx-auto max-w-6xl px-4 py-6 sm:px-6 lg:px-8"
     x-data="{
         headline: @js(old('headline', $profile?->headline)),
         bio: @js(old('bio', $profile?->bio)),
         expertise_topics_text: @js(old('expertise_topics_text', implode(', ', $profile?->expertise_topics ?? []))),
         availability_status: @js($availabilityValue),
         max_pending_requests: @js(old('max_pending_requests', $profile?->max_pending_requests)),
         response_expectation_text: @js(old('response_expectation_text', $profile?->response_expectation_text)),
         office_hours_text: @js(old('office_hours_text', $profile?->office_hours_text)),
         preferred_request_types: @js($selectedRequestTypes),
         custom_preferred_request: @js($customPreferredRequestVal)
     }">
    <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <a href="{{ route('mentor.discovery') }}" class="text-xs font-bold text-ue-brand hover:underline">← Quay lại danh sách mentor</a>
            <h1 class="mt-3 text-2xl font-bold text-slate-950">Thiết lập hồ sơ mentor</h1>
            <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-500">
                Đây là hồ sơ học sinh/sinh viên sẽ nhìn thấy trước khi gửi yêu cầu cố vấn. Hãy viết rõ bạn là ai, có thể hỗ trợ gì và kỳ vọng phản hồi ra sao.
            </p>
        </div>

        <div class="rounded-2xl border px-4 py-3 text-sm font-semibold {{ $isDiscoverable ? 'border-emerald-200 bg-emerald-50 text-emerald-800' : 'border-amber-200 bg-amber-50 text-amber-900' }}">
            @if ($isDiscoverable)
                Hồ sơ đang hiển thị trong danh sách mentor
            @elseif ($profile)
                @if (! $profile->is_public_ready)
                    <div class="space-y-1">
                        <p class="font-bold">Hồ sơ ẩn (Thiếu thông tin bắt buộc):</p>
                        <ul class="list-disc pl-5 text-xs font-normal space-y-1">
                            @if (! $hasTrustedAvatar) <li>Thiếu ảnh đại diện rõ mặt.</li> @endif
                            @if (empty($profile->headline)) <li>Thiếu Headline giới thiệu.</li> @endif
                            @if (empty($profile->bio)) <li>Thiếu phần Giới thiệu chi tiết (Bio).</li> @endif
                            @if (!is_array($profile->expertise_topics) || count($profile->expertise_topics) < 2) <li>Cần ít nhất 2 chủ đề chuyên môn.</li> @endif
                            @if (!is_array($profile->preferred_request_types) || count($profile->preferred_request_types) < 1) <li>Chọn ít nhất 1 loại yêu cầu hỗ trợ.</li> @endif
                            @if (empty($profile->response_expectation_text)) <li>Thiếu thời gian phản hồi dự kiến.</li> @endif
                        </ul>
                    </div>
                @else
                    Hồ sơ chưa public: kiểm tra trạng thái hoạt động, ẩn/hiện hoặc tình trạng nhận yêu cầu.
                @endif
            @else
                Bạn chưa có hồ sơ mentor được duyệt
            @endif
        </div>
    </div>

    @if (session('status'))
        <div class="mb-5 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">
            {{ session('status') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-5 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
            <p class="font-bold">Chưa lưu được hồ sơ mentor.</p>
            <ul class="mt-2 list-disc space-y-1 pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if (! $profile)
        <div class="grid gap-5 lg:grid-cols-[1.2fr_0.8fr]">
            <section class="rounded-2xl border border-amber-200 bg-amber-50 p-6">
                <h2 class="text-lg font-bold text-amber-950">Bạn chưa thể thiết lập hồ sơ mentor</h2>
                <p class="mt-2 text-sm leading-6 text-amber-900">
                    Trang này chỉ mở sau khi ban quản trị duyệt quyền mentor. Nếu bạn vừa gửi đăng ký, hãy chờ xét duyệt; nếu chưa đăng ký, bắt đầu bằng form đăng ký mentor.
                </p>

                @if ($latestRequest)
                    <div class="mt-4 rounded-xl border border-amber-200 bg-white/70 p-4 text-sm text-amber-900">
                        <p class="font-bold">Yêu cầu gần nhất: {{ $latestRequest->status->label() }}</p>
                        @if ($latestRequest->status === MentorAccessStatus::NeedMoreInfo && $latestRequest->review_reason)
                            <p class="mt-1">{{ $latestRequest->review_reason }}</p>
                        @endif
                    </div>
                @endif

                <div class="mt-5 flex flex-wrap gap-3">
                    <a href="{{ route('mentor.apply') }}" class="inline-flex items-center justify-center rounded-xl bg-ue-brand px-4 py-2 text-sm font-bold text-white hover:bg-ue-brand-dark">
                        Đăng ký làm mentor
                    </a>
                    <a href="{{ route('mentor.dashboard') }}" class="inline-flex items-center justify-center rounded-xl border border-amber-200 bg-white px-4 py-2 text-sm font-bold text-amber-900 hover:bg-amber-100">
                        Xem mentor dashboard
                    </a>
                </div>
            </section>

            <aside class="rounded-2xl border border-slate-200 bg-white p-5">
                <h2 class="text-sm font-bold text-slate-900">Mentor cần có gì?</h2>
                <div class="mt-4 space-y-3 text-sm text-slate-600">
                    <p>Ảnh đại diện/hồ sơ rõ ràng để người học tin tưởng.</p>
                    <p>Headline cụ thể, ví dụ: “Frontend mentor hỗ trợ CV và portfolio”.</p>
                    <p>Ít nhất 2 chủ đề chuyên môn và 2 nội dung có thể hỗ trợ.</p>
                    <p>Kỳ vọng phản hồi rõ ràng để tránh hiểu nhầm.</p>
                </div>
            </aside>
        </div>
    @else
        <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_360px]">
            <form method="POST" action="{{ route('mentor.setup.update') }}" class="space-y-5">
                @csrf
                @method('PATCH')

                <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex items-start gap-4">
                        <x-ui.avatar :user="$currentUser" size="lg" />
                        <div class="min-w-0">
                            <h2 class="text-base font-bold text-slate-950">Danh tính hiển thị</h2>
                            <p class="mt-1 text-sm text-slate-500">{{ $currentUser->name }}</p>
                            <p class="mt-2 text-xs leading-5 text-slate-500">
                                Ảnh đại diện là bắt buộc với mentor để người học biết họ đang gửi yêu cầu cho ai. Hãy dùng ảnh rõ mặt, không dùng logo hoặc ảnh quá mờ.
                            </p>
                        </div>
                    </div>

                    <div class="mt-5 rounded-2xl border p-4 {{ $hasTrustedAvatar ? 'border-emerald-200 bg-emerald-50' : 'border-red-200 bg-red-50' }}">
                        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <p class="text-sm font-bold {{ $hasTrustedAvatar ? 'text-emerald-900' : 'text-red-900' }}">
                                    {{ $hasTrustedAvatar ? 'Đã có ảnh đại diện tin cậy' : 'Bạn cần tải ảnh đại diện trước khi lưu' }}
                                </p>
                                <p class="mt-1 text-xs leading-5 {{ $hasTrustedAvatar ? 'text-emerald-700' : 'text-red-700' }}">
                                    Chấp nhận JPG, PNG, WEBP. Dung lượng tối đa {{ config('media.limits.avatar_mb', 5) }}MB.
                                </p>
                                @if ($avatarUploadMessage)
                                    <p class="mt-2 text-xs font-semibold text-emerald-700">{{ $avatarUploadMessage }}</p>
                                @endif
                                @error('avatarFile')
                                    <p class="mt-2 text-xs font-semibold text-red-700">{{ $message }}</p>
                                @enderror
                                @error('avatar')
                                    <p class="mt-2 text-xs font-semibold text-red-700">{{ $message }}</p>
                                @enderror
                            </div>

                            <label class="inline-flex cursor-pointer items-center justify-center rounded-xl px-4 py-2 text-sm font-bold shadow-sm transition {{ $hasTrustedAvatar ? 'bg-white text-emerald-800 ring-1 ring-emerald-200 hover:bg-emerald-100' : 'bg-red-600 text-white hover:bg-red-700' }}">
                                <x-ui.icon name="camera" size="xs" class="mr-2" />
                                {{ $hasTrustedAvatar ? 'Thay ảnh đại diện' : 'Tải ảnh đại diện' }}
                                <input type="file" wire:model="avatarFile" class="hidden" accept="image/jpeg,image/png,image/webp">
                            </label>
                        </div>

                        <div wire:loading wire:target="avatarFile" class="mt-3 text-xs font-semibold text-slate-500">
                            Đang tải ảnh lên và tạo phiên bản hiển thị...
                        </div>
                    </div>
                </section>

                <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <h2 class="text-base font-bold text-slate-950">Hồ sơ mentor công khai</h2>
                    <p class="mt-1 text-sm text-slate-500">Những nội dung này xuất hiện ở danh sách mentor và trang chi tiết mentor.</p>

                    <div class="mt-5 space-y-4">
                        <label class="block">
                            <span class="text-sm font-bold text-slate-700">Headline</span>
                            <input
                                name="headline"
                                value="{{ old('headline', $profile->headline) }}"
                                required
                                maxlength="160"
                                placeholder="Ví dụ: Frontend mentor hỗ trợ CV, portfolio và thực tập"
                                class="mt-2 w-full rounded-xl border-slate-200 text-sm focus:border-ue-brand focus:ring-ue-brand/20"
                                x-model="headline"
                            >
                            <span class="mt-1 block text-xs text-slate-400">Một câu cụ thể giúp người học hiểu ngay bạn phù hợp với nhu cầu nào.</span>
                        </label>

                        <label class="block">
                            <span class="text-sm font-bold text-slate-700">Giới thiệu mentor</span>
                            <textarea
                                name="bio"
                                required
                                rows="5"
                                maxlength="5000"
                                placeholder="Bạn là ai, đã có trải nghiệm gì, và thường hỗ trợ người học theo cách nào?"
                                class="mt-2 w-full rounded-xl border-slate-200 text-sm focus:border-ue-brand focus:ring-ue-brand/20"
                                x-model="bio"
                            >{{ old('bio', $profile->bio) }}</textarea>
                            <span class="mt-1 block text-xs text-slate-400">Nên viết 2-5 câu, tránh quá chung chung như “mình sẵn sàng hỗ trợ”.</span>
                        </label>

                        <label class="block">
                            <span class="text-sm font-bold text-slate-700">Chủ đề chuyên môn</span>
                            <textarea
                                name="expertise_topics_text"
                                required
                                rows="3"
                                placeholder="Laravel, React, CV Review, IELTS"
                                class="mt-2 w-full rounded-xl border-slate-200 text-sm focus:border-ue-brand focus:ring-ue-brand/20"
                                x-model="expertise_topics_text"
                            >{{ old('expertise_topics_text', implode(', ', $profile->expertise_topics ?? [])) }}</textarea>
                            <span class="mt-1 block text-xs text-slate-400">Tối thiểu 2 mục, ngăn cách bằng dấu phẩy hoặc xuống dòng.</span>
                        </label>


                    </div>
                </section>

                <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <h2 class="text-base font-bold text-slate-950">Phạm vi hỗ trợ</h2>
                    <p class="mt-1 text-sm text-slate-500">Chọn các loại yêu cầu bạn thật sự muốn nhận để tránh người học gửi nhầm kỳ vọng.</p>

                    <div class="mt-4 grid gap-2 sm:grid-cols-2">
                        @foreach ($preferredRequestOptions as $value => $label)
                            @if ($value === 'other')
                                <label class="flex items-center gap-2 rounded-xl border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-700 hover:border-ue-brand/40 hover:bg-ue-brand-soft/30">
                                    <input
                                        type="checkbox"
                                        name="preferred_request_types[]"
                                        value="other"
                                        @checked(in_array('other', $selectedRequestTypes, true))
                                        class="rounded border-slate-300 text-ue-brand focus:ring-ue-brand/30"
                                        x-model="preferred_request_types"
                                    >
                                    <span x-show="!preferred_request_types.includes('other')">Khác</span>
                                    <input
                                        x-show="preferred_request_types.includes('other')"
                                        type="text"
                                        name="custom_preferred_request"
                                        x-model="custom_preferred_request"
                                        placeholder="Nhập yêu cầu khác..."
                                        class="h-7 w-full border-0 border-b border-slate-300 p-0 text-sm focus:border-ue-brand focus:ring-0 bg-transparent"
                                        onclick="event.stopPropagation()"
                                    >
                                </label>
                            @else
                                <label class="flex items-center gap-2 rounded-xl border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-700 hover:border-ue-brand/40 hover:bg-ue-brand-soft/30">
                                    <input
                                        type="checkbox"
                                        name="preferred_request_types[]"
                                        value="{{ $value }}"
                                        @checked(in_array($value, $selectedRequestTypes, true))
                                        class="rounded border-slate-300 text-ue-brand focus:ring-ue-brand/30"
                                        x-model="preferred_request_types"
                                    >
                                    {{ $label }}
                                </label>
                            @endif
                        @endforeach
                    </div>
                </section>

                <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <h2 class="text-base font-bold text-slate-950">Trạng thái nhận yêu cầu</h2>
                    <div class="mt-4 grid gap-4 md:grid-cols-2">
                        <label class="block">
                            <span class="text-sm font-bold text-slate-700">Tình trạng hiện tại</span>
                            <select name="availability_status" class="mt-2 w-full rounded-xl border-slate-200 text-sm focus:border-ue-brand focus:ring-ue-brand/20" x-model="availability_status">
                                @foreach (MentorAvailabilityStatus::cases() as $status)
                                    <option value="{{ $status->value }}" @selected($availabilityValue === $status->value)>{{ $status->label() }}</option>
                                @endforeach
                            </select>
                        </label>

                        <label class="block">
                            <span class="text-sm font-bold text-slate-700">Số yêu cầu đang chờ tối đa</span>
                            <input
                                type="number"
                                min="1"
                                max="50"
                                name="max_pending_requests"
                                value="{{ old('max_pending_requests', $profile->max_pending_requests) }}"
                                class="mt-2 w-full rounded-xl border-slate-200 text-sm focus:border-ue-brand focus:ring-ue-brand/20"
                                x-model="max_pending_requests"
                            >
                        </label>
                    </div>

                    <div class="mt-4 grid gap-4 md:grid-cols-2">
                        <label class="block">
                            <span class="text-sm font-bold text-slate-700">Thời gian phản hồi dự kiến</span>
                            <input
                                name="response_expectation_text"
                                value="{{ old('response_expectation_text', $profile->response_expectation_text) }}"
                                required
                                maxlength="255"
                                placeholder="Ví dụ: Phản hồi trong 2-3 ngày làm việc"
                                class="mt-2 w-full rounded-xl border-slate-200 text-sm focus:border-ue-brand focus:ring-ue-brand/20"
                                x-model="response_expectation_text"
                            >
                        </label>

                        <label class="block">
                            <span class="text-sm font-bold text-slate-700">Khung giờ hỗ trợ</span>
                            <input
                                name="office_hours_text"
                                value="{{ old('office_hours_text', $profile->office_hours_text) }}"
                                maxlength="255"
                                placeholder="Ví dụ: Tối thứ 3 và thứ 5"
                                class="mt-2 w-full rounded-xl border-slate-200 text-sm focus:border-ue-brand focus:ring-ue-brand/20"
                                x-model="office_hours_text"
                            >
                        </label>
                    </div>

                    <input type="hidden" name="mentor_visibility" value="0">
                    <label class="mt-4 flex items-start gap-3 rounded-xl border border-slate-200 bg-slate-50 px-3 py-3 text-sm font-semibold text-slate-700">
                        <input
                            type="checkbox"
                            name="mentor_visibility"
                            value="1"
                            @checked($visibilityValue)
                            class="mt-0.5 rounded border-slate-300 text-ue-brand focus:ring-ue-brand/30"
                        >
                        <span>
                            Hiển thị trong danh sách mentor
                            <span class="mt-1 block text-xs font-medium text-slate-400">Nếu tắt, người học sẽ không thấy bạn ở trang khám phá mentor, nhưng bạn vẫn có thể chỉnh hồ sơ.</span>
                        </span>
                    </label>
                </section>

                <div class="sticky bottom-0 -mx-4 border-t border-slate-200 bg-white/90 px-4 py-3 backdrop-blur sm:static sm:mx-0 sm:border-0 sm:bg-transparent sm:px-0 sm:py-0">
                    @unless ($hasTrustedAvatar)
                        <p class="mb-3 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-xs font-semibold text-red-700">
                            Bạn cần tải ảnh đại diện trước khi lưu hồ sơ mentor.
                        </p>
                    @endunless
                    <button class="inline-flex w-full items-center justify-center rounded-xl bg-ue-brand px-5 py-3 text-sm font-bold text-white shadow-sm hover:bg-ue-brand-dark disabled:cursor-not-allowed disabled:bg-slate-300 sm:w-auto" @disabled(! $hasTrustedAvatar)>
                        Lưu hồ sơ mentor
                    </button>
                </div>
            </form>

                            <aside class="space-y-5 lg:sticky lg:top-6 self-start">
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <h3 class="text-xs font-bold uppercase tracking-wide text-slate-400 mb-3.5">Preview thẻ hồ sơ mentor</h3>

                    <div class="ue-loadable-card bg-white border border-slate-200 rounded-2xl p-4 flex flex-col justify-between shadow-2xs">
                        <div>
                            {{-- Identity info --}}
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex items-center gap-3">
                                    <div class="relative">
                                        <x-ui.avatar :user="$currentUser" size="md" class="border border-slate-100" />
                                    </div>
                                    <div class="min-w-0">
                                        <span class="text-xs font-bold text-slate-800 flex items-center gap-1 leading-snug">
                                            {{ $currentUser->name }}
                                            <x-ui.icon name="shield-check" size="xs" class="text-ue-brand fill-ue-brand" />
                                        </span>
                                        <p class="text-[9px] text-slate-400 font-semibold tracking-wide uppercase mt-0.5">
                                            @if ($currentUser->profile?->role_type === 'alumni') Cựu sinh viên
                                            @elseif ($currentUser->profile?->role_type === 'teacher') Giảng viên
                                            @elseif ($currentUser->profile?->role_type === 'exceptional_student') Sinh viên nổi bật
                                            @else Mentor
                                            @endif
                                        </p>
                                        <div class="mt-1 flex items-center gap-1.5">
                                            <span class="inline-block w-1.5 h-1.5 rounded-full" :class="availability_status === 'available' ? 'bg-emerald-500' : 'bg-slate-400'"></span>
                                            <span class="text-[9px] font-semibold" :class="availability_status === 'available' ? 'text-emerald-700' : 'text-slate-400'" x-text="{
                                                available: 'Đang nhận yêu cầu',
                                                paused: 'Tạm ngưng nhận',
                                                full: 'Đầy lượt',
                                                hidden: 'Ẩn hồ sơ'
                                            }[availability_status] || availability_status"></span>
                                        </div>
                                    </div>
                                </div>

                                @if ($currentUser->profile?->faculty)
                                    <span class="bg-slate-50 text-[9px] font-bold text-slate-500 px-2 py-0.5 rounded-md border border-slate-100 leading-none">
                                        {{ $currentUser->profile->faculty }}
                                    </span>
                                @endif
                            </div>

                            {{-- Headline --}}
                            <h4 class="mt-3.5 text-xs font-bold text-slate-850 line-clamp-2 leading-snug" x-text="headline || 'Headline giới thiệu ngắn về bạn...'"></h4>

                            {{-- Bio --}}
                            <p class="text-xxs text-slate-500 font-medium leading-relaxed mt-2 line-clamp-3" x-text="bio || 'Viết một chút giới thiệu về bạn để học viên biết vì sao nên chọn kết nối.'"></p>

                            {{-- Expertise Topics --}}
                            <div class="mt-3.5">
                                <span class="text-[9px] font-bold text-slate-400 block mb-1">Chuyên môn</span>
                                <div class="flex flex-wrap gap-1.5" x-html="
                                    expertise_topics_text.split(/[\r\n,]+/)
                                        .map(t => t.trim())
                                        .filter(t => t !== '')
                                        .slice(0, 8)
                                        .map(t => `<span class='bg-slate-50 text-[9px] font-semibold text-slate-600 px-2 py-0.5 rounded border border-slate-100 leading-none'>${t}</span>`)
                                        .join('') || `<span class='text-xxs text-slate-350 italic'>Chưa nhập chủ đề chuyên môn</span>`
                                "></div>
                            </div>

                            {{-- Preferred Request Types (Phạm vi hỗ trợ) --}}
                            <div class="mt-3" x-show="preferred_request_types.length > 0">
                                <span class="text-[9px] font-bold text-slate-400 block mb-1">Phạm vi hỗ trợ</span>
                                <div class="flex flex-wrap gap-1">
                                    <template x-for="type in preferred_request_types" :key="type">
                                        <span class="bg-blue-50 text-[9px] font-bold text-blue-700 px-2 py-0.5 rounded-md border border-blue-100 leading-none"
                                              x-text="type === 'other' ? (custom_preferred_request || 'Khác') : ({
                                                  cv_review: 'Review CV / Portfolio',
                                                  career_advice: 'Định hướng nghề nghiệp',
                                                  academic_guidance: 'Định hướng học thuật',
                                                  subject_support: 'Hỗ trợ môn học',
                                                  research_guidance: 'Nghiên cứu khoa học',
                                                  interview_prep: 'Chuẩn bị phỏng vấn',
                                                  internship_experience: 'Kinh nghiệm thực tập',
                                                  other: 'Khác'
                                              }[type] || type)">
                                        </span>
                                    </template>
                                </div>
                            </div>
                        </div>

                        {{-- Footer / Meta info --}}
                        <div class="mt-4 pt-3 border-t border-slate-100 flex flex-col gap-0.5 text-[10px] text-slate-400">
                            <span><span class="font-bold text-slate-600">Phản hồi:</span> <span x-text="response_expectation_text || 'N/A'"></span></span>
                            <span x-show="office_hours_text"><span class="font-bold text-slate-600">Lịch hỗ trợ:</span> <span x-text="office_hours_text"></span></span>
                            <span><span class="font-bold text-slate-600">Số yêu cầu đang chờ tối đa:</span> <span x-text="max_pending_requests"></span></span>
                        </div>
                    </div>

                    <div class="mt-4 space-y-2 text-xs leading-5 text-slate-500">
                        <p class="font-bold text-slate-700">Checklist trước khi public</p>
                        <p>• Headline cụ thể, không chung chung.</p>
                        <p>• Có ít nhất 2 chuyên môn và 2 nội dung hỗ trợ.</p>
                        <p>• Trạng thái là “Đang nhận yêu cầu” và bật hiển thị.</p>
                    </div>
                </div>
            </aside>
        </div>
    @endif
</div>
