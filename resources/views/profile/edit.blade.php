@extends('layouts.app')

@section('title', 'Editar Perfil - Vianorte')
@section('pageTitle', 'Editar Perfil')
@section('pageDescription', 'Visualize e atualize seus dados de acesso.')

@section('content')
@php
    $cargoCodigo = mb_strtolower(trim($user->cargo->codigo ?? ''));
    $isCaboTurma = $cargoCodigo === 'cabo de turma';

    $facePhotoUrl = null;
    if (!empty($user->face_photo_path) && \Illuminate\Support\Facades\Storage::disk('public')->exists($user->face_photo_path)) {
        $facePhotoUrl = \Illuminate\Support\Facades\Storage::url($user->face_photo_path);
    }
@endphp

<style>
    .profile-page{
        display:flex;
        justify-content:center;
        width:100%;
    }

    .profile-wrapper{
        width:100%;
        max-width:920px;
        margin:0 auto;
    }

    .profile-card + .profile-card{
        margin-top:20px;
    }

    .profile-grid{
        display:grid;
        grid-template-columns:repeat(2, minmax(0, 1fr));
        gap:18px;
    }

    .form-group-full{
        grid-column:1 / -1;
    }

    .profile-info-box{
        display:flex;
        align-items:center;
        gap:16px;
        padding:18px;
        border:1px solid rgba(255,255,255,.08);
        border-radius:18px;
        background:rgba(255,255,255,.02);
        margin-bottom:20px;
    }

    .profile-avatar{
        width:58px;
        height:58px;
        border-radius:50%;
        display:flex;
        align-items:center;
        justify-content:center;
        font-size:1.4rem;
        font-weight:800;
        background:linear-gradient(135deg, rgba(22,163,74,.18), rgba(22,163,74,.32));
        color:#fff;
        flex-shrink:0;
    }

    .profile-info-box h3{
        margin:0;
        font-size:1.1rem;
    }

    .profile-info-box p{
        margin:4px 0 0;
        color:var(--muted);
        word-break:break-word;
    }

    .helper-text{
        font-size:.9rem;
        color:var(--muted);
        margin-top:6px;
    }

    .alert-success-box{
        margin-bottom:18px;
    }

    .alert-error-box{
        background:rgba(220,38,38,.12);
        border:1px solid rgba(220,38,38,.28);
        color:#fecaca;
        padding:14px 16px;
        border-radius:14px;
        margin-bottom:18px;
    }

    .alert-error-box ul{
        margin:0;
        padding-left:18px;
    }

    .profile-actions{
        display:flex;
        justify-content:flex-end;
        gap:12px;
        margin-top:22px;
        flex-wrap:wrap;
    }

    .face-profile-box{
        display:flex;
        align-items:center;
        gap:18px;
        padding:18px;
        border:1px solid rgba(255,255,255,.08);
        border-radius:18px;
        background:rgba(255,255,255,.02);
    }

    .face-profile-photo{
        width:110px;
        height:110px;
        border-radius:18px;
        object-fit:cover;
        object-position:center;
        border:1px solid rgba(255,255,255,.08);
        background:#0b1220;
        flex-shrink:0;
        display:block;
    }

    .face-profile-placeholder{
        width:110px;
        height:110px;
        border-radius:18px;
        border:1px dashed rgba(255,255,255,.12);
        display:flex;
        align-items:center;
        justify-content:center;
        color:var(--muted);
        background:rgba(255,255,255,.02);
        flex-shrink:0;
        text-align:center;
        padding:10px;
        font-size:.85rem;
        line-height:1.4;
    }

    .face-profile-content{
        flex:1;
        min-width:0;
    }

    .face-profile-content h3{
        margin:0 0 6px;
        font-size:1.05rem;
    }

    .face-profile-content p{
        margin:0;
        color:var(--muted);
        line-height:1.6;
    }

    .face-profile-action{
        flex-shrink:0;
    }

    .face-profile-action .btn{
        white-space:nowrap;
    }

    @media (max-width: 768px){
        .profile-grid{
            grid-template-columns:1fr;
        }

        .profile-info-box{
            flex-direction:column;
            align-items:flex-start;
        }

        .face-profile-box{
            flex-direction:column;
            align-items:stretch;
        }

        .face-profile-photo,
        .face-profile-placeholder{
            width:100%;
            max-width:220px;
            height:220px;
            margin:0 auto;
        }

        .face-profile-content{
            text-align:left;
        }

        .face-profile-action{
            width:100%;
        }

        .face-profile-action .btn{
            width:100%;
            justify-content:center;
        }

        .profile-actions{
            flex-direction:column-reverse;
            align-items:stretch;
        }

        .profile-actions .btn,
        .profile-actions a,
        .profile-actions button{
            width:100%;
            justify-content:center;
        }
    }
</style>

<div class="profile-page">
    <div class="profile-wrapper">
        @if(session('success'))
            <div class="alert-success-box">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="alert-error-box">
                <ul>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="card profile-card">
            <div class="card-body">
                <div class="profile-info-box">
                    <div class="profile-avatar">
                        {{ strtoupper(substr($user->name ?? 'U', 0, 1)) }}
                    </div>

                    <div>
                        <h3>{{ $user->name }}</h3>
                        <p>
                            {{ $user->cargo?->nome ?? 'Sem cargo definido' }}
                            @if($user->email)
                                • {{ $user->email }}
                            @endif
                        </p>
                    </div>
                </div>

                <form method="POST" action="{{ route('profile.update') }}">
                    @csrf
                    @method('PUT')

                    <div class="profile-grid">
                        <div class="form-group">
                            <label class="form-label" for="name">Nome</label>
                            <input
                                type="text"
                                id="name"
                                name="name"
                                class="form-control-custom"
                                value="{{ old('name', $user->name) }}"
                                required
                            >
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="email">E-mail</label>
                            <input
                                type="email"
                                id="email"
                                name="email"
                                class="form-control-custom"
                                value="{{ old('email', $user->email) }}"
                                required
                            >
                        </div>

                        <div class="form-group form-group-full">
                            <label class="form-label" for="numero">Número</label>
                            <input
                                type="text"
                                id="numero"
                                name="numero"
                                class="form-control-custom"
                                value="{{ old('numero', $user->numero) }}"
                                placeholder="Digite seu número"
                            >
                        </div>
                    </div>

                    <hr style="border-color: rgba(255,255,255,.08); margin: 24px 0;">

                    <div class="profile-grid">
                        <div class="form-group">
                            <label class="form-label" for="password">Nova senha</label>
                            <input
                                type="password"
                                id="password"
                                name="password"
                                class="form-control-custom"
                                placeholder="Digite uma nova senha"
                            >
                            <p class="helper-text">Preencha apenas se quiser alterar a senha.</p>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="password_confirmation">Confirmar nova senha</label>
                            <input
                                type="password"
                                id="password_confirmation"
                                name="password_confirmation"
                                class="form-control-custom"
                                placeholder="Repita a nova senha"
                            >
                        </div>
                    </div>

                    <div class="profile-actions">
                        <a href="{{ url()->previous() }}" class="btn btn-secondary">
                            Voltar
                        </a>

                        <button type="submit" class="btn btn-green">
                            <i class="bi bi-check2-circle"></i>
                            Salvar alterações
                        </button>
                    </div>
                </form>
            </div>
        </div>

        @if($isCaboTurma)
            <div class="card profile-card">
                <div class="card-body">
                    <div class="face-profile-box">
                        @if($facePhotoUrl)
                            <img
                                src="{{ $facePhotoUrl }}"
                                alt="Foto da validação facial"
                                class="face-profile-photo"
                            >
                        @else
                            <div class="face-profile-placeholder">
                                Sem foto facial
                            </div>
                        @endif

                        <div class="face-profile-content">
                            <h3>Validação facial</h3>
                            <p>
                                @if($user->face_registered_at)
                                    Foto cadastrada em {{ $user->face_registered_at->format('d/m/Y H:i') }}.
                                @else
                                    Nenhuma foto facial cadastrada até o momento.
                                @endif
                            </p>
                        </div>

                        <div class="face-profile-action">
                            <a href="{{ route('face.update') }}" class="btn btn-green">
                                <i class="bi bi-camera"></i>
                                Alterar validação facial
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection