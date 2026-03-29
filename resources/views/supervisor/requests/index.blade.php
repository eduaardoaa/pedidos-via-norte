@extends('layouts.app')

@section('title', 'Minhas Solicitações - Supervisor')
@section('pageTitle', 'Solicitações')
@section('pageDescription', 'Acompanhe o histórico das suas solicitações de materiais.')

@section('content')
<style>
    .filters-grid-supervisor-requests{
        display:grid;
        grid-template-columns:repeat(4, minmax(0, 1fr));
        gap:12px;
        align-items:end;
    }

    .filters-grid-supervisor-requests .form-group{
        margin-bottom:0;
    }

    .table-actions{
        display:flex;
        gap:8px;
        flex-wrap:wrap;
        align-items:center;
    }

    .table-actions > .btn,
    .table-actions > a.btn,
    .table-actions > form{
        margin:0;
    }

    .table-actions form{
        display:inline-flex;
        margin:0;
    }

    .table-actions .btn{
        min-height:42px;
        padding:10px 16px;
        display:inline-flex;
        align-items:center;
        justify-content:center;
        gap:8px;
        border-radius:12px;
        white-space:nowrap;
    }

    .btn-action-inline{
        display:inline-flex;
        align-items:center;
        justify-content:center;
        gap:8px;
        margin:0;
        min-height:42px;
        padding:10px 16px;
        border-radius:12px;
        white-space:nowrap;
    }

    .table-actions form .btn-danger{
        width:auto;
    }

    @media (max-width: 900px){
        .filters-grid-supervisor-requests{
            grid-template-columns:repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 768px){
        .table-actions{
            flex-direction:column;
            align-items:stretch;
        }

        .table-actions .btn,
        .table-actions a.btn,
        .table-actions form,
        .table-actions form .btn{
            width:100%;
        }
    }

    @media (max-width: 520px){
        .filters-grid-supervisor-requests{
            grid-template-columns:1fr;
        }
    }

    .btn-danger{
        background: #dc3545;
        color: #fff;
        border: 1px solid #dc3545;
    }

    .btn-danger:hover{
        background: #bb2d3b;
        border-color: #bb2d3b;
        color: #fff;
    }
</style>

<div class="page-head">
    <div>
        <h2>Minhas Solicitações</h2>
        <p>Consulte o histórico das solicitações de materiais do almoxarifado feitas por você.</p>
    </div>

    <div class="actions-inline">
        <a href="{{ route('supervisor.requests.create') }}" class="btn btn-green">
            <i class="bi bi-plus-circle"></i>
            <span>Nova Solicitação</span>
        </a>
    </div>
</div>

@if(session('success'))
    <div class="alert-success-box">
        {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="alert-error-box">
        {{ session('error') }}
    </div>
@endif

<div class="card">
    <div class="card-header">
        <div>
            <div class="card-title">Filtros</div>
            <div class="card-subtitle">Refine sua busca por status, local e período</div>
        </div>
    </div>

    <div class="card-body">
        <form method="GET" action="{{ route('supervisor.requests.index') }}">
            <div class="filters-grid-supervisor-requests">
                <div class="form-group">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-control-custom">
                        <option value="">Todos</option>
                        <option value="pending" @selected(request('status') === 'pending')>Pendente</option>
                        <option value="approved" @selected(request('status') === 'approved')>Aprovada</option>
                        <option value="rejected" @selected(request('status') === 'rejected')>Recusada</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Local</label>
                    <select name="location_id" class="form-control-custom">
                        <option value="">Todos</option>
                        @foreach($locations as $location)
                            <option value="{{ $location->id }}" @selected((string) request('location_id') === (string) $location->id)>
                                {{ $location->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">De</label>
                    <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-control-custom">
                </div>

                <div class="form-group">
                    <label class="form-label">Até</label>
                    <input type="date" name="date_to" value="{{ request('date_to') }}" class="form-control-custom">
                </div>
            </div>

            <div class="actions-inline" style="margin-top:14px;">
                <button type="submit" class="btn btn-green">
                    <i class="bi bi-funnel"></i>
                    <span>Filtrar</span>
                </button>

                <a href="{{ route('supervisor.requests.index') }}" class="btn btn-dark">
                    <i class="bi bi-arrow-clockwise"></i>
                    <span>Limpar</span>
                </a>
            </div>
        </form>
    </div>
</div>

<div class="card" style="margin-top:16px;">
    <div class="card-header">
        <div>
            <div class="card-title">Solicitações cadastradas</div>
            <div class="card-subtitle">Lista completa das solicitações feitas por você</div>
        </div>
    </div>

    <div class="card-body">
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Local</th>
                        <th>Itens</th>
                        <th>Status</th>
                        <th>Data</th>
                        <th style="width:320px;">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($requests as $materialRequest)
                        <tr>
                            <td>#{{ $materialRequest->id }}</td>
                            <td>{{ $materialRequest->location->name ?? '-' }}</td>
                            <td>{{ $materialRequest->items->count() }}</td>
                            <td>
                                @if($materialRequest->status === 'pending')
                                    <span class="badge-status badge-warning">Pendente</span>
                                @elseif($materialRequest->status === 'approved')
                                    <span class="badge-status badge-success">Aprovada</span>
                                @elseif($materialRequest->status === 'rejected')
                                    <span class="badge-status badge-danger">Recusada</span>
                                @else
                                    <span class="badge-status">{{ ucfirst($materialRequest->status) }}</span>
                                @endif
                            </td>
                            <td>{{ $materialRequest->created_at?->format('d/m/Y H:i') }}</td>
                            <td>
                                <div class="table-actions">
                                    <button
                                        type="button"
                                        class="btn btn-dark"
                                        onclick="openRequestModal('{{ route('supervisor.requests.quick-view', $materialRequest->id) }}', false)"
                                    >
                                        <i class="bi bi-eye"></i>
                                        <span>Ver</span>
                                    </button>

                                    @if($materialRequest->status === 'pending')
                                        <a href="{{ route('supervisor.requests.edit', $materialRequest->id) }}" class="btn btn-green">
                                            <i class="bi bi-pencil-square"></i>
                                            <span>Editar</span>
                                        </a>

                                        <form
                                            method="POST"
                                            action="{{ route('supervisor.requests.destroy', $materialRequest->id) }}"
                                            onsubmit="return confirm('Tem certeza que deseja excluir esta solicitação?');"
                                        >
                                            @csrf
                                            @method('DELETE')

                                            <button type="submit" class="btn btn-danger btn-action-inline">
                                                <i class="bi bi-trash"></i>
                                                <span>Excluir</span>
                                            </button>
                                        </form>
                                    @else
                                        <a href="{{ route('supervisor.requests.redo', $materialRequest->id) }}" class="btn btn-green">
                                            <i class="bi bi-arrow-repeat"></i>
                                            <span>Repetir</span>
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">Nenhuma solicitação encontrada.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div style="margin-top:18px;">
            {{ $requests->links() }}
        </div>
    </div>
</div>

<div class="custom-modal" id="requestQuickViewModal">
    <div class="custom-modal-backdrop" onclick="closeRequestModal()"></div>

    <div class="custom-modal-dialog" style="max-width:900px;">
        <div class="custom-modal-header">
            <div>
                <h3 id="requestModalTitle">Detalhes da solicitação</h3>
                <p id="requestModalSubtitle">Visualização rápida da solicitação.</p>
            </div>

            <button type="button" class="custom-modal-close" onclick="closeRequestModal()">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <div class="custom-modal-body" id="requestModalBody">
            <div class="text-muted-small">Carregando...</div>
        </div>
    </div>
</div>

<script>
    function escapeHtml(value) {
        if (value === null || value === undefined) return '-';

        return String(value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function closeRequestModal() {
        const modal = document.getElementById('requestQuickViewModal');
        if (!modal) return;

        modal.classList.remove('is-open');
        document.body.classList.remove('modal-open');
    }

    async function openRequestModal(url, showRoute = false) {
        const modal = document.getElementById('requestQuickViewModal');
        const body = document.getElementById('requestModalBody');
        const title = document.getElementById('requestModalTitle');
        const subtitle = document.getElementById('requestModalSubtitle');

        if (!modal || !body || !title || !subtitle) return;

        title.textContent = 'Detalhes da solicitação';
        subtitle.textContent = 'Visualização rápida da solicitação.';
        body.innerHTML = '<div class="text-muted-small">Carregando...</div>';

        modal.classList.add('is-open');
        document.body.classList.add('modal-open');

        try {
            const response = await fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });

            if (!response.ok) {
                const errorText = await response.text();
                console.error('Erro quick-view:', response.status, errorText);
                throw new Error('Erro ao carregar solicitação.');
            }

            const data = await response.json();

            const statusBadge = data.status === 'pending'
                ? '<span class="badge-status badge-warning">Pendente</span>'
                : data.status === 'approved'
                    ? '<span class="badge-status badge-success">Aprovada</span>'
                    : data.status === 'rejected'
                        ? '<span class="badge-status badge-danger">Recusada</span>'
                        : '<span class="badge-status">' + escapeHtml(data.status) + '</span>';

            const routeHtml = showRoute ? `
                <div class="form-group">
                    <label class="form-label">Rota</label>
                    <div class="form-control-custom">${escapeHtml(data.route ?? '-')}</div>
                </div>
            ` : '';

            const itemsRows = (data.items || []).map(item => `
                <tr>
                    <td>${escapeHtml(item.product)}</td>
                    <td>${escapeHtml(item.variant)}</td>
                    <td>${escapeHtml(item.quantity)}</td>
                </tr>
            `).join('');

            body.innerHTML = `
                <div class="form-grid" style="grid-template-columns:repeat(3,minmax(0,1fr));gap:12px;">
                    <div class="form-group">
                        <label class="form-label">Status</label>
                        <div class="form-control-custom">${statusBadge}</div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Tipo</label>
                        <div class="form-control-custom">${escapeHtml(data.scope ?? '-')}</div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Local</label>
                        <div class="form-control-custom">${escapeHtml(data.location ?? '-')}</div>
                    </div>

                    ${routeHtml}

                    <div class="form-group">
                        <label class="form-label">Criada em</label>
                        <div class="form-control-custom">${escapeHtml(data.created_at ?? '-')}</div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Avaliada por</label>
                        <div class="form-control-custom">${escapeHtml(data.approved_by ?? '-')}</div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Avaliada em</label>
                        <div class="form-control-custom">${escapeHtml(data.approved_at ?? '-')}</div>
                    </div>
                </div>

                ${(data.notes ?? '') !== '' ? `
                    <div class="form-group" style="margin-top:16px;">
                        <label class="form-label">Observações do solicitante</label>
                        <div class="form-control-custom" style="min-height:80px;">${escapeHtml(data.notes)}</div>
                    </div>
                ` : ''}

                ${(data.admin_notes ?? '') !== '' ? `
                    <div class="form-group" style="margin-top:16px;">
                        <label class="form-label">Observações do admin</label>
                        <div class="form-control-custom" style="min-height:80px;">${escapeHtml(data.admin_notes)}</div>
                    </div>
                ` : ''}

                <div style="margin-top:18px;">
                    <div class="card-title" style="margin-bottom:10px;">Itens solicitados</div>

                    <div class="table-wrap">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Material</th>
                                    <th>Variação</th>
                                    <th>Quantidade</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${itemsRows || '<tr><td colspan="3">Nenhum item encontrado.</td></tr>'}
                            </tbody>
                        </table>
                    </div>
                </div>
            `;
        } catch (error) {
            console.error(error);
            body.innerHTML = `<div class="alert-error-box">Não foi possível carregar os detalhes da solicitação.</div>`;
        }
    }

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            closeRequestModal();
        }
    });
</script>
@endsection