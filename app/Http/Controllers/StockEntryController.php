<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreStockEntryRequest;
use App\Models\Product;
use App\Services\StockService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class StockEntryController extends Controller
{
    public function index(): View
    {
        $products = Product::with(['unit', 'variants'])
            ->where('active', true)
            ->orderBy('name')
            ->get();

        return view('estoque_entradas.index', compact('products'));
    }

    public function store(StoreStockEntryRequest $request, StockService $stockService): RedirectResponse
    {
        $data = $request->validated();
        $data['type'] = 'entry';
        $data['stock_location_id'] = null;
        $data['reference_type'] = 'manual_entry';

        try {
            $stockService->move($data);

            return redirect()
                ->route('stock-entries.index')
                ->with('success', 'Entrada registrada com sucesso.');
        } catch (ValidationException $e) {
            return back()
                ->withInput()
                ->withErrors($e->errors());
        }
    }
}