<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\EmailTemplate;
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
            'name' => fake()->words(3, true),
            'status' => fake()->randomElement(['draft', 'scheduled', 'processing', 'completed']),
            'total_recipients' => fake()->numberBetween(10, 1000),
            'sent_count' => 0,
            'failed_count' => 0,
        ];
    }
}
