@extends('layouts.app')

@section('title', 'Cargos - Vianorte')
@section('pageTitle', 'Cargos')
@section('pageDescription', 'Gerencie os cargos e perfis internos do sistema.')

@section('content')
    <div class="page-head">
        <div>
            <h2>Lista de cargos</h2>
            <p>Cadastre, edite e controle os cargos disponíveis no sistema.</p>
        </div>

        <div class="actions-inline">
            <button type="button" class="btn btn-green" onclick="openModal('modal-create-cargo')">
                <i class="bi bi-plus-circle"></i>
                <span>Novo cargo</span>
            </button>
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
            <div class="card-title">Cargos cadastrados</div>
            <div class="card-subtitle">Base de permissões e classificação dos usuários</div>
        </div>
        <div class="card-body">
            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nome</th>
                            <th>Código</th>
                            <th>Usuários vinculados</th>
                            <th>Status</th>
                            <th style="width: 240px;">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($cargos as $cargo)
                            <tr>
                                <td>{{ $cargo->id }}</td>
                                <td>{{ $cargo->nome }}</td>
                                <td>{{ $cargo->codigo }}</td>
                                <td>{{ $cargo->users_count }}</td>
                                <td>
                                    @if($cargo->ativo)
                                        <span class="badge-status badge-success">Ativo</span>
                                    @else
                                        <span class="badge-status badge-warning">Inativo</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="table-actions">
                                        <button
                                            type="button"
                                            class="btn btn-dark"
                                            onclick="openModal('modal-edit-cargo-{{ $cargo->id }}')"
                                        >
                                            <i class="bi bi-pencil-square"></i>
                                            <span>Editar</span>
                                        </button>

                                        <form method="POST" action="{{ route('cargos.toggle', $cargo) }}">
                                            @csrf
                                            @method('PATCH')

                                            <button type="submit" class="btn {{ $cargo->ativo ? 'btn-danger-soft' : 'btn-warning-soft' }}">
                                                @if($cargo->ativo)
                                                    <i class="bi bi-pause-circle"></i>
                                                    <span>Inativar</span>
                                                @else
                                                    <i class="bi bi-check-circle"></i>
                                                    <span>Ativar</span>
                                                @endif
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6">Nenhum cargo cadastrado ainda.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Modal criar cargo --}}
    <div class="custom-modal" id="modal-create-cargo">
        <div class="custom-modal-backdrop" onclick="closeModal('modal-create-cargo')"></div>

        <div class="custom-modal-dialog">
            <div class="custom-modal-header">
                <div>
                    <h3>Cadastrar cargo</h3>
                    <p>Defina o nome, código interno e status do cargo.</p>
                </div>

                <button type="button" class="custom-modal-close" onclick="closeModal('modal-create-cargo')">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>

            <div class="custom-modal-body">
                <form method="POST" action="{{ route('cargos.store') }}">
                    @csrf

                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">Nome do cargo</label>
                            <input
                                type="text"
                                name="nome"
                                class="form-control-custom"
                                value="{{ session('open_modal') === 'create' ? old('nome') : '' }}"
                                placeholder="Ex.: Administrador"
                                required
                            >
                        </div>

                        <div class="form-group">
                            <label class="form-label">Código</label>
                            <input
                                type="text"
                                name="codigo"
                                class="form-control-custom"
                                value="{{ session('open_modal') === 'create' ? old('codigo') : '' }}"
                                placeholder="Ex.: admin"
                            >
                        </div>

                        <div class="form-group form-group-full">
                            <label class="form-check-line">
                                <input
                                    type="checkbox"
                                    name="ativo"
                                    value="1"
                                    {{ session('open_modal') === 'create' ? (old('ativo', '1') ? 'checked' : '') : 'checked' }}
                                >
                                <span>Deixar cargo ativo</span>
                            </label>
                        </div>
                    </div>

                    <div class="actions-inline" style="margin-top:18px;">
                        <button type="submit" class="btn btn-green">
                            <i class="bi bi-check-circle"></i>
                            <span>Salvar cargo</span>
                        </button>

                        <button type="button" class="btn btn-dark" onclick="closeModal('modal-create-cargo')">
                            <i class="bi bi-x-circle"></i>
                            <span>Cancelar</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modais editar cargo --}}
    @foreach($cargos as $cargo)
        <div class="custom-modal" id="modal-edit-cargo-{{ $cargo->id }}">
            <div class="custom-modal-backdrop" onclick="closeModal('modal-edit-cargo-{{ $cargo->id }}')"></div>

            <div class="custom-modal-dialog">
                <div class="custom-modal-header">
                    <div>
                        <h3>Editar cargo</h3>
                        <p>Atualize os dados do cargo selecionado.</p>
                    </div>

                    <button type="button" class="custom-modal-close" onclick="closeModal('modal-edit-cargo-{{ $cargo->id }}')">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>

                <div class="custom-modal-body">
                    <form method="POST" action="{{ route('cargos.update', $cargo) }}">
                        @csrf
                        @method('PUT')

                        <div class="form-grid">
                            <div class="form-group">
                                <label class="form-label">Nome do cargo</label>
                                <input
                                    type="text"
                                    name="nome"
                                    class="form-control-custom"
                                    value="{{ session('open_modal') === 'edit_' . $cargo->id ? old('nome') : $cargo->nome }}"
                                    placeholder="Ex.: Administrador"
                                    required
                                >
                            </div>

                            <div class="form-group">
                                <label class="form-label">Código</label>
                                <input
                                    type="text"
                                    name="codigo"
                                    class="form-control-custom"
                                    value="{{ session('open_modal') === 'edit_' . $cargo->id ? old('codigo') : $cargo->codigo }}"
                                    placeholder="Ex.: admin"
                                >
                            </div>

                            <div class="form-group form-group-full">
                                <label class="form-check-line">
                                    <input
                                        type="checkbox"
                                        name="ativo"
                                        value="1"
                                        {{ session('open_modal') === 'edit_' . $cargo->id ? (old('ativo') ? 'checked' : '') : ($cargo->ativo ? 'checked' : '') }}
                                    >
                                    <span>Deixar cargo ativo</span>
                                </label>
                            </div>
                        </div>

                        <div class="actions-inline" style="margin-top:18px;">
                            <button type="submit" class="btn btn-green">
                                <i class="bi bi-check-circle"></i>
                                <span>Salvar alterações</span>
                            </button>

                            <button type="button" class="btn btn-dark" onclick="closeModal('modal-edit-cargo-{{ $cargo->id }}')">
                                <i class="bi bi-x-circle"></i>
                                <span>Cancelar</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endforeach

    <script>
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
            document.body.classList.remove('modal-open');
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
                const modalId = '{{ session('open_modal') === 'create' ? 'modal-create-cargo' : 'modal-edit-cargo-' . str_replace('edit_', '', session('open_modal')) }}';
                openModal(modalId);
            });
        @endif
    </script>
@endsection