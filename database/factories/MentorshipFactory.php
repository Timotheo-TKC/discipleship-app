<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Mentorship>
 */
class MentorshipFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startDate = $this->faker->dateTimeBetween('-1 year', 'now');
        $status = $this->faker->randomElement(['active', 'completed', 'paused']);

        return [
            'member_id' => \App\Models\Member::factory(),
            'mentor_id' => \App\Models\User::factory()->state(['role' => 'pastor']),
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $status === 'completed' ? $this->faker->dateTimeBetween($startDate, 'now')->format('Y-m-d') : null,
            'status' => $status,
            'meeting_frequency' => $this->faker->randomElement(['weekly', 'biweekly', 'monthly']),
            'notes' => $this->faker->optional(0.8)->paragraph(),
            'completed_at' => $status === 'completed' ? $this->faker->dateTimeBetween($startDate, 'now') : null,
        ];
    }
}
