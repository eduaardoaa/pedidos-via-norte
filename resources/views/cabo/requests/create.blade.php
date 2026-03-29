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
            'route_id' => $location->route_id,
            'route_name' => $location->route->name ?? 'Sem rota',
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
                : 'Solicite materiais da rota. Esta ação não baixa estoque e não gera pedido automaticamente.' }}
        </p>
    </div>

    <div class="actions-inline">
        <a href="{{ route('cabo.requests.index') }}" class="btn btn-dark">Voltar</a>
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

<form action="{{ route('cabo.requests.store') }}" method="POST" class="card" id="formSolicitacaoCabo">
    @csrf

    <div class="card-body">
        <div class="form-grid form-grid-order-top">
            <div class="form-group">
                <label class="form-label" for="route_id">Rota</label>
                <select name="route_id" id="route_id" class="form-control-custom" required>
                    <option value="">Selecione a rota</option>
                    @foreach($routes as $route)
                        <option
                            value="{{ $route->id }}"
                            {{ old('route_id', $materialRequest->route_id ?? '') == $route->id ? 'selected' : '' }}
                        >
                            {{ $route->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label class="form-label" for="location_id">Local</label>
                <select name="location_id" id="location_id" class="form-control-custom" required>
                    <option value="">Selecione primeiro a rota</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label" for="route_name">Rota vinculada</label>
                <input type="text" id="route_name" class="form-control-custom" value="" readonly>
            </div>

            <div class="form-group">
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

        <input type="hidden" name="request_latitude" id="request_latitude">
        <input type="hidden" name="request_longitude" id="request_longitude">
        <input type="hidden" name="request_location_accuracy" id="request_location_accuracy">

        <input type="hidden" name="request_street" id="request_street">
        <input type="hidden" name="request_number" id="request_number">
        <input type="hidden" name="request_neighborhood" id="request_neighborhood">
        <input type="hidden" name="request_city" id="request_city">
        <input type="hidden" name="request_state" id="request_state">
        <input type="hidden" name="request_zipcode" id="request_zipcode">
        <input type="hidden" name="request_full_address" id="request_full_address">

        <div class="actions-inline" style="margin-top:18px;">
            <button type="submit" class="btn btn-green">
                {{ isset($materialRequest) ? 'Enviar Nova Solicitação' : 'Enviar Solicitação' }}
            </button>
            <a href="{{ route('cabo.requests.index') }}" class="btn btn-dark">Cancelar</a>
        </div>
    </div>
</form>

<style>
    .form-grid-order-top{
        grid-template-columns:repeat(4, minmax(0, 1fr));
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
    const selectedLocationId = @json(old('location_id', $materialRequest->location_id ?? ''));
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

    function preencherLocais() {
        const routeId = document.getElementById('route_id').value;
        const locationSelect = document.getElementById('location_id');
        const routeNameInput = document.getElementById('route_name');

        locationSelect.innerHTML = '';
        routeNameInput.value = '';

        const optionDefault = document.createElement('option');
        optionDefault.value = '';
        optionDefault.text = 'Selecione o local';
        locationSelect.appendChild(optionDefault);

        const filtrados = locations.filter(function (location) {
            return location.scope === 'rota' && String(location.route_id || '') === String(routeId || '');
        });

        filtrados.forEach(function (location) {
            const option = document.createElement('option');
            option.value = location.id;
            option.text = location.name;
            option.dataset.route = location.route_name || '';

            if (String(selectedLocationId || '') === String(location.id)) {
                option.selected = true;
            }

            locationSelect.appendChild(option);
        });

        atualizarNomeRota();
    }

    function atualizarNomeRota() {
        const locationSelect = document.getElementById('location_id');
        const option = locationSelect.options[locationSelect.selectedIndex];
        document.getElementById('route_name').value = option ? (option.dataset.route || '') : '';
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

    document.getElementById('route_id').addEventListener('change', function () {
        const locationSelect = document.getElementById('location_id');
        locationSelect.dataset.userChanged = '1';
        preencherLocais();
    });

    document.getElementById('location_id').addEventListener('change', atualizarNomeRota);
    const formSolicitacao = document.getElementById('formSolicitacaoCabo');
const botaoEnviarSolicitacao = formSolicitacao
    ? formSolicitacao.querySelector('button[type="submit"]')
    : null;

let envioLiberadoComGps = false;
let envioEmAndamento = false;
let textoOriginalBotao = botaoEnviarSolicitacao ? botaoEnviarSolicitacao.innerHTML : '';

async function obterPosicaoAtual() {
    return new Promise(function (resolve, reject) {
        if (!navigator.geolocation) {
            reject(new Error('Geolocalização não suportada neste navegador.'));
            return;
        }

        navigator.geolocation.getCurrentPosition(
            function (position) {
                resolve(position);
            },
            function (error) {
                reject(error);
            },
            {
                enableHighAccuracy: true,
                timeout: 15000,
                maximumAge: 0
            }
        );
    });
}

async function buscarEnderecoPorCoordenadas(latitude, longitude) {
    const token = document.querySelector('meta[name="csrf-token"]');

    const response = await fetch("{{ route('reverse-geocode') }}", {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': token ? token.getAttribute('content') : ''
        },
        body: JSON.stringify({
            latitude: latitude,
            longitude: longitude
        })
    });

    if (!response.ok) {
        throw new Error('Não foi possível obter o endereço da localização.');
    }

    return await response.json();
}

function preencherCamposLocalizacao(position, endereco) {
    document.getElementById('request_latitude').value = position.coords.latitude ?? '';
    document.getElementById('request_longitude').value = position.coords.longitude ?? '';
    document.getElementById('request_location_accuracy').value = position.coords.accuracy ?? '';

    document.getElementById('request_street').value = endereco.street ?? '';
    document.getElementById('request_number').value = endereco.number ?? '';
    document.getElementById('request_neighborhood').value = endereco.neighborhood ?? '';
    document.getElementById('request_city').value = endereco.city ?? '';
    document.getElementById('request_state').value = endereco.state ?? '';
    document.getElementById('request_zipcode').value = endereco.zipcode ?? '';
    document.getElementById('request_full_address').value = endereco.full_address ?? '';
}

function bloquearEnvio(mensagem) {
    if (!botaoEnviarSolicitacao) {
        return;
    }

    botaoEnviarSolicitacao.disabled = true;
    botaoEnviarSolicitacao.style.opacity = '0.7';
    botaoEnviarSolicitacao.style.cursor = 'not-allowed';
    botaoEnviarSolicitacao.innerHTML = mensagem;
}

function liberarEnvio() {
    if (!botaoEnviarSolicitacao) {
        return;
    }

    botaoEnviarSolicitacao.disabled = false;
    botaoEnviarSolicitacao.style.opacity = '';
    botaoEnviarSolicitacao.style.cursor = '';
    botaoEnviarSolicitacao.innerHTML = textoOriginalBotao;
}

if (formSolicitacao) {
    formSolicitacao.addEventListener('submit', async function (event) {
        if (envioLiberadoComGps) {
            return;
        }

        event.preventDefault();

        if (envioEmAndamento) {
            return;
        }

        envioEmAndamento = true;
        bloquearEnvio('Obtendo localização...');

        try {
            const position = await obterPosicaoAtual();
            const latitude = position.coords.latitude;
            const longitude = position.coords.longitude;

            bloquearEnvio('Buscando endereço...');

            const endereco = await buscarEnderecoPorCoordenadas(latitude, longitude);

            preencherCamposLocalizacao(position, endereco);

            bloquearEnvio('Enviando solicitação...');

            envioLiberadoComGps = true;
            formSolicitacao.submit();
        } catch (error) {
            envioEmAndamento = false;
            liberarEnvio();

            alert('Para enviar a solicitação, é obrigatório permitir a localização do dispositivo.');
            console.error(error);
        }
    });
}
    renderizarProdutos();
    preencherLocais();
</script>
@endsection