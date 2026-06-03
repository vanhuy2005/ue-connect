<?php

namespace App\Actions\Admin;

use App\Models\VerificationRequest;
use App\Notifications\VerificationReviewedNotification;
use App\Services\AuditService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class ReviewVerificationAction
{
    public function execute(VerificationRequest $requestModel, array $data, AuditService $audit)
    {
        DB::beginTransaction();
        try {
            $before = $requestModel->toArray();

            $action = $data['action'];
            $reason = $data['reason'];

            switch ($action) {
                case 'approve':
                    $requestModel->status = 'approved';
                    $requestModel->reviewed_at = now();
                    break;
                case 'reject':
                    $requestModel->status = 'rejected';
                    $requestModel->reviewed_at = now();
                    break;
                case 'need_more_information':
                    $requestModel->status = 'need_more_information';
                    $requestModel->review_instruction = $data['instruction'] ?? null;
                    break;
                case 'mark_conflict':
                    $requestModel->status = 'conflict';
                    break;
                case 'suspend_suspicious':
                    $requestModel->status = 'suspended_by_admin';
                    break;
                case 'edit_before_approve':
                    // apply corrected fields
                    if (! empty($data['corrected_fields']) && is_array($data['corrected_fields'])) {
                        foreach ($data['corrected_fields'] as $k => $v) {
                            if (in_array($k, ['mssv', 'faculty', 'major', 'cohort', 'class_code', 'email'])) {
                                $requestModel->{$k} = $v;
                            }
                        }
                    }
                    $requestModel->status = 'approved';
                    $requestModel->reviewed_at = now();
                    break;
                default:
                    throw new \InvalidArgumentException('Unknown action');
            }

            $requestModel->reviewed_by = auth()->id();
            $requestModel->review_reason = $reason;
            $requestModel->save();

            $after = $requestModel->toArray();

            $audit->log([
                'action' => 'verification_action',
                'target_type' => 'verification_request',
                'target_id' => $requestModel->id,
                'before_values' => $before,
                'after_values' => $after,
                'reason' => $reason,
            ]);

            DB::commit();

            // Notify the user (non-blocking)
            try {
                $user = $requestModel->user;
                if ($user && ($data['notify_user'] ?? true)) {
                    Notification::send($user, new VerificationReviewedNotification($requestModel));
                }
            } catch (\Throwable $e) {
                report($e);
            }

            return $requestModel;
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
