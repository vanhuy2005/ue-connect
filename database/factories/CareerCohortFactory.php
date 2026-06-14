<?php

namespace Database\Factories;

use App\Models\CareerCohort;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<CareerCohort>
 */
class CareerCohortFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->year;
        $name = fake()->year;
        $name = fake()->unique()->year;
        $name = fake()->unique()->year;
        $name = fake()->unique()->year;

        return ['name' => $name, 'slug' => Str::slug($name)];
    }
}
