<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class FirstAccessPasswordController extends Controller
{
    public function edit()
    {
        if (! auth()->check()) {
            return redirect()->route('login');
        }

        if (! auth()->user()->must_change_password) {
            return $this->redirectByRole(auth()->user());
        }

        return view('auth.first-access-password');
    }

    public function update(Request $request)
    {
        if (! auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user()->load('cargo');

        if (! $user->must_change_password) {
            return $this->redirectByRole($user);
        }

        $request->validate([
            'password' => ['required', 'min:5', 'confirmed'],
        ], [
            'password.required' => 'Informe a nova senha.',
            'password.min' => 'A senha deve ter pelo menos 5 caracteres.',
            'password.confirmed' => 'A confirmação de senha não confere.',
        ]);

        $user->update([
            'password' => Hash::make($request->password),
            'must_change_password' => false,
        ]);

        $user->refresh()->load('cargo');

        $cargoCodigo = mb_strtolower(trim($user->cargo->codigo ?? ''));

        if ($cargoCodigo === 'cabo de turma') {
            if (empty($user->face_descriptor)) {
                return redirect()
                    ->route('face.register')
                    ->with('success', 'Senha alterada com sucesso. Agora cadastre sua validação facial.');
            }

            return redirect()
                ->route('cabo.dashboard')
                ->with('success', 'Senha alterada com sucesso.');
        }

        return $this->redirectByRole($user)
            ->with('success', 'Senha alterada com sucesso.');
    }

    private function redirectByRole($user)
    {
        $cargoCodigo = mb_strtolower(trim($user->cargo->codigo ?? ''));

        return match ($cargoCodigo) {
            'admin' => redirect()->route('dashboard'),
            'cabo de turma' => redirect()->route('cabo.dashboard'),
            'supervisor' => redirect()->route('supervisor.dashboard'),
            default => redirect()->route('login')->withErrors([
                'login' => 'Seu perfil não possui uma área de acesso configurada.',
            ]),
        };
    }
}