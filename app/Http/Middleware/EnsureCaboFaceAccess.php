<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureCaboFaceAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return $next($request);
        }

        $user = Auth::user();
        $cargoCodigo = mb_strtolower(trim($user->cargo->codigo ?? ''));

        if ($cargoCodigo !== 'cabo de turma') {
            return $next($request);
        }

        $currentRoute = $request->route()?->getName();

        $allowedWithoutFace = [
            'logout',
            'password.first_access',
            'password.first_access.update',
            'face.register',
            'face.register.store',
        ];

        if (empty($user->face_descriptor)) {
            if (!in_array($currentRoute, $allowedWithoutFace, true)) {
                return redirect()->route('face.register');
            }

            return $next($request);
        }

        $allowedWithoutFaceValidation = [
            'logout',
            'login',
            'login.attempt',
            'login.face',
            'login.face.verify',
        ];

        if (session('face_verified') !== true) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->with('error', 'Validação facial obrigatória para acessar o sistema.');
        }

        return $next($request);
    }
}