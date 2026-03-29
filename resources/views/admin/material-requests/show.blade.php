@extends('layouts.app')

@section('title', 'Detalhe da Solicitação - Vianorte')
@section('pageTitle', 'Detalhe da Solicitação')
@section('pageDescription', 'Visualize e gerencie os dados completos da solicitação.')

@section('content')
<style>
    .material-request-info-grid{
        display:grid;
        grid-template-columns:repeat(3, minmax(0, 1fr));
        gap:16px;
    }

    .material-request-info-item{
        padding:14px 16px;
        border-radius:14px;
        background:rgba(255,255,255,.03);
        border:1px solid rgba(255,255,255,.05);
    }

    .material-request-info-label{
        font-size:.82rem;
        color:var(--muted);
        margin-bottom:6px;
    }

    .material-request-info-value{
        font-size:1rem;
        font-weight:600;
        color:#fff;
        word-break:break-word;
    }

    .material-request-notes-box{
        min-height:88px;
        white-space:pre-wrap;
    }

    .material-request-actions-grid{
        display:grid;
        grid-template-columns:repeat(2, minmax(0, 1fr));
        gap:16px;
    }

    .material-request-action-card{
        padding:16px;
        border-radius:16px;
        background:rgba(255,255,255,.03);
        border:1px solid rgba(255,255,255,.05);
    }

    .material-request-action-title{
        font-size:1rem;
        font-weight:700;
        margin-bottom:6px;
    }

    .material-request-action-subtitle{
        color:var(--muted);
        font-size:.9rem;
        margin-bottom:14px;
    }

    @media (max-width: 1100px){
        .material-request-info-grid{
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
        }

        .page-head .actions-inline .btn{
            width:100%;
            justify-content:center;
        }

        .material-request-info-grid,
        .material-request-actions-grid{
            grid-template-columns:1fr;
        }
    }
</style>

<div class="page-head">
    <div>
        <h2>Solicitação #{{ $materialRequest->id }}</h2>
        <p>Detalhes completos da solicitação de materiais enviada ao administrador.</p>
    </div>

    <div class="actions-inline">
        <a href="{{ route('admin.material-requests.index') }}" class="btn btn-dark">
            <i class="bi bi-arrow-left-circle"></i>
            <span>Voltar</span>
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
            <div class="card-title">Informações gerais</div>
            <div class="card-subtitle">Dados principais da solicitação e do solicitante</div>
        </div>
    </div>

    <div class="card-body">
        <div class="material-request-info-grid">
            <div class="material-request-info-item">
                <div class="material-request-info-label">Solicitante</div>
                <div class="material-request-info-value">{{ $materialRequest->user->name ?? '-' }}</div>
            </div>

            <div class="material-request-info-item">
                <div class="material-request-info-label">Cargo</div>
                <div class="material-request-info-value">
                    {{ $materialRequest->requester_role === 'cabo_turma' ? 'Cabo de turma' : 'Supervisor' }}
                </div>
            </div>

            <div class="material-request-info-item">
                <div class="material-request-info-label">Status</div>
                <div class="material-request-info-value">
                    @if($materialRequest->status === 'pending')
                        <span class="badge-status badge-warning">Pendente</span>
                    @elseif($materialRequest->status === 'approved')
                        <span class="badge-status badge-success">Aprovada</span>
                    @elseif($materialRequest->status === 'rejected')
                        <span class="badge-status badge-danger">Recusada</span>
                    @else
                        <span class="badge-status">{{ ucfirst($materialRequest->status) }}</span>
                    @endif
                </div>
            </div>

            <div class="material-request-info-item">
                <div class="material-request-info-label">Tipo</div>
                <div class="material-request-info-value">{{ ucfirst($materialRequest->scope) }}</div>
            </div>

            <div class="material-request-info-item">
                <div class="material-request-info-label">Rota</div>
                <div class="material-request-info-value">{{ $materialRequest->route->name ?? '-' }}</div>
            </div>

            <div class="material-request-info-item">
                <div class="material-request-info-label">Local</div>
                <div class="material-request-info-value">{{ $materialRequest->location->name ?? '-' }}</div>
            </div>

            <div class="material-request-info-item">
                <div class="material-request-info-label">Criada em</div>
                <div class="material-request-info-value">{{ $materialRequest->created_at?->format('d/m/Y H:i') }}</div>
            </div>

            <div class="material-request-info-item">
                <div class="material-request-info-label">Avaliada por</div>
                <div class="material-request-info-value">{{ $materialRequest->approver->name ?? '-' }}</div>
            </div>

            <div class="material-request-info-item">
                <div class="material-request-info-label">Avaliada em</div>
                <div class="material-request-info-value">{{ $materialRequest->approved_at?->format('d/m/Y H:i') ?? '-' }}</div>
            </div>
        </div>

        @if($materialRequest->notes)
            <div style="margin-top:18px;">
                <label class="form-label">Observações do solicitante</label>
                <div class="form-control-custom material-request-notes-box">
                    {{ $materialRequest->notes }}
                </div>
            </div>
        @endif

        @if($materialRequest->admin_notes)
            <div style="margin-top:18px;">
                <label class="form-label">Observações do admin</label>
                <div class="form-control-custom material-request-notes-box">
                    {{ $materialRequest->admin_notes }}
                </div>
            </div>
        @endif
    </div>
</div>

<div class="card" style="margin-top:16px;">
    <div class="card-header">
        <div>
            <div class="card-title">Itens solicitados</div>
            <div class="card-subtitle">Materiais incluídos nesta solicitação</div>
        </div>
    </div>

    <div class="card-body">
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
                    @forelse($materialRequest->items as $item)
                        <tr>
                            <td>{{ $item->product->name ?? '-' }}</td>
                            <td>{{ $item->variant->name ?? 'Sem variação' }}</td>
                            <td>{{ $item->quantity }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3">Nenhum item encontrado.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@if($materialRequest->status === 'pending')
    <div class="card" style="margin-top:16px;">
        <div class="card-header">
            <div>
                <div class="card-title">Ações do administrador</div>
                <div class="card-subtitle">Aprove ou recuse a solicitação com uma observação opcional</div>
            </div>
        </div>

        <div class="material-request-action-card">
    <div class="material-request-action-title">Aprovar e gerar pedido</div>
    <div class="material-request-action-subtitle">
        Abra o formulário de pedido já preenchido com os itens da solicitação para revisar e salvar a saída real de estoque.
    </div>

    <a href="{{ route('admin.material-requests.redirect-to-order', $materialRequest) }}" class="btn btn-green">
        <i class="bi bi-check-circle"></i>
        <span>Aprovar e abrir pedido</span>
    </a>
</div>

                <div class="material-request-action-card">
                    <div class="material-request-action-title">Recusar solicitação</div>
                    <div class="material-request-action-subtitle">
                        Use esta opção para informar que a solicitação não foi aprovada.
                    </div>

                    <form method="POST" action="{{ route('admin.material-requests.reject', $materialRequest) }}">
                        @csrf

                        <div class="form-group">
                            <label class="form-label">Motivo da recusa</label>
                            <textarea
                                name="admin_notes"
                                rows="4"
                                class="form-control-custom"
                                placeholder="Informe o motivo da recusa"
                            ></textarea>
                        </div>

                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-x-circle"></i>
                            <span>Recusar solicitação</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endif
@endsection