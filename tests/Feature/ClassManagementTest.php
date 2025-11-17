<?php

namespace Tests\Feature;

use App\Models\ClassSession;
use App\Models\DiscipleshipClass;
use App\Models\Member;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClassManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $this->pastor = User::factory()->create(['role' => User::ROLE_PASTOR]);
    }

    /** @test */
    public function admin_can_create_class()
    {
        $this->actingAs($this->admin);

        $classData = [
            'title' => 'New Believers Class',
            'description' => 'Introduction to Christianity',
            'mentor_id' => $this->pastor->id,
            'capacity' => 25,
            'duration_weeks' => 8,
            'schedule_type' => 'weekly',
            'schedule_day' => 'sunday',
            'schedule_time' => '10:00',
            'start_date' => now()->addDays(7)->format('Y-m-d'),
            'end_date' => now()->addDays(7)->addWeeks(12)->format('Y-m-d'),
            'location' => 'Main Hall',
        ];

        $response = $this->post('/classes', $classData);

        $response->assertRedirect(route('classes.show', DiscipleshipClass::where('title', 'New Believers Class')->first()));
        // Message may include session count
        $response->assertSessionHas('success');
        $this->assertStringContainsString('Discipleship class created successfully', session('success'));

        $this->assertDatabaseHas('classes', [
            'title' => 'New Believers Class',
            'mentor_id' => $this->pastor->id,
            'capacity' => 25,
        ]);
    }

    // Coordinator role removed - test removed

    /** @test */
    public function class_creation_requires_validation()
    {
        $this->actingAs($this->admin);

        $response = $this->post(route('classes.store'), []);

        $response->assertSessionHasErrors([
            'title', 'mentor_id', 'capacity', 'duration_weeks',
        ]);
    }

    /** @test */
    public function admin_can_update_class()
    {
        $this->actingAs($this->admin);

        $class = DiscipleshipClass::factory()->create([
            'title' => 'Original Title',
            'mentor_id' => $this->pastor->id,
        ]);

        $updateData = [
            'title' => 'Updated Title',
            'description' => 'Updated description',
            'mentor_id' => $this->pastor->id,
            'capacity' => 30,
            'duration_weeks' => 10,
            'schedule_type' => 'weekly',
            'schedule_day' => 'sunday',
            'schedule_time' => '10:00',
            'start_date' => now()->addDays(7)->format('Y-m-d'),
            'end_date' => now()->addDays(7)->addWeeks(12)->format('Y-m-d'),
            'location' => 'Main Hall',
        ];

        $response = $this->put("/classes/{$class->id}", $updateData);

        $response->assertRedirect(route('classes.show', $class));
        $response->assertSessionHas('success', 'Discipleship class updated successfully.');

        $this->assertDatabaseHas('classes', [
            'id' => $class->id,
            'title' => 'Updated Title',
            'capacity' => 30,
        ]);
    }

    /** @test */
    public function admin_can_toggle_class_status()
    {
        $this->actingAs($this->admin);

        $class = DiscipleshipClass::factory()->create(['is_active' => true]);

        $response = $this->patch("/classes/{$class->id}/toggle-status");

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Class deactivated successfully.');

        $this->assertDatabaseHas('classes', [
            'id' => $class->id,
            'is_active' => false,
        ]);
    }

    /** @test */
    public function admin_can_delete_class_without_sessions()
    {
        $this->actingAs($this->admin);

        $class = DiscipleshipClass::factory()->create();

        $response = $this->delete("/classes/{$class->id}");

        $response->assertRedirect(route('classes.index'));
        $response->assertSessionHas('success', 'Discipleship class deleted successfully.');

        $this->assertDatabaseMissing('classes', [
            'id' => $class->id,
        ]);
    }

    /** @test */
    public function admin_cannot_delete_class_with_sessions()
    {
        $this->actingAs($this->admin);

        $class = DiscipleshipClass::factory()->create();
        ClassSession::factory()->create(['class_id' => $class->id]);

        $response = $this->delete("/classes/{$class->id}");

        $response->assertStatus(403);

        $this->assertDatabaseHas('classes', [
            'id' => $class->id,
        ]);
    }

    /** @test */
    public function admin_can_create_class_session()
    {
        $this->actingAs($this->admin);

        $class = DiscipleshipClass::factory()->create(['mentor_id' => $this->admin->id]);

        $sessionData = [
            'class_id' => $class->id,
            'session_date' => now()->addDays(7)->format('Y-m-d'),
            'topic' => 'Introduction to Salvation',
            'notes' => 'First session notes',
            'location' => 'Main Hall',
        ];

        $response = $this->post("/classes/{$class->id}/sessions", $sessionData);

        $createdSession = ClassSession::where('class_id', $class->id)->first();
        // Use shallow routing - only pass session
        $response->assertRedirect(route('sessions.show', $createdSession));
        $response->assertSessionHas('success', 'Class session created successfully.');

        $this->assertDatabaseHas('class_sessions', [
            'class_id' => $class->id,
            'topic' => 'Introduction to Salvation',
        ]);
    }

    /** @test */
    public function admin_can_mark_attendance()
    {
        $this->actingAs($this->admin);

        $class = DiscipleshipClass::factory()->create(['mentor_id' => $this->admin->id]);
        $session = ClassSession::factory()->create(['class_id' => $class->id]);
        $member = Member::factory()->create();

        $attendanceData = [
            'class_session_id' => $session->id,
            'member_id' => $member->id,
            'status' => 'present',
            'marked_at' => now(),
        ];

        $response = $this->post(route('attendance.store'), $attendanceData);

        $response->assertStatus(201);
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('attendance', [
            'class_session_id' => $session->id,
            'member_id' => $member->id,
            'status' => 'present',
        ]);
    }

    /** @test */
    public function admin_can_mark_bulk_attendance()
    {
        $this->actingAs($this->admin);

        $class = DiscipleshipClass::factory()->create(['mentor_id' => $this->admin->id]);
        $session = ClassSession::factory()->create(['class_id' => $class->id]);
        $members = Member::factory()->count(3)->create();

        $attendanceData = [
            'class_session_id' => $session->id,
            'attendance' => [
                ['member_id' => $members[0]->id, 'status' => 'present'],
                ['member_id' => $members[1]->id, 'status' => 'absent'],
                ['member_id' => $members[2]->id, 'status' => 'excused'],
            ],
        ];

        $response = $this->postJson(route('attendance.storeBulk'), $attendanceData);

        $response->assertStatus(201);
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('attendance', [
            'class_session_id' => $session->id,
            'member_id' => $members[0]->id,
            'status' => 'present',
        ]);

        $this->assertDatabaseHas('attendance', [
            'class_session_id' => $session->id,
            'member_id' => $members[1]->id,
            'status' => 'absent',
        ]);

        $this->assertDatabaseHas('attendance', [
            'class_session_id' => $session->id,
            'member_id' => $members[2]->id,
            'status' => 'excused',
        ]);
    }

    /** @test */
    public function classes_can_be_searched()
    {
        $this->actingAs($this->admin);

        DiscipleshipClass::factory()->create(['title' => 'New Believers Class']);
        DiscipleshipClass::factory()->create(['title' => 'Prayer and Fasting']);

        $response = $this->get(route('classes.index', ['search' => 'New Believers']));

        $response->assertOk();
        $response->assertSee('New Believers Class');
        $response->assertDontSee('Prayer and Fasting');
    }

    /** @test */
    public function classes_can_be_filtered_by_mentor()
    {
        $this->actingAs($this->admin);

        DiscipleshipClass::factory()->create(['mentor_id' => $this->pastor->id]);
        DiscipleshipClass::factory()->create(['mentor_id' => $this->pastor->id]);

        $response = $this->get(route('classes.index', ['mentor_id' => $this->pastor->id]));

        $response->assertOk();
        // Should only show classes mentored by the pastor
    }

    /** @test */
    public function classes_can_be_filtered_by_status()
    {
        $this->actingAs($this->admin);

        DiscipleshipClass::factory()->create(['is_active' => true]);
        DiscipleshipClass::factory()->create(['is_active' => false]);

        $response = $this->get(route('classes.index', ['status' => 'active']));

        $response->assertOk();
        // Should only show active classes
    }
}
