<?php

namespace Database\Factories;

use App\Models\CareerFaculty;
use App\Models\CareerMajor;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<CareerMajor>
 */
class CareerMajorFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->word;
        $name = fake()->word;
        $name = fake()->unique()->word;
        $name = fake()->unique()->word;
        $name = fake()->unique()->word;

        return ['faculty_id' => CareerFaculty::factory(), 'name' => $name, 'slug' => Str::slug($name)];
    }
}
