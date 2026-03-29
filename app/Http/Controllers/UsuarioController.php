<?php

namespace App\Http\Controllers;

use App\Models\Cargo;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\RedirectResponse;

class UsuarioController extends Controller
{
    public function index()
    {
        $usuarios = User::with('cargo')
            ->orderBy('name')
            ->get();

        $cargos = Cargo::where('ativo', true)
            ->orderBy('nome')
            ->get();

        return view('usuarios.index', compact('usuarios', 'cargos'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'usuario' => ['required', 'string', 'max:255', 'unique:users,usuario'],
            'cpf' => ['required', 'string', 'max:14', 'unique:users,cpf'],
            'numero' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255', 'unique:users,email'],
            'cargo_id' => ['required', 'exists:cargos,id'],
            'active' => ['nullable', 'boolean'],
        ], [
            'name.required' => 'Informe o nome do usuário.',
            'usuario.required' => 'Informe o usuário/login interno.',
            'usuario.unique' => 'Esse usuário já está em uso.',
            'cpf.required' => 'Informe o CPF.',
            'cpf.unique' => 'Esse CPF já está cadastrado.',
            'email.email' => 'Informe um e-mail válido.',
            'email.unique' => 'Esse e-mail já está em uso.',
            'cargo_id.required' => 'Selecione o cargo.',
            'cargo_id.exists' => 'Cargo inválido.',
        ]);

        if ($validator->fails()) {
            return redirect()
                ->route('usuarios.index')
                ->withErrors($validator)
                ->withInput()
                ->with('open_modal', 'create');
        }

        $data = $validator->validated();

        $data['cpf'] = $this->formatCpf($data['cpf']);
        $data['numero'] = $this->formatNumero($data['numero'] ?? null);
        $data['email'] = !empty($data['email']) ? trim($data['email']) : null;
        $data['usuario'] = trim($data['usuario']);
        $data['active'] = $request->boolean('active', true);
        $data['must_change_password'] = true;
        $data['password'] = Hash::make('12345');

        User::create($data);

        return redirect()
            ->route('usuarios.index')
            ->with('success', 'Usuário criado com sucesso. Senha inicial: 12345.');
    }

    public function update(Request $request, User $user)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'usuario' => [
                'required',
                'string',
                'max:255',
                Rule::unique('users', 'usuario')->ignore($user->id),
            ],
            'cpf' => [
                'required',
                'string',
                'max:14',
                Rule::unique('users', 'cpf')->ignore($user->id),
            ],
            'numero' => ['nullable', 'string', 'max:20'],
            'email' => [
                'nullable',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'cargo_id' => ['required', 'exists:cargos,id'],
            'active' => ['nullable', 'boolean'],
        ], [
            'name.required' => 'Informe o nome do usuário.',
            'usuario.required' => 'Informe o usuário/login interno.',
            'usuario.unique' => 'Esse usuário já está em uso.',
            'cpf.required' => 'Informe o CPF.',
            'cpf.unique' => 'Esse CPF já está cadastrado.',
            'email.email' => 'Informe um e-mail válido.',
            'email.unique' => 'Esse e-mail já está em uso.',
            'cargo_id.required' => 'Selecione o cargo.',
            'cargo_id.exists' => 'Cargo inválido.',
        ]);

        if ($validator->fails()) {
            return redirect()
                ->route('usuarios.index')
                ->withErrors($validator)
                ->withInput()
                ->with('open_modal', 'edit_' . $user->id);
        }

        $data = $validator->validated();

        $data['cpf'] = $this->formatCpf($data['cpf']);
        $data['numero'] = $this->formatNumero($data['numero'] ?? null);
        $data['email'] = !empty($data['email']) ? trim($data['email']) : null;
        $data['usuario'] = trim($data['usuario']);
        $data['active'] = $request->boolean('active');

        $user->update($data);

        return redirect()
            ->route('usuarios.index')
            ->with('success', 'Usuário atualizado com sucesso.');
    }

    public function toggle(User $user)
    {
        if (auth()->id() === $user->id) {
            return redirect()
                ->route('usuarios.index')
                ->with('error', 'Você não pode inativar o próprio usuário logado.');
        }

        $user->update([
            'active' => !$user->active,
        ]);

        return redirect()
            ->route('usuarios.index')
            ->with('success', $user->active ? 'Usuário ativado com sucesso.' : 'Usuário inativado com sucesso.');
    }

    public function resetPassword(User $user)
    {
        $user->update([
            'password' => Hash::make('12345'),
            'must_change_password' => true,
        ]);

        return redirect()
            ->route('usuarios.index')
            ->with('success', 'Senha resetada para 12345. O usuário será obrigado a trocar no próximo acesso.');
    }

    public function destroy(User $user)
    {
        if (auth()->id() === $user->id) {
            return redirect()
                ->route('usuarios.index')
                ->with('error', 'Você não pode excluir o próprio usuário logado.');
        }

        if ($user->orders()->exists() || $user->notices()->exists()) {
            return redirect()
                ->route('usuarios.index')
                ->with('error', 'Esse usuário possui vínculos no sistema e não pode ser excluído. Inative em vez de excluir.');
        }

        $user->delete();

        return redirect()
            ->route('usuarios.index')
            ->with('success', 'Usuário excluído com sucesso.');
    }

    private function formatCpf(string $cpf): string
    {
        $cpf = preg_replace('/\D/', '', $cpf);

        if (strlen($cpf) === 11) {
            return preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $cpf);
        }

        return $cpf;
    }

    private function formatNumero(?string $numero): ?string
    {
        if (!$numero) {
            return null;
        }

        $digits = preg_replace('/\D/', '', $numero);

        if (strlen($digits) === 11) {
            return preg_replace('/(\d{2})(\d{5})(\d{4})/', '($1) $2-$3', $digits);
        }

        if (strlen($digits) === 10) {
            return preg_replace('/(\d{2})(\d{4})(\d{4})/', '($1) $2-$3', $digits);
        }

        return trim($numero);
    }
    public function resetFace(User $user): RedirectResponse
{
    $cargoCodigo = mb_strtolower(trim($user->cargo->codigo ?? ''));

    if ($cargoCodigo !== 'cabo de turma') {
        return redirect()
            ->route('usuarios.index')
            ->with('error', 'A redefinição facial só está disponível para usuários do tipo Cabo de turma.');
    }

    if ($user->face_photo_path && Storage::disk('public')->exists($user->face_photo_path)) {
        Storage::disk('public')->delete($user->face_photo_path);
    }

    $user->update([
        'face_photo_path' => null,
        'face_descriptor' => null,
        'face_registered_at' => null,
        'must_register_face' => true,
    ]);

    return redirect()
        ->route('usuarios.index')
        ->with('success', 'Validação facial redefinida com sucesso.');
}
}