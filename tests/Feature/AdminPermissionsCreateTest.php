<?php

namespace Tests\Feature;

use App\Enums\AccountStatus;
use App\Models\User;
use Database\Seeders\Reference\AccessControlReferenceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;
use Tests\TestCase;

class AdminPermissionsCreateTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(AccessControlReferenceSeeder::class);
    }

    public function test_component_renders_successfully(): void
    {
        Volt::test('pages.admin.permissions-create')
            ->assertSee('Tạo cấp quyền')
            ->assertSee('Mã quyền');
    }

    public function test_user_autocomplete_search_suggests_matching_users(): void
    {
        $match1 = User::factory()->create(['name' => 'Phạm Văn Thien', 'account_status' => AccountStatus::ACTIVE]);
        $match2 = User::factory()->create(['email' => 'thien@hcmue.edu.vn', 'account_status' => AccountStatus::ACTIVE]);
        $nonMatch = User::factory()->create(['name' => 'Nguyễn Thị Thu', 'account_status' => AccountStatus::ACTIVE]);

        Volt::test('pages.admin.permissions-create')
            ->set('user_search', 'Phạm')
            ->assertSet('showDropdown', true)
            ->assertCount('searchResults', 1)
            ->set('user_search', 'thien')
            ->assertSet('showDropdown', true)
            ->assertCount('searchResults', 2);
    }

    public function test_selecting_user_updates_selection_state(): void
    {
        $user = User::factory()->create(['name' => 'Phạm Văn Thien', 'account_status' => AccountStatus::ACTIVE]);

        Volt::test('pages.admin.permissions-create')
            ->set('user_search', 'Phạm')
            ->call('selectUser', $user->id, $user->name)
            ->assertSet('user_id', $user->id)
            ->assertSet('user_search', $user->name)
            ->assertSet('showDropdown', false)
            ->assertCount('searchResults', 0);
    }

    public function test_submit_creates_permission_grant(): void
    {
        $admin = User::factory()->create(['account_status' => AccountStatus::ACTIVE]);
        $user = User::factory()->create(['account_status' => AccountStatus::ACTIVE]);

        $this->actingAs($admin);

        Volt::test('pages.admin.permissions-create')
            ->call('selectUser', $user->id, $user->name)
            ->set('permission_key', 'manage_club')
            ->set('scope_type', 'community')
            ->set('scope_id', 5)
            ->set('reason', 'Test granting club management')
            ->call('submit')
            ->assertHasNoErrors()
            ->assertRedirect(route('admin.permissions.index'));

        $this->assertDatabaseHas('permission_grants', [
            'user_id' => $user->id,
            'permission_key' => 'manage_club',
            'scope_type' => 'community',
            'scope_id' => 5,
            'reason' => 'Test granting club management',
            'granted_by' => $admin->id,
        ]);
    }

    public function test_submit_requires_valid_data(): void
    {
        Volt::test('pages.admin.permissions-create')
            ->call('submit')
            ->assertHasErrors(['user_id' => 'required', 'permission_key' => 'required', 'reason' => 'required']);
    }
}
