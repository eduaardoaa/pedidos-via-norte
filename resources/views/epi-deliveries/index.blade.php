@extends('layouts.app')

@section('title', 'Entregas de EPI')

@section('content')
<div class="page-head">
    <div>
        <h2>Entregas de EPI</h2>
        <p>Acompanhe as entregas registradas para os funcionários.</p>
    </div>

    <div class="actions-inline">
        <a href="{{ route('epi.index') }}" class="btn btn-dark">
            Voltar
        </a>

        <a href="{{ route('epi-deliveries.create') }}" class="btn btn-green">
            + Nova Entrega
        </a>
    </div>
</div>

<div class="card" style="margin-bottom:18px;">
    <div class="card-body">
        <form method="GET" action="{{ route('epi-deliveries.index') }}" id="formFiltrosEntregasEpi">
            <div class="form-grid" style="grid-template-columns: 2fr 1fr 1fr;">
                <div class="form-group">
                    <label class="form-label">Buscar funcionário</label>
                    <input
                        type="text"
                        name="search"
                        id="filtroBuscaEntregaEpi"
                        class="form-control-custom"
                        placeholder="Nome, CPF ou matrícula"
                        value="{{ request('search') }}"
                    >
                </div>

                <div class="form-group">
                    <label class="form-label">Cargo</label>
                    <select name="cargo_id" id="filtroCargoEntregaEpi" class="form-control-custom">
                        <option value="">Todos</option>
                        @foreach($cargos as $cargo)
                            <option value="{{ $cargo->id }}" @selected((string) request('cargo_id') === (string) $cargo->id)>
                                {{ $cargo->nome ?? $cargo->name ?? $cargo->descricao ?? $cargo->cargo ?? ('Cargo #' . $cargo->id) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Mês da entrega</label>
                    <select name="period" id="filtroPeriodoEntregaEpi" class="form-control-custom">
                        <option value="">Todos</option>
                        @foreach($availablePeriods as $period)
                            <option value="{{ $period['value'] }}" @selected(request('period') === $period['value'])>
                                {{ \Illuminate\Support\Str::ucfirst($period['label']) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group form-group-full">
                    <div class="actions-inline">
                        <a href="{{ route('epi-deliveries.index') }}" class="btn btn-dark">
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
        {{ $errors->first() }}
    </div>
@endif

<div class="card">
    <div class="card-header">
        <div class="card-title">Lista de Entregas ({{ $deliveries->total() }})</div>
        <div class="card-subtitle">
            Filtre por funcionário, cargo e mês/ano da entrega.
        </div>
    </div>

    <div class="card-body">
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>Data</th>
                        <th>Funcionário</th>
                        <th>Cargo</th>
                        <th>Itens</th>
                        <th>Registrado por</th>
                        <th width="120">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($deliveries as $delivery)
                        <tr>
                            <td>
                                {{ optional($delivery->delivery_date)->format('d/m/Y') }}
                            </td>

                            <td>
                                <strong>{{ $delivery->employee->name ?? '-' }}</strong>
                                <div class="text-muted-small">
                                    Matrícula: {{ $delivery->employee->registration ?? '-' }}
                                </div>
                            </td>

                            <td>
                                {{ $delivery->employee->cargo->nome ?? $delivery->employee->cargo->name ?? $delivery->employee->cargo->descricao ?? $delivery->employee->cargo->cargo ?? '-' }}
                            </td>

                            <td>
                                {{ $delivery->items->count() }}
                            </td>

                            <td>
                                {{ $delivery->user->name ?? '-' }}
                            </td>

                            <td>
                                <div class="table-actions">
                                    <a href="{{ route('epi-deliveries.show', $delivery) }}" class="btn btn-dark">
                                        Ver
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">Nenhuma entrega registrada.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div style="margin-top:18px;">
            {{ $deliveries->links() }}
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const formFiltrosEntregasEpi = document.getElementById('formFiltrosEntregasEpi');
    const filtroBuscaEntregaEpi = document.getElementById('filtroBuscaEntregaEpi');
    const filtroCargoEntregaEpi = document.getElementById('filtroCargoEntregaEpi');
    const filtroPeriodoEntregaEpi = document.getElementById('filtroPeriodoEntregaEpi');

    let filtroTimeout = null;

    if (filtroBuscaEntregaEpi && formFiltrosEntregasEpi) {
        filtroBuscaEntregaEpi.addEventListener('input', function () {
            clearTimeout(filtroTimeout);

            filtroTimeout = setTimeout(function () {
                formFiltrosEntregasEpi.submit();
            }, 400);
        });
    }

    if (filtroCargoEntregaEpi && formFiltrosEntregasEpi) {
        filtroCargoEntregaEpi.addEventListener('change', function () {
            formFiltrosEntregasEpi.submit();
        });
    }

    if (filtroPeriodoEntregaEpi && formFiltrosEntregasEpi) {
        filtroPeriodoEntregaEpi.addEventListener('change', function () {
            formFiltrosEntregasEpi.submit();
        });
    }
});
</script>
@endsection