@extends('layouts.app')

@section('title', isset($materialRequest) ? 'Refazer Solicitação de Materiais' : 'Nova Solicitação de Materiais')

@section('content')
@php
    $productsJson = $products->map(function ($product) {
        return [
            'id' => $product->id,
            'name' => $product->name,
            'uses_variants' => (bool) $product->uses_variants,
            'variants' => $product->variants->map(function ($variant) {
                return [
                    'id' => $variant->id,
                    'name' => $variant->name,
                ];
            })->values()->toArray(),
        ];
    })->values()->toArray();

    $locationsJson = $locations->map(function ($location) {
        return [
            'id' => $location->id,
            'name' => $location->name,
            'scope' => $location->scope,
        ];
    })->values()->toArray();

    $prefilledItems = old('items');

    if (!$prefilledItems && isset($materialRequest)) {
        $prefilledItems = $materialRequest->items->map(function ($item) {
            return [
                'product_id' => $item->product_id,
                'product_variant_id' => $item->product_variant_id,
                'quantity' => $item->quantity,
            ];
        })->values()->toArray();
    }

    $prefilledItems = $prefilledItems ?: [];
@endphp

<div class="page-head">
    <div>
        <h2>{{ isset($materialRequest) ? 'Refazer Solicitação de Materiais' : 'Nova Solicitação de Materiais' }}</h2>
        <p>
            {{ isset($materialRequest)
                ? 'Os dados da solicitação anterior já foram carregados. Você pode alterar e enviar uma nova solicitação.'
                : 'Solicite materiais do almoxarifado. Esta ação não baixa estoque e não gera pedido automaticamente.' }}
        </p>
    </div>

    <div class="actions-inline">
        <a href="{{ route('supervisor.requests.index') }}" class="btn btn-dark">Voltar</a>
    </div>
</div>

@if ($errors->any())
    <div class="alert-error-box">
        <ul style="margin:0;padding-left:18px;">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

@if(session('success'))
    <div class="alert-success-box">
        {{ session('success') }}
    </div>
@endif

<form action="{{ route('supervisor.requests.store') }}" method="POST" class="card" id="formSolicitacaoSupervisor">
    @csrf

    <div class="card-body">
        <div class="form-grid form-grid-order-top">
            <div class="form-group">
                <label class="form-label" for="location_id">Local</label>
                <select name="location_id" id="location_id" class="form-control-custom" required>
                    <option value="">Selecione o local</option>
                    @foreach($locations as $location)
                        <option
                            value="{{ $location->id }}"
                            {{ old('location_id', $materialRequest->location_id ?? '') == $location->id ? 'selected' : '' }}
                        >
                            {{ $location->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group form-group-full">
                <label class="form-label" for="notes">Observações</label>
                <input
                    type="text"
                    name="notes"
                    id="notes"
                    class="form-control-custom"
                    value="{{ old('notes', $materialRequest->notes ?? '') }}"
                    placeholder="Observações da solicitação"
                >
            </div>
        </div>

        <div class="section-spacer"></div>

        <div class="page-head" style="margin-bottom:14px;">
            <div>
                <h2 style="font-size:1.05rem;">Produtos da solicitação</h2>
                <p>Preencha somente os itens que deseja solicitar.</p>
            </div>
        </div>

        <div id="produtos-wrapper" style="display:flex;flex-direction:column;gap:12px;"></div>

        <div class="actions-inline" style="margin-top:18px;">
            <button type="submit" class="btn btn-green">
                {{ isset($materialRequest) ? 'Enviar Nova Solicitação' : 'Enviar Solicitação' }}
            </button>
            <a href="{{ route('supervisor.requests.index') }}" class="btn btn-dark">Cancelar</a>
        </div>
    </div>
</form>

<style>
    .form-grid-order-top{
        grid-template-columns:repeat(2, minmax(0, 1fr));
    }

    .produto-grid{
        display:grid;
        grid-template-columns:2fr 1.2fr;
        gap:12px;
        align-items:end;
    }

    .produto-card-title{
        font-size:1rem;
        font-weight:700;
        margin-bottom:4px;
    }

    .produto-card-subtitle{
        color:var(--muted);
        font-size:.9rem;
        margin-bottom:14px;
    }

    .variant-picker{
        display:flex;
        flex-wrap:wrap;
        gap:10px;
        margin-bottom:14px;
    }

    .variant-check{
        display:inline-flex;
        align-items:center;
        gap:8px;
        padding:10px 12px;
        border-radius:12px;
        background:rgba(255,255,255,.03);
        border:1px solid rgba(255,255,255,.06);
        color:#e5e7eb;
        cursor:pointer;
    }

    .variant-check input{
        accent-color: var(--verde-secundario);
    }

    .variant-fields{
        display:flex;
        flex-direction:column;
        gap:10px;
    }

    .variant-row{
        display:grid;
        grid-template-columns:2fr 1.2fr;
        gap:12px;
        align-items:end;
        padding:12px;
        border-radius:14px;
        background:rgba(255,255,255,.03);
        border:1px solid rgba(255,255,255,.05);
    }

    @media (max-width: 1200px){
        .form-grid-order-top,
        .produto-grid,
        .variant-row{
            grid-template-columns:repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 640px){
        .form-grid-order-top,
        .produto-grid,
        .variant-row{
            grid-template-columns:1fr;
        }
    }
</style>

<script>
    const produtos = @json($productsJson);
    const prefilledItems = @json($prefilledItems);

    function getPrefilledSimpleQuantity(productId) {
        const found = prefilledItems.find(function (item) {
            return String(item.product_id) === String(productId)
                && (!item.product_variant_id || String(item.product_variant_id) === '');
        });

        return found ? (found.quantity ?? '') : '';
    }

    function getPrefilledVariantQuantity(productId, variantId) {
        const found = prefilledItems.find(function (item) {
            return String(item.product_id) === String(productId)
                && String(item.product_variant_id || '') === String(variantId);
        });

        return found ? (found.quantity ?? '') : '';
    }

    function hasPrefilledVariant(productId, variantId) {
        return prefilledItems.some(function (item) {
            return String(item.product_id) === String(productId)
                && String(item.product_variant_id || '') === String(variantId)
                && Number(item.quantity || 0) > 0;
        });
    }

    function renderizarProdutos() {
        const wrapper = document.getElementById('produtos-wrapper');

        wrapper.innerHTML = '';

        if (!produtos.length) {
            wrapper.innerHTML = `
                <div class="card" style="padding:16px;">
                    <div class="text-muted-small">Nenhum produto encontrado para solicitação.</div>
                </div>
            `;
            return;
        }

        let itemIndex = 0;

        produtos.forEach(function (produto) {
            if (!produto.uses_variants) {
                const quantity = getPrefilledSimpleQuantity(produto.id);

                wrapper.insertAdjacentHTML('beforeend', `
                    <div class="card" style="padding:16px;">
                        <div class="produto-grid">
                            <div class="form-group">
                                <label class="form-label">Produto</label>
                                <input type="text" class="form-control-custom" value="${produto.name}" readonly>
                                <input type="hidden" name="items[${itemIndex}][product_id]" value="${produto.id}">
                            </div>

                            <div class="form-group">
                                <label class="form-label">Quantidade</label>
                                <input
                                    type="number"
                                    name="items[${itemIndex}][quantity]"
                                    class="form-control-custom"
                                    min="0"
                                    step="1"
                                    placeholder="0"
                                    value="${quantity}"
                                >
                            </div>
                        </div>
                    </div>
                `);

                itemIndex++;
                return;
            }

            let checksHtml = '';
            let fieldsHtml = '';

            produto.variants.forEach(function (variant) {
                const checkboxId = `produto_${produto.id}_variant_${variant.id}`;
                const checked = hasPrefilledVariant(produto.id, variant.id);
                const quantity = getPrefilledVariantQuantity(produto.id, variant.id);

                checksHtml += `
                    <label class="variant-check" for="${checkboxId}">
                        <input
                            type="checkbox"
                            id="${checkboxId}"
                            onchange="toggleVariantRow(${produto.id}, ${variant.id})"
                            ${checked ? 'checked' : ''}
                        >
                        <span>${variant.name}</span>
                    </label>
                `;

                fieldsHtml += `
                    <div class="variant-row" id="variant-row-${produto.id}-${variant.id}" style="display:${checked ? 'grid' : 'none'};">
                        <div class="form-group">
                            <label class="form-label">Variação</label>
                            <input type="text" class="form-control-custom" value="${variant.name}" readonly>
                            <input type="hidden" name="items[${itemIndex}][product_id]" value="${produto.id}">
                            <input type="hidden" name="items[${itemIndex}][product_variant_id]" value="${variant.id}">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Quantidade</label>
                            <input
                                type="number"
                                name="items[${itemIndex}][quantity]"
                                class="form-control-custom"
                                min="0"
                                step="1"
                                placeholder="0"
                                value="${quantity}"
                            >
                        </div>
                    </div>
                `;

                itemIndex++;
            });

            wrapper.insertAdjacentHTML('beforeend', `
                <div class="card" style="padding:16px;">
                    <div class="produto-card-title">${produto.name}</div>
                    <div class="produto-card-subtitle">Selecione uma ou mais variações</div>

                    <div class="variant-picker">
                        ${checksHtml}
                    </div>

                    <div class="variant-fields">
                        ${fieldsHtml}
                    </div>
                </div>
            `);
        });
    }

    function toggleVariantRow(productId, variantId) {
        const row = document.getElementById(`variant-row-${productId}-${variantId}`);
        const checkbox = document.getElementById(`produto_${productId}_variant_${variantId}`);

        if (!row || !checkbox) {
            return;
        }

        row.style.display = checkbox.checked ? 'grid' : 'none';

        if (!checkbox.checked) {
            const qtyInput = row.querySelector('input[name*="[quantity]"]');
            if (qtyInput) {
                qtyInput.value = '';
            }
        }
    }

    renderizarProdutos();
</script>
@endsection