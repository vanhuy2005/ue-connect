<?php

namespace Database\Factories;

use App\Models\Community;
use App\Models\CommunityJoinRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CommunityJoinRequest>
 */
class CommunityJoinRequestFactory extends Factory
{
    protected $model = CommunityJoinRequest::class;

    public function definition(): array
    {
        return [
            'community_id' => Community::factory(),
            'user_id' => User::factory(),
            'status' => 'pending',
            'join_reason' => $this->faker->optional()->sentence(),
            'reviewed_by' => null,
            'review_reason' => null,
        ];
    }

    public function pending(): static
    {
        return $this->state(['status' => 'pending']);
    }

    public function approved(): static
    {
        return $this->state([
            'status' => 'approved',
            'reviewed_by' => User::factory(),
            'reviewed_at' => now(),
        ]);
    }

    public function rejected(): static
    {
        return $this->state([
            'status' => 'rejected',
            'reviewed_by' => User::factory(),
            'review_reason' => $this->faker->sentence(),
            'reviewed_at' => now(),
        ]);
    }
}
