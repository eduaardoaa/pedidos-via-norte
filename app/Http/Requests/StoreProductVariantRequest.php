<?php

namespace App\Http\Requests;

use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;

class StoreProductVariantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'active' => $this->has('active') ? $this->boolean('active') : true,
        ]);
    }

    public function rules(): array
    {
        return [
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'name' => ['required', 'string', 'max:120'],
            'sku' => ['nullable', 'string', 'max:80', 'unique:product_variants,sku'],
            'initial_stock' => ['nullable', 'numeric', 'min:0'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'active' => ['required', 'boolean'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $product = Product::find($this->input('product_id'));

            if (! $product) {
                return;
            }

            if (! $product->uses_variants) {
                $validator->errors()->add('product_id', 'Esse produto não usa variações.');
            }

            $exists = $product->variants()
                ->whereRaw('LOWER(name) = ?', [mb_strtolower(trim((string) $this->input('name')))])
                ->exists();

            if ($exists) {
                $validator->errors()->add('name', 'Já existe uma variação com esse nome para esse produto.');
            }
        });
    }
}