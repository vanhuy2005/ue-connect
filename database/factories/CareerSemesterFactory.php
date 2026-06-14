<?php

namespace Database\Factories;

use App\Models\CareerProgram;
use App\Models\CareerSemester;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CareerSemester>
 */
class CareerSemesterFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return ['program_id' => CareerProgram::factory(), 'semester_number' => 1, 'title' => 'Học kỳ 1'];
    }
}
