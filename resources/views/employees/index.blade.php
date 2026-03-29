@extends('layouts.app')

@section('title', 'Funcionários')

@section('content')
<div class="page-head">
    <div>
        <h2>Funcionários</h2>
        <p>Gerencie os funcionários que receberão EPI.</p>
    </div>

    <div class="actions-inline">
    <a href="{{ route('epi.index') }}" class="btn btn-dark">
        <i class="bi bi-arrow-left"></i>
        <span>Voltar</span>
    </a>

    <a href="{{ route('employees.create') }}" class="btn btn-green">
        <i class="bi bi-plus-circle"></i>
        <span>Novo Funcionário</span>
    </a>
</div>
</div>

<div class="card" style="margin-bottom:18px;">
    <div class="card-body">
        <form method="GET" action="{{ route('employees.index') }}" id="formFiltrosFuncionarios">
            <div class="form-grid" style="grid-template-columns: 2fr 1fr;">
                <div class="form-group">
                    <label class="form-label">Buscar funcionário</label>
                    <input
                        type="text"
                        name="search"
                        id="filtroBuscaFuncionario"
                        class="form-control-custom"
                        placeholder="Nome, CPF ou matrícula"
                        value="{{ request('search') }}"
                    >
                </div>

                <div class="form-group">
                    <label class="form-label">Cargo</label>
                    <select name="cargo_id" id="filtroCargoFuncionario" class="form-control-custom">
                        <option value="">Todos</option>
                        @foreach($cargos as $cargo)
                            <option value="{{ $cargo->id }}" @selected((string) request('cargo_id') === (string) $cargo->id)>
                                {{ $cargo->nome ?? $cargo->name ?? $cargo->descricao ?? $cargo->cargo ?? ('Cargo #' . $cargo->id) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group form-group-full">
                    <div class="actions-inline">
                        <a href="{{ route('employees.index') }}" class="btn btn-dark">
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
        <div class="card-title">Lista de Funcionários ({{ $employees->total() }})</div>
        <div class="card-subtitle">
            Pesquise por nome, CPF, matrícula e filtre por cargo.
        </div>
    </div>

    <div class="card-body">
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>CPF</th>
                        <th>Matrícula</th>
                        <th>Cargo</th>
                        <th>Contratação</th>
                        <th>Status</th>
                        <th width="180">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($employees as $employee)
                        <tr>
                            <td>
                                <strong>{{ $employee->name }}</strong>
                            </td>

                            <td>{{ $employee->cpf }}</td>

                            <td>{{ $employee->registration }}</td>

                            <td>
                                {{ $employee->cargo->nome ?? $employee->cargo->name ?? $employee->cargo->descricao ?? $employee->cargo->cargo ?? '-' }}
                            </td>

                            <td>
                                {{ optional($employee->hired_at)->format('d/m/Y') }}
                            </td>

                            <td>
                                @if($employee->active)
                                    <span class="badge-status badge-success">Ativo</span>
                                @else
                                    <span class="badge-status badge-warning">Inativo</span>
                                @endif
                            </td>

                            <td>
                                <div class="table-actions">
                                    <a href="{{ route('employees.edit', $employee) }}" class="btn btn-dark">
                                        Editar
                                    </a>

                                    @if($employee->active)
                                        <form action="{{ route('employees.destroy', $employee) }}" method="POST" onsubmit="return confirm('Deseja realmente inativar este funcionário?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger-soft">
                                                Inativar
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">Nenhum funcionário encontrado.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div style="margin-top: 18px;">
            {{ $employees->links() }}
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const formFiltrosFuncionarios = document.getElementById('formFiltrosFuncionarios');
    const filtroBuscaFuncionario = document.getElementById('filtroBuscaFuncionario');
    const filtroCargoFuncionario = document.getElementById('filtroCargoFuncionario');

    let filtroTimeout = null;

    if (filtroBuscaFuncionario && formFiltrosFuncionarios) {
        filtroBuscaFuncionario.addEventListener('input', function () {
            clearTimeout(filtroTimeout);

            filtroTimeout = setTimeout(function () {
                formFiltrosFuncionarios.submit();
            }, 400);
        });
    }

    if (filtroCargoFuncionario && formFiltrosFuncionarios) {
        filtroCargoFuncionario.addEventListener('change', function () {
            formFiltrosFuncionarios.submit();
        });
    }
});
</script>
@endsection