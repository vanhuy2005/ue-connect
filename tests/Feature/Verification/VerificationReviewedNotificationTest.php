<?php

namespace Tests\Feature\Verification;

use App\Enums\VerificationStatus;
use App\Models\User;
use App\Models\VerificationRequest;
use App\Notifications\VerificationReviewedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VerificationReviewedNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_notification_serializes_enum_status_for_all_channels(): void
    {
        $user = User::factory()->create();
        $verificationRequest = VerificationRequest::create([
            'user_id' => $user->id,
            'role_requested' => 'student',
            'status' => VerificationStatus::APPROVED,
            'submitted_name' => 'Nguyen Van Student',
            'submitted_email' => 'student@student.hcmue.edu.vn',
            'submitted_at' => now(),
        ])->fresh();

        $this->assertSame(VerificationStatus::APPROVED, $verificationRequest->status);

        $notification = new VerificationReviewedNotification($verificationRequest);

        $mail = $notification->toMail($user);
        $databasePayload = $notification->toArray($user);
        $webPushPayload = $notification->toWebPush($user)->toArray();

        $this->assertStringContainsString(
            'Your verification request status has been updated: approved',
            implode("\n", $mail->introLines)
        );
        $this->assertSame('approved', $databasePayload['status']);
        $this->assertSame('Yêu cầu xác thực danh tính của bạn đã được chấp nhận.', $webPushPayload['body']);
    }

    public function test_notification_handles_missing_status_as_updated(): void
    {
        $user = User::factory()->create();
        $verificationRequest = new VerificationRequest([
            'user_id' => $user->id,
            'role_requested' => 'student',
            'submitted_name' => 'Nguyen Van Student',
            'submitted_email' => 'student@student.hcmue.edu.vn',
        ]);

        $notification = new VerificationReviewedNotification($verificationRequest);

        $mail = $notification->toMail($user);

        $this->assertStringContainsString(
            'Your verification request status has been updated: updated',
            implode("\n", $mail->introLines)
        );
    }
}
