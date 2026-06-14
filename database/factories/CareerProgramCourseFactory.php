<?php

namespace Database\Factories;

use App\Models\CareerCourse;
use App\Models\CareerProgram;
use App\Models\CareerProgramCourse;
use App\Models\CareerSemester;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CareerProgramCourse>
 */
class CareerProgramCourseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return ['program_id' => CareerProgram::factory(), 'semester_id' => CareerSemester::factory(), 'course_id' => CareerCourse::factory(), 'course_code' => fake()->word, 'is_mandatory' => true];
    }
}
