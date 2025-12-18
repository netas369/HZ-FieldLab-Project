<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class AuthenticatedSessionController extends Controller
{
    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): Response
    {
        dd($request);
        Log::info('Login attempt started', [
            'email' => $request->email,
            'ip' => $request->ip()
        ]);

        try {
            $request->authenticate();
            $request->session()->regenerate();
            
            Log::info('Login successful', [
                'user_id' => Auth::id(),
                'email' => Auth::user()->email
            ]);
            
            return response()->json([
                'message' => 'Login successful',
                'user' => Auth::user()
            ], 200);
            
        } catch (\Exception $e) {
            Log::error('Login failed', [
                'email' => $request->email,
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): Response
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return response()->json([
            'message' => 'Logged out successfully'
        ], 200);
    }
}
