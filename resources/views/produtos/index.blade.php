@extends('layouts.app')

@section('title', 'Produtos')

@section('content')
<div class="page-head products-page-head">
    <div>
        <h2>Produtos</h2>
        <p>Gerencie os produtos e os locais onde eles podem ser utilizados.</p>
    </div>

    <div class="actions-inline products-actions-inline">
        <button type="button" class="btn btn-green" id="abrirModalProduto">
            + Novo Produto
        </button>

        <a
            href="{{ route('products.stock.pdf', request()->only(['search', 'local'])) }}"
            class="btn btn-dark"
        >
            PDF do Estoque
        </a>

        <a href="{{ route('stock-replenishment.index') }}" class="btn btn-dark">
            Reposição de Estoque
        </a>

        <a href="{{ route('stock-history.index') }}" class="btn btn-dark">
            Histórico de Movimentações
        </a>
    </div>
</div>

<div class="card" style="margin-bottom:18px;">
    <div class="card-body">
        <form method="GET" action="{{ route('products.index') }}" id="formFiltrosProdutos">
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Pesquisar produto</label>
                    <input
                        type="text"
                        name="search"
                        id="filtroBuscaProduto"
                        class="form-control-custom"
                        placeholder="Digite o nome ou SKU do produto"
                        value="{{ $search ?? '' }}"
                    >
                </div>

                <div class="form-group">
                    <label class="form-label">Filtro rápido</label>
                    <select name="local" id="filtroLocalProduto" class="form-control-custom">
                        <option value="">Todos</option>
                        <option value="rota" @selected(($locationFilter ?? '') === 'rota')>Rota</option>
                        <option value="almoxarifado" @selected(($locationFilter ?? '') === 'almoxarifado')>Almoxarifado</option>
                    </select>
                </div>

                <div class="form-group form-group-full">
                    <div class="actions-inline">
                        <a href="{{ route('products.index') }}" class="btn btn-dark">
                            Limpar filtros
                        </a>
                    </div>
                </div>
            </div>
        </form>
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

<div class="card">
    <div class="card-header">
        <div class="card-title">
            Lista de Produtos ({{ method_exists($products, 'total') ? $products->total() : $products->count() }})
        </div>
        <div class="card-subtitle">
            Visualize estoque, unidade, locais disponíveis e status.
            @if($locationFilter === 'rota')
                Exibindo apenas produtos disponíveis para <strong>Rota</strong>.
            @elseif($locationFilter === 'almoxarifado')
                Exibindo apenas produtos disponíveis para <strong>Almoxarifado</strong>.
            @else
                Exibindo <strong>todos</strong> os produtos.
            @endif
        </div>
    </div>

    <div class="card-body">
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Produto</th>
                        <th>Unidade</th>
                        <th>Variações</th>
                        <th>Estoque</th>
                        <th>Locais</th>
                        <th>Status</th>
                        <th width="180">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $product)
                        <tr>
                            <td>{{ $product->id }}</td>

                            <td>
                                <strong>{{ $product->name }}</strong>
                                @if($product->sku)
                                    <div class="text-muted-small">SKU: {{ $product->sku }}</div>
                                @endif
                            </td>

                            <td>{{ $product->unit?->name ?? '-' }}</td>

                            <td>
                                @if($product->uses_variants)
                                    {{ $product->variants->count() }} variação(ões)
                                @else
                                    Não
                                @endif
                            </td>

                            <td>
                                @if($product->uses_variants)
                                    <div class="simple-list" style="gap:8px;">
                                        @foreach($product->variants as $variant)
                                            <div style="padding:8px 10px; border-radius:12px; background:rgba(255,255,255,.03); border:1px solid rgba(255,255,255,.05);">
                                                <strong>{{ $variant->name }}:</strong> {{ $variant->formatted_stock }}
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    {{ $product->total_stock }}
                                @endif
                            </td>

                            <td>
                                @forelse($product->locations as $location)
                                    <span class="badge-status badge-info" style="margin-right:6px; margin-bottom:6px;">
                                        {{ $location->name }}
                                    </span>
                                @empty
                                    <span class="text-muted-small">Sem local</span>
                                @endforelse
                            </td>

                            <td>
                                @if($product->active)
                                    <span class="badge-status badge-success">Ativo</span>
                                @else
                                    <span class="badge-status badge-warning">Inativo</span>
                                @endif
                            </td>

                            <td>
                                @php
                                    $variantsData = $product->variants->map(function ($variant) {
                                        return [
                                            'id' => $variant->id,
                                            'name' => $variant->name,
                                            'sku' => $variant->sku,
                                            'active' => $variant->active ? 1 : 0,
                                            'has_movements' => $variant->movements->count() > 0,
                                            'formatted_stock' => $variant->formatted_stock,
                                        ];
                                    })->values();
                                @endphp

                                <div class="table-actions">
                                    <button
                                        type="button"
                                        class="btn btn-warning-soft btn-ajustar-estoque"
                                        data-id="{{ $product->id }}"
                                        data-name="{{ $product->name }}"
                                        data-uses-variants="{{ $product->uses_variants ? 1 : 0 }}"
                                        data-current-stock="{{ $product->total_stock }}"
                                        data-variants='@json($variantsData)'
                                    >
                                        Ajustar estoque
                                    </button>

                                    <button
                                        type="button"
                                        class="btn btn-dark btn-editar-produto"
                                        data-id="{{ $product->id }}"
                                        data-variants='@json($variantsData)'
                                        data-name="{{ $product->name }}"
                                        data-sku="{{ $product->sku }}"
                                        data-description="{{ $product->description }}"
                                        data-unit="{{ $product->product_unit_id }}"
                                        data-active="{{ $product->active ? 1 : 0 }}"
                                        data-locations='@json($product->locations->pluck("id")->values())'
                                    >
                                        Editar
                                    </button>

                                    <form action="{{ route('products.destroy', $product) }}" method="POST" onsubmit="return confirm('Deseja inativar este produto?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger-soft">
                                            Inativar
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8">Nenhum produto cadastrado.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="section-spacer"></div>

<div class="card">
    <div class="card-header">
        <div class="card-title">Resumo rápido</div>
        <div class="card-subtitle">Produtos simples usam estoque direto. Produtos com variações somam o estoque das variações.</div>
    </div>

    <div class="card-body">
        <div class="simple-list">
            <li>
                <div>
                    <strong>Produtos sem variação</strong>
                    <small>Exemplo: álcool, rodo, saco de lixo.</small>
                </div>
            </li>
            <li>
                <div>
                    <strong>Produtos com variação</strong>
                    <small>Exemplo: calça P, M e G.</small>
                </div>
            </li>
            <li>
                <div>
                    <strong>Locais disponíveis</strong>
                    <small>O produto pode ser usado em rota, almoxarifado ou ambos, mas o estoque é único.</small>
                </div>
            </li>
        </div>
    </div>
</div>

{{-- Modal cadastrar produto --}}
<div class="custom-modal" id="modalProduto">
    <div class="custom-modal-backdrop" data-close-modal></div>

    <div class="custom-modal-dialog">
        <div class="custom-modal-header">
            <div>
                <h3>Cadastrar Produto</h3>
                <p>Preencha os dados do produto e defina onde ele poderá ser utilizado.</p>
            </div>

            <button type="button" class="custom-modal-close" data-close-modal>
                ✕
            </button>
        </div>

        <div class="custom-modal-body">
            <form action="{{ route('products.store') }}" method="POST">
                @csrf

                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Nome do produto</label>
                        <input type="text" name="name" class="form-control-custom" value="{{ old('name') }}" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">SKU</label>
                        <input type="text" name="sku" class="form-control-custom" value="{{ old('sku') }}">
                    </div>

                    <div class="form-group form-group-full">
                        <label class="form-label">Descrição</label>
                        <textarea name="description" class="form-control-custom" rows="3">{{ old('description') }}</textarea>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Unidade</label>
                        <select name="product_unit_id" class="form-control-custom" required>
                            <option value="">Selecione</option>
                            @foreach($units as $unit)
                                <option value="{{ $unit->id }}" @selected(old('product_unit_id') == $unit->id)>
                                    {{ $unit->name }} ({{ $unit->abbreviation }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Usa variações?</label>
                        <select name="uses_variants" id="uses_variants" class="form-control-custom" required>
                            <option value="0" @selected(old('uses_variants') == '0')>Não</option>
                            <option value="1" @selected(old('uses_variants') == '1')>Sim</option>
                        </select>
                    </div>

                    <div class="form-group" id="bloco_estoque_inicial">
                        <label class="form-label">Estoque inicial</label>
                        <input
                            type="number"
                            step="1"
                            min="0"
                            name="initial_stock"
                            class="form-control-custom"
                            value="{{ old('initial_stock', 0) }}"
                        >
                    </div>

                    <div class="form-group form-group-full">
                        <label class="form-label">Locais disponíveis</label>

                        <div class="actions-inline">
                            @foreach($locations as $location)
                                <label class="form-check-line">
                                    <input
                                        type="checkbox"
                                        name="stock_locations[]"
                                        value="{{ $location->id }}"
                                        @checked(in_array($location->id, old('stock_locations', [])))
                                    >
                                    <span>{{ $location->name }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="form-group form-group-full" id="bloco_variacoes" style="display:none;">
                        <label class="form-label">Variações</label>

                        <div id="lista_variacoes" class="simple-list" style="gap:10px;"></div>

                        <div class="actions-inline" style="margin-top:10px;">
                            <button type="button" class="btn btn-dark" id="btnAdicionarVariacao">
                                + Adicionar variação
                            </button>
                        </div>

                        <div class="text-muted-small" style="margin-top:8px;">
                            Exemplo: P, M, G, 38, 40, 42, Azul, Preto...
                        </div>
                    </div>

                    <div class="form-group form-group-full">
                        <label class="form-check-line">
                            <input type="checkbox" name="active" value="1" checked>
                            <span>Produto ativo</span>
                        </label>
                    </div>

                    <div class="form-group form-group-full">
                        <div class="actions-inline">
                            <button type="button" class="btn btn-dark" data-close-modal>
                                Cancelar
                            </button>
                            <button type="submit" class="btn btn-green">
                                Salvar Produto
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal editar produto --}}
<div class="custom-modal" id="modalEditarProduto">
    <div class="custom-modal-backdrop" data-close-edit-modal></div>

    <div class="custom-modal-dialog">
        <div class="custom-modal-header">
            <div>
                <h3>Editar Produto</h3>
                <p>Atualize os dados principais do produto e gerencie as variações.</p>
            </div>

            <button type="button" class="custom-modal-close" data-close-edit-modal>
                ✕
            </button>
        </div>

        <div class="custom-modal-body">
            <form id="formEditarProduto" method="POST">
                @csrf
                @method('PUT')

                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Nome do produto</label>
                        <input type="text" name="name" id="edit_name" class="form-control-custom" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">SKU</label>
                        <input type="text" name="sku" id="edit_sku" class="form-control-custom">
                    </div>

                    <div class="form-group form-group-full">
                        <label class="form-label">Descrição</label>
                        <textarea name="description" id="edit_description" class="form-control-custom" rows="3"></textarea>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Unidade</label>
                        <select name="product_unit_id" id="edit_product_unit_id" class="form-control-custom" required>
                            <option value="">Selecione</option>
                            @foreach($units as $unit)
                                <option value="{{ $unit->id }}">
                                    {{ $unit->name }} ({{ $unit->abbreviation }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Status</label>
                        <select name="active" id="edit_active" class="form-control-custom" required>
                            <option value="1">Ativo</option>
                            <option value="0">Inativo</option>
                        </select>
                    </div>

                    <div class="form-group form-group-full">
                        <label class="form-label">Locais disponíveis</label>

                        <div class="actions-inline">
                            @foreach($locations as $location)
                                <label class="form-check-line">
                                    <input
                                        type="checkbox"
                                        name="stock_locations[]"
                                        value="{{ $location->id }}"
                                        class="edit-location-checkbox"
                                    >
                                    <span>{{ $location->name }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="form-group form-group-full" id="bloco_variacoes_edicao" style="display:none;">
                        <label class="form-label">Variações</label>

                        <div id="lista_variacoes_edicao" class="simple-list" style="gap:10px;"></div>

                        <div class="actions-inline" style="margin-top:10px;">
                            <button type="button" class="btn btn-dark" id="btnAdicionarVariacaoEdicao">
                                + Adicionar variação
                            </button>
                        </div>

                        <div class="text-muted-small" style="margin-top:8px;">
                            Você pode editar, adicionar e remover variações. Se a variação já tiver movimentações, ela será inativada em vez de apagada.
                        </div>
                    </div>

                    <div class="form-group form-group-full">
                        <div class="actions-inline">
                            <button type="button" class="btn btn-dark" data-close-edit-modal>
                                Cancelar
                            </button>
                            <button type="submit" class="btn btn-green">
                                Salvar Alterações
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal ajustar estoque --}}
<div class="custom-modal" id="modalAjustarEstoque">
    <div class="custom-modal-backdrop" data-close-stock-modal></div>

    <div class="custom-modal-dialog">
        <div class="custom-modal-header">
            <div>
                <h3>Ajustar Estoque</h3>
                <p>Use essa opção para corrigir manualmente o estoque do produto ou de todas as variações de uma vez.</p>
            </div>

            <button type="button" class="custom-modal-close" data-close-stock-modal>
                ✕
            </button>
        </div>

        <div class="custom-modal-body">
            <form action="{{ route('product-stock.movements.store') }}" method="POST">
                @csrf

                <input type="hidden" name="product_id" id="stock_product_id">
                <input type="hidden" name="type" value="adjustment">

                <div class="form-grid">
                    <div class="form-group form-group-full">
                        <label class="form-label">Produto</label>
                        <input type="text" id="stock_product_name" class="form-control-custom" readonly>
                    </div>

                    <div class="form-group" id="stock_simple_group">
                        <label class="form-label">Novo estoque</label>
                        <input
                            type="number"
                            step="1"
                            min="0"
                            name="quantity"
                            id="stock_quantity"
                            class="form-control-custom"
                        >
                    </div>

                    <div class="form-group form-group-full" id="stock_variants_group" style="display:none;">
                        <label class="form-label">Ajuste das variações</label>

                        <div id="stock_variants_list" class="simple-list" style="gap:10px;"></div>

                        <div class="text-muted-small" style="margin-top:8px;">
                            Edite o estoque final de cada variação e salve tudo de uma vez.
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Data do ajuste</label>
                        <input
                            type="date"
                            name="movement_date"
                            class="form-control-custom"
                            value="{{ now()->toDateString() }}"
                            required
                        >
                    </div>

                    <div class="form-group form-group-full">
                        <label class="form-label">Observação</label>
                        <textarea
                            name="notes"
                            class="form-control-custom"
                            rows="3"
                            placeholder="Ex: correção após conferência física do estoque"
                        ></textarea>
                    </div>

                    <div class="form-group form-group-full">
                        <div class="actions-inline">
                            <button type="button" class="btn btn-dark" data-close-stock-modal>
                                Cancelar
                            </button>
                            <button type="submit" class="btn btn-warning-soft">
                                Salvar ajuste
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const modalAjustarEstoque = document.getElementById('modalAjustarEstoque');
    const fecharModalEstoqueBotoes = document.querySelectorAll('[data-close-stock-modal]');
    const botoesAjustarEstoque = document.querySelectorAll('.btn-ajustar-estoque');

    const stockProductId = document.getElementById('stock_product_id');
    const stockProductName = document.getElementById('stock_product_name');
    const stockQuantity = document.getElementById('stock_quantity');
    const stockSimpleGroup = document.getElementById('stock_simple_group');
    const stockVariantsGroup = document.getElementById('stock_variants_group');
    const stockVariantsList = document.getElementById('stock_variants_list');

    const modalProduto = document.getElementById('modalProduto');
    const abrirModalProduto = document.getElementById('abrirModalProduto');
    const fecharModalBotoes = document.querySelectorAll('[data-close-modal]');

    const modalEditarProduto = document.getElementById('modalEditarProduto');
    const fecharModalEditarBotoes = document.querySelectorAll('[data-close-edit-modal]');
    const botoesEditar = document.querySelectorAll('.btn-editar-produto');
    const formEditarProduto = document.getElementById('formEditarProduto');

    const selectVariacoes = document.getElementById('uses_variants');
    const blocoVariacoes = document.getElementById('bloco_variacoes');
    const blocoEstoqueInicial = document.getElementById('bloco_estoque_inicial');

    const listaVariacoes = document.getElementById('listaVariacoes') || document.getElementById('lista_variacoes');
    const btnAdicionarVariacao = document.getElementById('btnAdicionarVariacao');

    const listaVariacoesEdicao = document.getElementById('lista_variacoes_edicao');
    const blocoVariacoesEdicao = document.getElementById('bloco_variacoes_edicao');
    const btnAdicionarVariacaoEdicao = document.getElementById('btnAdicionarVariacaoEdicao');

    const formFiltrosProdutos = document.getElementById('formFiltrosProdutos');
    const filtroBuscaProduto = document.getElementById('filtroBuscaProduto');
    const filtroLocalProduto = document.getElementById('filtroLocalProduto');

    let filtroTimeout = null;
    let variacaoIndex = 0;
    let variacaoEdicaoIndex = 0;

    function abrirModal(modal) {
        if (!modal) return;
        modal.classList.add('is-open');
        document.body.classList.add('modal-open');
    }

    function fecharModal(modal) {
        if (!modal) return;
        modal.classList.remove('is-open');

        if (!document.querySelector('.custom-modal.is-open')) {
            document.body.classList.remove('modal-open');
        }
    }

    function criarBlocoVariacao(index, values = {}) {
        const item = document.createElement('div');
        item.className = 'card';
        item.style.padding = '14px';

        item.innerHTML = `
            <div class="actions-inline" style="justify-content:space-between; align-items:center; margin-bottom:10px;">
                <strong>Variação ${index + 1}</strong>
                <button type="button" class="btn btn-danger-soft btn-remover-variacao">Remover</button>
            </div>

            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Nome da variação</label>
                    <input type="text" name="variants[${index}][name]" class="form-control-custom" placeholder="Ex: P, M, G" value="${values.name ?? ''}">
                </div>

                <div class="form-group">
                    <label class="form-label">SKU da variação</label>
                    <input type="text" name="variants[${index}][sku]" class="form-control-custom" placeholder="SKU da variação" value="${values.sku ?? ''}">
                </div>

                <div class="form-group form-group-full">
                    <label class="form-label">Estoque inicial</label>
                    <input type="number" step="1" min="0" name="variants[${index}][initial_stock]" class="form-control-custom" placeholder="Estoque inicial" value="${values.initial_stock ?? ''}">
                </div>
            </div>
        `;

        item.querySelector('.btn-remover-variacao').addEventListener('click', function () {
            item.remove();
        });

        listaVariacoes.appendChild(item);
    }

    function criarBlocoVariacaoEdicao(index, values = {}) {
        const item = document.createElement('div');
        item.className = 'card';
        item.style.padding = '14px';

        const variantId = values.id ?? '';
        const hasMovements = !!values.has_movements;
        const active = values.active ?? 1;
        const estoque = values.formatted_stock ?? '0';

        item.innerHTML = `
            <input type="hidden" name="variants[${index}][id]" value="${variantId}">
            <input type="hidden" name="variants[${index}][remove]" value="0" class="input-remove-variacao">

            <div class="actions-inline" style="justify-content:space-between; align-items:center; margin-bottom:10px;">
                <div>
                    <strong>Variação ${index + 1}</strong>
                    <div class="text-muted-small">Estoque atual: ${estoque}</div>
                </div>
                <button type="button" class="btn btn-danger-soft btn-remover-variacao-edicao">
                    ${hasMovements ? 'Inativar' : 'Remover'}
                </button>
            </div>

            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Nome da variação</label>
                    <input type="text" name="variants[${index}][name]" class="form-control-custom input-nome-variacao" placeholder="Ex: P, M, G" value="${values.name ?? ''}">
                </div>

                <div class="form-group">
                    <label class="form-label">SKU da variação</label>
                    <input type="text" name="variants[${index}][sku]" class="form-control-custom" placeholder="SKU da variação" value="${values.sku ?? ''}">
                </div>

                <div class="form-group">
                    <label class="form-label">Status da variação</label>
                    <select name="variants[${index}][active]" class="form-control-custom">
                        <option value="1" ${String(active) === '1' ? 'selected' : ''}>Ativa</option>
                        <option value="0" ${String(active) === '0' ? 'selected' : ''}>Inativa</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Movimentações</label>
                    <input type="text" class="form-control-custom" value="${hasMovements ? 'Já possui movimentações' : 'Sem movimentações'}" readonly>
                </div>
            </div>
        `;

        const btnRemover = item.querySelector('.btn-remover-variacao-edicao');
        const inputRemove = item.querySelector('.input-remove-variacao');
        const inputNome = item.querySelector('.input-nome-variacao');

        btnRemover.addEventListener('click', function () {
            if (variantId) {
                inputRemove.value = '1';
                item.style.opacity = '0.45';
                item.style.pointerEvents = 'none';
                item.style.display = 'none';
                inputNome.value = inputNome.value || 'removida';
            } else {
                item.remove();
            }
        });

        listaVariacoesEdicao.appendChild(item);
    }

    function criarLinhaAjusteVariacao(variant) {
        const item = document.createElement('div');
        item.className = 'card';
        item.style.padding = '14px';
        item.style.transition = '0.2s ease';

        item.innerHTML = `
            <input type="hidden" name="variant_adjustments[${variant.id}][variant_id]" value="${variant.id}">

            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Variação</label>
                    <input type="text" class="form-control-custom" value="${variant.name}" readonly>
                </div>

                <div class="form-group">
                    <label class="form-label">Estoque atual</label>
                    <input type="text" class="form-control-custom" value="${variant.formatted_stock ?? '0'}" readonly>
                </div>

                <div class="form-group">
                    <label class="form-label">Novo estoque</label>
                    <input
                        type="number"
                        step="1"
                        min="0"
                        name="variant_adjustments[${variant.id}][quantity]"
                        class="form-control-custom input-ajuste-variacao"
                        value="${variant.formatted_stock ?? 0}"
                        required
                    >
                </div>
            </div>
        `;

        const input = item.querySelector('.input-ajuste-variacao');
        const valorOriginal = String(variant.formatted_stock ?? '0');

        input.addEventListener('input', function () {
            if (String(this.value) !== valorOriginal) {
                item.style.border = '1px solid rgba(245, 158, 11, 0.45)';
                item.style.background = 'rgba(245, 158, 11, 0.04)';
            } else {
                item.style.border = '';
                item.style.background = '';
            }
        });

        stockVariantsList.appendChild(item);
    }

    function garantirVariacoesMinimas() {
        if (listaVariacoes.children.length === 0) {
            criarBlocoVariacao(variacaoIndex++);
            criarBlocoVariacao(variacaoIndex++);
        }
    }

    function alternarCamposVariacoes() {
        const usaVariacoes = selectVariacoes && selectVariacoes.value === '1';
        blocoVariacoes.style.display = usaVariacoes ? 'block' : 'none';
        blocoEstoqueInicial.style.display = usaVariacoes ? 'none' : 'flex';

        if (usaVariacoes) {
            garantirVariacoesMinimas();
        }
    }

    if (filtroBuscaProduto && formFiltrosProdutos) {
        filtroBuscaProduto.addEventListener('input', function () {
            clearTimeout(filtroTimeout);

            filtroTimeout = setTimeout(function () {
                formFiltrosProdutos.submit();
            }, 400);
        });
    }

    if (filtroLocalProduto && formFiltrosProdutos) {
        filtroLocalProduto.addEventListener('change', function () {
            formFiltrosProdutos.submit();
        });
    }

    if (abrirModalProduto) {
        abrirModalProduto.addEventListener('click', function () {
            abrirModal(modalProduto);
        });
    }

    fecharModalBotoes.forEach(botao => {
        botao.addEventListener('click', function () {
            fecharModal(modalProduto);
        });
    });

    fecharModalEditarBotoes.forEach(botao => {
        botao.addEventListener('click', function () {
            fecharModal(modalEditarProduto);
        });
    });

    fecharModalEstoqueBotoes.forEach(botao => {
        botao.addEventListener('click', function () {
            fecharModal(modalAjustarEstoque);
        });
    });

    if (btnAdicionarVariacao) {
        btnAdicionarVariacao.addEventListener('click', function () {
            criarBlocoVariacao(variacaoIndex++);
        });
    }

    if (btnAdicionarVariacaoEdicao) {
        btnAdicionarVariacaoEdicao.addEventListener('click', function () {
            criarBlocoVariacaoEdicao(variacaoEdicaoIndex++, {
                id: '',
                name: '',
                sku: '',
                active: 1,
                has_movements: false,
                formatted_stock: '0'
            });
        });
    }

    if (selectVariacoes) {
        selectVariacoes.addEventListener('change', alternarCamposVariacoes);
        alternarCamposVariacoes();
    }

    botoesEditar.forEach(botao => {
        botao.addEventListener('click', function () {
            const id = this.dataset.id;
            const name = this.dataset.name ?? '';
            const sku = this.dataset.sku ?? '';
            const description = this.dataset.description ?? '';
            const unit = this.dataset.unit ?? '';
            const active = this.dataset.active ?? '1';
            const locations = JSON.parse(this.dataset.locations ?? '[]');
            const variants = JSON.parse(this.dataset.variants ?? '[]');

            formEditarProduto.action = `/produtos/${id}`;
            document.getElementById('edit_name').value = name;
            document.getElementById('edit_sku').value = sku;
            document.getElementById('edit_description').value = description;
            document.getElementById('edit_product_unit_id').value = unit;
            document.getElementById('edit_active').value = active;

            document.querySelectorAll('.edit-location-checkbox').forEach(checkbox => {
                checkbox.checked = locations.includes(Number(checkbox.value));
            });

            listaVariacoesEdicao.innerHTML = '';
            variacaoEdicaoIndex = 0;

            if (variants.length > 0) {
                blocoVariacoesEdicao.style.display = 'block';

                variants.forEach(variant => {
                    criarBlocoVariacaoEdicao(variacaoEdicaoIndex++, variant);
                });
            } else {
                blocoVariacoesEdicao.style.display = 'none';
            }

            abrirModal(modalEditarProduto);
        });
    });

    botoesAjustarEstoque.forEach(botao => {
        botao.addEventListener('click', function () {
            const id = this.dataset.id;
            const name = this.dataset.name ?? '';
            const usesVariants = this.dataset.usesVariants === '1';
            const currentStock = this.dataset.currentStock ?? '0';
            const variants = JSON.parse(this.dataset.variants ?? '[]');

            stockProductId.value = id;
            stockProductName.value = name;
            stockVariantsList.innerHTML = '';

            if (usesVariants) {
                stockSimpleGroup.style.display = 'none';
                stockVariantsGroup.style.display = 'block';
                stockQuantity.value = '';

                variants.forEach(variant => {
                    criarLinhaAjusteVariacao(variant);
                });
            } else {
                stockSimpleGroup.style.display = 'block';
                stockVariantsGroup.style.display = 'none';
                stockQuantity.value = currentStock;
            }

            abrirModal(modalAjustarEstoque);
        });
    });

    @if(old('uses_variants') == '1')
        while (listaVariacoes.firstChild) {
            listaVariacoes.removeChild(listaVariacoes.firstChild);
        }

        variacaoIndex = 0;

        @php
            $oldVariants = old('variants', []);
        @endphp

        @foreach($oldVariants as $oldIndex => $oldVariant)
            criarBlocoVariacao(variacaoIndex++, {
                name: @json($oldVariant['name'] ?? ''),
                sku: @json($oldVariant['sku'] ?? ''),
                initial_stock: @json($oldVariant['initial_stock'] ?? '')
            });
        @endforeach
    @endif

    @if($errors->any())
        abrirModal(modalProduto);
    @endif
});
</script>
@endsection