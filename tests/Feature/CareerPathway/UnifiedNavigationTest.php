<?php

namespace Tests\Feature\CareerPathway;

use App\Enums\AccountStatus;
use App\Models\CareerDataQualityIssue;
use App\Models\User;
use Database\Seeders\Reference\AccessControlReferenceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UnifiedNavigationTest extends TestCase
{
    use RefreshDatabase;

    private function activeUser(): User
    {
        return User::factory()->create([
            'account_status' => AccountStatus::ACTIVE,
        ]);
    }

    public function test_global_sidebar_has_only_one_career_pathway_item(): void
    {
        $this->actingAs($this->activeUser());

        $view = $this->view('partials.app.sidebar');

        $view->assertSee('Bản đồ học tập');
        $view->assertDontSee('Vị trí việc làm');
        $view->assertDontSee('Career Positions');
        $view->assertDontSee('Senior Pathways');
        $view->assertDontSee('Community Knowledge');
        $view->assertDontSee('Career Search');

        $sidebar = file_get_contents(resource_path('views/partials/app/sidebar.blade.php'));
        $this->assertStringContainsString("request()->routeIs('app.career-pathway.*')", $sidebar);
        $this->assertStringNotContainsString("route('app.career-positions.index')", $sidebar);
        $this->assertStringNotContainsString("route('app.senior-pathways.index')", $sidebar);
    }

    public function test_career_pathway_sub_sidebar_renders_module_items(): void
    {
        $this->actingAs($this->activeUser());

        $view = $this->view('components.career-pathway.sub-sidebar');

        foreach ([
            'Tổng quan',
            'Chương trình đào tạo',
            'Môn học & tri thức',
            'Vị trí nghề nghiệp',
            'Hành trình anh/chị khóa trước',
            'Đã lưu',
        ] as $label) {
            $view->assertSee($label);
        }

        $view->assertDontSee('Tìm kiếm');
    }

    public function test_sub_sidebar_admin_items_are_hidden_for_normal_users(): void
    {
        $this->actingAs($this->activeUser());

        $view = $this->view('components.career-pathway.sub-sidebar');

        $view->assertDontSee('Quản trị dữ liệu');
        $view->assertDontSee('Import runs');
        $view->assertDontSee('Vấn đề dữ liệu');
    }

    public function test_sub_sidebar_admin_items_render_for_admin_users(): void
    {
        $this->artisan('db:seed', ['--class' => AccessControlReferenceSeeder::class]);

        $admin = $this->activeUser();
        $admin->givePermissionTo('review_verification');
        $this->actingAs($admin);

        $view = $this->view('components.career-pathway.sub-sidebar');

        $view->assertSee('Quản trị dữ liệu');
        $view->assertSee('Import runs');
        $view->assertSee('Vấn đề dữ liệu');
    }

    public function test_sub_sidebar_active_state_tracks_current_section_patterns(): void
    {
        $subSidebar = file_get_contents(resource_path('views/components/career-pathway/sub-sidebar.blade.php'));

        $this->assertStringContainsString("request()->routeIs('app.career-pathway.index')", $subSidebar);
        $this->assertStringContainsString("request()->routeIs('app.career-pathway.programs')", $subSidebar);
        $this->assertStringContainsString("request()->routeIs('app.career-pathway.courses*')", $subSidebar);
        $this->assertStringContainsString("request()->routeIs('app.career-pathway.positions.*')", $subSidebar);
        $this->assertStringContainsString("request()->routeIs('app.career-pathway.senior-pathways.*')", $subSidebar);
        $this->assertStringContainsString("request()->routeIs('app.career-pathway.saved')", $subSidebar);
        $this->assertStringNotContainsString("route('app.career-pathway.search')", $subSidebar);
    }

    public function test_career_pathway_shell_uses_white_canvas_and_sticky_sub_sidebar(): void
    {
        $shell = file_get_contents(resource_path('views/components/career-pathway/shell.blade.php'));
        $subSidebar = file_get_contents(resource_path('views/components/career-pathway/sub-sidebar.blade.php'));

        $this->assertStringContainsString('data-career-pathway-shell class="min-h-full bg-white"', $shell);
        $this->assertStringContainsString('data-career-pathway-sub-sidebar', $subSidebar);
        $this->assertStringContainsString('lg:sticky lg:top-0 lg:h-screen', $subSidebar);
    }

    public function test_career_pages_use_shared_shell(): void
    {
        foreach ([
            'career-pathway-overview',
            'career-pathway',
            'career-pathway-courses',
            'career-positions-list',
            'career-positions-show',
            'career-positions-builder',
            'senior-pathways-list',
            'senior-pathways-show',
            'senior-pathways-builder',
            'career-pathway-search',
            'career-pathway-saved',
        ] as $file) {
            $contents = file_get_contents(resource_path("views/livewire/pages/app/{$file}.blade.php"));
            $this->assertStringContainsString('<x-career-pathway.shell', $contents, "{$file} should use CareerPathwayShell.");
        }
    }

    public function test_empty_states_are_vietnamese_and_contextual(): void
    {
        $positions = file_get_contents(resource_path('views/livewire/pages/app/career-positions-list.blade.php'));
        $pathways = file_get_contents(resource_path('views/livewire/pages/app/senior-pathways-list.blade.php'));
        $search = file_get_contents(resource_path('views/livewire/pages/app/career-pathway-search.blade.php'));

        $this->assertStringContainsString('Chưa có lộ trình nghề nghiệp nào được cộng đồng xuất bản', $positions);
        $this->assertStringContainsString('Chưa có hành trình nào được chia sẻ', $pathways);
        $this->assertStringContainsString('Chưa tìm thấy nội dung phù hợp', $search);
        $this->assertStringNotContainsString('No published career positions found.', $positions);
        $this->assertStringNotContainsString('No published pathways found.', $pathways);
        $this->assertStringNotContainsString('No results found', $search);
    }

    public function test_course_detail_empty_state_has_contribution_cta(): void
    {
        $courses = file_get_contents(resource_path('views/livewire/pages/app/career-pathway-courses.blade.php'));

        $this->assertStringContainsString('Thêm chia sẻ cho môn này', $courses);
        $this->assertStringContainsString('saveContribution', $courses);
        $this->assertStringContainsString('Tìm trong môn học & tri thức', $courses);
        $this->assertStringContainsString('setFilter', $courses);
        $this->assertStringContainsString('contributionDrawerOpen', $courses);
        $this->assertStringContainsString('Nội dung tham khảo', $courses);
        $this->assertStringContainsString('selectedCohortId', $courses);
        $this->assertStringContainsString('selectedFacultyId', $courses);
        $this->assertStringContainsString('selectedMajorId', $courses);
        $this->assertStringNotContainsString("'project' => 'Project'", $courses);
        $this->assertStringNotContainsString("route('app.career-pathway.search')", $courses);
    }

    public function test_course_update_proposal_cta_is_available_from_program_course_drawer(): void
    {
        $drawer = file_get_contents(resource_path('views/components/career-pathway/course-detail.blade.php'));

        $this->assertStringContainsString('Gửi đề xuất chờ duyệt', $drawer);
        $this->assertStringContainsString('update-proposals', $drawer);
        $this->assertStringContainsString('program_course_id', $drawer);
        $this->assertStringContainsString('Đề xuất cập nhật dữ liệu chính thức', $drawer);
    }

    public function test_senior_pathway_builder_supports_timeline_wizard(): void
    {
        $builder = file_get_contents(resource_path('views/livewire/pages/app/senior-pathways-builder.blade.php'));

        $this->assertStringContainsString('Chọn chương trình đã học', $builder);
        $this->assertStringContainsString('Thêm mốc vào hành trình', $builder);
        $this->assertStringContainsString('Xuất bản hành trình', $builder);
        $this->assertStringContainsString('items()->count() === 0', $builder);
    }

    public function test_career_position_builder_supports_full_wizard_steps(): void
    {
        $builder = file_get_contents(resource_path('views/livewire/pages/app/career-positions-builder.blade.php'));

        $this->assertStringContainsString('savePositionInfo', $builder);
        $this->assertStringContainsString('addBuilderItem', $builder);
        $this->assertStringContainsString('Gắn môn học từ chương trình', $builder);
        $this->assertStringContainsString('Gắn kỹ năng cần xây', $builder);
        $this->assertStringContainsString('Thêm project, tài nguyên và advice', $builder);
        $this->assertStringContainsString('Xuất bản lộ trình', $builder);
    }

    public function test_create_buttons_navigate_to_canonical_routes(): void
    {
        $positions = file_get_contents(resource_path('views/livewire/pages/app/career-positions-list.blade.php'));
        $pathways = file_get_contents(resource_path('views/livewire/pages/app/senior-pathways-list.blade.php'));

        $this->assertStringContainsString("route('app.career-pathway.positions.create')", $positions);
        $this->assertStringContainsString("route('app.career-pathway.senior-pathways.create')", $pathways);
    }

    public function test_nested_career_pathway_routes_are_canonical(): void
    {
        $this->assertSame('/app/career-pathway', route('app.career-pathway.index', absolute: false));
        $this->assertSame('/app/career-pathway/positions', route('app.career-pathway.positions.index', absolute: false));
        $this->assertSame('/app/career-pathway/senior-pathways', route('app.career-pathway.senior-pathways.index', absolute: false));
        $this->assertSame('/app/career-pathway/search', route('app.career-pathway.search', absolute: false));
    }

    public function test_canonical_module_pages_render_inside_app_shell(): void
    {
        $user = $this->activeUser();

        foreach ([
            route('app.career-pathway.index', absolute: false),
            route('app.career-pathway.programs', absolute: false),
            route('app.career-pathway.positions.index', absolute: false),
            route('app.career-pathway.positions.create', absolute: false),
            route('app.career-pathway.senior-pathways.index', absolute: false),
            route('app.career-pathway.senior-pathways.create', absolute: false),
            route('app.career-pathway.saved', absolute: false),
        ] as $url) {
            $this->actingAs($user)
                ->get($url)
                ->assertOk()
                ->assertSee('data-career-pathway-shell', false)
                ->assertSee('Bản đồ học tập');
        }
    }

    public function test_app_search_redirects_to_courses_knowledge_section(): void
    {
        $this->actingAs($this->activeUser())
            ->get(route('app.career-pathway.search', absolute: false))
            ->assertRedirect('/app/career-pathway/courses');
    }

    public function test_data_quality_issue_keeps_admin_career_program_relation_alias(): void
    {
        $issue = CareerDataQualityIssue::factory()->create();

        $loadedIssue = CareerDataQualityIssue::with('careerProgram')->findOrFail($issue->id);

        $this->assertTrue($loadedIssue->relationLoaded('careerProgram'));
    }
}
