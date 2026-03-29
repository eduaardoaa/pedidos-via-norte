<?php

namespace App\Http\Controllers;

use App\Models\Cargo;
use App\Models\Employee;
use App\Models\EpiDelivery;
use App\Models\EpiDeliveryItem;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\StockMovement;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class EpiDeliveryController extends Controller
{
    public function index(Request $request)
    {
        $query = EpiDelivery::with([
            'employee.cargo',
            'items.product',
            'items.variant',
            'user',
        ]);

        if ($request->filled('search')) {
            $search = trim($request->search);

            $query->whereHas('employee', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('cpf', 'like', "%{$search}%")
                    ->orWhere('registration', 'like', "%{$search}%");
            });
        }

        if ($request->filled('cargo_id')) {
            $query->whereHas('employee', function ($q) use ($request) {
                $q->where('cargo_id', $request->cargo_id);
            });
        }

        if ($request->filled('period')) {
            $query->whereRaw("DATE_FORMAT(delivery_date, '%Y-%m') = ?", [$request->period]);
        }

        $deliveries = $query
            ->orderByDesc('delivery_date')
            ->paginate(15)
            ->withQueryString();

        $cargos = Cargo::where('ativo', 1)
            ->orderBy('id')
            ->get();

        $availablePeriods = EpiDelivery::query()
            ->whereNotNull('delivery_date')
            ->select('delivery_date')
            ->get()
            ->map(function ($delivery) {
                $date = Carbon::parse($delivery->delivery_date);

                return [
                    'value' => $date->format('Y-m'),
                    'label' => $date->translatedFormat('F/Y'),
                    'sort' => $date->format('Y-m'),
                ];
            })
            ->unique('value')
            ->sortByDesc('sort')
            ->values();

        return view('epi-deliveries.index', compact('deliveries', 'cargos', 'availablePeriods'));
    }

    public function create(Request $request)
    {
        $employees = Employee::with('cargo')
            ->where('active', true)
            ->orderBy('name')
            ->get();

        $products = Product::with('variants')
            ->orderBy('name')
            ->get();

        $selectedEmployeeId = $request->get('employee_id');

        return view('epi-deliveries.create', compact('employees', 'products', 'selectedEmployeeId'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'employee_id' => ['required', 'exists:employees,id'],
            'delivery_date' => ['required', 'date'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.product_variant_id' => ['nullable', 'exists:product_variants,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.next_expected_date' => ['nullable', 'date'],
        ]);

        foreach ($request->items as $index => $item) {
            $product = Product::with('variants')->find($item['product_id'] ?? null);

            if (!$product) {
                continue;
            }

            $hasVariants = $product->variants && $product->variants->count() > 0;

            if ($hasVariants && empty($item['product_variant_id'])) {
                return back()
                    ->withErrors([
                        "items.$index.product_variant_id" => "Selecione a variação do produto {$product->name}.",
                    ])
                    ->withInput();
            }
        }

        try {
            DB::transaction(function () use ($request) {
                $delivery = EpiDelivery::create([
                    'employee_id' => $request->employee_id,
                    'delivery_date' => $request->delivery_date,
                    'notes' => $request->notes,
                    'created_by' => Auth::id(),
                ]);

                foreach ($request->items as $item) {
                    [$product, $variant] = $this->lockProductForStock(
                        (int) $item['product_id'],
                        !empty($item['product_variant_id']) ? (int) $item['product_variant_id'] : null
                    );

                    $quantity = (float) $item['quantity'];
                    $balanceBefore = $this->getCurrentBalance($product, $variant);

                    if ($balanceBefore < $quantity) {
                        $nomeItem = $variant
                            ? $product->name . ' - ' . $variant->name
                            : $product->name;

                        throw new RuntimeException(
                            "Estoque insuficiente para o item: {$nomeItem}. Disponível: {$this->formatStockNumber($balanceBefore)}."
                        );
                    }

                    $this->applyStockDecrease($product, $variant, $quantity);

                    $balanceAfter = $this->getCurrentBalance(
                        $product->fresh(),
                        $variant?->fresh()
                    );

                    EpiDeliveryItem::create([
                        'epi_delivery_id' => $delivery->id,
                        'product_id' => $product->id,
                        'product_variant_id' => $variant?->id,
                        'quantity' => $quantity,
                        'next_expected_date' => $item['next_expected_date'] ?? null,
                    ]);

                    $this->createStockMovement(
                        product: $product,
                        variant: $variant,
                        type: 'exit',
                        quantity: $quantity,
                        balanceBefore: $balanceBefore,
                        balanceAfter: $balanceAfter,
                        referenceType: EpiDelivery::class,
                        referenceId: $delivery->id,
                        notes: 'Baixa automática ao registrar entrega de EPI.'
                    );
                }
            });
        } catch (RuntimeException $e) {
            return back()
                ->withErrors(['stock' => $e->getMessage()])
                ->withInput();
        }

        return redirect()
            ->route('epi-deliveries.index')
            ->with('success', 'Entrega de EPI registrada com sucesso!');
    }

    public function quickView(EpiDelivery $delivery): JsonResponse
    {
        $delivery->load([
            'employee.cargo',
            'items.product',
            'items.variant',
            'user',
        ]);

        return response()->json([
            'id' => $delivery->id,
            'titulo' => 'Entrega de EPI #' . $delivery->id,
            'subtitulo' => 'Visualização rápida da entrega',
            'tipo' => 'Almoxarifado',
            'local' => 'Material entregue para: ' . ($delivery->employee->name ?? '-'),
            'rota' => '-',
            'usuario' => $delivery->user->name ?? '-',
            'data' => optional($delivery->delivery_date)->format('d/m/Y'),
            'status' => 'Entregue',
            'total_itens' => $delivery->items->count(),
            'total_unidades' => $this->formatStockNumber((float) $delivery->items->sum('quantity')),
            'edit_url' => null,
            'itens' => $delivery->items->map(function ($item) {
                return [
                    'produto' => $item->product->name ?? '-',
                    'variacao' => $item->variant->name ?? '-',
                    'quantidade' => $this->formatStockNumber((float) $item->quantity),
                    'unidade' => $item->product->unit->name ?? 'un',
                ];
            })->values()->toArray(),
        ]);
    }

    public function employeeHistory(Employee $employee)
    {
        $employee->load([
            'cargo',
            'epiDeliveries.items.product',
            'epiDeliveries.items.variant',
            'epiDeliveries.user',
        ]);

        $lastDeliveryDate = $employee->epiDeliveries
            ->sortByDesc('delivery_date')
            ->first()?->delivery_date;

        $allItems = $employee->epiDeliveries
            ->flatMap(function ($delivery) {
                return $delivery->items->map(function ($item) use ($delivery) {
                    $item->delivery_date = $delivery->delivery_date;
                    $item->registered_by = $delivery->user->name ?? '-';
                    return $item;
                });
            })
            ->sortByDesc(function ($item) {
                return Carbon::parse($item->delivery_date)->timestamp;
            })
            ->values();

        $latestPendingItems = $this->getLatestPendingItemsFromEmployee($employee);

        $nextItem = $latestPendingItems
            ->filter(fn ($item) => !empty($item->next_expected_date))
            ->sortBy('next_expected_date')
            ->first();

        return view('epi.employee-history', [
            'employee' => $employee,
            'lastDeliveryDate' => $lastDeliveryDate,
            'nextItem' => $nextItem,
            'historyItems' => $allItems,
        ]);
    }

    public function show(EpiDelivery $epiDelivery)
    {
        $epiDelivery->load([
            'employee.cargo',
            'items.product',
            'items.variant',
            'user',
        ]);

        return view('epi-deliveries.show', compact('epiDelivery'));
    }

    public function dashboard(Request $request)
    {
        $employeesQuery = Employee::with([
            'cargo',
            'epiDeliveries.items.product',
            'epiDeliveries.items.variant',
        ])->where('active', true);

        if ($request->filled('search')) {
            $search = trim($request->search);

            $employeesQuery->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('registration', 'like', "%{$search}%")
                    ->orWhere('cpf', 'like', "%{$search}%");
            });
        }

        if ($request->filled('cargo_id')) {
            $employeesQuery->where('cargo_id', $request->cargo_id);
        }

        $selectedPeriod = $request->period;
        $dueStatus = $request->due_status;
        $today = Carbon::today()->startOfDay();

        $employees = $employeesQuery
            ->orderBy('name')
            ->get()
            ->map(function ($employee) use ($selectedPeriod) {
                $lastDeliveryDate = $employee->epiDeliveries
                    ->sortByDesc('delivery_date')
                    ->first()?->delivery_date;

                $latestPendingItems = $this->getLatestPendingItemsFromEmployee($employee);

                if (!empty($selectedPeriod)) {
                    $latestPendingItems = $latestPendingItems->filter(function ($item) use ($selectedPeriod) {
                        if (empty($item->next_expected_date)) {
                            return false;
                        }

                        return Carbon::parse($item->next_expected_date)->format('Y-m') === $selectedPeriod;
                    });
                }

                $nextItem = $latestPendingItems
                    ->filter(fn ($item) => !empty($item->next_expected_date))
                    ->sortBy('next_expected_date')
                    ->first();

                $productName = $nextItem?->product?->name ?? '';
                $variantName = $nextItem?->variant?->name ?? '';

                $employee->last_delivery_date = $lastDeliveryDate;
                $employee->next_expected_date = $nextItem?->next_expected_date;
                $employee->next_item_name = trim($productName . ' ' . $variantName);

                return $employee;
            })
            ->filter(function ($employee) use ($selectedPeriod, $dueStatus, $today) {
                $nextDate = !empty($employee->next_expected_date)
                    ? Carbon::parse($employee->next_expected_date)->startOfDay()
                    : null;

                if (!empty($selectedPeriod) && empty($employee->next_expected_date)) {
                    return false;
                }

                if (empty($dueStatus)) {
                    return true;
                }

                return match ($dueStatus) {
                    'overdue' => $nextDate && $nextDate->lt($today),
                    'today' => $nextDate && $nextDate->isSameDay($today),
                    'upcoming' => $nextDate && $nextDate->gt($today),
                    'no_date' => empty($nextDate),
                    default => true,
                };
            })
            ->sort(function ($a, $b) use ($today) {
                $getPriority = function ($employee) use ($today) {
                    if (empty($employee->next_expected_date)) {
                        return 3;
                    }

                    $date = Carbon::parse($employee->next_expected_date)->startOfDay();

                    if ($date->lt($today)) {
                        return 0;
                    }

                    if ($date->isSameDay($today)) {
                        return 1;
                    }

                    return 2;
                };

                $priorityA = $getPriority($a);
                $priorityB = $getPriority($b);

                if ($priorityA !== $priorityB) {
                    return $priorityA <=> $priorityB;
                }

                if (!empty($a->next_expected_date) && !empty($b->next_expected_date)) {
                    $dateCompare = Carbon::parse($a->next_expected_date)->timestamp <=> Carbon::parse($b->next_expected_date)->timestamp;

                    if ($dateCompare !== 0) {
                        return $dateCompare;
                    }
                }

                return strcmp($a->name, $b->name);
            })
            ->values();

        $cargos = Cargo::where('ativo', 1)
            ->orderBy('id')
            ->get();

        $availablePeriods = EpiDeliveryItem::query()
            ->whereNotNull('next_expected_date')
            ->select('next_expected_date')
            ->get()
            ->map(function ($item) {
                $date = Carbon::parse($item->next_expected_date);

                return [
                    'value' => $date->format('Y-m'),
                    'label' => $date->translatedFormat('F/Y'),
                    'sort' => $date->format('Y-m'),
                ];
            })
            ->unique('value')
            ->sortBy('sort')
            ->values();

        return view('epi.index', compact('employees', 'cargos', 'availablePeriods'));
    }

    private function getLatestPendingItemsFromEmployee(Employee $employee)
    {
        return $employee->epiDeliveries
            ->flatMap(function ($delivery) {
                return $delivery->items->map(function ($item) use ($delivery) {
                    $item->delivery_date_ref = $delivery->delivery_date;
                    return $item;
                });
            })
            ->groupBy(function ($item) {
                return (string) $item->product_id;
            })
            ->map(function ($group) {
                return $group
                    ->sortByDesc(function ($item) {
                        return Carbon::parse($item->delivery_date_ref)->timestamp;
                    })
                    ->first();
            })
            ->values();
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
            'document_number' => 'EPI-' . str_pad((string) $referenceId, 6, '0', STR_PAD_LEFT),
            'source_name' => 'Entrega de EPI',
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'notes' => $notes,
        ]);
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