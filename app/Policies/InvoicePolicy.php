<?php

namespace App\Policies;

use App\Models\Invoice;
use App\Models\User;

class InvoicePolicy
{
    public function viewAny(User $user): bool
    {
        return true; // All authenticated users (scoped by client access in controller)
    }

    public function view(User $user, Invoice $invoice): bool
    {
        if ($user->isAtLeast(User::ROLE_ADMIN)) {
            return true;
        }

        // Members can view invoices for clients they're assigned to
        return $invoice->client->users()->where('user_id', $user->id)->exists();
    }

    public function create(User $user): bool
    {
        return $user->isAtLeast(User::ROLE_ADMIN);
    }

    public function update(User $user, Invoice $invoice): bool
    {
        if ($user->isAtLeast(User::ROLE_ADMIN)) {
            return true;
        }

        // Members can update invoices for clients they manage
        $pivot = $invoice->client->users()->where('user_id', $user->id)->first()?->pivot;

        return $pivot && in_array($pivot->role, ['owner', 'manager']);
    }

    public function delete(User $user, Invoice $invoice): bool
    {
        return $user->isAtLeast(User::ROLE_ADMIN);
    }

    public function restore(User $user, Invoice $invoice): bool
    {
        return $user->isAtLeast(User::ROLE_ADMIN);
    }

    public function forceDelete(User $user, Invoice $invoice): bool
    {
        return $user->isSuperUser();
    }
}
