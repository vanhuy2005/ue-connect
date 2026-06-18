<?php

namespace Tests\Feature;

use App\Enums\AccountStatus;
use App\Models\User;
use Database\Seeders\Reference\AccessControlReferenceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
