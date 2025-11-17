<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Attendance>
 */
class AttendanceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'class_session_id' => \App\Models\ClassSession::factory(),
            'member_id' => \App\Models\Member::factory(),
            'status' => $this->faker->randomElement(['present', 'absent', 'excused']),
            'marked_by' => \App\Models\User::factory(),
            'marked_at' => $this->faker->dateTimeBetween('-1 week', 'now'),
        ];
    }
}
