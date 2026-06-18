<?php

namespace Database\Factories;

use App\Models\PermissionGrant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PermissionGrant>
 */
class PermissionGrantFactory extends Factory
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
            'permission_key' => fake()->randomElement([
                'manage_users', 'suspend_users', 'ban_users',
                'manage_permissions', 'review_verification',
                'manage_communities', 'view_audit_log',
            ]),
            'scope_type' => null,
            'scope_id' => null,
            'granted_by' => User::factory(),
            'revoked_by' => null,
            'reason' => fake()->sentence(),
            'starts_at' => null,
            'expires_at' => null,
            'status' => 'active',
            'revoked_at' => null,
        ];
    }

    /** Grant is currently revoked. */
    public function revoked(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'revoked',
            'revoked_at' => now(),
        ]);
    }

    /** Grant has already expired. */
    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'expires_at' => now()->subMinute(),
        ]);
    }

    /** Grant is scoped to a specific model. */
    public function scoped(string $scopeType, int $scopeId): static
    {
        return $this->state(fn (array $attributes) => [
            'scope_type' => $scopeType,
            'scope_id' => $scopeId,
        ]);
    }
}
