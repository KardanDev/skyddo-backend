<?php

namespace App\Policies;

use App\Models\Document;
use App\Models\User;

class DocumentPolicy
{
    public function viewAny(User $user): bool
    {
        return true; // All authenticated users
    }

    public function view(User $user, Document $document): bool
    {
        if ($user->isAtLeast(User::ROLE_ADMIN)) {
            return true;
        }

        // Check access based on the related entity (documentable)
        $documentable = $document->documentable;

        if (method_exists($documentable, 'client')) {
            $client = $documentable->client;

            return $client && $client->users()->where('user_id', $user->id)->exists();
        }

        // If documentable is a Client itself
        if (get_class($documentable) === 'App\Models\Client') {
            return $documentable->users()->where('user_id', $user->id)->exists();
        }

        return false;
    }

    public function create(User $user): bool
    {
        return true; // All authenticated users can upload documents
    }

    public function update(User $user, Document $document): bool
    {
        if ($user->isAtLeast(User::ROLE_ADMIN)) {
            return true;
        }

        // Only the uploader or client managers can update
        if ($document->uploaded_by === $user->id) {
            return true;
        }

        $documentable = $document->documentable;
        if (method_exists($documentable, 'client')) {
            $client = $documentable->client;
            $pivot = $client->users()->where('user_id', $user->id)->first()?->pivot;

            return $pivot && in_array($pivot->role, ['owner', 'manager']);
        }

        return false;
    }

    public function delete(User $user, Document $document): bool
    {
        if ($user->isAtLeast(User::ROLE_ADMIN)) {
            return true;
        }

        // Only the uploader or client managers can delete
        if ($document->uploaded_by === $user->id) {
            return true;
        }

        $documentable = $document->documentable;
        if (method_exists($documentable, 'client')) {
            $client = $documentable->client;
            $pivot = $client->users()->where('user_id', $user->id)->first()?->pivot;

            return $pivot && in_array($pivot->role, ['owner', 'manager']);
        }

        return false;
    }

    public function restore(User $user, Document $document): bool
    {
        return $user->isAtLeast(User::ROLE_ADMIN);
    }

    public function forceDelete(User $user, Document $document): bool
    {
        return $user->isSuperUser();
    }
}
