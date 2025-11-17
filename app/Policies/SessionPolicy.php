<?php

namespace App\Policies;

use App\Models\ClassSession;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class SessionPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // All authenticated users can view sessions list (for their enrolled classes)
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ClassSession $session): bool
    {
        // Admins and pastors can always view
        if ($user->canManageClasses() || $user->id === $session->class->mentor_id) {
            return true;
        }
        
        // Members can view if they are enrolled in the class
        if ($user->isMember()) {
            $member = \App\Models\Member::where('user_id', $user->id)->first();
            if ($member) {
                return $member->enrollments()
                    ->where('class_id', $session->class_id)
                    ->where('status', 'approved')
                    ->exists();
            }
        }
        
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->canManageClasses();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ClassSession $session): bool
    {
        return $user->canManageClasses() || $user->id === $session->class->mentor_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ClassSession $session): bool
    {
        return $user->canManageClasses() || $user->id === $session->class->mentor_id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, ClassSession $session): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, ClassSession $session): bool
    {
        return $user->isAdmin();
    }
}
