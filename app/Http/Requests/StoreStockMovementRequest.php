<?php

namespace App\Http\Requests;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Foundation\Http\FormRequest;

class StoreStockMovementRequest extends FormRequest
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
        $product = null;

        if ($this->filled('product_id')) {
            $product = Product::with('locations')->find($this->input('product_id'));
        }

        $rules = [
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'product_variant_id' => ['nullable', 'integer', 'exists:product_variants,id'],
            'stock_location_id' => ['nullable', 'integer', 'exists:stock_locations,id'],

            'movement_date' => ['required', 'date'],
            'type' => ['required', 'in:entry,exit,adjustment'],

            'document_number' => ['nullable', 'string', 'max:100'],
            'source_name' => ['nullable', 'string', 'max:150'],
            'reference_type' => ['nullable', 'string', 'max:100'],
            'reference_id' => ['nullable', 'integer'],
            'notes' => ['nullable', 'string'],
        ];

        if (
            $product &&
            $product->uses_variants &&
            $this->input('type') === 'adjustment'
        ) {
            $rules['variant_adjustments'] = ['required', 'array', 'min:1'];
            $rules['variant_adjustments.*.variant_id'] = ['required', 'integer', 'exists:product_variants,id'];
            $rules['variant_adjustments.*.quantity'] = ['required', 'numeric', 'min:0'];
            $rules['quantity'] = ['nullable', 'numeric', 'min:0'];
        } else {
    $rules['quantity'] = ['required', 'numeric', 'min:0'];
    $rules['variant_adjustments'] = ['nullable', 'array'];
    $rules['variant_adjustments.*.variant_id'] = ['nullable', 'integer', 'exists:product_variants,id'];
    $rules['variant_adjustments.*.quantity'] = ['nullable', 'numeric', 'min:0'];
}

        return $rules;
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $product = Product::with(['locations', 'variants'])->find($this->input('product_id'));

            if (! $product) {
                return;
            }

            $variantId = $this->input('product_variant_id');
            $locationId = $this->input('stock_location_id');
            $type = $this->input('type');
            $variantAdjustments = $this->input('variant_adjustments', []);

            if ($product->uses_variants) {
                if ($type === 'adjustment') {
                    if (! is_array($variantAdjustments) || count($variantAdjustments) === 0) {
                        $validator->errors()->add('variant_adjustments', 'Esse produto exige variações para ajuste.');
                    } else {
                        foreach ($variantAdjustments as $index => $adjustment) {
                            $currentVariantId = $adjustment['variant_id'] ?? null;
                            $currentQuantity = $adjustment['quantity'] ?? null;

                            if (! $currentVariantId) {
                                $validator->errors()->add("variant_adjustments.$index.variant_id", 'A variação é obrigatória.');
                                continue;
                            }

                            $variant = ProductVariant::where('id', $currentVariantId)
                                ->where('product_id', $product->id)
                                ->first();

                            if (! $variant) {
                                $validator->errors()->add("variant_adjustments.$index.variant_id", 'A variação informada não pertence a esse produto.');
                            }

                            if ($currentQuantity === null || $currentQuantity === '') {
                                $validator->errors()->add("variant_adjustments.$index.quantity", 'Informe o novo estoque da variação.');
                            } elseif (! is_numeric($currentQuantity) || (float) $currentQuantity < 0) {
                                $validator->errors()->add("variant_adjustments.$index.quantity", 'O estoque da variação deve ser numérico e não negativo.');
                            }
                        }
                    }
                } else {
                    if (! $variantId) {
                        $validator->errors()->add('product_variant_id', 'Esse produto exige uma variação.');
                    }
                }
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

            if ($type === 'exit' && ! $locationId) {
                $validator->errors()->add('stock_location_id', 'Para saída, informe o local que consumiu o produto.');
            }

            if ($locationId && ! $product->locations->contains('id', $locationId)) {
                $validator->errors()->add('stock_location_id', 'Esse produto não está disponível para esse local.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'product_id.required' => 'O produto é obrigatório.',
            'product_id.integer' => 'O produto informado é inválido.',
            'product_id.exists' => 'O produto informado não existe.',

            'product_variant_id.integer' => 'A variação informada é inválida.',
            'product_variant_id.exists' => 'A variação informada não existe.',

            'stock_location_id.integer' => 'O local informado é inválido.',
            'stock_location_id.exists' => 'O local informado não existe.',

            'movement_date.required' => 'A data da movimentação é obrigatória.',
            'movement_date.date' => 'A data da movimentação é inválida.',

            'type.required' => 'O tipo de movimentação é obrigatório.',
            'type.in' => 'O tipo de movimentação informado é inválido.',

            'quantity.required' => 'A quantidade é obrigatória.',
            'quantity.numeric' => 'A quantidade deve ser numérica.',
            'quantity.min' => 'A quantidade deve ser maior que zero.',

            'variant_adjustments.required' => 'Informe as variações para ajuste.',
            'variant_adjustments.array' => 'As variações informadas são inválidas.',
            'variant_adjustments.min' => 'Informe pelo menos uma variação.',

            'variant_adjustments.*.variant_id.required' => 'A variação é obrigatória.',
            'variant_adjustments.*.variant_id.integer' => 'A variação informada é inválida.',
            'variant_adjustments.*.variant_id.exists' => 'A variação informada não existe.',

            'variant_adjustments.*.quantity.required' => 'Informe o novo estoque da variação.',
            'variant_adjustments.*.quantity.numeric' => 'O estoque da variação deve ser numérico.',
            'variant_adjustments.*.quantity.min' => 'O estoque da variação não pode ser negativo.',

            'document_number.string' => 'O número do documento é inválido.',
            'document_number.max' => 'O número do documento pode ter no máximo 100 caracteres.',

            'source_name.string' => 'A origem informada é inválida.',
            'source_name.max' => 'A origem pode ter no máximo 150 caracteres.',

            'reference_type.string' => 'O tipo de referência é inválido.',
            'reference_type.max' => 'O tipo de referência pode ter no máximo 100 caracteres.',

            'reference_id.integer' => 'A referência informada é inválida.',

            'notes.string' => 'A observação é inválida.',
        ];
    }
}