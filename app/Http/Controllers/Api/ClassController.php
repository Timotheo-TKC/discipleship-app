<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ClassRequest;
use App\Models\DiscipleshipClass;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ClassController extends Controller
{
    public function __construct()
    {
        // Authorization is handled by policies in individual methods
    }

    /**
     * Display a listing of classes
     */
    public function index(Request $request)
    {
        Gate::authorize('viewAny', DiscipleshipClass::class);

        $query = DiscipleshipClass::with(['mentor', 'sessions']);

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('mentor', function ($mentorQuery) use ($search) {
                      $mentorQuery->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Filter by mentor
        if ($request->filled('mentor_id')) {
            $query->where('mentor_id', $request->get('mentor_id'));
        }

        // Filter by status
        if ($request->filled('status')) {
            $status = $request->get('status');
            if ($status === 'active') {
                $query->where('is_active', true)
                      ->where(function ($q) {
                          $q->whereNull('end_date')
                            ->orWhere('end_date', '>=', now());
                      });
            } elseif ($status === 'completed') {
                $query->where('is_active', false)
                      ->orWhere('end_date', '<', now());
            }
        }

        // Filter by schedule type
        if ($request->filled('schedule_type')) {
            $query->where('schedule_type', $request->get('schedule_type'));
        }

        $perPage = $request->get('per_page', 15);
        $classes = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $classes,
        ]);
    }

    /**
     * Store a newly created class
     */
    public function store(ClassRequest $request)
    {
        Gate::authorize('create', DiscipleshipClass::class);

        $class = DiscipleshipClass::create($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Class created successfully',
            'data' => $class->load('mentor'),
        ], 201);
    }

    /**
     * Display the specified class
     */
    public function show(DiscipleshipClass $class)
    {
        Gate::authorize('view', $class);

        $class->load([
            'mentor',
            'sessions' => function ($query) {
                $query->orderBy('session_date', 'desc');
            },
            'sessions.attendance.member',
        ]);

        // Get class statistics
        $stats = [
            'total_sessions' => $class->sessions()->count(),
            'upcoming_sessions' => $class->sessions()
                ->where('session_date', '>=', now())
                ->count(),
            'total_attendance' => $class->sessions()
                ->withCount('attendance')
                ->get()
                ->sum('attendance_count'),
            'average_attendance' => $this->calculateAverageAttendance($class),
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'class' => $class,
                'statistics' => $stats,
            ],
        ]);
    }

    /**
     * Update the specified class
     */
    public function update(ClassRequest $request, DiscipleshipClass $class)
    {
        Gate::authorize('update', $class);

        $class->update($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Class updated successfully',
            'data' => $class->load('mentor'),
        ]);
    }

    /**
     * Remove the specified class
     */
    public function destroy(DiscipleshipClass $class)
    {
        Gate::authorize('delete', $class);

        // Check if class has sessions
        if ($class->sessions()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete class with existing sessions. Please delete all sessions first.',
            ], 422);
        }

        $class->delete();

        return response()->json([
            'success' => true,
            'message' => 'Class deleted successfully',
        ]);
    }

    /**
     * Toggle class active status
     */
    public function toggleStatus(DiscipleshipClass $class)
    {
        Gate::authorize('update', $class);

        $class->update(['is_active' => ! $class->is_active]);

        $status = $class->is_active ? 'activated' : 'deactivated';

        return response()->json([
            'success' => true,
            'message' => "Class {$status} successfully",
            'data' => $class,
        ]);
    }

    /**
     * Get class sessions
     */
    public function sessions(DiscipleshipClass $class, Request $request)
    {
        Gate::authorize('view', $class);

        $query = $class->sessions()->with(['attendance.member']);

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->where('session_date', '>=', $request->get('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->where('session_date', '<=', $request->get('date_to'));
        }

        $perPage = $request->get('per_page', 20);
        $sessions = $query->orderBy('session_date', 'desc')->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $sessions,
        ]);
    }

    /**
     * Get class statistics
     */
    public function statistics(DiscipleshipClass $class)
    {
        Gate::authorize('view', $class);

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
        $attendanceByStatus = \App\Models\Attendance::whereHas('classSession', function ($query) use ($class) {
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
     * Get available mentors
     */
    public function mentors()
    {
        $mentors = User::whereIn('role', ['admin', 'pastor', 'mentor'])
            ->orderBy('name')
            ->get(['id', 'name', 'role']);

        return response()->json([
            'success' => true,
            'data' => $mentors,
        ]);
    }

    /**
     * Calculate average attendance for a class
     */
    private function calculateAverageAttendance(DiscipleshipClass $class): float
    {
        $sessions = $class->sessions()->withCount('attendance')->get();

        if ($sessions->isEmpty()) {
            return 0;
        }

        $totalAttendance = $sessions->sum('attendance_count');
        $totalSessions = $sessions->count();

        return round($totalAttendance / $totalSessions, 2);
    }
}
