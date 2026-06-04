<?php

namespace Database\Factories;

use App\Enums\CommunityMemberRole;
use App\Enums\CommunityMemberStatus;
use App\Models\Community;
use App\Models\CommunityMember;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CommunityMember>
 */
class CommunityMemberFactory extends Factory
{
    protected $model = CommunityMember::class;

    public function definition(): array
    {
        return [
            'community_id' => Community::factory(),
            'user_id' => User::factory(),
            'role' => CommunityMemberRole::Member->value,
            'status' => CommunityMemberStatus::Active->value,
            'joined_at' => $this->faker->dateTimeBetween('-6 months', 'now'),
            'role_label' => null,
        ];
    }

    public function active(): static
    {
        return $this->state(['status' => CommunityMemberStatus::Active->value]);
    }

    public function pending(): static
    {
        return $this->state(['status' => CommunityMemberStatus::Pending->value]);
    }

    public function manager(): static
    {
        return $this->state([
            'role' => CommunityMemberRole::Manager->value,
            'role_label' => 'Quản lý CLB',
        ]);
    }

    public function owner(): static
    {
        return $this->state(['role' => CommunityMemberRole::Owner->value]);
    }

    public function muted(): static
    {
        return $this->state([
            'status' => CommunityMemberStatus::Muted->value,
        ]);
    }

    public function restricted(): static
    {
        return $this->state([
            'status' => CommunityMemberStatus::Restricted->value,
        ]);
    }

    public function banned(): static
    {
        return $this->state([
            'status' => CommunityMemberStatus::BannedFromCommunity->value,
        ]);
    }

    public function removed(): static
    {
        return $this->state([
            'status' => CommunityMemberStatus::Removed->value,
            'removed_at' => now(),
            'remove_reason' => 'Bị xóa khỏi nhóm',
        ]);
    }
}
