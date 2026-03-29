@extends('layouts.app')

@section(
    'title',
    !empty($prefillData['source_material_request_id'] ?? null)
        ? 'Gerar Pedido da Solicitação'
        : (!empty($prefillData['source_order_id'] ?? null) ? 'Repetir Pedido' : 'Novo Pedido')
)

@section('content')
@php
    $productsJson = $products->map(function ($product) {
        return [
            'id' => $product->id,
            'name' => $product->name,
            'uses_variants' => (bool) $product->uses_variants,
            'stock' => $product->total_stock,
            'scopes' => $product->locations->map(function ($location) {
                return strtolower(trim($location->name));
            })->values()->toArray(),
            'variants' => $product->variants->map(function ($variant) {
                return [
                    'id' => $variant->id,
                    'name' => $variant->name,
                    'stock' => $variant->formatted_stock,
                ];
            })->values()->toArray(),
        ];
    })->values()->toArray();

    $locationsJson = $locations->map(function ($location) {
        return [
            'id' => $location->id,
            'name' => $location->name,
            'scope' => $location->scope,
            'route_id' => $location->route_id,
            'route_name' => $location->route->name ?? 'Sem rota',
        ];
    })->values()->toArray();

    $prefillData = $prefillData ?? [
        'scope' => '',
        'route_id' => '',
        'location_id' => '',
        'items' => [],
        'source_order_id' => null,
        'source_material_request_id' => null,
    ];
@endphp

<div class="page-head">
    <div>
        <h2>
            @if(!empty($prefillData['source_material_request_id']))
                Gerar Pedido da Solicitação #{{ $prefillData['source_material_request_id'] }}
            @elseif(!empty($prefillData['source_order_id']))
                Repetir Pedido #{{ $prefillData['source_order_id'] }}
            @else
                Novo Pedido
            @endif
        </h2>

        <p>
            @if(!empty($prefillData['source_material_request_id']))
                Revise os itens da solicitação e salve o pedido para efetivar a saída no estoque.
            @elseif(!empty($prefillData['source_order_id']))
                Revise os itens preenchidos e salve um novo pedido com data de hoje.
            @else
                Crie um pedido de rota ou almoxarifado com baixa automática no estoque.
            @endif
        </p>
    </div>

    <div class="actions-inline">
        <a href="{{ route('orders.index') }}" class="btn btn-dark">Voltar</a>
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

<form action="{{ route('orders.store') }}" method="POST" class="card" id="formCriarPedido">
    @csrf

    @if(!empty($prefillData['source_material_request_id']))
        <input type="hidden" name="material_request_id" value="{{ $prefillData['source_material_request_id'] }}">
    @endif

    <div class="card-body">
        <div class="form-grid form-grid-order-top">
            <div class="form-group">
                <label class="form-label" for="scope">Tipo do pedido</label>
                <select name="scope_selector" id="scope" class="form-control-custom" required>
                    <option value="">Selecione</option>
                    <option value="rota" {{ ($prefillData['scope'] ?? '') === 'rota' ? 'selected' : '' }}>Rota</option>
                    <option value="almoxarifado" {{ ($prefillData['scope'] ?? '') === 'almoxarifado' ? 'selected' : '' }}>Almoxarifado</option>
                </select>
            </div>

            <div class="form-group" id="group-rota" style="display:none;">
                <label class="form-label" for="route_id_selector">Rota</label>
                <select id="route_id_selector" class="form-control-custom">
                    <option value="">Selecione a rota</option>
                    @foreach($locations->pluck('route')->filter()->unique('id')->sortBy('name') as $route)
                        <option value="{{ $route->id }}" {{ (string) ($prefillData['route_id'] ?? '') === (string) $route->id ? 'selected' : '' }}>
                            {{ $route->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label class="form-label" for="location_id">Local</label>
                <select name="location_id" id="location_id" class="form-control-custom" required>
                    <option value="">Selecione primeiro o tipo</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label" for="route_name">Rota vinculada</label>
                <input type="text" id="route_name" class="form-control-custom" value="" readonly>
            </div>
        </div>

        <div class="section-spacer"></div>

        <div class="page-head" style="margin-bottom:14px;">
            <div>
                <h2 style="font-size:1.05rem;">Produtos do pedido</h2>
                <p>
                    @if(!empty($prefillData['source_material_request_id']))
                        Os itens da solicitação já vieram preenchidos. Ajuste as quantidades e salve o pedido.
                    @elseif(!empty($prefillData['source_order_id']))
                        Os itens do pedido original já vieram preenchidos. Ajuste o que quiser antes de salvar.
                    @else
                        Preencha apenas os itens que realmente entrarão no pedido.
                    @endif
                </p>
            </div>
        </div>

        <div id="produtos-wrapper" style="display:flex;flex-direction:column;gap:12px;"></div>

        <div class="actions-inline" style="margin-top:18px;">
            <button type="submit" class="btn btn-green">
                @if(!empty($prefillData['source_material_request_id']))
                    Gerar Pedido
                @elseif(!empty($prefillData['source_order_id']))
                    Salvar Novo Pedido
                @else
                    Salvar Pedido
                @endif
            </button>

            <a href="{{ route('orders.index') }}" class="btn btn-dark">Cancelar</a>
        </div>
    </div>
</form>

<style>
    .form-grid-order-top{
        grid-template-columns:repeat(4, minmax(0, 1fr));
    }

    .produto-grid{
        display:grid;
        grid-template-columns:2fr 1fr 1.2fr;
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
        grid-template-columns:2fr 1fr 1.2fr;
        gap:12px;
        align-items:end;
        padding:12px;
        border-radius:14px;
        background:rgba(255,255,255,.03);
        border:1px solid rgba(255,255,255,.05);
    }

    .campo-estoque-erro{
        border-color: rgba(220,53,69,.65) !important;
        box-shadow: 0 0 0 4px rgba(220,53,69,.10) !important;
    }

    @media (max-width: 1200px){
        .form-grid-order-top{
            grid-template-columns:repeat(2, minmax(0, 1fr));
        }

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
    const locations = @json($locationsJson);
    const prefillData = @json($prefillData);

    function getPrefillQuantity(productId, variantId = null) {
        const found = (prefillData.items || []).find(function(item) {
            return String(item.product_id) === String(productId)
                && String(item.product_variant_id ?? '') === String(variantId ?? '');
        });

        return found ? found.quantity : '';
    }

    function hasPrefillVariant(productId, variantId) {
        return (prefillData.items || []).some(function(item) {
            return String(item.product_id) === String(productId)
                && String(item.product_variant_id ?? '') === String(variantId);
        });
    }

    function preencherLocais() {
        const scope = document.getElementById('scope').value;
        const routeId = document.getElementById('route_id_selector').value;
        const locationSelect = document.getElementById('location_id');
        const routeNameInput = document.getElementById('route_name');

        locationSelect.innerHTML = '';
        routeNameInput.value = '';

        const optionDefault = document.createElement('option');
        optionDefault.value = '';
        optionDefault.text = 'Selecione o local';
        locationSelect.appendChild(optionDefault);

        let filtrados = [];

        if (scope === 'almoxarifado') {
            filtrados = locations.filter(function (location) {
                return location.scope === 'almoxarifado';
            });
        } else if (scope === 'rota') {
            filtrados = locations.filter(function (location) {
                return location.scope === 'rota' && String(location.route_id || '') === String(routeId || '');
            });
        }

        filtrados.forEach(function (location) {
            const option = document.createElement('option');
            option.value = location.id;
            option.text = location.name;
            option.dataset.route = location.route_name || '';

            if (String(location.id) === String(prefillData.location_id || '')) {
                option.selected = true;
                routeNameInput.value = location.route_name || '';
            }

            locationSelect.appendChild(option);
        });
    }

    function atualizarCamposTipo() {
        const scope = document.getElementById('scope').value;
        const groupRota = document.getElementById('group-rota');
        const routeSelector = document.getElementById('route_id_selector');
        const locationSelect = document.getElementById('location_id');
        const routeNameInput = document.getElementById('route_name');

        locationSelect.innerHTML = '<option value="">Selecione o local</option>';
        routeNameInput.value = '';

        if (scope === 'rota') {
            groupRota.style.display = 'flex';
        } else {
            groupRota.style.display = 'none';
            routeSelector.value = '';
        }

        preencherLocais();
        renderizarProdutos();
    }

    function atualizarNomeRota() {
        const locationSelect = document.getElementById('location_id');
        const option = locationSelect.options[locationSelect.selectedIndex];
        document.getElementById('route_name').value = option ? (option.dataset.route || '') : '';
    }

    function produtoPertenceAoScope(produto, scope) {
        if (!scope) return false;

        const scopes = (produto.scopes || []).map(function (item) {
            return String(item).toLowerCase().trim();
        });

        return scopes.includes(scope);
    }

    function renderizarProdutos() {
        const wrapper = document.getElementById('produtos-wrapper');
        const scope = document.getElementById('scope').value;

        wrapper.innerHTML = '';

        if (!scope) {
            wrapper.innerHTML = `
                <div class="card" style="padding:16px;">
                    <div class="text-muted-small">Selecione primeiro o tipo do pedido para carregar os produtos.</div>
                </div>
            `;
            return;
        }

        const produtosFiltrados = produtos.filter(function (produto) {
            return produtoPertenceAoScope(produto, scope);
        });

        if (!produtosFiltrados.length) {
            wrapper.innerHTML = `
                <div class="card" style="padding:16px;">
                    <div class="text-muted-small">Nenhum produto encontrado para este tipo.</div>
                </div>
            `;
            return;
        }

        let itemIndex = 0;

        produtosFiltrados.forEach(function (produto) {
            if (!produto.uses_variants) {
                const qty = getPrefillQuantity(produto.id, null);

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
                                    class="form-control-custom quantity-check-input"
                                    min="0"
                                    step="1"
                                    placeholder="0"
                                    value="${qty}"
                                >
                            </div>

                            <div class="form-group">
                                <label class="form-label">Estoque disponível</label>
                                <input
                                    type="text"
                                    class="form-control-custom stock-check-value"
                                    value="${produto.stock}"
                                    readonly
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
                const checked = hasPrefillVariant(produto.id, variant.id);
                const qty = getPrefillQuantity(produto.id, variant.id);

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
                                class="form-control-custom quantity-check-input"
                                min="0"
                                step="1"
                                placeholder="0"
                                value="${qty}"
                            >
                        </div>

                        <div class="form-group">
                            <label class="form-label">Estoque disponível</label>
                            <input
                                type="text"
                                class="form-control-custom stock-check-value"
                                value="${variant.stock}"
                                readonly
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
                qtyInput.classList.remove('campo-estoque-erro');
            }

            const oldError = row.querySelector('.erro-estoque-msg');
            if (oldError) {
                oldError.remove();
            }
        }
    }

    function limparErrosEstoqueFrontend() {
        document.querySelectorAll('.erro-estoque-msg').forEach(function(el) {
            el.remove();
        });

        document.querySelectorAll('.campo-estoque-erro').forEach(function(el) {
            el.classList.remove('campo-estoque-erro');
        });
    }

    function adicionarErroCampo(input, mensagem) {
        input.classList.add('campo-estoque-erro');

        const msg = document.createElement('div');
        msg.className = 'erro-estoque-msg';
        msg.style.color = '#fecaca';
        msg.style.fontSize = '.85rem';
        msg.style.marginTop = '6px';
        msg.innerText = mensagem;

        input.parentElement.appendChild(msg);
    }

    function normalizarNumero(valor) {
        return parseInt(String(valor || '0').replace(',', '.')) || 0;
    }

    function validarEstoqueAntesDeEnviar() {
        limparErrosEstoqueFrontend();

        let temErro = false;

        document.querySelectorAll('.quantity-check-input').forEach(function(input) {
            const linha = input.closest('.produto-grid, .variant-row');

            if (!linha || linha.offsetParent === null) {
                return;
            }

            const quantidade = normalizarNumero(input.value);
            if (quantidade <= 0) {
                return;
            }

            const estoqueInput = linha.querySelector('.stock-check-value');
            if (!estoqueInput) {
                return;
            }

            const estoque = normalizarNumero(estoqueInput.value);

            if (quantidade > estoque) {
                temErro = true;

                const nomeItemInput = linha.querySelector('input[type="text"][readonly]');
                const nomeItem = nomeItemInput ? nomeItemInput.value : 'item';

                adicionarErroCampo(
                    input,
                    'Quantidade maior que o estoque disponível para ' + nomeItem + '.'
                );
            }
        });

        return !temErro;
    }

    function getMensagemConfirmacaoPedido() {
        if (prefillData.source_material_request_id) {
            return 'Deseja realmente gerar este pedido da solicitação?';
        }

        if (prefillData.source_order_id) {
            return 'Deseja realmente salvar este novo pedido repetido?';
        }

        return 'Deseja realmente lançar este pedido?';
    }

    const formCriarPedido = document.getElementById('formCriarPedido');
    const scopeInput = document.getElementById('scope');
    const routeSelector = document.getElementById('route_id_selector');
    const locationInput = document.getElementById('location_id');

    scopeInput.addEventListener('change', atualizarCamposTipo);
    routeSelector.addEventListener('change', preencherLocais);
    locationInput.addEventListener('change', atualizarNomeRota);

    formCriarPedido.addEventListener('submit', function(e) {
        if (!validarEstoqueAntesDeEnviar()) {
            e.preventDefault();
            alert('Existem itens com quantidade maior que o estoque disponível.');
        }
    });

    document.addEventListener('keydown', function(event) {
        if (event.key !== 'Enter') {
            return;
        }

        const target = event.target;
        if (!target) {
            return;
        }

        if (!formCriarPedido.contains(target)) {
            return;
        }

        const tagName = (target.tagName || '').toLowerCase();
        const inputType = (target.type || '').toLowerCase();

        if (tagName === 'textarea') {
            return;
        }

        if (inputType === 'button' || inputType === 'submit' || inputType === 'checkbox') {
            return;
        }

        event.preventDefault();
        event.stopPropagation();

        if (!validarEstoqueAntesDeEnviar()) {
            alert('Existem itens com quantidade maior que o estoque disponível.');
            return;
        }

        const confirmar = confirm(getMensagemConfirmacaoPedido());

        if (!confirmar) {
            return;
        }

        formCriarPedido.requestSubmit();
    }, true);

    atualizarCamposTipo();
</script>
@endsection
