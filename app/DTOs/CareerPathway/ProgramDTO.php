<?php

namespace App\DTOs\CareerPathway;

use App\Enums\ProgramStatus;

class ProgramDTO
{
    public function __construct(
        public readonly string $cohortName,
        public readonly string $facultyName,
        public readonly string $majorName,
        public readonly string $originalDir,
        public readonly string $filePath,
        public array $semesters = [],
        public array $dataQualityIssues = [],
        public ProgramStatus $status = ProgramStatus::READY,
        public int $totalCredits = 0,
        public int $totalSemesters = 0
    ) {}
}
