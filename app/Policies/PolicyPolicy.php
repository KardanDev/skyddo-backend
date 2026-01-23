<?php

namespace App\Policies;

use App\Models\Policy;
use App\Models\User;

class PolicyPolicy
{
    public function viewAny(User $user): bool
    {
        return true; // All authenticated users (scoped by client access in controller)
    }

    public function view(User $user, Policy $policy): bool
    {
        if ($user->isAtLeast(User::ROLE_ADMIN)) {
            return true;
        }

        // Members can view policies for clients they're assigned to
        return $policy->client->users()->where('user_id', $user->id)->exists();
    }

    public function create(User $user): bool
    {
        return $user->isAtLeast(User::ROLE_ADMIN);
    }

    public function update(User $user, Policy $policy): bool
    {
        if ($user->isAtLeast(User::ROLE_ADMIN)) {
            return true;
        }

        // Members can update policies for clients they manage
        $pivot = $policy->client->users()->where('user_id', $user->id)->first()?->pivot;

        return $pivot && in_array($pivot->role, ['owner', 'manager']);
    }

    public function delete(User $user, Policy $policy): bool
    {
        return $user->isAtLeast(User::ROLE_ADMIN);
    }

    public function restore(User $user, Policy $policy): bool
    {
        return $user->isAtLeast(User::ROLE_ADMIN);
    }

    public function forceDelete(User $user, Policy $policy): bool
    {
        return $user->isSuperUser();
    }
}
