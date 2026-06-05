<?php

use App\Enums\AccountStatus;
use App\Enums\VerificationStatus;
use App\Models\Faculty;
use App\Models\AcademicProgram;
use App\Models\VerificationRequest;
use App\Models\VerificationEvidence;
use App\Models\MediaFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;

new #[Layout('layouts.app')] class extends Component
{
    use WithFileUploads;

    public ?VerificationRequest $request = null;
    
    // Correction fields (for needs_more_information state)
    public bool $isCorrecting = false;
    public string $submitted_name = '';
    public string $submitted_student_code = '';
    public ?int $submitted_faculty_id = null;
    public ?int $submitted_academic_program_id = null;
    public string $submitted_cohort = '';
    public string $submitted_note = '';

    // Evidence
    public $evidence_files = [];
    public array $evidence_notes = [];
    public array $evidence_types = [];

    public function mount(): void
    {
        $this->loadRequest();
    }

    public function loadRequest(): void
    {
        $user = auth()->user();
        if ($user) {
            $this->request = VerificationRequest::where('user_id', $user->id)
                ->with(['evidences.mediaFile', 'reviewActions'])
                ->latest()
                ->first();

            if ($this->request && $this->request->status === VerificationStatus::APPROVED) {
                // If already approved, redirect to app home
                $this->redirect(route('dashboard'), navigate: true);
            }

            if ($this->request) {
                $this->submitted_name = $this->request->submitted_name;
                $this->submitted_student_code = $this->request->submitted_student_code ?? '';
                $this->submitted_faculty_id = $this->request->submitted_faculty_id;
                $this->submitted_academic_program_id = $this->request->submitted_academic_program_id;
                $this->submitted_cohort = $this->request->submitted_cohort ?? '';
                $this->submitted_note = $this->request->submitted_note ?? '';
            }
        }
    }

    public function getFacultiesProperty()
    {
        return Faculty::where('status', 'active')->orderBy('name')->get();
    }

    public function getAcademicProgramsProperty()
    {
        if (!$this->submitted_faculty_id) {
            return collect();
        }
        return AcademicProgram::where('faculty_id', $this->submitted_faculty_id)
            ->where('status', 'active')
            ->orderBy('name')
            ->get();
    }

    public function startCorrection(): void
    {
        $this->isCorrecting = true;
    }

    public function cancelCorrection(): void
    {
        $this->isCorrecting = false;
    }

    public function restartVerification(): void
    {
        // Redirect to start to create a new clean draft
        $this->redirect(route('verification.start'), navigate: true);
    }

    public function getLatestActionProperty()
    {
        if (!$this->request) {
            return null;
        }
        return $this->request->reviewActions()->latest()->first();
    }

    public function resubmit(): void
    {
        $user = auth()->user();
        if (!$user || !$this->request) {
            return;
        }

        $rules = [
            'submitted_name' => ['required', 'string', 'max:255'],
        ];

        if ($this->request->role_requested !== 'teacher') {
            $rules['submitted_student_code'] = ['required', 'string', 'max:50'];
            $rules['submitted_faculty_id'] = ['required', 'integer', 'exists:faculties,id'];
            $rules['submitted_academic_program_id'] = ['required', 'integer', 'exists:academic_programs,id'];
            $rules['submitted_cohort'] = ['required', 'string', 'max:50'];
        } else {
            $rules['submitted_faculty_id'] = ['nullable', 'integer', 'exists:faculties,id'];
        }

        $this->validate($rules, [
            'submitted_name.required' => 'Họ và tên không được để trống.',
            'submitted_student_code.required' => 'MSSV không được để trống.',
            'submitted_faculty_id.required' => 'Vui lòng chọn Khoa.',
            'submitted_academic_program_id.required' => 'Vui lòng chọn ngành.',
        ]);

        $this->validate([
            'evidence_files.*' => ['nullable', 'file', 'mimes:jpeg,png,pdf,webp', 'max:5120'],
            'evidence_notes.*' => ['nullable', 'string', 'max:500'],
        ]);

        DB::transaction(function () use ($user) {
            // Update Verification Request
            $this->request->update([
                'status' => VerificationStatus::PENDING_REVIEW,
                'submitted_name' => $this->submitted_name,
                'submitted_student_code' => $this->request->role_requested !== 'teacher' ? $this->submitted_student_code : null,
                'submitted_faculty_id' => $this->submitted_faculty_id,
                'submitted_academic_program_id' => $this->request->role_requested !== 'teacher' ? $this->submitted_academic_program_id : null,
                'submitted_cohort' => $this->request->role_requested !== 'teacher' ? $this->submitted_cohort : null,
                'submitted_note' => $this->submitted_note,
                'submitted_at' => now(),
            ]);

            // Save new evidences
            foreach ($this->evidence_files as $index => $file) {
                if ($file) {
                    $path = $file->store('verifications/' . $user->id, 'private');

                    $mediaFile = MediaFile::create([
                        'owner_id' => $user->id,
                        'disk' => 'private',
                        'path' => $path,
                        'original_name' => $file->getClientOriginalName(),
                        'mime_type' => $file->getMimeType(),
                        'extension' => $file->getClientOriginalExtension(),
                        'size_bytes' => $file->getSize(),
                        'visibility' => 'private',
                        'file_category' => 'verification_evidence',
                    ]);

                    VerificationEvidence::create([
                        'verification_request_id' => $this->request->id,
                        'media_file_id' => $mediaFile->id,
                        'evidence_type' => $this->evidence_types[$index] ?? 'other',
                        'user_note' => $this->evidence_notes[$index] ?? 'Minh chứng bổ sung.',
                        'status' => 'uploaded',
                    ]);
                }
            }

            // Update user status
            $user->update(['account_status' => AccountStatus::PENDING_VERIFICATION]);
        });

        $this->isCorrecting = false;
        $this->loadRequest();
    }
}; ?>

<div class="max-w-2xl mx-auto py-8 px-4 sm:px-6">
    @if (!$request)
        {{-- Empty State (No submission exists) --}}
        <div class="bg-ue-surface rounded-2xl border border-ue-border shadow-md p-8 text-center">
            <x-ui.icon name="shield" size="xl" class="text-ue-text-muted mx-auto mb-4" />
            <h2 class="text-xl font-bold text-ue-text mb-2">Cần hoàn tất xác thực hồ sơ</h2>
            <p class="text-sm text-ue-text-secondary mb-4 max-w-md mx-auto">
                Email HCMUE của bạn đã được xác minh. Để truy cập đầy đủ UEConnect, bạn cần gửi hồ sơ xác thực danh tính.
            </p>
            @if (auth()->user() && auth()->user()->intended_identity_type === \App\Enums\IdentityType::ALUMNI)
                <p class="text-xs text-ue-text-muted mb-6 max-w-md mx-auto">
                    Bạn có thể dùng email cá nhân nếu email sinh viên đã hết hạn. Vui lòng gửi minh chứng cựu sinh viên để được xét duyệt.
                </p>
            @else
                <p class="mb-4"></p>
            @endif
            <x-ui.button href="{{ route('verification.start') }}" variant="primary" icon="arrow-right" icon-position="right">
                Bắt đầu xác thực
            </x-ui.button>
        </div>
    @else
        {{-- Core statuses --}}
        <div class="bg-ue-surface rounded-2xl border border-ue-border shadow-md overflow-hidden">
            {{-- Header status banner --}}
            @php
                $statusColor = match($request->status) {
                    VerificationStatus::PENDING_REVIEW, VerificationStatus::UNDER_REVIEW, VerificationStatus::RESUBMITTED => 'bg-amber-50 text-amber-800 border-amber-200',
                    VerificationStatus::NEEDS_MORE_INFORMATION => 'bg-blue-50 text-blue-800 border-blue-200',
                    VerificationStatus::REJECTED => 'bg-red-50 text-red-800 border-red-200',
                    default => 'bg-ue-brand-soft text-ue-brand border-ue-brand-border',
                };

                $statusLabel = match($request->status) {
                    VerificationStatus::PENDING_REVIEW => 'Đang chờ giáo vụ phê duyệt',
                    VerificationStatus::UNDER_REVIEW => 'Đang được giáo vụ kiểm tra',
                    VerificationStatus::RESUBMITTED => 'Đã gửi lại - Đang chờ phê duyệt',
                    VerificationStatus::NEEDS_MORE_INFORMATION => 'Cần bổ sung thông tin',
                    VerificationStatus::REJECTED => 'Hồ sơ bị từ chối',
                    VerificationStatus::CONFLICT => 'Mã sinh viên bị xung đột',
                    default => 'Chờ kiểm tra',
                };
            @endphp

            <div class="p-6 border-b border-ue-border flex items-center justify-between {{ $statusColor }}">
                <div class="flex items-center gap-3">
                    <x-ui.icon name="clock" size="lg" />
                    <div>
                        <div class="text-xs uppercase font-bold tracking-wider opacity-75">Trạng thái hồ sơ</div>
                        <h2 class="text-lg font-bold">{{ $statusLabel }}</h2>
                    </div>
                </div>
                <div class="text-xs font-semibold">
                    Cập nhật: {{ $request->updated_at->diffForHumans() }}
                </div>
            </div>

            @if ($isCorrecting)
                {{-- Correction Form --}}
                <div class="p-6 sm:p-8">
                    <h3 class="font-bold text-ue-text mb-4">Cập nhật hồ sơ để gửi lại</h3>
                    
                    <div class="space-y-4 mb-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <x-ui.label for="submitted_name" :required="true">Họ và tên</x-ui.label>
                                <x-ui.input wire:model="submitted_name" id="submitted_name" class="mt-1" />
                                <x-ui.field-error name="submitted_name" />
                            </div>
                            <div>
                                <x-ui.label for="submitted_email">Email liên hệ</x-ui.label>
                                <x-ui.input wire:model="submitted_email" id="submitted_email" class="mt-1" :disabled="true" />
                            </div>
                        </div>

                        @if ($request->role_requested !== 'teacher')
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <x-ui.label for="submitted_student_code" :required="true">Mã số sinh viên (MSSV)</x-ui.label>
                                    <x-ui.input wire:model="submitted_student_code" id="submitted_student_code" class="mt-1" />
                                    <x-ui.field-error name="submitted_student_code" />
                                </div>
                                <div>
                                    <x-ui.label for="submitted_cohort" :required="true">Khóa học / Niên khóa</x-ui.label>
                                    <x-ui.input wire:model="submitted_cohort" id="submitted_cohort" class="mt-1" />
                                    <x-ui.field-error name="submitted_cohort" />
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <x-ui.label for="submitted_faculty_id" :required="true">Khoa đào tạo</x-ui.label>
                                    <x-ui.select wire:model.live="submitted_faculty_id" id="submitted_faculty_id" class="mt-1">
                                        <option value="">-- Chọn Khoa --</option>
                                        @foreach ($this->faculties as $faculty)
                                            <option value="{{ $faculty->id }}">{{ $faculty->name }}</option>
                                        @endforeach
                                    </x-ui.select>
                                    <x-ui.field-error name="submitted_faculty_id" />
                                </div>
                                <div>
                                    <x-ui.label for="submitted_academic_program_id" :required="true">Chuyên ngành</x-ui.label>
                                    <x-ui.select wire:model="submitted_academic_program_id" id="submitted_academic_program_id" class="mt-1" :disabled="!$submitted_faculty_id">
                                        <option value="">-- Chọn chuyên ngành --</option>
                                        @foreach ($this->academicPrograms as $program)
                                            <option value="{{ $program->id }}">{{ $program->name }}</option>
                                        @endforeach
                                    </x-ui.select>
                                    <x-ui.field-error name="submitted_academic_program_id" />
                                </div>
                            </div>
                        @else
                            <div>
                                <x-ui.label for="submitted_faculty_id">Khoa / Phòng ban</x-ui.label>
                                <x-ui.select wire:model="submitted_faculty_id" id="submitted_faculty_id" class="mt-1">
                                    <option value="">-- Chọn Khoa/Phòng ban --</option>
                                    @foreach ($this->faculties as $faculty)
                                        <option value="{{ $faculty->id }}">{{ $faculty->name }}</option>
                                    @endforeach
                                </x-ui.select>
                            </div>
                        @endif

                        {{-- Upload new files --}}
                        <div class="border-t border-ue-border pt-4">
                            <x-ui.label>Tải bổ sung minh chứng mới (Tùy chọn)</x-ui.label>
                            
                            <div class="mt-2 grid grid-cols-1 md:grid-cols-2 gap-4">
                                @for ($i = 0; $i < 2; $i++)
                                    <div class="border border-dashed border-ue-border rounded-xl p-3 text-center hover:border-ue-border-strong hover:bg-ue-surface-hover flex flex-col items-center justify-center min-h-[140px]">
                                        @if (isset($evidence_files[$i]) && $evidence_files[$i])
                                            <div class="text-ue-brand font-semibold text-xs truncate max-w-full mb-2">
                                                {{ $evidence_files[$i]->getClientOriginalName() }}
                                            </div>
                                            <x-ui.select wire:model="evidence_types.{{ $i }}" class="h-8 py-0 px-2 text-xs mb-2">
                                                <option value="student_card">Thẻ sinh viên</option>
                                                <option value="transcript">Bảng điểm</option>
                                                <option value="email_evidence">Email trường</option>
                                                <option value="other">Khác</option>
                                            </x-ui.select>
                                            <x-ui.input wire:model="evidence_notes.{{ $i }}" placeholder="Mô tả..." class="h-8 text-xs" />
                                        @else
                                            <x-ui.icon name="upload" size="md" class="text-ue-text-muted mb-1" />
                                            <label class="cursor-pointer bg-ue-brand-soft text-ue-brand px-2.5 py-1.5 rounded-lg text-xs font-semibold hover:bg-ue-brand-soft-hover transition-colors">
                                                Chọn tệp
                                                <input type="file" wire:model="evidence_files.{{ $i }}" class="hidden" accept="image/*,application/pdf" />
                                            </label>
                                        @endif
                                    </div>
                                @endfor
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 border-t border-ue-border pt-6">
                        <x-ui.button wire:click="cancelCorrection" variant="secondary">Hủy bỏ</x-ui.button>
                        <x-ui.button wire:click="resubmit" variant="primary" icon="check">Gửi lại hồ sơ</x-ui.button>
                    </div>
                </div>
            @else
                {{-- Standard Status Display --}}
                <div class="p-6 sm:p-8">
                    @if ($request->status === VerificationStatus::NEEDS_MORE_INFORMATION)
                        {{-- Instruction Warning Box --}}
                        <div class="mb-6 p-4 bg-blue-50 text-blue-800 rounded-xl border border-blue-200">
                            <div class="flex items-start gap-3">
                                <x-ui.icon name="info" size="md" class="mt-0.5" />
                                <div>
                                    <div class="font-bold text-sm mb-1">Yêu cầu bổ sung từ giáo vụ:</div>
                                    <p class="text-xs leading-relaxed font-semibold">
                                        "{{ $this->latestAction ? $this->latestAction->reason : 'Vui lòng kiểm tra lại thông tin và cung cấp thêm tài liệu minh chứng rõ ràng hơn.' }}"
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if ($request->status === VerificationStatus::REJECTED)
                        {{-- Rejection Box --}}
                        <div class="mb-6 p-4 bg-red-50 text-red-800 rounded-xl border border-red-200">
                            <div class="flex items-start gap-3">
                                <x-ui.icon name="x-circle" size="md" class="mt-0.5" />
                                <div>
                                    <div class="font-bold text-sm mb-1">Lý do từ chối:</div>
                                    <p class="text-xs leading-relaxed font-semibold">
                                        "{{ $this->latestAction ? $this->latestAction->reason : 'Tài liệu minh chứng không hợp lệ hoặc thông tin không trùng khớp.' }}"
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if ($request->status === VerificationStatus::CONFLICT)
                        {{-- Conflict Warning --}}
                        <div class="mb-6 p-4 bg-orange-50 text-orange-800 rounded-xl border border-orange-200">
                            <div class="flex items-start gap-3">
                                <x-ui.icon name="alert" size="md" class="mt-0.5" />
                                <div>
                                    <div class="font-bold text-sm mb-1">Xung đột thông tin định danh:</div>
                                    <p class="text-xs leading-relaxed">
                                        Mã sinh viên của bạn đang bị xung đột hoặc trùng với một tài khoản khác đã được phê duyệt. Vui lòng gửi email hỗ trợ trực tiếp hoặc chờ giáo vụ liên hệ kiểm tra chéo.
                                    </p>
                                </div>
                    @endif

                    @php
                        $hasCameraEvidence = $request->evidences->contains(fn($ev) => $ev->capture_method === \App\Enums\EvidenceCaptureMethod::Camera || ($ev->capture_method && $ev->capture_method->value === 'camera'));
                    @endphp

                    @if ($hasCameraEvidence && in_array($request->status, [\App\Enums\VerificationStatus::PENDING_REVIEW, \App\Enums\VerificationStatus::UNDER_REVIEW, \App\Enums\VerificationStatus::RESUBMITTED]))
                        <div class="mb-6 p-4 bg-blue-50/50 dark:bg-blue-950/10 text-blue-700 dark:text-blue-300 rounded-xl border border-blue-200 dark:border-blue-800/50">
                            <div class="flex items-start gap-3">
                                <x-ui.icon name="info" size="md" class="mt-0.5" />
                                <div>
                                    <div class="font-bold text-sm mb-0.5">ℹ️ Phân tích tự động</div>
                                    <p class="text-xs leading-relaxed font-semibold">
                                        UEConnect đang hỗ trợ kiểm tra thẻ sinh viên bằng AI cục bộ trước khi quản trị viên xét duyệt.
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- Summary Information --}}
                    <div class="space-y-4 mb-8">
                        <h3 class="font-bold text-sm uppercase tracking-wider text-ue-text-muted">Thông tin đã gửi</h3>
                        
                        <div class="bg-ue-surface-subtle border border-ue-border rounded-xl p-4 space-y-3">
                            <div class="grid grid-cols-2 gap-2 text-xs">
                                <div class="text-ue-text-muted font-semibold">Họ và tên:</div>
                                <div class="text-ue-text font-bold">{{ $request->submitted_name }}</div>
                            </div>
                            
                            @if ($request->role_requested !== 'teacher')
                                <div class="grid grid-cols-2 gap-2 text-xs">
                                    <div class="text-ue-text-muted font-semibold">Mã sinh viên (MSSV):</div>
                                    <div class="text-ue-text font-bold">{{ $request->submitted_student_code }}</div>
                                </div>
                                <div class="grid grid-cols-2 gap-2 text-xs">
                                    <div class="text-ue-text-muted font-semibold">Khoa quản lý:</div>
                                    <div class="text-ue-text font-bold">{{ $request->submittedFaculty ? $request->submittedFaculty->name : 'N/A' }}</div>
                                </div>
                                <div class="grid grid-cols-2 gap-2 text-xs">
                                    <div class="text-ue-text-muted font-semibold">Chuyên ngành học:</div>
                                    <div class="text-ue-text font-bold">{{ $request->submittedAcademicProgram ? $request->submittedAcademicProgram->name : 'N/A' }}</div>
                                </div>
                                <div class="grid grid-cols-2 gap-2 text-xs">
                                    <div class="text-ue-text-muted font-semibold">Khóa học / Lớp:</div>
                                    <div class="text-ue-text font-bold">{{ $request->submitted_cohort }}</div>
                                </div>
                            @else
                                <div class="grid grid-cols-2 gap-2 text-xs">
                                    <div class="text-ue-text-muted font-semibold">Khoa / Phòng ban:</div>
                                    <div class="text-ue-text font-bold">{{ $request->submittedFaculty ? $request->submittedFaculty->name : 'N/A' }}</div>
                                </div>
                                <div class="grid grid-cols-2 gap-2 text-xs">
                                    <div class="text-ue-text-muted font-semibold">Chức danh:</div>
                                    <div class="text-ue-text font-bold">{{ $request->submitted_position ?: 'Giảng viên' }}</div>
                                </div>
                                <div class="grid grid-cols-2 gap-2 text-xs">
                                    <div class="text-ue-text-muted font-semibold">Cố vấn học tập:</div>
                                    <div class="text-ue-text font-bold">{{ $request->submitted_is_academic_advisor ? 'Có' : 'Không' }}</div>
                                </div>
                                @if ($request->submitted_is_academic_advisor && filled($request->submitted_advised_class_codes))
                                    <div class="grid grid-cols-2 gap-2 text-xs">
                                        <div class="text-ue-text-muted font-semibold">Lớp cố vấn:</div>
                                        <div class="text-ue-text font-bold whitespace-pre-line">{{ $request->submitted_advised_class_codes }}</div>
                                    </div>
                                @endif
                            @endif

                            @if (!empty($request->submitted_note))
                                <div class="border-t border-ue-border pt-2 grid grid-cols-1 gap-1 text-xs">
                                    <div class="text-ue-text-muted font-semibold">Ghi chú của bạn:</div>
                                    <div class="text-ue-text italic">"{{ $request->submitted_note }}"</div>
                                </div>
                            @endif
                        </div>

                        {{-- Evidences List --}}
                        <div class="space-y-2">
                            <h3 class="font-bold text-xs uppercase tracking-wider text-ue-text-muted">Minh chứng đính kèm</h3>
                            <div class="space-y-2">
                                @foreach ($request->evidences as $evidence)
                                    <div class="flex items-center justify-between p-3 bg-ue-surface border border-ue-border rounded-xl text-xs">
                                        <div class="flex items-center gap-2">
                                            <x-ui.icon name="file" class="text-ue-brand" />
                                            <div>
                                                <div class="font-semibold text-ue-text">
                                                    {{ $evidence->mediaFile ? $evidence->mediaFile->original_name : 'Liên kết minh chứng' }}
                                                </div>
                                                <div class="text-[10px] text-ue-text-muted">
                                                    {{ $evidence->user_note }}
                                                </div>
                                            </div>
                                        </div>
                                        @if ($evidence->evidence_link)
                                            <a href="{{ $evidence->evidence_link }}" target="_blank" class="text-ue-brand font-semibold hover:underline">
                                                Xem liên kết
                                            </a>
                                        @else
                                            <span class="text-[10px] bg-ue-surface-pressed px-2 py-0.5 rounded font-bold uppercase">
                                                Private File
                                            </span>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    {{-- Actions block based on status --}}
                    <div class="flex justify-end gap-3 border-t border-ue-border pt-6">
                        @if ($request->status === VerificationStatus::NEEDS_MORE_INFORMATION)
                            <x-ui.button wire:click="startCorrection" variant="primary" icon="edit">
                                Bổ sung hồ sơ ngay
                            </x-ui.button>
                        @elseif ($request->status === VerificationStatus::REJECTED)
                            <x-ui.button wire:click="restartVerification" variant="primary" icon="plus">
                                Tạo hồ sơ xác thực mới
                            </x-ui.button>
                        @else
                            {{-- Pending review state --}}
                            <div class="text-center w-full text-xs text-ue-text-muted py-2">
                                Hệ thống đang kiểm duyệt thủ công hồ sơ của bạn. Quá trình kiểm tra thường mất từ 1-2 ngày làm việc. Vui lòng quay lại sau.
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    @endif

    {{-- Privacy & Security Guarantee --}}
    <div class="mt-8 p-4 bg-ue-neutral-25 rounded-2xl border border-ue-border flex items-start gap-3">
        <x-ui.icon name="shield" size="md" class="text-ue-brand flex-shrink-0 mt-0.5" />
        <div>
            <h4 class="text-xs font-bold text-ue-neutral-900 mb-1">Cam kết bảo mật dữ liệu minh chứng</h4>
            <p class="text-[11px] text-ue-text-secondary leading-relaxed">
                Tất cả các tài liệu, hình ảnh hoặc thông tin minh chứng được đăng tải phục vụ cho mục đích đối chiếu và phê duyệt vai trò thành viên. Mọi tập tin minh chứng của bạn đều được mã hóa lưu trữ ở phân vùng bảo mật riêng tư và sẽ được <strong>tự động xóa vĩnh viễn khỏi hệ thống</strong> ngay sau khi quá trình đối soát phê duyệt hoàn tất.
            </p>
        </div>
    </div>
</div>
