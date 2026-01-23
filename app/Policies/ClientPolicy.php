<?php

namespace App\Policies;

use App\Models\Client;
use App\Models\User;

class ClientPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // All authenticated users can list clients (but scoped by access)
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Client $client): bool
    {
        // Super users and admins can view all clients
        if ($user->isSuperUser() || $user->isAdmin()) {
            return true;
        }

        // Members can only view clients they're assigned to
        return $client->users()->where('user_id', $user->id)->exists();
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Only admins and super users can create clients
        return $user->isAtLeast(User::ROLE_ADMIN);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Client $client): bool
    {
        // Super users and admins can update any client
        if ($user->isSuperUser() || $user->isAdmin()) {
            return true;
        }

        // Members can update clients they're assigned to with owner or manager role
        $pivot = $client->users()->where('user_id', $user->id)->first()?->pivot;

        return $pivot && in_array($pivot->role, ['owner', 'manager']);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Client $client): bool
    {
        // Only super users and admins can delete clients
        return $user->isAtLeast(User::ROLE_ADMIN);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Client $client): bool
    {
        // Only super users and admins can restore clients
        return $user->isAtLeast(User::ROLE_ADMIN);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Client $client): bool
    {
        // Only super users can force delete
        return $user->isSuperUser();
    }
}
