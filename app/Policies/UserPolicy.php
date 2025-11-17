<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->canManageUsers();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, User $model): bool
    {
        return $user->canManageUsers() || $user->id === $model->id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->canManageUsers();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, User $model): bool
    {
        // Users can update their own profile
        if ($user->id === $model->id) {
            return true;
        }

        // Admins can update anyone
        if ($user->isAdmin()) {
            return true;
        }

        // Pastors can update members, but not other pastors or admins
        if ($user->isPastor() || $user->isMentor()) {
            return $model->hasAnyRole([User::ROLE_MEMBER]);
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, User $model): bool
    {
        // Users cannot delete themselves
        if ($user->id === $model->id) {
            return false;
        }

        // Only admins can delete users
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, User $model): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, User $model): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can update the user's role.
     */
    public function updateRole(User $user, User $model): bool
    {
        // Users can update their own role (for role requests/updates)
        if ($user->id === $model->id) {
            return true;
        }

        // Only admins can change other users' roles
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can view the user's profile.
     */
    public function viewProfile(User $user, User $model): bool
    {
        return $this->view($user, $model);
    }
}
