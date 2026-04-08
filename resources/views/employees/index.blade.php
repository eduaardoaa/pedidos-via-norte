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

    <a href="{{ route('employees.pdf', [
        'search' => request('search'),
        'cargo_id' => request('cargo_id'),
        'location_id' => request('location_id'),
    ]) }}" class="btn btn-red">
        <i class="bi bi-file-earmark-pdf"></i>
        <span>Baixar PDF</span>
    </a>

    <button type="button" class="btn btn-green" onclick="openModal('modal-create-employee')">
        <i class="bi bi-plus-circle"></i>
        <span>Novo Funcionário</span>
    </button>
</div>
</div>
<style>
.btn-red{
    display:inline-flex;
    align-items:center;
    justify-content:center;
    gap:8px;
    height:42px;
    padding:0 18px;
    border:none;
    border-radius:14px;
    background:linear-gradient(135deg, #dc2626 0%, #ef4444 100%);
    color:#fff;
    font-weight:700;
    font-size:.95rem;
    text-decoration:none;
    cursor:pointer;
    transition:.2s ease;
    box-shadow:0 10px 24px rgba(239, 68, 68, .18);
}

.btn-red:hover{
    transform:translateY(-1px);
    filter:brightness(1.05);
    color:#fff;
    text-decoration:none;
}

.btn-red i{
    font-size:1rem;
}

/* Paginação igual à página de pedidos */
.card-paginacao-funcionarios .card-body{
    padding:18px;
}

.paginacao-funcionarios-wrap{
    display:flex;
    justify-content:center;
    align-items:center;
}

.paginacao-funcionarios-wrap nav{
    width:100%;
}

.paginacao-funcionarios-wrap .pagination{
    display:flex;
    justify-content:center;
    align-items:center;
    flex-wrap:wrap;
    gap:8px;
    margin:0;
    padding:0;
    list-style:none;
}

.paginacao-funcionarios-wrap .page-item{
    list-style:none;
}

.paginacao-funcionarios-wrap .page-link,
.paginacao-funcionarios-wrap .page-item > span{
    min-width:40px;
    height:40px;
    padding:0 12px;
    display:inline-flex;
    align-items:center;
    justify-content:center;
    border-radius:10px;
    border:1px solid rgba(255,255,255,.08);
    background:rgba(255,255,255,.03);
    color:#e5e7eb;
    text-decoration:none;
    font-weight:600;
    line-height:1;
    font-size:.95rem;
    transition:.2s ease;
}

.paginacao-funcionarios-wrap .page-link:hover{
    background:rgba(22,163,74,.14);
    border-color:rgba(22,163,74,.35);
    color:#fff;
    transform:translateY(-1px);
}

.paginacao-funcionarios-wrap .page-item.active .page-link,
.paginacao-funcionarios-wrap .page-item.active > span{
    background:linear-gradient(135deg, rgba(22,163,74,.95), rgba(22,163,74,.72));
    border-color:rgba(22,163,74,.65);
    color:#fff;
}

.paginacao-funcionarios-wrap .page-item.disabled .page-link,
.paginacao-funcionarios-wrap .page-item.disabled > span{
    opacity:.45;
    cursor:not-allowed;
    pointer-events:none;
}

.paginacao-funcionarios-wrap .pagination svg{
    width:14px !important;
    height:14px !important;
    max-width:14px !important;
    max-height:14px !important;
    display:block;
}

.paginacao-funcionarios-wrap .pagination .page-link{
    min-width:40px !important;
    width:auto !important;
    height:40px !important;
    padding:0 12px !important;
    font-size:.95rem !important;
    line-height:1 !important;
}

.paginacao-funcionarios-wrap .pagination .page-item:first-child .page-link,
.paginacao-funcionarios-wrap .pagination .page-item:last-child .page-link{
    padding:0 14px !important;
}

.paginacao-funcionarios-wrap .pagination .page-item > span{
    min-width:40px !important;
    width:auto !important;
    height:40px !important;
    padding:0 12px !important;
    font-size:.95rem !important;
    line-height:1 !important;
}

.paginacao-funcionarios-wrap .pagination li{
    list-style:none !important;
}

.paginacao-funcionarios-wrap .pagination *{
    transform:none !important;
}

@media (max-width: 640px){
    .paginacao-funcionarios-wrap .page-link,
    .paginacao-funcionarios-wrap .page-item > span{
        min-width:36px;
        height:36px;
        font-size:.88rem;
        padding:0 10px;
    }
}
</style>
<div class="card" style="margin-bottom:18px;">
    <div class="card-body">
        <form method="GET" action="{{ route('employees.index') }}" id="formFiltrosFuncionarios">
            <div class="form-grid" style="grid-template-columns: 2fr 1fr 1fr;">
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

                <div class="form-group">
                    <label class="form-label">Centro de custo</label>
                    <select name="location_id" id="filtroCentroCustoFuncionario" class="form-control-custom">
                        <option value="">Todos</option>
                        @foreach($centrosCusto as $centro)
                            <option value="{{ $centro->id }}" @selected((string) request('location_id') === (string) $centro->id)>
                                {{ $centro->name }}
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
        <div class="card-title">Lista de Funcionários ({{ $employees->count() }})</div>
        <div class="card-subtitle">
            Pesquise por nome, CPF, matrícula, cargo e centro de custo.
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
                        <th>Centro de Custo</th>
                        <th>Contratação</th>
                        <th>Status</th>
                        <th width="180">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($employees as $employee)
                        <tr>
                            <td><strong>{{ $employee->name }}</strong></td>
                            <td>{{ $employee->cpf }}</td>
                            <td>{{ $employee->registration }}</td>
                            <td>{{ $employee->cargo->nome ?? $employee->cargo->name ?? $employee->cargo->descricao ?? $employee->cargo->cargo ?? '-' }}</td>
                            <td>{{ $employee->location?->name ?? '-' }}</td>
                            <td>{{ optional($employee->hired_at)->format('d/m/Y') }}</td>
                            <td>
                                @if($employee->active)
                                    <span class="badge-status badge-success">Ativo</span>
                                @else
                                    <span class="badge-status badge-warning">Inativo</span>
                                @endif
                            </td>
                            <td>
                                <div class="table-actions">
                                    <button type="button" class="btn btn-dark" onclick="openModal('modal-edit-employee-{{ $employee->id }}')">
                                        Editar
                                    </button>

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
                            <td colspan="8">Nenhum funcionário encontrado.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($employees instanceof \Illuminate\Pagination\AbstractPaginator && $employees->hasPages())
    <div class="card card-paginacao-funcionarios" style="margin-top:16px;">
        <div class="card-body">
            <div class="paginacao-funcionarios-wrap">
                {{ $employees->onEachSide(1)->links('vendor.pagination.bootstrap-5-ptbr') }}
            </div>
        </div>
    </div>
@endif
    </div>
</div>
<style>
.btn-red{
    display:inline-flex;
    align-items:center;
    justify-content:center;
    gap:8px;
    height:42px;
    padding:0 18px;
    border:none;
    border-radius:14px;
    background:linear-gradient(135deg, #dc2626 0%, #ef4444 100%);
    color:#fff;
    font-weight:700;
    font-size:.95rem;
    text-decoration:none;
    cursor:pointer;
    transition:.2s ease;
    box-shadow:0 10px 24px rgba(239, 68, 68, .18);
}

.btn-red:hover{
    transform:translateY(-1px);
    filter:brightness(1.05);
    color:#fff;
    text-decoration:none;
}

.btn-red i{
    font-size:1rem;
}
</style>
{{-- Modal criar --}}
<div class="custom-modal" id="modal-create-employee">
    <div class="custom-modal-backdrop" onclick="closeModal('modal-create-employee')"></div>

    <div class="custom-modal-dialog">
        <div class="custom-modal-header">
            <div>
                <h3>Novo Funcionário</h3>
                <p>Cadastre um novo funcionário para controle de EPI.</p>
            </div>

            <button type="button" class="custom-modal-close" onclick="closeModal('modal-create-employee')">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <div class="custom-modal-body">
            <form action="{{ route('employees.store') }}" method="POST">
                @csrf

                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Nome</label>
                        <input
                            type="text"
                            name="name"
                            class="form-control-custom"
                            value="{{ session('open_modal') === 'create' ? old('name') : '' }}"
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
                            value="{{ session('open_modal') === 'create' ? old('registration') : '' }}"
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
                            value="{{ session('open_modal') === 'create' ? old('cpf') : '' }}"
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
                                <option value="{{ $cargo->id }}" {{ session('open_modal') === 'create' && old('cargo_id') == $cargo->id ? 'selected' : '' }}>
                                    {{ $cargo->nome ?? $cargo->name ?? $cargo->descricao ?? $cargo->cargo ?? ('Cargo #' . $cargo->id) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Centro de Custo</label>
                        <select name="location_id" class="form-control-custom" required>
                            <option value="">Selecione</option>
                            @foreach($centrosCusto as $centro)
                                <option value="{{ $centro->id }}" {{ session('open_modal') === 'create' && old('location_id') == $centro->id ? 'selected' : '' }}>
                                    {{ $centro->name }}
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
                            value="{{ session('open_modal') === 'create' ? old('hired_at') : '' }}"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label class="form-label">Status</label>
                        <select name="active" class="form-control-custom" required>
                            <option value="1" {{ session('open_modal') === 'create' ? (old('active', '1') == '1' ? 'selected' : '') : 'selected' }}>Ativo</option>
                            <option value="0" {{ session('open_modal') === 'create' && old('active') == '0' ? 'selected' : '' }}>Inativo</option>
                        </select>
                    </div>
                </div>

                <div class="actions-inline" style="margin-top:18px;">
                    <button type="submit" class="btn btn-green">
                        Salvar Funcionário
                    </button>

                    <button type="button" class="btn btn-dark" onclick="closeModal('modal-create-employee')">
                        Cancelar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modais editar --}}
@foreach($employees as $employee)
    <div class="custom-modal" id="modal-edit-employee-{{ $employee->id }}">
        <div class="custom-modal-backdrop" onclick="closeModal('modal-edit-employee-{{ $employee->id }}')"></div>

        <div class="custom-modal-dialog">
            <div class="custom-modal-header">
                <div>
                    <h3>Editar Funcionário</h3>
                    <p>Atualize os dados do funcionário selecionado.</p>
                </div>

                <button type="button" class="custom-modal-close" onclick="closeModal('modal-edit-employee-{{ $employee->id }}')">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>

            <div class="custom-modal-body">
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
                                value="{{ session('open_modal') === 'edit_' . $employee->id ? old('name') : $employee->name }}"
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
                                value="{{ session('open_modal') === 'edit_' . $employee->id ? old('registration') : $employee->registration }}"
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
                                value="{{ session('open_modal') === 'edit_' . $employee->id ? old('cpf') : $employee->cpf }}"
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
                                    <option value="{{ $cargo->id }}" {{ (session('open_modal') === 'edit_' . $employee->id ? old('cargo_id') : $employee->cargo_id) == $cargo->id ? 'selected' : '' }}>
                                        {{ $cargo->nome ?? $cargo->name ?? $cargo->descricao ?? $cargo->cargo ?? ('Cargo #' . $cargo->id) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Centro de Custo</label>
                            <select name="location_id" class="form-control-custom" required>
                                <option value="">Selecione</option>
                                @foreach($centrosCusto as $centro)
                                    <option value="{{ $centro->id }}" {{ (session('open_modal') === 'edit_' . $employee->id ? old('location_id') : $employee->location_id) == $centro->id ? 'selected' : '' }}>
                                        {{ $centro->name }}
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
                                value="{{ session('open_modal') === 'edit_' . $employee->id ? old('hired_at') : optional($employee->hired_at)->format('Y-m-d') }}"
                                required
                            >
                        </div>

                        <div class="form-group">
                            <label class="form-label">Status</label>
                            <select name="active" class="form-control-custom" required>
                                <option value="1" {{ (session('open_modal') === 'edit_' . $employee->id ? old('active') : (string) $employee->active) == '1' ? 'selected' : '' }}>Ativo</option>
                                <option value="0" {{ (session('open_modal') === 'edit_' . $employee->id ? old('active') : (string) $employee->active) == '0' ? 'selected' : '' }}>Inativo</option>
                            </select>
                        </div>
                    </div>

                    <div class="actions-inline" style="margin-top:18px;">
                        <button type="submit" class="btn btn-green">
                            Salvar Alterações
                        </button>

                        <button type="button" class="btn btn-dark" onclick="closeModal('modal-edit-employee-{{ $employee->id }}')">
                            Cancelar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endforeach

<script>
document.addEventListener('DOMContentLoaded', function () {
    const formFiltrosFuncionarios = document.getElementById('formFiltrosFuncionarios');
    const filtroBuscaFuncionario = document.getElementById('filtroBuscaFuncionario');
    const filtroCargoFuncionario = document.getElementById('filtroCargoFuncionario');
    const filtroCentroCustoFuncionario = document.getElementById('filtroCentroCustoFuncionario');

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

    if (filtroCentroCustoFuncionario && formFiltrosFuncionarios) {
        filtroCentroCustoFuncionario.addEventListener('change', function () {
            formFiltrosFuncionarios.submit();
        });
    }

    document.querySelectorAll('.input-cpf').forEach(function(input) {
        input.addEventListener('input', function (e) {
            let value = e.target.value.replace(/\D/g, '');
            value = value.replace(/(\d{3})(\d)/, '$1.$2');
            value = value.replace(/(\d{3})(\d)/, '$1.$2');
            value = value.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
            e.target.value = value;
        });
    });
});

function openModal(id) {
    const modal = document.getElementById(id);
    if (!modal) return;

    modal.classList.add('is-open');
    document.body.classList.add('modal-open');
}

function closeModal(id) {
    const modal = document.getElementById(id);
    if (!modal) return;

    modal.classList.remove('is-open');

    if (!document.querySelector('.custom-modal.is-open')) {
        document.body.classList.remove('modal-open');
    }
}

document.addEventListener('keydown', function (event) {
    if (event.key === 'Escape') {
        document.querySelectorAll('.custom-modal.is-open').forEach(function(modal) {
            modal.classList.remove('is-open');
        });

        document.body.classList.remove('modal-open');
    }
});

@if(session('open_modal'))
window.addEventListener('load', function () {
    const modalId = '{{ session('open_modal') === 'create' ? 'modal-create-employee' : 'modal-edit-employee-' . str_replace('edit_', '', session('open_modal')) }}';
    openModal(modalId);
});
@endif
</script>
@endsection