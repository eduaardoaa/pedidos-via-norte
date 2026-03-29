<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'active' => $this->has('active')
                ? $this->boolean('active')
                : filter_var($this->input('active'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? (bool) $this->input('active'),
        ]);
    }

    public function rules(): array
    {
        $product = $this->route('product');
        $productId = is_object($product) ? $product->id : $product;

        return [
            'name' => ['required', 'string', 'max:150'],
            'sku' => ['nullable', 'string', 'max:80', 'unique:products,sku,' . $productId],
            'description' => ['nullable', 'string'],
            'product_unit_id' => ['required', 'integer', 'exists:product_units,id'],
            'active' => ['required', 'boolean'],

            'stock_locations' => ['required', 'array', 'min:1'],
            'stock_locations.*' => ['integer', 'exists:stock_locations,id'],

            'variants' => ['nullable', 'array'],
            'variants.*.id' => ['nullable', 'integer', 'exists:product_variants,id'],
            'variants.*.name' => ['nullable', 'string', 'max:120'],
            'variants.*.sku' => ['nullable', 'string', 'max:80'],
            'variants.*.active' => ['nullable', 'boolean'],
            'variants.*.remove' => ['nullable', 'boolean'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $product = $this->route('product');
            if (!is_object($product)) {
                return;
            }

            if (!$product->uses_variants) {
                return;
            }

            $variants = collect($this->input('variants', []))
                ->filter(function ($item) {
                    $remove = filter_var($item['remove'] ?? false, FILTER_VALIDATE_BOOLEAN);
                    return !$remove && filled($item['name'] ?? null);
                })
                ->values();

            if ($variants->isEmpty()) {
                $validator->errors()->add('variants', 'Informe pelo menos uma variação ativa para esse produto.');
                return;
            }

            $names = $variants->pluck('name')
                ->map(fn ($value) => mb_strtolower(trim((string) $value)));

            if ($names->duplicates()->isNotEmpty()) {
                $validator->errors()->add('variants', 'Existem nomes de variação repetidos.');
            }

            $skus = $variants->pluck('sku')
                ->filter()
                ->map(fn ($value) => mb_strtolower(trim((string) $value)));

            if ($skus->duplicates()->isNotEmpty()) {
                $validator->errors()->add('variants', 'Existem SKUs repetidos nas variações.');
            }
        });
    }
}