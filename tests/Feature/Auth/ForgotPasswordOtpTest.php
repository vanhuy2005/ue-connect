<?php

namespace Tests\Feature\Auth;

use App\Mail\Auth\ResetPasswordOtpMail;
use App\Models\User;
use App\Support\Mail\SmartMailer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Livewire\Volt\Volt;
use Tests\TestCase;

class ForgotPasswordOtpTest extends TestCase
{
    use RefreshDatabase;

    /**
     * SmartMailer routes HCMUE domains to outlook_smtp.
     */
    public function test_smart_mailer_routes_hcmue_to_outlook(): void
    {
        $this->assertSame('outlook_smtp', SmartMailer::resolveMailer('4901104055@student.hcmue.edu.vn'));
        $this->assertSame('outlook_smtp', SmartMailer::resolveMailer('ntt239@teacher.hcmue.edu.vn'));
        $this->assertSame('outlook_smtp', SmartMailer::resolveMailer('admin@hcmue.edu.vn'));
    }

    /**
     * SmartMailer routes non-HCMUE domains to the default (resend) mailer.
     */
    public function test_smart_mailer_routes_personal_email_to_resend(): void
    {
        $defaultMailer = config('mail.default', 'resend');

        $this->assertSame($defaultMailer, SmartMailer::resolveMailer('user@gmail.com'));
        $this->assertSame($defaultMailer, SmartMailer::resolveMailer('user@outlook.com'));
        $this->assertSame($defaultMailer, SmartMailer::resolveMailer('alumni@yahoo.com'));
    }

    /**
     * OTP được gửi và lưu cache khi user tồn tại.
     */
    public function test_otp_is_sent_and_cached_for_existing_user(): void
    {
        Mail::fake();

        $user = User::factory()->create();

        $component = Volt::test('pages.auth.forgot-password')
            ->set('email', $user->email)
            ->call('sendPasswordResetLink');

        $component->assertHasNoErrors();
        $component->assertRedirect(route('password.reset', ['email' => $user->email]));

        Mail::assertSent(ResetPasswordOtpMail::class);
        $this->assertTrue(Cache::has('password_reset_otp_'.$user->email));
    }

    /**
     * Người dùng không tồn tại vẫn redirect (không lộ thông tin email).
     */
    public function test_non_existent_email_still_redirects_silently(): void
    {
        Mail::fake();

        $component = Volt::test('pages.auth.forgot-password')
            ->set('email', 'nonexistent@student.hcmue.edu.vn')
            ->call('sendPasswordResetLink');

        $component->assertRedirect();
        Mail::assertNothingSent();
        $this->assertFalse(Cache::has('password_reset_otp_nonexistent@student.hcmue.edu.vn'));
    }

    /**
     * Khi gửi mail thất bại, OTP bị xóa khỏi cache và hiển thị lỗi.
     */
    public function test_otp_is_cleared_from_cache_when_mail_fails(): void
    {
        $user = User::factory()->create(['email' => 'testfail@gmail.com']);

        Mail::shouldReceive('mailer')->andReturnSelf();
        Mail::shouldReceive('to')->andReturnSelf();
        Mail::shouldReceive('send')->andThrow(new \RuntimeException('Connection refused'));

        $component = Volt::test('pages.auth.forgot-password')
            ->set('email', 'testfail@gmail.com')
            ->call('sendPasswordResetLink');

        $component->assertHasErrors(['email']);
        $this->assertFalse(Cache::has('password_reset_otp_testfail@gmail.com'));
    }
}
