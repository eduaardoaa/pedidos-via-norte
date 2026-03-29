<?php

namespace App\Http\Controllers;

use App\Models\EpiDelivery;
use App\Models\Order;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user()?->loadMissing('cargo:id,codigo');

        if (!$user || (($user->cargo->codigo ?? null) !== 'admin')) {
            abort(403);
        }

        $cacheKey = 'dashboard.admin.v3';

        $data = Cache::remember($cacheKey, now()->addMinutes(5), function () {
            return $this->buildDashboardData();
        });

        return view('dashboard.admin', $data);
    }

    private function buildDashboardData(): array
    {
        $today = Carbon::today();
        $endOfToday = $today->copy()->endOfDay();

        $startOfThisWeek = $today->copy()->startOfWeek()->startOfDay();
        $endOfThisWeek = $today->copy()->endOfWeek()->endOfDay();

        $startOfLastWeek = $today->copy()->subWeek()->startOfWeek()->startOfDay();
        $endOfLastWeek = $today->copy()->subWeek()->endOfWeek()->endOfDay();

        $startOfCurrentMonth = $today->copy()->startOfMonth()->startOfDay();
        $endOfCurrentMonth = $today->copy()->endOfMonth()->endOfDay();

        $last7DaysStart = $today->copy()->subDays(6)->startOfDay();
        $next7DaysEnd = $today->copy()->addDays(7)->endOfDay();

        /*
        |--------------------------------------------------------------------------
        | PEDIDOS - KPIs EM UMA CONSULTA
        |--------------------------------------------------------------------------
        */
        $ordersKpis = DB::table('orders')
            ->selectRaw(
                '
                SUM(CASE WHEN created_at BETWEEN ? AND ? THEN 1 ELSE 0 END) as orders_this_week,
                SUM(CASE WHEN created_at BETWEEN ? AND ? THEN 1 ELSE 0 END) as orders_last_week,
                SUM(CASE WHEN created_at BETWEEN ? AND ? THEN 1 ELSE 0 END) as orders_this_month
                ',
                [
                    $startOfThisWeek,
                    $endOfThisWeek,
                    $startOfLastWeek,
                    $endOfLastWeek,
                    $startOfCurrentMonth,
                    $endOfCurrentMonth,
                ]
            )
            ->first();

        $ordersThisWeek = (int) ($ordersKpis->orders_this_week ?? 0);
        $ordersLastWeek = (int) ($ordersKpis->orders_last_week ?? 0);
        $ordersThisMonth = (int) ($ordersKpis->orders_this_month ?? 0);

        $ordersVariationPercent = 0;

        if ($ordersLastWeek > 0) {
            $ordersVariationPercent = round((($ordersThisWeek - $ordersLastWeek) / $ordersLastWeek) * 100, 1);
        } elseif ($ordersThisWeek > 0) {
            $ordersVariationPercent = 100;
        }

        /*
        |--------------------------------------------------------------------------
        | PRODUTOS / ESTOQUE
        |--------------------------------------------------------------------------
        */
        $products = Product::query()
            ->select([
                'id',
                'name',
                'uses_variants',
                'current_stock',
            ])
            ->with([
                'variants:id,product_id,name,current_stock',
                'locations:id,name',
            ])
            ->get();

        $productsById = $products->keyBy('id');

        $lowStockRota = collect();
        $lowStockAlmoxarifado = collect();
        $zeroStockItems = collect();

        foreach ($products as $product) {
            $locationNames = $product->locations
                ->pluck('name')
                ->filter()
                ->map(fn ($name) => strtolower(trim($name)))
                ->values();

            $isRota = $locationNames->contains('rota');
            $isAlmoxarifado = $locationNames->contains('almoxarifado');

            if ($product->uses_variants && $product->variants->isNotEmpty()) {
                foreach ($product->variants as $variant) {
                    $stock = $this->getVariantRealStock($variant);

                    $row = [
                        'product_id' => $product->id,
                        'product_name' => $product->name,
                        'variant_id' => $variant->id,
                        'variant_name' => $variant->name,
                        'stock' => $stock,
                    ];

                    if ($stock < 50) {
                        if ($isRota) {
                            $lowStockRota->push($row);
                        }

                        if ($isAlmoxarifado) {
                            $lowStockAlmoxarifado->push($row);
                        }
                    }

                    if ($stock <= 0) {
                        $zeroStockItems->push($row + [
                            'type' => $isAlmoxarifado ? 'Almoxarifado' : ($isRota ? 'Rota' : 'Sem local'),
                        ]);
                    }
                }
            } else {
                $stock = $this->getProductRealStock($product);

                $row = [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'variant_id' => null,
                    'variant_name' => null,
                    'stock' => $stock,
                ];

                if ($stock < 50) {
                    if ($isRota) {
                        $lowStockRota->push($row);
                    }

                    if ($isAlmoxarifado) {
                        $lowStockAlmoxarifado->push($row);
                    }
                }

                if ($stock <= 0) {
                    $zeroStockItems->push($row + [
                        'type' => $isAlmoxarifado ? 'Almoxarifado' : ($isRota ? 'Rota' : 'Sem local'),
                    ]);
                }
            }
        }

        $lowStockRota = $lowStockRota->sortBy('stock')->values();
        $lowStockAlmoxarifado = $lowStockAlmoxarifado->sortBy('stock')->values();
        $zeroStockItems = $zeroStockItems->sortBy('product_name')->values();

        /*
        |--------------------------------------------------------------------------
        | CONSUMO ÚLTIMOS 7 DIAS
        |--------------------------------------------------------------------------
        */
        $consumptionLast7Days = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->whereBetween('orders.created_at', [$last7DaysStart, $endOfToday])
            ->select(
                'order_items.product_id',
                'order_items.product_variant_id',
                DB::raw('SUM(order_items.quantity) as total_quantity')
            )
            ->groupBy('order_items.product_id', 'order_items.product_variant_id')
            ->get();

        $stockCoverage = collect();
        $topOrderedItems = collect();

        foreach ($consumptionLast7Days as $item) {
            $product = $productsById->get($item->product_id);

            if (!$product) {
                continue;
            }

            $variant = null;
            $currentStock = 0;

            if (!empty($item->product_variant_id)) {
                $variant = $product->variants->firstWhere('id', $item->product_variant_id);

                if (!$variant) {
                    continue;
                }

                $currentStock = $this->getVariantRealStock($variant);
            } else {
                $currentStock = $this->getProductRealStock($product);
            }

            $total7Days = (float) $item->total_quantity;
            $avgDaily = round($total7Days / 7, 2);
            $coverageDays = $avgDaily > 0 ? (int) floor($currentStock / $avgDaily) : null;

            $row = [
                'product_name' => $product->name,
                'variant_name' => $variant->name ?? null,
                'stock' => $currentStock,
                'total_7_days' => $total7Days,
                'avg_daily' => $avgDaily,
                'coverage_days' => $coverageDays,
            ];

            $stockCoverage->push($row);
            $topOrderedItems->push($row);
        }

        $stockCoverage = $stockCoverage
            ->sortBy(fn ($item) => $item['coverage_days'] ?? 999999)
            ->values();

        $topOrderedItems = $topOrderedItems
            ->sortByDesc('total_7_days')
            ->values();

        /*
        |--------------------------------------------------------------------------
        | TOP PRODUTOS MAIS CONSUMIDOS NO MÊS
        |--------------------------------------------------------------------------
        */
        $topOrderedItemsMonthRaw = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->whereBetween('orders.created_at', [$startOfCurrentMonth, $endOfCurrentMonth])
            ->select(
                'order_items.product_id',
                'order_items.product_variant_id',
                DB::raw('SUM(order_items.quantity) as total_quantity')
            )
            ->groupBy('order_items.product_id', 'order_items.product_variant_id')
            ->get();

        $topOrderedItemsMonth = collect();

        foreach ($topOrderedItemsMonthRaw as $item) {
            $product = $productsById->get($item->product_id);

            if (!$product) {
                continue;
            }

            $variant = null;

            if (!empty($item->product_variant_id)) {
                $variant = $product->variants->firstWhere('id', $item->product_variant_id);
            }

            $topOrderedItemsMonth->push([
                'product_name' => $product->name,
                'variant_name' => $variant->name ?? null,
                'total_month' => (float) $item->total_quantity,
            ]);
        }

        $topOrderedItemsMonth = $topOrderedItemsMonth
            ->sortByDesc('total_month')
            ->values();

        /*
        |--------------------------------------------------------------------------
        | GRÁFICOS - PEDIDOS E ITENS SAÍDOS POR DIA (7 DIAS)
        |--------------------------------------------------------------------------
        */
        $ordersByDayRaw = DB::table('orders')
            ->whereBetween('created_at', [$last7DaysStart, $endOfToday])
            ->selectRaw('DATE(created_at) as day, COUNT(*) as total')
            ->groupBy('day')
            ->pluck('total', 'day');

        $itemsOutByDayRaw = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->whereBetween('orders.created_at', [$last7DaysStart, $endOfToday])
            ->selectRaw('DATE(orders.created_at) as day, SUM(order_items.quantity) as total')
            ->groupBy('day')
            ->pluck('total', 'day');

        $ordersByDay = collect();
        $itemsOutByDay = collect();

        for ($i = 6; $i >= 0; $i--) {
            $day = $today->copy()->subDays($i)->toDateString();

            $ordersByDay->push([
                'label' => Carbon::parse($day)->format('d/m'),
                'value' => (int) ($ordersByDayRaw[$day] ?? 0),
            ]);

            $itemsOutByDay->push([
                'label' => Carbon::parse($day)->format('d/m'),
                'value' => (float) ($itemsOutByDayRaw[$day] ?? 0),
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | EPI - KPIs E DADOS DO MÊS
        |--------------------------------------------------------------------------
        */
        $epiDeliveriesThisMonth = EpiDelivery::query()
            ->whereBetween('delivery_date', [$startOfCurrentMonth, $endOfCurrentMonth])
            ->count();

        $epiItemsDueThisMonth = DB::table('epi_delivery_items')
            ->leftJoin('epi_deliveries', 'epi_deliveries.id', '=', 'epi_delivery_items.epi_delivery_id')
            ->leftJoin('products', 'products.id', '=', 'epi_delivery_items.product_id')
            ->leftJoin('product_variants', 'product_variants.id', '=', 'epi_delivery_items.product_variant_id')
            ->whereBetween('epi_delivery_items.next_expected_date', [$startOfCurrentMonth, $endOfCurrentMonth])
            ->select([
                'epi_delivery_items.product_id',
                'epi_delivery_items.product_variant_id',
                'epi_delivery_items.quantity',
                'products.name as product_name',
                'products.current_stock as product_current_stock',
                'product_variants.name as variant_name',
                'product_variants.current_stock as variant_current_stock',
            ])
            ->get();

        $employeesDueThisMonth = DB::table('epi_delivery_items')
            ->join('epi_deliveries', 'epi_deliveries.id', '=', 'epi_delivery_items.epi_delivery_id')
            ->whereBetween('epi_delivery_items.next_expected_date', [$startOfCurrentMonth, $endOfCurrentMonth])
            ->distinct()
            ->count('epi_deliveries.employee_id');

        $employeesWithOverdueEpi = DB::table('epi_delivery_items')
            ->join('epi_deliveries', 'epi_deliveries.id', '=', 'epi_delivery_items.epi_delivery_id')
            ->whereNotNull('epi_delivery_items.next_expected_date')
            ->whereDate('epi_delivery_items.next_expected_date', '<', $today->toDateString())
            ->distinct()
            ->count('epi_deliveries.employee_id');

        $upcomingEpi7Days = DB::table('epi_delivery_items')
            ->leftJoin('epi_deliveries', 'epi_deliveries.id', '=', 'epi_delivery_items.epi_delivery_id')
            ->leftJoin('employees', 'employees.id', '=', 'epi_deliveries.employee_id')
            ->leftJoin('products', 'products.id', '=', 'epi_delivery_items.product_id')
            ->leftJoin('product_variants', 'product_variants.id', '=', 'epi_delivery_items.product_variant_id')
            ->whereNotNull('epi_delivery_items.next_expected_date')
            ->whereBetween('epi_delivery_items.next_expected_date', [$today->copy()->startOfDay(), $next7DaysEnd])
            ->orderBy('epi_delivery_items.next_expected_date')
            ->select([
                'employees.name as employee_name',
                'products.name as product_name',
                'product_variants.name as variant_name',
                'epi_delivery_items.quantity',
                'epi_delivery_items.next_expected_date',
            ])
            ->limit(50)
            ->get()
            ->map(fn ($item) => [
                'employee_name' => $item->employee_name ?? '-',
                'product_name' => $item->product_name ?? '-',
                'variant_name' => $item->variant_name,
                'quantity' => (float) $item->quantity,
                'next_expected_date' => $item->next_expected_date,
            ])
            ->values();

        /*
        |--------------------------------------------------------------------------
        | NECESSIDADE MÍNIMA DE EPI NO MÊS
        |--------------------------------------------------------------------------
        */
        $monthlyEpiNeeds = $epiItemsDueThisMonth
            ->groupBy(fn ($item) => ($item->product_id ?? '0') . '-' . ($item->product_variant_id ?? '0'))
            ->map(function ($items) {
                $first = $items->first();

                $neededQuantity = (float) $items->sum('quantity');

                $currentStock = $first->product_variant_id
                    ? (float) ($first->variant_current_stock ?? 0)
                    : (float) ($first->product_current_stock ?? 0);

                return [
                    'product_name' => $first->product_name ?? '-',
                    'variant_name' => $first->variant_name ?? null,
                    'needed_quantity' => $neededQuantity,
                    'current_stock' => $currentStock,
                    'difference' => $currentStock - $neededQuantity,
                ];
            })
            ->sortBy('difference')
            ->values();

        $epiRiskItems = $monthlyEpiNeeds
            ->filter(fn ($item) => $item['difference'] < 0)
            ->values();

        $topEpiItemsThisMonth = $monthlyEpiNeeds
            ->sortByDesc('needed_quantity')
            ->values();

        $purchaseRecommendations = $epiRiskItems
            ->map(fn ($item) => [
                'product_name' => $item['product_name'],
                'variant_name' => $item['variant_name'],
                'buy_quantity' => abs($item['difference']),
                'current_stock' => $item['current_stock'],
                'needed_quantity' => $item['needed_quantity'],
            ])
            ->sortByDesc('buy_quantity')
            ->values();

        /*
        |--------------------------------------------------------------------------
        | ALERTA DE MUDANÇA ANORMAL POR LOCAL / ITEM
        |--------------------------------------------------------------------------
        */
        $consumptionAnomalies = $this->detectConsumptionAnomalies();

        /*
        |--------------------------------------------------------------------------
        | ALERTAS CRÍTICOS
        |--------------------------------------------------------------------------
        */
        $criticalAlerts = collect();

        if ($epiRiskItems->count() > 0) {
            $criticalAlerts->push("Existem {$epiRiskItems->count()} itens de EPI com risco de falta neste mês.");
        }

        if ($zeroStockItems->count() > 0) {
            $criticalAlerts->push("Existem {$zeroStockItems->count()} itens zerados no estoque.");
        }

        if ($employeesWithOverdueEpi > 0) {
            $criticalAlerts->push("Existem {$employeesWithOverdueEpi} funcionário(s) com EPI atrasado.");
        }

        if ($consumptionAnomalies->count() > 0) {
            $criticalAlerts->push("Foram detectadas {$consumptionAnomalies->count()} anomalias de consumo em pedidos recentes.");
        }

        return compact(
            'ordersThisWeek',
            'ordersLastWeek',
            'ordersThisMonth',
            'ordersVariationPercent',
            'lowStockRota',
            'lowStockAlmoxarifado',
            'zeroStockItems',
            'stockCoverage',
            'topOrderedItems',
            'topOrderedItemsMonth',
            'ordersByDay',
            'itemsOutByDay',
            'epiDeliveriesThisMonth',
            'employeesDueThisMonth',
            'employeesWithOverdueEpi',
            'monthlyEpiNeeds',
            'epiRiskItems',
            'topEpiItemsThisMonth',
            'purchaseRecommendations',
            'upcomingEpi7Days',
            'consumptionAnomalies',
            'criticalAlerts'
        );
    }

    private function detectConsumptionAnomalies(): Collection
    {
        return Cache::remember('dashboard.admin.consumption_anomalies.v3', now()->addMinutes(10), function () {
            $orders = DB::table('orders')
                ->leftJoin('locations', 'locations.id', '=', 'orders.location_id')
                ->select([
                    'orders.id',
                    'orders.location_id',
                    'orders.created_at',
                    'locations.name as location_name',
                ])
                ->orderByDesc('orders.created_at')
                ->limit(120)
                ->get();

            if ($orders->isEmpty()) {
                return collect();
            }

            $orderIds = $orders->pluck('id')->values();

            $items = DB::table('order_items')
                ->whereIn('order_id', $orderIds)
                ->select([
                    'order_id',
                    'product_id',
                    'product_variant_id',
                    'quantity',
                ])
                ->get();

            $productIds = $items->pluck('product_id')->filter()->unique()->values();

            $products = Product::query()
                ->select(['id', 'name'])
                ->with(['variants:id,product_id,name'])
                ->whereIn('id', $productIds)
                ->get()
                ->keyBy('id');

            $itemsByOrder = $items->groupBy('order_id');
            $history = [];
            $alerts = collect();

            foreach ($orders->sortBy('created_at') as $order) {
                $orderItems = $itemsByOrder->get($order->id, collect());

                foreach ($orderItems as $item) {
                    $key = ($order->location_id ?? '0') . '-' . ($item->product_id ?? '0') . '-' . ($item->product_variant_id ?? '0');
                    $qty = (float) $item->quantity;

                    $previous = $history[$key] ?? [];

                    if (count($previous) >= 3) {
                        $avg = array_sum($previous) / count($previous);

                        if ($avg > 0 && $qty >= ($avg * 3) && $qty >= 20) {
                            $product = $products->get($item->product_id);
                            $variant = $product?->variants?->firstWhere('id', $item->product_variant_id);

                            $alerts->push([
                                'location_name' => $order->location_name ?? 'Sem local',
                                'product_name' => $product->name ?? ('Produto #' . $item->product_id),
                                'variant_name' => $variant->name ?? null,
                                'current_qty' => $qty,
                                'avg_qty' => round($avg, 2),
                                'created_at' => $order->created_at,
                            ]);
                        }
                    }

                    $history[$key][] = $qty;

                    if (count($history[$key]) > 3) {
                        array_shift($history[$key]);
                    }
                }
            }

            return $alerts->sortByDesc('created_at')->values();
        });
    }

    private function getProductRealStock($product): float
    {
        if (isset($product->current_stock)) {
            return (float) $product->current_stock;
        }

        if (isset($product->stock)) {
            return (float) $product->stock;
        }

        if (isset($product->total_stock)) {
            return (float) $product->total_stock;
        }

        return 0;
    }

    private function getVariantRealStock($variant): float
    {
        if (isset($variant->current_stock)) {
            return (float) $variant->current_stock;
        }

        if (isset($variant->stock)) {
            return (float) $variant->stock;
        }

        if (isset($variant->formatted_stock)) {
            return (float) str_replace(',', '.', preg_replace('/[^\d,.\-]/', '', (string) $variant->formatted_stock));
        }

        return 0;
    }
}