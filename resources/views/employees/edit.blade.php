@extends('layouts.app')

@section('title', 'Editar Funcionário')

@section('content')
<div class="page-head">
    <div>
        <h2>Editar Funcionário</h2>
        <p>Atualize os dados do funcionário selecionado.</p>
    </div>

    <div class="actions-inline">
        <a href="{{ route('employees.index') }}" class="btn btn-dark">
            Voltar
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

@if($errors->any())
    <div class="alert-error-box">
        {{ $errors->first() }}
    </div>
@endif

<div class="card">
    <div class="card-header">
        <div class="card-title">Editar funcionário</div>
        <div class="card-subtitle">Atualize os dados cadastrais e status do colaborador</div>
    </div>

    <div class="card-body">
        <form action="{{ route('employees.update', $employee) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Nome</label>
                    <input
                        type="text"
                        name="name"
                        class="form-control-custom"
                        value="{{ old('name', $employee->name) }}"
                        placeholder="Nome completo"
                        required
                    >
                </div>

                <div class="form-group">
                    <label class="form-label">Matrícula</label>
                    <input
                        type="text"
                        name="registration"
                        class="form-control-custom"
                        value="{{ old('registration', $employee->registration) }}"
                        placeholder="Número da matrícula"
                        required
                    >
                </div>

                <div class="form-group">
                    <label class="form-label">CPF</label>
                    <input
                        type="text"
                        name="cpf"
                        class="form-control-custom input-cpf"
                        value="{{ old('cpf', $employee->cpf) }}"
                        placeholder="000.000.000-00"
                        maxlength="14"
                        required
                    >
                </div>

                <div class="form-group">
                    <label class="form-label">Cargo</label>
                    <select name="cargo_id" class="form-control-custom" required>
                        <option value="">Selecione</option>
                        @foreach($cargos as $cargo)
                            <option value="{{ $cargo->id }}" {{ old('cargo_id', $employee->cargo_id) == $cargo->id ? 'selected' : '' }}>
                                {{ $cargo->nome ?? $cargo->name ?? $cargo->descricao ?? $cargo->cargo ?? ('Cargo #' . $cargo->id) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Data de contratação</label>
                    <input
                        type="date"
                        name="hired_at"
                        class="form-control-custom"
                        value="{{ old('hired_at', optional($employee->hired_at)->format('Y-m-d')) }}"
                        required
                    >
                </div>

                <div class="form-group">
                    <label class="form-label">Status</label>
                    <select name="active" class="form-control-custom" required>
                        <option value="1" {{ old('active', (string) $employee->active) == '1' ? 'selected' : '' }}>Ativo</option>
                        <option value="0" {{ old('active', (string) $employee->active) == '0' ? 'selected' : '' }}>Inativo</option>
                    </select>
                </div>
            </div>

            <div class="actions-inline" style="margin-top:18px;">
                <button type="submit" class="btn btn-green">
                    Salvar Alterações
                </button>

                <a href="{{ route('employees.index') }}" class="btn btn-dark">
                    Cancelar
                </a>
            </div>
        </form>
    </div>
</div>

<script>
    function applyCpfMask(input) {
        input.addEventListener('input', function (e) {
            let value = e.target.value.replace(/\D/g, '');

            value = value.replace(/(\d{3})(\d)/, '$1.$2');
            value = value.replace(/(\d{3})(\d)/, '$1.$2');
            value = value.replace(/(\d{3})(\d{1,2})$/, '$1-$2');

            e.target.value = value;
        });
    }

    document.querySelectorAll('.input-cpf').forEach(applyCpfMask);
</script>
@endsection