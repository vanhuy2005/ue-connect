<?php

use App\Enums\AccountStatus;
use App\Enums\IdentityType;
use App\Enums\VerificationStatus;
use App\Models\Faculty;
use App\Models\AcademicProgram;
use App\Models\Profile;
use App\Models\StudentProfile;
use App\Models\AlumniProfile;
use App\Models\AdvisorProfile;
use App\Models\VerificationRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.app')] class extends Component
{
    public string $role_type = 'student'; // 'student', 'alumni', 'advisor'
    
    // Common fields
    public string $display_name = '';
    public string $bio = '';
    public string $visibility = 'public';
    public bool $discoverable = true;
    public ?int $faculty_id = null;
    
    // Role-specific fields
    public string $student_code = '';
    public string $cohort = '';
    public ?int $academic_program_id = null;
    public ?int $graduation_year = null;
    public bool $willing_to_mentor = false;
    public string $department = '';
    public string $title = '';

    public function mount(): void
    {
        $user = auth()->user();
        if (!$user) {
            $this->redirect(route('login'));
            return;
        }

        // Server-side guard: Only allow PROFILE_INCOMPLETE users
        if ($user->account_status === AccountStatus::ACTIVE) {
            $this->redirect(route('dashboard'), navigate: true);
            return;
        }

        if ($user->account_status !== AccountStatus::PROFILE_INCOMPLETE) {
            $this->redirect(route('verification.status'), navigate: true);
            return;
        }
        
        // Load data from approved request or intended identity
        $this->loadIdentityData($user);
    }

    protected function loadIdentityData(User $user): void
    {
        // Find approved verification request
        $request = VerificationRequest::where('user_id', $user->id)
            ->where('status', VerificationStatus::APPROVED)
            ->latest()
            ->first();

        if ($request) {
            $this->role_type = $request->role_requested;
            $this->display_name = $request->submitted_name ?? $user->name;
            $this->faculty_id = $request->submitted_faculty_id;
            $this->academic_program_id = $request->submitted_academic_program_id;
            $this->student_code = $request->submitted_student_code ?? '';
            $this->cohort = $request->submitted_cohort ?? '';
            if ($this->role_type === 'alumni') {
                $this->graduation_year = (int)$request->submitted_graduation_year ?: null;
            } elseif ($this->role_type === 'advisor') {
                $this->department = $request->submitted_organization ?? '';
                $this->title = $request->submitted_position ?? '';
            }
        } else {
            // Fallback to intended_identity_type
            $intended = $user->intended_identity_type->value ?? $user->intended_identity_type ?? '';
            $this->role_type = match($intended) {
                IdentityType::CURRENT_STUDENT->value, 'current_student' => 'student',
                IdentityType::ALUMNI->value, 'alumni' => 'alumni',
                IdentityType::TEACHER_ADVISOR->value, 'teacher_advisor' => 'advisor',
                default => 'student',
            };
            $this->display_name = $user->name;
        }
    }

    public function getFacultiesProperty()
    {
        return Faculty::where('status', 'active')->orderBy('name')->get();
    }

    public function getAcademicProgramsProperty()
    {
        if (!$this->faculty_id) {
            return collect();
        }
        return AcademicProgram::where('faculty_id', $this->faculty_id)
            ->where('status', 'active')
            ->orderBy('name')
            ->get();
    }

    public function updatedFacultyId(): void
    {
        $this->academic_program_id = null;
    }

    public function save()
    {
        $user = auth()->user();
        if (!$user || $user->account_status !== AccountStatus::PROFILE_INCOMPLETE) {
            session()->flash('error', 'Hành động không hợp lệ.');
            return;
        }

        // Validation rules
        $rules = [
            'display_name' => ['required', 'string', 'min:2', 'max:255'],
            'bio' => ['nullable', 'string', 'max:1000'],
            'visibility' => ['required', 'string', 'in:public,connections,private'],
            'discoverable' => ['required', 'boolean'],
        ];

        if ($this->role_type === 'student') {
            $rules['faculty_id'] = ['required', 'integer', 'exists:faculties,id'];
            $rules['academic_program_id'] = ['required', 'integer', 'exists:academic_programs,id'];
            $rules['cohort'] = ['required', 'string', 'max:50'];
            $rules['student_code'] = ['required', 'string', 'max:50'];
        } elseif ($this->role_type === 'alumni') {
            $rules['faculty_id'] = ['nullable', 'integer', 'exists:faculties,id'];
            $rules['academic_program_id'] = ['required', 'integer', 'exists:academic_programs,id'];
            $rules['graduation_year'] = ['required', 'integer', 'min:1950', 'max:' . (date('Y') + 5)];
            $rules['willing_to_mentor'] = ['required', 'boolean'];
            $rules['cohort'] = ['nullable', 'string', 'max:50'];
        } elseif ($this->role_type === 'advisor') {
            $rules['faculty_id'] = ['nullable', 'integer', 'exists:faculties,id'];
            $rules['department'] = ['required', 'string', 'max:255'];
            $rules['title'] = ['required', 'string', 'max:255'];
        }

        $this->validate($rules, [
            'display_name.required' => 'Tên hiển thị không được để trống.',
            'faculty_id.required' => 'Vui lòng chọn Khoa.',
            'academic_program_id.required' => 'Vui lòng chọn chuyên ngành học.',
            'cohort.required' => 'Vui lòng nhập niên khóa (ví dụ: K47).',
            'student_code.required' => 'Vui lòng nhập mã số sinh viên.',
            'graduation_year.required' => 'Vui lòng nhập năm tốt nghiệp.',
            'department.required' => 'Vui lòng nhập bộ môn/khoa giảng dạy.',
            'title.required' => 'Vui lòng nhập chức danh/vị trí.',
        ]);

        DB::transaction(function () use ($user) {
            // Find existing profile (including soft-deleted ones)
            $profile = Profile::withTrashed()->where('user_id', $user->id)->first();

            if ($profile) {
                // If it was soft-deleted, restore it to retain history
                if ($profile->trashed()) {
                    $profile->restore();
                }

                // Update the profile fields to complete it
                $profile->update([
                    'display_name' => $this->display_name,
                    'bio' => $this->bio,
                    'role_type' => $this->role_type,
                    'profile_status' => 'complete',
                    'visibility' => $this->visibility,
                    'discoverable' => $this->discoverable,
                    'profile_completed_at' => now(),
                ]);

                // Clear any existing role-specific child profiles to avoid duplicates
                StudentProfile::where('profile_id', $profile->id)->delete();
                AlumniProfile::where('profile_id', $profile->id)->delete();
                AdvisorProfile::where('profile_id', $profile->id)->delete();
            } else {
                // Create a new Profile if none exists
                $profile = Profile::create([
                    'user_id' => $user->id,
                    'display_name' => $this->display_name,
                    'bio' => $this->bio,
                    'role_type' => $this->role_type,
                    'profile_status' => 'complete',
                    'visibility' => $this->visibility,
                    'discoverable' => $this->discoverable,
                    'profile_completed_at' => now(),
                ]);
            }

            // Create role-specific profile
            if ($this->role_type === 'student') {
                StudentProfile::create([
                    'profile_id' => $profile->id,
                    'student_code' => $this->student_code,
                    'faculty_id' => $this->faculty_id,
                    'academic_program_id' => $this->academic_program_id,
                    'cohort' => $this->cohort,
                ]);
            } elseif ($this->role_type === 'alumni') {
                AlumniProfile::create([
                    'profile_id' => $profile->id,
                    'faculty_id' => $this->faculty_id,
                    'academic_program_id' => $this->academic_program_id,
                    'cohort' => $this->cohort ?: null,
                    'graduation_year' => $this->graduation_year,
                    'willing_to_mentor' => $this->willing_to_mentor,
                ]);
            } elseif ($this->role_type === 'advisor') {
                AdvisorProfile::create([
                    'profile_id' => $profile->id,
                    'faculty_id' => $this->faculty_id,
                    'department' => $this->department,
                    'title' => $this->title,
                ]);
            }

            // Transition user status to ACTIVE!
            $user->update([
                'account_status' => AccountStatus::ACTIVE,
            ]);
        });

        session()->flash('success', 'Thiết lập hồ sơ thành công!');
        return $this->redirect(route('dashboard'), navigate: true);
    }
}; ?>

<div class="max-w-2xl mx-auto py-10 px-4 sm:px-6">
    <div class="text-center mb-8">
        {{-- Custom beautiful initials badge replacing real photo --}}
        <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-ue-brand-soft text-ue-brand text-2xl font-bold border-2 border-ue-brand mb-4">
            {{ Str::upper(Str::substr($display_name ?: auth()->user()->name, 0, 2)) }}
        </div>
        <h1 class="text-2xl font-extrabold text-ue-neutral-900 tracking-tight">Hoàn thành hồ sơ cá nhân</h1>
        <p class="mt-2 text-sm text-ue-text-secondary max-w-md mx-auto">
            Chúc mừng bạn đã xác thực danh tính thành công! Hãy bổ sung một số thông tin hiển thị để gia nhập cộng đồng UEConnect.
        </p>
    </div>

    @if (session()->has('error'))
        <div class="mb-6">
            <x-ui.alert variant="danger" title="Lỗi thực hiện">
                {{ session('error') }}
            </x-ui.alert>
        </div>
    @endif

    <form wire:submit="save" class="bg-ue-surface border border-ue-border rounded-2xl p-6 sm:p-8 shadow-sm space-y-6">
        {{-- Section 1: Common Profile Info --}}
        <div>
            <h2 class="text-base font-bold text-ue-neutral-900 border-b border-ue-border pb-2 mb-4 flex items-center gap-2">
                <x-ui.icon name="user" size="sm" class="text-ue-brand" />
                Thông tin cơ bản
            </h2>

            <div class="space-y-4">
                <div>
                    <x-ui.label for="display_name" :required="true">Tên hiển thị</x-ui.label>
                    <x-ui.input wire:model="display_name" id="display_name" class="mt-1" placeholder="Nhập tên hiển thị của bạn..." />
                    <span class="text-[10px] text-ue-text-muted mt-1 block">Tên này sẽ hiển thị công khai trên các bài viết và bình luận.</span>
                    <x-ui.field-error name="display_name" />
                </div>

                <div>
                    <x-ui.label for="bio">Giới thiệu ngắn (Bio)</x-ui.label>
                    <x-ui.textarea wire:model="bio" id="bio" class="mt-1" rows="3" placeholder="Chia sẻ một chút về bản thân, sở thích hoặc định hướng nghề nghiệp..." />
                    <span class="text-[10px] text-ue-text-muted mt-1 block">Tối đa 1000 ký tự.</span>
                    <x-ui.field-error name="bio" />
                </div>
            </div>
        </div>

        {{-- Section 2: Role-specific Fields --}}
        <div>
            <h2 class="text-base font-bold text-ue-neutral-900 border-b border-ue-border pb-2 mb-4 flex items-center gap-2">
                <x-ui.icon name="graduation-cap" size="sm" class="text-ue-brand" />
                Thông tin vai trò:
                <x-ui.badge :variant="match($role_type) { 'student' => 'student', 'alumni' => 'alumni', 'advisor' => 'advisor', default => 'neutral' }" size="sm">
                    {{ match($role_type) { 'student' => 'Sinh viên', 'alumni' => 'Cựu sinh viên', 'advisor' => 'Cố vấn/Giảng viên', default => $role_type } }}
                </x-ui.badge>
            </h2>

            @if ($role_type === 'student')
                {{-- Student form --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <x-ui.label for="student_code" :required="true">Mã số sinh viên (MSSV)</x-ui.label>
                        <x-ui.input wire:model="student_code" id="student_code" class="mt-1" placeholder="Nhập MSSV..." />
                        <x-ui.field-error name="student_code" />
                    </div>

                    <div>
                        <x-ui.label for="cohort" :required="true">Khóa học / Niên khóa</x-ui.label>
                        <x-ui.input wire:model="cohort" id="cohort" class="mt-1" placeholder="Ví dụ: K47, K48..." />
                        <x-ui.field-error name="cohort" />
                    </div>

                    <div>
                        <x-ui.label for="faculty_id" :required="true">Khoa đào tạo</x-ui.label>
                        <x-ui.select wire:model.live="faculty_id" id="faculty_id" class="mt-1">
                            <option value="">-- Chọn Khoa --</option>
                            @foreach ($this->faculties as $fac)
                                <option value="{{ $fac->id }}">{{ $fac->name }}</option>
                            @endforeach
                        </x-ui.select>
                        <x-ui.field-error name="faculty_id" />
                    </div>

                    <div>
                        <x-ui.label for="academic_program_id" :required="true">Chuyên ngành học</x-ui.label>
                        <x-ui.select wire:model="academic_program_id" id="academic_program_id" class="mt-1" :disabled="!$faculty_id">
                            <option value="">-- Chọn chuyên ngành --</option>
                            @foreach ($this->academicPrograms as $prog)
                                <option value="{{ $prog->id }}">{{ $prog->name }}</option>
                            @endforeach
                        </x-ui.select>
                        <x-ui.field-error name="academic_program_id" />
                    </div>
                </div>

            @elseif ($role_type === 'alumni')
                {{-- Alumni form --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <x-ui.label for="faculty_id">Khoa (Cũ)</x-ui.label>
                        <x-ui.select wire:model.live="faculty_id" id="faculty_id" class="mt-1">
                            <option value="">-- Chọn Khoa (Tùy chọn) --</option>
                            @foreach ($this->faculties as $fac)
                                <option value="{{ $fac->id }}">{{ $fac->name }}</option>
                            @endforeach
                        </x-ui.select>
                        <x-ui.field-error name="faculty_id" />
                    </div>

                    <div>
                        <x-ui.label for="academic_program_id" :required="true">Chuyên ngành đào tạo</x-ui.label>
                        <x-ui.select wire:model="academic_program_id" id="academic_program_id" class="mt-1" :disabled="!$faculty_id">
                            <option value="">-- Chọn chuyên ngành --</option>
                            @foreach ($this->academicPrograms as $prog)
                                <option value="{{ $prog->id }}">{{ $prog->name }}</option>
                            @endforeach
                        </x-ui.select>
                        <x-ui.field-error name="academic_program_id" />
                    </div>

                    <div>
                        <x-ui.label for="graduation_year" :required="true">Năm tốt nghiệp</x-ui.label>
                        <x-ui.input type="number" wire:model="graduation_year" id="graduation_year" class="mt-1" placeholder="Ví dụ: 2023..." />
                        <x-ui.field-error name="graduation_year" />
                    </div>

                    <div>
                        <x-ui.label for="cohort">Khóa học / Niên khóa</x-ui.label>
                        <x-ui.input wire:model="cohort" id="cohort" class="mt-1" placeholder="Ví dụ: K44, K45..." />
                        <x-ui.field-error name="cohort" />
                    </div>
                </div>

                <div class="mt-4 p-4 bg-ue-blue-50 border border-ue-blue-100 rounded-xl flex items-start gap-3">
                    <input type="checkbox" wire:model="willing_to_mentor" id="willing_to_mentor" class="h-4 w-4 rounded border-ue-border text-ue-brand focus:ring-ue-brand mt-1" />
                    <div>
                        <x-ui.label for="willing_to_mentor" class="font-bold text-ue-neutral-900">Sẵn sàng làm Mentor (Cố vấn nghề nghiệp)</x-ui.label>
                        <p class="text-[11px] text-ue-text-secondary leading-relaxed">
                            Bằng việc tích chọn, bạn đồng ý chia sẻ kinh nghiệm, hỗ trợ định hướng nghề nghiệp và phản hồi các câu hỏi học tập từ sinh viên khóa dưới.
                        </p>
                    </div>
                </div>

            @elseif ($role_type === 'advisor')
                {{-- Advisor form --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <x-ui.label for="faculty_id">Khoa quản lý / Công tác</x-ui.label>
                        <x-ui.select wire:model="faculty_id" id="faculty_id" class="mt-1">
                            <option value="">-- Chọn Khoa công tác (Tùy chọn) --</option>
                            @foreach ($this->faculties as $fac)
                                <option value="{{ $fac->id }}">{{ $fac->name }}</option>
                            @endforeach
                        </x-ui.select>
                        <x-ui.field-error name="faculty_id" />
                    </div>

                    <div>
                        <x-ui.label for="department" :required="true">Bộ môn / Phòng ban</x-ui.label>
                        <x-ui.input wire:model="department" id="department" class="mt-1" placeholder="Ví dụ: Bộ môn Công nghệ phần mềm..." />
                        <x-ui.field-error name="department" />
                    </div>

                    <div class="md:col-span-2">
                        <x-ui.label for="title" :required="true">Học hàm / Học vị / Chức vụ công tác</x-ui.label>
                        <x-ui.input wire:model="title" id="title" class="mt-1" placeholder="Ví dụ: Giảng viên chính, TS..." />
                        <x-ui.field-error name="title" />
                    </div>
                </div>
            @endif
        </div>

        {{-- Section 3: Privacy & Visibility Settings --}}
        <div>
            <h2 class="text-base font-bold text-ue-neutral-900 border-b border-ue-border pb-2 mb-4 flex items-center gap-2">
                <x-ui.icon name="lock" size="sm" class="text-ue-brand" />
                Cài đặt quyền riêng tư
            </h2>

            <div class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <x-ui.label for="visibility" :required="true">Chế độ hiển thị hồ sơ</x-ui.label>
                        <x-ui.select wire:model="visibility" id="visibility" class="mt-1">
                            <option value="public">Công khai (Tất cả mọi người)</option>
                            <option value="connections">Chỉ bạn bè / Người kết nối</option>
                            <option value="private">Riêng tư (Chỉ mình tôi)</option>
                        </x-ui.select>
                        <x-ui.field-error name="visibility" />
                    </div>

                    <div class="flex items-start gap-3 mt-6">
                        <input type="checkbox" wire:model="discoverable" id="discoverable" class="h-4 w-4 rounded border-ue-border text-ue-brand focus:ring-ue-brand mt-1" />
                        <div>
                            <x-ui.label for="discoverable" class="font-bold text-ue-neutral-900">Cho phép tìm kiếm</x-ui.label>
                            <p class="text-[10px] text-ue-text-secondary leading-relaxed">
                                Cho phép thành viên khác tìm kiếm thấy bạn qua thanh tìm kiếm chung bằng tên hoặc email.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Submit and actions --}}
        <div class="border-t border-ue-border pt-6 flex justify-end gap-3">
            <x-ui.button type="submit" variant="primary" icon="check" class="w-full sm:w-auto">
                Hoàn tất & Khám phá UEConnect
            </x-ui.button>
        </div>
    </form>
</div>
