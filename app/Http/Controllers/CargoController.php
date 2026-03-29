<?php

namespace App\Http\Controllers;

use App\Models\Cargo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CargoController extends Controller
{
    public function index()
    {
        $cargos = Cargo::withCount('users')
            ->orderBy('nome')
            ->get();

        return view('cargos.index', compact('cargos'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nome' => ['required', 'string', 'max:255'],
            'codigo' => ['nullable', 'string', 'max:255', 'unique:cargos,codigo'],
            'ativo' => ['nullable', 'boolean'],
        ], [
            'nome.required' => 'Informe o nome do cargo.',
            'codigo.unique' => 'Esse código já está em uso.',
        ]);

        if ($validator->fails()) {
            return redirect()
                ->route('cargos.index')
                ->withErrors($validator)
                ->withInput()
                ->with('open_modal', 'create');
        }

        $data = $validator->validated();
        $data['codigo'] = !empty($data['codigo']) ? $data['codigo'] : Str::slug($data['nome'], '_');
        $data['ativo'] = $request->boolean('ativo', true);

        Cargo::create($data);

        return redirect()
            ->route('cargos.index')
            ->with('success', 'Cargo criado com sucesso.');
    }

    public function update(Request $request, Cargo $cargo)
    {
        $validator = Validator::make($request->all(), [
            'nome' => ['required', 'string', 'max:255'],
            'codigo' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('cargos', 'codigo')->ignore($cargo->id),
            ],
            'ativo' => ['nullable', 'boolean'],
        ], [
            'nome.required' => 'Informe o nome do cargo.',
            'codigo.unique' => 'Esse código já está em uso.',
        ]);

        if ($validator->fails()) {
            return redirect()
                ->route('cargos.index')
                ->withErrors($validator)
                ->withInput()
                ->with('open_modal', 'edit_' . $cargo->id);
        }

        $data = $validator->validated();
        $data['codigo'] = !empty($data['codigo']) ? $data['codigo'] : Str::slug($data['nome'], '_');
        $data['ativo'] = $request->boolean('ativo');

        $cargo->update($data);

        return redirect()
            ->route('cargos.index')
            ->with('success', 'Cargo atualizado com sucesso.');
    }

    public function toggle(Cargo $cargo)
    {
        $cargo->update([
            'ativo' => !$cargo->ativo,
        ]);

        return redirect()
            ->route('cargos.index')
            ->with('success', $cargo->ativo ? 'Cargo ativado com sucesso.' : 'Cargo inativado com sucesso.');
    }
}