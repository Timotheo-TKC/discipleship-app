<?php

namespace Tests\Feature\Api;

use App\Models\Attendance;
use App\Models\ClassSession;
use App\Models\DiscipleshipClass;
use App\Models\Member;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $this->pastor = User::factory()->create(['role' => User::ROLE_PASTOR]);
    }

    /** @test */
    public function admin_can_mark_attendance_via_api()
    {
        $class = DiscipleshipClass::factory()->create(['mentor_id' => $this->admin->id]);
        $session = ClassSession::factory()->create(['class_id' => $class->id]);
        $member = Member::factory()->create();

        $token = $this->admin->createToken('test-token')->plainTextToken;

        $attendanceData = [
            'class_session_id' => $session->id,
            'member_id' => $member->id,
            'status' => 'present',
            'marked_at' => now()->toDateTimeString(),
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ])->postJson('/api/v1/attendance', $attendanceData);

        $response->assertStatus(201);
        $response->assertJson([
            'success' => true,
            'message' => 'Attendance recorded successfully',
        ]);

        $this->assertDatabaseHas('attendance', [
            'class_session_id' => $session->id,
            'member_id' => $member->id,
            'status' => 'present',
            'marked_by' => $this->admin->id,
        ]);
    }

    /** @test */
    public function admin_can_mark_bulk_attendance_via_api()
    {
        $class = DiscipleshipClass::factory()->create(['mentor_id' => $this->admin->id]);
        $session = ClassSession::factory()->create(['class_id' => $class->id]);
        $members = Member::factory()->count(3)->create();

        $token = $this->admin->createToken('test-token')->plainTextToken;

        $attendanceData = [
            'class_session_id' => $session->id,
            'attendance' => [
                ['member_id' => $members[0]->id, 'status' => 'present'],
                ['member_id' => $members[1]->id, 'status' => 'absent'],
                ['member_id' => $members[2]->id, 'status' => 'excused'],
            ],
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ])->postJson('/api/v1/attendance/bulk', $attendanceData);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'message' => 'Bulk attendance completed. 3 records processed successfully.',
        ]);

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
    public function attendance_requires_valid_status_via_api()
    {
        $class = DiscipleshipClass::factory()->create(['mentor_id' => $this->admin->id]);
        $session = ClassSession::factory()->create(['class_id' => $class->id]);
        $member = Member::factory()->create();

        $token = $this->admin->createToken('test-token')->plainTextToken;

        $attendanceData = [
            'class_session_id' => $session->id,
            'member_id' => $member->id,
            'status' => 'invalid_status',
            'marked_at' => now()->toDateTimeString(),
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ])->postJson('/api/v1/attendance', $attendanceData);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['status']);
    }

    /** @test */
    public function admin_can_update_attendance_via_api()
    {
        $class = DiscipleshipClass::factory()->create(['mentor_id' => $this->admin->id]);
        $session = ClassSession::factory()->create(['class_id' => $class->id]);
        $member = Member::factory()->create();

        $attendance = Attendance::factory()->create([
            'class_session_id' => $session->id,
            'member_id' => $member->id,
            'status' => 'present',
            'marked_by' => $this->admin->id,
        ]);

        $token = $this->admin->createToken('test-token')->plainTextToken;

        $updateData = [
            'class_session_id' => $session->id,
            'member_id' => $member->id,
            'status' => 'absent',
            'marked_at' => now()->toDateTimeString(),
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ])->putJson("/api/v1/attendance/{$attendance->id}", $updateData);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'message' => 'Attendance updated successfully',
        ]);

        $this->assertDatabaseHas('attendance', [
            'id' => $attendance->id,
            'status' => 'absent',
        ]);
    }

    /** @test */
    public function admin_can_delete_attendance_via_api()
    {
        $class = DiscipleshipClass::factory()->create(['mentor_id' => $this->admin->id]);
        $session = ClassSession::factory()->create(['class_id' => $class->id]);
        $member = Member::factory()->create();

        $attendance = Attendance::factory()->create([
            'class_session_id' => $session->id,
            'member_id' => $member->id,
            'marked_by' => $this->admin->id,
        ]);

        $token = $this->admin->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ])->deleteJson("/api/v1/attendance/{$attendance->id}");

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'message' => 'Attendance record deleted successfully',
        ]);

        $this->assertDatabaseMissing('attendance', [
            'id' => $attendance->id,
        ]);
    }

    /** @test */
    public function admin_can_get_member_attendance_stats_via_api()
    {
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

        $token = $this->admin->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ])->getJson("/api/v1/attendance/member/{$member->id}/stats");

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'data' => [
                'statistics' => [
                'total_sessions' => 10,
                'present_count' => 7,
                'absent_count' => 2,
                'excused_count' => 1,
                'attendance_rate' => 70.0,
                ]
            ]
        ]);
    }

    /** @test */
    public function admin_can_get_class_attendance_stats_via_api()
    {
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

        $token = $this->admin->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ])->getJson("/api/v1/attendance/class/{$class->id}/stats");

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'data' => [
                'statistics' => [
                'total_sessions' => 1,
                'total_attendance' => 5,
                'average_attendance' => 5.0,
                ]
            ]
        ]);
    }

    /** @test */
    public function admin_can_get_session_attendance_via_api()
    {
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

        $token = $this->admin->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ])->getJson("/api/v1/attendance/session/{$session->id}");

        $response->assertOk();
        $response->assertJsonStructure([
            'success',
            'data' => [
                '*' => [
                    'id',
                    'member_id',
                    'status',
                    'marked_at',
                ],
            ],
        ]);
    }

    // Coordinator role removed - tests removed

    /** @test */
    public function unauthenticated_user_cannot_access_attendance_endpoints()
    {
        $attendance = Attendance::factory()->create();

        $this->postJson('/api/v1/attendance', [])->assertStatus(401);
        $this->postJson('/api/v1/attendance/bulk', [])->assertStatus(401);
        $this->putJson("/api/v1/attendance/{$attendance->id}", [])->assertStatus(401);
        $this->deleteJson("/api/v1/attendance/{$attendance->id}")->assertStatus(401);
    }
}
