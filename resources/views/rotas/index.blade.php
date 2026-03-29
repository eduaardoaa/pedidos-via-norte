@extends('layouts.app')

@section('title', 'Rotas - Vianorte')
@section('pageTitle', 'Rotas')
@section('pageDescription', 'Gerencie as rotas operacionais do sistema.')

@section('content')
    <div class="page-head">
        <div>
            <h2>Lista de rotas</h2>
            <p>Cadastre, edite e controle as rotas disponíveis no sistema.</p>
        </div>

        <div class="actions-inline">
            <button type="button" class="btn btn-green" onclick="openModal('modal-create-route')">
                <i class="bi bi-plus-circle"></i>
                <span>Nova rota</span>
            </button>
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
            <div class="card-title">Rotas cadastradas</div>
            <div class="card-subtitle">Base centralizada para organização dos locais e pedidos</div>
        </div>
        <div class="card-body">
            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nome</th>
                            <th>Código</th>
                            <th>Locais vinculados</th>
                            <th>Status</th>
                            <th style="width: 240px;">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rotas as $rota)
                            <tr>
                                <td>{{ $rota->id }}</td>
                                <td>{{ $rota->name }}</td>
                                <td>{{ $rota->code }}</td>
                                <td>{{ $rota->locations_count }}</td>
                                <td>
                                    @if($rota->active)
                                        <span class="badge-status badge-success">Ativa</span>
                                    @else
                                        <span class="badge-status badge-warning">Inativa</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="table-actions">
                                        <button
                                            type="button"
                                            class="btn btn-dark"
                                            onclick="openModal('modal-edit-route-{{ $rota->id }}')"
                                        >
                                            <i class="bi bi-pencil-square"></i>
                                            <span>Editar</span>
                                        </button>

                                        <form method="POST" action="{{ route('rotas.toggle', $rota) }}">
                                            @csrf
                                            @method('PATCH')

                                            <button type="submit" class="btn {{ $rota->active ? 'btn-danger-soft' : 'btn-warning-soft' }}">
                                                @if($rota->active)
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
                                <td colspan="6">Nenhuma rota cadastrada ainda.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Modal criar rota --}}
    <div class="custom-modal" id="modal-create-route">
        <div class="custom-modal-backdrop" onclick="closeModal('modal-create-route')"></div>

        <div class="custom-modal-dialog">
            <div class="custom-modal-header">
                <div>
                    <h3>Cadastrar rota</h3>
                    <p>Defina o nome, código interno e status da rota.</p>
                </div>

                <button type="button" class="custom-modal-close" onclick="closeModal('modal-create-route')">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>

            <div class="custom-modal-body">
                <form method="POST" action="{{ route('rotas.store') }}">
                    @csrf

                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">Nome da rota</label>
                            <input
                                type="text"
                                name="name"
                                class="form-control-custom"
                                value="{{ session('open_modal') === 'create' ? old('name') : '' }}"
                                placeholder="Ex.: Rota 1 - Parte 1"
                                required
                            >
                        </div>

                        <div class="form-group">
                            <label class="form-label">Código</label>
                            <input
                                type="text"
                                name="code"
                                class="form-control-custom"
                                value="{{ session('open_modal') === 'create' ? old('code') : '' }}"
                                placeholder="Ex.: rota_1_parte_1"
                            >
                        </div>

                        <div class="form-group form-group-full">
                            <label class="form-check-line">
                                <input
                                    type="checkbox"
                                    name="active"
                                    value="1"
                                    {{ session('open_modal') === 'create' ? (old('active', '1') ? 'checked' : '') : 'checked' }}
                                >
                                <span>Deixar rota ativa</span>
                            </label>
                        </div>
                    </div>

                    <div class="actions-inline" style="margin-top:18px;">
                        <button type="submit" class="btn btn-green">
                            <i class="bi bi-check-circle"></i>
                            <span>Salvar rota</span>
                        </button>

                        <button type="button" class="btn btn-dark" onclick="closeModal('modal-create-route')">
                            <i class="bi bi-x-circle"></i>
                            <span>Cancelar</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modais editar rota --}}
    @foreach($rotas as $rota)
        <div class="custom-modal" id="modal-edit-route-{{ $rota->id }}">
            <div class="custom-modal-backdrop" onclick="closeModal('modal-edit-route-{{ $rota->id }}')"></div>

            <div class="custom-modal-dialog">
                <div class="custom-modal-header">
                    <div>
                        <h3>Editar rota</h3>
                        <p>Atualize os dados da rota selecionada.</p>
                    </div>

                    <button type="button" class="custom-modal-close" onclick="closeModal('modal-edit-route-{{ $rota->id }}')">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>

                <div class="custom-modal-body">
                    <form method="POST" action="{{ route('rotas.update', $rota) }}">
                        @csrf
                        @method('PUT')

                        <div class="form-grid">
                            <div class="form-group">
                                <label class="form-label">Nome da rota</label>
                                <input
                                    type="text"
                                    name="name"
                                    class="form-control-custom"
                                    value="{{ session('open_modal') === 'edit_' . $rota->id ? old('name') : $rota->name }}"
                                    placeholder="Ex.: Rota 1 - Parte 1"
                                    required
                                >
                            </div>

                            <div class="form-group">
                                <label class="form-label">Código</label>
                                <input
                                    type="text"
                                    name="code"
                                    class="form-control-custom"
                                    value="{{ session('open_modal') === 'edit_' . $rota->id ? old('code') : $rota->code }}"
                                    placeholder="Ex.: rota_1_parte_1"
                                >
                            </div>

                            <div class="form-group form-group-full">
                                <label class="form-check-line">
                                    <input
                                        type="checkbox"
                                        name="active"
                                        value="1"
                                        {{ session('open_modal') === 'edit_' . $rota->id ? (old('active') ? 'checked' : '') : ($rota->active ? 'checked' : '') }}
                                    >
                                    <span>Deixar rota ativa</span>
                                </label>
                            </div>
                        </div>

                        <div class="actions-inline" style="margin-top:18px;">
                            <button type="submit" class="btn btn-green">
                                <i class="bi bi-check-circle"></i>
                                <span>Salvar alterações</span>
                            </button>

                            <button type="button" class="btn btn-dark" onclick="closeModal('modal-edit-route-{{ $rota->id }}')">
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
                const modalId = '{{ session('open_modal') === 'create' ? 'modal-create-route' : 'modal-edit-route-' . str_replace('edit_', '', session('open_modal')) }}';
                openModal(modalId);
            });
        @endif
    </script>
@endsection