<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DiscipleshipClass>
 */
class DiscipleshipClassFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $classTitles = [
            'New Believers Class',
            'Biblical Foundations',
            'Discipleship 101',
            'Christian Living',
            'Prayer and Worship',
            'Bible Study Methods',
            'Evangelism Training',
            'Church Leadership',
            'Marriage and Family',
            'Youth Discipleship',
        ];

        $descriptions = [
            'Introduction to basic Christian beliefs and practices for new converts.',
            'Essential teachings from the Bible for spiritual growth.',
            'Comprehensive discipleship training covering all aspects of Christian life.',
            'Practical guidance for living as a Christian in today\'s world.',
            'Understanding the importance and practice of prayer and worship.',
            'Learn effective methods for personal and group Bible study.',
            'Training in sharing the gospel and leading others to Christ.',
            'Developing leadership skills for church ministry.',
            'Biblical principles for marriage, family, and relationships.',
            'Special discipleship program designed for young people.',
        ];

        return [
            'title' => $this->faker->randomElement($classTitles),
            'description' => $this->faker->randomElement($descriptions),
            'mentor_id' => \App\Models\User::factory()->state(['role' => 'pastor']),
            'schedule_type' => $this->faker->randomElement(['weekly', 'biweekly', 'monthly']),
            'schedule_day' => $this->faker->randomElement(['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday']),
            'schedule_time' => $this->faker->time('H:i:s'),
            'start_date' => $this->faker->dateTimeBetween('-1 month', '+1 month')->format('Y-m-d'),
            'end_date' => $this->faker->dateTimeBetween('+1 month', '+3 months')->format('Y-m-d'),
            'capacity' => $this->faker->numberBetween(15, 50),
            'duration_weeks' => $this->faker->numberBetween(8, 16),
            'location' => $this->faker->randomElement(['Main Hall', 'Conference Room', 'Fellowship Hall', 'Prayer Room']),
            'is_active' => $this->faker->boolean(80), // 80% chance of being active
        ];
    }

    /**
     * Create a class with sessions already generated
     */
    public function withSessions(): self
    {
        return $this->afterCreating(function (\App\Models\DiscipleshipClass $class) {
            $class->generateSessions();
        });
    }

    /**
     * Create a class with enrolled members
     */
    public function withMembers(int $count = 10): self
    {
        return $this->afterCreating(function (\App\Models\DiscipleshipClass $class) use ($count) {
            $members = \App\Models\Member::factory($count)->create();

            foreach ($members as $member) {
                // Create class sessions first
                if ($class->sessions()->count() === 0) {
                    $class->generateSessions();
                }

                // Enroll member in some sessions
                $sessions = $class->sessions()->take(rand(1, $class->sessions()->count()))->get();
                foreach ($sessions as $session) {
                    \App\Models\Attendance::factory()->create([
                        'class_session_id' => $session->id,
                        'member_id' => $member->id,
                        'marked_by' => $class->mentor_id,
                        'marked_at' => $session->session_date . ' ' . $this->faker->time('H:i:s'),
                    ]);
                }
            }
        });
    }
}
