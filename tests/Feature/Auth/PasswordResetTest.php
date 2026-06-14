<?php

namespace Tests\Feature\Auth;

use App\Mail\Auth\ResetPasswordOtpMail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Livewire\Volt\Volt;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_reset_password_link_screen_can_be_rendered(): void
    {
        $response = $this->get('/forgot-password');

        $response
            ->assertSeeVolt('pages.auth.forgot-password')
            ->assertStatus(200);
    }

    public function test_reset_password_otp_can_be_requested(): void
    {
        Mail::fake();

        $user = User::factory()->create();

        $component = Volt::test('pages.auth.forgot-password')
            ->set('email', $user->email)
            ->call('sendPasswordResetLink');

        $component->assertRedirect(route('password.reset', ['email' => $user->email]));

        Mail::assertSent(ResetPasswordOtpMail::class, function ($mail) use ($user) {
            return $mail->hasTo($user->email);
        });

        $this->assertTrue(Cache::has('password_reset_otp_' . $user->email));
    }

    public function test_reset_password_screen_can_be_rendered(): void
    {
        $response = $this->get('/reset-password?email=test@example.com');

        $response
            ->assertSeeVolt('pages.auth.reset-password')
            ->assertStatus(200);
    }

    public function test_password_can_be_reset_with_valid_otp(): void
    {
        $user = User::factory()->create();
        $otp = '123456';
        Cache::put('password_reset_otp_' . $user->email, $otp, 15);

        $component = Volt::test('pages.auth.reset-password')
            ->set('email', $user->email)
            ->set('otp', $otp)
            ->call('verifyOtp')
            ->assertSet('otpVerified', true)
            ->set('password', 'password')
            ->set('password_confirmation', 'password');

        $component->call('resetPassword');

        $component
            ->assertRedirect('/login')
            ->assertHasNoErrors();
            
        $this->assertFalse(Cache::has('password_reset_otp_' . $user->email));
    }
}
