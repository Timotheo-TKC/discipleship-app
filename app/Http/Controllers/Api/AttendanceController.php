<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AttendanceRequest;
use App\Http\Requests\BulkAttendanceRequest;
use App\Models\Attendance;
use App\Models\ClassSession;
use App\Models\DiscipleshipClass;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AttendanceController extends Controller
{
    public function __construct()
    {
        // Authorization is handled by policies in individual methods
    }

    /**
     * Store a newly created attendance record
     */
    public function store(AttendanceRequest $request)
    {
        $session = ClassSession::findOrFail($request->class_session_id);
        $class = $session->class;

        $this->authorize('viewAttendance', $class);

        // Check if attendance record already exists
        $existingAttendance = Attendance::where('class_session_id', $request->class_session_id)
            ->where('member_id', $request->member_id)
            ->first();

        if ($existingAttendance) {
            return response()->json([
                'success' => false,
                'message' => 'Attendance record already exists for this member and session.',
            ], 422);
        }

        $attendance = Attendance::create([
            ...$request->validated(),
            'marked_by' => $request->user()->id,
            'marked_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Attendance recorded successfully',
            'data' => $attendance->load('member'),
        ], 201);
    }

    /**
     * Update the specified attendance record
     */
    public function update(AttendanceRequest $request, Attendance $attendance)
    {
        $class = $attendance->classSession->class;
        $this->authorize('viewAttendance', $class);

        $attendance->update([
            ...$request->validated(),
            'marked_by' => $request->user()->id,
            'marked_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Attendance updated successfully',
            'data' => $attendance->load('member'),
        ]);
    }

    /**
     * Remove the specified attendance record
     */
    public function destroy(Attendance $attendance)
    {
        $class = $attendance->classSession->class;
        $this->authorize('viewAttendance', $class);

        $attendance->delete();

        return response()->json([
            'success' => true,
            'message' => 'Attendance record deleted successfully',
        ]);
    }

    /**
     * Store bulk attendance records for a session
     */
    public function storeBulk(BulkAttendanceRequest $request)
    {
        $session = ClassSession::findOrFail($request->class_session_id);
        $class = $session->class;

        $this->authorize('viewAttendance', $class);

        $results = [
            'success' => 0,
            'failed' => 0,
            'errors' => [],
        ];

        DB::transaction(function () use ($request, $session, &$results) {
            foreach ($request->attendance as $index => $attendanceData) {
                try {
                    // Check if attendance record already exists
                    $existingAttendance = Attendance::where('class_session_id', $request->class_session_id)
                        ->where('member_id', $attendanceData['member_id'])
                        ->first();

                    if ($existingAttendance) {
                        // Update existing record
                        $existingAttendance->update([
                            'status' => $attendanceData['status'],
                            'notes' => $attendanceData['notes'] ?? null,
                            'marked_by' => $request->user()->id,
                            'marked_at' => now(),
                        ]);
                    } else {
                        // Create new record
                        Attendance::create([
                            'class_session_id' => $request->class_session_id,
                            'member_id' => $attendanceData['member_id'],
                            'status' => $attendanceData['status'],
                            'notes' => $attendanceData['notes'] ?? null,
                            'marked_by' => $request->user()->id,
                            'marked_at' => now(),
                        ]);
                    }

                    $results['success']++;

                } catch (\Exception $e) {
                    $results['failed']++;
                    $results['errors'][] = [
                        'index' => $index,
                        'member_id' => $attendanceData['member_id'],
                        'error' => $e->getMessage(),
                    ];
                }
            }
        });

        $message = "Bulk attendance completed. {$results['success']} records processed successfully.";

        if ($results['failed'] > 0) {
            $message .= " {$results['failed']} records failed.";
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $results,
        ]);
    }

    /**
     * Get attendance statistics for a member
     */
    public function memberStats(Request $request, $memberId)
    {
        $member = \App\Models\Member::findOrFail($memberId);

        $stats = [
            'total_sessions' => $member->attendance()->count(),
            'present_count' => $member->attendance()->where('status', 'present')->count(),
            'absent_count' => $member->attendance()->where('status', 'absent')->count(),
            'excused_count' => $member->attendance()->where('status', 'excused')->count(),
        ];

        $stats['attendance_rate'] = $stats['total_sessions'] > 0
            ? round(($stats['present_count'] / $stats['total_sessions']) * 100, 2)
            : 0;

        // Get recent attendance history
        $recentAttendance = $member->attendance()
            ->with(['classSession.class'])
            ->orderBy('marked_at', 'desc')
            ->take(10)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'statistics' => $stats,
                'recent_attendance' => $recentAttendance,
            ],
        ]);
    }

    /**
     * Get attendance statistics for a class
     */
    public function classStats(Request $request, DiscipleshipClass $class)
    {
        $this->authorize('view', $class);

        $sessions = $class->sessions()
            ->withCount('attendance')
            ->orderBy('session_date')
            ->get();

        $stats = [
            'total_sessions' => $sessions->count(),
            'total_attendance' => $sessions->sum('attendance_count'),
            'average_attendance' => $sessions->count() > 0 ? round($sessions->sum('attendance_count') / $sessions->count(), 2) : 0,
        ];

        // Get attendance by status
        $attendanceByStatus = Attendance::whereHas('classSession', function ($query) use ($class) {
            $query->where('class_id', $class->id);
        })
        ->selectRaw('status, COUNT(*) as count')
        ->groupBy('status')
        ->get()
        ->pluck('count', 'status');

        return response()->json([
            'success' => true,
            'data' => [
                'statistics' => $stats,
                'attendance_by_status' => $attendanceByStatus,
                'sessions' => $sessions,
            ],
        ]);
    }

    /**
     * Get attendance for a specific session
     */
    public function sessionAttendance(ClassSession $session)
    {
        $class = $session->class;
        $this->authorize('viewAttendance', $class);

        $attendance = $session->attendance()
            ->with('member')
            ->join('members', 'attendance.member_id', '=', 'members.id')
            ->orderBy('members.full_name')
            ->select('attendance.*')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $attendance,
        ]);
    }
}
