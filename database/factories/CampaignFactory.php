<?php
// database/factories/CampaignFactory.php

namespace Database\Factories;

use App\Enums\CampaignStatus;
use App\Models\EmailTemplate;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Campaign>
 */
class CampaignFactory extends Factory
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
            'email_template_id' => EmailTemplate::factory(),
            'name' => fake()->sentence(3),
            'status' => CampaignStatus::DRAFT,
            'scheduled_at' => null,
            'sent_at' => null,
            'total_recipients' => 0,
            'sent_count' => 0,
            'failed_count' => 0,
        ];
    }

    /**
     * Indicate that the campaign is scheduled.
     */
    public function scheduled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => CampaignStatus::SCHEDULED,
            'scheduled_at' => now()->addHours(fake()->numberBetween(1, 48)),
        ]);
    }

    /**
     * Indicate that the campaign is processing.
     */
    public function processing(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => CampaignStatus::PROCESSING,
            'scheduled_at' => now()->subMinutes(5),
        ]);
    }

    /**
     * Indicate that the campaign is completed.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => CampaignStatus::COMPLETED,
            'scheduled_at' => now()->subHours(2),
            'sent_at' => now()->subHour(),
            'total_recipients' => fake()->numberBetween(100, 1000),
            'sent_count' => fake()->numberBetween(95, 100),
            'failed_count' => fake()->numberBetween(0, 5),
        ]);
    }

    /**
     * Indicate that the campaign has failed.
     */
    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => CampaignStatus::FAILED,
            'scheduled_at' => now()->subHours(1),
        ]);
    }
}
