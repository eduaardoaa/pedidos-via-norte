@extends('layouts.app')

@section('title', 'Pedidos')

@section('content')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<div class="page-head">
    <div>
        <h2>Pedidos</h2>
        <p>Gerencie os pedidos por tipo, rota, usuário e período.</p>
    </div>

    <div class="actions-inline">
        <a href="{{ route('orders.create') }}" class="btn btn-green">
            + Novo Pedido
        </a>
    </div>
</div>

@if(session('success'))
    <div class="alert-success-box">
        {{ session('success') }}
    </div>
@endif

<div class="card card-filtros-pedidos">
    <div class="card-body">
        <form method="GET" action="{{ route('orders.index') }}" id="filtrosPedidos">
            <div class="filters-grid-5">
                <div class="form-group">
                    <label class="form-label" for="scope">Tipo</label>
                    <select name="scope" id="scope" class="form-control-custom filtro-auto">
                        <option value="">Todos</option>
                        <option value="rota" {{ request('scope') === 'rota' ? 'selected' : '' }}>Rota</option>
                        <option value="almoxarifado" {{ request('scope') === 'almoxarifado' ? 'selected' : '' }}>Almoxarifado</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label" for="route_id">Rota</label>
                    <select name="route_id" id="route_id" class="form-control-custom filtro-auto">
                        <option value="">Todas</option>
                        @foreach($routes as $route)
                            <option value="{{ $route->id }}" {{ request('route_id') == $route->id ? 'selected' : '' }}>
                                {{ $route->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label" for="user_id">Usuário</label>
                    <select name="user_id" id="user_id" class="form-control-custom filtro-auto">
                        <option value="">Todos</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                {{ $user->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label" for="date_start">Data inicial</label>
                    <input
                        type="date"
                        name="date_start"
                        id="date_start"
                        class="form-control-custom filtro-auto"
                        value="{{ request('date_start') }}"
                    >
                </div>

                <div class="form-group">
                    <label class="form-label" for="date_end">Data final</label>
                    <input
                        type="date"
                        name="date_end"
                        id="date_end"
                        class="form-control-custom filtro-auto"
                        value="{{ request('date_end') }}"
                    >
                </div>
            </div>

            <div class="actions-inline" style="margin-top:16px;">
                <button type="submit" class="btn btn-green">
                    Filtrar
                </button>

                <a href="{{ route('orders.index') }}" class="btn btn-dark">
                    Limpar
                </a>
            </div>
        </form>
    </div>
</div>

<style>
    .filters-grid-5{
        display:grid;
        grid-template-columns:repeat(5, minmax(0, 1fr));
        gap:16px;
    }

    .pedido-check-col{
        width:48px;
        text-align:center;
    }

    .pedido-check-wrap{
        display:flex;
        align-items:center;
        justify-content:center;
    }

    .pedido-checkbox{
        width:18px;
        height:18px;
        accent-color: var(--verde-secundario);
        cursor:pointer;
    }

    .pedido-checkbox:disabled{
        opacity:.45;
        cursor:not-allowed;
    }

    .selecionados-bar{
        display:flex;
        align-items:center;
        justify-content:space-between;
        gap:12px;
        flex-wrap:wrap;
        margin-bottom:12px;
        padding:14px 16px;
        border-radius:16px;
        background:rgba(255,255,255,.03);
        border:1px solid rgba(255,255,255,.06);
    }

    .selecionados-info{
        color:#e5e7eb;
        font-weight:600;
    }

    .selecionados-info span{
        color:var(--verde-secundario);
    }

    .pedido-resumo-horizontal{
        display:grid;
        grid-template-columns:repeat(4, minmax(0, 1fr));
        gap:12px;
    }

    .pedido-resumo-item{
        background:rgba(255,255,255,.03);
        border:1px solid rgba(255,255,255,.06);
        border-radius:14px;
        padding:14px;
        display:flex;
        flex-direction:column;
        gap:6px;
    }

    .pedido-resumo-label{
        color:var(--muted);
        font-size:.85rem;
    }

    .pedido-resumo-item strong{
        color:#fff;
        font-size:.98rem;
        word-break:break-word;
    }

    .btn-pdf-top{
        background:linear-gradient(135deg, #1677ff, #0f5fe0);
        color:#fff;
        border:1px solid rgba(255,255,255,.08);
    }

    .btn-pdf-top:hover{
        color:#fff;
        filter:brightness(1.05);
        transform:translateY(-1px);
    }

    .btn-icon-inline{
        display:inline-flex;
        align-items:center;
        justify-content:center;
        gap:8px;
    }

    .acao-col{
        width:72px;
        min-width:72px;
        text-align:center;
        vertical-align:middle;
    }

    .acao-col form{
        margin:0;
        display:flex;
        justify-content:center;
    }

    .btn-sm-icon{
        width:38px;
        height:32px;
        padding:0;
        display:inline-flex;
        align-items:center;
        justify-content:center;
        border:none;
        border-radius:6px;
        color:#fff;
        font-size:1rem;
        transition:.2s ease;
        text-decoration:none;
    }

    .btn-sm-icon:hover{
        transform:translateY(-1px);
        filter:brightness(1.08);
        color:#fff;
    }

    .btn-info-icon{
        background:#0dcaf0;
    }

    .btn-primary-icon{
        background:#0d6efd;
    }

    .btn-danger-icon{
        background:#dc3545;
    }

    .btn-pdf-icon{
        background:#0d6efd;
    }

    .btn-excel-icon{
        background:#198754;
    }

    .btn-repeat-icon{
        background:#ffc107;
        color:#111;
    }

    .btn-repeat-icon:hover{
        color:#111;
    }

    .table thead th.acao-col{
        white-space:nowrap;
    }

    .card-filtros-pedidos{
        position:relative;
        z-index:20;
    }

    .card-filtros-pedidos .form-control-custom{
        cursor:pointer;
    }

    .card-filtros-pedidos input[type="date"]{
        cursor:pointer;
    }

    .card-filtros-pedidos select{
        cursor:pointer;
    }

    .card-lista-pedidos{
        position:relative;
        z-index:1;
    }

    .card-paginacao-pedidos .card-body{
        padding:18px;
    }

    .paginacao-pedidos-wrap{
        display:flex;
        justify-content:center;
        align-items:center;
    }

    .paginacao-pedidos-wrap nav{
        width:100%;
    }

    .paginacao-pedidos-wrap .pagination{
        display:flex;
        justify-content:center;
        align-items:center;
        flex-wrap:wrap;
        gap:8px;
        margin:0;
        padding:0;
        list-style:none;
    }

    .paginacao-pedidos-wrap .page-item{
        list-style:none;
    }

    .paginacao-pedidos-wrap .page-link,
    .paginacao-pedidos-wrap .page-item > span{
        min-width:40px;
        height:40px;
        padding:0 12px;
        display:inline-flex;
        align-items:center;
        justify-content:center;
        border-radius:10px;
        border:1px solid rgba(255,255,255,.08);
        background:rgba(255,255,255,.03);
        color:#e5e7eb;
        text-decoration:none;
        font-weight:600;
        line-height:1;
        font-size:.95rem;
        transition:.2s ease;
    }

    .paginacao-pedidos-wrap .page-link:hover{
        background:rgba(22,163,74,.14);
        border-color:rgba(22,163,74,.35);
        color:#fff;
        transform:translateY(-1px);
    }

    .paginacao-pedidos-wrap .page-item.active .page-link,
    .paginacao-pedidos-wrap .page-item.active > span{
        background:linear-gradient(135deg, rgba(22,163,74,.95), rgba(22,163,74,.72));
        border-color:rgba(22,163,74,.65);
        color:#fff;
    }

    .paginacao-pedidos-wrap .page-item.disabled .page-link,
    .paginacao-pedidos-wrap .page-item.disabled > span{
        opacity:.45;
        cursor:not-allowed;
        pointer-events:none;
    }

    @media (max-width: 640px){
        .paginacao-pedidos-wrap .page-link,
        .paginacao-pedidos-wrap .page-item > span{
            min-width:36px;
            height:36px;
            font-size:.88rem;
            padding:0 10px;
        }
    }

    @media (max-width: 1250px){
        .filters-grid-5{
            grid-template-columns:repeat(3, minmax(0, 1fr));
        }
    }

    @media (max-width: 900px){
        .pedido-resumo-horizontal{
            grid-template-columns:repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 820px){
        .filters-grid-5{
            grid-template-columns:repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 640px){
        .filters-grid-5,
        .pedido-resumo-horizontal{
            grid-template-columns:1fr;
        }
    }

    .paginacao-pedidos-wrap .pagination svg{
        width:14px !important;
        height:14px !important;
        max-width:14px !important;
        max-height:14px !important;
        display:block;
    }

    .paginacao-pedidos-wrap .pagination .page-link{
        min-width:40px !important;
        width:auto !important;
        height:40px !important;
        padding:0 12px !important;
        font-size:.95rem !important;
        line-height:1 !important;
    }

    .paginacao-pedidos-wrap .pagination .page-item:first-child .page-link,
    .paginacao-pedidos-wrap .pagination .page-item:last-child .page-link{
        padding:0 14px !important;
    }

    .paginacao-pedidos-wrap .pagination .page-item > span{
        min-width:40px !important;
        width:auto !important;
        height:40px !important;
        padding:0 12px !important;
        font-size:.95rem !important;
        line-height:1 !important;
    }

    .paginacao-pedidos-wrap .pagination li{
        list-style:none !important;
    }

    .paginacao-pedidos-wrap .pagination *{
        transform:none !important;
    }
</style>

<div class="section-spacer"></div>

<div class="selecionados-bar">
    <div class="selecionados-info">
        Selecionados: <span id="contadorSelecionados">0</span>
    </div>

    <div class="actions-inline">
        <button type="button" class="btn btn-pdf-top btn-icon-inline" id="btnPdfSelecionados">
            <i class="bi bi-file-earmark-pdf-fill"></i>
            <span>Requisições</span>
        </button>

        <button type="button" class="btn btn-green btn-icon-inline" id="btnExcelSelecionados">
            <i class="bi bi-file-earmark-excel-fill"></i>
            <span>Planilha</span>
        </button>

        <button type="button" class="btn btn-dark" onclick="limparSelecao()">
            Limpar seleção
        </button>
    </div>
</div>

<div class="card card-lista-pedidos">
    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th class="pedido-check-col">
                        <div class="pedido-check-wrap">
                            <input type="checkbox" id="marcarTodosPedidos" class="pedido-checkbox">
                        </div>
                    </th>
                    <th>ID</th>
                    <th>Tipo</th>
                    <th>Local</th>
                    <th>Rota</th>
                    <th>Usuário</th>
                    <th>Itens</th>
                    <th>Unidades</th>
                    <th>Data</th>
                    <th class="acao-col">Ver</th>
                    <th class="acao-col">Editar</th>
                    <th class="acao-col">Deletar</th>
                    <th class="acao-col">PDF</th>
                    <th class="acao-col">Excel</th>
                    <th class="acao-col">Repetir</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $order)
                    <tr data-order-row="{{ $order->list_id }}">
                        <td class="pedido-check-col">
                            <div class="pedido-check-wrap">
                                @if($order->can_select_batch)
                                    <input
                                        type="checkbox"
                                        class="pedido-checkbox pedido-item-check"
                                        value="{{ $order->id }}"
                                        data-order-id="{{ $order->id }}"
                                        data-order-type="{{ $order->list_type }}"
                                    >
                                @else
                                    <input
                                        type="checkbox"
                                        class="pedido-checkbox"
                                        disabled
                                        title="Entrega de EPI não participa do lote"
                                    >
                                @endif
                            </div>
                        </td>

                        <td>{{ $order->display_id }}</td>

                        <td>
                            @if($order->display_scope === 'almoxarifado')
                                <span class="badge-status badge-warning">Almoxarifado</span>
                            @else
                                <span class="badge-status badge-info">Rota</span>
                            @endif
                        </td>

                        <td>{{ $order->display_local }}</td>
                        <td>{{ $order->display_route }}</td>
                        <td>{{ $order->display_user }}</td>
                        <td>{{ $order->display_items_count }}</td>
                        <td>{{ rtrim(rtrim(number_format((float) ($order->display_total_units ?? 0), 3, '.', ''), '0'), '.') }}</td>
                        <td>{{ optional($order->display_date)->format('d/m/Y') }}</td>

                        <td class="acao-col">
                            <button
                                type="button"
                                class="btn btn-sm-icon btn-info-icon btn-ver-pedido"
                                data-order-id="{{ $order->id }}"
                                data-order-type="{{ $order->list_type }}"
                                title="Ver detalhes"
                            >
                                <i class="bi bi-eye"></i>
                            </button>
                        </td>

                        <td class="acao-col">
                            @if($order->can_edit)
                                <a
                                    href="{{ route('orders.edit', $order->id) }}"
                                    class="btn btn-sm-icon btn-primary-icon"
                                    title="Editar"
                                >
                                    <i class="bi bi-pencil"></i>
                                </a>
                            @else
                                <span style="opacity:.4;">-</span>
                            @endif
                        </td>

                        <td class="acao-col">
                            @if($order->can_delete)
                                <form action="{{ route('orders.destroy', $order->id) }}" method="POST" onsubmit="return confirm('Excluir este pedido e devolver todo o estoque?')">
                                    @csrf
                                    @method('DELETE')
                                    <button
                                        type="submit"
                                        class="btn btn-sm-icon btn-danger-icon"
                                        title="Excluir"
                                    >
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            @else
                                <span style="opacity:.4;">-</span>
                            @endif
                        </td>

                        <td class="acao-col">
                            @if($order->can_pdf)
                                <a
                                    href="{{ route('orders.pdf.single', $order->id) }}"
                                    class="btn btn-sm-icon btn-pdf-icon"
                                    title="Baixar PDF"
                                >
                                    <i class="bi bi-filetype-pdf"></i>
                                </a>
                            @else
                                <span style="opacity:.4;">-</span>
                            @endif
                        </td>

                        <td class="acao-col">
                            @if($order->can_excel)
                                <a
                                    href="{{ route('orders.excel.single', $order->id) }}"
                                    class="btn btn-sm-icon btn-excel-icon"
                                    title="Baixar Excel"
                                >
                                    <i class="bi bi-filetype-xlsx"></i>
                                </a>
                            @else
                                <span style="opacity:.4;">-</span>
                            @endif
                        </td>

                        <td class="acao-col">
                            @if($order->can_repeat)
                                <a
                                    href="{{ route('orders.repeat', $order->id) }}"
                                    class="btn btn-sm-icon btn-repeat-icon"
                                    title="Repetir pedido"
                                >
                                    <i class="bi bi-arrow-repeat"></i>
                                </a>
                            @else
                                <span style="opacity:.4;">-</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="15" style="text-align:center;">
                            Nenhum pedido encontrado.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@if($orders instanceof \Illuminate\Pagination\AbstractPaginator && $orders->hasPages())
    <div class="card card-lista-pedidos card-paginacao-pedidos" style="margin-top:16px;">
        <div class="card-body">
            <div class="paginacao-pedidos-wrap">
                {{ $orders->onEachSide(1)->links('vendor.pagination.bootstrap-5-ptbr') }}
            </div>
        </div>
    </div>
@endif

<div class="custom-modal" id="modalPedidoVisualizar">
    <div class="custom-modal-backdrop" onclick="fecharModalPedido()"></div>

    <div class="custom-modal-dialog" style="max-width:1000px;">
        <div class="custom-modal-header">
            <div>
                <h3 id="modalPedidoTitulo">Pedido</h3>
                <p id="modalPedidoSubtitulo">Detalhes do pedido</p>
            </div>

            <button type="button" class="custom-modal-close" onclick="fecharModalPedido()">✕</button>
        </div>

        <div class="custom-modal-body">
            <div class="pedido-resumo-horizontal">
                <div class="pedido-resumo-item">
                    <span class="pedido-resumo-label">Tipo</span>
                    <strong id="pedidoTipo"></strong>
                </div>

                <div class="pedido-resumo-item">
                    <span class="pedido-resumo-label">Data</span>
                    <strong id="pedidoData"></strong>
                </div>

                <div class="pedido-resumo-item">
                    <span class="pedido-resumo-label">Rota</span>
                    <strong id="pedidoRota"></strong>
                </div>

                <div class="pedido-resumo-item">
                    <span class="pedido-resumo-label">Local</span>
                    <strong id="pedidoLocal"></strong>
                </div>

                <div class="pedido-resumo-item">
                    <span class="pedido-resumo-label">Usuário</span>
                    <strong id="pedidoUsuario"></strong>
                </div>

                <div class="pedido-resumo-item">
                    <span class="pedido-resumo-label">Status</span>
                    <strong id="pedidoStatus"></strong>
                </div>

                <div class="pedido-resumo-item">
                    <span class="pedido-resumo-label">Itens</span>
                    <strong id="pedidoTotalItens"></strong>
                </div>

                <div class="pedido-resumo-item">
                    <span class="pedido-resumo-label">Unidades</span>
                    <strong id="pedidoTotalUnidades"></strong>
                </div>
            </div>

            <div class="section-spacer"></div>

            <div class="card">
                <div class="card-header">
                    <div class="card-title">Itens do pedido</div>
                    <div class="card-subtitle">Produtos incluídos neste pedido.</div>
                </div>

                <div class="card-body">
                    <div class="table-wrap">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Produto</th>
                                    <th>Variação</th>
                                    <th>Quantidade</th>
                                    <th>Unidade</th>
                                </tr>
                            </thead>
                            <tbody id="pedidoItensBody"></tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="actions-inline" style="margin-top:18px;">
                <a href="#" id="btnEditarDoModal" class="btn btn-warning-soft">Editar</a>
                <button type="button" class="btn btn-dark" onclick="fecharModalPedido()">Fechar</button>
            </div>
        </div>
    </div>
</div>
<script>
    (function () {
        const PEDIDOS_STORAGE_KEY = 'orders_selected_ids_v1';

        function getCheckboxesPedidos() {
            return Array.from(document.querySelectorAll('.pedido-item-check'));
        }

        function getSelectedOrdersStorage() {
            try {
                const raw = sessionStorage.getItem(PEDIDOS_STORAGE_KEY);
                const data = JSON.parse(raw || '[]');

                if (!Array.isArray(data)) {
                    return [];
                }

                return [...new Set(data.map(String))];
            } catch (e) {
                return [];
            }
        }

        function setSelectedOrdersStorage(ids) {
            const idsUnicos = [...new Set((ids || []).map(String))];
            sessionStorage.setItem(PEDIDOS_STORAGE_KEY, JSON.stringify(idsUnicos));
        }

        function addSelectedOrder(id) {
            const ids = getSelectedOrdersStorage();
            const stringId = String(id);

            if (!ids.includes(stringId)) {
                ids.push(stringId);
                setSelectedOrdersStorage(ids);
            }
        }

        function removeSelectedOrder(id) {
            const stringId = String(id);
            const ids = getSelectedOrdersStorage().filter(function (item) {
                return item !== stringId;
            });

            setSelectedOrdersStorage(ids);
        }

        function getPedidosSelecionados() {
            return getSelectedOrdersStorage();
        }

        function atualizarContadorSelecionados() {
            const idsSelecionados = getSelectedOrdersStorage();
            const checkboxes = getCheckboxesPedidos();
            const checkboxVisiveisMarcados = checkboxes.filter(function (checkbox) {
                return checkbox.checked;
            });

            const contador = document.getElementById('contadorSelecionados');
            if (contador) {
                contador.innerText = idsSelecionados.length;
            }

            const marcarTodos = document.getElementById('marcarTodosPedidos');

            if (!marcarTodos) {
                return;
            }

            if (!checkboxes.length) {
                marcarTodos.checked = false;
                marcarTodos.indeterminate = false;
                return;
            }

            if (checkboxVisiveisMarcados.length === 0) {
                marcarTodos.checked = false;
                marcarTodos.indeterminate = false;
                return;
            }

            if (checkboxVisiveisMarcados.length === checkboxes.length) {
                marcarTodos.checked = true;
                marcarTodos.indeterminate = false;
                return;
            }

            marcarTodos.checked = false;
            marcarTodos.indeterminate = true;
        }

        function aplicarSelecaoSalvaNaPagina() {
            const idsSelecionados = getSelectedOrdersStorage();

            getCheckboxesPedidos().forEach(function (checkbox) {
                checkbox.checked = idsSelecionados.includes(String(checkbox.value));
            });

            atualizarContadorSelecionados();
        }

        function marcarDesmarcarTodosPedidos(checked) {
            getCheckboxesPedidos().forEach(function (checkbox) {
                checkbox.checked = checked;

                if (checked) {
                    addSelectedOrder(checkbox.value);
                } else {
                    removeSelectedOrder(checkbox.value);
                }
            });

            atualizarContadorSelecionados();
        }

        function limparSelecaoPedidos() {
            sessionStorage.removeItem(PEDIDOS_STORAGE_KEY);
            aplicarSelecaoSalvaNaPagina();
        }

        function criarInputHidden(name, value) {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = name;
            input.value = value;
            return input;
        }

        function enviarLote(url, ids, mensagemVazia) {
            if (!ids.length) {
                alert(mensagemVazia);
                return;
            }

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

            if (!csrfToken) {
                alert('Token CSRF não encontrado.');
                return;
            }

            const form = document.createElement('form');
            form.method = 'POST';
            form.action = url;
            form.style.display = 'none';
            form.setAttribute('data-no-ajax', 'true');

            form.appendChild(criarInputHidden('_token', csrfToken));

            ids.forEach(function (id) {
                form.appendChild(criarInputHidden('order_ids[]', id));
            });

            document.body.appendChild(form);
            form.submit();
        }

        async function abrirModalPedidoPorId(itemId, itemType = 'order') {
            const modal = document.getElementById('modalPedidoVisualizar');
            const tbody = document.getElementById('pedidoItensBody');

            const tituloPrefixo = itemType === 'epi_delivery' ? 'Entrega de EPI #' : 'Pedido #';

            document.getElementById('modalPedidoTitulo').innerText = tituloPrefixo + itemId;
            document.getElementById('modalPedidoSubtitulo').innerText = 'Carregando detalhes...';

            document.getElementById('pedidoTipo').innerText = '-';
            document.getElementById('pedidoLocal').innerText = '-';
            document.getElementById('pedidoRota').innerText = '-';
            document.getElementById('pedidoUsuario').innerText = '-';
            document.getElementById('pedidoData').innerText = '-';
            document.getElementById('pedidoStatus').innerText = '-';
            document.getElementById('pedidoTotalItens').innerText = '-';
            document.getElementById('pedidoTotalUnidades').innerText = '-';
            document.getElementById('btnEditarDoModal').href = '#';
            document.getElementById('btnEditarDoModal').style.display = itemType === 'order' ? 'inline-flex' : 'none';

            tbody.innerHTML = `<tr><td colspan="4" style="text-align:center;">Carregando...</td></tr>`;

            modal.classList.add('is-open');
            document.body.classList.add('modal-open');

            try {
                const quickViewUrl = itemType === 'epi_delivery'
                    ? `{{ url('/epi-deliveries') }}/${itemId}/quick-view`
                    : `{{ url('/pedidos') }}/${itemId}/quick-view`;

                const response = await fetch(quickViewUrl, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    }
                });

                if (!response.ok) {
                    throw new Error('Erro ao carregar.');
                }

                const pedido = await response.json();

                document.getElementById('modalPedidoTitulo').innerText = pedido.titulo || (tituloPrefixo + pedido.id);
                document.getElementById('modalPedidoSubtitulo').innerText = pedido.subtitulo || 'Visualização rápida';

                document.getElementById('pedidoTipo').innerText = pedido.tipo || '-';
                document.getElementById('pedidoLocal').innerText = pedido.local || '-';
                document.getElementById('pedidoRota').innerText = pedido.rota || '-';
                document.getElementById('pedidoUsuario').innerText = pedido.usuario || '-';
                document.getElementById('pedidoData').innerText = pedido.data || '-';
                document.getElementById('pedidoStatus').innerText = pedido.status || '-';
                document.getElementById('pedidoTotalItens').innerText = pedido.total_itens ?? '-';
                document.getElementById('pedidoTotalUnidades').innerText = pedido.total_unidades ?? '-';

                if (pedido.edit_url) {
                    document.getElementById('btnEditarDoModal').href = pedido.edit_url;
                }

                tbody.innerHTML = '';

                const itens = Array.isArray(pedido.itens) ? pedido.itens : [];

                if (!itens.length) {
                    tbody.innerHTML = `<tr><td colspan="4" style="text-align:center;">Nenhum item encontrado.</td></tr>`;
                    return;
                }

                itens.forEach(function(item) {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td>${item.produto || '-'}</td>
                        <td>${item.variacao || '-'}</td>
                        <td>${item.quantidade || '-'}</td>
                        <td>${item.unidade || '-'}</td>
                    `;
                    tbody.appendChild(tr);
                });
            } catch (error) {
                tbody.innerHTML = `<tr><td colspan="4" style="text-align:center;">Erro ao carregar os detalhes.</td></tr>`;
                document.getElementById('modalPedidoSubtitulo').innerText = 'Não foi possível carregar os detalhes';
            }
        }

        function fecharModalPedido() {
            const modal = document.getElementById('modalPedidoVisualizar');

            if (modal) {
                modal.classList.remove('is-open');
            }

            document.body.classList.remove('modal-open');
        }

        function rehidratarPedidos() {
            aplicarSelecaoSalvaNaPagina();
        }

        window.limparSelecao = limparSelecaoPedidos;
        window.fecharModalPedido = fecharModalPedido;
        window.abrirModalPedidoPorId = abrirModalPedidoPorId;
        window.__ordersRehydrate = rehidratarPedidos;

        if (!window.__ordersEventsBound) {
            window.__ordersEventsBound = true;

            document.addEventListener('change', function (event) {
                if (event.target && event.target.id === 'marcarTodosPedidos') {
                    marcarDesmarcarTodosPedidos(event.target.checked);
                    return;
                }

                if (event.target && event.target.classList.contains('pedido-item-check')) {
                    if (event.target.checked) {
                        addSelectedOrder(event.target.value);
                    } else {
                        removeSelectedOrder(event.target.value);
                    }

                    atualizarContadorSelecionados();
                }
            });

            document.addEventListener('click', function (event) {
                const btnPdf = event.target.closest('#btnPdfSelecionados');
                if (btnPdf) {
                    event.preventDefault();

                    enviarLote(
                        "{{ route('orders.pdf.batch') }}",
                        getPedidosSelecionados(),
                        'Selecione pelo menos um pedido para gerar as requisições PDF.'
                    );
                    return;
                }

                const btnExcel = event.target.closest('#btnExcelSelecionados');
                if (btnExcel) {
                    event.preventDefault();

                    enviarLote(
                        "{{ route('orders.excel.batch') }}",
                        getPedidosSelecionados(),
                        'Selecione pelo menos um pedido para gerar a contagem Excel.'
                    );
                    return;
                }

                const btnVer = event.target.closest('.btn-ver-pedido');
                if (btnVer) {
                    event.preventDefault();

                    abrirModalPedidoPorId(
                        btnVer.dataset.orderId,
                        btnVer.dataset.orderType || 'order'
                    );
                }
            });

            document.addEventListener('page:updated', function () {
                if (typeof window.__ordersRehydrate === 'function') {
                    window.__ordersRehydrate();
                }
            });
        }

        rehidratarPedidos();
    })();
</script>

@endsection