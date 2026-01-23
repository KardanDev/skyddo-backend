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
        return $user->isAdmin() || $user->isSuperUser();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, User $model): bool
    {
        return $user->id === $model->id || $user->isAdmin() || $user->isSuperUser();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, User $model): bool
    {
        // Users can update themselves, admins can update non-super users
        if ($user->id === $model->id) {
            return true;
        }

        if ($user->isSuperUser()) {
            return true;
        }

        if ($user->isAdmin() && ! $model->isSuperUser()) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, User $model): bool
    {
        // Can't delete yourself
        if ($user->id === $model->id) {
            return false;
        }

        // Super users can delete anyone
        if ($user->isSuperUser()) {
            return true;
        }

        // Admins can delete members only
        if ($user->isAdmin() && $model->isMember()) {
            return true;
        }

        return false;
    }
}
