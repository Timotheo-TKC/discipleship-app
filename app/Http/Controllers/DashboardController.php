<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\ClassSession;
use App\Models\DiscipleshipClass;
use App\Models\Member;
use App\Models\Mentorship;
use App\Models\Message;
use App\Models\User;
use App\Services\BibleVerseService;
use App\Services\ReportService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    protected ReportService $reportService;

    public function __construct(ReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    /**
     * Display the dashboard
     */
    public function index(Request $request)
    {
        // All authenticated users can view dashboard
        // Different data will be shown based on user role

        $data = $this->getDashboardData();
        
        // Add analytics data
        $data['analytics'] = $this->getAnalyticsData();

        return view('dashboard', $data);
    }

    /**
     * Get dashboard statistics
     */
    private function getDashboardData(): array
    {
        $user = auth()->user();

        // Base statistics
        $totalMembers = Member::count();
        $totalClasses = DiscipleshipClass::count();
        $totalSessions = ClassSession::count();
        $totalAttendance = Attendance::count();

        // Recent activity (configurable days)
        $recentActivityDays = config('analytics.recent_activity_days', 30);
        $recentMembers = Member::where('created_at', '>=', Carbon::now()->subDays($recentActivityDays))->count();
        $recentSessions = ClassSession::where('session_date', '>=', Carbon::now()->subDays($recentActivityDays))->count();

        // Today's sessions
        $todaySessions = ClassSession::where('session_date', Carbon::today())->count();
        $todayAttendance = Attendance::whereHas('classSession', function ($query) {
            $query->where('session_date', Carbon::today());
        })->count();

        // Active mentorships
        $activeMentorships = Mentorship::where('status', 'active')->count();

        // Attendance rate calculation
        $attendanceRate = $this->calculateOverallAttendanceRate();

        // Recent messages (configurable days)
        $recentMessagesDays = config('analytics.recent_messages_days', 7);
        $recentMessages = Message::where('created_at', '>=', Carbon::now()->subDays($recentMessagesDays))
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // Role-specific data
        $roleSpecificData = $this->getRoleSpecificData($user);

        // Get daily Bible verse
        $bibleVerseService = new BibleVerseService();
        $dailyVerse = $bibleVerseService->getDailyVerse();

        return [
            'totalMembers' => $totalMembers,
            'totalClasses' => $totalClasses,
            'totalSessions' => $totalSessions,
            'totalAttendance' => $totalAttendance,
            'recentMembers' => $recentMembers,
            'recentSessions' => $recentSessions,
            'todaySessions' => $todaySessions,
            'todayAttendance' => $todayAttendance,
            'activeMentorships' => $activeMentorships,
            'attendanceRate' => $attendanceRate,
            'recentMessages' => $recentMessages,
            'user' => $user,
            'dailyVerse' => $dailyVerse,
        ] + $roleSpecificData;
    }

    /**
     * Calculate overall attendance rate
     */
    private function calculateOverallAttendanceRate(): float
    {
        $totalSessions = ClassSession::count();
        if ($totalSessions === 0) {
            return 0.0;
        }

        $totalPossibleAttendance = $totalSessions * Member::count();
        if ($totalPossibleAttendance === 0) {
            return 0.0;
        }

        $actualAttendance = Attendance::count();

        return round(($actualAttendance / $totalPossibleAttendance) * 100, 2);
    }

    /**
     * Get role-specific dashboard data
     */
    private function getRoleSpecificData(User $user): array
    {
        return match ($user->role) {
            'admin' => $this->getAdminData(),
            'pastor', 'mentor' => $this->getPastorData($user),
            default => [],
        };
    }

    /**
     * Get admin-specific data
     */
    private function getAdminData(): array
    {
        $userStats = User::selectRaw('role, COUNT(*) as count')
            ->groupBy('role')
            ->get()
            ->pluck('count', 'role')
            ->toArray();
        
        return [
            'userStats' => [
                'admin' => $userStats['admin'] ?? 0,
                'pastor' => $userStats['pastor'] ?? 0,
                'mentor' => $userStats['mentor'] ?? 0,
                'member' => $userStats['member'] ?? 0,
            ],
        ];
    }

    /**
     * Get pastor-specific data
     */
    private function getPastorData(?User $user = null): array
    {
        $user = $user ?? auth()->user();

        return [
            'myClasses' => DiscipleshipClass::byMentor($user->id)->count(),
            'mySessions' => ClassSession::whereHas('class', function ($query) use ($user) {
                $query->where('mentor_id', $user->id);
            })->count(),
            'myMembers' => Member::whereHas('attendance.classSession.class', function ($query) use ($user) {
                $query->where('mentor_id', $user->id);
            })->distinct()->count(),
        ];
    }


    /**
     * API endpoint for dashboard summary (for mobile apps)
     */
    public function summary(Request $request)
    {
        $data = $this->getDashboardData();

        return response()->json([
            'success' => true,
            'data' => [
                'overview' => [
                    'total_members' => $data['totalMembers'],
                    'total_classes' => $data['totalClasses'],
                    'total_sessions' => $data['totalSessions'],
                    'total_attendance' => $data['totalAttendance'],
                    'attendance_rate' => $data['attendanceRate'],
                ],
                'recent_activity' => [
                    'recent_members' => $data['recentMembers'],
                    'recent_sessions' => $data['recentSessions'],
                    'today_sessions' => $data['todaySessions'],
                    'active_mentorships' => $data['activeMentorships'],
                ],
                'role_specific' => $this->getRoleSpecificData($data['user']),
                'daily_verse' => $data['dailyVerse'] ?? null,
            ],
        ]);
    }

    /**
     * Get analytics data for dashboard
     */
    private function getAnalyticsData(): array
    {
        $attendanceTrends = $this->reportService->getAttendanceTrends(
            Carbon::now()->subMonths(config('analytics.dashboard_attendance_trends_months', 3)),
            Carbon::now()
        );

        return [
            'member_engagement' => $this->reportService->getMemberEngagement(),
            'class_performance' => $this->reportService->getClassPerformance(),
            'mentorship_success' => $this->reportService->getMentorshipSuccess(),
            'attendance_trends' => $attendanceTrends,
            'charts' => $this->getChartData($attendanceTrends),
        ];
    }

    /**
     * Get chart data for visualizations
     */
    private function getChartData(array $attendanceTrends): array
    {
        // Member growth over time (last 12 months) - SQLite compatible
        $memberGrowth = Member::where('created_at', '>=', Carbon::now()->subMonths(12))
            ->get()
            ->groupBy(function ($member) {
                return $member->created_at->format('Y-m');
            })
            ->map(function ($members, $month) {
                return [
                    'month' => $month,
                    'count' => $members->count(),
                ];
            })
            ->sortKeys()
            ->values();

        // Attendance by month (last 12 months) - SQLite compatible
        $attendanceByMonth = Attendance::whereHas('classSession', function ($query) {
            $query->where('session_date', '>=', Carbon::now()->subMonths(12));
        })
            ->with('classSession')
            ->get()
            ->groupBy(function ($attendance) {
                return Carbon::parse($attendance->classSession->session_date)->format('Y-m');
            })
            ->map(function ($attendances, $month) {
                return [
                    'month' => $month,
                    'count' => $attendances->count(),
                ];
            })
            ->sortKeys()
            ->values();

        // Class performance by class
        $classPerformance = DiscipleshipClass::withCount(['sessions', 'enrollments'])
            ->with(['sessions' => function ($query) {
                $query->withCount('attendance');
            }])
            ->get()
            ->map(function ($class) {
                $totalPossible = $class->sessions_count * $class->enrollments_count;
                $attendanceRate = $totalPossible > 0 
                    ? round(($class->sessions->sum('attendance_count') / $totalPossible) * 100, 1)
                    : 0;

                return [
                    'title' => $class->title,
                    'sessions' => $class->sessions_count,
                    'enrollments' => $class->enrollments_count,
                    'attendance_rate' => $attendanceRate,
                ];
            })
            ->sortByDesc('attendance_rate')
            ->take(10)
            ->values();

        // Messages sent over time (last 6 months) - SQLite compatible
        $messagesOverTime = Message::where('created_at', '>=', Carbon::now()->subMonths(6))
            ->get()
            ->groupBy(function ($message) {
                return $message->created_at->format('Y-m');
            })
            ->map(function ($messages, $month) {
                return [
                    'month' => $month,
                    'count' => $messages->count(),
                ];
            })
            ->sortKeys()
            ->values();

        // Mentorship status distribution
        $mentorshipStatus = Mentorship::selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->get()
            ->map(function ($item) {
                return [
                    'status' => ucfirst($item->status),
                    'count' => (int) $item->count,
                ];
            });

        return [
            'member_growth' => $memberGrowth,
            'attendance_by_month' => $attendanceByMonth,
            'class_performance' => $classPerformance,
            'messages_over_time' => $messagesOverTime,
            'mentorship_status' => $mentorshipStatus,
            'attendance_daily' => $attendanceTrends['trends'] ?? collect(),
        ];
    }
}
