<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $roles = ['admin', 'pastor', 'mentor', 'member'];

        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => $this->generateKenyanPhoneNumber(),
            'role' => fake()->randomElement($roles),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Generate a realistic Kenyan phone number
     */
    private function generateKenyanPhoneNumber(): string
    {
        $prefixes = ['071', '072', '073', '074', '075', '076', '077', '078', '079'];
        $prefix = fake()->randomElement($prefixes);
        $number = fake()->numberBetween(1000000, 9999999);

        return $prefix . $number;
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Create an admin user
     */
    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'admin',
            'name' => 'Admin User',
            'email' => fake()->unique()->safeEmail(),
        ]);
    }

    /**
     * Create a pastor user
     */
    public function pastor(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'pastor',
            'name' => fake()->name() . ' (Pastor)',
            'email' => fake()->unique()->safeEmail(),
        ]);
    }


    /**
     * Create a mentor user
     */
    public function mentor(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'mentor',
            'name' => fake()->name() . ' (Mentor)',
            'email' => fake()->unique()->safeEmail(),
        ]);
    }


    /**
     * Create a member user
     */
    public function member(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'member',
            'email' => fake()->unique()->safeEmail(),
        ]);
    }
}
