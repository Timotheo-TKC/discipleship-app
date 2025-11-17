<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\ClassSession;
use App\Models\DiscipleshipClass;
use App\Models\Member;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $this->pastor = User::factory()->create(['role' => User::ROLE_PASTOR]);
    }

    /** @test */
    public function admin_can_mark_individual_attendance()
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

        $response = $this->postJson(route('attendance.store'), $attendanceData);

        $response->assertStatus(201);
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('attendance', [
            'class_session_id' => $session->id,
            'member_id' => $member->id,
            'status' => 'present',
            'marked_by' => $this->admin->id,
        ]);
    }

    /** @test */
    public function admin_can_mark_bulk_attendance()
    {
        $this->actingAs($this->admin);

        $class = DiscipleshipClass::factory()->create(['mentor_id' => $this->admin->id]);
        $session = ClassSession::factory()->create(['class_id' => $class->id]);
        $members = Member::factory()->count(5)->create();

        $attendanceData = [
            'class_session_id' => $session->id,
            'attendance' => [
                ['member_id' => $members[0]->id, 'status' => 'present'],
                ['member_id' => $members[1]->id, 'status' => 'present'],
                ['member_id' => $members[2]->id, 'status' => 'absent'],
                ['member_id' => $members[3]->id, 'status' => 'excused'],
                ['member_id' => $members[4]->id, 'status' => 'present'],
            ],
        ];

        $response = $this->postJson(route('attendance.storeBulk'), $attendanceData);

        $response->assertStatus(201);
        $response->assertJson(['success' => true]);

        // Verify all attendance records were created
        foreach ($members as $index => $member) {
            $expectedStatus = $attendanceData['attendance'][$index]['status'];
            $this->assertDatabaseHas('attendance', [
                'class_session_id' => $session->id,
                'member_id' => $member->id,
                'status' => $expectedStatus,
                'marked_by' => $this->admin->id,
            ]);
        }
    }

    /** @test */
    public function attendance_requires_valid_status()
    {
        $this->actingAs($this->admin);

        $class = DiscipleshipClass::factory()->create(['mentor_id' => $this->admin->id]);
        $session = ClassSession::factory()->create(['class_id' => $class->id]);
        $member = Member::factory()->create();

        $attendanceData = [
            'class_session_id' => $session->id,
            'member_id' => $member->id,
            'status' => 'invalid_status', // Invalid status
            'marked_at' => now(),
        ];

        $response = $this->postJson(route('attendance.store'), $attendanceData);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['status']);
    }

    /** @test */
    public function attendance_can_be_updated()
    {
        $this->actingAs($this->admin);

        $class = DiscipleshipClass::factory()->create(['mentor_id' => $this->admin->id]);
        $session = ClassSession::factory()->create(['class_id' => $class->id]);
        $member = Member::factory()->create();

        $attendance = Attendance::factory()->create([
            'class_session_id' => $session->id,
            'member_id' => $member->id,
            'status' => 'present',
            'marked_by' => $this->admin->id,
        ]);

        $updateData = [
            'class_session_id' => $session->id,
            'member_id' => $member->id,
            'status' => 'absent',
            'marked_at' => now(),
        ];

        $response = $this->put(route('attendance.update', $attendance), $updateData);

        $response->assertOk();
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('attendance', [
            'id' => $attendance->id,
            'status' => 'absent',
        ]);
    }

    /** @test */
    public function attendance_can_be_deleted()
    {
        $this->actingAs($this->admin);

        $class = DiscipleshipClass::factory()->create(['mentor_id' => $this->admin->id]);
        $session = ClassSession::factory()->create(['class_id' => $class->id]);
        $member = Member::factory()->create();

        $attendance = Attendance::factory()->create([
            'class_session_id' => $session->id,
            'member_id' => $member->id,
            'marked_by' => $this->admin->id,
        ]);

        $response = $this->delete(route('attendance.destroy', $attendance));

        $response->assertOk();
        $response->assertJson(['success' => true]);

        $this->assertDatabaseMissing('attendance', [
            'id' => $attendance->id,
        ]);
    }

    /** @test */
    public function member_attendance_statistics_can_be_retrieved()
    {
        $this->actingAs($this->admin);

        $member = Member::factory()->create();
        $class = DiscipleshipClass::factory()->create(['mentor_id' => $this->admin->id]);
        $sessions = ClassSession::factory()->count(10)->create(['class_id' => $class->id]);

        // Create attendance records
        foreach ($sessions as $index => $session) {
            $status = $index < 7 ? 'present' : ($index < 9 ? 'absent' : 'excused');
            Attendance::factory()->create([
                'class_session_id' => $session->id,
                'member_id' => $member->id,
                'status' => $status,
                'marked_by' => $this->admin->id,
            ]);
        }

        $response = $this->get(route('attendance.memberStats', $member));

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'data' => [
                'total_sessions' => 10,
                'present_count' => 7,
                'absent_count' => 2,
                'excused_count' => 1,
                'attendance_rate' => 70.0,
            ],
        ]);
    }

    /** @test */
    public function class_attendance_statistics_can_be_retrieved()
    {
        $this->actingAs($this->admin);

        $class = DiscipleshipClass::factory()->create(['mentor_id' => $this->admin->id]);
        $session = ClassSession::factory()->create(['class_id' => $class->id]);
        $members = Member::factory()->count(5)->create();

        // Create attendance records
        foreach ($members as $index => $member) {
            $status = $index < 3 ? 'present' : 'absent';
            Attendance::factory()->create([
                'class_session_id' => $session->id,
                'member_id' => $member->id,
                'status' => $status,
                'marked_by' => $this->admin->id,
            ]);
        }

        $response = $this->get(route('attendance.classStats', $class));

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'data' => [
                'total_sessions' => 1,
                'total_attendance' => 5,
                'average_attendance' => 5.0,
            ],
        ]);
    }

    /** @test */
    public function session_attendance_can_be_exported()
    {
        $this->actingAs($this->admin);

        $class = DiscipleshipClass::factory()->create(['mentor_id' => $this->admin->id]);
        $session = ClassSession::factory()->create(['class_id' => $class->id]);
        $members = Member::factory()->count(3)->create();

        // Create attendance records
        foreach ($members as $member) {
            Attendance::factory()->create([
                'class_session_id' => $session->id,
                'member_id' => $member->id,
                'status' => 'present',
                'marked_by' => $this->admin->id,
            ]);
        }

        $response = $this->get(route('attendance.exportSession', $session));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $response->assertHeader('Content-Disposition', 'attachment; filename="session_attendance_' . $session->id . '.csv"');
    }

    /** @test */
    public function duplicate_attendance_cannot_be_created()
    {
        $this->actingAs($this->admin);

        $class = DiscipleshipClass::factory()->create(['mentor_id' => $this->admin->id]);
        $session = ClassSession::factory()->create(['class_id' => $class->id]);
        $member = Member::factory()->create();

        // Create first attendance record
        Attendance::factory()->create([
            'class_session_id' => $session->id,
            'member_id' => $member->id,
            'marked_by' => $this->admin->id,
        ]);

        // Try to create duplicate
        $attendanceData = [
            'class_session_id' => $session->id,
            'member_id' => $member->id,
            'status' => 'present',
            'marked_at' => now(),
        ];

        $response = $this->postJson(route('attendance.store'), $attendanceData);

        // Should fail due to unique constraint
        $response->assertStatus(422);
    }

    // Coordinator role removed - tests removed
}
