<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductVariantRequest;
use App\Http\Requests\UpdateProductVariantRequest;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\StockService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class ProductVariantController extends Controller
{
    public function store(StoreProductVariantRequest $request, StockService $stockService): RedirectResponse
    {
        $validated = $request->validated();

        $product = Product::findOrFail($validated['product_id']);

        DB::transaction(function () use ($validated, $product, $stockService) {
            $variant = $product->variants()->create([
                'name' => $validated['name'],
                'sku' => $validated['sku'] ?? null,
                'current_stock' => 0,
                'sort_order' => $validated['sort_order'] ?? 0,
                'active' => $validated['active'],
            ]);

            $stockService->createInitialForVariant(
                $product,
                $variant,
                (float) ($validated['initial_stock'] ?? 0)
            );
        });

        return redirect()
            ->route('products.index')
            ->with('success', 'Variação cadastrada com sucesso.');
    }

    public function update(UpdateProductVariantRequest $request, ProductVariant $productVariant): RedirectResponse
    {
        $validated = $request->validated();

        $productVariant->update([
            'name' => $validated['name'],
            'sku' => $validated['sku'] ?? null,
            'sort_order' => $validated['sort_order'] ?? $productVariant->sort_order,
            'active' => $validated['active'],
        ]);

        return redirect()
            ->route('products.index')
            ->with('success', 'Variação atualizada com sucesso.');
    }

    public function destroy(ProductVariant $productVariant): RedirectResponse
    {
        if ($productVariant->movements()->exists()) {
            $productVariant->update([
                'active' => false,
            ]);

            return redirect()
                ->route('products.index')
                ->with('success', 'Variação inativada porque já possui movimentações.');
        }

        $productVariant->delete();

        return redirect()
            ->route('products.index')
            ->with('success', 'Variação removida com sucesso.');
    }
}