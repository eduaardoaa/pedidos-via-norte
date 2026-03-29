@extends('layouts.app')

@section('title', 'Usuários - Vianorte')
@section('pageTitle', 'Usuários')
@section('pageDescription', 'Gerencie os acessos internos do sistema.')

@section('content')
    <style>
        .user-face-inline{
            display:flex;
            align-items:center;
            gap:12px;
        }

        .user-face-thumb{
            width:46px;
            height:46px;
            border-radius:12px;
            object-fit:cover;
            object-position:center;
            border:1px solid rgba(255,255,255,.08);
            background:#0b1220;
            flex-shrink:0;
        }

        .user-face-thumb-placeholder{
            width:46px;
            height:46px;
            border-radius:12px;
            display:flex;
            align-items:center;
            justify-content:center;
            border:1px dashed rgba(255,255,255,.12);
            background:rgba(255,255,255,.02);
            color:var(--muted);
            font-size:.75rem;
            text-align:center;
            line-height:1.1;
            padding:4px;
            flex-shrink:0;
        }

        .user-face-modal-box{
            display:flex;
            align-items:center;
            gap:14px;
            padding:14px;
            border:1px solid rgba(255,255,255,.08);
            border-radius:16px;
            background:rgba(255,255,255,.025);
            margin-bottom:18px;
        }

        .user-face-modal-photo{
            width:84px;
            height:84px;
            border-radius:16px;
            object-fit:cover;
            object-position:center;
            border:1px solid rgba(255,255,255,.08);
            background:#0b1220;
            flex-shrink:0;
        }

        .user-face-modal-placeholder{
            width:84px;
            height:84px;
            border-radius:16px;
            display:flex;
            align-items:center;
            justify-content:center;
            border:1px dashed rgba(255,255,255,.12);
            background:rgba(255,255,255,.02);
            color:var(--muted);
            font-size:.8rem;
            text-align:center;
            line-height:1.2;
            padding:8px;
            flex-shrink:0;
        }

        .user-face-modal-content h4{
            margin:0 0 6px;
            font-size:1rem;
        }

        .user-face-modal-content p{
            margin:0;
            color:var(--muted);
            line-height:1.5;
            font-size:.92rem;
        }

        .table-actions{
            display:flex;
            flex-wrap:wrap;
            gap:8px;
            align-items:center;
        }

        .table-actions form{
            margin:0;
        }

        .table-actions .btn{
            min-height:40px;
        }

        @media (max-width: 768px){
            .user-face-modal-box{
                flex-direction:column;
                align-items:flex-start;
            }
        }
    </style>

    <div class="page-head">
        <div>
            <h2>Lista de usuários</h2>
            <p>Cadastre, edite, inative, redefina senha e exclua usuários do sistema.</p>
        </div>

        <div class="actions-inline">
            <button type="button" class="btn btn-green" onclick="openModal('modal-create-user')">
                <i class="bi bi-plus-circle"></i>
                <span>Novo usuário</span>
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
            <div class="card-title">Usuários cadastrados</div>
            <div class="card-subtitle">Controle de acesso, cargos e status da conta</div>
        </div>
        <div class="card-body">
            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nome / Contato</th>
                            <th>Usuário</th>
                            <th>CPF</th>
                            <th>Cargo</th>
                            <th>Status</th>
                            <th style="width: 430px;">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($usuarios as $usuario)
                            @php
                                $cargoCodigo = mb_strtolower(trim($usuario->cargo->codigo ?? ''));
                                $isCaboTurma = $cargoCodigo === 'cabo de turma';

                                $facePhotoUrl = null;
                                if (!empty($usuario->face_photo_path) && \Illuminate\Support\Facades\Storage::disk('public')->exists($usuario->face_photo_path)) {
                                    $facePhotoUrl = \Illuminate\Support\Facades\Storage::url($usuario->face_photo_path);
                                }
                            @endphp

                            <tr>
                                <td>{{ $usuario->id }}</td>
                                <td>
                                    <strong>{{ $usuario->name }}</strong>

                                    @if($isCaboTurma)
                                        <div class="user-face-inline" style="margin-top:8px;">
                                            @if($facePhotoUrl)
                                                <img src="{{ $facePhotoUrl }}" alt="Face de {{ $usuario->name }}" class="user-face-thumb">
                                            @else
                                                <div class="user-face-thumb-placeholder">Sem face</div>
                                            @endif

                                            <div>
                                                <div class="text-muted-small">{{ $usuario->email ?: 'Sem e-mail' }}</div>
                                                <div class="text-muted-small">{{ $usuario->numero ?: 'Sem número' }}</div>
                                            </div>
                                        </div>
                                    @else
                                        <div class="text-muted-small">{{ $usuario->email ?: 'Sem e-mail' }}</div>
                                        <div class="text-muted-small">{{ $usuario->numero ?: 'Sem número' }}</div>
                                    @endif
                                </td>
                                <td>{{ $usuario->usuario }}</td>
                                <td>{{ $usuario->cpf }}</td>
                                <td>{{ $usuario->cargo?->nome ?? 'Sem cargo' }}</td>
                                <td>
                                    @if($usuario->active)
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
                                            onclick="openModal('modal-edit-user-{{ $usuario->id }}')"
                                        >
                                            <i class="bi bi-pencil-square"></i>
                                            <span>Editar</span>
                                        </button>

                                        <form
                                            method="POST"
                                            action="{{ route('usuarios.toggle', $usuario) }}"
                                            onsubmit="return confirm('{{ $usuario->active ? 'Tem certeza que deseja inativar este usuário?' : 'Tem certeza que deseja ativar este usuário?' }}');"
                                        >
                                            @csrf
                                            @method('PATCH')

                                            <button type="submit" class="btn {{ $usuario->active ? 'btn-danger-soft' : 'btn-warning-soft' }}">
                                                @if($usuario->active)
                                                    <i class="bi bi-pause-circle"></i>
                                                    <span>Inativar</span>
                                                @else
                                                    <i class="bi bi-check-circle"></i>
                                                    <span>Ativar</span>
                                                @endif
                                            </button>
                                        </form>

                                        <form method="POST" action="{{ route('usuarios.reset-password', $usuario) }}" onsubmit="return confirm('Resetar a senha deste usuário para 12345?');">
                                            @csrf
                                            @method('PATCH')

                                            <button type="submit" class="btn btn-warning-soft">
                                                <i class="bi bi-key"></i>
                                                <span>Resetar senha</span>
                                            </button>
                                        </form>

                                        @if($isCaboTurma)
                                            <form method="POST" action="{{ route('usuarios.reset-face', $usuario) }}" onsubmit="return confirm('Tem certeza que deseja apagar a validação facial deste usuário? Ele precisará cadastrar novamente no próximo acesso.');">
                                                @csrf
                                                @method('PATCH')

                                                <button type="submit" class="btn btn-warning-soft">
                                                    <i class="bi bi-camera-video-off"></i>
                                                    <span>Resetar facial</span>
                                                </button>
                                            </form>
                                        @endif

                                        <form method="POST" action="{{ route('usuarios.destroy', $usuario) }}" onsubmit="return confirm('Tem certeza que deseja excluir este usuário?');">
                                            @csrf
                                            @method('DELETE')

                                            <button type="submit" class="btn btn-danger-soft">
                                                <i class="bi bi-trash"></i>
                                                <span>Excluir</span>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7">Nenhum usuário cadastrado ainda.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Modal criar usuário --}}
    <div class="custom-modal" id="modal-create-user">
        <div class="custom-modal-backdrop" onclick="closeModal('modal-create-user')"></div>

        <div class="custom-modal-dialog">
            <div class="custom-modal-header">
                <div>
                    <h3>Cadastrar usuário</h3>
                    <p>O usuário será criado com senha inicial <strong>12345</strong> e troca obrigatória no primeiro acesso.</p>
                </div>

                <button type="button" class="custom-modal-close" onclick="closeModal('modal-create-user')">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>

            <div class="custom-modal-body">
                <form method="POST" action="{{ route('usuarios.store') }}">
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
                            <label class="form-label">Usuário</label>
                            <input
                                type="text"
                                name="usuario"
                                class="form-control-custom"
                                value="{{ session('open_modal') === 'create' ? old('usuario') : '' }}"
                                placeholder="Login interno"
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
                            <label class="form-label">Número</label>
                            <input
                                type="text"
                                name="numero"
                                class="form-control-custom input-numero"
                                value="{{ session('open_modal') === 'create' ? old('numero') : '' }}"
                                placeholder="(00) 00000-0000"
                            >
                        </div>

                        <div class="form-group">
                            <label class="form-label">E-mail</label>
                            <input
                                type="email"
                                name="email"
                                class="form-control-custom"
                                value="{{ session('open_modal') === 'create' ? old('email') : '' }}"
                                placeholder="email@empresa.com"
                            >
                        </div>

                        <div class="form-group">
                            <label class="form-label">Cargo</label>
                            <select name="cargo_id" class="form-control-custom" required>
                                <option value="">Selecione</option>
                                @foreach($cargos as $cargo)
                                    <option value="{{ $cargo->id }}" {{ session('open_modal') === 'create' && old('cargo_id') == $cargo->id ? 'selected' : '' }}>
                                        {{ $cargo->nome }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group form-group-full">
                            <label class="form-check-line">
                                <input
                                    type="checkbox"
                                    name="active"
                                    value="1"
                                    {{ session('open_modal') === 'create' ? (old('active', '1') ? 'checked' : '') : 'checked' }}
                                >
                                <span>Deixar usuário ativo</span>
                            </label>
                        </div>
                    </div>

                    <div class="actions-inline" style="margin-top:18px;">
                        <button type="submit" class="btn btn-green">
                            <i class="bi bi-check-circle"></i>
                            <span>Salvar usuário</span>
                        </button>

                        <button type="button" class="btn btn-dark" onclick="closeModal('modal-create-user')">
                            <i class="bi bi-x-circle"></i>
                            <span>Cancelar</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modais editar usuário --}}
    @foreach($usuarios as $usuario)
        @php
            $cargoCodigo = mb_strtolower(trim($usuario->cargo->codigo ?? ''));
            $isCaboTurma = $cargoCodigo === 'cabo de turma';

            $facePhotoUrl = null;
            if (!empty($usuario->face_photo_path) && \Illuminate\Support\Facades\Storage::disk('public')->exists($usuario->face_photo_path)) {
                $facePhotoUrl = \Illuminate\Support\Facades\Storage::url($usuario->face_photo_path);
            }
        @endphp

        <div class="custom-modal" id="modal-edit-user-{{ $usuario->id }}">
            <div class="custom-modal-backdrop" onclick="closeModal('modal-edit-user-{{ $usuario->id }}')"></div>

            <div class="custom-modal-dialog">
                <div class="custom-modal-header">
                    <div>
                        <h3>Editar usuário</h3>
                        <p>Atualize os dados do usuário selecionado.</p>
                    </div>

                    <button type="button" class="custom-modal-close" onclick="closeModal('modal-edit-user-{{ $usuario->id }}')">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>

                <div class="custom-modal-body">
                    @if($isCaboTurma)
                        <div class="user-face-modal-box">
                            @if($facePhotoUrl)
                                <img src="{{ $facePhotoUrl }}" alt="Face de {{ $usuario->name }}" class="user-face-modal-photo">
                            @else
                                <div class="user-face-modal-placeholder">Sem foto facial</div>
                            @endif

                            <div class="user-face-modal-content">
                                <h4>Validação facial</h4>
                                <p>
                                    @if($usuario->face_registered_at)
                                        Foto cadastrada em {{ \Illuminate\Support\Carbon::parse($usuario->face_registered_at)->format('d/m/Y H:i') }}.
                                    @else
                                        Nenhuma validação facial cadastrada até o momento.
                                    @endif
                                </p>
                            </div>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('usuarios.update', $usuario) }}">
                        @csrf
                        @method('PUT')

                        <div class="form-grid">
                            <div class="form-group">
                                <label class="form-label">Nome</label>
                                <input
                                    type="text"
                                    name="name"
                                    class="form-control-custom"
                                    value="{{ session('open_modal') === 'edit_' . $usuario->id ? old('name') : $usuario->name }}"
                                    placeholder="Nome completo"
                                    required
                                >
                            </div>

                            <div class="form-group">
                                <label class="form-label">Usuário</label>
                                <input
                                    type="text"
                                    name="usuario"
                                    class="form-control-custom"
                                    value="{{ session('open_modal') === 'edit_' . $usuario->id ? old('usuario') : $usuario->usuario }}"
                                    placeholder="Login interno"
                                    required
                                >
                            </div>

                            <div class="form-group">
                                <label class="form-label">CPF</label>
                                <input
                                    type="text"
                                    name="cpf"
                                    class="form-control-custom input-cpf"
                                    value="{{ session('open_modal') === 'edit_' . $usuario->id ? old('cpf') : $usuario->cpf }}"
                                    placeholder="000.000.000-00"
                                    maxlength="14"
                                    required
                                >
                            </div>

                            <div class="form-group">
                                <label class="form-label">Número</label>
                                <input
                                    type="text"
                                    name="numero"
                                    class="form-control-custom input-numero"
                                    value="{{ session('open_modal') === 'edit_' . $usuario->id ? old('numero') : $usuario->numero }}"
                                    placeholder="(00) 00000-0000"
                                >
                            </div>

                            <div class="form-group">
                                <label class="form-label">E-mail</label>
                                <input
                                    type="email"
                                    name="email"
                                    class="form-control-custom"
                                    value="{{ session('open_modal') === 'edit_' . $usuario->id ? old('email') : $usuario->email }}"
                                    placeholder="email@empresa.com"
                                >
                            </div>

                            <div class="form-group">
                                <label class="form-label">Cargo</label>
                                <select name="cargo_id" class="form-control-custom" required>
                                    <option value="">Selecione</option>
                                    @foreach($cargos as $cargo)
                                        <option
                                            value="{{ $cargo->id }}"
                                            {{
                                                (session('open_modal') === 'edit_' . $usuario->id
                                                    ? old('cargo_id')
                                                    : $usuario->cargo_id) == $cargo->id
                                                    ? 'selected'
                                                    : ''
                                            }}
                                        >
                                            {{ $cargo->nome }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group form-group-full">
                                <label class="form-check-line">
                                    <input
                                        type="checkbox"
                                        name="active"
                                        value="1"
                                        {{ session('open_modal') === 'edit_' . $usuario->id ? (old('active') ? 'checked' : '') : ($usuario->active ? 'checked' : '') }}
                                    >
                                    <span>Deixar usuário ativo</span>
                                </label>
                            </div>
                        </div>

                        <div class="actions-inline" style="margin-top:18px;">
                            <button type="submit" class="btn btn-green">
                                <i class="bi bi-check-circle"></i>
                                <span>Salvar alterações</span>
                            </button>

                            <button type="button" class="btn btn-dark" onclick="closeModal('modal-edit-user-{{ $usuario->id }}')">
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

        function applyCpfMask(input) {
            input.addEventListener('input', function (e) {
                let value = e.target.value.replace(/\D/g, '');

                value = value.replace(/(\d{3})(\d)/, '$1.$2');
                value = value.replace(/(\d{3})(\d)/, '$1.$2');
                value = value.replace(/(\d{3})(\d{1,2})$/, '$1-$2');

                e.target.value = value;
            });
        }

        function applyPhoneMask(input) {
            input.addEventListener('input', function (e) {
                let value = e.target.value.replace(/\D/g, '');

                if (value.length > 11) value = value.slice(0, 11);

                if (value.length > 10) {
                    value = value.replace(/(\d{2})(\d{5})(\d{0,4})/, '($1) $2-$3');
                } else if (value.length > 6) {
                    value = value.replace(/(\d{2})(\d{4})(\d{0,4})/, '($1) $2-$3');
                } else if (value.length > 2) {
                    value = value.replace(/(\d{2})(\d{0,5})/, '($1) $2');
                } else if (value.length > 0) {
                    value = value.replace(/(\d{0,2})/, '($1');
                }

                e.target.value = value;
            });
        }

        document.querySelectorAll('.input-cpf').forEach(applyCpfMask);
        document.querySelectorAll('.input-numero').forEach(applyPhoneMask);

        @if(session('open_modal'))
            window.addEventListener('load', function () {
                const modalId = '{{ session('open_modal') === 'create' ? 'modal-create-user' : 'modal-edit-user-' . str_replace('edit_', '', session('open_modal')) }}';
                openModal(modalId);
            });
        @endif
    </script>
@endsection