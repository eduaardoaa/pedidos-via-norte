<?php

namespace App\Http\Controllers;

use App\Models\FaceVerificationLog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class FaceVerificationController extends Controller
{
    private const FACE_APPROVAL_DISTANCE = 0.58;

    public function show(): View
    {
        return view('face.verify');
    }

    public function verify(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'face_image' => ['required', 'string'],
            'face_distance' => ['required', 'numeric'],
        ]);

        $user = Auth::user()?->loadMissing('cargo:id,codigo');

        if (! $user) {
            return redirect()
                ->route('login')
                ->with('error', 'Usuário não autenticado.');
        }

        $distance = (float) $validated['face_distance'];
        $approved = $distance <= self::FACE_APPROVAL_DISTANCE;

        $capturedPath = $this->storeCapturedImage($validated['face_image'], $user->id);

        $this->createLog($request, $user, $capturedPath, $distance, $approved);

        if (! $approved) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()
                ->route('login')
                ->with('error', 'Validação facial não conferiu. Faça login novamente.');
        }

        session(['face_verified' => true]);

        return $this->redirectByRole($user);
    }

    public function loginFaceForm(Request $request): View|RedirectResponse
    {
        $userId = session('pending_face_user_id');

        if (! $userId) {
            return redirect()->route('login');
        }

        $user = User::query()
            ->select(['id', 'face_descriptor'])
            ->find($userId);

        if (! $user || empty($user->face_descriptor)) {
            session()->forget(['pending_face_user_id', 'pending_face_remember']);

            return redirect()
                ->route('login')
                ->with('error', 'Usuário sem cadastro facial.');
        }

        return view('auth.login-face', [
            'pendingFaceDescriptor' => $user->face_descriptor,
        ]);
    }

    public function loginFaceVerify(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'face_image' => ['required', 'string'],
            'face_distance' => ['required', 'numeric'],
        ]);

        $userId = session('pending_face_user_id');

        if (! $userId) {
            return redirect()->route('login');
        }

        $user = User::query()
            ->select(['id', 'cargo_id', 'face_photo_path'])
            ->with('cargo:id,codigo')
            ->find($userId);

        if (! $user) {
            session()->forget(['pending_face_user_id', 'pending_face_remember']);

            return redirect()->route('login');
        }

        $distance = (float) $validated['face_distance'];
        $approved = $distance <= self::FACE_APPROVAL_DISTANCE;

        $capturedPath = $this->storeCapturedImage($validated['face_image'], $user->id);

        $this->createLog($request, $user, $capturedPath, $distance, $approved);

        if (! $approved) {
            session()->forget(['pending_face_user_id', 'pending_face_remember']);
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()
                ->route('login')
                ->with('error', 'Validação facial não conferiu.');
        }

        Auth::login($user, (bool) session('pending_face_remember', false));

        session()->forget(['pending_face_user_id', 'pending_face_remember']);
        session(['face_verified' => true]);

        $user->forceFill([
            'last_activity_at' => now(),
        ])->save();

        return $this->redirectByRole($user);
    }

    private function redirectByRole(User $user): RedirectResponse
    {
        $cargoCodigo = mb_strtolower(trim($user->cargo->codigo ?? ''));

        if ($cargoCodigo === 'cabo de turma') {
            return redirect()->route('cabo.dashboard');
        }

        if ($cargoCodigo === 'supervisor') {
            return redirect()->route('supervisor.dashboard');
        }

        return redirect()->route('dashboard');
    }

    private function createLog(
        Request $request,
        User $user,
        ?string $capturedPath,
        float $distance,
        bool $approved
    ): void {
        FaceVerificationLog::create([
            'user_id' => $user->id,
            'reference_photo_path' => $user->face_photo_path,
            'captured_photo_path' => $capturedPath,
            'match_distance' => $distance,
            'status' => $approved ? 'success' : 'failed',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'verified_at' => now(),
        ]);
    }

    private function storeCapturedImage(string $base64Image, int $userId): ?string
    {
        $base64Image = preg_replace('#^data:image/\w+;base64,#i', '', $base64Image);
        $binaryImage = base64_decode($base64Image, true);

        if ($binaryImage === false) {
            return null;
        }

        $directory = 'faces/captured';
        $filename = 'user_' . $userId . '_' . now()->format('YmdHisv') . '.jpg';
        $path = $directory . '/' . $filename;

        Storage::disk('public')->put($path, $binaryImage);

        return $path;
    }
}