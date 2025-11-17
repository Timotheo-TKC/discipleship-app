<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ClassSession>
 */
class ClassSessionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'class_id' => \App\Models\DiscipleshipClass::factory(),
            'session_date' => $this->faker->dateTimeBetween('-1 month', '+1 month')->format('Y-m-d'),
            'topic' => $this->faker->sentence(3),
            'notes' => $this->faker->optional(0.6)->paragraph(),
            'location' => $this->faker->optional(0.8)->randomElement(['Main Hall', 'Conference Room', 'Fellowship Hall', 'Prayer Room']),
            'created_by' => \App\Models\User::factory(),
        ];
    }
}
