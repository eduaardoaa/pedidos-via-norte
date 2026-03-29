@extends('layouts.app')

@section('title', 'Detalhe da Solicitação')
@section('pageTitle', 'Detalhe da Solicitação')
@section('pageDescription', 'Visualize os dados completos da sua solicitação.')

@section('content')
<div class="page-head">
    <div>
        <h2>Solicitação #{{ $materialRequest->id }}</h2>
        <p>Detalhes completos da sua solicitação de materiais.</p>
    </div>

    <div class="actions-inline">
        <a href="{{ route('cabo.requests.index') }}" class="btn btn-dark">
            <i class="bi bi-arrow-left-circle"></i>
            <span>Voltar</span>
        </a>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <div>
            <div class="card-title">Informações gerais</div>
            <div class="card-subtitle">Dados principais da solicitação</div>
        </div>
    </div>

    <div class="card-body">
        <div class="filters-grid-3">
            <div><strong>Status:</strong><br>
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

            <div><strong>Tipo:</strong><br>{{ ucfirst($materialRequest->scope) }}</div>
            <div><strong>Rota:</strong><br>{{ $materialRequest->route->name ?? '-' }}</div>
            <div><strong>Local:</strong><br>{{ $materialRequest->location->name ?? '-' }}</div>
            <div><strong>Criada em:</strong><br>{{ $materialRequest->created_at?->format('d/m/Y H:i') }}</div>
            <div><strong>Avaliada por:</strong><br>{{ $materialRequest->approver->name ?? '-' }}</div>
        </div>

        @if($materialRequest->notes)
            <div style="margin-top:18px;">
                <label class="form-label">Observações do solicitante</label>
                <div class="form-control-custom" style="min-height:88px;">{{ $materialRequest->notes }}</div>
            </div>
        @endif

        @if($materialRequest->admin_notes)
            <div style="margin-top:18px;">
                <label class="form-label">Observações do admin</label>
                <div class="form-control-custom" style="min-height:88px;">{{ $materialRequest->admin_notes }}</div>
            </div>
        @endif
    </div>
</div>

<div class="card" style="margin-top:16px;">
    <div class="card-header">
        <div>
            <div class="card-title">Itens solicitados</div>
            <div class="card-subtitle">Materiais incluídos na solicitação</div>
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
@endsection