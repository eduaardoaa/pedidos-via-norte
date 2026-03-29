@extends('layouts.app')

@section('title', 'Solicitações de Materiais - Vianorte')
@section('pageTitle', 'Solicitações de Materiais')
@section('pageDescription', 'Gerencie as solicitações enviadas por cabo de turma e supervisor.')

@section('content')
<style>
    .filters-grid-material-requests{
        display:grid;
        grid-template-columns:repeat(6, minmax(0, 1fr));
        gap:12px;
        align-items:end;
    }

    .filters-grid-material-requests .form-group{
        margin-bottom:0;
    }

    .filters-grid-material-requests .form-label{
        font-size:.82rem;
        margin-bottom:6px;
    }

    .filters-grid-material-requests .form-control-custom{
        min-height:42px;
        padding:10px 12px;
        font-size:.92rem;
    }

    .material-requests-actions{
        display:flex;
        gap:10px;
        flex-wrap:wrap;
        margin-top:14px;
    }

    .material-requests-table .badge-status{
        white-space:nowrap;
    }

    .request-address-link{
        color:#60a5fa;
        text-decoration:none;
        font-weight:600;
        word-break:break-word;
    }

    .request-address-link:hover{
        text-decoration:underline;
    }

    .request-address-muted{
        color:var(--muted);
        font-size:.92rem;
    }

    @media (max-width: 1300px){
        .filters-grid-material-requests{
            grid-template-columns:repeat(3, minmax(0, 1fr));
        }
    }

    @media (max-width: 768px){
        .filters-grid-material-requests{
            grid-template-columns:repeat(2, minmax(0, 1fr));
        }

        .material-requests-actions{
            flex-direction:column;
        }

        .material-requests-actions .btn{
            width:100%;
            justify-content:center;
        }
    }

    @media (max-width: 520px){
        .filters-grid-material-requests{
            grid-template-columns:1fr;
        }
    }
</style>

<div class="page-head">
    <div>
        <h2>Solicitações de Materiais</h2>
        <p>Gerencie as solicitações enviadas por cabo de turma e supervisor.</p>
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
            <div class="card-title">Filtros de busca</div>
            <div class="card-subtitle">Refine os resultados por status, tipo, cargo, solicitante e período</div>
        </div>
    </div>

    <div class="card-body">
        <form method="GET" action="{{ route('admin.material-requests.index') }}">
            <div class="filters-grid-material-requests">
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
                    <label class="form-label">Tipo</label>
                    <select name="scope" class="form-control-custom">
                        <option value="">Todos</option>
                        <option value="rota" @selected(request('scope') === 'rota')>Rota</option>
                        <option value="almoxarifado" @selected(request('scope') === 'almoxarifado')>Almoxarifado</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Cargo</label>
                    <select name="requester_role" class="form-control-custom">
                        <option value="">Todos</option>
                        <option value="cabo_turma" @selected(request('requester_role') === 'cabo_turma')>Cabo de turma</option>
                        <option value="supervisor" @selected(request('requester_role') === 'supervisor')>Supervisor</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Solicitante</label>
                    <select name="user_id" class="form-control-custom">
                        <option value="">Todos</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" @selected((string) request('user_id') === (string) $user->id)>
                                {{ $user->name }}
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

            <div class="material-requests-actions">
                <button type="submit" class="btn btn-green">
                    <i class="bi bi-funnel"></i>
                    <span>Filtrar</span>
                </button>

                <a href="{{ route('admin.material-requests.index') }}" class="btn btn-dark">
                    <i class="bi bi-arrow-clockwise"></i>
                    <span>Limpar</span>
                </a>
            </div>
        </form>
    </div>
</div>

<div class="card material-requests-table" style="margin-top:16px;">
    <div class="card-header">
        <div>
            <div class="card-title">Solicitações cadastradas</div>
            <div class="card-subtitle">Lista de solicitações enviadas para análise do administrador</div>
        </div>
    </div>

    <div class="card-body">
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Solicitante</th>
                        <th>Cargo</th>
                        <th>Tipo</th>
                        <th>Rota</th>
                        <th>Local</th>
                        <th>Endereço</th>
                        <th>Status</th>
                        <th>Data</th>
                        <th style="width: 120px;">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($requests as $requestItem)
                        @php
                            $requestAddress = trim((string) ($requestItem->request_full_address ?? ''));
                            $mapsQuery = $requestAddress !== '' ? urlencode($requestAddress) : '';
                            $mapsUrl = $mapsQuery !== '' ? 'https://www.google.com/maps/search/?api=1&query=' . $mapsQuery : null;
                        @endphp

                        <tr>
                            <td>#{{ $requestItem->id }}</td>
                            <td>{{ $requestItem->user->name ?? '-' }}</td>
                            <td>
                                {{ $requestItem->requester_role === 'cabo_turma' ? 'Cabo de turma' : 'Supervisor' }}
                            </td>
                            <td>{{ ucfirst($requestItem->scope) }}</td>
                            <td>{{ $requestItem->route->name ?? '-' }}</td>
                            <td>{{ $requestItem->location->name ?? '-' }}</td>
                            <td>
                                @if($requestItem->scope === 'rota')
                                    @if($mapsUrl)
                                        <a
                                            href="{{ $mapsUrl }}"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            class="request-address-link"
                                            title="Abrir no Google Maps"
                                        >
                                            {{ $requestAddress }}
                                        </a>
                                    @else
                                        <span class="request-address-muted">Não informado</span>
                                    @endif
                                @else
                                    <span class="request-address-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($requestItem->status === 'pending')
                                    <span class="badge-status badge-warning">Pendente</span>
                                @elseif($requestItem->status === 'approved')
                                    <span class="badge-status badge-success">Aprovada</span>
                                @elseif($requestItem->status === 'rejected')
                                    <span class="badge-status badge-danger">Recusada</span>
                                @else
                                    <span class="badge-status">{{ ucfirst($requestItem->status) }}</span>
                                @endif
                            </td>
                            <td>{{ $requestItem->created_at?->format('d/m/Y H:i') }}</td>
                            <td>
                                <div class="table-actions">
                                    <a href="{{ route('admin.material-requests.show', $requestItem) }}" class="btn btn-dark">
                                        <i class="bi bi-eye"></i>
                                        <span>Ver</span>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10">Nenhuma solicitação encontrada.</td>
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
@endsection