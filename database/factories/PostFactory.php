<?php

namespace Database\Factories;

use App\Enums\ModerationStatus;
use App\Enums\PostStatus;
use App\Enums\PostType;
use App\Enums\PostVisibility;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Post>
 */
class PostFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'body' => fake()->paragraph(),
            'visibility' => PostVisibility::VERIFIED_USERS,
            'status' => PostStatus::PUBLISHED,
            'post_type' => PostType::STANDARD,
            'moderation_status' => ModerationStatus::NONE,
            'published_at' => now(),
        ];
    }
}
