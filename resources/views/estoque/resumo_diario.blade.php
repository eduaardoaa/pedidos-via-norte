@extends('layouts.app')

@section('title', 'Resumo Diário de Estoque')

@section('content')
<div class="page-head">
    <div>
        <h2>Resumo Diário de Estoque</h2>
        <p>Veja entradas, saídas, saldo anterior e saldo atual por produto e variação.</p>
    </div>

    <div class="actions-inline">
        <a href="{{ route('stock-history.index') }}" class="btn btn-dark">
            Ver Movimentações
        </a>
    </div>
</div>

<div class="grid grid-3" style="margin-bottom:18px;">
    <div class="card kpi-card">
        <div class="card-body">
            <div class="kpi-label">Entradas do dia</div>
            <div class="kpi-value">{{ rtrim(rtrim(number_format((float) $cards['entries'], 3, '.', ''), '0'), '.') }}</div>
        </div>
    </div>

    <div class="card kpi-card">
        <div class="card-body">
            <div class="kpi-label">Saídas do dia</div>
            <div class="kpi-value">{{ rtrim(rtrim(number_format((float) $cards['exits'], 3, '.', ''), '0'), '.') }}</div>
        </div>
    </div>

    <div class="card kpi-card">
        <div class="card-body">
            <div class="kpi-label">Itens movimentados</div>
            <div class="kpi-value">{{ $cards['count'] }}</div>
        </div>
    </div>
</div>

<div class="card" style="margin-bottom:18px;">
    <div class="card-body">
        <form method="GET" action="{{ route('stock-history.daily') }}">
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Data</label>
                    <input type="date" name="date" class="form-control-custom" value="{{ $date }}">
                </div>

                <div class="form-group form-group-full">
                    <div class="actions-inline">
                        <button type="submit" class="btn btn-green">
                            Carregar Resumo
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <div class="card-title">Resumo do dia {{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}</div>
        <div class="card-subtitle">Entradas e saídas agrupadas por produto e variação.</div>
    </div>

    <div class="card-body">
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>Produto</th>
                        <th>Variação</th>
                        <th>Entradas do dia</th>
                        <th>Saídas do dia</th>
                        <th>Saldo anterior</th>
                        <th>Saldo atual</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $row)
                        <tr>
                            <td>{{ $row->product_name }}</td>
                            <td>{{ $row->variant_name ?? '-' }}</td>
                            <td>{{ rtrim(rtrim(number_format((float) $row->total_entries, 3, '.', ''), '0'), '.') }}</td>
                            <td>{{ rtrim(rtrim(number_format((float) $row->total_exits, 3, '.', ''), '0'), '.') }}</td>
                            <td>{{ rtrim(rtrim(number_format((float) $row->previous_balance, 3, '.', ''), '0'), '.') }}</td>
                            <td>{{ rtrim(rtrim(number_format((float) $row->current_balance, 3, '.', ''), '0'), '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">Nenhum movimento encontrado para essa data.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection