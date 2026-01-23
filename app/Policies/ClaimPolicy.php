<?php

namespace App\Policies;

use App\Models\Claim;
use App\Models\User;

class ClaimPolicy
{
    public function viewAny(User $user): bool
    {
        return true; // All authenticated users (scoped by client access in controller)
    }

    public function view(User $user, Claim $claim): bool
    {
        if ($user->isAtLeast(User::ROLE_ADMIN)) {
            return true;
        }

        // Members can view claims for clients they're assigned to
        return $claim->client->users()->where('user_id', $user->id)->exists();
    }

    public function create(User $user): bool
    {
        return true; // All users can create claims
    }

    public function update(User $user, Claim $claim): bool
    {
        if ($user->isAtLeast(User::ROLE_ADMIN)) {
            return true;
        }

        // Members can update claims for clients they manage
        $pivot = $claim->client->users()->where('user_id', $user->id)->first()?->pivot;

        return $pivot && in_array($pivot->role, ['owner', 'manager']);
    }

    public function delete(User $user, Claim $claim): bool
    {
        return $user->isAtLeast(User::ROLE_ADMIN);
    }

    public function restore(User $user, Claim $claim): bool
    {
        return $user->isAtLeast(User::ROLE_ADMIN);
    }

    public function forceDelete(User $user, Claim $claim): bool
    {
        return $user->isSuperUser();
    }
}
