<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'uses_variants' => $this->boolean('uses_variants'),
            'active' => $this->has('active') ? $this->boolean('active') : true,
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'sku' => ['nullable', 'string', 'max:80', 'unique:products,sku'],
            'description' => ['nullable', 'string'],
            'product_unit_id' => ['required', 'integer', 'exists:product_units,id'],
            'uses_variants' => ['required', 'boolean'],
            'active' => ['required', 'boolean'],

            'stock_locations' => ['required', 'array', 'min:1'],
            'stock_locations.*' => ['integer', 'exists:stock_locations,id'],

            'initial_stock' => ['nullable', 'numeric', 'min:0'],

            'variants' => ['nullable', 'array'],
            'variants.*.name' => ['nullable', 'string', 'max:120'],
            'variants.*.sku' => ['nullable', 'string', 'max:80'],
            'variants.*.initial_stock' => ['nullable', 'numeric', 'min:0'],
            'variants.*.active' => ['nullable', 'boolean'],
            'variants.*.sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $usesVariants = $this->boolean('uses_variants');

            $variants = collect($this->input('variants', []))
                ->filter(fn ($item) => filled($item['name'] ?? null))
                ->values();

            if ($usesVariants && $variants->isEmpty()) {
                $validator->errors()->add('variants', 'Esse produto usa variações, então informe pelo menos uma variação.');
            }

            $names = $variants->pluck('name')
                ->filter()
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