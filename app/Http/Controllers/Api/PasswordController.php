<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Mail\ResetPasswordMail;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PasswordController extends Controller
{
    /**
     * Send password reset email.
     *
     * @unauthenticated
     */
    public function forgot(ForgotPasswordRequest $request): JsonResponse
    {
        $user = User::where('email', $request->email)->first();

        if ($user) {
            // Delete any existing tokens
            DB::table('password_reset_tokens')->where('email', $request->email)->delete();

            $token = Str::random(64);

            DB::table('password_reset_tokens')->insert([
                'email' => $request->email,
                'token' => Hash::make($token),
                'created_at' => now(),
            ]);

            Mail::to($user->email)->send(new ResetPasswordMail($user, $token));
        }

        // Always return success to prevent email enumeration
        return response()->json([
            'message' => 'If this email exists in our system, you will receive a password reset link.',
        ]);
    }

    /**
     * Reset password using token.
     *
     * @unauthenticated
     */
    public function reset(ResetPasswordRequest $request): JsonResponse
    {
        $record = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        if (! $record) {
            throw ValidationException::withMessages([
                'token' => ['Invalid or expired reset token.'],
            ]);
        }

        // Check if token is expired (1 hour)
        $createdAt = \Carbon\Carbon::parse($record->created_at);
        if (now()->diffInMinutes($createdAt) > 60) {
            DB::table('password_reset_tokens')->where('email', $request->email)->delete();

            throw ValidationException::withMessages([
                'token' => ['Reset token has expired.'],
            ]);
        }

        if (! Hash::check($request->token, $record->token)) {
            throw ValidationException::withMessages([
                'token' => ['Invalid reset token.'],
            ]);
        }

        $user = User::where('email', $request->email)->firstOrFail();
        $user->update(['password' => $request->password]);

        // Delete the used token
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        // Revoke all existing tokens
        $user->tokens()->delete();

        return response()->json([
            'message' => 'Password has been reset successfully.',
        ]);
    }
}
