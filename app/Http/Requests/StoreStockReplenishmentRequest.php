<?php

namespace App\Http\Requests;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Foundation\Http\FormRequest;

class StoreStockReplenishmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'movement_date' => $this->input('movement_date') ?: now()->toDateString(),
        ]);
    }

    public function rules(): array
    {
        return [
            'stock_location_id' => ['required', 'integer', 'exists:stock_locations,id'],
            'movement_date' => ['required', 'date'],
            'document_number' => ['nullable', 'string', 'max:100'],
            'source_name' => ['nullable', 'string', 'max:150'],
            'notes' => ['nullable', 'string'],

            'items' => ['nullable', 'array'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.product_variant_id' => ['nullable', 'integer', 'exists:product_variants,id'],
            'items.*.quantity' => ['nullable', 'numeric', 'min:0'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $items = $this->input('items', []);
            $hasAnyPositive = false;

            foreach ($items as $index => $item) {
                $quantity = (float) ($item['quantity'] ?? 0);
                $productId = $item['product_id'] ?? null;
                $variantId = $item['product_variant_id'] ?? null;

                if ($quantity > 0) {
                    $hasAnyPositive = true;
                }

                if (!$productId) {
                    continue;
                }

                $product = Product::with('variants')->find($productId);

                if (!$product) {
                    continue;
                }

                if ($product->uses_variants && !$variantId) {
                    $validator->errors()->add("items.$index.product_variant_id", 'Selecione a variação desse item.');
                }

                if (!$product->uses_variants && $variantId) {
                    $validator->errors()->add("items.$index.product_variant_id", 'Esse produto não usa variações.');
                }

                if ($variantId) {
                    $variant = ProductVariant::where('id', $variantId)
                        ->where('product_id', $product->id)
                        ->first();

                    if (!$variant) {
                        $validator->errors()->add("items.$index.product_variant_id", 'A variação informada não pertence ao produto.');
                    }
                }
            }

            if (!$hasAnyPositive) {
                $validator->errors()->add('items', 'Informe pelo menos uma quantidade maior que zero.');
            }
        });
    }
}