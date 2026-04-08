@extends('layouts.app')

@section('title', 'Controle de EPI')

@section('content')
<div class="page-head">
    <div>
        <h2>Controle de EPI</h2>
        <p>Gerencie funcionários e acompanhe os próximos recebimentos de EPI.</p>
    </div>

    <div class="actions-inline">
        

        <a href="{{ route('epi-deliveries.create') }}" class="btn btn-green">
            + Nova Entrega
        </a>

        <a href="{{ route('employees.index') }}" class="btn btn-dark">
            Funcionários
        </a>

        <a href="{{ route('epi-deliveries.index') }}" class="btn btn-dark">
            Entregas
        </a>
    </div>
</div>

<div class="card" style="margin-bottom:18px;">
    <div class="card-body">
        <form method="GET" action="{{ route('epi.index') }}" id="formFiltrosEpi">
            <div class="form-grid" style="grid-template-columns: 2fr 1fr 1fr 1fr;">
                <div class="form-group">
                    <label class="form-label">Pesquisar funcionário</label>
                    <input
                        type="text"
                        name="search"
                        id="filtroBuscaFuncionarioEpi"
                        class="form-control-custom"
                        placeholder="Digite o nome do funcionário"
                        value="{{ request('search') }}"
                    >
                </div>

                <div class="form-group">
                    <label class="form-label">Cargo</label>
                    <select name="cargo_id" id="filtroCargoEpi" class="form-control-custom">
                        <option value="">Todos</option>
                        @foreach($cargos as $cargo)
                            <option value="{{ $cargo->id }}" @selected((string) request('cargo_id') === (string) $cargo->id)>
                                {{ $cargo->nome ?? $cargo->name ?? $cargo->descricao ?? $cargo->cargo ?? ('Cargo #' . $cargo->id) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Mês previsto</label>
                    <select name="period" id="filtroPeriodoEpi" class="form-control-custom">
                        <option value="">Todos</option>
                        @foreach($availablePeriods as $period)
                            <option value="{{ $period['value'] }}" @selected(request('period') === $period['value'])>
                                {{ \Illuminate\Support\Str::ucfirst($period['label']) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Status</label>
                    <select name="due_status" id="filtroStatusEpi" class="form-control-custom">
                        <option value="">Todos</option>
                        <option value="overdue" @selected(request('due_status') === 'overdue')>Atrasados</option>
                        <option value="today" @selected(request('due_status') === 'today')>Vence hoje</option>
                        <option value="upcoming" @selected(request('due_status') === 'upcoming')>Próximos</option>
                        <option value="no_date" @selected(request('due_status') === 'no_date')>Sem previsão</option>
                    </select>
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
        <strong>Corrija os erros abaixo:</strong>
        <ul style="margin-top:8px; padding-left:18px;">
            @foreach($errors->all() as $erro)
                <li>{{ $erro }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="card">
    <div class="card-header">
        <div class="card-title">Lista de Funcionários ({{ $employees->count() }})</div>
        <div class="card-subtitle">
            Visualize o último recebimento e o próximo item previsto para entrega de EPI.
        </div>
    </div>

    <div class="card-body">
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>Funcionário</th>
                        <th>Cargo</th>
                        <th>Último recebimento</th>
                        <th>Próximo recebimento</th>
                        <th>Item</th>
                        <th width="220">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($employees as $employee)
                        @php
                            $nextDate = $employee->next_expected_date ? \Carbon\Carbon::parse($employee->next_expected_date) : null;
                            $today = now()->startOfDay();

                            $statusLabel = null;
                            $statusClass = 'background:#eef2f7;color:#334155;';

                            if ($nextDate) {
                                if ($nextDate->lt($today)) {
                                    $statusLabel = 'Atrasado';
                                    $statusClass = 'background:#fee2e2;color:#b91c1c;';
                                } elseif ($nextDate->isSameDay($today)) {
                                    $statusLabel = 'Vence hoje';
                                    $statusClass = 'background:#fef3c7;color:#92400e;';
                                } else {
                                    $statusLabel = 'Em dia';
                                    $statusClass = 'background:#dcfce7;color:#166534;';
                                }
                            } else {
                                $statusLabel = 'Sem previsão';
                                $statusClass = 'background:#e5e7eb;color:#374151;';
                            }
                        @endphp

                        <tr>
                            <td>
                                <strong>{{ $employee->name }}</strong>
                                <div class="text-muted-small">Matrícula: {{ $employee->registration }}</div>
                            </td>

                            <td>
                                {{ $employee->cargo->nome ?? $employee->cargo->name ?? $employee->cargo->descricao ?? $employee->cargo->cargo ?? '-' }}
                            </td>

                            <td>
                                {{ $employee->last_delivery_date ? \Carbon\Carbon::parse($employee->last_delivery_date)->format('d/m/Y') : '-' }}
                            </td>

                            <td>
                                @if($employee->next_expected_date)
                                    {{ \Carbon\Carbon::parse($employee->next_expected_date)->format('d/m/Y') }}
                                    <div style="margin-top:6px;">
                                        <span style="display:inline-block;padding:4px 10px;border-radius:999px;font-size:12px;font-weight:700;{{ $statusClass }}">
                                            {{ $statusLabel }}
                                        </span>
                                    </div>
                                @else
                                    -
                                    <div style="margin-top:6px;">
                                        <span style="display:inline-block;padding:4px 10px;border-radius:999px;font-size:12px;font-weight:700;{{ $statusClass }}">
                                            {{ $statusLabel }}
                                        </span>
                                    </div>
                                @endif
                            </td>

                            <td>
                                {{ $employee->next_item_name ?: '-' }}
                            </td>

                            <td>
                                <div class="table-actions">
                                    <a href="{{ route('epi-deliveries.create') }}?employee_id={{ $employee->id }}" class="btn btn-green">
                                        Entregar
                                    </a>

                                    <a href="{{ route('epi.employee-history', $employee) }}" class="btn btn-dark">
                                        Histórico
                                    </a>

                                    <a href="{{ route('employees.edit', $employee) }}" class="btn btn-dark">
                                        Editar
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">Nenhum funcionário encontrado.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const formFiltrosEpi = document.getElementById('formFiltrosEpi');
    const filtroBuscaFuncionarioEpi = document.getElementById('filtroBuscaFuncionarioEpi');
    const filtroCargoEpi = document.getElementById('filtroCargoEpi');
    const filtroPeriodoEpi = document.getElementById('filtroPeriodoEpi');
    const filtroStatusEpi = document.getElementById('filtroStatusEpi');

    let filtroTimeout = null;

    if (filtroBuscaFuncionarioEpi && formFiltrosEpi) {
        filtroBuscaFuncionarioEpi.addEventListener('input', function () {
            clearTimeout(filtroTimeout);

            filtroTimeout = setTimeout(function () {
                formFiltrosEpi.submit();
            }, 400);
        });
    }

    if (filtroCargoEpi && formFiltrosEpi) {
        filtroCargoEpi.addEventListener('change', function () {
            formFiltrosEpi.submit();
        });
    }

    if (filtroPeriodoEpi && formFiltrosEpi) {
        filtroPeriodoEpi.addEventListener('change', function () {
            formFiltrosEpi.submit();
        });
    }

    if (filtroStatusEpi && formFiltrosEpi) {
        filtroStatusEpi.addEventListener('change', function () {
            formFiltrosEpi.submit();
        });
    }
});
</script>
@endsection