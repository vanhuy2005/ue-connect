<?php

namespace Database\Factories;

use App\Models\CareerDataQualityIssue;
use App\Models\CareerImportRun;
use App\Models\CareerProgram;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CareerDataQualityIssue>
 */
class CareerDataQualityIssueFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'import_run_id' => CareerImportRun::factory(),
            'program_id' => CareerProgram::factory(),
            'issue_type' => 'empty_markdown',
            'severity' => 'p1',
            'message' => fake()->sentence,
            'context' => json_encode(['raw' => 'SECRET']),
        ];
    }
}
