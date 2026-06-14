<?php

namespace Database\Factories;

use App\Models\CareerCohort;
use App\Models\CareerFaculty;
use App\Models\CareerMajor;
use App\Models\CareerProgram;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<CareerProgram>
 */
class CareerProgramFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->words(3, true);
        $name = fake()->unique()->words(3, true);
        $name = fake()->unique()->words(3, true);

        return [
            'cohort_id' => CareerCohort::factory(),
            'faculty_id' => CareerFaculty::factory(),
            'major_id' => CareerMajor::factory(),
            'name' => $name,
            'slug' => Str::slug($name),
            'status' => 'ready',
            'total_credits' => 120,
        ];
    }
}
