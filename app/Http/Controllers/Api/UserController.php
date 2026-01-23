<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('manage-users');

        $perPage = $request->input('per_page', 15);
        $search = $request->input('search');
        $role = $request->input('role');

        $query = User::query();

        // Search by name or email
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                    ->orWhere('email', 'ilike', "%{$search}%");
            });
        }

        // Filter by role
        if ($role) {
            $query->where('role', $role);
        }

        // Order by created_at descending
        $query->orderBy('created_at', 'desc');

        $users = $query->paginate($perPage);

        return response()->json([
            'data' => UserResource::collection($users->items()),
            'current_page' => $users->currentPage(),
            'last_page' => $users->lastPage(),
            'per_page' => $users->perPage(),
            'total' => $users->total(),
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user): JsonResponse
    {
        $this->authorize('manage-users');

        return response()->json(new UserResource($user));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        // Prevent users from modifying super users
        if ($user->isSuperUser()) {
            return response()->json([
                'message' => 'Super users cannot be modified.',
            ], 403);
        }

        // Prevent users from changing their own role
        if ($user->id === $request->user()->id && $request->has('role')) {
            return response()->json([
                'message' => 'You cannot change your own role.',
            ], 403);
        }

        $user->update($request->validated());

        return response()->json([
            'message' => 'User updated successfully.',
            'data' => new UserResource($user),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, User $user): JsonResponse
    {
        $this->authorize('manage-users');

        // Prevent users from deleting super users
        if ($user->isSuperUser()) {
            return response()->json([
                'message' => 'Super users cannot be deleted.',
            ], 403);
        }

        // Prevent users from deleting themselves
        if ($user->id === $request->user()->id) {
            return response()->json([
                'message' => 'You cannot delete your own account.',
            ], 403);
        }

        $user->delete();

        return response()->json([
            'message' => 'User deleted successfully.',
        ]);
    }
}
