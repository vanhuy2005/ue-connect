<?php

namespace Database\Seeders\Reference;

use App\Models\AcademicProgram;
use App\Models\AdmissionCohort;
use App\Models\Faculty;
use App\Models\Major;
use App\Models\TrainingProgram;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AcademicStructureSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Seed legacy HCMUE reference data to keep all UAT scenarios and tests passing
        $this->seedLegacyData();

        // 2. Seed dynamic folder structure from database/AI/Chuongtrinhdaotao
        $this->seedDynamicFolders();
    }

    private function seedLegacyData(): void
    {
        foreach ($this->legacyFaculties() as $facultyData) {
            $programs = $facultyData['programs'];
            unset($facultyData['programs']);

            $faculty = Faculty::updateOrCreate(
                ['slug' => $facultyData['slug']],
                [
                    'name' => $facultyData['name'],
                    'code' => $this->getFacultyCode($facultyData['name']),
                    'normalized_name' => $this->normalizeText($facultyData['name']),
                    'description' => $facultyData['description'] ?? null,
                    'status' => $facultyData['status'] ?? 'active',
                ]
            );

            foreach ($programs as $programData) {
                AcademicProgram::updateOrCreate(
                    [
                        'faculty_id' => $faculty->id,
                        'slug' => $programData['slug'],
                    ],
                    [
                        'name' => $programData['name'],
                        'degree_level' => 'undergraduate',
                        'status' => 'active',
                        'description' => $programData['description'] ?? null,
                    ]
                );
            }
        }
    }

    private function seedDynamicFolders(): void
    {
        $dir = base_path('database/AI/Chuongtrinhdaotao');

        if (! is_dir($dir)) {
            $this->command->warn("Dynamic training program directory not found: {$dir}");

            return;
        }

        $cohortFolders = glob($dir.'/*', GLOB_ONLYDIR);
        if (empty($cohortFolders)) {
            $this->command->warn("No cohort directories found in: {$dir}");

            return;
        }

        $this->command->info('Scanning '.count($cohortFolders).' cohort folders...');

        foreach ($cohortFolders as $cohortPath) {
            $cohortFolderName = basename($cohortPath);

            // Parse cohort year and name from folder "YYYY - Khóa XX"
            $cohortYear = null;
            $cohortName = $cohortFolderName;
            if (preg_match('/^(\d{4})\s*-\s*(.*)$/', $cohortFolderName, $matches)) {
                $cohortYear = (int) $matches[1];
                $cohortName = trim($matches[2]);
            }

            if (! $cohortYear) {
                if (preg_match('/\b(\d{4})\b/', $cohortFolderName, $matches)) {
                    $cohortYear = (int) $matches[1];
                } else {
                    $cohortYear = (int) date('Y');
                }
            }

            // Create or update cohort
            $cohort = AdmissionCohort::updateOrCreate(
                ['year' => $cohortYear],
                [
                    'cohort_name' => $cohortFolderName,
                    'normalized_name' => $this->normalizeText($cohortName),
                    'note' => "Seeded from directory: {$cohortFolderName}",
                ]
            );

            $khoaPath = $cohortPath.'/Khoa';
            if (! is_dir($khoaPath)) {
                continue;
            }

            foreach (glob($khoaPath.'/*', GLOB_ONLYDIR) as $facultyPath) {
                $facultyFolderName = basename($facultyPath);

                // Standardize faculty name (prepend "Khoa " if not present)
                $facultyName = $facultyFolderName;
                if (! preg_match('/^khoa\s+/iu', $facultyFolderName)) {
                    $facultyName = 'Khoa '.$facultyFolderName;
                }

                $facultySlug = $this->getFacultySlug($facultyFolderName);
                $facultyCode = $this->getFacultyCode($facultyName);

                // Create or update faculty
                $faculty = Faculty::updateOrCreate(
                    ['slug' => $facultySlug],
                    [
                        'name' => $facultyName,
                        'code' => $facultyCode,
                        'normalized_name' => $this->normalizeText($facultyName),
                        'status' => 'active',
                    ]
                );

                $nganhPath = $facultyPath.'/Ngành';
                if (! is_dir($nganhPath)) {
                    continue;
                }

                foreach (glob($nganhPath.'/*', GLOB_ONLYDIR) as $majorPath) {
                    $majorName = basename($majorPath);
                    $majorSlug = $this->getMajorSlug($majorName);
                    $majorCode = Str::upper(Str::replace('-', '_', $majorSlug));

                    // Create or update major (chatbot table)
                    $major = Major::updateOrCreate(
                        ['code' => $majorCode],
                        [
                            'faculty_id' => $faculty->id,
                            'name' => $majorName,
                            'normalized_name' => $this->normalizeText($majorName),
                            'degree_level' => 'undergraduate',
                        ]
                    );

                    // Create or update academic program (compatibility table)
                    AcademicProgram::updateOrCreate(
                        [
                            'faculty_id' => $faculty->id,
                            'slug' => $majorSlug,
                        ],
                        [
                            'name' => $majorName,
                            'degree_level' => 'undergraduate',
                            'status' => 'active',
                        ]
                    );

                    // Create or update training program mapping
                    TrainingProgram::updateOrCreate(
                        [
                            'cohort_id' => $cohort->id,
                            'faculty_id' => $faculty->id,
                            'major_id' => $major->id,
                        ],
                        [
                            'title' => "{$majorName} - {$cohortName}",
                            'total_credits' => 0,
                            'effective_from' => $cohortYear,
                            'effective_to' => $cohortYear + 4,
                            'status' => 'published',
                            'published_at' => now(),
                        ]
                    );
                }
            }
        }

        $this->command->info('Database seeded successfully from folder structures.');
    }

    private function getFacultySlug(string $folderName): string
    {
        $map = [
            'công nghệ thông tin' => 'cntt',
            'toán - tin' => 'toan-thong-ke',
            'tiếng trung' => 'tieng-trung-quoc',
        ];

        $lower = mb_strtolower(trim($folderName), 'UTF-8');
        if (isset($map[$lower])) {
            return $map[$lower];
        }

        return Str::slug($folderName);
    }

    private function getMajorSlug(string $folderName): string
    {
        $map = [
            'sư phạm tin' => 'su-pham-tin-hoc',
            'ngôn ngữ trung' => 'ngon-ngu-trung-quoc',
        ];

        $lower = mb_strtolower(trim($folderName), 'UTF-8');
        if (isset($map[$lower])) {
            return $map[$lower];
        }

        return Str::slug($folderName);
    }

    private function normalizeText(?string $text): ?string
    {
        if (! $text) {
            return null;
        }

        $text = mb_strtolower($text, 'UTF-8');

        $unicode = [
            'a' => 'á|à|ả|ã|ạ|ă|ắ|ằ|ẳ|ẵ|ặ|â|ấ|ầ|ẩ|ẫ|ậ',
            'd' => 'đ',
            'e' => 'é|è|ẻ|ẽ|ẹ|ê|ế|ề|ể|ễ|ệ',
            'i' => 'í|ì|ỉ|ĩ|ị',
            'o' => 'ó|ò|ỏ|õ|ọ|ô|ố|ồ|ổ|ỗ|ộ|ơ|ớ|ờ|ở|ỡ|ợ',
            'u' => 'ú|ù|ủ|ũ|ụ|ư|ứ|ừ|ử|ữ|ự',
            'y' => 'ý|ỳ|ỷ|ỹ|ỵ',
        ];

        foreach ($unicode as $nonUnicode => $unicodePattern) {
            $text = preg_replace("/($unicodePattern)/i", $nonUnicode, $text);
        }

        return trim(preg_replace('/\s+/', ' ', $text));
    }

    private function getFacultyCode(string $name): string
    {
        $cleanName = preg_replace('/^khoa\s+/iu', '', $name);
        $map = [
            'công nghệ thông tin' => 'CNTT',
            'giáo dục chính trị' => 'GDCT',
            'giáo dục quốc phòng' => 'GDQP',
            'giáo dục tiểu học' => 'GDTH',
            'giáo dục mầm non' => 'GDMN',
            'giáo dục đặc biệt' => 'GDDB',
            'hóa học' => 'HH',
            'khoa học giáo dục' => 'KHGD',
            'ngữ văn' => 'NV',
            'tiếng anh' => 'TA',
            'tiếng hàn quốc' => 'THQ',
            'tiếng nga' => 'TNGA',
            'tiếng nhật' => 'TNHT',
            'tiếng pháp' => 'TP',
            'tiếng trung' => 'TTR',
            'toán - tin' => 'TTIN',
            'toán - thống kê' => 'TTK',
            'tâm lý học' => 'TLH',
            'vật lý' => 'VL',
            'địa lý' => 'DL',
        ];

        $lower = mb_strtolower(trim($cleanName), 'UTF-8');
        if (isset($map[$lower])) {
            return $map[$lower];
        }

        $words = explode(' ', preg_replace('/\s+/', ' ', trim($cleanName)));
        $code = '';
        foreach ($words as $word) {
            $code .= mb_substr($word, 0, 1, 'UTF-8');
        }

        return mb_strtoupper($code, 'UTF-8');
    }

    private function legacyFaculties(): array
    {
        return [
            ['name' => 'Khoa Công nghệ Thông tin', 'slug' => 'cntt', 'description' => 'Đào tạo các chương trình công nghệ thông tin và sư phạm tin học.', 'status' => 'active', 'programs' => [['name' => 'Công nghệ thông tin', 'slug' => 'cong-nghe-thong-tin'], ['name' => 'Sư phạm Tin học', 'slug' => 'su-pham-tin-hoc']]],
            ['name' => 'Khoa Toán - Thống kê', 'slug' => 'toan-thong-ke', 'description' => 'Đào tạo toán học, thống kê ứng dụng và sư phạm toán.', 'status' => 'active', 'programs' => [['name' => 'Sư phạm Toán học', 'slug' => 'su-pham-toan-hoc'], ['name' => 'Toán học', 'slug' => 'toan-hoc']]],
            ['name' => 'Khoa Vật lý', 'slug' => 'vat-ly', 'description' => 'Đào tạo vật lý học và sư phạm vật lý.', 'status' => 'active', 'programs' => [['name' => 'Sư phạm Vật lý', 'slug' => 'su-pham-vat-ly'], ['name' => 'Vật lý học', 'slug' => 'vat-ly-hoc']]],
            ['name' => 'Khoa Hóa học', 'slug' => 'hoa-hoc', 'description' => 'Đào tạo hóa học và sư phạm hóa học.', 'status' => 'active', 'programs' => [['name' => 'Sư phạm Hóa học', 'slug' => 'su-pham-hoa-hoc'], ['name' => 'Hóa học', 'slug' => 'hoa-hoc']]],
            ['name' => 'Khoa Sinh học', 'slug' => 'sinh-hoc', 'description' => 'Đào tạo sinh học và sư phạm sinh học.', 'status' => 'active', 'programs' => [['name' => 'Sư phạm Sinh học', 'slug' => 'su-pham-sinh-hoc'], ['name' => 'Sinh học', 'slug' => 'sinh-hoc']]],
            ['name' => 'Khoa Ngữ văn', 'slug' => 'ngu-van', 'description' => 'Đào tạo văn học và sư phạm ngữ văn.', 'status' => 'active', 'programs' => [['name' => 'Sư phạm Ngữ văn', 'slug' => 'su-pham-ngu-van'], ['name' => 'Văn học', 'slug' => 'van-hoc']]],
            ['name' => 'Khoa Lịch sử', 'slug' => 'lich-su', 'description' => 'Đào tạo lịch sử, quốc tế học và các chương trình sư phạm liên quan.', 'status' => 'active', 'programs' => [['name' => 'Sư phạm Lịch sử', 'slug' => 'su-pham-lich-su'], ['name' => 'Lịch sử', 'slug' => 'lich-su'], ['name' => 'Quốc tế học', 'slug' => 'quoc-te-hoc'], ['name' => 'Sư phạm Lịch sử - Địa lý', 'slug' => 'su-pham-lich-su-dia-ly']]],
            ['name' => 'Khoa Địa lý', 'slug' => 'dia-ly', 'description' => 'Đào tạo địa lý học, Việt Nam học và sư phạm địa lý.', 'status' => 'active', 'programs' => [['name' => 'Sư phạm Địa lý', 'slug' => 'su-pham-dia-ly'], ['name' => 'Địa lý học', 'slug' => 'dia-ly-hoc'], ['name' => 'Việt Nam học', 'slug' => 'viet-nam-hoc']]],
            ['name' => 'Khoa Tiếng Anh', 'slug' => 'tieng-anh', 'description' => 'Đào tạo ngôn ngữ Anh và sư phạm tiếng Anh.', 'status' => 'active', 'programs' => [['name' => 'Sư phạm Tiếng Anh', 'slug' => 'su-pham-tieng-anh'], ['name' => 'Ngôn ngữ Anh', 'slug' => 'ngon-ngu-anh']]],
            ['name' => 'Khoa Tiếng Trung Quốc', 'slug' => 'tieng-trung-quoc', 'description' => 'Đào tạo ngôn ngữ Trung Quốc và sư phạm tiếng Trung Quốc.', 'status' => 'active', 'programs' => [['name' => 'Sư phạm Tiếng Trung Quốc', 'slug' => 'su-pham-tieng-trung-quoc'], ['name' => 'Ngôn ngữ Trung Quốc', 'slug' => 'ngon-ngu-trung-quoc']]],
            ['name' => 'Khoa Tiếng Pháp', 'slug' => 'tieng-phap', 'description' => 'Đào tạo ngôn ngữ Pháp và sư phạm tiếng Pháp.', 'status' => 'active', 'programs' => [['name' => 'Sư phạm Tiếng Pháp', 'slug' => 'su-pham-tieng-phap'], ['name' => 'Ngôn ngữ Pháp', 'slug' => 'ngon-ngu-phap']]],
            ['name' => 'Khoa Tiếng Nga', 'slug' => 'tieng-nga', 'description' => 'Đào tạo ngôn ngữ Nga và sư phạm tiếng Nga.', 'status' => 'active', 'programs' => [['name' => 'Sư phạm Tiếng Nga', 'slug' => 'su-pham-tieng-nga'], ['name' => 'Ngôn ngữ Nga', 'slug' => 'ngon-ngu-nga']]],
            ['name' => 'Khoa Tiếng Nhật', 'slug' => 'tieng-nhat', 'description' => 'Đào tạo ngôn ngữ Nhật và sư phạm tiếng Nhật.', 'status' => 'active', 'programs' => [['name' => 'Sư phạm Tiếng Nhật', 'slug' => 'su-pham-tieng-nhat'], ['name' => 'Ngôn ngữ Nhật', 'slug' => 'ngon-ngu-nhat']]],
            ['name' => 'Khoa Tiếng Hàn Quốc', 'slug' => 'tieng-han-quoc', 'description' => 'Đào tạo ngôn ngữ Hàn Quốc.', 'status' => 'active', 'programs' => [['name' => 'Ngôn ngữ Hàn Quốc', 'slug' => 'ngon-ngu-han-quoc']]],
            ['name' => 'Khoa Giáo dục Tiểu học', 'slug' => 'giao-duc-tieu-hoc', 'description' => 'Đào tạo giáo viên giáo dục tiểu học.', 'status' => 'active', 'programs' => [['name' => 'Giáo dục Tiểu học', 'slug' => 'giao-duc-tieu-hoc']]],
            ['name' => 'Khoa Giáo dục Mầm non', 'slug' => 'giao-duc-mam-non', 'description' => 'Đào tạo giáo viên giáo dục mầm non.', 'status' => 'active', 'programs' => [['name' => 'Giáo dục Mầm non', 'slug' => 'giao-duc-mam-non']]],
            ['name' => 'Khoa Giáo dục Đặc biệt', 'slug' => 'giao-duc-dac-biet', 'description' => 'Đào tạo giáo dục đặc biệt.', 'status' => 'active', 'programs' => [['name' => 'Giáo dục Đặc biệt', 'slug' => 'giao-duc-dac-biet']]],
            ['name' => 'Khoa Giáo dục Chính trị', 'slug' => 'giao-duc-chinh-tri', 'description' => 'Đào tạo giáo dục chính trị và giáo dục công dân.', 'status' => 'active', 'programs' => [['name' => 'Giáo dục Chính trị', 'slug' => 'giao-duc-chinh-tri'], ['name' => 'Giáo dục Công dân', 'slug' => 'giao-duc-cong-dan']]],
            ['name' => 'Khoa Tâm lý học', 'slug' => 'tam-ly-hoc', 'description' => 'Đào tạo tâm lý học và tâm lý học giáo dục.', 'status' => 'active', 'programs' => [['name' => 'Tâm lý học', 'slug' => 'tam-ly-hoc'], ['name' => 'Tâm lý học giáo dục', 'slug' => 'tam-ly-hoc-giao-duc']]],
            ['name' => 'Khoa Khoa học Giáo dục', 'slug' => 'khoa-hoc-giao-duc', 'description' => 'Đào tạo khoa học giáo dục, quản lý giáo dục và công tác xã hội.', 'status' => 'active', 'programs' => [['name' => 'Quản lý giáo dục', 'slug' => 'quan-ly-giao-duc'], ['name' => 'Giáo dục học', 'slug' => 'giao-duc-hoc'], ['name' => 'Công tác xã hội', 'slug' => 'cong-tac-xa-hoi'], ['name' => 'Công nghệ giáo dục', 'slug' => 'cong-nghe-giao-duc']]],
            ['name' => 'Khoa Giáo dục Thể chất', 'slug' => 'giao-duc-the-chat', 'description' => 'Đào tạo giáo dục thể chất.', 'status' => 'active', 'programs' => [['name' => 'Giáo dục Thể chất', 'slug' => 'giao-duc-the-chat']]],
            ['name' => 'Khoa Giáo dục Quốc phòng', 'slug' => 'giao-duc-quoc-phong', 'description' => 'Đào tạo giáo dục quốc phòng và an ninh.', 'status' => 'active', 'programs' => [['name' => 'Giáo dục Quốc phòng và An ninh', 'slug' => 'giao-duc-quoc-phong-va-an-ninh']]],
            ['name' => 'Khoa Nghệ thuật', 'slug' => 'nghe-thuat', 'description' => 'Đào tạo các ngành sư phạm nghệ thuật.', 'status' => 'active', 'programs' => [['name' => 'Sư phạm Âm nhạc', 'slug' => 'su-pham-am-nhac'], ['name' => 'Sư phạm Mỹ thuật', 'slug' => 'su-pham-my-thuat']]],
            ['name' => 'Khoa Khoa học Tự nhiên', 'slug' => 'khoa-hoc-tu-nhien', 'description' => 'Đào tạo chương trình sư phạm khoa học tự nhiên liên ngành.', 'status' => 'active', 'programs' => [['name' => 'Sư phạm Khoa học tự nhiên', 'slug' => 'su-pham-khoa-hoc-tu-nhien']]],
        ];
    }
}
