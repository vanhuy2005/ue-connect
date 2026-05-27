<?php

use App\Models\VerificationRequest;
use App\Models\VerificationEvidence;
use App\Models\VerificationReviewAction;
use App\Models\Profile;
use App\Models\StudentProfile;
use App\Models\AlumniProfile;
use App\Models\AdvisorProfile;
use App\Enums\VerificationStatus;
use App\Enums\AccountStatus;
use Livewire\Volt\Component;
use Illuminate\Support\Facades\DB;
use App\Services\AuditLogService;

new class extends Component {
    public int $requestId;
    public ?VerificationRequest $request = null;

    // Actions
    public string $action = 'approve'; // approve | reject | need_more_information | conflict | suspicious
    public string $reason = '';
    public string $instruction = '';

    public function mount(int $id): void
    {
        $this->requestId = $id;
        $this->loadRequest();
    }

    public function loadRequest(): void
    {
        $this->request = VerificationRequest::with([
            'user.roles',
            'submittedFaculty',
            'submittedAcademicProgram',
            'evidences.mediaFile',
            'reviewActions.admin'
        ])->findOrFail($this->requestId);

        // Auto transition PENDING_REVIEW / RESUBMITTED to UNDER_REVIEW
        if ($this->request->status === VerificationStatus::PENDING_REVIEW || $this->request->status === VerificationStatus::RESUBMITTED) {
            DB::transaction(function () {
                $beforeSnapshot = $this->request->toArray();

                $this->request->update([
                    'status' => VerificationStatus::UNDER_REVIEW,
                    'assigned_admin_id' => auth()->id(),
                ]);

                $afterSnapshot = $this->request->toArray();

                // Create Action record
                VerificationReviewAction::create([
                    'verification_request_id' => $this->request->id,
                    'admin_id' => auth()->id(),
                    'action_key' => 'start_review',
                    'reason' => 'Bắt đầu kiểm duyệt hồ sơ.',
                    'before_snapshot_json' => $beforeSnapshot,
                    'after_snapshot_json' => $afterSnapshot,
                ]);

                // Create Audit record
                AuditLogService::log(
                    actorId: auth()->id(),
                    actorType: 'admin',
                    actionKey: 'verification.start_review',
                    targetType: 'verification_requests',
                    targetId: $this->request->id,
                    beforeSnapshot: $beforeSnapshot,
                    afterSnapshot: $afterSnapshot,
                    reason: 'Admin bắt đầu kiểm duyệt.'
                );
            });
        }
    }

    public function processReview(): void
    {
        $user = $this->request->user;
        if (!$user) {
            $this->addError('general', 'Không tìm thấy tài khoản người dùng liên quan.');
            return;
        }

        // Validate based on chosen action
        if ($this->action === 'approve') {
            $this->validate([
                'reason' => ['nullable', 'string', 'max:1000'],
            ]);

            // If Student or Alumni, check unique MSSV/Student code in student_profiles
            if ($this->request->role_requested === 'student') {
                $exists = StudentProfile::where('student_code', $this->request->submitted_student_code)->exists();
                if ($exists) {
                    $this->addError('general', 'Mã số sinh viên (MSSV) này đã được sử dụng bởi một tài khoản khác.');
                    return;
                }
            }
        } elseif ($this->action === 'reject') {
            $this->validate([
                'reason' => ['required', 'string', 'min:5', 'max:1000'],
            ], [
                'reason.required' => 'Vui lòng cung cấp lý do từ chối cụ thể.',
                'reason.min' => 'Lý do từ chối cần có ít nhất 5 ký tự.',
            ]);
        } elseif ($this->action === 'need_more_information') {
            $this->validate([
                'instruction' => ['required', 'string', 'min:5', 'max:1000'],
            ], [
                'instruction.required' => 'Vui lòng cung cấp hướng dẫn bổ sung thông tin cụ thể cho người dùng.',
                'instruction.min' => 'Hướng dẫn cần có ít nhất 5 ký tự.',
            ]);
        } elseif ($this->action === 'conflict') {
            $this->validate([
                'reason' => ['required', 'string', 'min:5', 'max:1000'],
            ], [
                'reason.required' => 'Vui lòng cung cấp lý do/chi tiết xung đột.',
                'reason.min' => 'Chi tiết xung đột cần có ít nhất 5 ký tự.',
            ]);
        } elseif ($this->action === 'suspicious') {
            $this->validate([
                'reason' => ['required', 'string', 'min:5', 'max:1000'],
            ], [
                'reason.required' => 'Vui lòng nhập lý do đánh giá tài khoản nghi ngờ/giả mạo.',
                'reason.min' => 'Lý do cần có ít nhất 5 ký tự.',
            ]);
        }

        DB::transaction(function () use ($user) {
            $beforeSnapshot = $this->request->toArray();
            $userBeforeSnapshot = $user->toArray();

            if ($this->action === 'approve') {
                // 1. Create or Update Profile
                $profile = Profile::updateOrCreate(
                    ['user_id' => $user->id],
                    [
                        'display_name' => $this->request->submitted_name,
                        'role_type' => $this->request->role_requested,
                        'profile_status' => 'incomplete',
                    ]
                );

                // 2. Create specific subprofile
                if ($this->request->role_requested === 'student') {
                    StudentProfile::updateOrCreate(
                        ['profile_id' => $profile->id],
                        [
                            'student_code' => $this->request->submitted_student_code,
                            'faculty_id' => $this->request->submitted_faculty_id,
                            'academic_program_id' => $this->request->submitted_academic_program_id,
                            'cohort' => $this->request->submitted_cohort,
                        ]
                    );
                } elseif ($this->request->role_requested === 'alumni') {
                    AlumniProfile::updateOrCreate(
                        ['profile_id' => $profile->id],
                        [
                            'faculty_id' => $this->request->submitted_faculty_id,
                            'academic_program_id' => $this->request->submitted_academic_program_id,
                            'cohort' => $this->request->submitted_cohort,
                        ]
                    );
                } elseif ($this->request->role_requested === 'advisor') {
                    AdvisorProfile::updateOrCreate(
                        ['profile_id' => $profile->id],
                        [
                            'faculty_id' => $this->request->submitted_faculty_id,
                        ]
                    );
                }

                // 3. Assign role via Spatie Permission
                $user->syncRoles([$this->request->role_requested]);

                // 4. Update request and user status
                $this->request->update([
                    'status' => VerificationStatus::APPROVED,
                    'reviewed_at' => now(),
                ]);

                $user->update([
                    'account_status' => AccountStatus::PROFILE_INCOMPLETE,
                ]);

                $reviewReason = $this->reason ?: 'Đã kiểm duyệt và phê duyệt thông tin hợp lệ.';

                // Log Action & Audit
                VerificationReviewAction::create([
                    'verification_request_id' => $this->request->id,
                    'admin_id' => auth()->id(),
                    'action_key' => 'approve',
                    'reason' => $reviewReason,
                    'before_snapshot_json' => $beforeSnapshot,
                    'after_snapshot_json' => $this->request->fresh()->toArray(),
                ]);

                AuditLogService::log(
                    actorId: auth()->id(),
                    actorType: 'admin',
                    actionKey: 'verification.approve',
                    targetType: 'verification_requests',
                    targetId: $this->request->id,
                    beforeSnapshot: $beforeSnapshot,
                    afterSnapshot: $this->request->fresh()->toArray(),
                    reason: $reviewReason
                );

                AuditLogService::log(
                    actorId: auth()->id(),
                    actorType: 'admin',
                    actionKey: 'user.update_status',
                    targetType: 'users',
                    targetId: $user->id,
                    beforeSnapshot: $userBeforeSnapshot,
                    afterSnapshot: $user->fresh()->toArray(),
                    reason: 'Tài khoản được phê duyệt định danh thành công.'
                );

            } elseif ($this->action === 'reject') {
                $this->request->update([
                    'status' => VerificationStatus::REJECTED,
                    'reviewed_at' => now(),
                ]);

                $user->update([
                    'account_status' => AccountStatus::REGISTERED,
                ]);

                VerificationReviewAction::create([
                    'verification_request_id' => $this->request->id,
                    'admin_id' => auth()->id(),
                    'action_key' => 'reject',
                    'reason' => $this->reason,
                    'before_snapshot_json' => $beforeSnapshot,
                    'after_snapshot_json' => $this->request->fresh()->toArray(),
                ]);

                AuditLogService::log(
                    actorId: auth()->id(),
                    actorType: 'admin',
                    actionKey: 'verification.reject',
                    targetType: 'verification_requests',
                    targetId: $this->request->id,
                    beforeSnapshot: $beforeSnapshot,
                    afterSnapshot: $this->request->fresh()->toArray(),
                    reason: $this->reason
                );

                AuditLogService::log(
                    actorId: auth()->id(),
                    actorType: 'admin',
                    actionKey: 'user.update_status',
                    targetType: 'users',
                    targetId: $user->id,
                    beforeSnapshot: $userBeforeSnapshot,
                    afterSnapshot: $user->fresh()->toArray(),
                    reason: 'Tài khoản bị từ chối hồ sơ xác thực. Lý do: ' . $this->reason
                );

            } elseif ($this->action === 'need_more_information') {
                $this->request->update([
                    'status' => VerificationStatus::NEEDS_MORE_INFORMATION,
                    'reviewed_at' => now(),
                ]);

                $user->update([
                    'account_status' => AccountStatus::REGISTERED,
                ]);

                VerificationReviewAction::create([
                    'verification_request_id' => $this->request->id,
                    'admin_id' => auth()->id(),
                    'action_key' => 'need_more_information',
                    'instruction' => $this->instruction,
                    'reason' => 'Yêu cầu bổ sung thêm thông tin.',
                    'before_snapshot_json' => $beforeSnapshot,
                    'after_snapshot_json' => $this->request->fresh()->toArray(),
                ]);

                AuditLogService::log(
                    actorId: auth()->id(),
                    actorType: 'admin',
                    actionKey: 'verification.need_more_information',
                    targetType: 'verification_requests',
                    targetId: $this->request->id,
                    beforeSnapshot: $beforeSnapshot,
                    afterSnapshot: $this->request->fresh()->toArray(),
                    reason: $this->instruction
                );

            } elseif ($this->action === 'conflict') {
                $this->request->update([
                    'status' => VerificationStatus::CONFLICT,
                    'reviewed_at' => now(),
                ]);

                $user->update([
                    'account_status' => AccountStatus::RESTRICTED,
                    'account_status_reason' => 'Xung đột mã số định danh hoặc thông tin hồ sơ.',
                ]);

                VerificationReviewAction::create([
                    'verification_request_id' => $this->request->id,
                    'admin_id' => auth()->id(),
                    'action_key' => 'mark_conflict',
                    'reason' => $this->reason,
                    'before_snapshot_json' => $beforeSnapshot,
                    'after_snapshot_json' => $this->request->fresh()->toArray(),
                ]);

                AuditLogService::log(
                    actorId: auth()->id(),
                    actorType: 'admin',
                    actionKey: 'verification.mark_conflict',
                    targetType: 'verification_requests',
                    targetId: $this->request->id,
                    beforeSnapshot: $beforeSnapshot,
                    afterSnapshot: $this->request->fresh()->toArray(),
                    reason: $this->reason
                );

                AuditLogService::log(
                    actorId: auth()->id(),
                    actorType: 'admin',
                    actionKey: 'user.update_status',
                    targetType: 'users',
                    targetId: $user->id,
                    beforeSnapshot: $userBeforeSnapshot,
                    afterSnapshot: $user->fresh()->toArray(),
                    reason: 'Đánh dấu tài khoản bị hạn chế do xung đột mã định danh. Chi tiết: ' . $this->reason
                );

            } elseif ($this->action === 'suspicious') {
                $this->request->update([
                    'status' => VerificationStatus::SUSPICIOUS,
                    'reviewed_at' => now(),
                ]);

                $user->update([
                    'account_status' => AccountStatus::SUSPENDED,
                    'account_status_reason' => 'Tài khoản có dấu hiệu giả mạo thông tin minh chứng.',
                ]);

                VerificationReviewAction::create([
                    'verification_request_id' => $this->request->id,
                    'admin_id' => auth()->id(),
                    'action_key' => 'suspend_suspicious',
                    'reason' => $this->reason,
                    'before_snapshot_json' => $beforeSnapshot,
                    'after_snapshot_json' => $this->request->fresh()->toArray(),
                ]);

                AuditLogService::log(
                    actorId: auth()->id(),
                    actorType: 'admin',
                    actionKey: 'verification.suspend_suspicious',
                    targetType: 'verification_requests',
                    targetId: $this->request->id,
                    beforeSnapshot: $beforeSnapshot,
                    afterSnapshot: $this->request->fresh()->toArray(),
                    reason: $this->reason
                );

                AuditLogService::log(
                    actorId: auth()->id(),
                    actorType: 'admin',
                    actionKey: 'user.update_status',
                    targetType: 'users',
                    targetId: $user->id,
                    beforeSnapshot: $userBeforeSnapshot,
                    afterSnapshot: $user->fresh()->toArray(),
                    reason: 'Đình chỉ hoạt động tài khoản do hồ sơ minh chứng giả mạo. Lý do: ' . $this->reason
                );
            }
        });

        session()->flash('message', 'Hồ sơ đã được xử lý và cập nhật thành công.');
        $this->redirect(route('admin.verifications.queue'), navigate: true);
    }
}; ?>

<div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    {{-- Breadcrumb / Header --}}
    <div class="mb-6">
        <a href="{{ route('admin.verifications.queue') }}" class="inline-flex items-center text-xs font-semibold text-ue-text-muted hover:text-ue-brand transition-colors mb-2">
            <x-ui.icon name="arrow-left" size="sm" class="mr-1" />
            Quay lại Danh sách chờ duyệt
        </a>
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-ue-text">Kiểm duyệt chi tiết hồ sơ</h1>
                <p class="text-sm text-ue-text-secondary mt-1">Hồ sơ định danh của #{{ $request->id }} — {{ $request->submitted_name }}</p>
            </div>
            <div>
                @php
                    $badgeVariant = match($request->status) {
                        VerificationStatus::PENDING_REVIEW => 'pending',
                        VerificationStatus::UNDER_REVIEW => 'info',
                        VerificationStatus::RESUBMITTED => 'pending',
                        VerificationStatus::NEEDS_MORE_INFORMATION => 'need-more-info',
                        VerificationStatus::APPROVED => 'success',
                        VerificationStatus::REJECTED => 'rejected',
                        VerificationStatus::CONFLICT => 'warning',
                        VerificationStatus::SUSPICIOUS => 'danger',
                        default => 'neutral',
                    };
                @endphp
                <x-ui.badge :variant="$badgeVariant" size="md">
                    {{ match($request->status) {
                        VerificationStatus::PENDING_REVIEW => 'Chờ duyệt',
                        VerificationStatus::UNDER_REVIEW => 'Đang kiểm tra',
                        VerificationStatus::RESUBMITTED => 'Học viên gửi lại',
                        VerificationStatus::NEEDS_MORE_INFORMATION => 'Cần bổ sung thêm',
                        VerificationStatus::APPROVED => 'Đã duyệt',
                        VerificationStatus::REJECTED => 'Bị từ chối',
                        VerificationStatus::CONFLICT => 'Xung đột MSSV',
                        VerificationStatus::SUSPICIOUS => 'Nghi ngờ',
                        default => $request->status->value,
                    } }}
                </x-ui.badge>
            </div>
        </div>
    </div>

    {{-- Error messages --}}
    @if ($errors->has('general'))
        <div class="mb-6 p-4 bg-[var(--danger-bg-soft)] text-[var(--danger-text)] rounded-xl border border-[var(--danger-border)]">
            <div class="flex items-center gap-2">
                <x-ui.icon name="alert-circle" />
                <span class="font-bold text-sm">{{ $errors->first('general') }}</span>
            </div>
        </div>
    @endif

    {{-- Split Screen Layout --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        {{-- Left & Middle Column (2/3): Submission Info & Evidences --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- User Submitted Details --}}
            <x-ui.card>
                <h2 class="text-base font-bold text-ue-text border-b border-ue-border pb-3 mb-4">Thông tin đăng ký định danh</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-y-4 gap-x-6 text-sm">
                    <div>
                        <div class="text-xs text-ue-text-muted font-semibold">Họ và tên học viên</div>
                        <div class="font-bold text-ue-text mt-0.5">{{ $request->submitted_name }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-ue-text-muted font-semibold">Email liên hệ</div>
                        <div class="font-bold text-ue-text mt-0.5">{{ $request->submitted_email }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-ue-text-muted font-semibold">Vai trò yêu cầu</div>
                        <div class="font-bold text-ue-text mt-0.5 flex items-center gap-1.5 capitalize">
                            <x-ui.badge :variant="match($request->role_requested) { 'student'=>'student', 'alumni'=>'alumni', 'advisor'=>'advisor', default=>'neutral' }" size="sm">
                                {{ match($request->role_requested) { 'student'=>'Sinh viên', 'alumni'=>'Cựu sinh viên', 'advisor'=>'Cố vấn', default=>$request->role_requested } }}
                            </x-ui.badge>
                        </div>
                    </div>
                    
                    @if ($request->role_requested !== 'advisor')
                        <div>
                            <div class="text-xs text-ue-text-muted font-semibold">Mã số sinh viên (MSSV)</div>
                            <div class="font-bold text-ue-text mt-0.5">{{ $request->submitted_student_code ?: 'N/A' }}</div>
                        </div>
                        <div>
                            <div class="text-xs text-ue-text-muted font-semibold">Khoa quản lý</div>
                            <div class="font-bold text-ue-text mt-0.5">{{ $request->submittedFaculty ? $request->submittedFaculty->name : 'N/A' }}</div>
                        </div>
                        <div>
                            <div class="text-xs text-ue-text-muted font-semibold">Chuyên ngành học</div>
                            <div class="font-bold text-ue-text mt-0.5">{{ $request->submittedAcademicProgram ? $request->submittedAcademicProgram->name : 'N/A' }}</div>
                        </div>
                        <div>
                            <div class="text-xs text-ue-text-muted font-semibold">Khóa học / Niên khóa</div>
                            <div class="font-bold text-ue-text mt-0.5">{{ $request->submitted_cohort ?: 'N/A' }}</div>
                        </div>
                    @else
                        <div>
                            <div class="text-xs text-ue-text-muted font-semibold">Khoa / Phòng ban công tác</div>
                            <div class="font-bold text-ue-text mt-0.5">{{ $request->submittedFaculty ? $request->submittedFaculty->name : 'N/A' }}</div>
                        </div>
                    @endif

                    @if ($request->submitted_note)
                        <div class="md:col-span-2 border-t border-ue-border pt-4">
                            <div class="text-xs text-ue-text-muted font-semibold">Ghi chú bổ sung từ học viên</div>
                            <div class="text-ue-text italic mt-1 bg-ue-surface-subtle p-3 rounded-lg border border-ue-border">
                                "{{ $request->submitted_note }}"
                            </div>
                        </div>
                    @endif
                </div>
            </x-ui.card>

            {{-- Evidences List & Previews --}}
            <x-ui.card>
                <h2 class="text-base font-bold text-ue-text border-b border-ue-border pb-3 mb-4">Tài liệu minh chứng đính kèm (Tối đa 3)</h2>
                
                <div class="space-y-6">
                    @forelse ($request->evidences as $index => $evidence)
                        <div class="border border-ue-border rounded-xl p-4 bg-ue-surface-subtle space-y-4">
                            <div class="flex items-start justify-between border-b border-ue-border pb-2.5">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-8 h-8 rounded-lg bg-ue-brand-soft text-ue-brand flex items-center justify-center font-bold text-sm">
                                        {{ $index + 1 }}
                                    </div>
                                    <div>
                                        <div class="font-semibold text-xs text-ue-text">
                                            Loại: 
                                            <span class="font-bold uppercase text-ue-brand ml-0.5">
                                                {{ match($evidence->evidence_type) {
                                                    'student_card' => 'Thẻ sinh viên',
                                                    'admission_letter' => 'Giấy báo nhập học',
                                                    'transcript' => 'Bảng điểm',
                                                    'graduation_certificate' => 'Bằng tốt nghiệp',
                                                    'email_evidence' => 'Email trường',
                                                    default => 'Khác',
                                                } }}
                                            </span>
                                        </div>
                                        <div class="text-[10px] text-ue-text-muted mt-0.5">
                                            {{ $evidence->mediaFile ? $evidence->mediaFile->original_name : 'Liên kết bên ngoài' }}
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    @if ($evidence->evidence_link)
                                        <x-ui.button href="{{ $evidence->evidence_link }}" target="_blank" variant="secondary" size="xs" icon="external-link">
                                            Mở liên kết
                                        </x-ui.button>
                                    @else
                                        <span class="text-[10px] bg-ue-surface-pressed text-ue-text-secondary px-2 py-0.5 rounded font-bold uppercase tracking-wider">
                                            Private File
                                        </span>
                                    @endif
                                </div>
                            </div>

                            @if ($evidence->user_note)
                                <div class="text-xs">
                                    <span class="font-semibold text-ue-text-muted">Ghi chú của học viên:</span>
                                    <span class="text-ue-text italic ml-1">"{{ $evidence->user_note }}"</span>
                                </div>
                            @endif

                            {{-- Inline Evidence Rendering --}}
                            <div class="mt-2 bg-ue-surface border border-ue-border rounded-lg p-2 overflow-hidden flex items-center justify-center min-h-[120px]">
                                @if ($evidence->evidence_link)
                                    <div class="text-center p-4">
                                        <x-ui.icon name="external-link" size="lg" class="text-ue-text-muted mx-auto mb-2" />
                                        <div class="text-xs font-semibold text-ue-text">Tài liệu ngoài được liên kết:</div>
                                        <a href="{{ $evidence->evidence_link }}" target="_blank" class="text-xs font-bold text-ue-brand hover:underline mt-1 block break-all">
                                            {{ $evidence->evidence_link }}
                                        </a>
                                    </div>
                                @elseif ($evidence->mediaFile)
                                    @php
                                        $isImage = str_starts_with($evidence->mediaFile->mime_type, 'image/');
                                        $isPdf = $evidence->mediaFile->mime_type === 'application/pdf';
                                    @endphp

                                    @if ($isImage)
                                        <img src="{{ route('admin.verification.evidence', ['evidence' => $evidence->id]) }}" alt="Minh chứng #{{ $index + 1 }}" class="max-w-full max-h-[450px] rounded-lg shadow-2xs border border-ue-border" />
                                    @elseif ($isPdf)
                                        <div class="text-center p-6 w-full flex flex-col items-center">
                                            <x-ui.icon name="file-text" size="xl" class="text-red-500 mb-2" />
                                            <div class="text-xs font-bold text-ue-text">Tài liệu PDF: {{ $evidence->mediaFile->original_name }}</div>
                                            <div class="text-[10px] text-ue-text-muted mt-0.5">{{ number_format($evidence->mediaFile->size_bytes / 1024 / 1024, 2) }} MB</div>
                                            <div class="mt-4 flex gap-2">
                                                <x-ui.button href="{{ route('admin.verification.evidence', ['evidence' => $evidence->id]) }}" target="_blank" variant="secondary" size="xs" icon="external-link">
                                                    Mở PDF trong tab mới
                                                </x-ui.button>
                                                <x-ui.button href="{{ route('admin.verification.evidence', ['evidence' => $evidence->id]) }}" download variant="outline" size="xs" icon="download">
                                                    Tải xuống máy
                                                </x-ui.button>
                                            </div>
                                        </div>
                                    @else
                                        <div class="text-center p-6 flex flex-col items-center">
                                            <x-ui.icon name="file" size="lg" class="text-ue-text-muted mb-2" />
                                            <div class="text-xs font-semibold text-ue-text">Định dạng tệp không được hỗ trợ preview trực tiếp</div>
                                            <div class="text-[10px] text-ue-text-muted mt-1">{{ $evidence->mediaFile->mime_type }} — {{ number_format($evidence->mediaFile->size_bytes / 1024, 1) }} KB</div>
                                            <x-ui.button href="{{ route('admin.verification.evidence', ['evidence' => $evidence->id]) }}" download variant="secondary" size="xs" class="mt-3" icon="download">
                                                Tải về kiểm tra
                                            </x-ui.button>
                                        </div>
                                    @endif
                                @else
                                    <div class="text-center text-xs text-ue-text-muted p-4">Không tìm thấy tài liệu đính kèm nào.</div>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="text-center text-xs text-ue-text-muted py-6">Không tìm thấy tài liệu minh chứng nào được đính kèm.</div>
                    @endforelse
                </div>
            </x-ui.card>
        </div>

        {{-- Right Column (1/3): Audit / History & Review Form Drawer --}}
        <div class="space-y-6">
            {{-- Review Form Drawer --}}
            <x-ui.card variant="elevated" class="sticky top-6">
                <h2 class="text-base font-bold text-ue-text border-b border-ue-border pb-3 mb-4">Xử lý hồ sơ xác thực</h2>

                <form wire:submit.prevent="processReview" class="space-y-4">
                    {{-- Action Selection --}}
                    <div>
                        <x-ui.label for="action">Thao tác phê duyệt</x-ui.label>
                        <x-ui.select wire:model.live="action" id="action" class="mt-1">
                            <option value="approve">Chấp thuận (Approve)</option>
                            <option value="reject">Từ chối (Reject)</option>
                            <option value="need_more_information">Cần bổ sung thông tin (Needs Info)</option>
                            <option value="conflict">Đánh dấu Xung đột (Conflict)</option>
                            <option value="suspicious">Nghi ngờ giả mạo (Suspicious)</option>
                        </x-ui.select>
                    </div>

                    {{-- Dynamic input fields --}}
                    @if ($action === 'approve')
                        <div class="p-3 bg-[var(--success-bg-soft)] text-[var(--success-text)] border border-[var(--success-border)] rounded-lg text-xs space-y-1">
                            <div class="font-bold flex items-center gap-1">
                                <x-ui.icon name="check-circle" size="xs" />
                                Lưu ý trước khi chấp thuận:
                            </div>
                            <ul class="list-disc pl-4 space-y-0.5 leading-relaxed font-semibold">
                                <li>Nâng cấp User Status thành <span class="underline">profile_incomplete</span>.</li>
                                <li>Gán vai trò <span class="underline capitalize">{{ $request->role_requested }}</span> thành công.</li>
                                <li>MSSV sinh viên sẽ được lưu và ràng buộc duy nhất vĩnh viễn trên hệ thống.</li>
                            </ul>
                        </div>

                        <div>
                            <x-ui.label for="reason">Ghi chú phê duyệt (Tùy chọn)</x-ui.label>
                            <x-ui.textarea wire:model="reason" id="reason" rows="3" placeholder="Nhập thêm ghi chú phê duyệt lưu lịch sử..." class="mt-1 text-xs" />
                            <x-ui.field-error name="reason" />
                        </div>

                    @elseif ($action === 'reject')
                        <div class="p-3 bg-[var(--danger-bg-soft)] text-[var(--danger-text)] border border-[var(--danger-border)] rounded-lg text-xs space-y-1">
                            <div class="font-bold flex items-center gap-1">
                                <x-ui.icon name="x-circle" size="xs" />
                                Từ chối hồ sơ:
                            </div>
                            <div class="leading-relaxed font-semibold">Học viên sẽ nhận thông báo, trạng thái tài khoản chuyển về <span class="underline">registered</span> và được phép sửa đổi/tạo lại hồ sơ mới.</div>
                        </div>

                        <div>
                            <x-ui.label for="reason" :required="true">Lý do từ chối cụ thể</x-ui.label>
                            <x-ui.textarea wire:model="reason" id="reason" rows="3" placeholder="Nhập chi tiết lý do từ chối hiển thị trực tiếp cho học viên..." class="mt-1 text-xs" />
                            <x-ui.field-error name="reason" />
                        </div>

                    @elseif ($action === 'need_more_information')
                        <div class="p-3 bg-[var(--warning-bg-soft)] text-[var(--warning-text)] border border-[var(--warning-border)] rounded-lg text-xs space-y-1">
                            <div class="font-bold flex items-center gap-1">
                                <x-ui.icon name="info" size="xs" />
                                Yêu cầu bổ sung thông tin:
                            </div>
                            <div class="leading-relaxed font-semibold">Yêu cầu học viên cập nhật/upload lại minh chứng. Tài khoản chuyển về <span class="underline">needs_more_information</span>, giao diện học viên sẽ hiện form cho sửa đổi thông tin.</div>
                        </div>

                        <div>
                            <x-ui.label for="instruction" :required="true">Hướng dẫn bổ sung thông tin</x-ui.label>
                            <x-ui.textarea wire:model="instruction" id="instruction" rows="3" placeholder="Nhập rõ ràng hướng dẫn học viên cần chụp lại thẻ hay bổ sung tài liệu gì..." class="mt-1 text-xs" />
                            <x-ui.field-error name="instruction" />
                        </div>

                    @elseif ($action === 'conflict')
                        <div class="p-3 bg-orange-50 text-orange-800 border border-orange-200 rounded-lg text-xs space-y-1">
                            <div class="font-bold flex items-center gap-1">
                                <x-ui.icon name="alert" size="xs" />
                                Phát hiện Xung đột MSSV:
                            </div>
                            <div class="leading-relaxed font-semibold">Mã số sinh viên (MSSV) bị trùng lặp hoặc mâu thuẫn. Tài khoản người dùng sẽ bị <span class="underline">Hạn chế (Restricted)</span> tạm thời để giáo vụ xác minh chéo.</div>
                        </div>

                        <div>
                            <x-ui.label for="reason" :required="true">Chi tiết nguyên nhân xung đột</x-ui.label>
                            <x-ui.textarea wire:model="reason" id="reason" rows="3" placeholder="Ghi chú nguyên nhân xung đột hoặc mã tài khoản bị trùng..." class="mt-1 text-xs" />
                            <x-ui.field-error name="reason" />
                        </div>

                    @elseif ($action === 'suspicious')
                        <div class="p-3 bg-red-50 text-red-800 border border-red-200 rounded-lg text-xs space-y-1">
                            <div class="font-bold flex items-center gap-1">
                                <x-ui.icon name="shield" size="xs" />
                                Cảnh báo Giả mạo / Nghi ngờ:
                            </div>
                            <div class="leading-relaxed font-semibold">Minh chứng hoặc thông tin có dấu hiệu gian lận nghiêm trọng. Tài khoản người dùng sẽ bị <span class="underline">Khóa (Suspended)</span> ngay lập tức và ngăn cấm đăng nhập.</div>
                        </div>

                        <div>
                            <x-ui.label for="reason" :required="true">Lý do khóa tài khoản</x-ui.label>
                            <x-ui.textarea wire:model="reason" id="reason" rows="3" placeholder="Ghi rõ lý do tại sao tài khoản bị phát hiện nghi ngờ/giả mạo..." class="mt-1 text-xs" />
                            <x-ui.field-error name="reason" />
                        </div>
                    @endif

                    {{-- Actions controls --}}
                    <div class="border-t border-ue-border pt-4 mt-6 flex justify-end">
                        <x-ui.button type="submit" variant="primary" icon="check" class="w-full">
                            Xác nhận & Cập nhật
                        </x-ui.button>
                    </div>
                </form>
            </x-ui.card>

            {{-- Audit Trail & Review Actions History --}}
            <x-ui.card>
                <h2 class="text-base font-bold text-ue-text border-b border-ue-border pb-3 mb-3">Lịch sử kiểm duyệt</h2>
                
                <div class="relative pl-4 border-l-2 border-ue-border space-y-5 py-1 text-xs">
                    @forelse ($request->reviewActions as $act)
                        @php
                            $dotColor = match($act->action_key) {
                                'approve' => 'bg-green-500 ring-green-100',
                                'reject' => 'bg-red-500 ring-red-100',
                                'need_more_information' => 'bg-blue-500 ring-blue-100',
                                'mark_conflict' => 'bg-orange-500 ring-orange-100',
                                'suspend_suspicious' => 'bg-red-700 ring-red-200',
                                'start_review' => 'bg-slate-400 ring-slate-100',
                                default => 'bg-slate-500 ring-slate-100',
                            };

                            $actLabel = match($act->action_key) {
                                'approve' => 'Đã phê duyệt',
                                'reject' => 'Đã từ chối',
                                'need_more_information' => 'Yêu cầu bổ sung',
                                'mark_conflict' => 'Đánh dấu xung đột',
                                'suspend_suspicious' => 'Khóa giả mạo',
                                'start_review' => 'Bắt đầu kiểm duyệt',
                                default => $act->action_key,
                            };
                        @endphp
                        
                        <div class="relative">
                            {{-- Timeline dot indicator --}}
                            <div class="absolute -left-[21px] mt-0.5 w-2.5 h-2.5 rounded-full {{ $dotColor }} ring-4"></div>
                            
                            <div class="font-bold text-ue-text">{{ $actLabel }}</div>
                            <div class="text-[10px] text-ue-text-muted mt-0.5">
                                Thực hiện bởi: <span class="font-bold">{{ $act->admin ? $act->admin->name : 'Hệ thống' }}</span>
                            </div>
                            <div class="text-[10px] text-ue-text-disabled">{{ $act->created_at->format('H:i d/m/Y') }} ({{ $act->created_at->diffForHumans() }})</div>
                            
                            @if ($act->reason && $act->action_key !== 'start_review')
                                <div class="mt-1 bg-ue-surface border border-ue-border p-2 rounded text-ue-text-secondary leading-normal">
                                    "{{ $act->reason }}"
                                </div>
                            @endif

                            @if ($act->instruction)
                                <div class="mt-1 bg-ue-surface border border-ue-border p-2 rounded text-ue-text-secondary leading-normal">
                                    Hướng dẫn: "{{ $act->instruction }}"
                                </div>
                            @endif
                        </div>
                    @empty
                        <div class="text-ue-text-muted italic py-1 pl-1">Chưa có lịch sử thao tác nào được lưu.</div>
                    @endforelse
                </div>
            </x-ui.card>
        </div>

    </div>
</div>
