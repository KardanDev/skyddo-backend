<?php

namespace App\Policies;

use App\Models\Insurer;
use App\Models\User;

class InsurerPolicy
{
    public function viewAny(User $user): bool
    {
        return true; // All authenticated users can list insurers
    }

    public function view(User $user, Insurer $insurer): bool
    {
        return true; // All authenticated users can view insurers
    }

    public function create(User $user): bool
    {
        return $user->isAtLeast(User::ROLE_ADMIN); // Only admins and super users
    }

    public function update(User $user, Insurer $insurer): bool
    {
        return $user->isAtLeast(User::ROLE_ADMIN);
    }

    public function delete(User $user, Insurer $insurer): bool
    {
        return $user->isAtLeast(User::ROLE_ADMIN);
    }

    public function restore(User $user, Insurer $insurer): bool
    {
        return $user->isAtLeast(User::ROLE_ADMIN);
    }

    public function forceDelete(User $user, Insurer $insurer): bool
    {
        return $user->isSuperUser();
    }
}
