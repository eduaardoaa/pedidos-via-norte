<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockLocation;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StockHistoryController extends Controller
{
    public function index(Request $request): View
    {
        $dateFrom = $request->get('date_from', now()->toDateString());
        $dateTo = $request->get('date_to', now()->toDateString());
        $productId = $request->get('product_id');
        $locationId = $request->get('stock_location_id');
        $type = $request->get('type');

        $baseQuery = StockMovement::query()
            ->whereBetween('stock_movements.movement_date', [$dateFrom, $dateTo]);

        if ($productId) {
            $baseQuery->where('stock_movements.product_id', $productId);
        }

        if ($locationId) {
            $baseQuery->where('stock_movements.stock_location_id', $locationId);
        }

        if ($type) {
            $baseQuery->where('stock_movements.type', $type);
        }

        $summary = (clone $baseQuery)
            ->selectRaw("
                COALESCE(SUM(CASE WHEN stock_movements.type = 'entry' THEN stock_movements.quantity ELSE 0 END), 0) as total_entries,
                COALESCE(SUM(CASE WHEN stock_movements.type = 'exit' THEN stock_movements.quantity ELSE 0 END), 0) as total_exits,
                COALESCE(SUM(CASE WHEN stock_movements.type = 'adjustment' THEN stock_movements.quantity ELSE 0 END), 0) as total_adjustments
            ")
            ->first();

        $groupedBase = StockMovement::query()
            ->whereBetween('stock_movements.movement_date', [$dateFrom, $dateTo]);

        if ($productId) {
            $groupedBase->where('stock_movements.product_id', $productId);
        }

        if ($locationId) {
            $groupedBase->where('stock_movements.stock_location_id', $locationId);
        }

        if ($type) {
            $groupedBase->where('stock_movements.type', $type);
        }

        $firstMovements = StockMovement::query()
            ->selectRaw('MIN(stock_movements.id) as first_id, stock_movements.product_id, stock_movements.product_variant_id')
            ->whereBetween('stock_movements.movement_date', [$dateFrom, $dateTo]);

        if ($productId) {
            $firstMovements->where('stock_movements.product_id', $productId);
        }

        if ($locationId) {
            $firstMovements->where('stock_movements.stock_location_id', $locationId);
        }

        if ($type) {
            $firstMovements->where('stock_movements.type', $type);
        }

        $firstMovements->groupBy('stock_movements.product_id', 'stock_movements.product_variant_id');

        $lastMovements = StockMovement::query()
            ->selectRaw('MAX(stock_movements.id) as last_id, stock_movements.product_id, stock_movements.product_variant_id')
            ->whereBetween('stock_movements.movement_date', [$dateFrom, $dateTo]);

        if ($productId) {
            $lastMovements->where('stock_movements.product_id', $productId);
        }

        if ($locationId) {
            $lastMovements->where('stock_movements.stock_location_id', $locationId);
        }

        if ($type) {
            $lastMovements->where('stock_movements.type', $type);
        }

        $lastMovements->groupBy('stock_movements.product_id', 'stock_movements.product_variant_id');

        $rows = $groupedBase
            ->join('products', 'products.id', '=', 'stock_movements.product_id')
            ->leftJoin('product_variants', 'product_variants.id', '=', 'stock_movements.product_variant_id')
            ->leftJoinSub($firstMovements, 'first_movements', function ($join) {
                $join->on('first_movements.product_id', '=', 'stock_movements.product_id');

                $join->where(function ($query) {
                    $query->whereColumn('first_movements.product_variant_id', 'stock_movements.product_variant_id')
                        ->orWhere(function ($sub) {
                            $sub->whereNull('first_movements.product_variant_id')
                                ->whereNull('stock_movements.product_variant_id');
                        });
                });
            })
            ->leftJoinSub($lastMovements, 'last_movements', function ($join) {
                $join->on('last_movements.product_id', '=', 'stock_movements.product_id');

                $join->where(function ($query) {
                    $query->whereColumn('last_movements.product_variant_id', 'stock_movements.product_variant_id')
                        ->orWhere(function ($sub) {
                            $sub->whereNull('last_movements.product_variant_id')
                                ->whereNull('stock_movements.product_variant_id');
                        });
                });
            })
            ->leftJoin('stock_movements as first_stock_movement', 'first_stock_movement.id', '=', 'first_movements.first_id')
            ->leftJoin('stock_movements as last_stock_movement', 'last_stock_movement.id', '=', 'last_movements.last_id')
            ->groupBy(
                'stock_movements.product_id',
                'stock_movements.product_variant_id',
                'products.name',
                'product_variants.name',
                'first_stock_movement.balance_before',
                'last_stock_movement.balance_after'
            )
            ->selectRaw("
                stock_movements.product_id,
                stock_movements.product_variant_id,
                products.name as product_name,
                product_variants.name as variant_name,
                COALESCE(SUM(CASE WHEN stock_movements.type = 'entry' THEN stock_movements.quantity ELSE 0 END), 0) as total_entries,
                COALESCE(SUM(CASE WHEN stock_movements.type = 'exit' THEN stock_movements.quantity ELSE 0 END), 0) as total_exits,
                COALESCE(SUM(CASE WHEN stock_movements.type = 'adjustment' THEN stock_movements.quantity ELSE 0 END), 0) as total_adjustments,
                COALESCE(first_stock_movement.balance_before, 0) as opening_balance,
                COALESCE(last_stock_movement.balance_after, 0) as closing_balance
            ")
            ->orderBy('products.name')
            ->orderBy('product_variants.name')
            ->get();

        $products = Product::where('active', true)->orderBy('name')->get();
        $locations = StockLocation::where('active', true)->orderBy('name')->get();

        return view('estoque.movimentacoes', compact(
            'rows',
            'summary',
            'products',
            'locations',
            'dateFrom',
            'dateTo',
            'productId',
            'locationId',
            'type'
        ));
    }

    public function daily(Request $request): View
    {
        $date = $request->get('date', now()->toDateString());

        $rows = StockMovement::query()
            ->join('products', 'products.id', '=', 'stock_movements.product_id')
            ->leftJoin('product_variants', 'product_variants.id', '=', 'stock_movements.product_variant_id')
            ->whereDate('stock_movements.movement_date', $date)
            ->groupBy(
                'stock_movements.product_id',
                'stock_movements.product_variant_id',
                'products.name',
                'product_variants.name'
            )
            ->selectRaw("
                stock_movements.product_id,
                stock_movements.product_variant_id,
                products.name as product_name,
                product_variants.name as variant_name,
                COALESCE(SUM(CASE WHEN stock_movements.type = 'entry' THEN stock_movements.quantity ELSE 0 END), 0) as total_entries,
                COALESCE(SUM(CASE WHEN stock_movements.type = 'exit' THEN stock_movements.quantity ELSE 0 END), 0) as total_exits,
                MIN(stock_movements.balance_before) as previous_balance,
                MAX(stock_movements.balance_after) as current_balance
            ")
            ->orderBy('products.name')
            ->orderBy('product_variants.name')
            ->get();

        $cards = [
            'entries' => $rows->sum('total_entries'),
            'exits' => $rows->sum('total_exits'),
            'count' => $rows->count(),
        ];

        return view('estoque.resumo_diario', compact('rows', 'date', 'cards'));
    }
}