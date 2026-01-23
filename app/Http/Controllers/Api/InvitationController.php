<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\CreateInvitationRequest;
use App\Mail\InvitationMail;
use App\Models\Invitation;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class InvitationController extends Controller
{
    /**
     * List all pending invitations.
     */
    public function index(Request $request): JsonResponse
    {
        Gate::authorize('manage-invitations');

        $invitations = Invitation::with('inviter:id,name,email')
            ->whereNull('accepted_at')
            ->where('expires_at', '>', now())
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($invitations);
    }

    /**
     * Create a new invitation.
     */
    public function store(CreateInvitationRequest $request): JsonResponse
    {
        Gate::authorize('manage-invitations');

        /** @var User $user */
        $user = $request->user();

        // Generate a unique short code
        do {
            $shortCode = Str::random(8);
        } while (Invitation::where('short_code', $shortCode)->exists());

        $invitation = Invitation::create([
            'email' => $request->email,
            'role' => $request->role,
            'token' => Str::random(64),
            'short_code' => $shortCode,
            'invited_by' => $user->id,
            'expires_at' => now()->addDays(7),
        ]);

        Mail::to($invitation->email)->send(new InvitationMail($invitation));

        return response()->json($invitation, 201);
    }

    /**
     * Redirect short code to full registration URL (public endpoint).
     *
     * @unauthenticated
     */
    public function redirectShortCode(string $shortCode)
    {
        $invitation = Invitation::where('short_code', $shortCode)
            ->whereNull('accepted_at')
            ->where('expires_at', '>', now())
            ->first();

        if (! $invitation) {
            $frontendUrl = config('app.frontend_url', config('app.url'));

            return redirect($frontendUrl.'/sign-in?error=invalid_invitation');
        }

        $frontendUrl = config('app.frontend_url', config('app.url'));

        return redirect($frontendUrl.'/register?token='.$invitation->token);
    }

    /**
     * Verify an invitation token (public endpoint).
     *
     * @unauthenticated
     */
    public function verify(string $token): JsonResponse
    {
        $invitation = Invitation::where('token', $token)
            ->whereNull('accepted_at')
            ->where('expires_at', '>', now())
            ->first();

        if (! $invitation) {
            return response()->json(['message' => 'Invalid or expired invitation.'], 404);
        }

        return response()->json([
            'email' => $invitation->email,
            'role' => $invitation->role,
            'expires_at' => $invitation->expires_at,
        ]);
    }

    /**
     * Revoke an invitation.
     */
    public function destroy(Invitation $invitation): JsonResponse
    {
        Gate::authorize('manage-invitations');

        if ($invitation->isAccepted()) {
            return response()->json(['message' => 'Cannot revoke an accepted invitation.'], 422);
        }

        $invitation->delete();

        return response()->json(['message' => 'Invitation revoked.']);
    }
}
