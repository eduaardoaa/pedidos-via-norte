<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreStockMovementRequest;
use App\Services\StockService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;

class ProductStockController extends Controller
{
    public function store(StoreStockMovementRequest $request, StockService $stockService): RedirectResponse
    {
        try {
            $stockService->move($request->validated());

            return redirect()
                ->back()
                ->with('success', 'Movimentação registrada com sucesso.');
        } catch (ValidationException $e) {
            return back()
                ->withInput()
                ->withErrors($e->errors());
        }
    }
}