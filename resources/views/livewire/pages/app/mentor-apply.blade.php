<?php

use App\Actions\Media\AttachMediaToModelAction;
use App\Actions\Media\DeleteMediaAction;
use App\Actions\Media\GenerateMediaUrlAction;
use App\Actions\Media\StoreTemporaryMediaAction;
use App\Actions\Mentor\RequestMentorAccessAction;
use App\Enums\MentorAccessStatus;
use App\Models\MentorAccessRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;

new class extends Component
{
    use WithFileUploads;

    // File uploads
    public $avatarFile;

    public $evidenceFile;

    // Form fields
    public string $requested_role_context = '';

    public string $motivation = '';

    public string $experience_summary = '';

    public string $headline = '';

    public string $bio = '';

    public string $portfolio_link = '';

    public string $availability_note = '';

    public bool $policy_agreed = false;

    public string $response_expectation_text = 'Thường phản hồi trong 2-3 ngày làm việc';

    public string $office_hours_text = '';

    // List fields (comma/newline separated inputs)
    public string $expertise_topics_text = '';

    public string $help_topics_text = '';

    public string $career_paths_text = '';

    public string $skills_text = '';

    // Checkboxes
    public array $preferred_request_types = ['cv_review', 'career_advice'];

    public string $custom_preferred_request = '';

    public ?int $evidenceMediaId = null;

    public ?string $evidenceFileName = null;

    public ?string $evidencePreviewUrl = null;

    public ?string $avatarUrl = null;

    public ?string $avatarUploadMessage = null;

    public ?int $existingRequestId = null;

    public function mount(): void
    {
        $user = Auth::user();
        $profileRecord = $user->profile;
        if ($profileRecord && $profileRecord->avatar()->exists()) {
            $this->avatarUrl = app(GenerateMediaUrlAction::class)->execute($profileRecord->avatar()->first(), 'thumb', $user);
        }

        $openRequest = MentorAccessRequest::where('user_id', $user->id)
            ->whereIn('status', [MentorAccessStatus::NeedMoreInfo->value])
            ->latest()
            ->first();

        if ($openRequest) {
            $this->existingRequestId = $openRequest->id;
            $this->requested_role_context = $openRequest->requested_role_context;
            $this->motivation = $openRequest->motivation;
            $this->experience_summary = $openRequest->experience_summary ?? '';
            $this->headline = $openRequest->headline ?? '';
            $this->bio = $openRequest->bio ?? '';
            $this->portfolio_link = $openRequest->portfolio_link ?? '';
            $this->availability_note = $openRequest->availability_note ?? '';
            $this->policy_agreed = $openRequest->policy_agreed;
            $this->response_expectation_text = $openRequest->response_expectation_text ?? 'Thường phản hồi trong 2-3 ngày làm việc';
            $this->office_hours_text = $openRequest->office_hours_text ?? '';
            $this->expertise_topics_text = is_array($openRequest->expertise_topics) ? implode(', ', $openRequest->expertise_topics) : '';
            $this->help_topics_text = is_array($openRequest->help_topics) ? implode(', ', $openRequest->help_topics) : '';
            $this->career_paths_text = is_array($openRequest->career_paths) ? implode(', ', $openRequest->career_paths) : '';
            $this->skills_text = is_array($openRequest->skills) ? implode(', ', $openRequest->skills) : '';
            $validTypes = ['cv_review', 'career_advice', 'academic_guidance', 'subject_support', 'research_guidance', 'interview_prep', 'internship_experience', 'other'];
            $types = $openRequest->preferred_request_types ?? ['cv_review', 'career_advice'];
            $this->preferred_request_types = [];
            foreach ($types as $type) {
                if (in_array($type, $validTypes, true)) {
                    $this->preferred_request_types[] = $type;
                } else {
                    $this->preferred_request_types[] = 'other';
                    $this->custom_preferred_request = $type;
                }
            }
            $this->preferred_request_types = array_unique($this->preferred_request_types);
            $this->evidenceMediaId = $openRequest->evidence_media_id;
        } else {
            $eligible = RequestMentorAccessAction::eligibleRoleContextsFor($user);
            if (! empty($eligible)) {
                $this->requested_role_context = old('requested_role_context', array_key_first($eligible));
            }
        }

        // Pre-fill bio from completed profile
        if ($profileRecord) {
            if ($this->bio === '' && $profileRecord->bio) {
                $this->bio = $profileRecord->bio;
            }
        }
    }

    public function messages(): array
    {
        return [
            'motivation.required' => 'Vui lòng nhập mục tiêu và động lực làm mentor.',
            'motivation.min' => 'Mục tiêu và động lực làm mentor phải có ít nhất :min ký tự.',
            'motivation.max' => 'Mục tiêu và động lực làm mentor không được vượt quá :max ký tự.',
            'headline.required' => 'Vui lòng nhập headline giới thiệu ngắn.',
            'headline.min' => 'Headline giới thiệu ngắn phải có ít nhất :min ký tự.',
            'headline.max' => 'Headline giới thiệu ngắn không được vượt quá :max ký tự.',
            'bio.required' => 'Vui lòng nhập giới thiệu chi tiết.',
            'bio.min' => 'Giới thiệu chi tiết phải có ít nhất :min ký tự.',
            'bio.max' => 'Giới thiệu chi tiết không được vượt quá :max ký tự.',
            'expertise_topics_text.required' => 'Vui lòng nhập chủ đề chuyên môn.',
            'preferred_request_types.required' => 'Vui lòng chọn ít nhất một loại yêu cầu sẵn sàng nhận.',
            'preferred_request_types.min' => 'Vui lòng chọn ít nhất một loại yêu cầu sẵn sàng nhận.',
            'response_expectation_text.required' => 'Vui lòng nhập thời gian phản hồi dự kiến.',
            'policy_agreed.required' => 'Bạn phải đồng ý với cam kết bảo mật.',
            'policy_agreed.accepted' => 'Bạn phải đồng ý với cam kết bảo mật.',
            'portfolio_link.url' => 'Link cá nhân / Portfolio phải là một đường dẫn URL hợp lệ.',
            'avatarFile.required' => 'Vui lòng tải lên ảnh đại diện rõ mặt.',
            'avatarFile.image' => 'Ảnh đại diện phải là một tệp hình ảnh.',
            'avatarFile.mimes' => 'Ảnh đại diện phải có định dạng là jpg, jpeg, png hoặc webp.',
            'avatarFile.max' => 'Kích thước ảnh đại diện không được vượt quá :max KB.',
            'evidenceFile.required' => 'Vui lòng tải lên tài liệu/minh chứng năng lực.',
            'evidenceFile.mimes' => 'Tài liệu minh chứng phải có định dạng jpg, jpeg, png, pdf, webp, docx hoặc zip.',
            'evidenceFile.max' => 'Kích thước tài liệu minh chứng không được vượt quá 10MB.',
        ];
    }

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
            $this->avatarUrl = app(GenerateMediaUrlAction::class)->execute($media, 'thumb', $user);
            $this->avatarUploadMessage = 'Đã cập nhật ảnh đại diện.';
        } catch (Throwable $exception) {
            $this->addError('avatarFile', 'Không tải được ảnh đại diện: '.$exception->getMessage());
        }
    }

    public function updatedEvidenceFile(): void
    {
        $this->validate([
            'evidenceFile' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf,webp,docx,zip', 'max:10240'],
        ]);

        try {
            $user = Auth::user();
            $storeAction = app(StoreTemporaryMediaAction::class);
            $media = $storeAction->execute($user, $this->evidenceFile, 'verification_evidence', ['visibility' => 'private']);
            $this->evidenceMediaId = $media->id;
            $this->evidenceFileName = $this->evidenceFile->getClientOriginalName();
            $this->evidencePreviewUrl = app(GenerateMediaUrlAction::class)->execute($media, 'preview', $user);
        } catch (Throwable $exception) {
            $this->addError('evidenceFile', 'Không tải được file minh chứng: '.$exception->getMessage());
        }
    }

    public function submit(RequestMentorAccessAction $action): void
    {
        $user = Auth::user();
        $eligible = RequestMentorAccessAction::eligibleRoleContextsFor($user);

        $rules = [
            'requested_role_context' => ['required', 'string', Rule::in(array_keys($eligible))],
            'motivation' => ['required', 'string', 'min:20', 'max:5000'],
            'experience_summary' => ['nullable', 'string', 'max:5000'],
            'headline' => ['required', 'string', 'min:12', 'max:160'],
            'bio' => ['required', 'string', 'min:40', 'max:5000'],
            'expertise_topics_text' => ['required', 'string', 'max:1000'],
            'help_topics_text' => ['nullable', 'string', 'max:1000'],
            'career_paths_text' => ['nullable', 'string', 'max:1000'],
            'skills_text' => ['nullable', 'string', 'max:1000'],
            'preferred_request_types' => ['required', 'array', 'min:1'],
            'preferred_request_types.*' => ['string', 'max:80'],
            'custom_preferred_request' => [
                Rule::requiredIf(in_array('other', $this->preferred_request_types, true)),
                'nullable',
                'string',
                'max:80',
            ],
            'response_expectation_text' => ['required', 'string', 'max:255'],
            'office_hours_text' => ['nullable', 'string', 'max:255'],
            'portfolio_link' => ['nullable', 'url', 'max:255'],
            'availability_note' => ['nullable', 'string', 'max:1000'],
            'policy_agreed' => ['required', 'accepted'],
        ];

        if (! ($user->profile && $user->profile->avatar()->exists())) {
            $this->addError('avatarFile', 'Vui lòng tải lên ảnh đại diện rõ mặt trước khi gửi đăng ký.');

            return;
        }

        if (! $this->evidenceMediaId) {
            $this->addError('evidenceFile', 'Vui lòng tải lên tài liệu/minh chứng năng lực trước khi gửi đăng ký.');

            return;
        }

        $validated = $this->validate($rules);

        $normalizeList = function (?string $value, int $limit): array {
            return collect(preg_split('/[\r\n,]+/', (string) $value))
                ->map(fn (string $item) => trim($item))
                ->filter()
                ->unique()
                ->take($limit)
                ->values()
                ->all();
        };

        $expertiseTopics = $normalizeList($this->expertise_topics_text, 10);
        $helpTopics = $normalizeList($this->help_topics_text, 8);
        $careerPaths = $normalizeList($this->career_paths_text, 8);
        $skills = $normalizeList($this->skills_text, 12);

        if (count($expertiseTopics) < 2) {
            $this->addError('expertise_topics_text', 'Vui lòng nhập ít nhất 2 chủ đề chuyên môn.');

            return;
        }

        $types = $validated['preferred_request_types'];
        if (in_array('other', $types, true) && ! empty($this->custom_preferred_request)) {
            $types = array_map(function ($type) {
                return $type === 'other' ? $this->custom_preferred_request : $type;
            }, $types);
        }

        $data = [
            'requested_role_context' => $validated['requested_role_context'],
            'motivation' => $validated['motivation'],
            'experience_summary' => $validated['experience_summary'],
            'headline' => $validated['headline'],
            'bio' => $validated['bio'],
            'expertise_topics' => $expertiseTopics,
            'help_topics' => $helpTopics,
            'career_paths' => $careerPaths,
            'skills' => $skills,
            'preferred_request_types' => $types,
            'response_expectation_text' => $validated['response_expectation_text'],
            'office_hours_text' => $validated['office_hours_text'],
            'portfolio_link' => $validated['portfolio_link'],
            'availability_note' => $validated['availability_note'],
            'policy_agreed' => $validated['policy_agreed'],
            'evidence_media_id' => $this->evidenceMediaId,
        ];

        try {
            $existing = $this->existingRequestId
                ? MentorAccessRequest::where('id', $this->existingRequestId)
                    ->where('user_id', $user->id)
                    ->where('status', MentorAccessStatus::NeedMoreInfo)
                    ->first()
                : null;

            if ($existing) {
                $existing->update(array_merge($data, [
                    'status' => MentorAccessStatus::Submitted,
                    'reviewed_by' => null,
                    'reviewed_at' => null,
                    'review_reason' => null,
                    'admin_notes' => null,
                ]));
                session()->flash('status', 'Yêu cầu mentor đã được cập nhật và gửi lại.');
            } else {
                $action->execute($user, $data);
                session()->flash('status', 'Yêu cầu trở thành mentor đã được gửi.');
            }
            $this->redirect(route('mentor.dashboard'));
        } catch (Exception $exception) {
            $this->addError('requested_role_context', $exception->getMessage());
        }
    }

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
            'currentUser' => $user,
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
@endphp

<div class="mx-auto max-w-6xl px-4 py-6 sm:px-6 lg:px-8">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-900">Đăng ký trở thành mentor</h1>
        <p class="mt-1 text-sm text-slate-500">Chia sẻ kinh nghiệm của bạn để ban quản trị xét duyệt quyền mentor.</p>
    </div>

    @if ($openRequest && $openRequest->status !== MentorAccessStatus::NeedMoreInfo)
        <div class="rounded-2xl border border-blue-100 bg-blue-50 p-5 text-sm text-blue-900 shadow-sm">
            <p class="font-bold text-base">Bạn đã có yêu cầu mentor: {{ $openRequest->status->label() }}</p>
            @if ($openRequest->status === MentorAccessStatus::Approved && $mentorProfile)
                <p class="mt-2 text-blue-800 leading-relaxed">Yêu cầu của bạn đã được duyệt. Hãy thiết lập hồ sơ mentor công khai để người học có thể tin tưởng khi gửi yêu cầu.</p>
                <div class="mt-4 flex flex-wrap gap-2">
                    <a href="{{ route('mentor.setup') }}" class="inline-flex items-center justify-center rounded-xl bg-ue-brand px-4 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-ue-brand-dark transition-all">
                        Thiết lập hồ sơ mentor
                    </a>
                    <a href="{{ route('mentor.dashboard') }}" class="inline-flex items-center justify-center rounded-xl border border-blue-200 bg-white px-4 py-2.5 text-sm font-bold text-blue-900 hover:bg-blue-100 transition-all">
                        Mentor dashboard
                    </a>
                </div>
            @else
                <p class="mt-2 text-blue-800 leading-relaxed">Ban quản trị đang xét duyệt yêu cầu của bạn.</p>
            @endif
        </div>
    @elseif (empty($eligibleRoleContexts))
        <div class="rounded-2xl border border-amber-100 bg-amber-50 p-5 text-sm text-amber-900 shadow-sm">
            <p class="font-bold text-base">Hồ sơ hiện tại chưa đủ điều kiện đăng ký mentor.</p>
            <p class="mt-2 text-amber-800 leading-relaxed">
                Hãy hoàn tất xác thực tài khoản và hồ sơ vai trò. Quyền làm Mentor hiện hỗ trợ cựu sinh viên, cố vấn/giảng viên và sinh viên nổi bật khi hệ thống cho phép xét duyệt ngoại lệ.
            </p>
        </div>
    @else
        @if ($openRequest && $openRequest->status === MentorAccessStatus::NeedMoreInfo)
            <div class="mb-6 rounded-2xl border border-amber-200 bg-amber-50 p-5 text-sm text-amber-900 shadow-sm">
                <p class="font-bold text-base">Yêu cầu cần bổ sung thông tin</p>
                @if ($openRequest->review_reason)
                    <p class="mt-2 font-medium">{{ $openRequest->review_reason }}</p>
                @endif
                <p class="mt-3 text-amber-800">Vui lòng cập nhật thông tin bên dưới và gửi lại.</p>
            </div>
        @endif
        <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_380px]">
            {{-- Left side - Interactive Registration Form --}}
            <form wire:submit.prevent="submit" x-data="{ step: 1 }" class="space-y-6">
                @if ($errors->any())
                    <div class="rounded-2xl border border-red-150 bg-red-50 p-4 text-sm text-red-700">
                        <p class="font-bold text-red-900">Vui lòng sửa các lỗi sau trước khi gửi:</p>
                        <ul class="mt-2 list-disc space-y-1 pl-5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- Step Progress Bar --}}
                <div class="rounded-2xl border border-slate-200 bg-white px-4 sm:px-6 py-4 shadow-xs">
                    <div class="relative max-w-xl mx-auto my-2">
                        <!-- Progress line background -->
                        <div class="absolute left-4 right-4 top-4 h-0.5 bg-slate-200 -translate-y-1/2 z-0" aria-hidden="true"></div>
                        
                        <!-- Progress line active -->
                        <div class="absolute left-4 top-4 h-0.5 bg-ue-brand -translate-y-1/2 transition-all duration-500 z-0" 
                             :style="`width: calc((${step} - 1) / 3 * (100% - 2rem))`" aria-hidden="true"></div>

                        <!-- Steps -->
                        <div class="relative flex justify-between z-10">
                            <!-- Step 1 -->
                            <div class="flex flex-col items-center">
                                <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold transition-all duration-300 ring-4 ring-white"
                                    :class="step >= 1 ? 'bg-ue-brand text-white shadow-sm' : 'bg-slate-200 text-slate-500'">
                                    <span x-text="step > 1 ? '✓' : '1'"></span>
                                </div>
                                <span class="text-[10px] mt-2 font-semibold text-center leading-tight transition-colors duration-300 hidden sm:block"
                                    :class="step >= 1 ? 'text-ue-brand' : 'text-slate-400'">Danh tính</span>
                            </div>

                            <!-- Step 2 -->
                            <div class="flex flex-col items-center">
                                <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold transition-all duration-300 ring-4 ring-white"
                                    :class="step >= 2 ? 'bg-ue-brand text-white shadow-sm' : 'bg-slate-200 text-slate-500'">
                                    <span x-text="step > 2 ? '✓' : '2'"></span>
                                </div>
                                <span class="text-[10px] mt-2 font-semibold text-center leading-tight transition-colors duration-300 hidden sm:block"
                                    :class="step >= 2 ? 'text-ue-brand' : 'text-slate-400'">Hồ sơ</span>
                            </div>

                            <!-- Step 3 -->
                            <div class="flex flex-col items-center">
                                <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold transition-all duration-300 ring-4 ring-white"
                                    :class="step >= 3 ? 'bg-ue-brand text-white shadow-sm' : 'bg-slate-200 text-slate-500'">
                                    <span x-text="step > 3 ? '✓' : '3'"></span>
                                </div>
                                <span class="text-[10px] mt-2 font-semibold text-center leading-tight transition-colors duration-300 hidden sm:block"
                                    :class="step >= 3 ? 'text-ue-brand' : 'text-slate-400'">Phạm vi</span>
                            </div>

                            <!-- Step 4 -->
                            <div class="flex flex-col items-center">
                                <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold transition-all duration-300 ring-4 ring-white"
                                    :class="step >= 4 ? 'bg-ue-brand text-white shadow-sm' : 'bg-slate-200 text-slate-500'">
                                    <span x-text="step > 4 ? '✓' : '4'"></span>
                                </div>
                                <span class="text-[10px] mt-2 font-semibold text-center leading-tight transition-colors duration-300 hidden sm:block"
                                    :class="step >= 4 ? 'text-ue-brand' : 'text-slate-400'">Cam kết</span>
                            </div>
                        </div>
                    </div>
                    <p class="mt-3 text-center text-xs text-slate-400 font-medium">
                        Bước <span x-text="step"></span>/4
                    </p>
                </div>

                {{-- Group 1: Identity & Verify documents --}}
                <section x-show="step === 1" x-transition.opacity.duration.300ms class="rounded-2xl border border-slate-200 bg-white p-5 shadow-xs space-y-5">
                    <h2 class="text-base font-bold text-slate-900 border-b pb-2">1. Minh chứng & Danh tính</h2>

                    {{-- Avatar Section --}}
                    <div class="flex flex-col sm:flex-row items-center gap-6">
                        @if ($avatarUrl)
                            <img src="{{ $avatarUrl }}" class="h-28 w-28 rounded-full border-4 border-white object-cover shadow-xl shrink-0" alt="Avatar" />
                        @else
                            <div class="h-28 w-28 rounded-full border-2 border-dashed border-slate-300 bg-slate-50 flex items-center justify-center shrink-0 shadow-sm">
                                <x-ui.icon name="user" size="2xl" class="text-slate-400" />
                            </div>
                        @endif
                        <div class="flex-1 text-center sm:text-left">
                            <div class="flex items-center justify-center sm:justify-start gap-2 flex-wrap">
                                <span class="text-base font-bold text-slate-900">Ảnh đại diện hồ sơ</span>
                                <span class="inline-flex items-center rounded-full bg-red-500 px-2.5 py-1 text-[10px] font-bold text-white uppercase tracking-wide shadow-sm">Bắt buộc</span>
                            </div>
                            <p class="text-sm text-slate-600 mt-2 leading-relaxed">
                                Ảnh đại diện <span class="font-semibold">rõ mặt</span> giúp tạo niềm tin cao đối với sinh viên khi kết nối.
                            </p>
                            <p class="text-xs text-slate-500 mt-1">
                                Chụp ảnh thẳng, không đeo khẩu trang/kính râm, đảm bảo ánh sáng tốt.
                            </p>
                            
                            <div class="mt-4 flex flex-wrap items-center justify-center sm:justify-start gap-3">
                                <label class="inline-flex cursor-pointer items-center justify-center rounded-xl bg-ue-brand hover:bg-ue-brand-dark px-5 py-2.5 text-sm font-bold text-white shadow-lg transition-all ring-2 ring-ue-brand/20">
                                    <x-ui.icon name="camera" size="sm" class="mr-2" />
                                    @if ($avatarUrl) Đổi ảnh đại diện @else Tải ảnh lên @endif
                                    <input type="file" wire:model="avatarFile" class="hidden" accept="image/jpeg,image/png,image/webp">
                                </label>
                                <span wire:loading wire:target="avatarFile" class="text-xs text-slate-600 font-bold animate-pulse">Đang upload...</span>
                                @if ($avatarUploadMessage)
                                    <span class="text-xs text-emerald-700 font-bold flex items-center gap-1">
                                        <x-ui.icon name="check-circle" size="sm" />
                                        {{ $avatarUploadMessage }}
                                    </span>
                                @endif
                            </div>
                            @error('avatarFile')
                                <p class="mt-3 text-sm font-bold text-red-600 flex items-center justify-center sm:justify-start gap-1">
                                    <x-ui.icon name="alert-circle" size="sm" />
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>
                    </div>

                    {{-- Evidence upload --}}
                    <div class="border-t pt-4">
                        <label class="block">
                            <span class="text-sm font-bold text-slate-700">Tài liệu/Minh chứng năng lực <span class="text-red-500">*</span></span>
                            <p class="text-xs text-slate-500 mt-1">Tải lên chứng chỉ, bảng điểm, CV hoặc chứng từ chứng minh năng lực chuyên môn của bạn để Admin xác thực.</p>
                            
                            <div class="mt-3 flex items-center gap-3">
                                <label class="inline-flex cursor-pointer items-center justify-center rounded-xl bg-slate-100 hover:bg-slate-200 px-3 py-1.5 text-xs font-bold text-slate-700 shadow-2xs transition">
                                    <x-ui.icon name="file-text" size="xs" class="mr-1.5" />
                                    Chọn tệp minh chứng
                                    <input type="file" wire:model="evidenceFile" class="hidden" accept=".jpg,.jpeg,.png,.pdf,.webp,.docx,.zip">
                                </label>
                                <span wire:loading wire:target="evidenceFile" class="text-xxs text-slate-400 font-semibold animate-pulse">Đang upload...</span>
                                @if ($evidencePreviewUrl)
                                    <a href="{{ $evidencePreviewUrl }}" target="_blank" rel="noopener noreferrer" class="shrink-0">
                                        <img src="{{ $evidencePreviewUrl }}" class="h-12 w-12 rounded-lg border border-slate-200 object-cover shadow-sm" alt="{{ $evidenceFileName }}" title="{{ $evidenceFileName }}" />
                                    </a>
                                @elseif ($evidenceFileName)
                                    <span class="text-xxs text-slate-600 font-bold bg-slate-100 px-2 py-1 rounded border">{{ $evidenceFileName }}</span>
                                @endif
                            </div>
                            @error('evidenceFile')
                                <p class="mt-1 text-xs font-bold text-red-600">{{ $message }}</p>
                            @enderror
                        </label>
                    </div>

                </section>

                {{-- Group 2: Public Profile Setup --}}
                <section x-show="step === 2" x-transition.opacity.duration.300ms class="rounded-2xl border border-slate-200 bg-white p-5 shadow-xs space-y-4">
                    <h2 class="text-base font-bold text-slate-900 border-b pb-2">2. Thiết lập hồ sơ công khai</h2>

                    <label class="block">
                        <span class="text-sm font-bold text-slate-700">Headline giới thiệu ngắn <span class="text-red-500">*</span></span>
                        <input
                            type="text"
                            wire:model.live="headline"
                            placeholder="Ví dụ: Frontend mentor hỗ trợ CV, portfolio và thực tập"
                            class="mt-2 w-full rounded-xl border-slate-200 text-sm focus:border-ue-brand focus:ring-ue-brand/20"
                            maxlength="160"
                        />
                            <span class="mt-1 block text-[10px] text-slate-400">Ví dụ hay: "Cựu SV FIT hỗ trợ review CV và định hướng thực tập"</span>
                        @error('headline') <p class="mt-1 text-xs text-red-600 font-bold">{{ $message }}</p> @enderror
                    </label>

                    <label class="block">
                        <span class="text-sm font-bold text-slate-700">Giới thiệu chi tiết <span class="text-red-500">*</span></span>
                        <textarea
                            wire:model.live="bio"
                            rows="5"
                            placeholder="Bạn là ai, đã có trải nghiệm gì, và thường hỗ trợ người học theo cách nào?"
                            class="mt-2 w-full rounded-xl border-slate-200 text-sm focus:border-ue-brand focus:ring-ue-brand/20"
                            maxlength="5000"
                        ></textarea>
                            <span class="mt-1 block text-[10px] text-slate-400">Gợi ý: Bạn học/làm ở đâu, chuyên môn gì, thường giúp SV như thế nào?</span>
                        @error('bio') <p class="mt-1 text-xs text-red-600 font-bold">{{ $message }}</p> @enderror
                    </label>

                    <label class="block">
                        <span class="text-sm font-bold text-slate-700">Chủ đề chuyên môn (Tối thiểu 2) <span class="text-red-500">*</span></span>
                        <textarea
                            wire:model.live="expertise_topics_text"
                            rows="3"
                                placeholder="Mỗi chủ đề là 1 từ/cụm từ, ngăn cách dấu phẩy. VD: Laravel, React, SQL"
                            class="mt-2 w-full rounded-xl border-slate-200 text-sm focus:border-ue-brand focus:ring-ue-brand/20"
                        ></textarea>
                        @error('expertise_topics_text') <p class="mt-1 text-xs text-red-600 font-bold">{{ $message }}</p> @enderror
                    </label>

                </section>

                {{-- Group 3: Scope & Expectations --}}
                <section x-show="step === 3" x-transition.opacity.duration.300ms class="rounded-2xl border border-slate-200 bg-white p-5 shadow-xs space-y-4">
                    <h2 class="text-base font-bold text-slate-900 border-b pb-2">3. Phạm vi hỗ trợ & Kỳ vọng</h2>

                    <div>
                        <span class="text-sm font-bold text-slate-700">Loại yêu cầu bạn sẵn sàng nhận <span class="text-red-500">*</span></span>
                        <div class="mt-3 grid gap-2 sm:grid-cols-2">
                            @foreach ($preferredRequestOptions as $value => $label)
                                @if ($value === 'other')
                                    <label class="flex items-center gap-2 rounded-xl border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-700 hover:border-ue-brand/45 hover:bg-slate-50 cursor-pointer">
                                        <input
                                            type="checkbox"
                                            wire:model.live="preferred_request_types"
                                            value="other"
                                            class="rounded border-slate-300 text-ue-brand focus:ring-ue-brand/30"
                                        >
                                        @if (in_array('other', $preferred_request_types, true))
                                            <input
                                                type="text"
                                                wire:model.live="custom_preferred_request"
                                                placeholder="Nhập yêu cầu khác..."
                                                class="h-7 w-full border-0 border-b border-slate-300 p-0 text-xs focus:border-ue-brand focus:ring-0 bg-transparent"
                                                onclick="event.stopPropagation()"
                                            >
                                        @else
                                            <span>Khác</span>
                                        @endif
                                    </label>
                                @else
                                    <label class="flex items-center gap-2 rounded-xl border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-700 hover:border-ue-brand/45 hover:bg-slate-50 cursor-pointer">
                                        <input
                                            type="checkbox"
                                            wire:model.live="preferred_request_types"
                                            value="{{ $value }}"
                                            class="rounded border-slate-300 text-ue-brand focus:ring-ue-brand/30"
                                        >
                                        {{ $label }}
                                    </label>
                                @endif
                            @endforeach
                        </div>
                        @error('preferred_request_types') <p class="mt-1 text-xs text-red-600 font-bold">{{ $message }}</p> @enderror
                        @error('custom_preferred_request') <p class="mt-1 text-xs text-red-600 font-bold">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid gap-4 md:grid-cols-2 border-t pt-4">
                        <label class="block">
                            <span class="text-sm font-bold text-slate-700">Thời gian phản hồi dự kiến <span class="text-red-500">*</span></span>
                            <input
                                type="text"
                                wire:model.live="response_expectation_text"
                                placeholder="Ví dụ: Phản hồi trong 2-3 ngày làm việc"
                                class="mt-2 w-full rounded-xl border-slate-200 text-sm focus:border-ue-brand focus:ring-ue-brand/20"
                            />
                            @error('response_expectation_text') <p class="mt-1 text-xs text-red-600 font-bold">{{ $message }}</p> @enderror
                        </label>

                        <label class="block">
                            <span class="text-sm font-bold text-slate-700">Khung giờ hỗ trợ</span>
                            <input
                                type="text"
                                wire:model.live="office_hours_text"
                                placeholder="Ví dụ: Tối thứ 3 và thứ 5 hàng tuần"
                                class="mt-2 w-full rounded-xl border-slate-200 text-sm focus:border-ue-brand focus:ring-ue-brand/20"
                            />
                        </label>
                    </div>

                </section>

                {{-- Motivation and Policy --}}
                <section x-show="step === 4" x-transition.opacity.duration.300ms class="rounded-2xl border border-slate-200 bg-white p-5 shadow-xs space-y-4">
                    <h2 class="text-base font-bold text-slate-900 border-b pb-2">4. Cam kết & Lý do đăng ký</h2>

                    <label class="block">
                        <span class="text-sm font-bold text-slate-700">Mục tiêu / Động lực làm mentor <span class="text-red-500">*</span></span>
                        <textarea
                            wire:model="motivation"
                            rows="4"
                                    placeholder="Tôi muốn... vì... Tôi từng..."
                            class="mt-2 w-full rounded-xl border-slate-200 text-sm focus:border-ue-brand focus:ring-ue-brand/20"
                            maxlength="5000"
                        ></textarea>
                        @error('motivation') <p class="mt-1 text-xs text-red-600 font-bold">{{ $message }}</p> @enderror
                    </label>



                    <label class="flex items-start gap-3 rounded-xl border border-amber-250 bg-amber-50/50 p-4 text-xs font-semibold text-slate-700">
                        <input
                            type="checkbox"
                            wire:model="policy_agreed"
                            class="mt-0.5 rounded border-slate-300 text-ue-brand focus:ring-ue-brand/30"
                        >
                        <span class="leading-relaxed">
                            Tôi cam kết bảo mật tuyệt đối các thông tin riêng tư, thông tin học tập của sinh viên được hỗ trợ, đồng thời chấp hành nghiêm túc các chính sách văn minh và nguyên tắc an toàn cộng đồng của UE Connect. <span class="text-red-500">*</span>
                        </span>
                    </label>
                    @error('policy_agreed') <p class="text-xs text-red-600 font-bold">{{ $message }}</p> @enderror
                </section>

                {{-- Step Navigation --}}
                <div class="flex items-center justify-between gap-3">
                    <button type="button" @click="step--" x-show="step > 1"
                        class="rounded-xl border border-slate-200 bg-white hover:bg-slate-50 px-5 py-3 text-sm font-bold text-slate-700 shadow-xs transition-all">
                        ← Quay lại
                    </button>
                    <div class="flex-1"></div>
                    <button type="button" @click="step++" x-show="step < 4"
                        class="rounded-xl bg-ue-brand hover:bg-ue-brand-dark px-5 py-3 text-sm font-bold text-white shadow-xs transition-all">
                        Tiếp theo →
                    </button>
                    <button type="submit" x-show="step === 4"
                        class="rounded-xl bg-ue-brand hover:bg-ue-brand-dark px-5 py-3 text-sm font-bold text-white shadow-xs transition-all">
                        {{ $existingRequestId ? 'Cập nhật và gửi lại' : 'Gửi hồ sơ đăng ký' }}
                    </button>
                </div>
            </form>

            {{-- Right side - Live Preview Card --}}
            <aside class="space-y-5 lg:sticky lg:top-6 self-start">
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <h3 class="text-xs font-bold uppercase tracking-wide text-slate-400 mb-3.5">Preview thẻ hồ sơ mentor</h3>

                    <div class="ue-loadable-card bg-white border border-slate-200 rounded-2xl p-4 flex flex-col justify-between shadow-2xs">
                        <div>
                            {{-- Identity info --}}
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex items-center gap-3">
                                    <div class="relative">
                                        @if ($avatarUrl)
                                            <img src="{{ $avatarUrl }}" class="h-10 w-10 rounded-full border border-slate-100 object-cover" alt="Avatar" />
                                        @else
                                            <x-ui.avatar :user="$currentUser" size="md" class="border border-slate-100" />
                                        @endif
                                    </div>
                                    <div class="min-w-0">
                                        <span class="text-xs font-bold text-slate-800 flex items-center gap-1 leading-snug">
                                            {{ $currentUser->name }}
                                            <x-ui.icon name="shield-check" size="xs" class="text-ue-brand fill-ue-brand" />
                                        </span>
                                        <p class="text-[9px] text-slate-400 font-semibold tracking-wide uppercase mt-0.5">
                                            @if ($requested_role_context === 'alumni') Cựu sinh viên
                                            @elseif ($requested_role_context === 'teacher') Giảng viên
                                            @elseif ($requested_role_context === 'exceptional_student') Sinh viên nổi bật
                                            @else Mentor
                                            @endif
                                        </p>
                                    </div>
                                </div>

                                @if ($currentUser->profile?->faculty)
                                    <span class="bg-slate-50 text-[9px] font-bold text-slate-500 px-2 py-0.5 rounded-md border border-slate-100 leading-none">
                                        {{ $currentUser->profile->faculty }}
                                    </span>
                                @endif
                            </div>

                            {{-- Headline --}}
                            <h4 class="mt-3.5 text-xs font-bold text-slate-850 line-clamp-2 leading-snug">
                                {{ $headline ?: 'Headline giới thiệu ngắn về bạn...' }}
                            </h4>

                            {{-- Bio --}}
                            <p class="text-xxs text-slate-500 font-medium leading-relaxed mt-2 line-clamp-3">
                                {{ $bio ?: 'Viết một chút giới thiệu về bạn để học viên biết vì sao nên chọn kết nối.' }}
                            </p>

                            {{-- Expertise Topics --}}
                            <div class="mt-3.5">
                                <span class="text-[9px] font-bold text-slate-400 block mb-1">Chuyên môn</span>
                                <div class="flex flex-wrap gap-1.5">
                                    @php
                                        $previewTopics = collect(preg_split('/[\r\n,]+/', (string) $expertise_topics_text))
                                            ->map(fn($item) => trim($item))
                                            ->filter()
                                            ->take(8)
                                            ->all();
                                    @endphp
                                    @forelse ($previewTopics as $topic)
                                        <span class="bg-slate-50 text-[9px] font-semibold text-slate-600 px-2 py-0.5 rounded border border-slate-100 leading-none">
                                            {{ $topic }}
                                        </span>
                                    @empty
                                        <span class="text-xxs text-slate-350 italic">Chưa nhập chủ đề chuyên môn</span>
                                    @endforelse
                                </div>
                            </div>

                            {{-- Preferred Request Types (Phạm vi hỗ trợ) --}}
                            @if (! empty($preferred_request_types))
                                <div class="mt-3">
                                    <span class="text-[9px] font-bold text-slate-400 block mb-1">Phạm vi hỗ trợ</span>
                                    <div class="flex flex-wrap gap-1">
                                        @foreach ($preferred_request_types as $type)
                                            @php
                                                $displayText = $preferredRequestOptions[$type] ?? $type;
                                                if ($type === 'other' && ! empty($custom_preferred_request)) {
                                                    $displayText = $custom_preferred_request;
                                                }
                                            @endphp
                                            <span class="bg-blue-50 text-[9px] font-bold text-blue-700 px-2 py-0.5 rounded-md border border-blue-100 leading-none">
                                                {{ $displayText }}
                                            </span>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>

                        {{-- Footer / Meta info --}}
                        <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-[10px] text-slate-400">
                            <div class="flex flex-col gap-0.5 leading-snug">
                                <span><span class="font-bold text-slate-600">Phản hồi:</span> {{ $response_expectation_text ?: 'N/A' }}</span>
                                @if ($office_hours_text)
                                    <span><span class="font-bold text-slate-600">Lịch hỗ trợ:</span> {{ $office_hours_text }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <div class="rounded-2xl border border-amber-200 bg-amber-50/50 p-4 text-xs leading-relaxed text-amber-900 shadow-3xs">
                    <p class="font-bold">Lưu ý chất lượng hồ sơ:</p>
                    <p class="mt-1">Hồ sơ sẽ chỉ được ban quản trị phê duyệt khi điền đầy đủ và cụ thể thông tin. Tránh dùng nội dung copy, headline chung chung hoặc thiếu ảnh đại diện rõ mặt.</p>
                </div>
            </aside>
        </div>
    @endif
</div>
