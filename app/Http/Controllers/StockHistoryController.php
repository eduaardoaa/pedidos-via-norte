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

        $query = StockMovement::with(['product', 'variant', 'location', 'user'])
            ->whereBetween('movement_date', [$dateFrom, $dateTo]);

        if ($productId) {
            $query->where('product_id', $productId);
        }

        if ($locationId) {
            $query->where('stock_location_id', $locationId);
        }

        if ($type) {
            $query->where('type', $type);
        }

        $movements = $query
            ->orderByDesc('movement_date')
            ->orderByDesc('id')
            ->paginate(30)
            ->withQueryString();

        $summaryQuery = StockMovement::query()
            ->whereBetween('movement_date', [$dateFrom, $dateTo]);

        if ($productId) {
            $summaryQuery->where('product_id', $productId);
        }

        if ($locationId) {
            $summaryQuery->where('stock_location_id', $locationId);
        }

        if ($type) {
            $summaryQuery->where('type', $type);
        }

        $summary = $summaryQuery
            ->selectRaw("
                COALESCE(SUM(CASE WHEN type = 'entry' THEN quantity ELSE 0 END), 0) as total_entries,
                COALESCE(SUM(CASE WHEN type = 'exit' THEN quantity ELSE 0 END), 0) as total_exits,
                COALESCE(SUM(CASE WHEN type = 'adjustment' THEN quantity ELSE 0 END), 0) as total_adjustments
            ")
            ->first();

        $products = Product::where('active', true)->orderBy('name')->get();
        $locations = StockLocation::where('active', true)->orderBy('name')->get();

        return view('estoque.movimentacoes', compact(
            'movements',
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