<?php

namespace Database\Factories;

use App\Enums\CommunityResourceStatus;
use App\Models\Community;
use App\Models\CommunityResource;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CommunityResource>
 */
class CommunityResourceFactory extends Factory
{
    protected $model = CommunityResource::class;

    public function definition(): array
    {
        return [
            'community_id' => Community::factory(),
            'submitted_by' => User::factory(),
            'title' => $this->faker->sentence(),
            'description' => $this->faker->optional()->paragraph(),
            'resource_type' => 'link',
            'url' => $this->faker->url(),
            'file_id' => null,
            'category' => $this->faker->optional()->word(),
            'copyright_attestation' => true,
            'status' => CommunityResourceStatus::PendingReview->value,
            'approved_by' => null,
            'approved_at' => null,
            'rejection_reason' => null,
        ];
    }

    public function published(): static
    {
        return $this->state([
            'status' => CommunityResourceStatus::Published->value,
            'approved_by' => User::factory(),
            'approved_at' => now(),
        ]);
    }

    public function pending(): static
    {
        return $this->state(['status' => CommunityResourceStatus::PendingReview->value]);
    }

    public function rejected(): static
    {
        return $this->state([
            'status' => CommunityResourceStatus::Rejected->value,
            'rejection_reason' => 'Không phù hợp với nội dung cộng đồng.',
        ]);
    }
}
