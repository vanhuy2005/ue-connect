<?php

namespace Database\Seeders;

use App\Models\AcademicProgram;
use App\Models\Faculty;
use Illuminate\Database\Seeder;

class FacultyAndAcademicProgramSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faculties = [
            [
                'name' => 'Khoa Công nghệ Thông tin',
                'slug' => 'cntt',
                'description' => 'Khoa đào tạo cử nhân sư phạm và cử nhân kỹ thuật ngành Công nghệ thông tin.',
                'status' => 'active',
                'majors' => [
                    [
                        'name' => 'Công nghệ Thông tin',
                        'slug' => 'cong-nghe-thong-tin',
                        'degree_level' => 'undergraduate',
                        'status' => 'active',
                    ],
                    [
                        'name' => 'Sư phạm Tin học',
                        'slug' => 'su-pham-tin-hoc',
                        'degree_level' => 'undergraduate',
                        'status' => 'active',
                    ],
                ],
            ],
            [
                'name' => 'Khoa Tiếng Anh',
                'slug' => 'tieng-anh',
                'description' => 'Khoa tiếng Anh chuyên đào tạo giáo viên Tiếng Anh và cử nhân Ngôn ngữ Anh.',
                'status' => 'active',
                'majors' => [
                    [
                        'name' => 'Ngôn ngữ Anh',
                        'slug' => 'ngon-ngu-anh',
                        'degree_level' => 'undergraduate',
                        'status' => 'active',
                    ],
                    [
                        'name' => 'Sư phạm Tiếng Anh',
                        'slug' => 'su-pham-tieng-anh',
                        'degree_level' => 'undergraduate',
                        'status' => 'active',
                    ],
                ],
            ],
            [
                'name' => 'Khoa Toán - Thống kê',
                'slug' => 'toan-thong-ke',
                'description' => 'Đào tạo cử nhân Sư phạm Toán học và cử nhân Toán học.',
                'status' => 'active',
                'majors' => [
                    [
                        'name' => 'Sư phạm Toán học',
                        'slug' => 'su-pham-toan-hoc',
                        'degree_level' => 'undergraduate',
                        'status' => 'active',
                    ],
                    [
                        'name' => 'Toán học',
                        'slug' => 'toan-hoc',
                        'degree_level' => 'undergraduate',
                        'status' => 'active',
                    ],
                ],
            ],
            [
                'name' => 'Khoa Giáo dục Tiểu học',
                'slug' => 'giao-duc-tieu-hoc',
                'description' => 'Đào tạo giáo viên dạy tiểu học.',
                'status' => 'active',
                'majors' => [
                    [
                        'name' => 'Giáo dục Tiểu học',
                        'slug' => 'giao-duc-tieu-hoc',
                        'degree_level' => 'undergraduate',
                        'status' => 'active',
                    ],
                ],
            ],
            [
                'name' => 'Khoa Ngữ văn',
                'slug' => 'ngu-van',
                'description' => 'Đào tạo cử nhân sư phạm và cử nhân khoa học chuyên ngành Ngữ văn.',
                'status' => 'active',
                'majors' => [
                    [
                        'name' => 'Văn học',
                        'slug' => 'van-hoc',
                        'degree_level' => 'undergraduate',
                        'status' => 'active',
                    ],
                    [
                        'name' => 'Sư phạm Ngữ văn',
                        'slug' => 'su-pham-ngu-van',
                        'degree_level' => 'undergraduate',
                        'status' => 'active',
                    ],
                ],
            ],
        ];

        foreach ($faculties as $facultyData) {
            $majors = $facultyData['majors'];
            unset($facultyData['majors']);

            $faculty = Faculty::updateOrCreate(
                ['slug' => $facultyData['slug']],
                $facultyData
            );

            foreach ($majors as $majorData) {
                $majorData['faculty_id'] = $faculty->id;
                AcademicProgram::updateOrCreate(
                    [
                        'faculty_id' => $faculty->id,
                        'slug' => $majorData['slug'],
                    ],
                    $majorData
                );
            }
        }
    }
}
