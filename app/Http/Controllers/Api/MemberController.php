<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\MemberRequest;
use App\Http\Resources\MemberResource;
use App\Models\Member;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class MemberController extends Controller
{
    public function __construct()
    {
        // Authorization is handled by policies in individual methods
    }

    /**
     * Display a listing of members
     */
    public function index(Request $request)
    {
        Gate::authorize('viewAny', Member::class);

        $query = Member::query();

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter by conversion date
        if ($request->filled('conversion_date_from')) {
            $query->where('date_of_conversion', '>=', $request->get('conversion_date_from'));
        }

        if ($request->filled('conversion_date_to')) {
            $query->where('date_of_conversion', '<=', $request->get('conversion_date_to'));
        }

        // Filter by preferred contact
        if ($request->filled('preferred_contact')) {
            $query->where('preferred_contact', $request->get('preferred_contact'));
        }

        $perPage = $request->get('per_page', 20);
        $members = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => [
                'data' => MemberResource::collection($members->items()),
                'current_page' => $members->currentPage(),
                'last_page' => $members->lastPage(),
                'per_page' => $members->perPage(),
                'total' => $members->total(),
            ],
        ]);
    }

    /**
     * Store a newly created member
     */
    public function store(MemberRequest $request)
    {
        Gate::authorize('create', Member::class);

        $member = Member::create($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Member created successfully',
            'data' => new MemberResource($member),
        ], 201);
    }

    /**
     * Display the specified member
     */
    public function show(Member $member)
    {
        Gate::authorize('view', $member);

        $member->load([
            'attendance.classSession.class',
            'mentorships.mentor',
        ]);

        // Get attendance statistics
        $attendanceStats = [
            'total_sessions' => $member->attendance()->count(),
            'present_count' => $member->attendance()->where('status', 'present')->count(),
            'absent_count' => $member->attendance()->where('status', 'absent')->count(),
            'excused_count' => $member->attendance()->where('status', 'excused')->count(),
        ];

        $attendanceStats['attendance_rate'] = $attendanceStats['total_sessions'] > 0
            ? round(($attendanceStats['present_count'] / $attendanceStats['total_sessions']) * 100, 2)
            : 0;

        return response()->json([
            'success' => true,
            'data' => [
                'member' => new MemberResource($member),
                'attendance_stats' => $attendanceStats,
            ],
        ]);
    }

    /**
     * Update the specified member
     */
    public function update(MemberRequest $request, Member $member)
    {
        Gate::authorize('update', $member);

        $member->update($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Member updated successfully',
            'data' => new MemberResource($member),
        ]);
    }

    /**
     * Remove the specified member
     */
    public function destroy(Member $member)
    {
        Gate::authorize('delete', $member);

        $member->delete();

        return response()->json([
            'success' => true,
            'message' => 'Member deleted successfully',
        ]);
    }

    /**
     * Get member attendance history
     */
    public function attendance(Member $member, Request $request)
    {
        Gate::authorize('view', $member);

        $query = $member->attendance()->with(['classSession.class']);

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereHas('classSession', function ($q) use ($request) {
                $q->where('session_date', '>=', $request->get('date_from'));
            });
        }

        if ($request->filled('date_to')) {
            $query->whereHas('classSession', function ($q) use ($request) {
                $q->where('session_date', '<=', $request->get('date_to'));
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        $perPage = $request->get('per_page', 20);
        $attendance = $query->orderBy('marked_at', 'desc')->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $attendance,
        ]);
    }

    /**
     * Get member mentorships
     */
    public function mentorships(Member $member)
    {
        Gate::authorize('view', $member);

        $mentorships = $member->mentorships()->with('mentor')->get();

        return response()->json([
            'success' => true,
            'data' => $mentorships,
        ]);
    }

    /**
     * Get member statistics
     */
    public function statistics(Member $member)
    {
        Gate::authorize('view', $member);

        $stats = [
            'total_sessions' => $member->attendance()->count(),
            'present_count' => $member->attendance()->where('status', 'present')->count(),
            'absent_count' => $member->attendance()->where('status', 'absent')->count(),
            'excused_count' => $member->attendance()->where('status', 'excused')->count(),
            'active_mentorships' => $member->mentorships()->where('status', 'active')->count(),
            'completed_mentorships' => $member->mentorships()->where('status', 'completed')->count(),
        ];

        $stats['attendance_rate'] = $stats['total_sessions'] > 0
            ? round(($stats['present_count'] / $stats['total_sessions']) * 100, 2)
            : 0;

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }
}
