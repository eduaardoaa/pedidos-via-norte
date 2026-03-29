<?php

namespace App\Http\Controllers;

use App\Models\Location;
use App\Models\Route as RouteModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class LocalController extends Controller
{
    public function index(Request $request)
    {
        $routeFilter = $request->get('route_id');
        $scopeFilter = $request->get('scope');

        $rotas = RouteModel::orderBy('name')->get();

        $locais = Location::with('route')
            ->when($scopeFilter, function ($query) use ($scopeFilter) {
                $query->where('scope', $scopeFilter);
            })
            ->when($routeFilter, function ($query) use ($routeFilter) {
                $query->where('route_id', $routeFilter);
            })
            ->orderBy('name')
            ->get();

        return view('locais.index', compact('locais', 'rotas', 'routeFilter', 'scopeFilter'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'scope' => ['required', Rule::in(['rota', 'almoxarifado'])],
            'route_id' => ['nullable', 'exists:routes,id'],
            'name' => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'active' => ['nullable', 'boolean'],
        ], [
            'scope.required' => 'Selecione o tipo do local.',
            'scope.in' => 'Tipo de local inválido.',
            'route_id.exists' => 'Rota inválida.',
            'name.required' => 'Informe o nome do local.',
        ]);

        $validator->after(function ($validator) use ($request) {
            if ($request->scope === 'rota' && ! $request->route_id) {
                $validator->errors()->add('route_id', 'Selecione a rota para um local de rota.');
            }
        });

        if ($validator->fails()) {
            return redirect()
                ->route('locais.index', [
                    'route_id' => $request->route_id,
                    'scope' => $request->scope,
                ])
                ->withErrors($validator)
                ->withInput()
                ->with('open_modal', 'create');
        }

        $data = $validator->validated();
        $data['active'] = $request->boolean('active', true);

        if ($data['scope'] === 'almoxarifado') {
            $data['route_id'] = null;
        }

        $coordinates = $this->geocodeAddress($data['address'] ?? null);

        $data['latitude'] = $coordinates['latitude'] ?? null;
        $data['longitude'] = $coordinates['longitude'] ?? null;
        $data['geocoded_at'] = $coordinates ? now() : null;

        Location::create($data);

        return redirect()
            ->route('locais.index', [
                'route_id' => $request->route_id,
                'scope' => $request->scope,
            ])
            ->with('success', 'Local criado com sucesso.');
    }

    public function update(Request $request, Location $local)
    {
        $validator = Validator::make($request->all(), [
            'scope' => ['required', Rule::in(['rota', 'almoxarifado'])],
            'route_id' => ['nullable', 'exists:routes,id'],
            'name' => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'active' => ['nullable', 'boolean'],
        ], [
            'scope.required' => 'Selecione o tipo do local.',
            'scope.in' => 'Tipo de local inválido.',
            'route_id.exists' => 'Rota inválida.',
            'name.required' => 'Informe o nome do local.',
        ]);

        $validator->after(function ($validator) use ($request) {
            if ($request->scope === 'rota' && ! $request->route_id) {
                $validator->errors()->add('route_id', 'Selecione a rota para um local de rota.');
            }
        });

        if ($validator->fails()) {
            return redirect()
                ->route('locais.index', [
                    'route_id' => $request->route_id ?: $local->route_id,
                    'scope' => $request->scope ?: $local->scope,
                ])
                ->withErrors($validator)
                ->withInput()
                ->with('open_modal', 'edit_' . $local->id);
        }

        $data = $validator->validated();
        $data['active'] = $request->boolean('active');

        if ($data['scope'] === 'almoxarifado') {
            $data['route_id'] = null;
        }

        $addressChanged = trim((string) ($data['address'] ?? '')) !== trim((string) ($local->address ?? ''));

if ($addressChanged) {
    $coordinates = $this->geocodeAddress($data['address'] ?? null);

    $data['latitude'] = $coordinates['latitude'] ?? null;
    $data['longitude'] = $coordinates['longitude'] ?? null;
    $data['geocoded_at'] = $coordinates ? now() : null;
}

        $local->update($data);

        return redirect()
            ->route('locais.index', [
                'route_id' => $request->route_id,
                'scope' => $request->scope,
            ])
            ->with('success', 'Local atualizado com sucesso.');
    }

    public function toggle(Location $local)
    {
        $local->update([
            'active' => ! $local->active,
        ]);

        return redirect()
            ->route('locais.index', [
                'route_id' => request('route_id') ?: $local->route_id,
                'scope' => request('scope') ?: $local->scope,
            ])
            ->with('success', $local->active ? 'Local ativado com sucesso.' : 'Local inativado com sucesso.');
    }

    private function geocodeAddress(?string $address): ?array
{
    if (blank($address)) {
        return null;
    }

    $queries = array_values(array_unique(array_filter([
        trim($address),
        trim($address . ', Aracaju, SE, Brasil'),
        trim($address . ', Sergipe, Brasil'),
        trim($address . ', Brasil'),
    ])));

    foreach ($queries as $query) {
        try {
            $response = Http::acceptJson()
                ->withHeaders([
                    'User-Agent' => 'Vianorte/1.0 (contato-interno)',
                ])
                ->connectTimeout(3)
                ->timeout(6)
                ->get('https://nominatim.openstreetmap.org/search', [
                    'q' => $query,
                    'format' => 'jsonv2',
                    'limit' => 1,
                    'addressdetails' => 1,
                    'countrycodes' => 'br',
                ]);

            if (! $response->successful()) {
                continue;
            }

            $json = $response->json();

            if (! is_array($json) || empty($json[0])) {
                continue;
            }

            $latitude = isset($json[0]['lat']) ? (float) $json[0]['lat'] : null;
            $longitude = isset($json[0]['lon']) ? (float) $json[0]['lon'] : null;

            if ($latitude === null || $longitude === null) {
                continue;
            }

            return [
                'latitude' => $latitude,
                'longitude' => $longitude,
            ];
        } catch (\Throwable $e) {
            report($e);
        }
    }

    return null;
}
}