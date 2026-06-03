<?php

namespace Database\Seeders;

use App\Models\AcademicProgram;
use App\Models\Faculty;
use Illuminate\Database\Seeder;

class FacultyAndAcademicProgramSeeder extends Seeder
{
    /**
     * Seed HCMUE/SPS undergraduate admission reference data.
     *
     * Based on undergraduate admission reference data for HCMUE/SPS. Verify
     * annually because program availability, faculty ownership, and admission
     * naming can change by admission year.
     */
    public function run(): void
    {
        foreach ($this->faculties() as $facultyData) {
            $programs = $facultyData['programs'];
            unset($facultyData['programs']);

            $faculty = Faculty::updateOrCreate(
                ['slug' => $facultyData['slug']],
                $facultyData
            );

            foreach ($programs as $programData) {
                AcademicProgram::updateOrCreate(
                    [
                        'faculty_id' => $faculty->id,
                        'slug' => $programData['slug'],
                    ],
                    array_merge($programData, [
                        'faculty_id' => $faculty->id,
                        'degree_level' => 'undergraduate',
                        'status' => 'active',
                    ])
                );
            }
        }
    }

    /**
     * @return array<int, array{
     *     name: string,
     *     slug: string,
     *     description: string,
     *     status: string,
     *     programs: array<int, array{name: string, slug: string, description?: string}>
     * }>
     */
    private function faculties(): array
    {
        return [
            [
                'name' => 'Khoa Công nghệ Thông tin',
                'slug' => 'cntt',
                'description' => 'Đào tạo các chương trình công nghệ thông tin và sư phạm tin học.',
                'status' => 'active',
                'programs' => [
                    ['name' => 'Công nghệ thông tin', 'slug' => 'cong-nghe-thong-tin'],
                    ['name' => 'Sư phạm Tin học', 'slug' => 'su-pham-tin-hoc'],
                ],
            ],
            [
                'name' => 'Khoa Toán - Thống kê',
                'slug' => 'toan-thong-ke',
                'description' => 'Đào tạo toán học, thống kê ứng dụng và sư phạm toán.',
                'status' => 'active',
                'programs' => [
                    ['name' => 'Sư phạm Toán học', 'slug' => 'su-pham-toan-hoc'],
                    ['name' => 'Toán học', 'slug' => 'toan-hoc'],
                ],
            ],
            [
                'name' => 'Khoa Vật lý',
                'slug' => 'vat-ly',
                'description' => 'Đào tạo vật lý học và sư phạm vật lý.',
                'status' => 'active',
                'programs' => [
                    ['name' => 'Sư phạm Vật lý', 'slug' => 'su-pham-vat-ly'],
                    ['name' => 'Vật lý học', 'slug' => 'vat-ly-hoc'],
                ],
            ],
            [
                'name' => 'Khoa Hóa học',
                'slug' => 'hoa-hoc',
                'description' => 'Đào tạo hóa học và sư phạm hóa học.',
                'status' => 'active',
                'programs' => [
                    ['name' => 'Sư phạm Hóa học', 'slug' => 'su-pham-hoa-hoc'],
                    ['name' => 'Hóa học', 'slug' => 'hoa-hoc'],
                ],
            ],
            [
                'name' => 'Khoa Sinh học',
                'slug' => 'sinh-hoc',
                'description' => 'Đào tạo sinh học và sư phạm sinh học.',
                'status' => 'active',
                'programs' => [
                    ['name' => 'Sư phạm Sinh học', 'slug' => 'su-pham-sinh-hoc'],
                    ['name' => 'Sinh học', 'slug' => 'sinh-hoc'],
                ],
            ],
            [
                'name' => 'Khoa Ngữ văn',
                'slug' => 'ngu-van',
                'description' => 'Đào tạo văn học và sư phạm ngữ văn.',
                'status' => 'active',
                'programs' => [
                    ['name' => 'Sư phạm Ngữ văn', 'slug' => 'su-pham-ngu-van'],
                    ['name' => 'Văn học', 'slug' => 'van-hoc'],
                ],
            ],
            [
                'name' => 'Khoa Lịch sử',
                'slug' => 'lich-su',
                'description' => 'Đào tạo lịch sử, quốc tế học và các chương trình sư phạm liên quan.',
                'status' => 'active',
                'programs' => [
                    ['name' => 'Sư phạm Lịch sử', 'slug' => 'su-pham-lich-su'],
                    ['name' => 'Lịch sử', 'slug' => 'lich-su'],
                    ['name' => 'Quốc tế học', 'slug' => 'quoc-te-hoc'],
                    ['name' => 'Sư phạm Lịch sử - Địa lý', 'slug' => 'su-pham-lich-su-dia-ly'],
                ],
            ],
            [
                'name' => 'Khoa Địa lý',
                'slug' => 'dia-ly',
                'description' => 'Đào tạo địa lý học, Việt Nam học và sư phạm địa lý.',
                'status' => 'active',
                'programs' => [
                    ['name' => 'Sư phạm Địa lý', 'slug' => 'su-pham-dia-ly'],
                    ['name' => 'Địa lý học', 'slug' => 'dia-ly-hoc'],
                    ['name' => 'Việt Nam học', 'slug' => 'viet-nam-hoc'],
                ],
            ],
            [
                'name' => 'Khoa Tiếng Anh',
                'slug' => 'tieng-anh',
                'description' => 'Đào tạo ngôn ngữ Anh và sư phạm tiếng Anh.',
                'status' => 'active',
                'programs' => [
                    ['name' => 'Sư phạm Tiếng Anh', 'slug' => 'su-pham-tieng-anh'],
                    ['name' => 'Ngôn ngữ Anh', 'slug' => 'ngon-ngu-anh'],
                ],
            ],
            [
                'name' => 'Khoa Tiếng Trung Quốc',
                'slug' => 'tieng-trung-quoc',
                'description' => 'Đào tạo ngôn ngữ Trung Quốc và sư phạm tiếng Trung Quốc.',
                'status' => 'active',
                'programs' => [
                    ['name' => 'Sư phạm Tiếng Trung Quốc', 'slug' => 'su-pham-tieng-trung-quoc'],
                    ['name' => 'Ngôn ngữ Trung Quốc', 'slug' => 'ngon-ngu-trung-quoc'],
                ],
            ],
            [
                'name' => 'Khoa Tiếng Pháp',
                'slug' => 'tieng-phap',
                'description' => 'Đào tạo ngôn ngữ Pháp và sư phạm tiếng Pháp.',
                'status' => 'active',
                'programs' => [
                    ['name' => 'Sư phạm Tiếng Pháp', 'slug' => 'su-pham-tieng-phap'],
                    ['name' => 'Ngôn ngữ Pháp', 'slug' => 'ngon-ngu-phap'],
                ],
            ],
            [
                'name' => 'Khoa Tiếng Nga',
                'slug' => 'tieng-nga',
                'description' => 'Đào tạo ngôn ngữ Nga và sư phạm tiếng Nga.',
                'status' => 'active',
                'programs' => [
                    ['name' => 'Sư phạm Tiếng Nga', 'slug' => 'su-pham-tieng-nga'],
                    ['name' => 'Ngôn ngữ Nga', 'slug' => 'ngon-ngu-nga'],
                ],
            ],
            [
                'name' => 'Khoa Tiếng Nhật',
                'slug' => 'tieng-nhat',
                'description' => 'Đào tạo ngôn ngữ Nhật và sư phạm tiếng Nhật.',
                'status' => 'active',
                'programs' => [
                    ['name' => 'Sư phạm Tiếng Nhật', 'slug' => 'su-pham-tieng-nhat'],
                    ['name' => 'Ngôn ngữ Nhật', 'slug' => 'ngon-ngu-nhat'],
                ],
            ],
            [
                'name' => 'Khoa Tiếng Hàn Quốc',
                'slug' => 'tieng-han-quoc',
                'description' => 'Đào tạo ngôn ngữ Hàn Quốc.',
                'status' => 'active',
                'programs' => [
                    ['name' => 'Ngôn ngữ Hàn Quốc', 'slug' => 'ngon-ngu-han-quoc'],
                ],
            ],
            [
                'name' => 'Khoa Giáo dục Tiểu học',
                'slug' => 'giao-duc-tieu-hoc',
                'description' => 'Đào tạo giáo viên giáo dục tiểu học.',
                'status' => 'active',
                'programs' => [
                    ['name' => 'Giáo dục Tiểu học', 'slug' => 'giao-duc-tieu-hoc'],
                ],
            ],
            [
                'name' => 'Khoa Giáo dục Mầm non',
                'slug' => 'giao-duc-mam-non',
                'description' => 'Đào tạo giáo viên giáo dục mầm non.',
                'status' => 'active',
                'programs' => [
                    ['name' => 'Giáo dục Mầm non', 'slug' => 'giao-duc-mam-non'],
                ],
            ],
            [
                'name' => 'Khoa Giáo dục Đặc biệt',
                'slug' => 'giao-duc-dac-biet',
                'description' => 'Đào tạo giáo dục đặc biệt.',
                'status' => 'active',
                'programs' => [
                    ['name' => 'Giáo dục Đặc biệt', 'slug' => 'giao-duc-dac-biet'],
                ],
            ],
            [
                'name' => 'Khoa Giáo dục Chính trị',
                'slug' => 'giao-duc-chinh-tri',
                'description' => 'Đào tạo giáo dục chính trị và giáo dục công dân.',
                'status' => 'active',
                'programs' => [
                    ['name' => 'Giáo dục Chính trị', 'slug' => 'giao-duc-chinh-tri'],
                    ['name' => 'Giáo dục Công dân', 'slug' => 'giao-duc-cong-dan'],
                ],
            ],
            [
                'name' => 'Khoa Tâm lý học',
                'slug' => 'tam-ly-hoc',
                'description' => 'Đào tạo tâm lý học và tâm lý học giáo dục.',
                'status' => 'active',
                'programs' => [
                    ['name' => 'Tâm lý học', 'slug' => 'tam-ly-hoc'],
                    ['name' => 'Tâm lý học giáo dục', 'slug' => 'tam-ly-hoc-giao-duc'],
                ],
            ],
            [
                'name' => 'Khoa Khoa học Giáo dục',
                'slug' => 'khoa-hoc-giao-duc',
                'description' => 'Đào tạo khoa học giáo dục, quản lý giáo dục và công tác xã hội.',
                'status' => 'active',
                'programs' => [
                    ['name' => 'Quản lý giáo dục', 'slug' => 'quan-ly-giao-duc'],
                    ['name' => 'Giáo dục học', 'slug' => 'giao-duc-hoc'],
                    ['name' => 'Công tác xã hội', 'slug' => 'cong-tac-xa-hoi'],
                    ['name' => 'Công nghệ giáo dục', 'slug' => 'cong-nghe-giao-duc'],
                ],
            ],
            [
                'name' => 'Khoa Giáo dục Thể chất',
                'slug' => 'giao-duc-the-chat',
                'description' => 'Đào tạo giáo dục thể chất.',
                'status' => 'active',
                'programs' => [
                    ['name' => 'Giáo dục Thể chất', 'slug' => 'giao-duc-the-chat'],
                ],
            ],
            [
                'name' => 'Khoa Giáo dục Quốc phòng',
                'slug' => 'giao-duc-quoc-phong',
                'description' => 'Đào tạo giáo dục quốc phòng và an ninh.',
                'status' => 'active',
                'programs' => [
                    ['name' => 'Giáo dục Quốc phòng và An ninh', 'slug' => 'giao-duc-quoc-phong-va-an-ninh'],
                ],
            ],
            [
                'name' => 'Khoa Nghệ thuật',
                'slug' => 'nghe-thuat',
                'description' => 'Đào tạo các ngành sư phạm nghệ thuật.',
                'status' => 'active',
                'programs' => [
                    ['name' => 'Sư phạm Âm nhạc', 'slug' => 'su-pham-am-nhac'],
                    ['name' => 'Sư phạm Mỹ thuật', 'slug' => 'su-pham-my-thuat'],
                ],
            ],
            [
                'name' => 'Khoa Khoa học Tự nhiên',
                'slug' => 'khoa-hoc-tu-nhien',
                'description' => 'Đào tạo chương trình sư phạm khoa học tự nhiên liên ngành.',
                'status' => 'active',
                'programs' => [
                    ['name' => 'Sư phạm Khoa học tự nhiên', 'slug' => 'su-pham-khoa-hoc-tu-nhien'],
                ],
            ],
        ];
    }
}
