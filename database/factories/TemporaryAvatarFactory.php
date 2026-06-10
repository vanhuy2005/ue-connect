<?php

namespace Database\Factories;

use App\Models\TemporaryAvatar;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TemporaryAvatar>
 */
class TemporaryAvatarFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'previous_media_id' => null,
            'expires_at' => fake()->dateTimeBetween('+1 hour', '+7 days'),
        ];
    }
}
