<?php

namespace Database\Seeders\Uat;

use App\Enums\DetectedDocumentType;
use App\Enums\EvidenceAnalysisRecommendation;
use App\Enums\EvidenceAnalysisStatus;
use App\Enums\EvidenceCaptureMethod;
use App\Enums\EvidenceCaptureStatus;
use App\Enums\EvidenceRiskFlag;
use App\Enums\VerificationStatus;
use App\Models\AcademicProgram;
use App\Models\AuditLog;
use App\Models\EvidenceAnalysisJob;
use App\Models\EvidenceAnalysisResult;
use App\Models\EvidenceCaptureSession;
use App\Models\Faculty;
use App\Models\MediaFile;
use App\Models\User;
use App\Models\VerificationEvidence;
use App\Models\VerificationRequest;
use App\Models\VerificationReviewAction;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UatVerificationSeeder extends Seeder
{
    /** @var array<string, User> */
    private array $users = [];

    private User $reviewer;

    public function run(): void
    {
        DB::transaction(function () {
            $this->resolveUsers();
            $this->seedRequests();
        });
    }

    private function resolveUsers(): void
    {
        $this->reviewer = User::where('email', 'verification.reviewer@teacher.hcmue.edu.vn')->firstOrFail();

        foreach ([
            'unverified' => 'unverified.student@student.hcmue.edu.vn',
            'legacy' => 'student.test@student.hcmue.edu.vn',
            'student' => 'student@student.hcmue.edu.vn',
            'student2' => 'student2@student.hcmue.edu.vn',
            'alumni_pending' => 'alumni.pending@gmail.com',
            'teacher_pending' => 'teacher.pending@teacher.hcmue.edu.vn',
            'teacher_rejected' => 'teacher.rejected@teacher.hcmue.edu.vn',
            'blocked_student' => 'blocked.student@student.hcmue.edu.vn',
        ] as $key => $email) {
            $this->users[$key] = User::where('email', $email)->firstOrFail();
        }
    }

    private function seedRequests(): void
    {
        $cntt = Faculty::where('slug', 'cntt')->firstOrFail();
        $program = AcademicProgram::where('faculty_id', $cntt->id)
            ->where('slug', 'cong-nghe-thong-tin')
            ->firstOrFail();

        $cases = [
            [
                'key' => 'student_pending_camera',
                'user' => $this->users['unverified'],
                'role' => 'student',
                'status' => VerificationStatus::PENDING_REVIEW,
                'student_code' => 'SV-UAT-001',
                'note' => 'Hồ sơ sinh viên đang chờ admin duyệt.',
                'recommendation' => EvidenceAnalysisRecommendation::LikelyMatch,
                'risk_flags' => [],
            ],
            [
                'key' => 'student_under_review',
                'user' => $this->users['legacy'],
                'role' => 'student',
                'status' => VerificationStatus::UNDER_REVIEW,
                'student_code' => 'SV-UAT-002',
                'note' => 'Admin đang kiểm tra minh chứng.',
                'recommendation' => EvidenceAnalysisRecommendation::ManualReview,
                'risk_flags' => [EvidenceRiskFlag::OcrUnavailable->value],
            ],
            [
                'key' => 'student_more_info',
                'user' => $this->users['blocked_student'],
                'role' => 'student',
                'status' => VerificationStatus::NEEDS_MORE_INFORMATION,
                'student_code' => 'SV-UAT-003',
                'note' => 'Thiếu ảnh mặt trước thẻ sinh viên.',
                'recommendation' => EvidenceAnalysisRecommendation::ManualReview,
                'risk_flags' => [EvidenceRiskFlag::LowResolution->value],
            ],
            [
                'key' => 'student_approved',
                'user' => $this->users['student'],
                'role' => 'student',
                'status' => VerificationStatus::APPROVED,
                'student_code' => 'SV240001',
                'note' => 'Hồ sơ đã duyệt để kiểm thử audit và notification.',
                'recommendation' => EvidenceAnalysisRecommendation::LikelyMatch,
                'risk_flags' => [],
            ],
            [
                'key' => 'alumni_rejected',
                'user' => $this->users['alumni_pending'],
                'role' => 'alumni',
                'status' => VerificationStatus::REJECTED,
                'student_code' => 'SV-UAT-004',
                'note' => 'Minh chứng cựu sinh viên chưa đủ rõ.',
                'recommendation' => EvidenceAnalysisRecommendation::RejectRecommended,
                'risk_flags' => [EvidenceRiskFlag::DocumentTypeMismatch->value],
            ],
            [
                'key' => 'teacher_conflict',
                'user' => $this->users['teacher_pending'],
                'role' => 'teacher',
                'status' => VerificationStatus::CONFLICT,
                'student_code' => null,
                'note' => 'Thông tin cố vấn cần đối chiếu thủ công.',
                'recommendation' => EvidenceAnalysisRecommendation::Suspicious,
                'risk_flags' => [EvidenceRiskFlag::ManualReviewRequired->value],
            ],
            [
                'key' => 'teacher_suspicious',
                'user' => $this->users['teacher_rejected'],
                'role' => 'teacher',
                'status' => VerificationStatus::SUSPICIOUS,
                'student_code' => null,
                'note' => 'Minh chứng có dấu hiệu không khớp.',
                'recommendation' => EvidenceAnalysisRecommendation::Suspicious,
                'risk_flags' => [EvidenceRiskFlag::SchoolMismatch->value],
            ],
            [
                'key' => 'student_expired',
                'user' => $this->users['student2'],
                'role' => 'student',
                'status' => VerificationStatus::EXPIRED,
                'student_code' => 'SV-UAT-005',
                'note' => 'Hồ sơ hết hạn để kiểm thử trạng thái expired.',
                'recommendation' => EvidenceAnalysisRecommendation::ManualReview,
                'risk_flags' => [EvidenceRiskFlag::CaptureSessionExpired->value],
            ],
        ];

        foreach ($cases as $case) {
            $request = VerificationRequest::updateOrCreate(
                ['user_id' => $case['user']->id, 'role_requested' => $case['role']],
                [
                    'requested_identity_type' => $this->identityTypeForRole($case['role']),
                    'status' => $case['status'],
                    'submitted_name' => $case['user']->name,
                    'submitted_student_code' => $case['student_code'],
                    'submitted_faculty_id' => $cntt->id,
                    'submitted_academic_program_id' => $program->id,
                    'submitted_cohort' => $case['role'] === 'student' ? 'K48' : null,
                    'submitted_graduation_year' => $case['role'] === 'alumni' ? '2020' : null,
                    'submitted_email' => $case['user']->email,
                    'submitted_old_student_email' => $case['role'] === 'alumni' ? 'alumni.demo.old@student.hcmue.edu.vn' : null,
                    'submitted_note' => '[UAT] '.$case['note'],
                    'submitted_position' => $case['role'] === 'teacher' ? 'Cố vấn học tập' : null,
                    'submitted_organization' => $case['role'] === 'teacher' ? 'HCMUE' : null,
                    'submitted_is_academic_advisor' => $case['role'] === 'teacher',
                    'submitted_advised_class_codes' => $case['role'] === 'teacher' ? '49.CNTTD' : null,
                    'assigned_admin_id' => $this->reviewer->id,
                    'submitted_at' => now()->subDays(6),
                    'reviewed_at' => $this->isReviewed($case['status']) ? now()->subDays(2) : null,
                    'expires_at' => $case['status'] === VerificationStatus::EXPIRED ? now()->subDay() : now()->addDays(14),
                ]
            );

            $evidence = $this->evidence($request, $case['key']);
            $this->analysis($request, $evidence, $case['recommendation'], $case['risk_flags']);
            $this->reviewAction($request, $case['status'], $case['note']);
            $this->audit($request, $case['status'], $case['note']);
        }
    }

    private function evidence(VerificationRequest $request, string $seedKey): VerificationEvidence
    {
        $evidenceType = match ($request->role_requested) {
            'teacher' => 'teacher_email_screenshot',
            'alumni' => 'graduation_certificate',
            default => 'student_card',
        };

        $media = MediaFile::updateOrCreate(
            ['checksum' => 'uat-verification-'.$seedKey],
            [
                'owner_id' => $request->user_id,
                'disk' => 'local',
                'path' => 'demo/evidence/'.$seedKey.'.pdf',
                'original_name' => $seedKey.'-placeholder.pdf',
                'mime_type' => 'application/pdf',
                'extension' => 'pdf',
                'size_bytes' => 128000,
                'visibility' => 'private',
                'file_category' => 'verification_evidence',
                'metadata_json' => ['demo_seed' => true],
            ]
        );

        $session = EvidenceCaptureSession::updateOrCreate(
            ['session_token_hash' => hash('sha256', 'uat-capture-'.$seedKey)],
            [
                'user_id' => $request->user_id,
                'verification_request_id' => $request->id,
                'status' => EvidenceCaptureStatus::Completed,
                'required_evidence_type' => $evidenceType,
                'started_at' => now()->subDays(6),
                'expires_at' => now()->addDays(7),
                'completed_at' => now()->subDays(6)->addMinutes(4),
                'attempt_count' => 1,
                'client_user_agent' => 'UEConnect UAT Seeder',
            ]
        );

        return VerificationEvidence::updateOrCreate(
            ['verification_request_id' => $request->id, 'evidence_type' => $evidenceType],
            [
                'media_file_id' => $media->id,
                'evidence_link' => null,
                'user_note' => 'Minh chứng giả lập dùng cho UAT, không chứa dữ liệu thật.',
                'status' => 'uploaded',
                'capture_method' => EvidenceCaptureMethod::Camera,
                'captured_at' => now()->subDays(6)->addMinutes(3),
                'capture_session_id' => $session->id,
                'client_user_agent' => 'UEConnect UAT Seeder',
                'image_quality_score' => 0.8800,
            ]
        );
    }

    /**
     * @param  list<string>  $riskFlags
     */
    private function analysis(VerificationRequest $request, VerificationEvidence $evidence, EvidenceAnalysisRecommendation $recommendation, array $riskFlags): void
    {
        $job = EvidenceAnalysisJob::updateOrCreate(
            ['verification_evidence_id' => $evidence->id, 'provider' => 'uat-local'],
            [
                'verification_request_id' => $request->id,
                'media_file_id' => $evidence->media_file_id,
                'model_name' => 'uat-student-card-analyzer',
                'status' => EvidenceAnalysisStatus::Succeeded,
                'attempt_count' => 1,
                'queued_at' => now()->subDays(6),
                'started_at' => now()->subDays(6)->addMinute(),
                'finished_at' => now()->subDays(6)->addMinutes(2),
            ]
        );

        EvidenceAnalysisResult::updateOrCreate(
            ['analysis_job_id' => $job->id],
            [
                'verification_request_id' => $request->id,
                'verification_evidence_id' => $evidence->id,
                'document_type_detected' => DetectedDocumentType::StudentCard->value,
                'document_type_confidence' => 0.9100,
                'ocr_text' => 'UAT OCR placeholder. No real identity data.',
                'extracted_fields_json' => ['student_code' => $request->submitted_student_code, 'school' => 'HCMUE'],
                'match_result_json' => ['overall_score' => 0.86, 'demo_seed' => true],
                'risk_flags_json' => $riskFlags,
                'confidence_score' => 0.8600,
                'recommendation' => $recommendation,
                'review_summary' => 'Kết quả AI giả lập cho admin UAT, không tự động duyệt.',
            ]
        );
    }

    private function reviewAction(VerificationRequest $request, VerificationStatus $status, string $note): void
    {
        if (! $this->isReviewed($status)) {
            return;
        }

        VerificationReviewAction::updateOrCreate(
            ['verification_request_id' => $request->id, 'action_key' => $this->actionForStatus($status)],
            [
                'admin_id' => $this->reviewer->id,
                'reason' => '[UAT] '.$note,
                'instruction' => $status === VerificationStatus::NEEDS_MORE_INFORMATION ? 'Vui lòng bổ sung ảnh minh chứng rõ hơn.' : null,
                'before_snapshot_json' => ['status' => VerificationStatus::UNDER_REVIEW->value],
                'after_snapshot_json' => ['status' => $status->value],
            ]
        );
    }

    private function audit(VerificationRequest $request, VerificationStatus $status, string $note): void
    {
        if (! $this->isReviewed($status)) {
            return;
        }

        AuditLog::updateOrCreate(
            ['action_key' => 'verification.'.$this->actionForStatus($status), 'target_type' => 'verification_request', 'target_id' => $request->id],
            [
                'actor_id' => $this->reviewer->id,
                'actor_type' => 'user',
                'before_values' => ['status' => VerificationStatus::UNDER_REVIEW->value],
                'after_values' => ['status' => $status->value],
                'reason' => '[UAT] '.$note,
                'metadata' => ['demo_seed' => true],
                'created_at' => now(),
            ]
        );
    }

    private function identityTypeForRole(string $role): string
    {
        return match ($role) {
            'alumni' => 'alumni',
            'teacher' => 'teacher_advisor',
            default => 'current_student',
        };
    }

    private function isReviewed(VerificationStatus $status): bool
    {
        return in_array($status, [
            VerificationStatus::NEEDS_MORE_INFORMATION,
            VerificationStatus::APPROVED,
            VerificationStatus::REJECTED,
            VerificationStatus::CONFLICT,
            VerificationStatus::SUSPICIOUS,
            VerificationStatus::EXPIRED,
        ], true);
    }

    private function actionForStatus(VerificationStatus $status): string
    {
        return match ($status) {
            VerificationStatus::NEEDS_MORE_INFORMATION => 'need_more_information',
            VerificationStatus::APPROVED => 'approve',
            VerificationStatus::REJECTED => 'reject',
            VerificationStatus::CONFLICT => 'mark_conflict',
            VerificationStatus::SUSPICIOUS => 'mark_suspicious',
            VerificationStatus::EXPIRED => 'expire',
            default => 'review',
        };
    }
}
