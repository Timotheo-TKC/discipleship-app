<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Member>
 */
class MemberFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $preferredContacts = ['email', 'call'];

        return [
            'full_name' => $this->faker->name(),
            'phone' => $this->generateKenyanPhoneNumber(),
            'email' => $this->faker->unique()->safeEmail(),
            'date_of_conversion' => $this->faker->dateTimeBetween('-2 years', '-1 month')->format('Y-m-d'),
            'preferred_contact' => $this->faker->randomElement($preferredContacts),
            'notes' => $this->faker->optional(0.7)->paragraph(),
        ];
    }

    /**
     * Generate a realistic Kenyan phone number
     */
    private function generateKenyanPhoneNumber(): string
    {
        $prefixes = ['071', '072', '073', '074', '075', '076', '077', '078', '079'];
        $prefix = $this->faker->randomElement($prefixes);
        $number = $this->faker->numberBetween(1000000, 9999999);

        return $prefix . $number;
    }

    /**
     * Create a member with a linked user
     */
    public function withUser(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'user_id' => \App\Models\User::factory(),
            ];
        });
    }

    /**
     * Create a member with high attendance rate
     */
    public function highAttendance(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'date_of_conversion' => $this->faker->dateTimeBetween('-1 year', '-6 months')->format('Y-m-d'),
                'notes' => 'Dedicated member with excellent attendance record.',
            ];
        });
    }
}
