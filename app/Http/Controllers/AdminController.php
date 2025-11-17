<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\ClassSession;
use App\Models\DiscipleshipClass;
use App\Models\Member;
use App\Models\Mentorship;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            if (! auth()->user() || ! auth()->user()->isAdmin()) {
                abort(403, 'Access denied. Admin privileges required.');
            }

            return $next($request);
        });
    }

    /**
     * Show admin dashboard
     */
    public function index()
    {
        $stats = $this->getSystemStats();
        $recentActivity = $this->getRecentActivity();
        $systemHealth = $this->getSystemHealth();

        return view('admin.dashboard', compact('stats', 'recentActivity', 'systemHealth'));
    }

    /**
     * Show user management page
     */
    public function users(Request $request)
    {
        $query = User::query();

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter by role
        if ($request->filled('role')) {
            $query->where('role', $request->get('role'));
        }

        // Filter by email verification status
        if ($request->filled('email_verified')) {
            if ($request->get('email_verified') === 'verified') {
                $query->whereNotNull('email_verified_at');
            } else {
                $query->whereNull('email_verified_at');
            }
        }

        $perPage = $request->get('per_page', 20);
        $users = $query->orderBy('created_at', 'desc')->paginate($perPage);

        $roles = User::getRoles();

        return view('admin.users.index', compact('users', 'roles'));
    }

    /**
     * Show create user form
     */
    public function createUser(): \Illuminate\View\View
    {
        $this->authorize('create', User::class);

        $roles = [
            User::ROLE_PASTOR => 'Pastor',
            User::ROLE_MENTOR => 'Mentor',
        ];

        return view('admin.users.create', compact('roles'));
    }

    /**
     * Store a newly created pastor or mentor account
     */
    public function storeUser(Request $request)
    {
        $this->authorize('create', User::class);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:25', 'unique:users,phone'],
            'role' => ['required', Rule::in([User::ROLE_PASTOR, User::ROLE_MENTOR])],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'role' => $validated['role'],
            'password' => $validated['password'],
        ]);

        $user->forceFill(['email_verified_at' => now()])->save();

        return redirect()
            ->route('admin.users.show', $user)
            ->with('success', 'Account created successfully. Share the credentials below with the new user.')
            ->with('new_user_credentials', [
                'email' => $validated['email'],
                'password' => $validated['password'],
            ]);
    }

    /**
     * Show user details
     */
    public function showUser(User $user)
    {
        $user->load(['members', 'mentoredClasses', 'mentorships']);

        return view('admin.users.show', compact('user'));
    }

    /**
     * Show system health page
     */
    public function systemHealth()
    {
        $health = $this->getSystemHealth();
        $databaseStats = $this->getDatabaseStats();
        $performanceMetrics = $this->getPerformanceMetrics();

        return view('admin.system-health', compact('health', 'databaseStats', 'performanceMetrics'));
    }

    /**
     * Get system statistics
     */
    private function getSystemStats(): array
    {
        return [
            'total_users' => User::count(),
            'total_members' => Member::count(),
            'total_classes' => DiscipleshipClass::count(),
            'total_sessions' => ClassSession::count(),
            'total_attendance' => Attendance::count(),
            'total_mentorships' => Mentorship::count(),
            'active_classes' => DiscipleshipClass::where('is_active', true)->count(),
            'pending_email_verifications' => User::whereNull('email_verified_at')->count(),
        ];
    }

    /**
     * Get recent activity
     */
    private function getRecentActivity(): array
    {
        $recentUsers = User::orderBy('created_at', 'desc')->limit(5)->get();
        $recentMembers = Member::orderBy('created_at', 'desc')->limit(5)->get();
        $recentClasses = DiscipleshipClass::orderBy('created_at', 'desc')->limit(5)->get();

        return [
            'recent_users' => $recentUsers,
            'recent_members' => $recentMembers,
            'recent_classes' => $recentClasses,
        ];
    }

    /**
     * Get system health metrics
     */
    private function getSystemHealth(): array
    {
        $diskUsage = $this->getDiskUsage();
        $memoryUsage = $this->getMemoryUsage();

        return [
            'database_connection' => $this->checkDatabaseConnection(),
            'disk_usage' => $diskUsage,
            'memory_usage' => $memoryUsage,
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'environment' => app()->environment(),
            'debug_mode' => config('app.debug'),
            'cache_driver' => config('cache.default'),
            'queue_driver' => config('queue.default'),
        ];
    }

    /**
     * Get database statistics
     */
    private function getDatabaseStats(): array
    {
        try {
            $tables = [
                'users' => User::count(),
                'members' => Member::count(),
                'discipleship_classes' => DiscipleshipClass::count(),
                'class_sessions' => ClassSession::count(),
                'attendance' => Attendance::count(),
                'mentorships' => Mentorship::count(),
            ];

            $totalRecords = array_sum($tables);

            return [
                'tables' => $tables,
                'total_records' => $totalRecords,
                'database_size' => $this->getDatabaseSize(),
            ];
        } catch (\Exception $e) {
            return [
                'error' => 'Unable to retrieve database statistics: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Get performance metrics
     */
    private function getPerformanceMetrics(): array
    {
        return [
            'average_response_time' => $this->getAverageResponseTime(),
            'peak_memory_usage' => memory_get_peak_usage(true),
            'current_memory_usage' => memory_get_usage(true),
            'uptime' => $this->getSystemUptime(),
        ];
    }

    /**
     * Check database connection
     */
    private function checkDatabaseConnection(): bool
    {
        try {
            DB::connection()->getPdo();

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get disk usage
     */
    private function getDiskUsage(): array
    {
        $totalBytes = disk_total_space('/');
        $freeBytes = disk_free_space('/');
        $usedBytes = $totalBytes - $freeBytes;

        return [
            'total' => $this->formatBytes($totalBytes),
            'used' => $this->formatBytes($usedBytes),
            'free' => $this->formatBytes($freeBytes),
            'percentage' => round(($usedBytes / $totalBytes) * 100, 2),
        ];
    }

    /**
     * Get memory usage
     */
    private function getMemoryUsage(): array
    {
        $memoryLimit = ini_get('memory_limit');
        $memoryUsage = memory_get_usage(true);
        $memoryLimitBytes = $this->parseMemoryLimit($memoryLimit);

        return [
            'limit' => $memoryLimit,
            'used' => $this->formatBytes($memoryUsage),
            'percentage' => $memoryLimitBytes > 0 ? round(($memoryUsage / $memoryLimitBytes) * 100, 2) : 0,
        ];
    }

    /**
     * Get database size
     */
    private function getDatabaseSize(): string
    {
        try {
            $result = DB::select("
                SELECT 
                    ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS 'size_mb'
                FROM information_schema.tables 
                WHERE table_schema = DATABASE()
            ");

            return $result[0]->size_mb . ' MB';
        } catch (\Exception $e) {
            return 'Unable to determine';
        }
    }

    /**
     * Get average response time (simplified)
     */
    private function getAverageResponseTime(): string
    {
        // This is a simplified implementation
        // In a real application, you might use APM tools or custom logging
        return 'N/A';
    }

    /**
     * Get system uptime
     */
    private function getSystemUptime(): string
    {
        try {
            $uptime = shell_exec('uptime -p 2>/dev/null');

            return $uptime ? trim($uptime) : 'Unable to determine';
        } catch (\Exception $e) {
            return 'Unable to determine';
        }
    }

    /**
     * Format bytes to human readable format
     */
    private function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }

    /**
     * Parse memory limit string to bytes
     */
    private function parseMemoryLimit(string $memoryLimit): int
    {
        $memoryLimit = trim($memoryLimit);
        $last = strtolower($memoryLimit[strlen($memoryLimit) - 1]);
        $memoryLimit = (int) $memoryLimit;

        switch ($last) {
            case 'g':
                $memoryLimit *= 1024;
                // no break
            case 'm':
                $memoryLimit *= 1024;
                // no break
            case 'k':
                $memoryLimit *= 1024;
        }

        return $memoryLimit;
    }
}
