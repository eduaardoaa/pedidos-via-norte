<?php

namespace App\Http\Controllers;

use App\Models\Location;
use App\Models\MaterialRequest;
use App\Models\Product;
use App\Models\Route;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class MaterialRequestController extends Controller
{
    public function createCabo(): View
    {
        $this->authorizeRole(['cabo de turma']);

        $routes = Route::query()
            ->orderBy('name')
            ->get();

        $locations = Location::query()
            ->where('scope', 'rota')
            ->orderBy('name')
            ->get();

        $products = $this->getProductsByScope('rota');

        return view('cabo.requests.create', compact('routes', 'locations', 'products'));
    }

    public function storeCabo(Request $request): RedirectResponse
{
    $this->authorizeRole(['cabo de turma']);

    $itemsFiltrados = collect($request->input('items', []))
        ->map(function ($item) {
            return [
                'product_id' => $item['product_id'] ?? null,
                'product_variant_id' => $item['product_variant_id'] ?? null,
                'quantity' => isset($item['quantity']) && $item['quantity'] !== ''
                    ? (int) $item['quantity']
                    : null,
            ];
        })
        ->filter(function ($item) {
            return !empty($item['product_id']) && !empty($item['quantity']) && $item['quantity'] > 0;
        })
        ->values()
        ->toArray();

    $request->merge([
        'items' => $itemsFiltrados,
    ]);

    $validated = $request->validate([
        'route_id' => ['required', 'exists:routes,id'],
        'location_id' => ['required', 'exists:locations,id'],
        'notes' => ['nullable', 'string'],

        'request_latitude' => ['required', 'numeric'],
        'request_longitude' => ['required', 'numeric'],
        'request_location_accuracy' => ['nullable', 'numeric'],
        'request_street' => ['nullable', 'string', 'max:255'],
        'request_number' => ['nullable', 'string', 'max:255'],
        'request_neighborhood' => ['nullable', 'string', 'max:255'],
        'request_city' => ['nullable', 'string', 'max:255'],
        'request_state' => ['nullable', 'string', 'max:100'],
        'request_zipcode' => ['nullable', 'string', 'max:20'],
        'request_full_address' => ['nullable', 'string', 'max:255'],

        'items' => ['required', 'array', 'min:1'],
        'items.*.product_id' => ['required', 'exists:products,id'],
        'items.*.product_variant_id' => ['nullable', 'exists:product_variants,id'],
        'items.*.quantity' => ['required', 'integer', 'min:1'],
    ], [
        'request_latitude.required' => 'É obrigatório permitir a localização para enviar a solicitação.',
        'request_longitude.required' => 'É obrigatório permitir a localização para enviar a solicitação.',
        'items.required' => 'Adicione pelo menos um item com quantidade maior que zero.',
        'items.min' => 'Adicione pelo menos um item com quantidade maior que zero.',
    ]);

    $location = Location::findOrFail($validated['location_id']);

    if ($location->scope !== 'rota') {
        return back()
            ->withErrors([
                'location_id' => 'O local selecionado não pertence ao escopo de rota.',
            ])
            ->withInput();
    }

    DB::transaction(function () use ($validated) {
        $materialRequest = MaterialRequest::create([
            'user_id' => Auth::id(),
            'route_id' => $validated['route_id'],
            'location_id' => $validated['location_id'],
            'requester_role' => 'cabo_turma',
            'scope' => 'rota',
            'status' => 'pending',
            'notes' => $validated['notes'] ?? null,

            'request_latitude' => $validated['request_latitude'],
            'request_longitude' => $validated['request_longitude'],
            'request_location_accuracy' => $validated['request_location_accuracy'] ?? null,
            'request_street' => $validated['request_street'] ?? null,
            'request_number' => $validated['request_number'] ?? null,
            'request_neighborhood' => $validated['request_neighborhood'] ?? null,
            'request_city' => $validated['request_city'] ?? null,
            'request_state' => $validated['request_state'] ?? null,
            'request_zipcode' => $validated['request_zipcode'] ?? null,
            'request_full_address' => $validated['request_full_address'] ?? null,
            'request_location_captured_at' => now(),
        ]);

        foreach ($validated['items'] as $item) {
            $materialRequest->items()->create([
                'product_id' => $item['product_id'],
                'product_variant_id' => $item['product_variant_id'] ?? null,
                'quantity' => $item['quantity'],
            ]);
        }
    });

    return redirect()
        ->route('cabo.requests.create')
        ->with('success', 'Solicitação enviada com sucesso.');
}

    public function caboIndex(Request $request): View
    {
        $this->authorizeRole(['cabo de turma']);

        $query = MaterialRequest::query()
            ->with([
                'route',
                'location',
                'items.product',
                'items.variant',
                'approver',
            ])
            ->where('user_id', Auth::id())
            ->where('requester_role', 'cabo_turma')
            ->latest('id');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('route_id')) {
            $query->where('route_id', $request->integer('route_id'));
        }

        if ($request->filled('location_id')) {
            $query->where('location_id', $request->integer('location_id'));
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $requests = $query->paginate(15)->withQueryString();

        $routes = Route::query()
            ->orderBy('name')
            ->get(['id', 'name']);

        $locations = Location::query()
            ->where('scope', 'rota')
            ->orderBy('name')
            ->get(['id', 'name', 'route_id']);

        return view('cabo.requests.index', compact('requests', 'routes', 'locations'));
    }

    public function caboShow(MaterialRequest $materialRequest): View
    {
        $this->authorizeRole(['cabo de turma']);

        abort_unless(
            (int) $materialRequest->user_id === (int) Auth::id()
            && $materialRequest->requester_role === 'cabo_turma',
            403
        );

        $materialRequest->load([
            'user',
            'route',
            'location',
            'approver',
            'items.product',
            'items.variant',
        ]);

        return view('cabo.requests.show', compact('materialRequest'));
    }

    public function createSupervisor(): View
    {
        $this->authorizeRole(['supervisor']);

        $locations = Location::query()
            ->where('scope', 'almoxarifado')
            ->orderBy('name')
            ->get();

        $products = $this->getProductsByScope('almoxarifado');

        return view('supervisor.requests.create', compact('locations', 'products'));
    }

    public function storeSupervisor(Request $request): RedirectResponse
    {
        $this->authorizeRole(['supervisor']);

        $itemsFiltrados = collect($request->input('items', []))
            ->map(function ($item) {
                return [
                    'product_id' => $item['product_id'] ?? null,
                    'product_variant_id' => $item['product_variant_id'] ?? null,
                    'quantity' => isset($item['quantity']) && $item['quantity'] !== ''
                        ? (int) $item['quantity']
                        : null,
                ];
            })
            ->filter(function ($item) {
                return !empty($item['product_id']) && !empty($item['quantity']) && $item['quantity'] > 0;
            })
            ->values()
            ->toArray();

        $request->merge([
            'items' => $itemsFiltrados,
        ]);

        $validated = $request->validate([
            'location_id' => ['required', 'exists:locations,id'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.product_variant_id' => ['nullable', 'exists:product_variants,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ], [
            'items.required' => 'Adicione pelo menos um item com quantidade maior que zero.',
            'items.min' => 'Adicione pelo menos um item com quantidade maior que zero.',
        ]);

        $location = Location::findOrFail($validated['location_id']);

        if ($location->scope !== 'almoxarifado') {
            return back()
                ->withErrors([
                    'location_id' => 'O local selecionado não pertence ao escopo de almoxarifado.',
                ])
                ->withInput();
        }

        DB::transaction(function () use ($validated) {
            $materialRequest = MaterialRequest::create([
                'user_id' => Auth::id(),
                'route_id' => null,
                'location_id' => $validated['location_id'],
                'requester_role' => 'supervisor',
                'scope' => 'almoxarifado',
                'status' => 'pending',
                'notes' => $validated['notes'] ?? null,
            ]);

            foreach ($validated['items'] as $item) {
                $materialRequest->items()->create([
                    'product_id' => $item['product_id'],
                    'product_variant_id' => $item['product_variant_id'] ?? null,
                    'quantity' => $item['quantity'],
                ]);
            }
        });

        return redirect()
            ->route('supervisor.requests.create')
            ->with('success', 'Solicitação enviada com sucesso.');
    }

    public function supervisorIndex(Request $request): View
    {
        $this->authorizeRole(['supervisor']);

        $query = MaterialRequest::query()
            ->with([
                'location',
                'items.product',
                'items.variant',
                'approver',
            ])
            ->where('user_id', Auth::id())
            ->where('requester_role', 'supervisor')
            ->latest('id');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('location_id')) {
            $query->where('location_id', $request->integer('location_id'));
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $requests = $query->paginate(15)->withQueryString();

        $locations = Location::query()
            ->where('scope', 'almoxarifado')
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('supervisor.requests.index', compact('requests', 'locations'));
    }

    public function supervisorShow(MaterialRequest $materialRequest): View
    {
        $this->authorizeRole(['supervisor']);

        abort_unless(
            (int) $materialRequest->user_id === (int) Auth::id()
            && $materialRequest->requester_role === 'supervisor',
            403
        );

        $materialRequest->load([
            'user',
            'location',
            'approver',
            'items.product',
            'items.variant',
        ]);

        return view('supervisor.requests.show', compact('materialRequest'));
    }

    public function caboQuickView(MaterialRequest $materialRequest): JsonResponse
    {
        $this->authorizeRole(['cabo de turma']);

        abort_unless(
            (int) $materialRequest->user_id === (int) Auth::id()
            && $materialRequest->requester_role === 'cabo_turma',
            403
        );

        $materialRequest->load([
            'route',
            'location',
            'approver',
            'items.product',
            'items.variant',
        ]);

        return response()->json([
            'id' => $materialRequest->id,
            'status' => $materialRequest->status,
            'scope' => $materialRequest->scope === 'almoxarifado' ? 'Almoxarifado' : 'Rota',
            'route' => $materialRequest->route->name ?? '-',
            'location' => $materialRequest->location->name ?? '-',
            'created_at' => $materialRequest->created_at?->format('d/m/Y H:i') ?? '-',
            'approved_by' => $materialRequest->approver->name ?? '-',
            'approved_at' => $materialRequest->approved_at?->format('d/m/Y H:i') ?? '-',
            'notes' => $materialRequest->notes ?? '',
            'admin_notes' => $materialRequest->admin_notes ?? '',
            'items' => $materialRequest->items->map(function ($item) {
                return [
                    'product' => $item->product->name ?? '-',
                    'variant' => $item->variant->name ?? 'Sem variação',
                    'quantity' => $item->quantity,
                ];
            })->values()->toArray(),
        ]);
    }

    public function supervisorQuickView(MaterialRequest $materialRequest): JsonResponse
    {
        $this->authorizeRole(['supervisor']);

        abort_unless(
            (int) $materialRequest->user_id === (int) Auth::id()
            && $materialRequest->requester_role === 'supervisor',
            403
        );

        $materialRequest->load([
            'location',
            'approver',
            'items.product',
            'items.variant',
        ]);

        return response()->json([
            'id' => $materialRequest->id,
            'status' => $materialRequest->status,
            'scope' => $materialRequest->scope === 'almoxarifado' ? 'Almoxarifado' : 'Rota',
            'route' => '-',
            'location' => $materialRequest->location->name ?? '-',
            'created_at' => $materialRequest->created_at?->format('d/m/Y H:i') ?? '-',
            'approved_by' => $materialRequest->approver->name ?? '-',
            'approved_at' => $materialRequest->approved_at?->format('d/m/Y H:i') ?? '-',
            'notes' => $materialRequest->notes ?? '',
            'admin_notes' => $materialRequest->admin_notes ?? '',
            'items' => $materialRequest->items->map(function ($item) {
                return [
                    'product' => $item->product->name ?? '-',
                    'variant' => $item->variant->name ?? 'Sem variação',
                    'quantity' => $item->quantity,
                ];
            })->values()->toArray(),
        ]);
    }

    public function caboEdit(MaterialRequest $materialRequest): View
    {
        $this->authorizeRole(['cabo de turma']);

        abort_unless(
            (int) $materialRequest->user_id === (int) Auth::id()
            && $materialRequest->requester_role === 'cabo_turma',
            403
        );

        if ($materialRequest->status !== 'pending') {
            return redirect()
                ->route('cabo.requests.index')
                ->with('error', 'Apenas solicitações pendentes podem ser editadas.');
        }

        $materialRequest->load([
            'route',
            'location',
            'items.product',
            'items.variant',
        ]);

        $products = $this->getProductsByScope('rota');

        return view('cabo.requests.edit', compact('materialRequest', 'products'));
    }

    public function caboUpdate(Request $request, MaterialRequest $materialRequest): RedirectResponse
    {
        $this->authorizeRole(['cabo de turma']);

        abort_unless(
            (int) $materialRequest->user_id === (int) Auth::id()
            && $materialRequest->requester_role === 'cabo_turma',
            403
        );

        if ($materialRequest->status !== 'pending') {
            return redirect()
                ->route('cabo.requests.index')
                ->with('error', 'Apenas solicitações pendentes podem ser editadas.');
        }

        $itemsFiltrados = collect($request->input('items', []))
            ->map(function ($item) {
                return [
                    'product_id' => $item['product_id'] ?? null,
                    'product_variant_id' => $item['product_variant_id'] ?? null,
                    'quantity' => isset($item['quantity']) && $item['quantity'] !== ''
                        ? (int) $item['quantity']
                        : null,
                ];
            })
            ->filter(function ($item) {
                return !empty($item['product_id']) && !empty($item['quantity']) && $item['quantity'] > 0;
            })
            ->values()
            ->toArray();

        $request->merge([
            'items' => $itemsFiltrados,
        ]);

        $validated = $request->validate([
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.product_variant_id' => ['nullable', 'exists:product_variants,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ], [
            'items.required' => 'Adicione pelo menos um item com quantidade maior que zero.',
            'items.min' => 'Adicione pelo menos um item com quantidade maior que zero.',
        ]);

        DB::transaction(function () use ($materialRequest, $validated) {
            $materialRequest->update([
                'notes' => $validated['notes'] ?? null,
            ]);

            $materialRequest->items()->delete();

            foreach ($validated['items'] as $item) {
                $materialRequest->items()->create([
                    'product_id' => $item['product_id'],
                    'product_variant_id' => $item['product_variant_id'] ?? null,
                    'quantity' => $item['quantity'],
                ]);
            }
        });

        return redirect()
            ->route('cabo.requests.index')
            ->with('success', 'Solicitação atualizada com sucesso.');
    }

    public function caboDestroy(MaterialRequest $materialRequest): RedirectResponse
    {
        $this->authorizeRole(['cabo de turma']);

        abort_unless(
            (int) $materialRequest->user_id === (int) Auth::id()
            && $materialRequest->requester_role === 'cabo_turma',
            403
        );

        if ($materialRequest->status !== 'pending') {
            return redirect()
                ->route('cabo.requests.index')
                ->with('error', 'Apenas solicitações pendentes podem ser excluídas.');
        }

        DB::transaction(function () use ($materialRequest) {
            $materialRequest->items()->delete();
            $materialRequest->delete();
        });

        return redirect()
            ->route('cabo.requests.index')
            ->with('success', 'Solicitação excluída com sucesso.');
    }

    public function supervisorEdit(MaterialRequest $materialRequest): View
    {
        $this->authorizeRole(['supervisor']);

        abort_unless(
            (int) $materialRequest->user_id === (int) Auth::id()
            && $materialRequest->requester_role === 'supervisor',
            403
        );

        if ($materialRequest->status !== 'pending') {
            return redirect()
                ->route('supervisor.requests.index')
                ->with('error', 'Apenas solicitações pendentes podem ser editadas.');
        }

        $materialRequest->load([
            'location',
            'items.product',
            'items.variant',
        ]);

        $products = $this->getProductsByScope('almoxarifado');

        return view('supervisor.requests.edit', compact('materialRequest', 'products'));
    }

    public function supervisorUpdate(Request $request, MaterialRequest $materialRequest): RedirectResponse
    {
        $this->authorizeRole(['supervisor']);

        abort_unless(
            (int) $materialRequest->user_id === (int) Auth::id()
            && $materialRequest->requester_role === 'supervisor',
            403
        );

        if ($materialRequest->status !== 'pending') {
            return redirect()
                ->route('supervisor.requests.index')
                ->with('error', 'Apenas solicitações pendentes podem ser editadas.');
        }

        $itemsFiltrados = collect($request->input('items', []))
            ->map(function ($item) {
                return [
                    'product_id' => $item['product_id'] ?? null,
                    'product_variant_id' => $item['product_variant_id'] ?? null,
                    'quantity' => isset($item['quantity']) && $item['quantity'] !== ''
                        ? (int) $item['quantity']
                        : null,
                ];
            })
            ->filter(function ($item) {
                return !empty($item['product_id']) && !empty($item['quantity']) && $item['quantity'] > 0;
            })
            ->values()
            ->toArray();

        $request->merge([
            'items' => $itemsFiltrados,
        ]);

        $validated = $request->validate([
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.product_variant_id' => ['nullable', 'exists:product_variants,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ], [
            'items.required' => 'Adicione pelo menos um item com quantidade maior que zero.',
            'items.min' => 'Adicione pelo menos um item com quantidade maior que zero.',
        ]);

        DB::transaction(function () use ($materialRequest, $validated) {
            $materialRequest->update([
                'notes' => $validated['notes'] ?? null,
            ]);

            $materialRequest->items()->delete();

            foreach ($validated['items'] as $item) {
                $materialRequest->items()->create([
                    'product_id' => $item['product_id'],
                    'product_variant_id' => $item['product_variant_id'] ?? null,
                    'quantity' => $item['quantity'],
                ]);
            }
        });

        return redirect()
            ->route('supervisor.requests.index')
            ->with('success', 'Solicitação atualizada com sucesso.');
    }

    public function supervisorDestroy(MaterialRequest $materialRequest): RedirectResponse
    {
        $this->authorizeRole(['supervisor']);

        abort_unless(
            (int) $materialRequest->user_id === (int) Auth::id()
            && $materialRequest->requester_role === 'supervisor',
            403
        );

        if ($materialRequest->status !== 'pending') {
            return redirect()
                ->route('supervisor.requests.index')
                ->with('error', 'Apenas solicitações pendentes podem ser excluídas.');
        }

        DB::transaction(function () use ($materialRequest) {
            $materialRequest->items()->delete();
            $materialRequest->delete();
        });

        return redirect()
            ->route('supervisor.requests.index')
            ->with('success', 'Solicitação excluída com sucesso.');
    }

    public function caboRedo(MaterialRequest $materialRequest): View
    {
        $this->authorizeRole(['cabo de turma']);

        abort_unless(
            (int) $materialRequest->user_id === (int) Auth::id()
            && $materialRequest->requester_role === 'cabo_turma',
            403
        );

        $materialRequest->load([
            'route',
            'location',
            'items.product',
            'items.variant',
        ]);

        $routes = Route::query()
            ->orderBy('name')
            ->get();

        $locations = Location::query()
            ->where('scope', 'rota')
            ->orderBy('name')
            ->get();

        $products = $this->getProductsByScope('rota');

        return view('cabo.requests.create', compact(
            'routes',
            'locations',
            'products',
            'materialRequest'
        ));
    }

    public function supervisorRedo(MaterialRequest $materialRequest): View
    {
        $this->authorizeRole(['supervisor']);

        abort_unless(
            (int) $materialRequest->user_id === (int) Auth::id()
            && $materialRequest->requester_role === 'supervisor',
            403
        );

        $materialRequest->load([
            'location',
            'items.product',
            'items.variant',
        ]);

        $locations = Location::query()
            ->where('scope', 'almoxarifado')
            ->orderBy('name')
            ->get();

        $products = $this->getProductsByScope('almoxarifado');

        return view('supervisor.requests.create', compact(
            'locations',
            'products',
            'materialRequest'
        ));
    }

    protected function authorizeRole(array $roles): void
    {
        $user = Auth::user();

        if (!$user) {
            abort(403);
        }

        $cargoCodigo = $user->cargo->codigo ?? null;

        if (!in_array($cargoCodigo, $roles, true)) {
            abort(403);
        }
    }

    protected function getProductsByScope(string $scope)
    {
        $stockLocationName = $scope === 'rota' ? 'Rota' : 'Almoxarifado';

        return Product::query()
            ->with([
                'variants' => function ($query) {
                    $query->orderBy('name');
                }
            ])
            ->whereHas('locations', function ($query) use ($stockLocationName) {
                $query->where('name', $stockLocationName);
            })
            ->orderBy('name')
            ->get();
    }
}
