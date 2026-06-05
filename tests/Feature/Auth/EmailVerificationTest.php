<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Livewire\Volt\Volt;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_email_verification_screen_can_be_rendered(): void
    {
        $user = User::factory()->unverified()->create();

        $response = $this->actingAs($user)->get('/verify-email');

        $response
            ->assertSeeVolt('pages.auth.verify-email')
            ->assertStatus(200);
    }

    public function test_registration_sends_email_verification_notification(): void
    {
        Notification::fake();

        Volt::test('pages.auth.register')
            ->set('name', 'Test User')
            ->set('email', '4901104055@student.hcmue.edu.vn')
            ->set('password', 'password')
            ->set('password_confirmation', 'password')
            ->call('register');

        $user = User::where('email', '4901104055@student.hcmue.edu.vn')->firstOrFail();

        Notification::assertSentTo($user, VerifyEmail::class);
    }

    public function test_verification_notice_resends_email_through_configured_outlook_smtp(): void
    {
        Notification::fake();

        config([
            'mail.default' => 'smtp',
            'mail.mailers.smtp.host' => 'smtp.office365.com',
            'mail.mailers.smtp.username' => 'ueconnect@teacher.hcmue.edu.vn',
            'mail.mailers.smtp.password' => 'secret',
            'mail.from.address' => 'ueconnect@teacher.hcmue.edu.vn',
        ]);

        $user = User::factory()->unverified()->create();

        $this->actingAs($user);

        Volt::test('pages.auth.verify-email')
            ->call('sendVerification')
            ->assertHasNoErrors();

        Notification::assertSentTo($user, VerifyEmail::class);
    }

    public function test_verification_notice_does_not_claim_real_delivery_when_mailer_is_log(): void
    {
        Notification::fake();
        config(['mail.default' => 'log']);

        $user = User::factory()->unverified()->create();

        $this->actingAs($user);

        Volt::test('pages.auth.verify-email')
            ->call('sendVerification')
            ->assertSet('mailDeliveryStatus', 'verification-link-logged')
            ->assertHasNoErrors();

        Notification::assertSentTo($user, VerifyEmail::class);
    }

    public function test_email_can_be_verified(): void
    {
        $user = User::factory()->unverified()->create();

        Event::fake();

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        $response = $this->actingAs($user)->get($verificationUrl);

        Event::assertDispatched(Verified::class);
        $this->assertTrue($user->fresh()->hasVerifiedEmail());
        $response->assertRedirect(route('dashboard', absolute: false).'?verified=1');
    }

    public function test_email_is_not_verified_with_invalid_hash(): void
    {
        $user = User::factory()->unverified()->create();

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1('wrong-email')]
        );

        $this->actingAs($user)->get($verificationUrl);

        $this->assertFalse($user->fresh()->hasVerifiedEmail());
    }
}
