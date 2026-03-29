@extends('layouts.app')

@section('title', 'Movimentações de Estoque')

@section('content')
<div class="page-head">
    <div>
        <h2>Movimentações de Estoque</h2>
        <p>Acompanhe entradas, saídas e ajustes realizados no estoque.</p>
    </div>

    <div class="actions-inline">
        <a href="{{ route('stock-history.daily') }}" class="btn btn-dark">
            Resumo Diário
        </a>
        <a href="{{ route('products.index') }}" class="btn btn-dark">
            Voltar para Produtos
        </a>
    </div>
</div>

<div class="grid grid-3" style="margin-bottom:18px;">
    <div class="card kpi-card">
        <div class="card-body">
            <div class="kpi-label">Entradas no período</div>
            <div class="kpi-value">{{ rtrim(rtrim(number_format((float) $summary->total_entries, 3, '.', ''), '0'), '.') }}</div>
        </div>
    </div>

    <div class="card kpi-card">
        <div class="card-body">
            <div class="kpi-label">Saídas no período</div>
            <div class="kpi-value">{{ rtrim(rtrim(number_format((float) $summary->total_exits, 3, '.', ''), '0'), '.') }}</div>
        </div>
    </div>

    <div class="card kpi-card">
        <div class="card-body">
            <div class="kpi-label">Ajustes no período</div>
            <div class="kpi-value">{{ rtrim(rtrim(number_format((float) $summary->total_adjustments, 3, '.', ''), '0'), '.') }}</div>
        </div>
    </div>
</div>

<div class="card" style="margin-bottom:18px;">
    <div class="card-header">
        <div class="card-title">Filtros</div>
        <div class="card-subtitle">Use os filtros para localizar movimentações específicas.</div>
    </div>

    <div class="card-body">
        <form method="GET" action="{{ route('stock-history.index') }}">
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Data inicial</label>
                    <input type="date" name="date_from" class="form-control-custom" value="{{ $dateFrom }}">
                </div>

                <div class="form-group">
                    <label class="form-label">Data final</label>
                    <input type="date" name="date_to" class="form-control-custom" value="{{ $dateTo }}">
                </div>

                <div class="form-group">
                    <label class="form-label">Produto</label>
                    <select name="product_id" class="form-control-custom">
                        <option value="">Todos</option>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}" @selected((string)$productId === (string)$product->id)>
                                {{ $product->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Local</label>
                    <select name="stock_location_id" class="form-control-custom">
                        <option value="">Todos</option>
                        @foreach($locations as $location)
                            <option value="{{ $location->id }}" @selected((string)$locationId === (string)$location->id)>
                                {{ $location->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Tipo</label>
                    <select name="type" class="form-control-custom">
                        <option value="">Todos</option>
                        <option value="entry" @selected($type === 'entry')>Entrada</option>
                        <option value="exit" @selected($type === 'exit')>Saída</option>
                        <option value="adjustment" @selected($type === 'adjustment')>Ajuste</option>
                        <option value="initial" @selected($type === 'initial')>Inicial</option>
                    </select>
                </div>

                <div class="form-group form-group-full">
                    <div class="actions-inline">
                        <button type="submit" class="btn btn-green">Filtrar</button>
                        <a href="{{ route('stock-history.index') }}" class="btn btn-dark">Limpar filtros</a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <div class="card-title">Lista de Movimentações ({{ $movements->total() }})</div>
        <div class="card-subtitle">Cada registro representa uma alteração real no estoque.</div>
    </div>

    <div class="card-body">
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>Data</th>
                        <th>Produto</th>
                        <th>Variação</th>
                        <th>Tipo</th>
                        <th>Quantidade</th>
                        <th>Antes</th>
                        <th>Depois</th>
                        <th>Local</th>
                        <th>Origem</th>
                        <th>Documento</th>
                        <th>Observação</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($movements as $movement)
                        <tr>
                            <td>{{ optional($movement->movement_date)->format('d/m/Y') }}</td>
                            <td>{{ $movement->product?->name ?? '-' }}</td>
                            <td>{{ $movement->variant?->name ?? '-' }}</td>
                            <td>
                                @if($movement->type === 'entry')
                                    <span class="badge-status badge-success">Entrada</span>
                                @elseif($movement->type === 'exit')
                                    <span class="badge-status badge-warning">Saída</span>
                                @elseif($movement->type === 'adjustment')
                                    <span class="badge-status badge-info">Ajuste</span>
                                @else
                                    <span class="badge-status badge-info">Inicial</span>
                                @endif
                            </td>
                            <td>{{ rtrim(rtrim(number_format((float) $movement->quantity, 3, '.', ''), '0'), '.') }}</td>
                            <td>{{ rtrim(rtrim(number_format((float) $movement->balance_before, 3, '.', ''), '0'), '.') }}</td>
                            <td>{{ rtrim(rtrim(number_format((float) $movement->balance_after, 3, '.', ''), '0'), '.') }}</td>
                            <td>{{ $movement->location?->name ?? '-' }}</td>
                            <td>{{ $movement->source_name ?? '-' }}</td>
                            <td>{{ $movement->document_number ?? '-' }}</td>
                            <td>{{ $movement->notes ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11">Nenhuma movimentação encontrada.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div style="margin-top:18px;">
            {{ $movements->links() }}
        </div>
    </div>
</div>
@endsection