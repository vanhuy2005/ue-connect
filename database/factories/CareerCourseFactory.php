<?php

namespace Database\Factories;

use App\Models\CareerCourse;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CareerCourse>
 */
class CareerCourseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return ['code' => fake()->unique()->word, 'name' => fake()->word, 'credits' => 3];
    }
}
