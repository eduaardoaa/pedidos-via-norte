@extends('layouts.app')

@section('title', 'Editar Cargo - Vianorte')
@section('pageTitle', 'Editar Cargo')
@section('pageDescription', 'Atualize os dados do cargo selecionado.')

@section('content')
    <div class="page-head">
        <div>
            <h2>Editar cargo</h2>
            <p>Altere as informações do cargo conforme necessário.</p>
        </div>

        <div class="actions-inline">
            <a href="{{ route('cargos.index') }}" class="btn btn-dark">
                <i class="bi bi-arrow-left"></i>
                <span>Voltar</span>
            </a>
        </div>
    </div>

    @if($errors->any())
        <div class="alert-error-box">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="card form-card">
        <div class="card-header">
            <div class="card-title">Dados do cargo</div>
            <div class="card-subtitle">Atualize os campos abaixo</div>
        </div>
        <div class="card-body">
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
                            value="{{ old('nome', $cargo->nome) }}"
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
                            value="{{ old('codigo', $cargo->codigo) }}"
                            placeholder="Ex.: admin"
                        >
                    </div>

                    <div class="form-group form-group-full">
                        <label class="form-check-line">
                            <input
                                type="checkbox"
                                name="ativo"
                                value="1"
                                {{ old('ativo', $cargo->ativo) ? 'checked' : '' }}
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

                    <a href="{{ route('cargos.index') }}" class="btn btn-dark">
                        <i class="bi bi-x-circle"></i>
                        <span>Cancelar</span>
                    </a>
                </div>
            </form>
        </div>
    </div>
@endsection