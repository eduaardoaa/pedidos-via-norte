<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'cpf' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $cpf = preg_replace('/\D/', '', $request->cpf);

        $usuario = User::with('cargo')
            ->whereRaw("REPLACE(REPLACE(REPLACE(cpf, '.', ''), '-', ''), '/', '') = ?", [$cpf])
            ->first();

        if (! $usuario || ! Hash::check($request->password, $usuario->password)) {
            return back()
                ->withErrors(['login' => 'CPF ou senha inválidos.'])
                ->withInput($request->only('cpf'));
        }

        if (! $usuario->active) {
            return back()
                ->withErrors(['login' => 'Usuário bloqueado ou inativo.'])
                ->withInput($request->only('cpf'));
        }

        $cargoCodigo = mb_strtolower(trim($usuario->cargo->codigo ?? ''));

        if ($cargoCodigo === 'cabo de turma') {
            Auth::login($usuario);
            $request->session()->regenerate();

            if ($usuario->must_change_password) {
                return redirect()->route('password.first_access');
            }

            if (empty($usuario->face_descriptor)) {
                session()->forget('face_verified');
                return redirect()->route('face.register');
            }

            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            $request->session()->put('pending_face_user_id', $usuario->id);
            $request->session()->put('pending_face_remember', false);

            return redirect()->route('login.face');
        }

        Auth::login($usuario);
        $request->session()->regenerate();

        if ($cargoCodigo === 'admin') {
            return redirect()->route('dashboard');
        }

        if ($cargoCodigo === 'supervisor') {
            return redirect()->route('supervisor.dashboard');
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()
            ->route('login')
            ->withErrors(['login' => 'Seu perfil não possui uma área de acesso configurada.']);
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}