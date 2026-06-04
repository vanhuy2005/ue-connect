<?php

namespace Database\Factories;

use App\Enums\CommunityJoinPolicy;
use App\Enums\CommunityStatus;
use App\Enums\CommunityType;
use App\Enums\CommunityVisibility;
use App\Models\Community;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Community>
 */
class CommunityFactory extends Factory
{
    protected $model = Community::class;

    public function definition(): array
    {
        $name = $this->faker->unique()->words(3, true).' Community';

        return [
            'name' => Str::title($name),
            'slug' => Str::slug($name).'-'.$this->faker->unique()->randomNumber(5),
            'type' => $this->faker->randomElement(CommunityType::cases())->value,
            'description' => $this->faker->paragraph(),
            'short_description' => $this->faker->sentence(),
            'rules' => $this->faker->optional()->paragraph(),
            'status' => CommunityStatus::Active->value,
            'visibility' => CommunityVisibility::Public->value,
            'join_policy' => $this->faker->randomElement(CommunityJoinPolicy::cases())->value,
            'members_count' => 0,
            'post_count' => 0,
            'resource_count' => 0,
            'created_by' => User::factory(),
            'owner_id' => null,
        ];
    }

    public function active(): static
    {
        return $this->state(['status' => CommunityStatus::Active->value]);
    }

    public function suspended(): static
    {
        return $this->state([
            'status' => CommunityStatus::Suspended->value,
            'suspended_at' => now(),
            'suspended_reason' => 'Test suspension',
        ]);
    }

    public function archived(): static
    {
        return $this->state([
            'status' => CommunityStatus::Archived->value,
            'archived_at' => now(),
        ]);
    }

    public function draft(): static
    {
        return $this->state(['status' => CommunityStatus::Draft->value]);
    }

    public function openJoin(): static
    {
        return $this->state(['join_policy' => CommunityJoinPolicy::Open->value]);
    }

    public function requiresApproval(): static
    {
        return $this->state(['join_policy' => CommunityJoinPolicy::ApprovalRequired->value]);
    }

    public function forOwner(User $owner): static
    {
        return $this->state([
            'owner_id' => $owner->id,
            'created_by' => $owner->id,
        ]);
    }

    public function club(): static
    {
        return $this->state(['type' => CommunityType::Club->value]);
    }

    public function academic(): static
    {
        return $this->state(['type' => CommunityType::AcademicGroup->value]);
    }
}
