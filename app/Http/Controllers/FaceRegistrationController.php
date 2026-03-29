<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class FaceRegistrationController extends Controller
{
    public function create(): View
    {
        return view('face.register', [
            'mode' => 'create',
        ]);
    }

    public function edit(): View
    {
        $user = Auth::user()?->loadMissing('cargo:id,codigo');
        $cargoCodigo = mb_strtolower(trim($user->cargo->codigo ?? ''));

        abort_unless($cargoCodigo === 'cabo de turma', 403);

        return view('face.register', [
            'mode' => 'update',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'face_image' => ['required', 'string'],
            'face_descriptor' => ['required', 'string'],
        ], [
            'face_image.required' => 'Capture a foto do rosto antes de continuar.',
            'face_descriptor.required' => 'Não foi possível processar os dados faciais.',
        ]);

        $user = Auth::user()?->loadMissing('cargo:id,codigo');

        if (! $user) {
            return redirect()
                ->route('login')
                ->with('error', 'Usuário não autenticado.');
        }

        $binaryImage = $this->decodeBase64Image($validated['face_image']);

        if ($binaryImage === null) {
            return back()->withErrors([
                'face_image' => 'A imagem facial enviada é inválida.',
            ])->withInput();
        }

        if ($user->face_photo_path && Storage::disk('public')->exists($user->face_photo_path)) {
            Storage::disk('public')->delete($user->face_photo_path);
        }

        $path = $this->storeReferenceImage($binaryImage, $user->id);

        $user->update([
            'face_photo_path' => $path,
            'face_descriptor' => $validated['face_descriptor'],
            'face_registered_at' => now(),
            'must_register_face' => false,
        ]);

        session(['face_verified' => true]);

        return $this->redirectAfterFaceRegister($user);
    }

    private function redirectAfterFaceRegister($user): RedirectResponse
    {
        $cargoCodigo = mb_strtolower(trim($user->cargo->codigo ?? ''));

        if ($cargoCodigo === 'cabo de turma') {
            return redirect()
                ->route('profile.edit')
                ->with('success', 'Validação facial atualizada com sucesso.');
        }

        if ($cargoCodigo === 'supervisor') {
            return redirect()
                ->route('supervisor.dashboard')
                ->with('success', 'Cadastro facial realizado com sucesso.');
        }

        return redirect()
            ->route('dashboard')
            ->with('success', 'Cadastro facial realizado com sucesso.');
    }

    private function decodeBase64Image(string $base64Image): ?string
    {
        $cleanBase64 = preg_replace('#^data:image/\w+;base64,#i', '', $base64Image);

        if (! $cleanBase64) {
            return null;
        }

        $binaryImage = base64_decode($cleanBase64, true);

        return $binaryImage === false ? null : $binaryImage;
    }

    private function storeReferenceImage(string $binaryImage, int $userId): string
    {
        $directory = 'faces/reference';
        $filename = 'user_' . $userId . '_' . now()->format('YmdHisv') . '.jpg';
        $path = $directory . '/' . $filename;

        Storage::disk('public')->put($path, $binaryImage);

        return $path;
    }
}