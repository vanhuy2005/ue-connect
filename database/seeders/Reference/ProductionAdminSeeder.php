<?php

namespace Database\Seeders\Reference;

use App\Enums\AccountStatus;
use App\Enums\IdentityType;
use App\Models\AdvisorProfile;
use App\Models\Faculty;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\PermissionRegistrar;

class ProductionAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        DB::transaction(function () {
            // Disable foreign key constraints for clean deletion
            Schema::disableForeignKeyConstraints();

            $excludeTables = [
                'migrations',
                'faculties',
                'academic_programs',
                'roles',
                'permissions',
                'role_has_permissions',
            ];

            // Get all tables in the database dynamically
            $tables = DB::select("SELECT table_name FROM information_schema.tables WHERE table_type = 'BASE TABLE'");

            foreach ($tables as $table) {
                $tableArray = array_change_key_case((array) $table, CASE_LOWER);
                $tableName = $tableArray['table_name'] ?? null;
                if ($tableName && ! in_array(strtolower($tableName), $excludeTables, true)) {
                    DB::table($tableName)->delete();
                }
            }

            Schema::enableForeignKeyConstraints();

            // 1. Ensure Roles & Permissions reference data are populated
            $this->call(AccessControlReferenceSeeder::class);

            // 2. Create the single Super Admin user
            $user = User::create([
                'name' => 'UEConnect Admin',
                'email' => 'admin@teacher.hcmue.edu.vn',
                'password' => Hash::make('Quanghuy@20102005'),
                'account_status' => AccountStatus::ACTIVE,
                'last_login_at' => now(),
                'intended_identity_type' => IdentityType::TEACHER_ADVISOR,
                'email_verified_at' => now(),
            ]);

            // Assign Spatie admin role
            $user->assignRole('admin');

            // 3. Create Profile
            $profile = Profile::create([
                'user_id' => $user->id,
                'display_name' => $user->name,
                'bio' => 'Tài khoản quản trị viên tối cao của hệ thống UEConnect.',
                'role_type' => 'teacher',
                'profile_status' => 'complete',
                'visibility' => 'public',
                'discoverable' => true,
                'profile_completed_at' => now(),
            ]);

            // 4. Create Advisor Profile (since role_type is teacher)
            $faculty = Faculty::first();
            AdvisorProfile::create([
                'profile_id' => $profile->id,
                'faculty_id' => $faculty ? $faculty->id : null,
                'department' => 'Ban Giám Hiệu / Phòng Công Tác Học Sinh Sinh Viên',
                'title' => 'Quản trị viên hệ thống',
                'office_location' => 'Trường Đại học Sư phạm TP.HCM',
                'advising_areas' => 'Quản trị hệ thống, duyệt hồ sơ định danh',
            ]);
        });

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
