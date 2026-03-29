<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class UpdateLastActivity
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();

            if ($user->last_activity_at && now()->diffInMinutes($user->last_activity_at) >= 30) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')->with('error', 'Sua sessão expirou por inatividade.');
            }

            $user->forceFill([
                'last_activity_at' => now(),
            ])->save();
        }

        return $next($request);
    }
}