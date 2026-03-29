<?php

namespace App\Http\Requests;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Foundation\Http\FormRequest;

class StoreStockEntryRequest extends FormRequest
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
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'product_variant_id' => ['nullable', 'integer', 'exists:product_variants,id'],
            'movement_date' => ['required', 'date'],
            'quantity' => ['required', 'numeric', 'min:0.001'],
            'document_number' => ['nullable', 'string', 'max:100'],
            'source_name' => ['nullable', 'string', 'max:150'],
            'notes' => ['nullable', 'string'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $product = Product::find($this->input('product_id'));

            if (! $product) {
                return;
            }

            $variantId = $this->input('product_variant_id');

            if ($product->uses_variants && ! $variantId) {
                $validator->errors()->add('product_variant_id', 'Esse produto exige uma variação.');
            }

            if (! $product->uses_variants && $variantId) {
                $validator->errors()->add('product_variant_id', 'Esse produto não usa variações.');
            }

            if ($variantId) {
                $variant = ProductVariant::where('id', $variantId)
                    ->where('product_id', $product->id)
                    ->first();

                if (! $variant) {
                    $validator->errors()->add('product_variant_id', 'A variação informada não pertence a esse produto.');
                }
            }
        });
    }
}