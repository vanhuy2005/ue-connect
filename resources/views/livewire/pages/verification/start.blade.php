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
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;

new #[Layout('layouts.app')] class extends Component
{
    use WithFileUploads;

    // Active tab / stage
    public int $step = 1;

    // Fields
    public string $role_requested = 'student';
    public string $submitted_name = '';
    public string $submitted_student_code = '';
    public ?int $submitted_faculty_id = null;
    public ?int $submitted_academic_program_id = null;
    public string $submitted_cohort = '';
    public string $submitted_email = '';
    public string $submitted_graduation_year = '';
    public string $submitted_old_student_email = '';
    public string $submitted_position = '';
    public string $submitted_organization = '';
    public string $submitted_note = '';

    // Evidence fields
    public $evidence_files = [];
    public array $evidence_notes = [];
    public array $evidence_types = [];
    public array $evidence_links = [];

    public function mount(): void
    {
        $user = auth()->user();
        if ($user) {
            $this->submitted_name = $user->name;
            $this->submitted_email = $user->email;

            // Pre-fill role requested based on user's intended_identity_type
            if ($user->intended_identity_type) {
                $this->role_requested = match($user->intended_identity_type->value ?? $user->intended_identity_type) {
                    'current_student' => 'student',
                    'teacher_advisor' => 'advisor',
                    'alumni' => 'alumni',
                    default => 'student',
                };
            }

            // If already submitted and pending, redirect to status
            $activeRequest = $user->activeVerificationRequest;
            if ($activeRequest) {
                $this->redirect(route('verification.status'), navigate: true);
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

    public function selectRole(string $role): void
    {
        $this->role_requested = $role;
    }

    public function nextStep(): void
    {
        if ($this->step === 1) {
            $this->validate([
                'role_requested' => ['required', 'string', 'in:student,alumni,advisor'],
            ]);
            $this->step = 2;
            return;
        }

        if ($this->step === 2) {
            $rules = [
                'submitted_name' => ['required', 'string', 'max:255'],
                'submitted_email' => ['required', 'email', 'max:255'],
            ];

            if ($this->role_requested === 'student') {
                $rules['submitted_student_code'] = ['required', 'string', 'max:50'];
                $rules['submitted_faculty_id'] = ['required', 'integer', 'exists:faculties,id'];
                $rules['submitted_academic_program_id'] = ['required', 'integer', 'exists:academic_programs,id'];
                $rules['submitted_cohort'] = ['required', 'string', 'max:50'];
            } elseif ($this->role_requested === 'alumni') {
                $rules['submitted_student_code'] = ['nullable', 'string', 'max:50'];
                $rules['submitted_faculty_id'] = ['required', 'integer', 'exists:faculties,id'];
                $rules['submitted_academic_program_id'] = ['required', 'integer', 'exists:academic_programs,id'];
                $rules['submitted_graduation_year'] = ['required', 'string', 'max:10'];
                $rules['submitted_old_student_email'] = ['nullable', 'email', 'max:255'];
            } elseif ($this->role_requested === 'advisor') {
                $rules['submitted_faculty_id'] = ['nullable', 'integer', 'exists:faculties,id'];
                $rules['submitted_position'] = ['required', 'string', 'max:100'];
            }

            $this->validate($rules, [
                'submitted_name.required' => 'Họ và tên không được để trống.',
                'submitted_student_code.required' => 'Mã số sinh viên (MSSV) không được để trống.',
                'submitted_faculty_id.required' => 'Vui lòng chọn Khoa.',
                'submitted_academic_program_id.required' => 'Vui lòng chọn ngành đào tạo.',
                'submitted_cohort.required' => 'Vui lòng nhập khóa học (ví dụ: K48).',
                'submitted_graduation_year.required' => 'Vui lòng nhập năm tốt nghiệp.',
                'submitted_position.required' => 'Vui lòng nhập chức vụ / vai trò công tác.',
            ]);

            $this->step = 3;
            return;
        }
    }

    public function prevStep(): void
    {
        if ($this->step > 1) {
            $this->step--;
        }
    }

    public function addEvidenceField(): void
    {
        $fileCount = 0;
        foreach ($this->evidence_files as $file) {
            if ($file) {
                $fileCount++;
            }
        }
        $linkCount = count($this->evidence_links);
        
        if ($fileCount + $linkCount >= 3) {
            $this->addError('evidence', 'Bạn chỉ được cung cấp tối đa 3 tài liệu minh chứng.');
            return;
        }

        $this->evidence_links[] = '';
        $this->evidence_notes[] = '';
        $this->evidence_types[] = 'other';
    }

    public function removeEvidenceField(int $index): void
    {
        unset($this->evidence_links[$index]);
        unset($this->evidence_notes[$index]);
        unset($this->evidence_types[$index]);

        $this->evidence_links = array_values($this->evidence_links);
        $this->evidence_notes = array_values($this->evidence_notes);
        $this->evidence_types = array_values($this->evidence_types);
    }

    public function submit(): void
    {
        $user = auth()->user();
        if (!$user) {
            return;
        }

        // Only one active verification request per user
        $activeRequest = $user->activeVerificationRequest;
        if ($activeRequest) {
            $this->addError('evidence', 'Bạn đã có một yêu cầu xác thực đang được xử lý.');
            return;
        }

        // Validate step 3: files & links
        $this->validate([
            'evidence_files.*' => ['nullable', 'file', 'mimes:jpeg,png,pdf,webp', 'max:5120'], // 5MB
            'evidence_links.*' => ['nullable', 'url', 'max:2048'],
            'evidence_notes.*' => ['required_with:evidence_files.*,evidence_links.*', 'nullable', 'string', 'max:500'],
        ], [
            'evidence_files.*.max' => 'Kích thước tệp tin không được vượt quá 5MB.',
            'evidence_files.*.mimes' => 'Hệ thống chỉ chấp nhận tệp ảnh (JPEG, PNG, WEBP) hoặc tệp PDF.',
            'evidence_links.*.url' => 'Liên kết minh chứng phải là một URL hợp lệ (bắt đầu bằng https://).',
            'evidence_notes.*.required_with' => 'Vui lòng điền ghi chú giải thích minh chứng này chứng minh điều gì.',
        ]);

        // Count non-empty files and links
        $fileCount = 0;
        foreach ($this->evidence_files as $file) {
            if ($file) {
                $fileCount++;
            }
        }
        
        $linkCount = 0;
        foreach ($this->evidence_links as $link) {
            if (! empty($link)) {
                $linkCount++;
            }
        }

        $totalItems = $fileCount + $linkCount;

        if ($totalItems < 1) {
            $this->addError('evidence', 'Bạn cần cung cấp ít nhất một tài liệu minh chứng (file tải lên hoặc link).');
            return;
        }

        if ($this->role_requested === 'alumni' && $fileCount < 1) {
            $this->addError('evidence', 'Cựu sinh viên bắt buộc phải tải lên ít nhất một tệp tin minh chứng (ví dụ: bằng tốt nghiệp, bảng điểm).');
            return;
        }

        if ($totalItems > 3) {
            $this->addError('evidence', 'Bạn chỉ được cung cấp tối đa 3 tài liệu minh chứng.');
            return;
        }

        // Collect paths of uploaded files so we can clean up on failure (P0-4)
        $uploadedPaths = [];

        try {
            DB::transaction(function () use ($user, &$uploadedPaths) {
                // 1. Create Verification Request
                $request = VerificationRequest::create([
                    'user_id' => $user->id,
                    'role_requested' => $this->role_requested,
                    'requested_identity_type' => $user->intended_identity_type ?? null,
                    'status' => VerificationStatus::PENDING_REVIEW,
                    'submitted_name' => $this->submitted_name,
                    'submitted_student_code' => $this->role_requested !== 'advisor' ? $this->submitted_student_code : null,
                    'submitted_faculty_id' => $this->submitted_faculty_id,
                    'submitted_academic_program_id' => $this->role_requested !== 'advisor' ? $this->submitted_academic_program_id : null,
                    'submitted_cohort' => $this->role_requested === 'student' ? $this->submitted_cohort : null,
                    'submitted_graduation_year' => $this->role_requested === 'alumni' ? $this->submitted_graduation_year : null,
                    'submitted_email' => $this->submitted_email,
                    'submitted_old_student_email' => $this->role_requested === 'alumni' ? $this->submitted_old_student_email : null,
                    'submitted_note' => $this->submitted_note,
                    'submitted_position' => $this->role_requested === 'advisor' ? $this->submitted_position : null,
                    'submitted_organization' => $this->role_requested === 'advisor' ? $this->submitted_organization : null,
                    'submitted_at' => now(),
                    'expires_at' => now()->addDays(30),
                ]);

                // 2. Upload Files & Create Evidences
                foreach ($this->evidence_files as $index => $file) {
                    if ($file) {
                        $path = $file->store('verifications/'.$user->id, 'private');
                        $uploadedPaths[] = $path;

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
                            'verification_request_id' => $request->id,
                            'media_file_id' => $mediaFile->id,
                            'evidence_type' => $this->evidence_types[$index] ?? 'other',
                            'user_note' => $this->evidence_notes[$index] ?? 'Minh chứng tải lên.',
                            'status' => 'uploaded',
                        ]);
                    }
                }

                // 3. Save Link Evidences
                foreach ($this->evidence_links as $index => $link) {
                    if (! empty($link)) {
                        VerificationEvidence::create([
                            'verification_request_id' => $request->id,
                            'media_file_id' => null,
                            'evidence_type' => $this->evidence_types[$index] ?? 'other',
                            'evidence_link' => $link,
                            'user_note' => $this->evidence_notes[$index] ?? 'Liên kết minh chứng.',
                            'status' => 'uploaded',
                        ]);
                    }
                }

                // 4. Update user status
                $user->update(['account_status' => AccountStatus::PENDING_VERIFICATION]);
            });
        } catch (\Throwable $e) {
            // P0-4: Clean up any files uploaded before the DB failure
            foreach ($uploadedPaths as $orphanedPath) {
                Storage::disk('private')->delete($orphanedPath);
            }

            $this->addError('evidence', 'Đã xảy ra lỗi khi lưu hồ sơ. Vui lòng thử lại.');

            return;
        }

        // Redirect to status page
        $this->redirect(route('verification.status'), navigate: true);
    }
}; ?>

<div class="max-w-3xl mx-auto py-8 px-4 sm:px-6">
    {{-- Wizard progress --}}
    <div class="mb-10">
        <div class="flex items-center justify-between">
            <div class="flex flex-col items-center">
                <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold transition-colors duration-md {{ $step >= 1 ? 'bg-ue-brand text-ue-text-inverse' : 'bg-ue-surface-hover text-ue-text-muted border border-ue-border' }}">1</div>
                <span class="mt-2 text-xs font-semibold {{ $step >= 1 ? 'text-ue-brand' : 'text-ue-text-muted' }}">Chọn vai trò</span>
            </div>
            <div class="flex-1 h-0.5 mx-4 transition-colors duration-md {{ $step >= 2 ? 'bg-ue-brand' : 'bg-ue-border' }}"></div>
            <div class="flex flex-col items-center">
                <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold transition-colors duration-md {{ $step >= 2 ? 'bg-ue-brand text-ue-text-inverse' : 'bg-ue-surface-hover text-ue-text-muted border border-ue-border' }}">2</div>
                <span class="mt-2 text-xs font-semibold {{ $step >= 2 ? 'text-ue-brand' : 'text-ue-text-muted' }}">Nhập thông tin</span>
            </div>
            <div class="flex-1 h-0.5 mx-4 transition-colors duration-md {{ $step >= 3 ? 'bg-ue-brand' : 'bg-ue-border' }}"></div>
            <div class="flex flex-col items-center">
                <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold transition-colors duration-md {{ $step >= 3 ? 'bg-ue-brand text-ue-text-inverse' : 'bg-ue-surface-hover text-ue-text-muted border border-ue-border' }}">3</div>
                <span class="mt-2 text-xs font-semibold {{ $step >= 3 ? 'text-ue-brand' : 'text-ue-text-muted' }}">Minh chứng</span>
            </div>
        </div>
    </div>

    {{-- Main content card --}}
    <div class="bg-ue-surface rounded-2xl border border-ue-border shadow-md overflow-hidden">
        {{-- Step 1: Role Selection --}}
        @if ($step === 1)
            <div class="p-6 sm:p-8">
                <h2 class="text-xl font-bold text-ue-text mb-2">Chọn vai trò của bạn trên UEConnect</h2>
                <p class="text-sm text-ue-text-secondary mb-6">Để đảm bảo an toàn và kết nối đúng đối tượng, vui lòng chọn vai trò chính xác của bạn.</p>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
                    {{-- Student --}}
                    <button wire:click="selectRole('student')" class="flex flex-col items-start p-5 rounded-xl border text-left transition-all duration-sm ue-focus-ring {{ $role_requested === 'student' ? 'border-ue-brand bg-ue-brand-soft shadow-sm' : 'border-ue-border hover:border-ue-border-strong hover:bg-ue-surface-hover' }}">
                        <div class="p-3 bg-ue-brand text-ue-text-inverse rounded-lg mb-4">
                            <x-ui.icon name="user" size="lg" />
                        </div>
                        <h3 class="font-bold text-ue-text mb-1">Sinh viên</h3>
                        <p class="text-xs text-ue-text-secondary leading-relaxed">Đang học tập tại Trường Đại học Sư phạm TP.HCM.</p>
                    </button>

                    {{-- Alumni --}}
                    <button wire:click="selectRole('alumni')" class="flex flex-col items-start p-5 rounded-xl border text-left transition-all duration-sm ue-focus-ring {{ $role_requested === 'alumni' ? 'border-ue-brand bg-ue-brand-soft shadow-sm' : 'border-ue-border hover:border-ue-border-strong hover:bg-ue-surface-hover' }}">
                        <div class="p-3 bg-ue-brand text-ue-text-inverse rounded-lg mb-4">
                            <x-ui.icon name="graduation-cap" size="lg" />
                        </div>
                        <h3 class="font-bold text-ue-text mb-1">Cựu sinh viên</h3>
                        <p class="text-xs text-ue-text-secondary leading-relaxed">Đã tốt nghiệp từ Trường Đại học Sư phạm TP.HCM.</p>
                    </button>

                    {{-- Advisor --}}
                    <button wire:click="selectRole('advisor')" class="flex flex-col items-start p-5 rounded-xl border text-left transition-all duration-sm ue-focus-ring {{ $role_requested === 'advisor' ? 'border-ue-brand bg-ue-brand-soft shadow-sm' : 'border-ue-border hover:border-ue-border-strong hover:bg-ue-surface-hover' }}">
                        <div class="p-3 bg-ue-brand text-ue-text-inverse rounded-lg mb-4">
                            <x-ui.icon name="shield-check" size="lg" />
                        </div>
                        <h3 class="font-bold text-ue-text mb-1">Cố vấn / Giảng viên</h3>
                        <p class="text-xs text-ue-text-secondary leading-relaxed">Giảng viên hoặc cố vấn học tập chính thức của trường.</p>
                    </button>
                </div>

                <div class="flex justify-end border-t border-ue-border pt-6">
                    <x-ui.button wire:click="nextStep" variant="primary" icon="arrow-right" icon-position="right">
                        Tiếp tục
                    </x-ui.button>
                </div>
            </div>
        @endif

        {{-- Step 2: Information Input --}}
        @if ($step === 2)
            <div class="p-6 sm:p-8">
                <h2 class="text-xl font-bold text-ue-text mb-2">Nhập thông tin định danh</h2>
                <p class="text-sm text-ue-text-secondary mb-6">Cung cấp chính xác thông tin để bộ phận giáo vụ kiểm tra và phê duyệt nhanh chóng.</p>

                <div class="space-y-4 mb-8">
                    {{-- Common fields --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <x-ui.label for="submitted_name" :required="true">Họ và tên hợp lệ</x-ui.label>
                            <x-ui.input wire:model="submitted_name" id="submitted_name" placeholder="Ví dụ: Nguyễn Văn A" class="mt-1" />
                            <x-ui.field-error name="submitted_name" />
                        </div>
                        <div>
                            <x-ui.label for="submitted_email" :required="true">Email liên hệ</x-ui.label>
                            <x-ui.input wire:model="submitted_email" id="submitted_email" type="email" class="mt-1" :disabled="true" />
                            <x-ui.field-error name="submitted_email" />
                        </div>
                    </div>

                    @if ($role_requested === 'student')
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <x-ui.label for="submitted_student_code" :required="true">Mã số sinh viên (MSSV)</x-ui.label>
                                <x-ui.input wire:model="submitted_student_code" id="submitted_student_code" placeholder="Nhập MSSV của bạn" class="mt-1" />
                                <x-ui.field-error name="submitted_student_code" />
                            </div>
                            <div>
                                <x-ui.label for="submitted_cohort" :required="true">Khóa học / Niên khóa</x-ui.label>
                                <x-ui.input wire:model="submitted_cohort" id="submitted_cohort" placeholder="Ví dụ: K48 hoặc 2022-2026" class="mt-1" />
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
                    @elseif ($role_requested === 'alumni')
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <x-ui.label for="submitted_student_code">Mã số sinh viên cũ (MSSV - Nếu nhớ)</x-ui.label>
                                <x-ui.input wire:model="submitted_student_code" id="submitted_student_code" placeholder="Nhập MSSV cũ nếu nhớ" class="mt-1" />
                                <x-ui.field-error name="submitted_student_code" />
                            </div>
                            <div>
                                <x-ui.label for="submitted_graduation_year" :required="true">Năm tốt nghiệp</x-ui.label>
                                <x-ui.input wire:model="submitted_graduation_year" id="submitted_graduation_year" placeholder="Ví dụ: 2022" class="mt-1" />
                                <x-ui.field-error name="submitted_graduation_year" />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <x-ui.label for="submitted_old_student_email">Email sinh viên cũ (Nếu nhớ)</x-ui.label>
                                <x-ui.input wire:model="submitted_old_student_email" id="submitted_old_student_email" type="email" placeholder="Ví dụ: mssv@student.hcmue.edu.vn" class="mt-1" />
                                <x-ui.field-error name="submitted_old_student_email" />
                            </div>
                            <div>
                                <x-ui.label for="submitted_faculty_id" :required="true">Khoa đã học</x-ui.label>
                                <x-ui.select wire:model.live="submitted_faculty_id" id="submitted_faculty_id" class="mt-1">
                                    <option value="">-- Chọn Khoa --</option>
                                    @foreach ($this->faculties as $faculty)
                                        <option value="{{ $faculty->id }}">{{ $faculty->name }}</option>
                                    @endforeach
                                </x-ui.select>
                                <x-ui.field-error name="submitted_faculty_id" />
                            </div>
                        </div>

                        <div>
                            <x-ui.label for="submitted_academic_program_id" :required="true">Ngành đào tạo đã tốt nghiệp</x-ui.label>
                            <x-ui.select wire:model="submitted_academic_program_id" id="submitted_academic_program_id" class="mt-1" :disabled="!$submitted_faculty_id">
                                <option value="">-- Chọn chuyên ngành --</option>
                                @foreach ($this->academicPrograms as $program)
                                    <option value="{{ $program->id }}">{{ $program->name }}</option>
                                @endforeach
                            </x-ui.select>
                            <x-ui.field-error name="submitted_academic_program_id" />
                        </div>
                    @elseif ($role_requested === 'advisor')
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <x-ui.label for="submitted_faculty_id">Khoa / Phòng ban công tác</x-ui.label>
                                <x-ui.select wire:model="submitted_faculty_id" id="submitted_faculty_id" class="mt-1">
                                    <option value="">-- Chọn Khoa/Phòng ban (Tùy chọn) --</option>
                                    @foreach ($this->faculties as $faculty)
                                        <option value="{{ $faculty->id }}">{{ $faculty->name }}</option>
                                    @endforeach
                                </x-ui.select>
                                <x-ui.field-error name="submitted_faculty_id" />
                            </div>
                            <div>
                                <x-ui.label for="submitted_position" :required="true">Chức vụ / Vai trò</x-ui.label>
                                <x-ui.input wire:model="submitted_position" id="submitted_position" placeholder="Ví dụ: Giảng viên, Cố vấn học tập" class="mt-1" />
                                <x-ui.field-error name="submitted_position" />
                            </div>
                        </div>
                    @endif

                    <div>
                        <x-ui.label for="submitted_note">Ghi chú gửi tới giáo vụ</x-ui.label>
                        <x-ui.textarea wire:model="submitted_note" id="submitted_note" rows="3" placeholder="Nhập thêm ghi chú hoặc giải trình nếu có..." class="mt-1" />
                        <x-ui.field-error name="submitted_note" />
                    </div>
                </div>

                <div class="flex justify-between border-t border-ue-border pt-6">
                    <x-ui.button wire:click="prevStep" variant="secondary" icon="arrow-left">
                        Quay lại
                    </x-ui.button>
                    <x-ui.button wire:click="nextStep" variant="primary" icon="arrow-right" icon-position="right">
                        Tiếp tục
                    </x-ui.button>
                </div>
            </div>
        @endif

        {{-- Step 3: Evidences --}}
        @if ($step === 3)
            <div class="p-6 sm:p-8">
                <h2 class="text-xl font-bold text-ue-text mb-2">Tải lên minh chứng định danh</h2>
                <p class="text-sm text-ue-text-secondary mb-6">Tải lên hình ảnh thẻ sinh viên, bảng điểm, bằng tốt nghiệp hoặc chụp màn hình email trường để chứng minh vai trò học viên.</p>

                @if ($errors->has('evidence'))
                    <div class="mb-4 p-3 bg-red-50 text-red-700 text-sm rounded-lg border border-red-200">
                        {{ $errors->first('evidence') }}
                    </div>
                @endif

                <div class="space-y-6 mb-8">
                    {{-- File Drop Area (Tải lên tối đa 3 files) --}}
                    <div>
                        <x-ui.label :required="true">Tập tin minh chứng (Ảnh/PDF, tối đa 5MB)</x-ui.label>
                        
                        <div class="mt-2 grid grid-cols-1 md:grid-cols-3 gap-4">
                            @for ($i = 0; $i < 3; $i++)
                                <div class="border border-dashed border-ue-border rounded-xl p-4 text-center hover:border-ue-border-strong hover:bg-ue-surface-hover transition-all duration-sm flex flex-col items-center justify-center min-h-[160px]">
                                    @if (isset($evidence_files[$i]) && $evidence_files[$i])
                                        <div class="text-ue-brand font-semibold text-xs truncate max-w-full mb-2">
                                            {{ $evidence_files[$i]->getClientOriginalName() }}
                                        </div>
                                        <div class="text-[10px] text-ue-text-muted mb-4">
                                            {{ number_format($evidence_files[$i]->getSize() / 1024 / 1024, 2) }} MB
                                        </div>
                                        
                                        <div class="w-full">
                                            <x-ui.label class="text-left text-xs mb-1" :required="true">Loại minh chứng</x-ui.label>
                                            <x-ui.select wire:model="evidence_types.{{ $i }}" class="h-8 py-0 px-2 text-xs mb-2">
                                                <option value="student_card">Thẻ sinh viên</option>
                                                <option value="admission_letter">Giấy báo nhập học</option>
                                                <option value="transcript">Bảng điểm</option>
                                                <option value="graduation_certificate">Bằng tốt nghiệp</option>
                                                <option value="email_evidence">Chụp màn hình email trường</option>
                                                <option value="other">Minh chứng khác</option>
                                            </x-ui.select>

                                            <x-ui.label class="text-left text-xs mb-1" :required="true">Mô tả chi tiết</x-ui.label>
                                            <x-ui.input wire:model="evidence_notes.{{ $i }}" placeholder="Thẻ SV mặt trước..." class="h-8 text-xs" />
                                        </div>
                                    @else
                                        <x-ui.icon name="upload" size="lg" class="text-ue-text-muted mb-2" />
                                        <label class="cursor-pointer bg-ue-brand-soft text-ue-brand px-3 py-1.5 rounded-lg text-xs font-semibold hover:bg-ue-brand-soft-hover transition-colors">
                                            Chọn tệp
                                            <input type="file" wire:model="evidence_files.{{ $i }}" class="hidden" accept="image/*,application/pdf" />
                                        </label>
                                        <span class="text-[10px] text-ue-text-muted mt-2">JPEG, PNG, WEBP, PDF tối đa 5MB</span>
                                    @endif
                                </div>
                            @endfor
                        </div>
                        <x-ui.field-error name="evidence_files.*" />
                        <x-ui.field-error name="evidence_notes.*" />
                    </div>

                    {{-- Link Evidences --}}
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <x-ui.label>Cung cấp liên kết minh chứng khác (Tùy chọn)</x-ui.label>
                            <x-ui.button wire:click="addEvidenceField" variant="ghost" size="xs" icon="plus">Thêm link</x-ui.button>
                        </div>

                        <div class="space-y-3">
                            @foreach ($evidence_links as $index => $link)
                                <div class="flex gap-3 items-end bg-ue-surface-subtle p-3 rounded-xl border border-ue-border">
                                    <div class="flex-1 space-y-2">
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                            <div>
                                                <x-ui.label class="text-xs">Liên kết URL</x-ui.label>
                                                <x-ui.input wire:model="evidence_links.{{ $index }}" placeholder="https://..." class="h-8 text-xs mt-1" />
                                            </div>
                                            <div>
                                                <x-ui.label class="text-xs" :required="true">Mô tả liên kết</x-ui.label>
                                                <x-ui.input wire:model="evidence_notes.{{ $index }}" placeholder="Mô tả liên kết này chứa gì..." class="h-8 text-xs mt-1" />
                                            </div>
                                        </div>
                                    </div>
                                    <x-ui.button wire:click="removeEvidenceField({{ $index }})" variant="danger-outline" size="sm" class="h-8 min-h-0">
                                        Xóa
                                    </x-ui.button>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="flex justify-between border-t border-ue-border pt-6">
                    <x-ui.button wire:click="prevStep" variant="secondary" icon="arrow-left">
                        Quay lại
                    </x-ui.button>
                    <x-ui.button wire:click="submit" variant="primary" icon="check" icon-position="right">
                        Gửi xác thực
                    </x-ui.button>
                </div>
            </div>
        @endif
    </div>
</div>
