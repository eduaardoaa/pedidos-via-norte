@extends('layouts.app')

@section('title', 'Locais - Vianorte')
@section('pageTitle', 'Locais')
@section('pageDescription', 'Gerencie os locais vinculados às rotas e os locais do almoxarifado.')

@section('content')
    <div class="page-head">
        <div>
            <h2>Lista de locais</h2>
            <p>Cadastre, edite e controle os locais do sistema.</p>
        </div>

        <div class="actions-inline">
            <button type="button" class="btn btn-green" onclick="openModal('modal-create-location')">
                <i class="bi bi-plus-circle"></i>
                <span>Novo local</span>
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

    <div class="card" style="margin-bottom: 18px;">
        <div class="card-body">
            <form method="GET" action="{{ route('locais.index') }}">
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Tipo do local</label>
                        <select name="scope" class="form-control-custom" onchange="this.form.submit()">
                            <option value="">Todos</option>
                            <option value="rota" {{ $scopeFilter === 'rota' ? 'selected' : '' }}>Rota</option>
                            <option value="almoxarifado" {{ $scopeFilter === 'almoxarifado' ? 'selected' : '' }}>Almoxarifado</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Filtrar por rota</label>
                        <select name="route_id" class="form-control-custom" onchange="this.form.submit()">
                            <option value="">Todas as rotas</option>
                            @foreach($rotas as $rota)
                                <option value="{{ $rota->id }}" {{ (string)$routeFilter === (string)$rota->id ? 'selected' : '' }}>
                                    {{ $rota->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group form-group-full" style="display:flex; align-items:end;">
                        <div class="actions-inline">
                            <a href="{{ route('locais.index') }}" class="btn btn-dark">
                                <i class="bi bi-arrow-clockwise"></i>
                                <span>Limpar filtro</span>
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <div class="card-title">Locais cadastrados</div>
            <div class="card-subtitle">Base centralizada de locais de rota e almoxarifado</div>
        </div>
        <div class="card-body">
            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Local</th>
                            <th>Tipo</th>
                            <th>Rota</th>
                            <th>Endereço</th>
                            <th>Status</th>
                            <th style="width: 240px;">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($locais as $local)
                            <tr>
                                <td>{{ $local->id }}</td>
                                <td>{{ $local->name }}</td>
                                <td>
                                    @if($local->scope === 'rota')
                                        <span class="badge-status badge-info">Rota</span>
                                    @else
                                        <span class="badge-status badge-warning">Almoxarifado</span>
                                    @endif
                                </td>
                                <td>{{ $local->route?->name ?? '-' }}</td>
                                <td>{{ $local->address ?: 'Sem endereço' }}</td>
                                <td>
                                    @if($local->active)
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
                                            onclick="openModal('modal-edit-location-{{ $local->id }}')"
                                        >
                                            <i class="bi bi-pencil-square"></i>
                                            <span>Editar</span>
                                        </button>

                                        <form method="POST" action="{{ route('locais.toggle', $local) }}">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="route_id" value="{{ $routeFilter ?: $local->route_id }}">
                                            <input type="hidden" name="scope" value="{{ $scopeFilter ?: $local->scope }}">

                                            <button type="submit" class="btn {{ $local->active ? 'btn-danger-soft' : 'btn-warning-soft' }}">
                                                @if($local->active)
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
                                <td colspan="7">Nenhum local cadastrado ainda.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Modal criar local --}}
    <div class="custom-modal" id="modal-create-location">
        <div class="custom-modal-backdrop" onclick="closeModal('modal-create-location')"></div>

        <div class="custom-modal-dialog">
            <div class="custom-modal-header">
                <div>
                    <h3>Cadastrar local</h3>
                    <p>Defina o tipo, rota, nome e endereço do local.</p>
                </div>

                <button type="button" class="custom-modal-close" onclick="closeModal('modal-create-location')">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>

            <div class="custom-modal-body">
                <form method="POST" action="{{ route('locais.store') }}">
                    @csrf

                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">Tipo do local</label>
                            <select name="scope" class="form-control-custom local-scope-select" required>
                                <option value="rota"
                                    {{ session('open_modal') === 'create'
                                        ? (old('scope', $scopeFilter ?: 'rota') === 'rota' ? 'selected' : '')
                                        : (($scopeFilter ?: 'rota') === 'rota' ? 'selected' : '') }}>
                                    Rota
                                </option>
                                <option value="almoxarifado"
                                    {{ session('open_modal') === 'create'
                                        ? (old('scope', $scopeFilter) === 'almoxarifado' ? 'selected' : '')
                                        : ($scopeFilter === 'almoxarifado' ? 'selected' : '') }}>
                                    Almoxarifado
                                </option>
                            </select>
                        </div>

                        <div class="form-group route-select-wrapper">
                            <label class="form-label">Rota</label>
                            <select name="route_id" class="form-control-custom">
                                <option value="">Selecione</option>
                                @foreach($rotas as $rota)
                                    <option value="{{ $rota->id }}"
                                        {{ session('open_modal') === 'create'
                                            ? ((string)old('route_id', $routeFilter) === (string)$rota->id ? 'selected' : '')
                                            : ((string)$routeFilter === (string)$rota->id ? 'selected' : '') }}>
                                        {{ $rota->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Nome do local</label>
                            <input
                                type="text"
                                name="name"
                                class="form-control-custom"
                                value="{{ session('open_modal') === 'create' ? old('name') : '' }}"
                                placeholder="Ex.: Escola Municipal X"
                                required
                            >
                        </div>

                        <div class="form-group form-group-full">
                            <label class="form-label">Endereço</label>
                            <input
                                type="text"
                                name="address"
                                class="form-control-custom"
                                value="{{ session('open_modal') === 'create' ? old('address') : '' }}"
                                placeholder="Rua, número, bairro..."
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
                                <span>Deixar local ativo</span>
                            </label>
                        </div>
                    </div>

                    <div class="actions-inline" style="margin-top:18px;">
                        <button type="submit" class="btn btn-green">
                            <i class="bi bi-check-circle"></i>
                            <span>Salvar local</span>
                        </button>

                        <button type="button" class="btn btn-dark" onclick="closeModal('modal-create-location')">
                            <i class="bi bi-x-circle"></i>
                            <span>Cancelar</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modais editar local --}}
    @foreach($locais as $local)
        <div class="custom-modal" id="modal-edit-location-{{ $local->id }}">
            <div class="custom-modal-backdrop" onclick="closeModal('modal-edit-location-{{ $local->id }}')"></div>

            <div class="custom-modal-dialog">
                <div class="custom-modal-header">
                    <div>
                        <h3>Editar local</h3>
                        <p>Atualize os dados do local selecionado.</p>
                    </div>

                    <button type="button" class="custom-modal-close" onclick="closeModal('modal-edit-location-{{ $local->id }}')">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>

                <div class="custom-modal-body">
                    <form method="POST" action="{{ route('locais.update', $local) }}">
                        @csrf
                        @method('PUT')

                        <div class="form-grid">
                            <div class="form-group">
                                <label class="form-label">Tipo do local</label>
                                <select name="scope" class="form-control-custom local-scope-select" required>
                                    <option value="rota"
                                        {{ (session('open_modal') === 'edit_' . $local->id ? old('scope') : $local->scope) === 'rota' ? 'selected' : '' }}>
                                        Rota
                                    </option>
                                    <option value="almoxarifado"
                                        {{ (session('open_modal') === 'edit_' . $local->id ? old('scope') : $local->scope) === 'almoxarifado' ? 'selected' : '' }}>
                                        Almoxarifado
                                    </option>
                                </select>
                            </div>

                            <div class="form-group route-select-wrapper">
                                <label class="form-label">Rota</label>
                                <select name="route_id" class="form-control-custom">
                                    <option value="">Selecione</option>
                                    @foreach($rotas as $rota)
                                        <option
                                            value="{{ $rota->id }}"
                                            {{
                                                (session('open_modal') === 'edit_' . $local->id
                                                    ? old('route_id')
                                                    : $local->route_id) == $rota->id
                                                    ? 'selected'
                                                    : ''
                                            }}
                                        >
                                            {{ $rota->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Nome do local</label>
                                <input
                                    type="text"
                                    name="name"
                                    class="form-control-custom"
                                    value="{{ session('open_modal') === 'edit_' . $local->id ? old('name') : $local->name }}"
                                    placeholder="Ex.: Escola Municipal X"
                                    required
                                >
                            </div>

                            <div class="form-group form-group-full">
                                <label class="form-label">Endereço</label>
                                <input
                                    type="text"
                                    name="address"
                                    class="form-control-custom"
                                    value="{{ session('open_modal') === 'edit_' . $local->id ? old('address') : $local->address }}"
                                    placeholder="Rua, número, bairro..."
                                >
                            </div>

                            <div class="form-group form-group-full">
                                <label class="form-check-line">
                                    <input
                                        type="checkbox"
                                        name="active"
                                        value="1"
                                        {{ session('open_modal') === 'edit_' . $local->id ? (old('active') ? 'checked' : '') : ($local->active ? 'checked' : '') }}
                                    >
                                    <span>Deixar local ativo</span>
                                </label>
                            </div>
                        </div>

                        <div class="actions-inline" style="margin-top:18px;">
                            <button type="submit" class="btn btn-green">
                                <i class="bi bi-check-circle"></i>
                                <span>Salvar alterações</span>
                            </button>

                            <button type="button" class="btn btn-dark" onclick="closeModal('modal-edit-location-{{ $local->id }}')">
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

        function updateRouteVisibility(container) {
            const scopeSelect = container.querySelector('.local-scope-select');
            const routeWrapper = container.querySelector('.route-select-wrapper');

            if (!scopeSelect || !routeWrapper) return;

            if (scopeSelect.value === 'almoxarifado') {
                routeWrapper.style.display = 'none';
            } else {
                routeWrapper.style.display = '';
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

        document.querySelectorAll('.custom-modal-dialog').forEach(function(dialog) {
            const scopeSelect = dialog.querySelector('.local-scope-select');

            if (scopeSelect) {
                updateRouteVisibility(dialog);

                scopeSelect.addEventListener('change', function() {
                    updateRouteVisibility(dialog);
                });
            }
        });

        @if(session('open_modal'))
            window.addEventListener('load', function () {
                const modalId = '{{ session('open_modal') === 'create' ? 'modal-create-location' : 'modal-edit-location-' . str_replace('edit_', '', session('open_modal')) }}';
                openModal(modalId);
            });
        @endif
    </script>
@endsection