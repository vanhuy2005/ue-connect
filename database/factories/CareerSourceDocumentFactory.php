<?php

namespace Database\Factories;

use App\Models\CareerImportRun;
use App\Models\CareerSourceDocument;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CareerSourceDocument>
 */
class CareerSourceDocumentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return ['import_run_id' => CareerImportRun::factory(), 'original_filename' => 'test.md', 'storage_path' => 'test.md', 'hash' => fake()->md5, 'type' => 'markdown', 'extraction_status' => 'success'];
    }
}
