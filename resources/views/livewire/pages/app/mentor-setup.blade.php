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
            'profile' => $user->mentorProfile()->first(),
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

    $selectedRequestTypes = old('preferred_request_types', $profile?->preferred_request_types ?? []);
    $availabilityValue = old('availability_status', $profile?->availability_status?->value ?? MentorAvailabilityStatus::Available->value);
    $visibilityValue = (bool) old('mentor_visibility', $profile?->mentor_visibility ?? true);
    $isDiscoverable = $profile
        && $profile->is_active
        && $hasTrustedAvatar
        && $visibilityValue
        && $availabilityValue === MentorAvailabilityStatus::Available->value;
@endphp

<div class="mx-auto max-w-6xl px-4 py-6 sm:px-6 lg:px-8">
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
                Hồ sơ chưa public: kiểm tra trạng thái, visibility hoặc quyền mentor
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
                            >{{ old('bio', $profile->bio) }}</textarea>
                            <span class="mt-1 block text-xs text-slate-400">Nên viết 2-5 câu, tránh quá chung chung như “mình sẵn sàng hỗ trợ”.</span>
                        </label>

                        <div class="grid gap-4 md:grid-cols-2">
                            <label class="block">
                                <span class="text-sm font-bold text-slate-700">Chủ đề chuyên môn</span>
                                <textarea
                                    name="expertise_topics_text"
                                    required
                                    rows="3"
                                    placeholder="Laravel, React, CV Review, IELTS"
                                    class="mt-2 w-full rounded-xl border-slate-200 text-sm focus:border-ue-brand focus:ring-ue-brand/20"
                                >{{ old('expertise_topics_text', implode(', ', $profile->expertise_topics ?? [])) }}</textarea>
                                <span class="mt-1 block text-xs text-slate-400">Tối thiểu 2 mục, ngăn cách bằng dấu phẩy hoặc xuống dòng.</span>
                            </label>

                            <label class="block">
                                <span class="text-sm font-bold text-slate-700">Bạn có thể hỗ trợ điều gì?</span>
                                <textarea
                                    name="help_topics_text"
                                    required
                                    rows="3"
                                    placeholder="Review CV, định hướng thực tập, chọn đề tài NCKH"
                                    class="mt-2 w-full rounded-xl border-slate-200 text-sm focus:border-ue-brand focus:ring-ue-brand/20"
                                >{{ old('help_topics_text', implode(', ', $profile->help_topics ?? [])) }}</textarea>
                                <span class="mt-1 block text-xs text-slate-400">Nói theo nhu cầu của người học, không chỉ theo kỹ năng của bạn.</span>
                            </label>
                        </div>

                        <div class="grid gap-4 md:grid-cols-2">
                            <label class="block">
                                <span class="text-sm font-bold text-slate-700">Lộ trình nghề nghiệp / học thuật</span>
                                <input
                                    name="career_paths_text"
                                    value="{{ old('career_paths_text', implode(', ', $profile->career_paths ?? [])) }}"
                                    placeholder="Software Engineering, Teaching, Research"
                                    class="mt-2 w-full rounded-xl border-slate-200 text-sm focus:border-ue-brand focus:ring-ue-brand/20"
                                >
                            </label>

                            <label class="block">
                                <span class="text-sm font-bold text-slate-700">Kỹ năng liên quan</span>
                                <input
                                    name="skills_text"
                                    value="{{ old('skills_text', implode(', ', $profile->skills ?? [])) }}"
                                    placeholder="Laravel, Figma, Data Analysis"
                                    class="mt-2 w-full rounded-xl border-slate-200 text-sm focus:border-ue-brand focus:ring-ue-brand/20"
                                >
                            </label>
                        </div>
                    </div>
                </section>

                <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <h2 class="text-base font-bold text-slate-950">Phạm vi hỗ trợ</h2>
                    <p class="mt-1 text-sm text-slate-500">Chọn các loại yêu cầu bạn thật sự muốn nhận để tránh người học gửi nhầm kỳ vọng.</p>

                    <div class="mt-4 grid gap-2 sm:grid-cols-2">
                        @foreach ($preferredRequestOptions as $value => $label)
                            <label class="flex items-center gap-2 rounded-xl border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-700 hover:border-ue-brand/40 hover:bg-ue-brand-soft/30">
                                <input
                                    type="checkbox"
                                    name="preferred_request_types[]"
                                    value="{{ $value }}"
                                    @checked(in_array($value, $selectedRequestTypes, true))
                                    class="rounded border-slate-300 text-ue-brand focus:ring-ue-brand/30"
                                >
                                {{ $label }}
                            </label>
                        @endforeach
                    </div>
                </section>

                <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <h2 class="text-base font-bold text-slate-950">Trạng thái nhận yêu cầu</h2>
                    <div class="mt-4 grid gap-4 md:grid-cols-2">
                        <label class="block">
                            <span class="text-sm font-bold text-slate-700">Tình trạng hiện tại</span>
                            <select name="availability_status" class="mt-2 w-full rounded-xl border-slate-200 text-sm focus:border-ue-brand focus:ring-ue-brand/20">
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

            <aside class="space-y-5">
                <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm lg:sticky lg:top-6">
                    <div class="flex items-center gap-3">
                        <x-ui.avatar :user="$currentUser" size="md" />
                        <div class="min-w-0">
                            <p class="truncate text-sm font-bold text-slate-950">{{ $currentUser->name }}</p>
                            <p class="text-xs font-semibold text-emerald-700">{{ MentorAvailabilityStatus::from($availabilityValue)->label() }}</p>
                        </div>
                    </div>

                    <div class="mt-4 rounded-xl border border-slate-200 bg-slate-50 p-4">
                        <p class="text-xs font-bold uppercase tracking-wide text-slate-400">Preview hồ sơ</p>
                        <h3 class="mt-3 text-base font-bold text-slate-950 break-words line-clamp-2">{{ old('headline', $profile->headline) ?: 'Headline mentor sẽ hiển thị ở đây' }}</h3>
                        <p class="mt-2 line-clamp-4 text-sm leading-6 text-slate-600 break-words">{{ old('bio', $profile->bio) ?: 'Phần giới thiệu mentor giúp người học hiểu bạn là ai và nên gửi yêu cầu gì.' }}</p>

                        <div class="mt-4 flex flex-wrap gap-2">
                            @foreach (array_slice(preg_split('/[\r\n,]+/', old('expertise_topics_text', implode(', ', $profile->expertise_topics ?? []))), 0, 5) as $topic)
                                @if (trim($topic) !== '')
                                    <span class="max-w-full truncate rounded-full bg-white px-2.5 py-1 text-xs font-semibold text-slate-600 ring-1 ring-slate-200" title="{{ trim($topic) }}">{{ trim($topic) }}</span>
                                @endif
                            @endforeach
                        </div>

                        <div class="mt-4 rounded-lg bg-white p-3 text-xs leading-5 text-slate-500 ring-1 ring-slate-200">
                            <p><span class="font-bold text-slate-700">Phản hồi:</span> {{ old('response_expectation_text', $profile->response_expectation_text) ?: 'Chưa thiết lập' }}</p>
                            <p class="mt-1"><span class="font-bold text-slate-700">Khung giờ:</span> {{ old('office_hours_text', $profile->office_hours_text) ?: 'Linh hoạt theo lịch hẹn' }}</p>
                        </div>
                    </div>

                    <div class="mt-4 space-y-2 text-xs leading-5 text-slate-500">
                        <p class="font-bold text-slate-700">Checklist trước khi public</p>
                        <p>• Headline cụ thể, không chung chung.</p>
                        <p>• Có ít nhất 2 chuyên môn và 2 nội dung hỗ trợ.</p>
                        <p>• Trạng thái là “Đang nhận yêu cầu” và bật hiển thị.</p>
                    </div>
                </section>
            </aside>
        </div>
    @endif
</div>
