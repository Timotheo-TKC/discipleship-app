<?php

namespace Tests\Feature\Auth;

use App\Models\DiscipleshipClass;
use App\Models\Member;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleBasedAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test users with different roles
        $this->admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $this->pastor = User::factory()->create(['role' => User::ROLE_PASTOR]);
        $this->member = User::factory()->create(['role' => User::ROLE_MEMBER]);
    }

    /** @test */
    public function admin_can_access_all_resources()
    {
        $this->actingAs($this->admin);

        // Dashboard access
        $this->get(route('dashboard'))->assertOk();

        // Member management
        $this->get(route('members.index'))->assertOk();
        $this->get(route('members.create'))->assertOk();

        // Class management
        $this->get(route('classes.index'))->assertOk();
        $this->get(route('classes.create'))->assertOk();

        // Mentorship management
        $this->get(route('mentorships.index'))->assertOk();
        $this->get(route('mentorships.create'))->assertOk();

        // Admin panel
        $this->get(route('admin.dashboard'))->assertOk();
        $this->get(route('admin.users'))->assertOk();
    }

    /** @test */
    public function pastor_can_manage_members_and_classes()
    {
        $this->actingAs($this->pastor);

        // Dashboard access
        $this->get(route('dashboard'))->assertOk();

        // Member management
        $this->get(route('members.index'))->assertOk();
        $this->get(route('members.create'))->assertOk();

        // Class management
        $this->get(route('classes.index'))->assertOk();
        $this->get(route('classes.create'))->assertOk();

        // Mentorship management
        $this->get(route('mentorships.index'))->assertOk();
        $this->get(route('mentorships.create'))->assertOk();

        // Admin panel access denied
        $this->get(route('admin.dashboard'))->assertForbidden();
        $this->get(route('admin.users'))->assertForbidden();
    }

    // Coordinator role removed - test removed

    /** @test */
    public function member_can_only_access_dashboard()
    {
        $this->actingAs($this->member);

        // Dashboard access
        $this->get(route('dashboard'))->assertOk();

        // All other resources denied
        $this->get(route('members.index'))->assertForbidden();
        // Members can browse classes (but not manage them)
        $this->get(route('classes.index'))->assertOk();
        $this->get(route('mentorships.index'))->assertForbidden();
        $this->get(route('admin.dashboard'))->assertForbidden();
    }

    /** @test */
    public function unauthenticated_user_cannot_access_protected_routes()
    {
        $this->get(route('dashboard'))->assertRedirect(route('login'));
        $this->get(route('members.index'))->assertRedirect(route('login'));
        $this->get(route('classes.index'))->assertRedirect(route('login'));
        $this->get(route('mentorships.index'))->assertRedirect(route('login'));
        $this->get(route('admin.dashboard'))->assertRedirect(route('login'));
    }

    /** @test */
    public function admin_can_create_members()
    {
        $this->actingAs($this->admin);

        $memberData = [
            'full_name' => 'John Doe',
            'phone' => '0712345678',
            'email' => 'john@example.com',
            'date_of_conversion' => '2024-01-01',
            'preferred_contact' => 'sms',
            'notes' => 'Test member',
        ];

        $this->post(route('members.store'), $memberData)
            ->assertRedirect(route('members.index'));

        $this->assertDatabaseHas('members', [
            'full_name' => 'John Doe',
            'phone' => '0712345678',
        ]);
    }


    /** @test */
    public function admin_can_create_classes()
    {
        $this->actingAs($this->admin);

        $classData = [
            'title' => 'Test Class',
            'description' => 'Test description',
            'mentor_id' => $this->pastor->id,
            'capacity' => 20,
            'duration_weeks' => 8,
            'schedule_type' => 'weekly',
            'schedule_day' => 'sunday',
            'schedule_time' => '10:00',
            'start_date' => now()->addDays(7)->format('Y-m-d'),
            'end_date' => now()->addDays(7)->addWeeks(12)->format('Y-m-d'),
            'location' => 'Main Hall',
        ];

        $response = $this->post(route('classes.store'), $classData);
        $response->assertRedirect();
        
        $class = DiscipleshipClass::where('title', 'Test Class')->first();
        $this->assertNotNull($class);

        $this->assertDatabaseHas('classes', [
            'title' => 'Test Class',
            'mentor_id' => $this->pastor->id,
        ]);
    }


    /** @test */
    public function member_cannot_create_classes()
    {
        $this->actingAs($this->member);

        $classData = [
            'title' => 'Test Class',
            'description' => 'Test description',
            'mentor_id' => $this->pastor->id,
            'capacity' => 20,
            'duration_weeks' => 8,
            'schedule_type' => 'weekly',
            'schedule_day' => 'sunday',
            'schedule_time' => '10:00:00',
            'start_date' => '2024-01-01',
            'end_date' => '2024-03-01',
            'location' => 'Main Hall',
        ];

        $this->post(route('classes.store'), $classData)
            ->assertForbidden();

        $this->assertDatabaseMissing('classes', [
            'title' => 'Test Class',
        ]);
    }
}
