@extends('layouts.app')

@section('title', 'Selecionar Cabo de Turma')
@section('pageTitle', 'Detalhamento de Visitas')
@section('pageDescription', 'Selecione o cabo de turma para visualizar o resumo detalhado das visitas.')

@section('content')
<style>
    .summary-selector-page{
        display:flex;
        justify-content:center;
    }

    .summary-selector-wrapper{
        width:100%;
        max-width:760px;
    }

    .summary-selector-card .card-body{
        padding:28px;
    }

    .summary-selector-header{
        margin-bottom:22px;
    }

    .summary-selector-title{
        margin:0 0 10px 0;
        font-size:1.35rem;
        font-weight:800;
        color:#fff;
        line-height:1.3;
    }

    .summary-selector-description{
        margin:0;
        color:rgba(255,255,255,.72);
        line-height:1.6;
        font-size:.96rem;
    }

    .summary-selector-form{
        display:flex;
        flex-direction:column;
        gap:18px;
    }

    .summary-selector-form .form-group{
        margin-bottom:0;
    }

    .summary-selector-form .form-label{
        margin-bottom:8px;
        font-weight:700;
        color:#fff;
    }

    .summary-selector-hint{
        margin-top:8px;
        font-size:.9rem;
        color:rgba(255,255,255,.58);
        line-height:1.45;
    }

    .summary-selector-actions{
        display:flex;
        gap:12px;
        flex-wrap:wrap;
        margin-top:4px;
    }

    .summary-selector-actions .btn{
        min-height:46px;
        padding:12px 18px;
        display:inline-flex;
        align-items:center;
        justify-content:center;
        border-radius:12px;
        font-weight:800;
        text-decoration:none;
        border:none;
        transition:.2s ease;
        white-space:nowrap;
    }

    .summary-selector-actions .btn-primary{
        background:linear-gradient(135deg, #2f80ed, #1c64d1);
        color:#fff;
        box-shadow:0 8px 20px rgba(47,128,237,.22);
    }

    .summary-selector-actions .btn-primary:hover{
        transform:translateY(-1px);
        filter:brightness(1.05);
    }

    .summary-selector-actions .btn-secondary{
        background:rgba(255,255,255,.06);
        color:#fff;
        border:1px solid rgba(255,255,255,.10);
    }

    .summary-selector-actions .btn-secondary:hover{
        background:rgba(255,255,255,.10);
    }

    .summary-selector-note{
        margin-top:20px;
        padding:14px 16px;
        border-radius:14px;
        background:rgba(255,255,255,.04);
        border:1px solid rgba(255,255,255,.08);
        color:rgba(255,255,255,.70);
        font-size:.93rem;
        line-height:1.55;
    }

    .summary-selector-note strong{
        color:#fff;
    }

    @media (max-width: 768px){
        .summary-selector-card .card-body{
            padding:20px 16px;
        }

        .summary-selector-title{
            font-size:1.18rem;
        }

        .summary-selector-description{
            font-size:.95rem;
        }

        .summary-selector-actions{
            flex-direction:column;
        }

        .summary-selector-actions .btn{
            width:100%;
        }

        .summary-selector-note{
            margin-top:16px;
            font-size:.92rem;
        }
    }
</style>

<div class="summary-selector-page">
    <div class="summary-selector-wrapper">
        <div class="card summary-selector-card">
            <div class="card-body">
                <div class="summary-selector-header">
                    <h3 class="summary-selector-title">Selecionar cabo de turma</h3>
                    <p class="summary-selector-description">
                        Escolha um usuário para visualizar os indicadores mensais, os locais visitados
                        e o histórico detalhado das visitas registradas.
                    </p>
                </div>

                <form method="GET" action="{{ route('admin.visits.summary-redirect') }}" class="summary-selector-form">
                    <div class="form-group">
                        <label class="form-label" for="user_id">Cabo de turma</label>
                        <select name="user_id" id="user_id" class="form-control-custom" required>
                            <option value="">Selecione um cabo de turma</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}">
                                    {{ $user->name }}
                                </option>
                            @endforeach
                        </select>

                        <div class="summary-selector-hint">
                            Selecione o cabo desejado para abrir a tela com o resumo completo das visitas.
                        </div>
                    </div>

                    <div class="summary-selector-actions">
                        <button type="submit" class="btn btn-primary">
                            Ver detalhamento
                        </button>

                        <a href="{{ route('admin.visits.index') }}" class="btn btn-secondary">
                            Voltar para visitas
                        </a>
                    </div>
                </form>

                <div class="summary-selector-note">
                    <strong>Dica:</strong> o detalhamento mostra informações consolidadas do cabo de turma,
                    facilitando a análise de frequência, locais visitados e histórico mensal.
                </div>
            </div>
        </div>
    </div>
</div>
@endsection