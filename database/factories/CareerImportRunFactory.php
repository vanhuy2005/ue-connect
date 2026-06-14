<?php

namespace Database\Factories;

use App\Models\CareerImportRun;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CareerImportRun>
 */
class CareerImportRunFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return ['status' => 'completed', 'started_at' => now()];
    }
}
