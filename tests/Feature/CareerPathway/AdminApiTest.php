<?php

namespace Tests\Feature\CareerPathway;

use App\Enums\ProgramStatus;
use App\Models\CareerDataQualityIssue;
use App\Models\CareerImportRun;
use App\Models\CareerProgram;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminApiTest extends TestCase
{
    use RefreshDatabase;

    private function getAdminUser()
    {
        $admin = User::factory()->create(['account_status' => 'active', 'email_verified_at' => now()]);

        if (method_exists($admin, 'assignRole')) {
            // Need to create the role first in test db if it uses spatie
            Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
            $admin->assignRole('admin');
        }

        return $admin;
    }

    private function getNormalUser()
    {
        return User::factory()->create(['account_status' => 'active', 'email_verified_at' => now()]);
    }

    public function test_non_admin_cannot_access_admin_endpoints()
    {
        $user = $this->getNormalUser();
        $response = $this->actingAs($user)->getJson(route('admin.career-pathway.import-runs.index'));

        $response->assertForbidden();
    }

    public function test_admin_can_update_program_status()
    {
        $admin = $this->getAdminUser();
        $program = CareerProgram::factory()->create(['status' => ProgramStatus::EMPTY_EXTRACTION->value]);

        $response = $this->actingAs($admin)->patchJson(route('admin.career-pathway.programs.update-status', $program), [
            'status' => ProgramStatus::EXCLUDED_NON_PROGRAM_DOCUMENT->value,
        ]);

        $response->assertOk()
            ->assertJsonPath('message', 'Program status updated successfully.');

        $this->assertEquals(ProgramStatus::EXCLUDED_NON_PROGRAM_DOCUMENT, $program->fresh()->status);
    }

    public function test_changing_non_public_program_to_ready_requires_reason()
    {
        $admin = $this->getAdminUser();
        $program = CareerProgram::factory()->create(['status' => ProgramStatus::EMPTY_EXTRACTION->value]);

        $response = $this->actingAs($admin)->patchJson(route('admin.career-pathway.programs.update-status', $program), [
            'status' => ProgramStatus::READY->value,
        ]);

        $response->assertJsonValidationErrors(['reason']);

        // Now with reason
        $response = $this->actingAs($admin)->patchJson(route('admin.career-pathway.programs.update-status', $program), [
            'status' => ProgramStatus::READY->value,
            'reason' => 'Manually verified.',
        ]);

        $response->assertOk();
    }

    public function test_status_update_invalidates_worktree_cache()
    {
        $admin = $this->getAdminUser();
        $program = CareerProgram::factory()->create(['status' => ProgramStatus::READY->value]);

        Cache::shouldReceive('tags')->with(['career_program:'.$program->id])->andReturnSelf();
        Cache::shouldReceive('flush')->once();

        // Setup default config mock for tags check if needed or just let it run if redis.
        config(['cache.default' => 'redis']);

        $this->actingAs($admin)->patchJson(route('admin.career-pathway.programs.update-status', $program), [
            'status' => ProgramStatus::EMPTY_EXTRACTION->value,
        ]);
    }

    public function test_import_run_request_validates_unsafe_path()
    {
        $admin = $this->getAdminUser();

        $response = $this->actingAs($admin)->postJson(route('admin.career-pathway.import-runs.store'), [
            'path' => '../../etc/passwd',
        ]);

        $response->assertJsonValidationErrors(['path']);
    }

    public function test_admin_data_quality_issues_render_as_html_for_browser_requests()
    {
        $admin = $this->getAdminUser();
        CareerDataQualityIssue::factory()->create([
            'issue_type' => 'missing_course_descriptions',
            'severity' => 'p1',
            'message' => 'Extracted issue: missing_course_descriptions',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.career-pathway.data-quality-issues.index'));

        $response
            ->assertOk()
            ->assertSee('Dữ liệu cần xử lý')
            ->assertSee('Danh sách vấn đề')
            ->assertSee('Thiếu mô tả môn học');

        $this->assertFalse(str_starts_with($response->getContent(), '{"data"'));
    }

    public function test_admin_import_runs_render_as_html_for_browser_requests()
    {
        $admin = $this->getAdminUser();
        CareerImportRun::factory()->create([
            'status' => 'completed',
            'log' => 'Imported HCMUE curriculum data.',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.career-pathway.import-runs.index'));

        $response
            ->assertOk()
            ->assertSee('Import runs')
            ->assertSee('Lịch sử import')
            ->assertSee('Tạo import run');

        $this->assertFalse(str_starts_with($response->getContent(), '{"data"'));
    }

    public function test_admin_data_quality_issues_still_support_json_api()
    {
        $admin = $this->getAdminUser();
        CareerDataQualityIssue::factory()->create();

        $response = $this->actingAs($admin)->getJson(route('admin.career-pathway.data-quality-issues.index'));

        $response
            ->assertOk()
            ->assertJsonStructure(['data' => ['data']]);
    }
}
