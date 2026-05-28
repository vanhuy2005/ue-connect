<?php

use App\Models\VerificationRequest;
use App\Models\Faculty;
use App\Enums\VerificationStatus;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    public string $search = '';
    public string $status = 'pending_review'; // default to pending_review
    public string $role = '';
    public ?int $faculty_id = null;
    public string $cohort = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'status' => ['except' => ''],
        'role' => ['except' => ''],
        'faculty_id' => ['except' => null],
        'cohort' => ['except' => ''],
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatus(): void
    {
        $this->resetPage();
    }

    public function updatingRole(): void
    {
        $this->resetPage();
    }

    public function updatingFacultyId(): void
    {
        $this->resetPage();
    }

    public function updatingCohort(): void
    {
        $this->resetPage();
    }

    public function getFacultiesProperty()
    {
        return Faculty::where('status', 'active')->orderBy('name')->get();
    }

    public function getRequestsProperty()
    {
        $query = VerificationRequest::with(['user', 'submittedFaculty', 'submittedAcademicProgram'])
            ->latest('submitted_at');

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('submitted_name', 'like', '%' . $this->search . '%')
                  ->orWhere('submitted_student_code', 'like', '%' . $this->search . '%')
                  ->orWhere('submitted_email', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->status) {
            $query->where('status', $this->status);
        }

        if ($this->role) {
            $query->where('role_requested', $this->role);
        }

        if ($this->faculty_id) {
            $query->where('submitted_faculty_id', $this->faculty_id);
        }

        if ($this->cohort) {
            $query->where('submitted_cohort', 'like', '%' . $this->cohort . '%');
        }

        return $query->paginate(15);
    }
}; ?>

<div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    {{-- Header --}}
    <div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-ue-text">Phê duyệt định danh tài khoản</h1>
            <p class="text-sm text-ue-text-secondary mt-1">Quản lý hồ sơ xác thực của Sinh viên, Cựu sinh viên, và Cố vấn học tập HCMUE.</p>
        </div>
    </div>

    {{-- Filters block --}}
    <x-ui.card class="mb-6">
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-4">
            {{-- Search --}}
            <div>
                <x-ui.label for="search" class="text-xs">Tìm kiếm</x-ui.label>
                <x-ui.input wire:model.live.debounce.300ms="search" id="search" placeholder="Tên, MSSV, Email..." class="mt-1 h-9 text-xs" />
            </div>

            {{-- Status --}}
            <div>
                <x-ui.label for="status" class="text-xs">Trạng thái hồ sơ</x-ui.label>
                <x-ui.select wire:model.live="status" id="status" class="mt-1 h-9 text-xs py-1">
                    <option value="">-- Tất cả --</option>
                    <option value="pending_review">Chờ duyệt (Mới)</option>
                    <option value="under_review">Đang kiểm tra</option>
                    <option value="resubmitted">Gửi lại (Chờ duyệt)</option>
                    <option value="needs_more_information">Cần bổ sung thông tin</option>
                    <option value="approved">Đã phê duyệt</option>
                    <option value="rejected">Bị từ chối</option>
                    <option value="conflict">Xung đột MSSV</option>
                    <option value="suspicious">Nghi ngờ / Giả mạo</option>
                </x-ui.select>
            </div>

            {{-- Role --}}
            <div>
                <x-ui.label for="role" class="text-xs">Vai trò yêu cầu</x-ui.label>
                <x-ui.select wire:model.live="role" id="role" class="mt-1 h-9 text-xs py-1">
                    <option value="">-- Tất cả --</option>
                    <option value="student">Sinh viên</option>
                    <option value="alumni">Cựu sinh viên</option>
                    <option value="advisor">Cố vấn / Giảng viên</option>
                </x-ui.select>
            </div>

            {{-- Faculty --}}
            <div>
                <x-ui.label for="faculty_id" class="text-xs">Khoa đào tạo</x-ui.label>
                <x-ui.select wire:model.live="faculty_id" id="faculty_id" class="mt-1 h-9 text-xs py-1">
                    <option value="">-- Tất cả --</option>
                    @foreach ($this->faculties as $fac)
                        <option value="{{ $fac->id }}">{{ $fac->name }}</option>
                    @endforeach
                </x-ui.select>
            </div>

            {{-- Cohort --}}
            <div>
                <x-ui.label for="cohort" class="text-xs">Khóa học</x-ui.label>
                <x-ui.input wire:model.live.debounce.300ms="cohort" id="cohort" placeholder="Ví dụ: K48" class="mt-1 h-9 text-xs" />
            </div>
        </div>
    </x-ui.card>

    {{-- Requests Table / Queue --}}
    <x-ui.card padding="none" class="overflow-hidden">
        {{-- Desktop view --}}
        <div class="hidden md:block overflow-x-auto">
            <table class="min-w-full divide-y divide-ue-border text-left">
                <thead class="bg-ue-surface-subtle text-xs font-bold text-ue-text-muted uppercase tracking-wider">
                    <tr>
                        <th scope="col" class="px-6 py-4">Tên & Vai trò</th>
                        <th scope="col" class="px-6 py-4">MSSV / Mã</th>
                        <th scope="col" class="px-6 py-4">Khoa & Ngành</th>
                        <th scope="col" class="px-6 py-4">Trạng thái</th>
                        <th scope="col" class="px-6 py-4">Thời gian gửi</th>
                        <th scope="col" class="px-6 py-4 text-right">Thao tác</th>
                    </tr>
                </thead>
                <tbody class="bg-ue-surface divide-y divide-ue-border text-sm">
                    @forelse ($this->requests as $req)
                        @php
                            $badgeVariant = match($req->status) {
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

                            $roleBadgeVariant = match($req->role_requested) {
                                'student' => 'student',
                                'alumni' => 'alumni',
                                'advisor' => 'advisor',
                                default => 'neutral',
                            };

                            $roleLabel = match($req->role_requested) {
                                'student' => 'Sinh viên',
                                'alumni' => 'Cựu sinh viên',
                                'advisor' => 'Cố vấn',
                                default => $req->role_requested,
                            };
                        @endphp
                        <tr class="hover:bg-ue-surface-hover transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-3">
                                    <div>
                                        <div class="font-bold text-ue-text">{{ $req->submitted_name }}</div>
                                        <div class="text-xs text-ue-text-muted mt-0.5">{{ $req->submitted_email }}</div>
                                    </div>
                                    <x-ui.badge :variant="$roleBadgeVariant" size="sm">{{ $roleLabel }}</x-ui.badge>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap font-medium text-ue-text">
                                {{ $req->submitted_student_code ?: 'N/A' }}
                                @if ($req->submitted_cohort)
                                    <span class="text-xs text-ue-text-muted block mt-0.5">Khóa {{ $req->submitted_cohort }}</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @if ($req->submittedFaculty)
                                    <div class="font-semibold text-ue-text text-xs">{{ $req->submittedFaculty->name }}</div>
                                @endif
                                @if ($req->submittedAcademicProgram)
                                    <div class="text-[11px] text-ue-text-muted mt-0.5">{{ $req->submittedAcademicProgram->name }}</div>
                                @endif
                                @if (!$req->submittedFaculty && !$req->submittedAcademicProgram)
                                    <span class="text-xs text-ue-text-disabled">N/A</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <x-ui.badge :variant="$badgeVariant">
                                    {{ match($req->status) {
                                        VerificationStatus::PENDING_REVIEW => 'Chờ duyệt',
                                        VerificationStatus::UNDER_REVIEW => 'Đang kiểm tra',
                                        VerificationStatus::RESUBMITTED => 'Gửi lại',
                                        VerificationStatus::NEEDS_MORE_INFORMATION => 'Yêu cầu thêm',
                                        VerificationStatus::APPROVED => 'Đã duyệt',
                                        VerificationStatus::REJECTED => 'Từ chối',
                                        VerificationStatus::CONFLICT => 'Xung đột MSSV',
                                        VerificationStatus::SUSPICIOUS => 'Nghi ngờ',
                                        default => $req->status->value,
                                    } }}
                                </x-ui.badge>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-xs text-ue-text-muted font-medium">
                                {{ $req->submitted_at ? $req->submitted_at->format('H:i d/m/Y') : 'N/A' }}
                                <span class="block text-[10px] text-ue-text-disabled mt-0.5">{{ $req->submitted_at ? $req->submitted_at->diffForHumans() : '' }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <x-ui.button href="{{ route('admin.verifications.detail', ['id' => $req->id]) }}" variant="secondary" size="sm" icon="eye">
                                    Chi tiết
                                </x-ui.button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <x-ui.empty-state icon="shield" title="Không tìm thấy yêu cầu nào" description="Hiện tại không có hồ sơ xác thực nào khớp với bộ lọc của bạn." />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Mobile card list view --}}
        <div class="md:hidden divide-y divide-ue-border">
            @forelse ($this->requests as $req)
                @php
                    $badgeVariant = match($req->status) {
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

                    $roleBadgeVariant = match($req->role_requested) {
                        'student' => 'student',
                        'alumni' => 'alumni',
                        'advisor' => 'advisor',
                        default => 'neutral',
                    };

                    $roleLabel = match($req->role_requested) {
                        'student' => 'Sinh viên',
                        'alumni' => 'Cựu sinh viên',
                        'advisor' => 'Cố vấn',
                        default => $req->role_requested,
                    };
                @endphp
                <div class="p-4 bg-ue-surface hover:bg-ue-surface-hover transition-colors flex flex-col gap-3">
                    {{-- Requester + Role --}}
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <div class="font-bold text-ue-text text-base leading-snug">{{ $req->submitted_name }}</div>
                            <div class="text-xs text-ue-text-muted mt-0.5">{{ $req->submitted_email }}</div>
                        </div>
                        <x-ui.badge :variant="$roleBadgeVariant" size="sm">{{ $roleLabel }}</x-ui.badge>
                    </div>

                    {{-- Identity details (MSSV, Faculty/Academic program) --}}
                    <div class="grid grid-cols-2 gap-x-4 gap-y-2 text-xs border-t border-b border-ue-border py-2.5">
                        <div>
                            <span class="text-ue-text-muted block text-[10px] uppercase font-bold tracking-wider">MSSV / Mã</span>
                            <span class="font-semibold text-ue-text mt-0.5 block">
                                {{ $req->submitted_student_code ?: 'N/A' }}
                                @if ($req->submitted_cohort)
                                    <span class="text-xs text-ue-text-muted font-normal"> (Khóa {{ $req->submitted_cohort }})</span>
                                @endif
                            </span>
                        </div>
                        <div>
                            <span class="text-ue-text-muted block text-[10px] uppercase font-bold tracking-wider">Khoa & Ngành</span>
                            <span class="font-semibold text-ue-text mt-0.5 block leading-tight">
                                @if ($req->submittedFaculty)
                                    {{ $req->submittedFaculty->name }}
                                @else
                                    N/A
                                @endif
                                @if ($req->submittedAcademicProgram)
                                    <span class="text-[10px] text-ue-text-muted block font-normal mt-0.5">{{ $req->submittedAcademicProgram->name }}</span>
                                @endif
                            </span>
                        </div>
                    </div>

                    {{-- Status + Submitted Time + Action --}}
                    <div class="flex items-center justify-between gap-4 mt-1">
                        <div class="flex flex-col gap-1">
                            <div class="flex items-center gap-1.5">
                                <x-ui.badge :variant="$badgeVariant" size="sm">
                                    {{ match($req->status) {
                                        VerificationStatus::PENDING_REVIEW => 'Chờ duyệt',
                                        VerificationStatus::UNDER_REVIEW => 'Đang kiểm tra',
                                        VerificationStatus::RESUBMITTED => 'Gửi lại',
                                        VerificationStatus::NEEDS_MORE_INFORMATION => 'Yêu cầu thêm',
                                        VerificationStatus::APPROVED => 'Đã duyệt',
                                        VerificationStatus::REJECTED => 'Từ chối',
                                        VerificationStatus::CONFLICT => 'Xung đột MSSV',
                                        VerificationStatus::SUSPICIOUS => 'Nghi ngờ',
                                        default => $req->status->value,
                                    } }}
                                </x-ui.badge>
                            </div>
                            <span class="text-[10px] text-ue-text-muted font-medium">
                                Gửi: {{ $req->submitted_at ? $req->submitted_at->diffForHumans() : 'N/A' }}
                            </span>
                        </div>
                        
                        <x-ui.button href="{{ route('admin.verifications.detail', ['id' => $req->id]) }}" variant="secondary" size="sm" icon="eye">
                            Chi tiết
                        </x-ui.button>
                    </div>
                </div>
            @empty
                <div class="px-6 py-12 text-center">
                    <x-ui.empty-state icon="shield" title="Không tìm thấy yêu cầu nào" description="Hiện tại không có hồ sơ xác thực nào khớp với bộ lọc của bạn." />
                </div>
            @endforelse
        </div>

        {{-- Pagination --}}
        @if ($this->requests->hasPages())
            <div class="px-6 py-4 border-t border-ue-border bg-ue-surface-subtle">
                {{ $this->requests->links() }}
            </div>
        @endif
    </x-ui.card>
</div>
