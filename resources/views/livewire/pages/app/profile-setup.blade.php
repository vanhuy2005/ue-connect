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
use Livewire\WithFileUploads;

new #[Layout('layouts.app')] class extends Component
{
    use WithFileUploads;

    public string $role_type = 'student'; // 'student', 'alumni', 'teacher'
    
    // Common fields
    public $avatar;
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
    public bool $is_academic_advisor = false;
    public string $advised_class_codes = '';
    
    // Wizard Steps
    public int $currentStep = 1;

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
            } elseif (in_array($this->role_type, ['teacher', 'advisor'], true)) {
                $this->role_type = 'teacher';
                $this->department = $request->submitted_organization ?? '';
                $this->title = $request->submitted_position ?: 'Giảng viên';
                $this->is_academic_advisor = (bool) $request->submitted_is_academic_advisor;
                $this->advised_class_codes = $request->submitted_advised_class_codes ?? '';
            }
        } else {
            // Fallback to intended_identity_type
            $intended = $user->intended_identity_type->value ?? $user->intended_identity_type ?? '';
            $this->role_type = match($intended) {
                IdentityType::CURRENT_STUDENT->value, 'current_student' => 'student',
                IdentityType::ALUMNI->value, 'alumni' => 'alumni',
                IdentityType::TEACHER_ADVISOR->value, 'teacher_advisor' => 'teacher',
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

    public function nextStep()
    {
        if ($this->currentStep === 1) {
            if ($this->avatar) {
                $this->validate([
                    'avatar' => ['image', 'max:2048'], // 2MB Max
                ]);
            }
            $this->currentStep = 2;
        } elseif ($this->currentStep === 2) {
            $this->validate([
                'display_name' => ['required', 'string', 'min:2', 'max:255'],
                'bio' => ['nullable', 'string', 'max:1000'],
            ], [
                'display_name.required' => 'Tên hiển thị không được để trống.',
            ]);
            $this->currentStep = 3;
        } elseif ($this->currentStep === 3) {
            $rules = [];
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
            } elseif ($this->role_type === 'teacher') {
                $rules['faculty_id'] = ['nullable', 'integer', 'exists:faculties,id'];
                $rules['department'] = ['nullable', 'string', 'max:255'];
                $rules['title'] = ['nullable', 'string', 'max:255'];
                $rules['is_academic_advisor'] = ['required', 'boolean'];
                $rules['advised_class_codes'] = ['nullable', 'string', 'max:500'];
            }

            $this->validate($rules, [
                'faculty_id.required' => 'Vui lòng chọn Khoa.',
                'academic_program_id.required' => 'Vui lòng chọn chuyên ngành học.',
                'cohort.required' => 'Vui lòng nhập niên khóa.',
                'student_code.required' => 'Vui lòng nhập mã số sinh viên.',
                'graduation_year.required' => 'Vui lòng nhập năm tốt nghiệp.',
                'department.required' => 'Vui lòng nhập bộ môn/khoa giảng dạy.',
                'title.required' => 'Vui lòng nhập chức danh/vị trí.',
            ]);
            $this->currentStep = 4;
        }
    }

    public function previousStep()
    {
        if ($this->currentStep > 1) {
            $this->currentStep--;
        }
    }

    public function save()
    {
        $user = auth()->user();
        if (!$user || $user->account_status !== AccountStatus::PROFILE_INCOMPLETE) {
            session()->flash('error', 'Hành động không hợp lệ.');
            return;
        }

        // Final step validation
        $this->validate([
            'visibility' => ['required', 'string', 'in:public,connections,private'],
            'discoverable' => ['required', 'boolean'],
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
            } elseif ($this->role_type === 'teacher') {
                AdvisorProfile::create([
                    'profile_id' => $profile->id,
                    'faculty_id' => $this->faculty_id,
                    'department' => $this->department,
                    'title' => $this->title ?: 'Giảng viên',
                    'is_academic_advisor' => $this->is_academic_advisor,
                    'advised_class_codes' => $this->is_academic_advisor ? $this->advised_class_codes : null,
                ]);
            }

            // Transition user status to ACTIVE!
            $user->update([
                'account_status' => AccountStatus::ACTIVE,
            ]);
        });

        session()->flash('success', 'Thiết lập hồ sơ thành công!');

        if (in_array($this->role_type, ['alumni']) && $this->willing_to_mentor) {
            return $this->redirect(route('mentor.apply'), navigate: true);
        }

        return $this->redirect(route('dashboard'), navigate: true);
    }
}; ?>

<div class="max-w-5xl mx-auto py-4 sm:py-8 px-4 sm:px-6">
    {{-- Header --}}
    <div class="text-center mb-8">
        <h1 class="text-2xl font-extrabold text-ue-neutral-900 tracking-tight">Hoàn thành hồ sơ</h1>
        <p class="mt-2 text-sm text-ue-text-secondary leading-relaxed">
            Bổ sung một số thông tin hiển thị để gia nhập cộng đồng UEConnect.
        </p>
    </div>

    <div class="flex flex-col md:flex-row gap-6 lg:gap-12 items-start">

        {{-- Left Column: Progress Steps --}}
        <div class="w-full md:w-64 lg:w-72 shrink-0">
            <div class="sticky top-24 w-full">
                <div class="flex w-full md:flex-col items-start md:items-start relative gap-0 md:gap-8" wire:key="progress-container">
                    {{-- Horizontal line (mobile) --}}
                    <div class="absolute left-[12.5%] top-4 w-[75%] -translate-y-1/2 h-1 bg-ue-border rounded-full z-0 md:hidden"></div>
                    <div wire:key="progress-mobile" class="absolute left-[12.5%] top-4 -translate-y-1/2 h-1 bg-ue-brand rounded-full z-0 transition-all duration-700 ease-in-out md:hidden" style="width: {{ ($currentStep - 1) * 25 }}%;"></div>
                    
                    {{-- Vertical line (desktop) --}}
                    <div class="hidden md:block absolute left-4 top-4 bottom-4 w-0.5 bg-ue-border rounded-full z-0"></div>
                    <div wire:key="progress-desktop" class="hidden md:block absolute left-4 top-4 w-0.5 bg-ue-brand rounded-full z-0 transition-all duration-700 ease-in-out" style="height: {{ ($currentStep - 1) * 33.33 }}%;"></div>

                    @foreach([1 => 'Ảnh đại diện', 2 => 'Cơ bản', 3 => 'Vai trò', 4 => 'Bảo mật'] as $step => $label)
                    <div class="relative z-10 flex flex-col md:flex-row items-center md:items-center gap-2 md:gap-4 w-1/4 md:w-auto min-w-0" wire:key="step-indicator-{{ $step }}">
                        <div class="w-8 h-8 shrink-0 rounded-full flex items-center justify-center text-sm font-bold transition-all duration-500 ease-out {{ $currentStep >= $step ? 'bg-ue-brand text-white shadow-sm ring-4 ring-white' : 'bg-ue-surface border-2 border-ue-border text-ue-text-muted ring-4 ring-white' }}">
                            {{ $step }}
                        </div>
                        <div class="flex flex-col items-center md:items-start text-center md:text-left">
                            <span class="text-[10px] md:text-sm font-bold whitespace-nowrap {{ $currentStep >= $step ? 'text-ue-neutral-900' : 'text-ue-text-muted hidden md:block' }}">{{ $label }}</span>
                            <span class="text-[10px] text-ue-text-muted hidden md:block">Bước {{ $step }}</span>
                        </div>
                    </div>
                @endforeach
                </div>
            </div>
        </div>

        {{-- Right Column: Form --}}
        <div class="w-full flex-1 min-w-0 relative">
            @if (session()->has('error'))
                <div class="mb-6">
                    <x-ui.alert variant="danger" title="Lỗi thực hiện">
                        {{ session('error') }}
                    </x-ui.alert>
                </div>
            @endif

            <form wire:submit="{{ $currentStep === 4 ? 'save' : 'nextStep' }}" class="w-full {{ $currentStep === 1 ? '' : 'sm:bg-ue-surface sm:border sm:border-ue-border sm:rounded-2xl sm:p-6 sm:shadow-sm' }} overflow-hidden relative flex flex-col sm:min-h-[460px]">
                
                {{-- Section 1: Avatar Upload --}}
                @if($currentStep === 1)
                <div class="space-y-6 animate-in fade-in slide-in-from-right-8 duration-500 ease-out fill-mode-both flex flex-col items-center justify-center pt-8" wire:key="step-1">
                    <div class="flex flex-col items-center justify-center py-6 space-y-4">
                        <div class="relative group cursor-pointer">
                            <div class="w-40 h-40 rounded-full bg-ue-surface-hover border-4 border-white shadow-md flex items-center justify-center overflow-hidden">
                                @if($avatar)
                                    <img src="{{ $avatar->temporaryUrl() }}" class="w-full h-full object-cover" />
                                @else
                                    <div class="text-ue-brand text-5xl font-bold">
                                        {{ Str::upper(Str::substr($display_name ?: auth()->user()->name, 0, 2)) }}
                                    </div>
                                @endif
                            </div>
                            <div class="absolute inset-0 bg-black/40 rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                                <x-ui.icon name="upload" class="text-white" size="xl" />
                            </div>
                            <input type="file" wire:model="avatar" class="absolute inset-0 opacity-0 cursor-pointer w-full h-full" accept="image/*" />
                        </div>
                        <div class="text-center mt-6">
                            <h3 class="text-lg font-bold text-ue-neutral-900">Cập nhật ảnh đại diện</h3>
                            <p class="text-sm text-ue-text-muted mt-2">Giúp mọi người dễ dàng nhận ra bạn trong cộng đồng.</p>
                            <p class="text-xs text-ue-text-muted mt-1">Khuyến nghị ảnh vuông, tối đa 2MB.</p>
                        </div>
                        @error('avatar') <span class="text-xs text-red-600 mt-1">{{ $message }}</span> @enderror
                    </div>
                </div>
                @endif

                {{-- Section 2: Common Profile Info --}}
                @if($currentStep === 2)
                <div class="space-y-6 animate-in fade-in slide-in-from-right-8 duration-500 ease-out fill-mode-both" wire:key="step-2">
                    <h2 class="text-base font-bold text-ue-neutral-900 border-b border-ue-border pb-2 flex items-center gap-2">
                        <x-ui.icon name="user" size="sm" class="text-ue-brand" />
                        Thông tin cơ bản
                    </h2>

                    <div class="space-y-5">
                        <div>
                            <x-ui.label for="display_name" :required="true">Tên hiển thị</x-ui.label>
                            <x-ui.input wire:model="display_name" id="display_name" class="mt-1" placeholder="Nhập tên hiển thị của bạn..." />
                            <span class="text-[10px] text-ue-text-muted mt-1 block">Tên này sẽ hiển thị công khai trên các bài viết và bình luận.</span>
                            <x-ui.field-error name="display_name" />
                        </div>

                        <div>
                            <x-ui.label for="bio">Giới thiệu ngắn (Bio)</x-ui.label>
                            <x-ui.textarea wire:model="bio" id="bio" class="mt-1" rows="4" placeholder="Chia sẻ một chút về bản thân, sở thích hoặc định hướng nghề nghiệp..." />
                            <span class="text-[10px] text-ue-text-muted mt-1 block">Tối đa 1000 ký tự.</span>
                            <x-ui.field-error name="bio" />
                        </div>
                    </div>
                </div>
                @endif

                {{-- Section 3: Role Info --}}
                @if($currentStep === 3)
                <div class="space-y-6 animate-in fade-in slide-in-from-right-8 duration-500 ease-out fill-mode-both" wire:key="step-3">
                    <h2 class="text-base font-bold text-ue-neutral-900 border-b border-ue-border pb-2 flex items-center gap-2">
                        <x-ui.icon name="graduation-cap" size="sm" class="text-ue-brand" />
                        Thông tin vai trò:
                        <x-ui.badge :variant="match($role_type) { 'student' => 'student', 'alumni' => 'alumni', 'teacher' => 'advisor', default => 'neutral' }" size="sm">
                            {{ match($role_type) { 'student' => 'Sinh viên', 'alumni' => 'Cựu sinh viên', 'teacher' => 'Giảng viên', default => $role_type } }}
                        </x-ui.badge>
                    </h2>

                    @if ($role_type === 'student')
                        {{-- Student form --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
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
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
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

                        <div 
                            x-data="{ checked: @entangle('willing_to_mentor') }"
                            :class="checked ? 'border-ue-brand bg-ue-brand-soft/20 shadow-xs' : 'border-ue-border hover:border-slate-300 bg-slate-50/50'"
                            class="mt-6 p-4 rounded-xl border transition-all duration-300 flex items-start gap-3.5 cursor-pointer relative"
                            @click="checked = !checked"
                        >
                            <div class="flex items-center h-5 shrink-0">
                                <input 
                                    type="checkbox" 
                                    wire:model="willing_to_mentor" 
                                    id="willing_to_mentor" 
                                    class="h-4 w-4 rounded border-slate-300 text-ue-brand focus:ring-ue-brand/30"
                                    @click.stop
                                />
                            </div>
                            <div class="select-none flex-1">
                                <label for="willing_to_mentor" class="font-bold text-slate-900 text-sm cursor-pointer" @click.stop>
                                    Sẵn sàng làm Mentor (Cố vấn nghề nghiệp)
                                </label>
                                <p class="text-xs text-slate-500 mt-2 leading-relaxed">
                                    Bằng việc tích chọn, bạn đồng ý chia sẻ kinh nghiệm, hỗ trợ định hướng nghề nghiệp và phản hồi các câu hỏi.
                                </p>
                                <p class="text-[10px] text-slate-400 mt-2 font-medium flex items-center gap-1">
                                    <x-ui.icon name="info" size="xs" class="text-ue-brand shrink-0" />
                                    Sau khi ấn "Hoàn tất", bạn sẽ được tự động chuyển hướng đến biểu mẫu đăng ký hồ sơ Mentor.
                                </p>
                            </div>
                        </div>

                    @elseif ($role_type === 'teacher')
                        {{-- Teacher form --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
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
                                <x-ui.label for="department">Bộ môn / Phòng ban</x-ui.label>
                                <x-ui.input wire:model="department" id="department" class="mt-1" placeholder="Ví dụ: Bộ môn Công nghệ phần mềm..." />
                                <x-ui.field-error name="department" />
                            </div>

                            <div class="sm:col-span-2">
                                <x-ui.label for="title">Học hàm / Học vị / Chức vụ công tác</x-ui.label>
                                <x-ui.input wire:model="title" id="title" class="mt-1" placeholder="Ví dụ: Giảng viên chính, TS..." />
                                <x-ui.field-error name="title" />
                            </div>
                        </div>

                        <div class="mt-5 p-4 bg-ue-blue-50 sm:border border-ue-blue-100 rounded-xl space-y-3">
                            <label class="flex items-start gap-3">
                                <input type="checkbox" wire:model.live="is_academic_advisor" id="is_academic_advisor" class="h-4 w-4 rounded border-ue-border text-ue-brand focus:ring-ue-brand mt-1" />
                                <span>
                                    <span class="block text-sm font-bold text-ue-neutral-900">Đang là cố vấn học tập</span>
                                    <span class="block text-[11px] text-ue-text-secondary leading-relaxed">Thông tin này giúp sinh viên nhận diện đúng vai trò hỗ trợ học tập của giảng viên.</span>
                                </span>
                            </label>

                            @if ($is_academic_advisor)
                                <div class="mt-3">
                                    <x-ui.label for="advised_class_codes">Lớp đang cố vấn</x-ui.label>
                                    <x-ui.textarea wire:model="advised_class_codes" id="advised_class_codes" rows="3" class="mt-1" placeholder="Ví dụ: 49.CNTTD&#10;50.CNTTA" />
                                    <x-ui.field-error name="advised_class_codes" />
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
                @endif

                {{-- Section 4: Privacy Settings --}}
                @if($currentStep === 4)
                <div class="space-y-6 animate-in fade-in slide-in-from-right-8 duration-500 ease-out fill-mode-both" wire:key="step-4">
                    <h2 class="text-base font-bold text-ue-neutral-900 border-b border-ue-border pb-2 flex items-center gap-2">
                        <x-ui.icon name="lock" size="sm" class="text-ue-brand" />
                        Cài đặt quyền riêng tư
                    </h2>

                    <div class="space-y-5">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                            <div>
                                <x-ui.label for="visibility" :required="true">Chế độ hiển thị hồ sơ</x-ui.label>
                                <x-ui.select wire:model="visibility" id="visibility" class="mt-1">
                                    <option value="public">Công khai (Tất cả mọi người)</option>
                                    <option value="connections">Chỉ bạn bè / Người kết nối</option>
                                    <option value="private">Riêng tư (Chỉ mình tôi)</option>
                                </x-ui.select>
                                <x-ui.field-error name="visibility" />
                            </div>

                            <div class="flex items-start gap-3 sm:mt-7">
                                <div class="flex items-center h-5 shrink-0">
                                    <input type="checkbox" wire:model="discoverable" id="discoverable" class="h-4 w-4 rounded border-ue-border text-ue-brand focus:ring-ue-brand mt-1" />
                                </div>
                                <div>
                                    <x-ui.label for="discoverable" class="font-bold text-ue-neutral-900">Cho phép tìm kiếm</x-ui.label>
                                    <p class="text-[10px] text-ue-text-secondary leading-relaxed mt-1">
                                        Cho phép thành viên khác tìm kiếm thấy bạn qua thanh tìm kiếm chung bằng tên hoặc email.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                {{-- Actions --}}
                <div wire:key="actions-{{ $currentStep }}" class="mt-auto pt-6 {{ $currentStep === 1 ? '' : 'sm:border-t border-ue-border bg-white' }} flex flex-row items-center gap-3 relative z-10">
                    @if($currentStep > 1)
                        <x-ui.button type="button" wire:click="previousStep" variant="ghost" class="flex-1 sm:flex-none sm:w-auto px-4" icon="arrow-left" iconPosition="left">
                            Quay lại
                        </x-ui.button>
                    @endif

                    @if($currentStep < 4)
                        <x-ui.button type="button" wire:click="nextStep" variant="primary" class="flex-1 sm:flex-none sm:w-auto px-6 shadow-sm" icon="arrow-right" iconPosition="right">
                            Tiếp tục
                        </x-ui.button>
                    @else
                        <x-ui.button type="submit" variant="primary" icon="check" class="flex-1 sm:flex-none sm:w-auto px-6 shadow-sm" iconPosition="left">
                            Hoàn tất & Khám phá
                        </x-ui.button>
                    @endif
                </div>
            </form>
        </div>
    </div>
</div>
