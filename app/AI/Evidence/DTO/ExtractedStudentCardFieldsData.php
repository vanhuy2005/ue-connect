<?php

namespace App\AI\Evidence\DTO;

readonly class ExtractedStudentCardFieldsData
{
    public function __construct(
        public ?string $fullName = null,
        public ?string $studentCode = null,
        public ?string $faculty = null,
        public ?string $academicProgram = null,
        public ?string $cohort = null,
        public ?string $schoolName = null,
        public ?bool $portraitPresentHint = null,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'full_name' => $this->fullName,
            'student_code' => $this->studentCode,
            'faculty' => $this->faculty,
            'academic_program' => $this->academicProgram,
            'cohort' => $this->cohort,
            'school_name' => $this->schoolName,
            'portrait_present_hint' => $this->portraitPresentHint,
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            fullName: $data['full_name'] ?? null,
            studentCode: $data['student_code'] ?? null,
            faculty: $data['faculty'] ?? null,
            academicProgram: $data['academic_program'] ?? null,
            cohort: $data['cohort'] ?? null,
            schoolName: $data['school_name'] ?? null,
            portraitPresentHint: isset($data['portrait_present_hint']) ? (bool) $data['portrait_present_hint'] : null,
        );
    }
}
