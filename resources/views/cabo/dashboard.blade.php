@extends('layouts.app')

@section('title', 'Dashboard do Cabo de Turma - Vianorte')
@section('pageTitle', 'Painel')
@section('pageDescription', 'Acompanhe suas solicitações de materiais da rota.')

@section('content')
<style>
    .dashboard-mobile-grid{
        display:grid;
        grid-template-columns:repeat(4, minmax(0, 1fr));
        gap:16px;
    }

    .dashboard-kpi-card{
        min-height:110px;
        display:flex;
        align-items:center;
    }

    .dashboard-kpi-label{
        color:var(--muted);
        font-size:.95rem;
        margin-bottom:10px;
    }

    .dashboard-kpi-value{
        font-size:2rem;
        font-weight:800;
        line-height:1;
    }

    .dashboard-table-card .card-header{
        display:flex;
        justify-content:space-between;
        align-items:center;
        gap:14px;
        flex-wrap:wrap;
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

    .table-actions .btn-danger{
        background:linear-gradient(135deg, #dc3545, #b91c1c);
        color:#fff;
        border:1px solid rgba(255,255,255,.08);
        box-shadow:0 6px 16px rgba(220,53,69,.25);
    }

    .table-actions .btn-danger:hover{
        filter:brightness(1.05);
        color:#fff;
    }

    @media (max-width: 1100px){
        .dashboard-mobile-grid{
            grid-template-columns:repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 768px){
        .page-head{
            flex-direction:column;
            align-items:flex-start;
            gap:14px;
        }

        .page-head .actions-inline{
            width:100%;
            display:flex;
            flex-direction:column;
            gap:10px;
        }

        .page-head .actions-inline .btn{
            width:100%;
            justify-content:center;
        }

        .dashboard-mobile-grid{
            grid-template-columns:1fr;
            gap:12px;
        }

        .dashboard-kpi-card{
            min-height:auto;
        }

        .dashboard-kpi-label{
            font-size:.9rem;
            margin-bottom:8px;
        }

        .dashboard-kpi-value{
            font-size:1.8rem;
        }

        .dashboard-table-card .card-header{
            flex-direction:column;
            align-items:flex-start;
        }

        .dashboard-table-card .actions-inline{
            width:100%;
        }

        .dashboard-table-card .actions-inline .btn{
            width:100%;
            justify-content:center;
        }

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

    @media (max-width: 480px){
        .dashboard-kpi-value{
            font-size:1.6rem;
        }

        .card-header,
        .card-body{
            padding-left:14px !important;
            padding-right:14px !important;
        }
    }
</style>

<div class="page-head">
    <div>
        <h2>Dashboard do Cabo de Turma</h2>
        <p>Acompanhe suas solicitações e envie novos pedidos de materiais.</p>
    </div>

    <div class="actions-inline">
        <a href="{{ route('cabo.requests.create') }}" class="btn btn-green">
            <i class="bi bi-plus-circle"></i>
            <span>Nova Solicitação</span>
        </a>

        <a href="{{ route('cabo.requests.index') }}" class="btn btn-dark">
            <i class="bi bi-list-check"></i>
            <span>Ver Solicitações</span>
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

<div class="dashboard-mobile-grid">
    <div class="card dashboard-kpi-card">
        <div class="card-body">
            <div class="dashboard-kpi-label">Total de solicitações</div>
            <div class="dashboard-kpi-value">{{ $totalRequests }}</div>
        </div>
    </div>

    <div class="card dashboard-kpi-card">
        <div class="card-body">
            <div class="dashboard-kpi-label">Pendentes</div>
            <div class="dashboard-kpi-value">{{ $pendingRequests }}</div>
        </div>
    </div>

    <div class="card dashboard-kpi-card">
        <div class="card-body">
            <div class="dashboard-kpi-label">Aprovadas</div>
            <div class="dashboard-kpi-value">{{ $approvedRequests }}</div>
        </div>
    </div>

    <div class="card dashboard-kpi-card">
        <div class="card-body">
            <div class="dashboard-kpi-label">Recusadas</div>
            <div class="dashboard-kpi-value">{{ $rejectedRequests }}</div>
        </div>
    </div>
</div>

<div class="card dashboard-table-card" style="margin-top:16px;">
    <div class="card-header">
        <div>
            <div class="card-title">Últimas solicitações</div>
            <div class="card-subtitle">Histórico recente das suas solicitações de materiais da rota</div>
        </div>

        <div class="actions-inline">
            <a href="{{ route('cabo.requests.index') }}" class="btn btn-green">
                <i class="bi bi-arrow-right-circle"></i>
                <span>Ver todas</span>
            </a>
        </div>
    </div>

    <div class="card-body">
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Rota</th>
                        <th>Local</th>
                        <th>Status</th>
                        <th>Data</th>
                        <th style="width:320px;">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($latestRequests as $materialRequest)
                        <tr>
                            <td>#{{ $materialRequest->id }}</td>
                            <td>{{ $materialRequest->route->name ?? '-' }}</td>
                            <td>{{ $materialRequest->location->name ?? '-' }}</td>
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
                                        onclick="openRequestModal('{{ route('cabo.requests.quick-view', $materialRequest->id) }}', true)"
                                    >
                                        <i class="bi bi-eye"></i>
                                        <span>Ver</span>
                                    </button>

                                    @if($materialRequest->status === 'pending')
                                        <a href="{{ route('cabo.requests.edit', $materialRequest->id) }}" class="btn btn-green">
                                            <i class="bi bi-pencil-square"></i>
                                            <span>Editar</span>
                                        </a>

                                        <form
                                            method="POST"
                                            action="{{ route('cabo.requests.destroy', $materialRequest->id) }}"
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
                                        <a href="{{ route('cabo.requests.redo', $materialRequest->id) }}" class="btn btn-green">
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