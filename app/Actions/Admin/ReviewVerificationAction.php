<?php

namespace App\Actions\Admin;

use App\Enums\AccountStatus;
use App\Enums\VerificationStatus;
use App\Models\AdvisorProfile;
use App\Models\AlumniProfile;
use App\Models\Profile;
use App\Models\StudentProfile;
use App\Models\User;
use App\Models\VerificationRequest;
use App\Models\VerificationReviewAction;
use App\Notifications\VerificationReviewedNotification;
use App\Services\AuditLogService;
use App\Services\AuditService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;

class ReviewVerificationAction
{
    /**
     * Start reviewing a pending verification request.
     */
    public function startReview(VerificationRequest $requestModel, ?User $admin = null): VerificationRequest
    {
        $admin ??= Auth::user();
        $this->authorizeReviewer($admin);

        return DB::transaction(function () use ($requestModel, $admin) {
            $requestModel->refresh();

            if (! in_array($requestModel->status, [VerificationStatus::PENDING_REVIEW, VerificationStatus::RESUBMITTED], true)) {
                return $requestModel;
            }

            $before = $requestModel->toArray();

            $requestModel->forceFill([
                'status' => VerificationStatus::UNDER_REVIEW,
                'assigned_admin_id' => $admin->id,
            ])->save();

            $this->recordReviewAction(
                requestModel: $requestModel,
                actionKey: 'start_review',
                reason: 'Bắt đầu kiểm duyệt hồ sơ.',
                before: $before,
                after: $requestModel->fresh()->toArray(),
                admin: $admin
            );

            AuditLogService::log(
                actorId: $admin->id,
                actorType: 'admin',
                actionKey: 'verification.start_review',
                targetType: 'verification_requests',
                targetId: $requestModel->id,
                beforeSnapshot: $before,
                afterSnapshot: $requestModel->fresh()->toArray(),
                reason: 'Admin bắt đầu kiểm duyệt.'
            );

            return $requestModel->fresh();
        });
    }

    /**
     * Apply a terminal or follow-up verification review decision.
     *
     * @param  array<string, mixed>  $data
     */
    public function execute(VerificationRequest $requestModel, array $data, ?AuditService $audit = null, ?User $admin = null): VerificationRequest
    {
        $admin ??= Auth::user();
        $this->authorizeReviewer($admin);

        return DB::transaction(function () use ($requestModel, $data, $admin) {
            $requestModel->refresh();
            $user = $requestModel->user;

            if (! $user) {
                throw ValidationException::withMessages([
                    'general' => 'Không tìm thấy tài khoản người dùng liên quan.',
                ]);
            }

            $before = $requestModel->toArray();
            $userBefore = $user->toArray();
            $action = $this->normalizeAction((string) $data['action']);
            $reason = trim((string) ($data['reason'] ?? ''));
            $instruction = trim((string) ($data['instruction'] ?? ''));

            $this->validateDecision($requestModel, $action, $reason, $instruction);

            if ($action === 'edit_before_approve') {
                $this->applyCorrectedFields($requestModel, $data['corrected_fields'] ?? []);
                $action = 'approve';
            }

            if ($action === 'approve') {
                $this->ensureStudentCodeIsUnique($requestModel);
            }

            match ($action) {
                'approve' => $this->approve($requestModel, $user, $reason, $admin),
                'reject' => $this->reject($requestModel, $user, $reason, $admin),
                'need_more_information' => $this->requestMoreInformation($requestModel, $user, $instruction, $admin),
                'mark_conflict' => $this->markConflict($requestModel, $user, $reason, $admin),
                'suspend_suspicious' => $this->markSuspicious($requestModel, $user, $reason, $admin),
                default => throw ValidationException::withMessages(['action' => 'Thao tác kiểm duyệt không hợp lệ.']),
            };

            $after = $requestModel->fresh()->toArray();

            $reviewReason = match ($action) {
                'approve' => $reason !== '' ? $reason : 'Đã kiểm duyệt và phê duyệt thông tin hợp lệ.',
                'need_more_information' => 'Yêu cầu bổ sung thêm thông tin.',
                default => $reason,
            };

            $reviewActionKey = match ($action) {
                'mark_conflict' => 'mark_conflict',
                'suspend_suspicious' => 'suspend_suspicious',
                'need_more_information' => 'need_more_information',
                default => $action,
            };

            $this->recordReviewAction(
                requestModel: $requestModel,
                actionKey: $reviewActionKey,
                reason: $reviewReason,
                before: $before,
                after: $after,
                admin: $admin,
                instruction: $action === 'need_more_information' ? $instruction : null
            );

            AuditLogService::log(
                actorId: $admin->id,
                actorType: 'admin',
                actionKey: 'verification.'.$reviewActionKey,
                targetType: 'verification_requests',
                targetId: $requestModel->id,
                beforeSnapshot: $before,
                afterSnapshot: $after,
                reason: $action === 'need_more_information' ? $instruction : $reviewReason
            );

            if ($user->wasChanged('account_status') || $user->wasChanged('account_status_reason')) {
                AuditLogService::log(
                    actorId: $admin->id,
                    actorType: 'admin',
                    actionKey: 'user.update_status',
                    targetType: 'users',
                    targetId: $user->id,
                    beforeSnapshot: $userBefore,
                    afterSnapshot: $user->fresh()->toArray(),
                    reason: $this->userStatusAuditReason($action, $reviewReason)
                );
            }

            try {
                if ($user && ($data['notify_user'] ?? true)) {
                    Notification::send($user, new VerificationReviewedNotification($requestModel->fresh()));
                }
            } catch (\Throwable $e) {
                report($e);
            }

            return $requestModel->fresh();
        });
    }

    private function authorizeReviewer(?User $admin): void
    {
        if (! $admin) {
            throw new AuthorizationException;
        }

        Gate::forUser($admin)->authorize('act', VerificationRequest::class);
    }

    private function approve(VerificationRequest $requestModel, User $user, string $reason, User $admin): void
    {
        $profile = Profile::updateOrCreate(
            ['user_id' => $user->id],
            [
                'display_name' => $requestModel->submitted_name,
                'role_type' => $requestModel->role_requested,
                'profile_status' => 'incomplete',
            ]
        );

        if ($requestModel->role_requested === 'student') {
            StudentProfile::updateOrCreate(
                ['profile_id' => $profile->id],
                [
                    'student_code' => $requestModel->submitted_student_code,
                    'faculty_id' => $requestModel->submitted_faculty_id,
                    'academic_program_id' => $requestModel->submitted_academic_program_id,
                    'cohort' => $requestModel->submitted_cohort,
                ]
            );
        } elseif ($requestModel->role_requested === 'alumni') {
            AlumniProfile::updateOrCreate(
                ['profile_id' => $profile->id],
                [
                    'faculty_id' => $requestModel->submitted_faculty_id,
                    'academic_program_id' => $requestModel->submitted_academic_program_id,
                    'cohort' => $requestModel->submitted_cohort,
                    'graduation_year' => $requestModel->submitted_graduation_year,
                ]
            );
        } elseif (in_array($requestModel->role_requested, ['teacher', 'advisor'], true)) {
            AdvisorProfile::updateOrCreate(
                ['profile_id' => $profile->id],
                [
                    'faculty_id' => $requestModel->submitted_faculty_id,
                    'department' => $requestModel->submitted_organization,
                    'title' => $requestModel->submitted_position ?: 'Giảng viên',
                    'is_academic_advisor' => $requestModel->submitted_is_academic_advisor,
                    'advised_class_codes' => $requestModel->submitted_advised_class_codes,
                ]
            );
        }

        $user->syncRoles([$requestModel->role_requested === 'advisor' ? 'teacher' : $requestModel->role_requested]);
        $user->forceFill([
            'account_status' => AccountStatus::PROFILE_INCOMPLETE,
            'account_status_reason' => null,
        ])->save();

        $requestModel->forceFill([
            'status' => VerificationStatus::APPROVED,
            'reviewed_at' => now(),
            'reviewed_by' => $admin->id,
            'review_reason' => $reason !== '' ? $reason : 'Đã kiểm duyệt và phê duyệt thông tin hợp lệ.',
            'review_instruction' => null,
        ])->save();
    }

    private function reject(VerificationRequest $requestModel, User $user, string $reason, User $admin): void
    {
        $requestModel->forceFill([
            'status' => VerificationStatus::REJECTED,
            'reviewed_at' => now(),
            'reviewed_by' => $admin->id,
            'review_reason' => $reason,
            'review_instruction' => null,
        ])->save();

        $user->forceFill([
            'account_status' => AccountStatus::REGISTERED,
            'account_status_reason' => null,
        ])->save();
    }

    private function requestMoreInformation(VerificationRequest $requestModel, User $user, string $instruction, User $admin): void
    {
        $requestModel->forceFill([
            'status' => VerificationStatus::NEEDS_MORE_INFORMATION,
            'reviewed_at' => now(),
            'reviewed_by' => $admin->id,
            'review_reason' => 'Yêu cầu bổ sung thêm thông tin.',
            'review_instruction' => $instruction,
        ])->save();

        $user->forceFill([
            'account_status' => AccountStatus::REGISTERED,
            'account_status_reason' => null,
        ])->save();
    }

    private function markConflict(VerificationRequest $requestModel, User $user, string $reason, User $admin): void
    {
        $requestModel->forceFill([
            'status' => VerificationStatus::CONFLICT,
            'reviewed_at' => now(),
            'reviewed_by' => $admin->id,
            'review_reason' => $reason,
            'review_instruction' => null,
        ])->save();

        $user->forceFill([
            'account_status' => AccountStatus::RESTRICTED,
            'account_status_reason' => 'Xung đột mã số định danh hoặc thông tin hồ sơ.',
        ])->save();
    }

    private function markSuspicious(VerificationRequest $requestModel, User $user, string $reason, User $admin): void
    {
        $requestModel->forceFill([
            'status' => VerificationStatus::SUSPICIOUS,
            'reviewed_at' => now(),
            'reviewed_by' => $admin->id,
            'review_reason' => $reason,
            'review_instruction' => null,
        ])->save();

        $user->forceFill([
            'account_status' => AccountStatus::SUSPENDED,
            'account_status_reason' => 'Tài khoản có dấu hiệu giả mạo thông tin minh chứng.',
        ])->save();
    }

    /**
     * @param  array<string, mixed>  $correctedFields
     */
    private function applyCorrectedFields(VerificationRequest $requestModel, mixed $correctedFields): void
    {
        if (! is_array($correctedFields)) {
            return;
        }

        $allowedFields = [
            'submitted_name',
            'submitted_student_code',
            'submitted_faculty_id',
            'submitted_academic_program_id',
            'submitted_cohort',
            'submitted_graduation_year',
            'submitted_email',
            'submitted_old_student_email',
            'submitted_position',
            'submitted_organization',
        ];

        foreach ($correctedFields as $field => $value) {
            if (in_array($field, $allowedFields, true)) {
                $requestModel->{$field} = $value;
            }
        }
    }

    private function validateDecision(VerificationRequest $requestModel, string $action, string $reason, string $instruction): void
    {
        if (! in_array($action, ['approve', 'reject', 'need_more_information', 'mark_conflict', 'suspend_suspicious', 'edit_before_approve'], true)) {
            throw ValidationException::withMessages(['action' => 'Thao tác kiểm duyệt không hợp lệ.']);
        }

        if (in_array($action, ['reject', 'mark_conflict', 'suspend_suspicious'], true) && mb_strlen($reason) < 5) {
            throw ValidationException::withMessages(['reason' => 'Vui lòng cung cấp lý do cụ thể.']);
        }

        if ($action === 'need_more_information' && mb_strlen($instruction) < 5) {
            throw ValidationException::withMessages(['instruction' => 'Vui lòng cung cấp hướng dẫn bổ sung thông tin cụ thể.']);
        }

    }

    private function normalizeAction(string $action): string
    {
        return match ($action) {
            'conflict' => 'mark_conflict',
            'suspicious' => 'suspend_suspicious',
            default => $action,
        };
    }

    private function ensureStudentCodeIsUnique(VerificationRequest $requestModel): void
    {
        if ($requestModel->role_requested !== 'student' || ! $requestModel->submitted_student_code) {
            return;
        }

        $exists = StudentProfile::query()
            ->where('student_code', $requestModel->submitted_student_code)
            ->whereHas('profile', function ($query) use ($requestModel) {
                $query->where('user_id', '!=', $requestModel->user_id);
            })
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'general' => 'Mã số sinh viên (MSSV) này đã được sử dụng bởi một tài khoản khác.',
            ]);
        }
    }

    /**
     * @param  array<string, mixed>  $before
     * @param  array<string, mixed>  $after
     */
    private function recordReviewAction(
        VerificationRequest $requestModel,
        string $actionKey,
        string $reason,
        array $before,
        array $after,
        User $admin,
        ?string $instruction = null
    ): void {
        VerificationReviewAction::create([
            'verification_request_id' => $requestModel->id,
            'admin_id' => $admin->id,
            'action_key' => $actionKey,
            'reason' => $reason,
            'instruction' => $instruction,
            'before_snapshot_json' => $before,
            'after_snapshot_json' => $after,
        ]);
    }

    private function userStatusAuditReason(string $action, string $reviewReason): string
    {
        return match ($action) {
            'approve' => 'Tài khoản được phê duyệt định danh thành công.',
            'reject' => 'Tài khoản bị từ chối hồ sơ xác thực. Lý do: '.$reviewReason,
            'need_more_information' => 'Tài khoản cần bổ sung thông tin xác thực.',
            'mark_conflict' => 'Đánh dấu tài khoản bị hạn chế do xung đột mã định danh. Chi tiết: '.$reviewReason,
            'suspend_suspicious' => 'Đình chỉ hoạt động tài khoản do hồ sơ minh chứng giả mạo. Lý do: '.$reviewReason,
            default => $reviewReason,
        };
    }
}
