<?php

namespace App\Policies;

use App\Models\Quote;
use App\Models\User;

class QuotePolicy
{
    public function viewAny(User $user): bool
    {
        return true; // All authenticated users (scoped by client access in controller)
    }

    public function view(User $user, Quote $quote): bool
    {
        if ($user->isAtLeast(User::ROLE_ADMIN)) {
            return true;
        }

        // Members can view quotes for clients they're assigned to
        return $quote->client->users()->where('user_id', $user->id)->exists();
    }

    public function create(User $user): bool
    {
        return $user->isAtLeast(User::ROLE_ADMIN); // Members typically can't create quotes
    }

    public function update(User $user, Quote $quote): bool
    {
        if ($user->isAtLeast(User::ROLE_ADMIN)) {
            return true;
        }

        // Members can update quotes for clients they manage
        $pivot = $quote->client->users()->where('user_id', $user->id)->first()?->pivot;

        return $pivot && in_array($pivot->role, ['owner', 'manager']);
    }

    public function delete(User $user, Quote $quote): bool
    {
        return $user->isAtLeast(User::ROLE_ADMIN);
    }

    public function restore(User $user, Quote $quote): bool
    {
        return $user->isAtLeast(User::ROLE_ADMIN);
    }

    public function forceDelete(User $user, Quote $quote): bool
    {
        return $user->isSuperUser();
    }
}
