<?php

namespace Database\Factories;

use App\Enums\CommunityEventStatus;
use App\Models\Community;
use App\Models\CommunityEvent;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<CommunityEvent>
 */
class CommunityEventFactory extends Factory
{
    protected $model = CommunityEvent::class;

    public function definition(): array
    {
        $title = $this->faker->sentence(4);
        $startsAt = $this->faker->dateTimeBetween('now', '+3 months');

        return [
            'community_id' => Community::factory(),
            'created_by' => User::factory(),
            'title' => rtrim($title, '.'),
            'slug' => Str::slug($title).'-'.$this->faker->unique()->randomNumber(6),
            'description' => $this->faker->paragraphs(2, true),
            'event_type' => $this->faker->randomElement(['in_person', 'online', 'hybrid']),
            'status' => CommunityEventStatus::Published->value,
            'visibility' => 'community_members',
            'starts_at' => $startsAt,
            'ends_at' => (clone $startsAt)->modify('+2 hours'),
            'location' => $this->faker->optional()->address(),
            'online_link' => null,
            'rsvp_required' => true,
            'rsvp_deadline' => null,
            'capacity' => $this->faker->optional()->numberBetween(20, 200),
            'waitlist_enabled' => false,
            'going_count' => 0,
            'interested_count' => 0,
            'waitlist_count' => 0,
        ];
    }

    public function published(): static
    {
        return $this->state(['status' => CommunityEventStatus::Published->value]);
    }

    public function draft(): static
    {
        return $this->state(['status' => CommunityEventStatus::Draft->value]);
    }

    public function upcoming(): static
    {
        return $this->state([
            'starts_at' => $this->faker->dateTimeBetween('+1 day', '+3 months'),
        ]);
    }

    public function past(): static
    {
        return $this->state([
            'starts_at' => $this->faker->dateTimeBetween('-3 months', '-1 day'),
        ]);
    }

    public function online(): static
    {
        return $this->state([
            'event_type' => 'online',
            'location' => null,
            'online_link' => $this->faker->url(),
        ]);
    }

    public function withCapacity(int $capacity): static
    {
        return $this->state(['capacity' => $capacity]);
    }
}
