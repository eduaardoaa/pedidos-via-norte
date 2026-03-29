<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForcePasswordChangeMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check() && auth()->user()->must_change_password) {
            if (!$request->routeIs('password.first_access', 'password.first_access.update', 'logout')) {
                return redirect()->route('password.first_access');
            }
        }

        return $next($request);
    }
}