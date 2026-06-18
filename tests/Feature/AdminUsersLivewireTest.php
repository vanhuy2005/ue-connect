<?php

namespace Tests\Feature;

use App\Enums\AccountStatus;
use App\Enums\IdentityType;
use App\Mail\Auth\ResetPasswordOtpMail;
use App\Models\User;
use Database\Seeders\Reference\AccessControlReferenceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Livewire\Volt\Volt;
use Tests\TestCase;

class AdminUsersLivewireTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(AccessControlReferenceSeeder::class);
    }

    public function test_admin_users_route_renders_successfully(): void
    {
        $admin = $this->adminUser();

        $response = $this->actingAs($admin)->get(route('admin.users.index'));

        $response->assertStatus(200);
        $response->assertSee('Quản lý tài khoản người dùng');
    }

    public function test_admin_can_render_create_and_edit_user_pages(): void
    {
        $admin = $this->adminUser();
        $target = User::factory()->create([
            'name' => 'Editable Student',
            'account_status' => AccountStatus::ACTIVE,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.users.create'))
            ->assertOk()
            ->assertSee('Tạo tài khoản người dùng');

        $this->actingAs($admin)
            ->get(route('admin.users.edit', $target))
            ->assertOk()
            ->assertSee('Chỉnh sửa tài khoản');
    }

    public function test_component_renders_users_with_enum_account_statuses(): void
    {
        $admin = $this->adminUser();
        $this->createStatusUsers();

        $this->actingAs($admin);

        Volt::test('pages.admin.users-list')
            ->assertSee('Status Active Student')
            ->assertSee('Hoạt động')
            ->assertSee('Status Registered Student')
            ->assertSee('Đăng ký')
            ->assertSee('Status Pending Verification Student')
            ->assertSee('Chờ xác thực')
            ->assertSee('Status Profile Incomplete Student')
            ->assertSee('Hồ sơ chưa hoàn tất')
            ->assertSee('Status Restricted Student')
            ->assertSee('Bị hạn chế')
            ->assertSee('Status Suspended Student')
            ->assertSee('Bị tạm khóa')
            ->assertSee('Status Banned Student')
            ->assertSee('Bị cấm')
            ->assertSee('Status Deleted Student')
            ->assertSee('Đã xóa');
    }

    public function test_livewire_search_and_role_updates_do_not_fail(): void
    {
        $admin = $this->adminUser();
        $student = User::factory()->create([
            'name' => 'Searchable Student',
            'email' => 'searchable.student@example.test',
            'account_status' => AccountStatus::ACTIVE,
        ]);
        $student->assignRole('student');

        $teacher = User::factory()->create([
            'name' => 'Hidden Teacher',
            'email' => 'hidden.teacher@example.test',
            'account_status' => AccountStatus::ACTIVE,
        ]);
        $teacher->assignRole('teacher');

        $this->actingAs($admin);

        Volt::test('pages.admin.users-list')
            ->set('search', 'Searchable')
            ->assertSet('search', 'Searchable')
            ->assertSee('Searchable Student')
            ->assertDontSee('Hidden Teacher')
            ->set('search', '')
            ->set('role', 'student')
            ->assertSet('role', 'student')
            ->assertSee('Searchable Student')
            ->assertDontSee('Hidden Teacher');
    }

    public function test_livewire_account_status_updates_filter_and_render_each_status(): void
    {
        $admin = $this->adminUser();
        $usersByStatus = $this->createStatusUsers();

        $this->actingAs($admin);

        $component = Volt::test('pages.admin.users-list');

        foreach ($usersByStatus as $statusValue => $user) {
            $component
                ->set('account_status', $statusValue)
                ->assertSet('account_status', $statusValue)
                ->assertSee($user->name);
        }

        $component
            ->set('account_status', AccountStatus::ACTIVE->value)
            ->assertSee('Status Active Student')
            ->assertDontSee('Status Banned Student');
    }

    public function test_admin_can_create_user_and_send_invite_otp(): void
    {
        Mail::fake();

        $admin = $this->adminUser();
        $this->actingAs($admin);

        Volt::test('pages.admin.users-form')
            ->set('name', 'Created Student')
            ->set('email', 'created.student@example.test')
            ->set('intended_identity_type', IdentityType::CURRENT_STUDENT->value)
            ->set('account_status', AccountStatus::REGISTERED->value)
            ->set('roles', ['student'])
            ->set('send_invite', true)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('users', [
            'email' => 'created.student@example.test',
            'name' => 'Created Student',
            'account_status' => AccountStatus::REGISTERED->value,
            'intended_identity_type' => IdentityType::CURRENT_STUDENT->value,
        ]);

        $createdUser = User::where('email', 'created.student@example.test')->firstOrFail();

        $this->assertTrue($createdUser->hasRole('student'));
        Mail::assertSent(ResetPasswordOtpMail::class);
        $this->assertDatabaseHas('audit_logs', [
            'action_key' => 'admin.user.create',
            'target_type' => 'user',
            'target_id' => $createdUser->id,
        ]);
    }

    public function test_admin_can_update_user_identity_status_and_roles(): void
    {
        $admin = $this->adminUser();
        $target = User::factory()->create([
            'name' => 'Old Name',
            'email' => 'old-name@example.test',
            'account_status' => AccountStatus::REGISTERED,
        ]);
        $target->assignRole('student');

        $this->actingAs($admin);

        Volt::test('pages.admin.users-form', ['user' => $target])
            ->set('name', 'Updated Name')
            ->set('email', 'updated-name@example.test')
            ->set('intended_identity_type', IdentityType::ALUMNI->value)
            ->set('account_status', AccountStatus::ACTIVE->value)
            ->set('account_status_reason', 'Verified by admin')
            ->set('roles', ['alumni'])
            ->call('save')
            ->assertHasNoErrors();

        $target->refresh();

        $this->assertSame('Updated Name', $target->name);
        $this->assertSame('updated-name@example.test', $target->email);
        $this->assertSame(IdentityType::ALUMNI, $target->intended_identity_type);
        $this->assertSame(AccountStatus::ACTIVE, $target->account_status);
        $this->assertTrue($target->hasRole('alumni'));
        $this->assertFalse($target->hasRole('student'));
        $this->assertDatabaseHas('audit_logs', [
            'action_key' => 'admin.user.update',
            'target_type' => 'user',
            'target_id' => $target->id,
        ]);
    }

    public function test_admin_can_soft_delete_and_restore_user_from_list(): void
    {
        $admin = $this->adminUser();
        $target = User::factory()->create(['account_status' => AccountStatus::ACTIVE]);

        $this->actingAs($admin);

        Volt::test('pages.admin.users-list')
            ->call('deleteUser', $target->id)
            ->assertHasNoErrors();

        $this->assertSoftDeleted('users', ['id' => $target->id]);
        $this->assertDatabaseHas('audit_logs', [
            'action_key' => 'admin.user.delete',
            'target_type' => 'user',
            'target_id' => $target->id,
        ]);

        Volt::test('pages.admin.users-list')
            ->set('trashed', 'deleted')
            ->call('restoreUser', $target->id)
            ->assertHasNoErrors();

        $this->assertFalse(User::withTrashed()->findOrFail($target->id)->trashed());
        $this->assertDatabaseHas('audit_logs', [
            'action_key' => 'admin.user.restore',
            'target_type' => 'user',
            'target_id' => $target->id,
        ]);
    }

    public function test_admin_cannot_modify_or_delete_own_account(): void
    {
        $admin = $this->adminUser();

        $this->actingAs($admin);

        Volt::test('pages.admin.users-form', ['user' => $admin])
            ->set('name', 'Self Modified')
            ->call('save')
            ->assertHasErrors(['user']);

        Volt::test('pages.admin.users-list')
            ->call('deleteUser', $admin->id)
            ->assertHasErrors(['bulk_action']);

        $this->assertNotSoftDeleted('users', ['id' => $admin->id]);
    }

    public function test_non_admin_user_cannot_access_admin_user_crud(): void
    {
        $user = User::factory()->create(['account_status' => AccountStatus::ACTIVE]);
        $user->assignRole('student');

        $this->actingAs($user)
            ->get(route('admin.users.create'))
            ->assertForbidden();

        $this->actingAs($user)
            ->get(route('admin.users.edit', $user))
            ->assertForbidden();
    }

    private function adminUser(): User
    {
        $admin = User::factory()->create([
            'name' => 'Admin User',
            'account_status' => AccountStatus::ACTIVE,
        ]);

        $admin->assignRole('admin');

        return $admin;
    }

    /**
     * @return array<string, User>
     */
    private function createStatusUsers(): array
    {
        $definitions = [
            AccountStatus::ACTIVE->value => 'Status Active Student',
            AccountStatus::REGISTERED->value => 'Status Registered Student',
            AccountStatus::PENDING_VERIFICATION->value => 'Status Pending Verification Student',
            AccountStatus::PROFILE_INCOMPLETE->value => 'Status Profile Incomplete Student',
            AccountStatus::RESTRICTED->value => 'Status Restricted Student',
            AccountStatus::SUSPENDED->value => 'Status Suspended Student',
            AccountStatus::BANNED->value => 'Status Banned Student',
            AccountStatus::DELETED->value => 'Status Deleted Student',
        ];

        $users = [];

        foreach ($definitions as $statusValue => $name) {
            $user = User::factory()->create([
                'name' => $name,
                'email' => str_replace('_', '-', $statusValue).'@example.test',
                'account_status' => $statusValue,
            ]);

            $user->assignRole('student');
            $users[$statusValue] = $user;
        }

        return $users;
    }
}
