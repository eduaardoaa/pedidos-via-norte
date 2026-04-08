@extends('layouts.app')

@section('title', 'Movimentações de Estoque')

@section('content')
<div class="page-head">
    <div>
        <h2>Movimentações de Estoque</h2>
        <p>Acompanhe o resumo consolidado de entradas, saídas e ajustes por produto no período.</p>
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
        <div class="card-title">Resumo por Produto ({{ $rows->count() }})</div>
        <div class="card-subtitle">Cada linha mostra o consolidado do período por produto/variação.</div>
    </div>

    <div class="card-body">
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>Produto</th>
                        <th>Variação</th>
                        <th>Saldo inicial</th>
                        <th>Entradas</th>
                        <th>Saídas</th>
                        <th>Ajustes</th>
                        <th>Saldo final</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $row)
                        <tr>
                            <td>{{ $row->product_name ?? '-' }}</td>
                            <td>{{ $row->variant_name ?? '-' }}</td>
                            <td>{{ rtrim(rtrim(number_format((float) $row->opening_balance, 3, '.', ''), '0'), '.') }}</td>
                            <td>{{ rtrim(rtrim(number_format((float) $row->total_entries, 3, '.', ''), '0'), '.') }}</td>
                            <td>{{ rtrim(rtrim(number_format((float) $row->total_exits, 3, '.', ''), '0'), '.') }}</td>
                            <td>{{ rtrim(rtrim(number_format((float) $row->total_adjustments, 3, '.', ''), '0'), '.') }}</td>
                            <td>{{ rtrim(rtrim(number_format((float) $row->closing_balance, 3, '.', ''), '0'), '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">Nenhuma movimentação encontrada.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection