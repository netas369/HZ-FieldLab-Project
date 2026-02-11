<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;

class PasswordResetController extends Controller
{
    /**
     * Generate a password reset token for a user
     */
    public function forgotPassword(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
        ], [
            'email.exists' => 'No account found with this email address.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }


        $token = Str::random(64);

        $user->update([
            'reset_token' => $token,
            'reset_token_expires_at' => now()->addMinutes(30)
        ]);

        return response()->json([
            'message' => 'Password reset token generated successfully',
            'token' => $token,
            'expires_at' => $user->reset_token_expires_at->toDateTimeString()
        ]);
    }

    /**
     * Reset the user's password using the token
     */
    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required|string',
            'email' => 'required|email|exists:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        // Find user by email and token
        $user = User::where('email', $request->email)
            ->where('reset_token', $request->token)
            ->first();

        if (!$user) {
            throw ValidationException::withMessages([
                'token' => ['Invalid reset token or email address.'],
            ]);
        }

        // Check if token has expired
        if ($user->reset_token_expires_at < now()) {
            throw ValidationException::withMessages([
                'token' => ['This reset token has expired. Please request a new one.'],
            ]);
        }

        // Update the password
        $user->update([
            'password' => Hash::make($request->password),
            'reset_token' => null,
            'reset_token_expires_at' => null,
        ]);

        return response()->json([
            'message' => 'Password reset successfully'
        ]);
    }
}   