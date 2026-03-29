<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StockService
{
    public function move(array $data): StockMovement|null
    {
        return DB::transaction(function () use ($data) {
            $product = Product::with(['locations', 'variants'])->findOrFail($data['product_id']);

            $type = (string) $data['type'];
            $locationId = $data['stock_location_id'] ?? null;
            $variantId = $data['product_variant_id'] ?? null;

            if ($type === 'exit' && ! $locationId) {
                throw ValidationException::withMessages([
                    'stock_location_id' => 'Informe o local da saída.',
                ]);
            }

            if ($locationId && ! $product->locations->contains('id', $locationId)) {
                throw ValidationException::withMessages([
                    'stock_location_id' => 'Esse produto não está disponível para esse local.',
                ]);
            }

            if ($product->uses_variants && $type === 'adjustment' && ! empty($data['variant_adjustments'])) {
                $lastMovement = null;

                foreach ($data['variant_adjustments'] as $adjustment) {
                    $currentVariantId = $adjustment['variant_id'] ?? null;
                    $quantity = (float) ($adjustment['quantity'] ?? 0);

                    $variant = ProductVariant::where('id', $currentVariantId)
                        ->where('product_id', $product->id)
                        ->first();

                    if (! $variant) {
                        throw ValidationException::withMessages([
                            'variant_adjustments' => 'Uma das variações informadas não pertence a esse produto.',
                        ]);
                    }

                    $before = (float) $variant->current_stock;
                    $after = $quantity;

                    if ($after < 0) {
                        throw ValidationException::withMessages([
                            'variant_adjustments' => 'O saldo ajustado da variação não pode ser negativo.',
                        ]);
                    }

                    $variant->update([
                        'current_stock' => $after,
                    ]);

                    $lastMovement = StockMovement::create([
                        'product_id' => $product->id,
                        'product_variant_id' => $variant->id,
                        'stock_location_id' => $locationId,
                        'user_id' => auth()->id(),
                        'movement_date' => $data['movement_date'],
                        'type' => $type,
                        'quantity' => $quantity,
                        'balance_before' => $before,
                        'balance_after' => $after,
                        'document_number' => $data['document_number'] ?? null,
                        'source_name' => $data['source_name'] ?? null,
                        'reference_type' => $data['reference_type'] ?? null,
                        'reference_id' => $data['reference_id'] ?? null,
                        'notes' => $data['notes'] ?? null,
                    ]);
                }

                return $lastMovement;
            }

            $quantity = (float) $data['quantity'];
            $variant = null;

            if ($product->uses_variants) {
                if (! $variantId) {
                    throw ValidationException::withMessages([
                        'product_variant_id' => 'Esse produto exige uma variação.',
                    ]);
                }

                $variant = ProductVariant::where('id', $variantId)
                    ->where('product_id', $product->id)
                    ->first();

                if (! $variant) {
                    throw ValidationException::withMessages([
                        'product_variant_id' => 'A variação informada não pertence a esse produto.',
                    ]);
                }
            } else {
                $variantId = null;
            }

            $target = $variant ?: $product;
            $before = (float) $target->current_stock;
            $after = $before;

            if ($type === 'entry') {
                $after = $before + $quantity;
            } elseif ($type === 'exit') {
                $after = $before - $quantity;

                if ($after < 0) {
                    throw ValidationException::withMessages([
                        'quantity' => 'Saldo insuficiente para essa saída.',
                    ]);
                }
            } elseif ($type === 'adjustment') {
                if ($quantity < 0) {
                    throw ValidationException::withMessages([
                        'quantity' => 'O saldo ajustado não pode ser negativo.',
                    ]);
                }

                $after = $quantity;
            } else {
                throw ValidationException::withMessages([
                    'type' => 'Tipo de movimentação inválido.',
                ]);
            }

            $target->update([
                'current_stock' => $after,
            ]);

            return StockMovement::create([
                'product_id' => $product->id,
                'product_variant_id' => $variant?->id,
                'stock_location_id' => $locationId,
                'user_id' => auth()->id(),
                'movement_date' => $data['movement_date'],
                'type' => $type,
                'quantity' => $quantity,
                'balance_before' => $before,
                'balance_after' => $after,
                'document_number' => $data['document_number'] ?? null,
                'source_name' => $data['source_name'] ?? null,
                'reference_type' => $data['reference_type'] ?? null,
                'reference_id' => $data['reference_id'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);
        });
    }

    public function createInitialForProduct(Product $product, float $initialStock = 0): void
    {
        if ($initialStock <= 0) {
            return;
        }

        $product->update([
            'current_stock' => $initialStock,
        ]);

        StockMovement::create([
            'product_id' => $product->id,
            'product_variant_id' => null,
            'stock_location_id' => null,
            'user_id' => auth()->id(),
            'movement_date' => now()->toDateString(),
            'type' => 'initial',
            'quantity' => $initialStock,
            'balance_before' => 0,
            'balance_after' => $initialStock,
            'notes' => 'Estoque inicial do produto.',
        ]);
    }

    public function createInitialForVariant(Product $product, ProductVariant $variant, float $initialStock = 0): void
    {
        if ($initialStock <= 0) {
            return;
        }

        $variant->update([
            'current_stock' => $initialStock,
        ]);

        StockMovement::create([
            'product_id' => $product->id,
            'product_variant_id' => $variant->id,
            'stock_location_id' => null,
            'user_id' => auth()->id(),
            'movement_date' => now()->toDateString(),
            'type' => 'initial',
            'quantity' => $initialStock,
            'balance_before' => 0,
            'balance_after' => $initialStock,
            'notes' => 'Estoque inicial da variação.',
        ]);
    }
}