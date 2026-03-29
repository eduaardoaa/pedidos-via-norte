<?php

namespace App\Http\Controllers;

use App\Models\Route as RouteModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class RotaController extends Controller
{
    public function index()
    {
        $rotas = RouteModel::withCount('locations')
            ->orderBy('name')
            ->get();

        return view('rotas.index', compact('rotas'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:255', 'unique:routes,code'],
            'active' => ['nullable', 'boolean'],
        ], [
            'name.required' => 'Informe o nome da rota.',
            'code.unique' => 'Esse código já está em uso.',
        ]);

        if ($validator->fails()) {
            return redirect()
                ->route('rotas.index')
                ->withErrors($validator)
                ->withInput()
                ->with('open_modal', 'create');
        }

        $data = $validator->validated();
        $data['code'] = !empty($data['code']) ? trim($data['code']) : Str::slug($data['name'], '_');
        $data['active'] = $request->boolean('active', true);

        RouteModel::create($data);

        return redirect()
            ->route('rotas.index')
            ->with('success', 'Rota criada com sucesso.');
    }

    public function update(Request $request, RouteModel $rota)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'code' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('routes', 'code')->ignore($rota->id),
            ],
            'active' => ['nullable', 'boolean'],
        ], [
            'name.required' => 'Informe o nome da rota.',
            'code.unique' => 'Esse código já está em uso.',
        ]);

        if ($validator->fails()) {
            return redirect()
                ->route('rotas.index')
                ->withErrors($validator)
                ->withInput()
                ->with('open_modal', 'edit_' . $rota->id);
        }

        $data = $validator->validated();
        $data['code'] = !empty($data['code']) ? trim($data['code']) : Str::slug($data['name'], '_');
        $data['active'] = $request->boolean('active');

        $rota->update($data);

        return redirect()
            ->route('rotas.index')
            ->with('success', 'Rota atualizada com sucesso.');
    }

    public function toggle(RouteModel $rota)
    {
        $rota->update([
            'active' => !$rota->active,
        ]);

        return redirect()
            ->route('rotas.index')
            ->with('success', $rota->active ? 'Rota ativada com sucesso.' : 'Rota inativada com sucesso.');
    }
}