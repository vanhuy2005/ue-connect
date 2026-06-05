<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response
            ->assertOk()
            ->assertSeeVolt('pages.auth.register');
    }

    public function test_new_users_can_register(): void
    {
        $component = Volt::test('pages.auth.register')
            ->set('name', 'Test User')
            ->set('email', 'test@student.hcmue.edu.vn')
            ->set('password', 'password')
            ->set('password_confirmation', 'password');

        $component->call('register');

        $component->assertRedirect(route('verification.notice', absolute: false));

        $this->assertAuthenticated();
    }

    public function test_student_registration_rejects_personal_email(): void
    {
        $component = Volt::test('pages.auth.register')
            ->set('identity_type', 'current_student')
            ->set('name', 'Test Student')
            ->set('email', 'student@gmail.com')
            ->set('password', 'password')
            ->set('password_confirmation', 'password');

        $component->call('register');

        $component->assertHasErrors(['email']);
        $this->assertGuest();
    }

    public function test_teacher_registration_accepts_teacher_domain(): void
    {
        $component = Volt::test('pages.auth.register')
            ->set('identity_type', 'teacher_advisor')
            ->set('name', 'Test Staff')
            ->set('email', 'teacher@teacher.hcmue.edu.vn')
            ->set('password', 'password')
            ->set('password_confirmation', 'password');

        $component->call('register');

        $component->assertRedirect(route('verification.notice', absolute: false));
        $this->assertAuthenticated();
    }

    public function test_teacher_registration_rejects_root_hcmue_domain(): void
    {
        $component = Volt::test('pages.auth.register')
            ->set('identity_type', 'teacher_advisor')
            ->set('name', 'Test Staff')
            ->set('email', 'teacher@hcmue.edu.vn')
            ->set('password', 'password')
            ->set('password_confirmation', 'password');

        $component->call('register');

        $component->assertHasErrors(['email']);
        $this->assertGuest();
    }

    public function test_alumni_registration_accepts_personal_email(): void
    {
        $component = Volt::test('pages.auth.register')
            ->set('identity_type', 'alumni')
            ->set('name', 'Test Alumni')
            ->set('email', 'alumni@gmail.com')
            ->set('password', 'password')
            ->set('password_confirmation', 'password');

        $component->call('register');

        $component->assertRedirect(route('verification.notice', absolute: false));
        $this->assertAuthenticated();
    }

    public function test_external_mentor_is_not_a_public_registration_role(): void
    {
        $component = Volt::test('pages.auth.register')
            ->set('identity_type', 'external_mentor')
            ->set('name', 'Test Mentor')
            ->set('email', 'mentor@gmail.com')
            ->set('password', 'password')
            ->set('password_confirmation', 'password');

        $component->call('register');

        $component->assertHasErrors(['identity_type']);
        $this->assertGuest();
    }
}
