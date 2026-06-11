<?php

namespace Tests\Feature;

use App\Models\AdmissionCohort;
use App\Models\Faculty;
use App\Models\Major;
use Database\Seeders\Reference\AcademicStructureSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AcademicStructureSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_academic_structure_seeder_scans_folders_and_populates_tables(): void
    {
        // Execute the seeder
        $this->seed(AcademicStructureSeeder::class);

        // Verify that cohorts are created
        $this->assertDatabaseHas('admission_cohorts', [
            'year' => 2025,
            'cohort_name' => '2025 - Khóa 51',
            'normalized_name' => 'khoa 51',
        ]);

        // Verify that faculties are created and standardized
        $this->assertDatabaseHas('faculties', [
            'name' => 'Khoa Công nghệ thông tin',
            'code' => 'CNTT',
            'slug' => 'cntt',
            'normalized_name' => 'khoa cong nghe thong tin',
            'status' => 'active',
        ]);

        $faculty = Faculty::where('slug', 'cntt')->firstOrFail();

        // Verify that majors are created
        $this->assertDatabaseHas('majors', [
            'faculty_id' => $faculty->id,
            'name' => 'Sư phạm tin',
            'code' => 'SU_PHAM_TIN_HOC',
            'normalized_name' => 'su pham tin',
            'degree_level' => 'undergraduate',
        ]);

        $major = Major::where('code', 'SU_PHAM_TIN_HOC')->firstOrFail();

        // Verify that academic programs (compatibility table) are created
        $this->assertDatabaseHas('academic_programs', [
            'faculty_id' => $faculty->id,
            'name' => 'Sư phạm tin',
            'slug' => 'su-pham-tin-hoc',
            'degree_level' => 'undergraduate',
            'status' => 'active',
        ]);

        // Verify that training programs map them together
        $cohort = AdmissionCohort::where('year', 2025)->firstOrFail();

        $this->assertDatabaseHas('training_programs', [
            'cohort_id' => $cohort->id,
            'faculty_id' => $faculty->id,
            'major_id' => $major->id,
            'title' => 'Sư phạm tin - Khóa 51',
            'effective_from' => 2025,
            'effective_to' => 2029,
            'status' => 'published',
        ]);
    }
}
