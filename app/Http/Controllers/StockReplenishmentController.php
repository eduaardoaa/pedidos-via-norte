<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreStockReplenishmentRequest;
use App\Models\Product;
use App\Models\StockLocation;
use App\Services\StockService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StockReplenishmentController extends Controller
{
    public function index(Request $request): View
    {
        $locationId = $request->get('stock_location_id');
        $search = trim((string) $request->get('search'));

        $locations = StockLocation::where('active', true)
            ->orderBy('name')
            ->get();

        $products = collect();

        if ($locationId) {
            $products = Product::with(['unit', 'variants'])
                ->where('active', true)
                ->whereHas('locations', function ($query) use ($locationId) {
                    $query->where('stock_locations.id', $locationId);
                })
                ->when($search !== '', function ($query) use ($search) {
                    $query->where(function ($subQuery) use ($search) {
                        $subQuery->where('name', 'like', '%' . $search . '%')
                            ->orWhere('sku', 'like', '%' . $search . '%');
                    });
                })
                ->orderBy('name')
                ->get();
        }

        return view('estoque.reposicao', compact('locations', 'products', 'locationId', 'search'));
    }

    public function store(StoreStockReplenishmentRequest $request, StockService $stockService): RedirectResponse
    {
        $validated = $request->validated();

        $items = $validated['items'] ?? [];
        $movementDate = $validated['movement_date'];
        $documentNumber = $validated['document_number'] ?? null;
        $sourceName = $validated['source_name'] ?? null;
        $notes = $validated['notes'] ?? null;
        $processed = 0;

        foreach ($items as $item) {
            $quantity = (float) ($item['quantity'] ?? 0);

            if ($quantity <= 0) {
                continue;
            }

            $stockService->move([
                'product_id' => $item['product_id'],
                'product_variant_id' => $item['product_variant_id'] ?? null,
                'stock_location_id' => null,
                'type' => 'entry',
                'quantity' => $quantity,
                'movement_date' => $movementDate,
                'document_number' => $documentNumber,
                'source_name' => $sourceName,
                'reference_type' => 'replenishment',
                'notes' => $notes,
            ]);

            $processed++;
        }

        return redirect()
            ->route('stock-replenishment.index', [
                'stock_location_id' => $validated['stock_location_id'],
            ])
            ->with(
                'success',
                $processed > 0
                    ? "Reposição registrada com sucesso. {$processed} item(ns) atualizado(s)."
                    : 'Nenhum item foi informado para reposição.'
            );
    }
}