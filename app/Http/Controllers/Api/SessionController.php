<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SessionRequest;
use App\Models\ClassSession;
use App\Models\DiscipleshipClass;
use Illuminate\Http\Request;

class SessionController extends Controller
{
    public function __construct()
    {
        // Authorization is handled by policies in individual methods
    }

    /**
     * Display a listing of sessions for a class
     */
    public function index(DiscipleshipClass $class, Request $request)
    {
        $this->authorize('view', $class);

        $query = $class->sessions()->with(['attendance.member']);

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->where('session_date', '>=', $request->get('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->where('session_date', '<=', $request->get('date_to'));
        }

        // Filter by status
        if ($request->filled('status')) {
            $status = $request->get('status');
            if ($status === 'upcoming') {
                $query->where('session_date', '>=', now());
            } elseif ($status === 'past') {
                $query->where('session_date', '<', now());
            } elseif ($status === 'today') {
                $query->whereDate('session_date', today());
            }
        }

        $perPage = $request->get('per_page', 20);
        $sessions = $query->orderBy('session_date', 'desc')->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $sessions,
        ]);
    }

    /**
     * Store a newly created session
     */
    public function store(SessionRequest $request, DiscipleshipClass $class)
    {
        $this->authorize('manageSessions', $class);

        $session = ClassSession::create([
            ...$request->validated(),
            'created_by' => $request->user()->id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Session created successfully',
            'data' => $session->load('class.mentor'),
        ], 201);
    }

    /**
     * Display the specified session
     */
    public function show(DiscipleshipClass $class, ClassSession $session)
    {
        $this->authorize('view', $class);

        $session->load([
            'class.mentor',
            'attendance.member',
            'createdBy',
        ]);

        // Get attendance statistics
        $attendanceStats = [
            'total_members' => $session->attendance()->count(),
            'present_count' => $session->attendance()->where('status', 'present')->count(),
            'absent_count' => $session->attendance()->where('status', 'absent')->count(),
            'excused_count' => $session->attendance()->where('status', 'excused')->count(),
        ];

        $attendanceStats['attendance_rate'] = $attendanceStats['total_members'] > 0
            ? round(($attendanceStats['present_count'] / $attendanceStats['total_members']) * 100, 2)
            : 0;

        return response()->json([
            'success' => true,
            'data' => [
                'session' => $session,
                'attendance_statistics' => $attendanceStats,
            ],
        ]);
    }

    /**
     * Update the specified session
     */
    public function update(SessionRequest $request, DiscipleshipClass $class, ClassSession $session)
    {
        $this->authorize('manageSessions', $class);

        $session->update($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Session updated successfully',
            'data' => $session->load('class.mentor'),
        ]);
    }

    /**
     * Remove the specified session
     */
    public function destroy(DiscipleshipClass $class, ClassSession $session)
    {
        $this->authorize('manageSessions', $class);

        // Check if session has attendance records
        if ($session->attendance()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete session with attendance records. Please delete attendance records first.',
            ], 422);
        }

        $session->delete();

        return response()->json([
            'success' => true,
            'message' => 'Session deleted successfully',
        ]);
    }

    /**
     * Get session attendance
     */
    public function attendance(DiscipleshipClass $class, ClassSession $session)
    {
        $this->authorize('viewAttendance', $class);

        $attendance = $session->attendance()
            ->with('member')
            ->orderBy('member.full_name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $attendance,
        ]);
    }

    /**
     * Get upcoming sessions for a class
     */
    public function upcoming(DiscipleshipClass $class)
    {
        $this->authorize('view', $class);

        $upcomingSessions = $class->sessions()
            ->where('session_date', '>=', now())
            ->orderBy('session_date')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $upcomingSessions,
        ]);
    }

    /**
     * Get session statistics for a class
     */
    public function statistics(DiscipleshipClass $class)
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
            'attendance_trend' => $this->calculateAttendanceTrend($sessions),
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'statistics' => $stats,
                'sessions' => $sessions,
            ],
        ]);
    }

    /**
     * Calculate attendance trend over time
     */
    private function calculateAttendanceTrend($sessions): array
    {
        $trend = [];

        foreach ($sessions as $session) {
            $trend[] = [
                'date' => $session->session_date->format('Y-m-d'),
                'attendance' => $session->attendance_count,
            ];
        }

        return $trend;
    }
}
