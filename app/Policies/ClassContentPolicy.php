<?php

namespace App\Policies;

use App\Models\ClassContent;
use App\Models\User;

class ClassContentPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->canManageClasses();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ClassContent $classContent): bool
    {
        // Admins and pastors can view all content
        if ($user->canManageClasses()) {
            return true;
        }

        // Members can view if enrolled and content is published
        if ($user->isMember()) {
            $member = \App\Models\Member::where('user_id', $user->id)->first();
            if ($member) {
                $enrollment = $member->enrollments()
                    ->where('class_id', $classContent->class_id)
                    ->where('status', 'approved')
                    ->first();
                return $enrollment && $classContent->is_published;
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
    public function update(User $user, ClassContent $classContent): bool
    {
        return $user->canManageClasses();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ClassContent $classContent): bool
    {
        return $user->canManageClasses();
    }
}
