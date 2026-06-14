<?php

namespace App\DTOs\CareerPathway;

class CourseDTO
{
    public function __construct(
        public readonly string $code,
        public readonly string $name,
        public readonly int $credits,
        public readonly bool $isMandatory,
        public readonly ?string $knowledgeBlock,
        public ?string $description = null
    ) {}
}
