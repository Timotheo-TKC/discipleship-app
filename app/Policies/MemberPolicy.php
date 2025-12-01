<?php

namespace App\Policies;

use App\Models\Member;
use App\Models\User;

class MemberPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // Mentors can view members (read-only), admins and pastors can manage
        return $user->isAdmin() || $user->isPastor() || $user->isMentor();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Member $member): bool
    {
        // Mentors can view members (read-only), admins and pastors can manage
        return $user->isAdmin() || $user->isPastor() || $user->isMentor();
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Only admins and pastors can create members, not mentors
        return $user->isAdmin() || $user->isPastor();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Member $member): bool
    {
        // Only admins and pastors can update members, not mentors
        return $user->isAdmin() || $user->isPastor();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Member $member): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Member $member): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Member $member): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can import members.
     */
    public function import(User $user): bool
    {
        // Only admins and pastors can import members, not mentors
        return $user->isAdmin() || $user->isPastor();
    }

    /**
     * Determine whether the user can export members.
     */
    public function export(User $user): bool
    {
        // Mentors can export (read-only operation), admins and pastors can export
        return $user->isAdmin() || $user->isPastor() || $user->isMentor();
    }
}
