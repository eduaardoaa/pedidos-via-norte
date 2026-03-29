<?php

namespace App\Http\Controllers;

use App\Models\EpiDelivery;
use App\Models\Location;
use App\Models\MaterialRequest;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Route;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use RuntimeException;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        $perPage = 50;

        $ordersQuery = Order::query()
            ->select([
                'id',
                'user_id',
                'route_id',
                'location_id',
                'order_date',
                'status',
            ])
            ->with([
                'user:id,name',
                'route:id,name',
                'location:id,name,scope',
            ])
            ->withCount('items')
            ->withSum('items as total_units', 'quantity');

        if ($request->filled('scope')) {
            $scope = $request->get('scope');

            $ordersQuery->whereHas('location', function ($q) use ($scope) {
                $q->where('scope', $scope);
            });
        }

        if ($request->filled('route_id')) {
            $ordersQuery->where('route_id', $request->integer('route_id'));
        }

        if ($request->filled('user_id')) {
            $ordersQuery->where('user_id', $request->integer('user_id'));
        }

        if ($request->filled('date_start')) {
            $ordersQuery->where('order_date', '>=', $request->date_start);
        }

        if ($request->filled('date_end')) {
            $ordersQuery->where('order_date', '<=', $request->date_end);
        }

        $ordersPaginator = $ordersQuery
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();

        $ordersPaginator->setCollection(
            $ordersPaginator->getCollection()->map(function ($order) {
                $order->list_type = 'order';
                $order->list_id = 'order_' . $order->id;
                $order->display_id = (string) $order->id;
                $order->display_scope = ($order->location->scope ?? null) === 'almoxarifado' ? 'almoxarifado' : 'rota';
                $order->display_local = $order->location->name ?? '-';
                $order->display_route = $order->route->name ?? '-';
                $order->display_user = $order->user->name ?? '-';
                $order->display_items_count = (int) ($order->items_count ?? 0);
                $order->display_total_units = (float) ($order->total_units ?? 0);
                $order->display_date = $order->order_date;
                $order->display_status = $order->status ?? '-';

                $order->can_select_batch = true;
                $order->can_edit = true;
                $order->can_delete = true;
                $order->can_pdf = true;
                $order->can_excel = true;
                $order->can_repeat = true;

                return $order;
            })
        );

        $showEpiDeliveries = !$request->filled('scope') || $request->get('scope') === 'almoxarifado';

        if ($showEpiDeliveries && $ordersPaginator->currentPage() === 1) {
            $epiDeliveriesQuery = EpiDelivery::query()
                ->select([
                    'id',
                    'employee_id',
                    'created_by',
                    'delivery_date',
                ])
                ->with([
                    'employee:id,name',
                    'user:id,name',
                ])
                ->withCount('items')
                ->withSum('items as total_units', 'quantity');

            if ($request->filled('user_id')) {
                $epiDeliveriesQuery->where('created_by', $request->integer('user_id'));
            }

            if ($request->filled('date_start')) {
                $epiDeliveriesQuery->where('delivery_date', '>=', $request->date_start);
            }

            if ($request->filled('date_end')) {
                $epiDeliveriesQuery->where('delivery_date', '<=', $request->date_end);
            }

            $epiDeliveries = $epiDeliveriesQuery
                ->orderByDesc('id')
                ->limit(8)
                ->get()
                ->map(function ($delivery) {
                    $delivery->list_type = 'epi_delivery';
                    $delivery->list_id = 'epi_' . $delivery->id;
                    $delivery->display_id = 'EPI-' . $delivery->id;
                    $delivery->display_scope = 'almoxarifado';
                    $delivery->display_local = 'Material entregue para: ' . ($delivery->employee->name ?? '-');
                    $delivery->display_route = '-';
                    $delivery->display_user = $delivery->user->name ?? '-';
                    $delivery->display_items_count = (int) ($delivery->items_count ?? 0);
                    $delivery->display_total_units = (float) ($delivery->total_units ?? 0);
                    $delivery->display_date = $delivery->delivery_date;
                    $delivery->display_status = 'entregue';

                    $delivery->can_select_batch = false;
                    $delivery->can_edit = false;
                    $delivery->can_delete = false;
                    $delivery->can_pdf = false;
                    $delivery->can_excel = false;
                    $delivery->can_repeat = false;

                    return $delivery;
                });

            if ($epiDeliveries->isNotEmpty()) {
                $mergedFirstPage = $ordersPaginator->getCollection()
                    ->concat($epiDeliveries)
                    ->sortByDesc(function ($item) {
                        return [
                            optional($item->display_date)->format('Y-m-d') ?? '0000-00-00',
                            (int) $item->id,
                        ];
                    })
                    ->take($perPage)
                    ->values();

                $ordersPaginator->setCollection($mergedFirstPage);
            }
        }

        $routes = Cache::remember('orders.index.routes', 3600, function () {
            return Route::select('id', 'name')
                ->orderBy('name')
                ->get();
        });

        $users = Cache::remember('orders.index.users', 3600, function () {
            return User::select('id', 'name')
                ->orderBy('name')
                ->get();
        });

        $orders = $ordersPaginator;

        return view('orders.index', compact('orders', 'routes', 'users'));
    }

    public function quickView(Order $order): JsonResponse
    {
        $order->load([
            'user:id,name',
            'route:id,name',
            'location:id,name,scope,route_id',
            'location.route:id,name',
            'items:id,order_id,product_id,product_variant_id,quantity,product_name_snapshot,unit_snapshot',
            'items.product:id,name',
            'items.variant:id,name,product_id',
        ]);

        $items = $order->items
            ->map(function ($item) {
                return [
                    'produto' => $item->product_name_snapshot ?: ($item->product->name ?? '-'),
                    'variacao' => $item->variant->name ?? '-',
                    'quantidade' => rtrim(rtrim(number_format((float) $item->quantity, 3, '.', ''), '0'), '.'),
                    'unidade' => $item->unit_snapshot ?? '-',
                ];
            })
            ->values()
            ->toArray();

        return response()->json([
            'id' => $order->id,
            'titulo' => 'Pedido #' . $order->id,
            'subtitulo' => 'Visualização rápida do pedido',
            'tipo' => ($order->location->scope ?? null) === 'almoxarifado' ? 'Almoxarifado' : 'Rota',
            'local' => $order->location->name ?? '-',
            'rota' => $order->route->name ?? ($order->location->route->name ?? '-'),
            'usuario' => $order->user->name ?? '-',
            'data' => optional($order->order_date)->format('d/m/Y'),
            'status' => $order->status ?? '-',
            'itens' => $items,
            'total_itens' => count($items),
            'total_unidades' => $this->formatStockNumber((float) $order->items->sum('quantity')),
            'edit_url' => route('orders.edit', $order),
        ]);
    }

    public function create(Request $request): View
    {
        $locations = Location::with('route')
            ->where('active', true)
            ->orderBy('scope')
            ->orderBy('name')
            ->get();

        $products = Product::with([
                'unit',
                'locations',
                'variants' => function ($query) {
                    $query->where('active', true)
                        ->orderBy('sort_order')
                        ->orderBy('name');
                }
            ])
            ->where('active', true)
            ->orderBy('name')
            ->get();

        $prefillData = [
            'scope' => '',
            'route_id' => '',
            'location_id' => '',
            'items' => [],
            'source_order_id' => null,
            'source_material_request_id' => null,
        ];

        if ($request->filled('material_request_id')) {
            $materialRequest = MaterialRequest::with([
                'route',
                'location',
                'items.product',
                'items.variant',
            ])->findOrFail($request->integer('material_request_id'));

            if ($materialRequest->status !== 'pending') {
                abort(404);
            }

            $prefillData = [
                'scope' => $materialRequest->scope,
                'route_id' => $materialRequest->route_id,
                'location_id' => $materialRequest->location_id,
                'items' => $materialRequest->items->map(function ($item) {
                    return [
                        'product_id' => $item->product_id,
                        'product_variant_id' => $item->product_variant_id,
                        'quantity' => (float) $item->quantity,
                    ];
                })->values()->toArray(),
                'source_order_id' => null,
                'source_material_request_id' => $materialRequest->id,
            ];
        }

        return view('orders.create', compact('locations', 'products', 'prefillData'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'location_id' => ['required', 'exists:locations,id'],
            'material_request_id' => ['nullable', 'exists:material_requests,id'],
            'items' => ['nullable', 'array'],
            'items.*.product_id' => ['nullable', 'exists:products,id'],
            'items.*.product_variant_id' => ['nullable', 'exists:product_variants,id'],
            'items.*.quantity' => ['nullable', 'numeric', 'min:0'],
        ]);

        $data['items'] = collect($data['items'] ?? [])
            ->filter(function ($item) {
                return !empty($item['product_id'])
                    && isset($item['quantity'])
                    && (float) $item['quantity'] > 0;
            })
            ->values()
            ->toArray();

        try {
            $groupedItems = $this->groupItems($data['items']);

            DB::transaction(function () use ($data, $groupedItems) {
                $location = Location::with('route')->findOrFail($data['location_id']);

                $materialRequest = null;

                if (!empty($data['material_request_id'])) {
                    $materialRequest = MaterialRequest::lockForUpdate()
                        ->findOrFail((int) $data['material_request_id']);

                    if ($materialRequest->status !== 'pending') {
                        throw new RuntimeException('Esta solicitação não está mais pendente e não pode gerar pedido.');
                    }
                }

                $order = Order::create([
                    'user_id' => Auth::id(),
                    'route_id' => $location->route_id,
                    'location_id' => $location->id,
                    'order_date' => now()->toDateString(),
                    'status' => 'feito',
                ]);

                foreach ($groupedItems as $item) {
                    [$product, $variant] = $this->lockProductForStock(
                        $item['product_id'],
                        $item['product_variant_id']
                    );

                    $balanceBefore = $this->getCurrentBalance($product, $variant);
                    $quantity = (float) $item['quantity'];

                    if ($balanceBefore < $quantity) {
                        $nomeItem = $this->buildProductSnapshotName($product, $variant);
                        throw new RuntimeException("Estoque insuficiente para o item: {$nomeItem}. Disponível: {$this->formatStockNumber($balanceBefore)}.");
                    }

                    $this->applyStockDecrease($product, $variant, $quantity);

                    $balanceAfter = $this->getCurrentBalance(
                        $product->fresh(),
                        $variant?->fresh()
                    );

                    $order->items()->create([
                        'product_id' => $product->id,
                        'product_variant_id' => $variant?->id,
                        'quantity' => $quantity,
                        'product_name_snapshot' => $this->buildProductSnapshotName($product, $variant),
                        'unit_snapshot' => $product->unit?->name,
                    ]);

                    $notes = 'Baixa automática ao criar pedido.';
                    if ($materialRequest) {
                        $notes = 'Baixa automática ao criar pedido a partir da solicitação #' . $materialRequest->id . '.';
                    }

                    $this->createStockMovement(
                        product: $product,
                        variant: $variant,
                        type: 'exit',
                        quantity: $quantity,
                        balanceBefore: $balanceBefore,
                        balanceAfter: $balanceAfter,
                        referenceType: Order::class,
                        referenceId: $order->id,
                        notes: $notes
                    );
                }

                if ($materialRequest) {
                    $materialRequest->update([
                        'status' => 'approved',
                        'approved_by' => Auth::id(),
                        'approved_at' => now(),
                        'admin_notes' => 'Solicitação aprovada e convertida em pedido #' . $order->id . '.',
                    ]);
                }
            });
        } catch (RuntimeException $e) {
            return back()
                ->withErrors(['stock' => $e->getMessage()])
                ->withInput();
        }

        return redirect()
            ->route('orders.index')
            ->with('success', 'Pedido criado com sucesso.');
    }

    public function show(Order $order): View
    {
        $order->load([
            'user',
            'route',
            'location',
            'items.product.unit',
            'items.variant',
        ]);

        return view('orders.show', compact('order'));
    }

    public function edit(Order $order): View
    {
        $order->load([
            'items.product',
            'items.variant',
            'location.route',
        ]);

        $locations = Location::with('route')
            ->where('active', true)
            ->orderBy('name')
            ->get();

        $products = Product::with([
                'unit',
                'locations',
                'variants' => function ($query) {
                    $query->where('active', true)
                        ->orderBy('sort_order')
                        ->orderBy('name');
                }
            ])
            ->where('active', true)
            ->orderBy('name')
            ->get();

        return view('orders.edit', compact('order', 'locations', 'products'));
    }

    public function update(Request $request, Order $order): RedirectResponse
    {
        $data = $request->validate([
            'location_id' => ['required', 'exists:locations,id'],
            'items' => ['nullable', 'array'],
            'items.*.product_id' => ['nullable', 'exists:products,id'],
            'items.*.product_variant_id' => ['nullable', 'exists:product_variants,id'],
            'items.*.quantity' => ['nullable', 'numeric', 'min:0'],
        ]);

        $data['items'] = collect($data['items'] ?? [])
            ->filter(function ($item) {
                return !empty($item['product_id'])
                    && isset($item['quantity'])
                    && (float) $item['quantity'] > 0;
            })
            ->values()
            ->toArray();

        try {
            DB::transaction(function () use ($order, $data) {
                $order->load('items');

                $oldItems = [];
                foreach ($order->items as $item) {
                    $key = $this->makeItemKey($item->product_id, $item->product_variant_id);

                    $oldItems[$key] = [
                        'product_id' => $item->product_id,
                        'product_variant_id' => $item->product_variant_id,
                        'quantity' => (float) $item->quantity,
                    ];
                }

                $newItems = $this->groupItems($data['items']);
                $allKeys = array_unique(array_merge(array_keys($oldItems), array_keys($newItems)));

                foreach ($allKeys as $key) {
                    $oldQty = $oldItems[$key]['quantity'] ?? 0;
                    $newQty = $newItems[$key]['quantity'] ?? 0;
                    $difference = $newQty - $oldQty;

                    $productId = $newItems[$key]['product_id'] ?? $oldItems[$key]['product_id'];
                    $variantId = $newItems[$key]['product_variant_id'] ?? $oldItems[$key]['product_variant_id'];

                    [$product, $variant] = $this->lockProductForStock($productId, $variantId);

                    if ($difference > 0) {
                        $balanceBefore = $this->getCurrentBalance($product, $variant);

                        if ($balanceBefore < $difference) {
                            $nomeItem = $this->buildProductSnapshotName($product, $variant);
                            throw new RuntimeException("Estoque insuficiente para o item: {$nomeItem}. Disponível: {$this->formatStockNumber($balanceBefore)}.");
                        }

                        $this->applyStockDecrease($product, $variant, $difference);

                        $balanceAfter = $this->getCurrentBalance(
                            $product->fresh(),
                            $variant?->fresh()
                        );

                        $this->createStockMovement(
                            product: $product,
                            variant: $variant,
                            type: 'exit',
                            quantity: $difference,
                            balanceBefore: $balanceBefore,
                            balanceAfter: $balanceAfter,
                            referenceType: Order::class,
                            referenceId: $order->id,
                            notes: 'Baixa automática por aumento na edição do pedido.'
                        );
                    }

                    if ($difference < 0) {
                        $returnQty = abs($difference);
                        $balanceBefore = $this->getCurrentBalance($product, $variant);

                        $this->applyStockIncrease($product, $variant, $returnQty);

                        $balanceAfter = $this->getCurrentBalance(
                            $product->fresh(),
                            $variant?->fresh()
                        );

                        $this->createStockMovement(
                            product: $product,
                            variant: $variant,
                            type: 'entry',
                            quantity: $returnQty,
                            balanceBefore: $balanceBefore,
                            balanceAfter: $balanceAfter,
                            referenceType: Order::class,
                            referenceId: $order->id,
                            notes: 'Devolução automática por redução na edição do pedido.'
                        );
                    }
                }

                $location = Location::with('route')->findOrFail($data['location_id']);

                $order->update([
                    'route_id' => $location->route_id,
                    'location_id' => $location->id,
                    'order_date' => $order->order_date,
                ]);

                $order->items()->delete();

                foreach ($newItems as $item) {
                    $product = Product::with('unit')->findOrFail($item['product_id']);
                    $variant = $item['product_variant_id']
                        ? ProductVariant::find($item['product_variant_id'])
                        : null;

                    $order->items()->create([
                        'product_id' => $product->id,
                        'product_variant_id' => $variant?->id,
                        'quantity' => $item['quantity'],
                        'product_name_snapshot' => $this->buildProductSnapshotName($product, $variant),
                        'unit_snapshot' => $product->unit?->name,
                    ]);
                }
            });
        } catch (RuntimeException $e) {
            return back()
                ->withErrors(['stock' => $e->getMessage()])
                ->withInput();
        }

        return redirect()
            ->route('orders.index')
            ->with('success', 'Pedido atualizado com sucesso.');
    }

    public function destroy(Order $order): RedirectResponse
    {
        DB::transaction(function () use ($order) {
            $order->load('items');

            foreach ($order->items as $item) {
                [$product, $variant] = $this->lockProductForStock(
                    $item->product_id,
                    $item->product_variant_id
                );

                $quantity = (float) $item->quantity;
                $balanceBefore = $this->getCurrentBalance($product, $variant);

                $this->applyStockIncrease($product, $variant, $quantity);

                $balanceAfter = $this->getCurrentBalance(
                    $product->fresh(),
                    $variant?->fresh()
                );

                $this->createStockMovement(
                    product: $product,
                    variant: $variant,
                    type: 'entry',
                    quantity: $quantity,
                    balanceBefore: $balanceBefore,
                    balanceAfter: $balanceAfter,
                    referenceType: Order::class,
                    referenceId: $order->id,
                    notes: 'Devolução total de estoque ao excluir pedido.'
                );
            }

            $order->delete();
        });

        return redirect()
            ->route('orders.index')
            ->with('success', 'Pedido excluído com sucesso.');
    }

    public function repeatForm(Order $order): View
    {
        $order->load([
            'items.variant',
            'location.route',
        ]);

        $locations = Location::with('route')
            ->where('active', true)
            ->orderBy('scope')
            ->orderBy('name')
            ->get();

        $products = Product::with([
                'unit',
                'locations',
                'variants' => function ($query) {
                    $query->where('active', true)
                        ->orderBy('sort_order')
                        ->orderBy('name');
                }
            ])
            ->where('active', true)
            ->orderBy('name')
            ->get();

        $prefillItems = $order->items->map(function ($item) {
            return [
                'product_id' => $item->product_id,
                'product_variant_id' => $item->product_variant_id,
                'quantity' => (int) $item->quantity,
            ];
        })->values()->toArray();

        $prefillData = [
            'scope' => $order->location->scope ?? 'rota',
            'route_id' => $order->route_id ?? $order->location->route_id,
            'location_id' => $order->location_id,
            'items' => $prefillItems,
            'source_order_id' => $order->id,
            'source_material_request_id' => null,
        ];

        return view('orders.create', compact('locations', 'products', 'prefillData'));
    }

    private function groupItems(array $items): array
    {
        $grouped = [];

        foreach ($items as $item) {
            $productId = (int) $item['product_id'];
            $variantId = !empty($item['product_variant_id']) ? (int) $item['product_variant_id'] : null;
            $quantity = (float) $item['quantity'];

            $key = $this->makeItemKey($productId, $variantId);

            if (!isset($grouped[$key])) {
                $grouped[$key] = [
                    'product_id' => $productId,
                    'product_variant_id' => $variantId,
                    'quantity' => 0,
                ];
            }

            $grouped[$key]['quantity'] += $quantity;
        }

        return $grouped;
    }

    private function makeItemKey(int $productId, ?int $variantId): string
    {
        return $productId . ':' . ($variantId ?? 'null');
    }

    private function lockProductForStock(int $productId, ?int $variantId): array
    {
        $product = Product::lockForUpdate()->findOrFail($productId);
        $variant = null;

        if ($variantId) {
            $variant = ProductVariant::where('product_id', $productId)
                ->lockForUpdate()
                ->findOrFail($variantId);
        }

        return [$product, $variant];
    }

    private function getCurrentBalance(Product $product, ?ProductVariant $variant): float
    {
        return $variant
            ? (float) $variant->current_stock
            : (float) $product->current_stock;
    }

    private function applyStockDecrease(Product $product, ?ProductVariant $variant, float $quantity): void
    {
        if ($variant) {
            $variant->decrement('current_stock', $quantity);
            return;
        }

        $product->decrement('current_stock', $quantity);
    }

    private function applyStockIncrease(Product $product, ?ProductVariant $variant, float $quantity): void
    {
        if ($variant) {
            $variant->increment('current_stock', $quantity);
            return;
        }

        $product->increment('current_stock', $quantity);
    }

    private function createStockMovement(
        Product $product,
        ?ProductVariant $variant,
        string $type,
        float $quantity,
        float $balanceBefore,
        float $balanceAfter,
        string $referenceType,
        int $referenceId,
        ?string $notes = null
    ): void {
        StockMovement::create([
            'product_id' => $product->id,
            'product_variant_id' => $variant?->id,
            'stock_location_id' => null,
            'user_id' => Auth::id(),
            'movement_date' => now()->toDateString(),
            'type' => $type,
            'quantity' => $quantity,
            'balance_before' => $balanceBefore,
            'balance_after' => $balanceAfter,
            'document_number' => 'PED-' . str_pad((string) $referenceId, 6, '0', STR_PAD_LEFT),
            'source_name' => 'Pedido',
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'notes' => $notes,
        ]);
    }

    private function buildProductSnapshotName(Product $product, ?ProductVariant $variant): string
    {
        return $variant
            ? $product->name . ' - ' . $variant->name
            : $product->name;
    }

    private function formatStockNumber($value): string
    {
        $value = (float) $value;

        if (fmod($value, 1.0) === 0.0) {
            return (string) (int) $value;
        }

        return rtrim(rtrim(number_format($value, 3, '.', ''), '0'), '.');
    }
}