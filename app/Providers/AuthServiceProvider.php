<?php

namespace App\Providers;

use App\Models\Attendance;
use App\Models\ClassContent;
use App\Models\ClassSession;
use App\Models\DiscipleshipClass;
use App\Models\Member;
use App\Models\Mentorship;
use App\Models\User;
use App\Policies\AttendancePolicy;
use App\Policies\ClassContentPolicy;
use App\Policies\ClassPolicy;
use App\Policies\MemberPolicy;
use App\Policies\MentorshipPolicy;
use App\Policies\SessionPolicy;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        User::class => UserPolicy::class,
        Member::class => MemberPolicy::class,
        DiscipleshipClass::class => ClassPolicy::class,
        ClassSession::class => SessionPolicy::class,
        ClassContent::class => ClassContentPolicy::class,
        Attendance::class => AttendancePolicy::class,
        Mentorship::class => MentorshipPolicy::class,
    ];

    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // Additional gates for common authorization checks
        Gate::define('manage-users', function (User $user) {
            return $user->canManageUsers();
        });

        Gate::define('manage-classes', function (User $user) {
            return $user->canManageClasses();
        });

        Gate::define('view-dashboard', function (User $user) {
            return $user->hasAnyRole([User::ROLE_ADMIN, User::ROLE_PASTOR, User::ROLE_MENTOR]);
        });

        Gate::define('manage-members', function (User $user) {
            return $user->hasAnyRole([User::ROLE_ADMIN, User::ROLE_PASTOR, User::ROLE_MENTOR]);
        });
    }
}
