@extends('layouts.app')

@section('title', 'Entrada de Estoque')

@section('content')
<div class="page-head">
    <div>
        <h2>Entrada de Estoque</h2>
        <p>Registre a chegada de produtos e atualize o estoque central.</p>
    </div>
</div>

@if(session('success'))
    <div class="alert-success-box">
        {{ session('success') }}
    </div>
@endif

@if($errors->any())
    <div class="alert-error-box">
        <strong>Corrija os erros abaixo:</strong>
        <ul style="margin-top:8px; padding-left:18px;">
            @foreach($errors->all() as $erro)
                <li>{{ $erro }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="grid grid-2">
    <div class="card">
        <div class="card-header">
            <div class="card-title">Lançar Entrada</div>
            <div class="card-subtitle">Selecione o produto e informe os dados da entrada.</div>
        </div>

        <div class="card-body">
            <form action="{{ route('stock-entries.store') }}" method="POST">
                @csrf

                <div class="form-grid">
                    <div class="form-group form-group-full">
                        <label class="form-label">Produto</label>
                        <select name="product_id" id="product_id" class="form-control-custom" required>
                            <option value="">Selecione</option>
                            @foreach($products as $product)
    @php
        $variantsData = $product->variants->map(function ($variant) {
            return [
                'id' => $variant->id,
                'name' => $variant->name,
                'stock' => $variant->formatted_stock,
            ];
        })->values();
    @endphp

    <option
        value="{{ $product->id }}"
        data-uses-variants="{{ $product->uses_variants ? 1 : 0 }}"
        data-variants='@json($variantsData)'
        @selected(old('product_id') == $product->id)
    >
        {{ $product->name }}
    </option>
@endforeach
                        </select>
                    </div>

                    <div class="form-group form-group-full" id="variant_group" style="display:none;">
                        <label class="form-label">Variação</label>
                        <select name="product_variant_id" id="product_variant_id" class="form-control-custom">
                            <option value="">Selecione a variação</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Quantidade recebida</label>
                        <input
                            type="number"
                            step="0.001"
                            min="0.001"
                            name="quantity"
                            class="form-control-custom"
                            value="{{ old('quantity') }}"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label class="form-label">Data da entrada</label>
                        <input
                            type="date"
                            name="movement_date"
                            class="form-control-custom"
                            value="{{ old('movement_date', now()->toDateString()) }}"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label class="form-label">Documento</label>
                        <input
                            type="text"
                            name="document_number"
                            class="form-control-custom"
                            value="{{ old('document_number') }}"
                            placeholder="NF, recibo, pedido..."
                        >
                    </div>

                    <div class="form-group">
                        <label class="form-label">Fornecedor / Origem</label>
                        <input
                            type="text"
                            name="source_name"
                            class="form-control-custom"
                            value="{{ old('source_name') }}"
                            placeholder="Nome do fornecedor"
                        >
                    </div>

                    <div class="form-group form-group-full">
                        <label class="form-label">Observação</label>
                        <textarea
                            name="notes"
                            class="form-control-custom"
                            rows="4"
                            placeholder="Informações adicionais sobre a entrada"
                        >{{ old('notes') }}</textarea>
                    </div>

                    <div class="form-group form-group-full">
                        <div class="actions-inline">
                            <button type="submit" class="btn btn-green">
                                Salvar Entrada
                            </button>
                            <a href="{{ route('products.index') }}" class="btn btn-dark">
                                Voltar para Produtos
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <div class="card-title">Orientações</div>
            <div class="card-subtitle">Use essa tela para registrar entrada real de mercadoria.</div>
        </div>

        <div class="card-body">
            <div class="simple-list">
                <li>
                    <div>
                        <strong>Produto simples</strong>
                        <small>O estoque será somado diretamente no produto.</small>
                    </div>
                </li>

                <li>
                    <div>
                        <strong>Produto com variação</strong>
                        <small>Você precisa escolher a variação correta antes de salvar.</small>
                    </div>
                </li>

                <li>
                    <div>
                        <strong>Histórico</strong>
                        <small>Toda entrada salva aqui ficará registrada no histórico de estoque.</small>
                    </div>
                </li>

                <li>
                    <div>
                        <strong>Correções manuais</strong>
                        <small>Para corrigir saldo diretamente, use o botão “Ajustar estoque” na tela de produtos.</small>
                    </div>
                </li>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const productSelect = document.getElementById('product_id');
    const variantGroup = document.getElementById('variant_group');
    const variantSelect = document.getElementById('product_variant_id');

    function atualizarVariacoes() {
        const selectedOption = productSelect.options[productSelect.selectedIndex];

        if (!selectedOption || !selectedOption.value) {
            variantGroup.style.display = 'none';
            variantSelect.innerHTML = '<option value="">Selecione a variação</option>';
            return;
        }

        const usesVariants = selectedOption.dataset.usesVariants === '1';
        const variants = JSON.parse(selectedOption.dataset.variants || '[]');
        const oldVariantId = @json(old('product_variant_id'));

        variantSelect.innerHTML = '<option value="">Selecione a variação</option>';

        if (usesVariants) {
            variantGroup.style.display = 'block';

            variants.forEach(variant => {
                const option = document.createElement('option');
                option.value = variant.id;
                option.textContent = `${variant.name} (estoque atual: ${variant.stock})`;

                if (String(oldVariantId) === String(variant.id)) {
                    option.selected = true;
                }

                variantSelect.appendChild(option);
            });
        } else {
            variantGroup.style.display = 'none';
        }
    }

    if (productSelect) {
        productSelect.addEventListener('change', atualizarVariacoes);
        atualizarVariacoes();
    }
});
</script>
@endsection