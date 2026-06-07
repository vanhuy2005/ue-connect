<?php

use App\Enums\AccountStatus;
use App\Enums\VerificationStatus;
use App\Enums\EvidenceCaptureMethod;
use App\Enums\EvidenceCaptureStatus;
use App\Models\Faculty;
use App\Models\AcademicProgram;
use App\Models\VerificationRequest;
use App\Models\VerificationEvidence;
use App\Models\EvidenceCaptureSession;
use App\Models\MediaFile;
use App\Support\Auth\AllowedEmailDomain;
use App\Jobs\AnalyzeStudentCardEvidenceJob;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Renderless;
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
    public bool $submitted_is_academic_advisor = false;
    public string $submitted_advised_class_codes = '';
    public string $submitted_note = '';

    // Evidence fields
    public $evidence_files = [];
    public array $evidence_notes = [];
    public array $evidence_types = [];
    public array $evidence_links = [];

    // Camera AI verification
    public string $evidenceMethod = ''; // 'camera' | 'upload'
    public string $capturedImageData = ''; // base64 from camera
    public string $captureSessionStatus = ''; // ui state tracking
    public ?string $captureSessionToken = null;

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
                    'teacher_advisor' => 'teacher',
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

    private function lockedRoleForCurrentUser(): string
    {
        $identityType = auth()->user()?->intended_identity_type;
        $identityType = $identityType?->value ?? $identityType;

        return match ($identityType) {
            'current_student' => 'student',
            'teacher_advisor' => 'teacher',
            'alumni' => 'alumni',
            default => 'student',
        };
    }

    private function syncLockedRole(): void
    {
        $this->role_requested = $this->lockedRoleForCurrentUser();
    }

    private function domainRule(array $allowedDomains, string $message): \Closure
    {
        return function ($attribute, $value, $fail) use ($allowedDomains, $message) {
            if (! AllowedEmailDomain::check((string) $value, $allowedDomains)) {
                $fail($message);
            }
        };
    }

    /**
     * @return array<string, string>
     */
    public function evidenceTypeOptions(): array
    {
        return match ($this->role_requested) {
            'student' => [
                'student_card' => 'Thẻ sinh viên',
                'admission_letter' => 'Giấy báo nhập học',
                'transcript' => 'Bảng điểm',
                'student_email_screenshot' => 'Chụp màn hình email sinh viên',
                'other' => 'Minh chứng khác',
            ],
            'alumni' => [
                'graduation_certificate' => 'Bằng tốt nghiệp',
                'transcript' => 'Bảng điểm',
                'old_student_card' => 'Thẻ sinh viên cũ',
                'student_email_screenshot' => 'Chụp màn hình email sinh viên cũ',
                'other' => 'Minh chứng khác',
            ],
            'teacher' => [
                'teacher_email_screenshot' => 'Chụp màn hình email công vụ',
                'staff_card' => 'Thẻ viên chức / thẻ giảng viên',
                'appointment_decision' => 'Quyết định phân công / bổ nhiệm',
                'faculty_profile' => 'Trang hồ sơ trên website khoa',
                'academic_advisor_assignment' => 'Thông tin phân công cố vấn học tập',
                'other' => 'Minh chứng khác',
            ],
            default => ['other' => 'Minh chứng khác'],
        };
    }

    private function normalizeAndValidateAdvisedClasses(): void
    {
        if (! $this->submitted_is_academic_advisor) {
            $this->submitted_advised_class_codes = '';

            return;
        }

        $classCodes = array_values(array_filter(array_map(
            fn (string $classCode): string => Str::upper(trim($classCode)),
            preg_split('/[\r\n,;]+/', $this->submitted_advised_class_codes) ?: []
        )));

        foreach ($classCodes as $classCode) {
            if (! preg_match('/^\d{2}\.[A-Z0-9]{2,12}[A-D]$/u', $classCode)) {
                throw ValidationException::withMessages([
                    'submitted_advised_class_codes' => 'Tên lớp cố vấn cần đúng định dạng như 49.CNTTD hoặc 50.CNTTA.',
                ]);
            }
        }

        $this->submitted_advised_class_codes = implode("\n", array_unique($classCodes));
    }

    public function selectRole(string $role): void
    {
        $lockedRole = $this->lockedRoleForCurrentUser();

        if ($role !== $lockedRole) {
            $this->addError('role_requested', 'Vai trò xác thực đã được khóa theo lựa chọn lúc đăng ký.');

            return;
        }

        $this->role_requested = $lockedRole;
    }

    public function nextStep(): void
    {
        if ($this->step === 1) {
            $this->syncLockedRole();

            $this->validate([
                'role_requested' => ['required', 'string', 'in:student,alumni,teacher'],
            ]);
            $this->step = 2;
            return;
        }

        if ($this->step === 2) {
            $this->syncLockedRole();

            $rules = [
                'submitted_name' => ['required', 'string', 'max:255'],
                'submitted_email' => [
                    'required',
                    'email',
                    'max:255',
                    function ($attribute, $value, $fail) {
                        $userEmail = auth()->user()?->email;

                        if ($userEmail && strcasecmp((string) $value, $userEmail) !== 0) {
                            $fail('Email xác thực phải trùng với email đã đăng ký.');
                        }
                    },
                ],
            ];

            if ($this->role_requested === 'student') {
                $rules['submitted_email'][] = $this->domainRule(['student.hcmue.edu.vn'], 'Sinh viên phải dùng email dạng mssv@student.hcmue.edu.vn.');
                $rules['submitted_student_code'] = ['required', 'string', 'max:50'];
                $rules['submitted_faculty_id'] = ['required', 'integer', 'exists:faculties,id'];
                $rules['submitted_academic_program_id'] = ['required', 'integer', 'exists:academic_programs,id'];
                $rules['submitted_cohort'] = ['required', 'string', 'max:50'];
            } elseif ($this->role_requested === 'alumni') {
                $rules['submitted_student_code'] = ['nullable', 'string', 'max:50'];
                $rules['submitted_faculty_id'] = ['required', 'integer', 'exists:faculties,id'];
                $rules['submitted_academic_program_id'] = ['required', 'integer', 'exists:academic_programs,id'];
                $rules['submitted_graduation_year'] = ['required', 'string', 'max:10'];
                $rules['submitted_old_student_email'] = ['nullable', 'email', 'max:255', $this->domainRule(['student.hcmue.edu.vn'], 'Email sinh viên cũ phải có dạng mssv@student.hcmue.edu.vn.')];
            } elseif ($this->role_requested === 'teacher') {
                $rules['submitted_email'][] = function ($attribute, $value, $fail) {
                    $studentDomains = config('ueconnect.identity.student_email_domains', ['student.hcmue.edu.vn']);
                    if (AllowedEmailDomain::check((string) $value, $studentDomains)) {
                        $fail('Email sinh viên không thể dùng để xác thực vai trò giảng viên.');
                    }
                };
                $rules['submitted_faculty_id'] = ['nullable', 'integer', 'exists:faculties,id'];
                $rules['submitted_position'] = ['nullable', 'string', 'max:100'];
                $rules['submitted_is_academic_advisor'] = ['boolean'];
                $rules['submitted_advised_class_codes'] = ['nullable', 'string', 'max:500'];
            }

            $this->validate($rules, [
                'submitted_name.required' => 'Họ và tên không được để trống.',
                'submitted_student_code.required' => 'Mã số sinh viên (MSSV) không được để trống.',
                'submitted_faculty_id.required' => 'Vui lòng chọn Khoa.',
                'submitted_academic_program_id.required' => 'Vui lòng chọn ngành đào tạo.',
                'submitted_cohort.required' => 'Vui lòng nhập khóa học (ví dụ: K48).',
                'submitted_graduation_year.required' => 'Vui lòng nhập năm tốt nghiệp.',
            ]);

            if ($this->role_requested === 'teacher') {
                $this->normalizeAndValidateAdvisedClasses();
            }

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

    public function updatedEvidenceMethod(string $value): void
    {
        if ($value === 'camera') {
            $this->createCaptureSession();
        }
    }

    public function createCaptureSession(): void
    {
        $user = auth()->user();
        if (!$user) {
            return;
        }

        $token = Str::random(40);
        $this->captureSessionToken = $token;

        EvidenceCaptureSession::create([
            'user_id' => $user->id,
            'session_token_hash' => hash('sha256', $token),
            'status' => EvidenceCaptureStatus::Started,
            'required_evidence_type' => 'student_card',
            'started_at' => now(),
            'expires_at' => now()->addMinutes(config('ai-verification.capture.session_ttl_minutes', 10)),
            'attempt_count' => 0,
            'client_user_agent' => request()->userAgent(),
        ]);
    }

    /** Store confirmed camera image data from the client-side camera UI. */
    #[Renderless]
    public function setCapturedImage(string $base64Data): void
    {
        $this->capturedImageData = $base64Data;
    }

    public function submit(): void
    {
        $user = auth()->user();
        if (!$user) {
            return;
        }

        $privateDisk = config('media.private_disk', 'private');

        $this->syncLockedRole();

        // Only one active verification request per user
        $activeRequest = $user->activeVerificationRequest;
        if ($activeRequest) {
            $this->addError('evidence', 'Bạn đã có một yêu cầu xác thực đang được xử lý.');
            return;
        }

        // Camera method validation: must have captured image if student chose camera
        if ($this->role_requested === 'student' && $this->evidenceMethod === 'camera' && empty($this->capturedImageData)) {
            $this->addError('evidence', 'Vui lòng chụp ảnh thẻ sinh viên trước khi gửi.');
            return;
        }

        // Validate step 3: files & links if not camera
        if ($this->role_requested !== 'student' || $this->evidenceMethod !== 'camera') {
            $this->validate([
                'evidence_files.*' => ['nullable', 'file', 'mimes:jpeg,png,pdf,webp', 'max:5120'], // 5MB
                'evidence_links.*' => ['nullable', 'url', 'max:2048'],
                'evidence_types.*' => ['nullable', 'string', Rule::in(array_keys($this->evidenceTypeOptions()))],
                'evidence_notes.*' => ['required_with:evidence_files.*,evidence_links.*', 'nullable', 'string', 'max:500'],
            ], [
                'evidence_files.*.max' => 'Kích thước tệp tin không được vượt quá 5MB.',
                'evidence_files.*.mimes' => 'Hệ thống chỉ chấp nhận tệp ảnh (JPEG, PNG, WEBP) hoặc tệp PDF.',
                'evidence_links.*.url' => 'Liên kết minh chứng phải là một URL hợp lệ (bắt đầu bằng https://).',
                'evidence_notes.*.required_with' => 'Vui lòng điền ghi chú giải thích minh chứng này chứng minh điều gì.',
            ]);
        }

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

        // For camera method, camera image counts as evidence
        $cameraCount = ($this->role_requested === 'student' && $this->evidenceMethod === 'camera' && !empty($this->capturedImageData)) ? 1 : 0;

        $totalItems = $fileCount + $linkCount + $cameraCount;

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

        $session = null;
        $capturedPath = null;
        $capturedMedia = null;

        // Process camera image first before transaction to handle decoding issues safely
        if ($this->role_requested === 'student' && $this->evidenceMethod === 'camera') {
            if (!$this->captureSessionToken) {
                $this->addError('evidence', 'Phiên chụp ảnh không hợp lệ.');
                return;
            }

            $sessionHash = hash('sha256', $this->captureSessionToken);
            $session = EvidenceCaptureSession::where('session_token_hash', $sessionHash)
                ->where('user_id', $user->id)
                ->first();

            if (!$session || !$session->isActive()) {
                $this->addError('evidence', 'Phiên chụp ảnh đã hết hạn hoặc không hợp lệ. Vui lòng thử lại.');
                return;
            }

            if (!$session->canAttempt()) {
                $this->addError('evidence', 'Bạn đã vượt quá số lần chụp ảnh cho phép.');
                return;
            }

            $session->increment('attempt_count');

            try {
                $base64Data = $this->capturedImageData;
                if (!preg_match('/^data:image\/(\w+);base64,/', $base64Data, $typeMatches)) {
                    throw ValidationException::withMessages([
                        'evidence' => 'Dữ liệu hình ảnh không đúng định dạng base64.',
                    ]);
                }
                
                $extension = strtolower($typeMatches[1]);
                if (!in_array($extension, ['jpg', 'jpeg', 'png', 'webp'])) {
                    throw ValidationException::withMessages([
                        'evidence' => 'Hệ thống chỉ chấp nhận ảnh định dạng JPEG, PNG, WEBP.',
                    ]);
                }

                $rawImage = base64_decode(substr($base64Data, strpos($base64Data, ',') + 1));
                if ($rawImage === false) {
                    throw ValidationException::withMessages([
                        'evidence' => 'Không thể giải mã dữ liệu hình ảnh.',
                    ]);
                }

                // P0-1: Server-side validation of real image
                $imageInfo = @getimagesizefromstring($rawImage);
                if ($imageInfo === false) {
                    throw ValidationException::withMessages([
                        'evidence' => 'Ảnh chụp không hợp lệ. Vui lòng chụp lại.',
                    ]);
                }

                $mimeType = $imageInfo['mime'] ?? null;
                if (!in_array($mimeType, ['image/jpeg', 'image/png', 'image/webp'])) {
                    throw ValidationException::withMessages([
                        'evidence' => 'Hệ thống chỉ chấp nhận định dạng ảnh JPEG, PNG, WEBP.',
                    ]);
                }

                [$width, $height] = $imageInfo;
                $minWidth = config('ai-verification.capture.min_width', 640);
                $minHeight = config('ai-verification.capture.min_height', 360);

                if ($width < $minWidth || $height < $minHeight) {
                    throw ValidationException::withMessages([
                        'evidence' => "Ảnh chụp quá nhỏ ($width x $height). Vui lòng chụp lại rõ hơn (tối thiểu {$minWidth}x{$minHeight}px).",
                    ]);
                }

                $mimeToExtension = [
                    'image/jpeg' => 'jpg',
                    'image/png' => 'png',
                    'image/webp' => 'webp',
                ];
                $targetExtension = $mimeToExtension[$mimeType] ?? 'jpg';

                $capturedPath = 'verifications/' . $user->id . '/captures/' . Str::uuid() . '.' . $targetExtension;
                Storage::disk($privateDisk)->put($capturedPath, $rawImage);
                
                $capturedMedia = [
                    'owner_id' => $user->id,
                    'disk' => $privateDisk,
                    'path' => $capturedPath,
                    'original_name' => 'camera_capture_' . time() . '.' . $targetExtension,
                    'mime_type' => $mimeType ?? 'image/jpeg',
                    'extension' => $targetExtension,
                    'size_bytes' => strlen($rawImage),
                    'visibility' => 'private',
                    'file_category' => 'verification_evidence',
                ];
            } catch (ValidationException $e) {
                if ($session) {
                    $session->update([
                        'status' => EvidenceCaptureStatus::Failed,
                        'failed_at' => now(),
                    ]);
                }
                throw $e;
            } catch (\Throwable $e) {
                if ($session) {
                    $session->update([
                        'status' => EvidenceCaptureStatus::Failed,
                        'failed_at' => now(),
                    ]);
                }
                $this->addError('evidence', 'Không thể xử lý ảnh chụp: ' . $e->getMessage());
                return;
            }
        }

        // Collect paths of uploaded files so we can clean up on failure (P0-4)
        $uploadedPaths = [];
        if ($capturedPath) {
            $uploadedPaths[] = $capturedPath;
        }

        try {
            DB::transaction(function () use ($user, &$uploadedPaths, $session, $capturedMedia, $privateDisk) {
                // 1. Create Verification Request
                $request = VerificationRequest::create([
                    'user_id' => $user->id,
                    'role_requested' => $this->role_requested,
                    'requested_identity_type' => $user->intended_identity_type ?? null,
                    'status' => VerificationStatus::PENDING_REVIEW,
                    'submitted_name' => $this->submitted_name,
                    'submitted_student_code' => $this->role_requested !== 'teacher' ? $this->submitted_student_code : null,
                    'submitted_faculty_id' => $this->submitted_faculty_id,
                    'submitted_academic_program_id' => $this->role_requested !== 'teacher' ? $this->submitted_academic_program_id : null,
                    'submitted_cohort' => $this->role_requested === 'student' ? $this->submitted_cohort : null,
                    'submitted_graduation_year' => $this->role_requested === 'alumni' ? $this->submitted_graduation_year : null,
                    'submitted_email' => $this->submitted_email,
                    'submitted_old_student_email' => $this->role_requested === 'alumni' ? $this->submitted_old_student_email : null,
                    'submitted_note' => $this->submitted_note,
                    'submitted_position' => $this->role_requested === 'teacher' ? ($this->submitted_position ?: 'Giảng viên') : null,
                    'submitted_organization' => $this->role_requested === 'teacher' ? $this->submitted_organization : null,
                    'submitted_is_academic_advisor' => $this->role_requested === 'teacher' && $this->submitted_is_academic_advisor,
                    'submitted_advised_class_codes' => $this->role_requested === 'teacher' ? $this->submitted_advised_class_codes : null,
                    'submitted_at' => now(),
                    'expires_at' => now()->addDays(30),
                ]);

                if ($session) {
                    $session->update([
                        'verification_request_id' => $request->id,
                        'status' => EvidenceCaptureStatus::Completed,
                        'completed_at' => now(),
                    ]);
                }

                // 2. Process camera evidence
                if ($capturedMedia) {
                    $mediaFile = MediaFile::create($capturedMedia);
                    
                    VerificationEvidence::create([
                        'verification_request_id' => $request->id,
                        'media_file_id' => $mediaFile->id,
                        'evidence_type' => 'student_card',
                        'user_note' => $this->submitted_note ?: 'Ảnh chụp thẻ sinh viên từ camera.',
                        'status' => 'uploaded',
                        'capture_method' => EvidenceCaptureMethod::Camera,
                        'captured_at' => now(),
                        'capture_session_id' => $session ? $session->id : null,
                        'client_user_agent' => request()->userAgent(),
                    ]);
                }

                // 3. Upload Files & Create Evidences
                foreach ($this->evidence_files as $index => $file) {
                    if ($file) {
                        $path = $file->store('verifications/'.$user->id, $privateDisk);
                        $uploadedPaths[] = $path;

                        $mediaFile = MediaFile::create([
                            'owner_id' => $user->id,
                            'disk' => $privateDisk,
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
                            'evidence_type' => $this->evidence_types[$index] ?? array_key_first($this->evidenceTypeOptions()),
                            'user_note' => $this->evidence_notes[$index] ?? 'Minh chứng tải lên.',
                            'status' => 'uploaded',
                            'capture_method' => EvidenceCaptureMethod::UploadFallback,
                            'captured_at' => null,
                            'client_user_agent' => request()->userAgent(),
                        ]);
                    }
                }

                // 4. Save Link Evidences
                foreach ($this->evidence_links as $index => $link) {
                    if (! empty($link)) {
                        VerificationEvidence::create([
                            'verification_request_id' => $request->id,
                            'media_file_id' => null,
                            'evidence_type' => $this->evidence_types[$index] ?? array_key_first($this->evidenceTypeOptions()),
                            'evidence_link' => $link,
                            'user_note' => $this->evidence_notes[$index] ?? 'Liên kết minh chứng.',
                            'status' => 'uploaded',
                            'capture_method' => EvidenceCaptureMethod::UploadFallback,
                            'captured_at' => null,
                            'client_user_agent' => request()->userAgent(),
                        ]);
                    }
                }

                // 5. Update user status
                $user->update(['account_status' => AccountStatus::PENDING_VERIFICATION]);

                // 6. Dispatch AI analysis job if eligible
                if (
                    config('ai-verification.enabled')
                    && $this->role_requested === 'student'
                ) {
                    $cameraRequired = config('ai-verification.camera_capture_required_for_ai', true);
                    
                    if (!$cameraRequired || $this->evidenceMethod === 'camera') {
                        $evidenceQuery = $request->evidences()->where('evidence_type', 'student_card');
                        
                        if ($cameraRequired) {
                            $evidenceQuery->where('capture_method', EvidenceCaptureMethod::Camera);
                        }
                        
                        $evidence = $evidenceQuery->first();
                        
                        if ($evidence) {
                            AnalyzeStudentCardEvidenceJob::dispatch($evidence->id)->afterCommit();
                        }
                    }
                }
            });
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            // Clean up files on error
            foreach ($uploadedPaths as $orphanedPath) {
                Storage::disk($privateDisk)->delete($orphanedPath);
            }

            if ($session) {
                $session->update([
                    'status' => EvidenceCaptureStatus::Failed,
                    'failed_at' => now(),
                ]);
            }

            Log::error('Verification submit failed: ' . $e->getMessage());
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
                @php
                    $roleMeta = match($role_requested) {
                        'student' => ['label' => 'Sinh viên', 'icon' => 'user', 'description' => 'Đang học tập tại Trường Đại học Sư phạm TP.HCM.'],
                        'alumni' => ['label' => 'Cựu sinh viên', 'icon' => 'graduation-cap', 'description' => 'Đã tốt nghiệp từ Trường Đại học Sư phạm TP.HCM.'],
                        'teacher' => ['label' => 'Giảng viên', 'icon' => 'shield-check', 'description' => 'Giảng viên HCMUE; nếu đang là cố vấn học tập, bạn sẽ bổ sung thông tin ở bước sau.'],
                        default => ['label' => 'Sinh viên', 'icon' => 'user', 'description' => 'Đang học tập tại Trường Đại học Sư phạm TP.HCM.'],
                    };
                @endphp

                <h2 class="text-xl font-bold text-ue-text mb-2">Vai trò xác thực đã được khóa</h2>
                <p class="text-sm text-ue-text-secondary mb-6">UEConnect dùng đúng vai trò bạn đã chọn ở bước đăng ký. Nếu đăng ký sai vai trò, vui lòng tạo lại tài khoản theo đúng luồng đăng ký.</p>

                <div class="mb-8 rounded-xl border border-ue-brand bg-ue-brand-soft p-5 text-left">
                    <div class="flex items-start gap-4">
                        <div class="p-3 bg-ue-brand text-ue-text-inverse rounded-lg">
                            <x-ui.icon :name="$roleMeta['icon']" size="lg" />
                        </div>
                        <div>
                            <div class="text-xs font-bold uppercase tracking-wider text-ue-brand mb-1">Vai trò của tài khoản này</div>
                            <h3 class="font-bold text-ue-text mb-1">{{ $roleMeta['label'] }}</h3>
                            <p class="text-xs text-ue-text-secondary leading-relaxed">{{ $roleMeta['description'] }}</p>
                        </div>
                    </div>
                </div>
                <x-ui.field-error name="role_requested" />

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
                    @elseif ($role_requested === 'teacher')
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <x-ui.label for="submitted_faculty_id">Khoa / Phòng ban công tác</x-ui.label>
                                <x-ui.select wire:model="submitted_faculty_id" id="submitted_faculty_id" class="mt-1">
                                    <option value="">-- Có thể bổ sung sau --</option>
                                    @foreach ($this->faculties as $faculty)
                                        <option value="{{ $faculty->id }}">{{ $faculty->name }}</option>
                                    @endforeach
                                </x-ui.select>
                                <x-ui.field-error name="submitted_faculty_id" />
                            </div>
                            <div>
                                <x-ui.label for="submitted_position">Chức danh hiện tại</x-ui.label>
                                <x-ui.input wire:model="submitted_position" id="submitted_position" placeholder="Ví dụ: Giảng viên, Giảng viên chính" class="mt-1" />
                                <x-ui.field-error name="submitted_position" />
                            </div>
                        </div>

                        <div class="rounded-xl border border-ue-border bg-ue-surface-subtle p-4 space-y-3">
                            <label class="flex items-start gap-3 cursor-pointer">
                                <input type="checkbox" wire:model.live="submitted_is_academic_advisor" class="mt-1 rounded border-ue-border text-ue-brand focus:ring-ue-brand">
                                <span>
                                    <span class="block text-sm font-bold text-ue-text">Tôi đang là cố vấn học tập</span>
                                    <span class="block text-xs text-ue-text-secondary mt-0.5">Cố vấn học tập là thuộc tính của giảng viên, không phải role riêng.</span>
                                </span>
                            </label>

                            @if ($submitted_is_academic_advisor)
                                <div>
                                    <x-ui.label for="submitted_advised_class_codes">Lớp đang cố vấn</x-ui.label>
                                    <x-ui.textarea wire:model="submitted_advised_class_codes" id="submitted_advised_class_codes" rows="3" placeholder="Ví dụ: 49.CNTTD&#10;50.CNTTA" class="mt-1" />
                                    <p class="text-[10px] text-ue-text-muted mt-1">Mỗi dòng một lớp, theo định dạng khóa + ngành viết tắt + nhóm lớp, ví dụ 49.CNTTD.</p>
                                    <x-ui.field-error name="submitted_advised_class_codes" />
                                </div>
                            @endif
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
                @php
                    $evidenceIntro = match($role_requested) {
                        'student' => 'Tải lên thẻ sinh viên, giấy báo nhập học, bảng điểm hoặc chụp màn hình email sinh viên để chứng minh bạn là sinh viên HCMUE.',
                        'alumni' => 'Tải lên một minh chứng cựu sinh viên như bằng tốt nghiệp, bảng điểm, thẻ sinh viên cũ hoặc minh chứng khác để admin xét duyệt.',
                        'teacher' => 'Tải lên minh chứng giảng viên như email công vụ, thẻ viên chức, quyết định phân công, trang hồ sơ khoa hoặc minh chứng công tác phù hợp.',
                        default => 'Tải lên minh chứng định danh phù hợp để admin xét duyệt.',
                    };
                @endphp
                <h2 class="text-xl font-bold text-ue-text mb-2">Tải lên minh chứng định danh</h2>
                <p class="text-sm text-ue-text-secondary mb-6">{{ $evidenceIntro }}</p>

                @if ($errors->has('evidence'))
                    <div class="mb-4 p-3 bg-red-50 text-red-700 text-sm rounded-lg border border-red-200">
                        {{ $errors->first('evidence') }}
                    </div>
                @endif

                <div class="space-y-6 mb-8">

                    {{-- Evidence Method Selection (student role only) --}}
                    @if($role_requested === 'student' && $evidenceMethod === '')
                    <div class="space-y-4">
                        <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Chọn cách cung cấp minh chứng thẻ sinh viên</p>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            {{-- Camera Card --}}
                            <button
                                type="button"
                                wire:click="$set('evidenceMethod', 'camera')"
                                class="relative flex flex-col items-start p-5 rounded-xl border-2 border-gray-200 dark:border-gray-700 hover:border-blue-500 dark:hover:border-blue-400 bg-white dark:bg-gray-800 text-left transition-all duration-200 group focus:outline-none focus:ring-2 focus:ring-blue-500"
                            >
                                <span class="absolute top-3 right-3 text-xs font-semibold text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/30 px-2 py-0.5 rounded-full">Khuyến nghị</span>
                                <div class="flex items-center gap-3 mb-3">
                                    <div class="p-2.5 bg-blue-50 dark:bg-blue-900/30 rounded-lg group-hover:bg-blue-100 dark:group-hover:bg-blue-900/50 transition-colors">
                                        <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 0 1 5.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 0 0-1.134-.175 2.31 2.31 0 0 1-1.64-1.055l-.822-1.316a2.192 2.192 0 0 0-1.736-1.039 48.774 48.774 0 0 0-5.232 0 2.192 2.192 0 0 0-1.736 1.039l-.821 1.316Z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0ZM18.75 10.5h.008v.008h-.008V10.5Z" />
                                        </svg>
                                    </div>
                                    <span class="font-semibold text-gray-900 dark:text-white">Chụp trực tiếp bằng camera</span>
                                </div>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Chụp trực tiếp giúp UEConnect kiểm tra minh chứng nhanh hơn bằng AI.</p>
                            </button>

                            {{-- Upload Card --}}
                            <button
                                type="button"
                                wire:click="$set('evidenceMethod', 'upload')"
                                class="flex flex-col items-start p-5 rounded-xl border-2 border-gray-200 dark:border-gray-700 hover:border-gray-400 dark:hover:border-gray-500 bg-white dark:bg-gray-800 text-left transition-all duration-200 group focus:outline-none focus:ring-2 focus:ring-gray-400"
                            >
                                <div class="flex items-center gap-3 mb-3">
                                    <div class="p-2.5 bg-gray-50 dark:bg-gray-700 rounded-lg group-hover:bg-gray-100 dark:group-hover:bg-gray-600 transition-colors">
                                        <svg class="w-6 h-6 text-gray-500 dark:text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5" />
                                        </svg>
                                    </div>
                                    <span class="font-semibold text-gray-900 dark:text-white">Upload file minh chứng</span>
                                </div>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Dùng khi bạn không thể mở camera. Hồ sơ upload cũng sẽ được AI hỗ trợ phân tích và trích xuất thông tin tự động.</p>
                            </button>
                        </div>
                    </div>
                    @endif

                    {{-- Camera Capture UI (student + camera method selected) --}}
                    @if($role_requested === 'student' && $evidenceMethod === 'camera')
                    <div
                        x-data="identityCameraUpload()"
                        class="space-y-4"
                        x-cloak
                    >
                        {{-- Back button --}}
                        <button type="button" wire:click="$set('evidenceMethod', '')" class="text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" /></svg>
                            Đổi phương thức
                        </button>

                        {{-- Privacy notice --}}
                        <div class="rounded-lg bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 p-3">
                            <p class="text-xs text-blue-700 dark:text-blue-300">📷 UEConnect yêu cầu chụp thẻ trực tiếp bằng camera để giảm gian lận. UEConnect <strong>không</strong> thực hiện nhận diện khuôn mặt.</p>
                        </div>

                        {{-- HTTPS warning (only on non-localhost HTTP) --}}
                        <div x-show="!isCameraContextAllowed" class="rounded-lg bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 p-3">
                            <p class="text-xs text-yellow-700 dark:text-yellow-300">⚠️ Camera yêu cầu kết nối HTTPS. Trên môi trường phát triển localhost, camera vẫn hoạt động.</p>
                        </div>

                        {{-- State: idle --}}
                        <div x-show="state === 'idle'" class="flex flex-col items-center gap-4 py-8">
                            <div class="p-4 bg-gray-100 dark:bg-gray-700 rounded-full">
                                <svg class="w-12 h-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 0 1 5.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 0 0-1.134-.175 2.31 2.31 0 0 1-1.64-1.055l-.822-1.316a2.192 2.192 0 0 0-1.736-1.039 48.774 48.774 0 0 0-5.232 0 2.192 2.192 0 0 0-1.736 1.039l-.821 1.316Z" />
                                </svg>
                            </div>
                            <p class="text-sm text-gray-600 dark:text-gray-400 text-center">Đưa thẻ sinh viên vào trong khung.<br>Đảm bảo thấy rõ họ tên, MSSV, khoa/ngành và tên trường.</p>
                            <button type="button" @click="startCamera()" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
                                Bật camera
                            </button>
                        </div>

                        {{-- State: permission denied or error --}}
                        <div x-show="state === 'error'" class="rounded-lg bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 p-4 text-center">
                            <p class="text-sm text-red-700 dark:text-red-300 mb-3" x-text="errorMessage"></p>
                            <div class="flex flex-col sm:flex-row gap-2 justify-center">
                                <button type="button" @click="startCamera()" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg">Thử lại</button>
                                <button type="button" wire:click="$set('evidenceMethod', 'upload')" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 text-sm font-medium rounded-lg">Dùng upload thay thế</button>
                            </div>
                        </div>

                        {{-- State: camera loading --}}
                        <div x-show="state === 'loading'" class="flex flex-col items-center gap-3 py-8">
                            <div class="w-8 h-8 border-2 border-blue-600 border-t-transparent rounded-full animate-spin"></div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Đang khởi động camera...</p>
                        </div>

                        {{-- State: camera ready / capture preview --}}
                        <div x-show="state === 'camera_ready' || state === 'capture_preview'" class="space-y-3">
                            <div wire:ignore class="relative rounded-xl overflow-hidden bg-black aspect-video">
                                {{-- Live video feed --}}
                                <video
                                    x-ref="videoEl"
                                    x-show="state === 'camera_ready'"
                                    class="w-full h-full object-cover"
                                    autoplay
                                    playsinline
                                    muted
                                ></video>

                                {{-- Card guide frame overlay — portrait 54×86 mm ratio --}}
                                <div x-show="state === 'camera_ready'" class="absolute inset-0 flex items-center justify-center pointer-events-none">
                                    <div class="relative rounded-xl"
                                         style="width: 38%; aspect-ratio: 54/86; box-shadow: 0 0 0 9999px rgba(0,0,0,0.45);">
                                        {{-- Corner markers --}}
                                        <span class="absolute top-0 left-0 w-5 h-5 border-t-2 border-l-2 border-white rounded-tl-lg"></span>
                                        <span class="absolute top-0 right-0 w-5 h-5 border-t-2 border-r-2 border-white rounded-tr-lg"></span>
                                        <span class="absolute bottom-0 left-0 w-5 h-5 border-b-2 border-l-2 border-white rounded-bl-lg"></span>
                                        <span class="absolute bottom-0 right-0 w-5 h-5 border-b-2 border-r-2 border-white rounded-br-lg"></span>
                                        {{-- Label --}}
                                        <div class="absolute -bottom-7 left-0 right-0 text-center">
                                            <span class="text-white text-xs bg-black bg-opacity-50 px-2 py-1 rounded whitespace-nowrap">📷 Đặt thẻ sinh viên vào khung</span>
                                        </div>
                                    </div>
                                </div>

                                {{-- Capture preview image — bound to Alpine data, survives Livewire morphs --}}
                                <img
                                    x-show="state === 'capture_preview'"
                                    :src="capturedData"
                                    class="w-full h-full object-cover"
                                    alt="Ảnh đã chụp"
                                />

                                {{-- Hidden canvas for capture --}}
                                <canvas x-ref="canvasEl" class="hidden"></canvas>
                            </div>

                            <div class="flex gap-3 justify-center">
                                {{-- Capture button --}}
                                <button
                                    x-show="state === 'camera_ready'"
                                    type="button"
                                    @click="capturePhoto()"
                                    class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg flex items-center gap-2 transition-colors"
                                >
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <circle cx="12" cy="12" r="3"/>
                                        <path d="M6.827 6.175A2.31 2.31 0 0 1 5.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 0 0-1.134-.175 2.31 2.31 0 0 1-1.64-1.055l-.822-1.316a2.192 2.192 0 0 0-1.736-1.039 48.774 48.774 0 0 0-5.232 0 2.192 2.192 0 0 0-1.736 1.039l-.821 1.316Z"/>
                                    </svg>
                                    Chụp ảnh
                                </button>

                                {{-- Retake button --}}
                                <button
                                    x-show="state === 'capture_preview'"
                                    type="button"
                                    @click="retakePhoto()"
                                    class="px-4 py-2.5 bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 text-sm font-medium rounded-lg transition-colors"
                                >
                                    Chụp lại
                                </button>

                                {{-- Use this photo button --}}
                                <button
                                    x-show="state === 'capture_preview'"
                                    type="button"
                                    @click="confirmPhoto()"
                                    class="px-5 py-2.5 bg-green-600 hover:bg-green-700 text-white text-sm font-semibold rounded-lg transition-colors"
                                >
                                    Dùng ảnh này ✓
                                </button>
                            </div>
                        </div>

                        {{-- State: confirmed (ready to submit) --}}
                        <div x-show="state === 'confirmed'" class="rounded-lg bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 p-4">
                            <div class="flex items-center gap-3">
                                <svg class="w-5 h-5 text-green-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                </svg>
                                <div>
                                    <p class="text-sm font-medium text-green-800 dark:text-green-300">Ảnh đã sẵn sàng gửi</p>
                                    <p class="text-xs text-green-600 dark:text-green-400 mt-0.5">Ảnh thẻ sinh viên đã được chụp thành công. Nhấn "Gửi hồ sơ" để tiếp tục.</p>
                                </div>
                            </div>
                            <button type="button" @click="retakePhoto()" class="mt-3 text-xs text-green-600 dark:text-green-400 underline">Chụp lại từ đầu</button>
                        </div>
                    </div>
                    @endif

                    {{-- Upload form (non-student roles always see this; students see it when upload method selected) --}}
                    @if($role_requested !== 'student' || $evidenceMethod === 'upload')
                        @if($role_requested === 'student' && $evidenceMethod === 'upload')
                        <div class="mb-4">
                            <button type="button" wire:click="$set('evidenceMethod', '')" class="text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" /></svg>
                                Đổi phương thức
                            </button>
                            <div class="mt-2 rounded-lg bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 p-3">
                                <p class="text-xs text-amber-700 dark:text-amber-300">📁 Hệ thống AI sẽ tự động phân tích và đối sánh thẻ sinh viên được tải lên.</p>
                            </div>
                        </div>
                        @endif

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
                                                    @foreach ($this->evidenceTypeOptions() as $value => $label)
                                                        <option value="{{ $value }}">{{ $label }}</option>
                                                    @endforeach
                                                </x-ui.select>

                                                <x-ui.label class="text-left text-xs mb-1" :required="true">Mô tả chi tiết</x-ui.label>
                                                <x-ui.input wire:model="evidence_notes.{{ $i }}" placeholder="Thẻ SV mặt trước..." class="h-8 text-xs" />
                                            </div>
                                        @else
                                            <x-ui.icon name="upload" size="lg" class="text-ue-text-muted mb-2" />
                                            <label class="cursor-pointer bg-ue-brand-soft text-ue-brand px-3 py-1.5 rounded-lg text-xs font-semibold hover:bg-ue-brand-soft-hover transition-colors">
                                                Chọn tệp
                                                <input type="file" 
                                                       wire:model="evidence_files.{{ $i }}" 
                                                       class="hidden" 
                                                       accept="image/*,application/pdf"
                                                       x-on:change="
                                                           if ($event.target.files.length > 0 && $event.target.files[0].size > 5120 * 1024) {
                                                               $event.preventDefault();
                                                               $event.target.value = '';
                                                               window.dispatchEvent(new CustomEvent('ue:toast', {
                                                                   detail: {
                                                                       type: 'danger',
                                                                       message: 'Kích thước tệp tin không được vượt quá 5MB.'
                                                                   }
                                                               }));
                                                           }
                                                       " />
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
                    @endif

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
