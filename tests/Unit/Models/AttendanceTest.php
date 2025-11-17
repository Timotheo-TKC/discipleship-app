<?php

namespace Tests\Unit\Models;

use App\Models\Attendance;
use App\Models\ClassSession;
use App\Models\Member;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function attendance_has_status_helper_methods()
    {
        $attendance = Attendance::factory()->create(['status' => 'present']);

        $this->assertTrue($attendance->isPresent());
        $this->assertFalse($attendance->isAbsent());
        $this->assertFalse($attendance->isExcused());

        $attendance->update(['status' => 'absent']);

        $this->assertFalse($attendance->isPresent());
        $this->assertTrue($attendance->isAbsent());
        $this->assertFalse($attendance->isExcused());

        $attendance->update(['status' => 'excused']);

        $this->assertFalse($attendance->isPresent());
        $this->assertFalse($attendance->isAbsent());
        $this->assertTrue($attendance->isExcused());
    }

    /** @test */
    public function attendance_belongs_to_class_session()
    {
        $classSession = ClassSession::factory()->create();
        $attendance = Attendance::factory()->create(['class_session_id' => $classSession->id]);

        $this->assertInstanceOf(ClassSession::class, $attendance->classSession);
        $this->assertEquals($classSession->id, $attendance->classSession->id);
    }

    /** @test */
    public function attendance_belongs_to_member()
    {
        $member = Member::factory()->create();
        $attendance = Attendance::factory()->create(['member_id' => $member->id]);

        $this->assertInstanceOf(Member::class, $attendance->member);
        $this->assertEquals($member->id, $attendance->member->id);
    }

    /** @test */
    public function attendance_belongs_to_marked_by_user()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create(['marked_by' => $user->id]);

        $this->assertInstanceOf(User::class, $attendance->markedBy);
        $this->assertEquals($user->id, $attendance->markedBy->id);
    }

    /** @test */
    public function attendance_has_correct_fillable_attributes()
    {
        $attendance = new Attendance();

        $expectedFillable = [
            'class_session_id',
            'member_id',
            'status',
            'marked_by',
            'marked_at',
        ];

        $this->assertEquals($expectedFillable, $attendance->getFillable());
    }

    /** @test */
    public function attendance_has_correct_casts()
    {
        $attendance = Attendance::factory()->create(['marked_at' => now()]);

        $this->assertInstanceOf(\Carbon\Carbon::class, $attendance->marked_at);
    }

    /** @test */
    public function attendance_can_be_created_with_valid_data()
    {
        $classSession = ClassSession::factory()->create();
        $member = Member::factory()->create();
        $user = User::factory()->create();

        $attendanceData = [
            'class_session_id' => $classSession->id,
            'member_id' => $member->id,
            'status' => 'present',
            'marked_by' => $user->id,
            'marked_at' => now(),
        ];

        $attendance = Attendance::create($attendanceData);

        $this->assertDatabaseHas('attendance', [
            'class_session_id' => $classSession->id,
            'member_id' => $member->id,
            'status' => 'present',
            'marked_by' => $user->id,
        ]);

        $this->assertEquals('present', $attendance->status);
        $this->assertTrue($attendance->isPresent());
    }

    /** @test */
    public function attendance_can_be_updated()
    {
        $attendance = Attendance::factory()->create(['status' => 'present']);

        $attendance->update(['status' => 'absent']);

        $this->assertEquals('absent', $attendance->status);
        $this->assertTrue($attendance->isAbsent());
        $this->assertFalse($attendance->isPresent());
    }

    /** @test */
    public function attendance_can_be_deleted()
    {
        $attendance = Attendance::factory()->create();

        $attendanceId = $attendance->id;
        $attendance->delete();

        $this->assertDatabaseMissing('attendance', [
            'id' => $attendanceId,
        ]);
    }

    /** @test */
    public function attendance_has_unique_constraint_on_session_and_member()
    {
        $classSession = ClassSession::factory()->create();
        $member = Member::factory()->create();
        $user = User::factory()->create();

        // Create first attendance record
        Attendance::factory()->create([
            'class_session_id' => $classSession->id,
            'member_id' => $member->id,
            'marked_by' => $user->id,
        ]);

        // Try to create duplicate - should fail
        $this->expectException(\Illuminate\Database\QueryException::class);

        Attendance::factory()->create([
            'class_session_id' => $classSession->id,
            'member_id' => $member->id,
            'marked_by' => $user->id,
        ]);
    }

    /** @test */
    public function attendance_status_must_be_valid()
    {
        $this->expectException(\Illuminate\Database\QueryException::class);

        Attendance::factory()->create(['status' => 'invalid_status']);
    }

    /** @test */
    public function attendance_can_have_null_marked_by()
    {
        $attendance = Attendance::factory()->create(['marked_by' => null]);

        $this->assertNull($attendance->marked_by);
        $this->assertNull($attendance->markedBy);
    }

    /** @test */
    public function attendance_has_correct_table_name()
    {
        $attendance = new Attendance();

        $this->assertEquals('attendance', $attendance->getTable());
    }
}
