<?php

namespace App\DTOs\CareerPathway;

class SemesterDTO
{
    public function __construct(
        public readonly int $semesterNumber,
        public readonly string $title,
        public array $courses = []
    ) {}
}
