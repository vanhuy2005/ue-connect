<?php

namespace Tests\Feature\Auth;

use App\Mail\Auth\ResetPasswordOtpMail;
use App\Models\User;
use App\Support\Mail\MailDeliveryConfiguration;
use App\Support\Mail\SmartMailer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
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
        config(['mail.default' => 'resend']);

        $defaultMailer = config('mail.default', 'resend');

        $this->assertSame($defaultMailer, SmartMailer::resolveMailer('user@gmail.com'));
        $this->assertSame($defaultMailer, SmartMailer::resolveMailer('user@outlook.com'));
        $this->assertSame($defaultMailer, SmartMailer::resolveMailer('alumni@yahoo.com'));
    }

    public function test_mail_delivery_configuration_detects_missing_resend_settings(): void
    {
        config([
            'mail.default' => 'resend',
            'services.resend.key' => null,
            'mail.from.address' => 'no-reply@send.ueconnect.io.vn',
        ]);

        $status = MailDeliveryConfiguration::status('resend');

        $this->assertFalse($status['configured']);
        $this->assertSame('missing-required-mail-config', $status['reason']);
        $this->assertSame(['services.resend.key'], $status['context']['missing']);
    }

    public function test_mail_delivery_configuration_detects_missing_outlook_settings(): void
    {
        config([
            'mail.mailers.outlook_smtp.host' => 'smtp.office365.com',
            'mail.mailers.outlook_smtp.username' => null,
            'mail.mailers.outlook_smtp.password' => null,
            'mail.mailers.outlook_smtp.from_address' => null,
        ]);

        $status = MailDeliveryConfiguration::status('outlook_smtp');

        $this->assertFalse($status['configured']);
        $this->assertSame('missing-required-mail-config', $status['reason']);
        $this->assertSame([
            'mail.mailers.outlook_smtp.username',
            'mail.mailers.outlook_smtp.password',
            'mail.mailers.outlook_smtp.from_address',
        ], $status['context']['missing']);
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

    public function test_hcmue_otp_uses_outlook_mailer_even_when_default_mailer_is_resend(): void
    {
        config([
            'mail.default' => 'resend',
            'services.resend.key' => 're_testkey123',
            'mail.from.address' => 'no-reply@send.ueconnect.io.vn',
            'mail.mailers.outlook_smtp.host' => 'smtp.office365.com',
            'mail.mailers.outlook_smtp.username' => 'ueconnect@teacher.hcmue.edu.vn',
            'mail.mailers.outlook_smtp.password' => 'secret',
            'mail.mailers.outlook_smtp.from_address' => 'ueconnect@teacher.hcmue.edu.vn',
        ]);

        $user = User::factory()->create(['email' => '4901104055@student.hcmue.edu.vn']);

        Mail::shouldReceive('mailer')->once()->with('outlook_smtp')->andReturnSelf();
        Mail::shouldReceive('to')->once()->with($user->email)->andReturnSelf();
        Mail::shouldReceive('send')->once()->withArgs(
            fn (ResetPasswordOtpMail $mail): bool => $mail->fromAddress === 'ueconnect@teacher.hcmue.edu.vn'
        );

        $component = Volt::test('pages.auth.forgot-password')
            ->set('email', '  4901104055@student.hcmue.edu.vn  ')
            ->call('sendPasswordResetLink');

        $component->assertHasNoErrors();
        $component->assertRedirect(route('password.reset', ['email' => $user->email]));

        $this->assertTrue(Cache::has('password_reset_otp_'.$user->email));
    }

    public function test_otp_is_not_cached_when_required_mailer_configuration_is_missing(): void
    {
        Log::spy();

        config([
            'mail.default' => 'resend',
            'services.resend.key' => null,
            'mail.from.address' => 'no-reply@send.ueconnect.io.vn',
        ]);

        $user = User::factory()->create(['email' => 'testfail@gmail.com']);

        Mail::shouldReceive('mailer')->never();

        $component = Volt::test('pages.auth.forgot-password')
            ->set('email', $user->email)
            ->call('sendPasswordResetLink');

        $component->assertHasErrors(['email']);

        $this->assertFalse(Cache::has('password_reset_otp_'.$user->email));

        Log::shouldHaveReceived('warning')->with('Password reset OTP mailer is not configured', \Mockery::on(
            fn (array $context): bool => $context['mailer'] === 'resend'
                && $context['reason'] === 'missing-required-mail-config'
                && in_array('services.resend.key', $context['context']['missing'], true)
        ));
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
