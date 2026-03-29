@extends('layouts.app')

@section('title', 'Gerenciar Visitas')
@section('pageTitle', 'Gerenciar Visitas')
@section('pageDescription', 'Acompanhe as visitas registradas pelos cabos de turma.')

@section('content')
<style>
    .visits-page{
        display:flex;
        flex-direction:column;
        gap:16px;
    }

    .visits-filter-card .card-body,
    .visits-table-card .card-body{
        padding:20px;
    }

    .visits-filter-header{
        display:flex;
        justify-content:space-between;
        align-items:center;
        gap:14px;
        flex-wrap:wrap;
        margin-bottom:18px;
    }

    .visits-filter-title{
        margin:0;
        font-size:1.08rem;
        font-weight:800;
        color:#fff;
    }

    .visits-filter-subtitle{
        margin:4px 0 0 0;
        color:rgba(255,255,255,.72);
        font-size:.95rem;
        line-height:1.45;
    }

    .filters-grid-visits{
        display:grid;
        grid-template-columns:1fr 1fr auto auto;
        gap:14px;
        align-items:end;
    }

    .filters-grid-visits .form-group{
        margin-bottom:0;
        min-width:0;
    }

    .filters-grid-visits .form-label{
        margin-bottom:8px;
        font-weight:700;
        color:#fff;
    }

    .visits-filter-actions{
        display:flex;
        gap:10px;
        flex-wrap:wrap;
        align-items:center;
    }

    .visits-filter-actions .btn,
    .visits-filter-extra .btn{
        min-height:44px;
        padding:11px 18px;
        display:inline-flex;
        align-items:center;
        justify-content:center;
        border-radius:12px;
        font-weight:800;
        border:none;
        transition:.2s ease;
        text-decoration:none;
        white-space:nowrap;
    }

    .visits-filter-actions .btn-primary{
        background:linear-gradient(135deg, #2f80ed, #1c64d1);
        color:#fff;
        box-shadow:0 8px 20px rgba(47,128,237,.22);
    }

    .visits-filter-actions .btn-primary:hover{
        transform:translateY(-1px);
        filter:brightness(1.05);
    }

    .visits-filter-actions .btn-secondary{
        background:rgba(255,255,255,.06);
        color:#fff;
        border:1px solid rgba(255,255,255,.10);
    }

    .visits-filter-actions .btn-secondary:hover{
        background:rgba(255,255,255,.10);
    }

    .visits-filter-extra{
        display:flex;
        justify-content:flex-end;
        align-items:end;
    }

    .visits-filter-extra .btn-green{
        background:linear-gradient(135deg, #22c55e, #16a34a);
        color:#fff;
        box-shadow:0 8px 20px rgba(34,197,94,.18);
    }

    .visits-filter-extra .btn-green:hover{
        transform:translateY(-1px);
        filter:brightness(1.04);
    }

    .visits-toolbar{
        display:flex;
        justify-content:space-between;
        align-items:flex-start;
        gap:14px;
        flex-wrap:wrap;
        margin-bottom:16px;
    }

    .visits-toolbar-title{
        margin:0;
        font-size:1.05rem;
        font-weight:800;
        color:#fff;
    }

    .visits-toolbar-subtitle{
        margin:4px 0 0 0;
        color:rgba(255,255,255,.72);
        font-size:.94rem;
    }

    .visits-count-badge{
        display:inline-flex;
        align-items:center;
        justify-content:center;
        min-height:38px;
        padding:8px 14px;
        border-radius:999px;
        background:rgba(29, 122, 255, 0.14);
        color:#1e90ff;
        font-weight:800;
        font-size:.92rem;
        white-space:nowrap;
    }

    .table-responsive{
        width:100%;
        overflow-x:auto;
    }

    .visits-table{
        width:100%;
        border-collapse:separate;
        border-spacing:0;
        table-layout:fixed;
    }

    .visits-table col:nth-child(1){
        width:220px;
    }

    .visits-table col:nth-child(2){
        width:170px;
    }

    .visits-table col:nth-child(3){
        width:300px;
    }

    .visits-table col:nth-child(4){
        width:auto;
    }

    .visits-table thead th{
        text-align:left;
        padding:14px 16px;
        font-size:.88rem;
        font-weight:800;
        color:#fff;
        border-bottom:1px solid rgba(255,255,255,.08);
        white-space:nowrap;
    }

    .visits-table tbody td{
        padding:16px;
        vertical-align:top;
        border-bottom:1px solid rgba(255,255,255,.06);
    }

    .visits-table tbody tr:last-child td{
        border-bottom:none;
    }

    .visit-user{
        font-weight:800;
        color:#fff;
        font-size:.98rem;
        line-height:1.4;
        word-break:break-word;
    }

    .visit-date{
        font-weight:800;
        color:#fff;
        line-height:1.2;
        font-size:1rem;
    }

    .visit-time{
        color:rgba(255,255,255,.68);
        font-size:.92rem;
        margin-top:6px;
    }

    .visit-place{
        font-weight:800;
        font-size:1rem;
        color:#fff;
        line-height:1.35;
        margin-bottom:8px;
        word-break:break-word;
    }

    .visit-route{
        font-size:.88rem;
        color:#22c55e;
        margin-bottom:8px;
        font-weight:700;
        line-height:1.45;
    }

    .visit-route strong{
        color:#86efac;
    }

    .visit-address{
        font-size:.93rem;
        color:rgba(255,255,255,.72);
        line-height:1.55;
        word-break:break-word;
        max-width:100%;
    }

    .visit-report{
        font-size:.93rem;
        color:rgba(255,255,255,.82);
        line-height:1.6;
        white-space:pre-line;
        word-break:break-word;
    }

    .visit-report-empty{
        color:rgba(255,255,255,.50);
        font-style:italic;
    }

    .empty-state-visits{
        text-align:center;
        padding:28px 16px;
        color:rgba(255,255,255,.72);
    }

    .empty-state-visits strong{
        display:block;
        color:#fff;
        font-size:1rem;
        margin-bottom:6px;
    }

    .visit-address-link{
        color:inherit;
        text-decoration:none;
        border-bottom:1px dashed rgba(255,255,255,.22);
        transition:.2s ease;
        word-break:break-word;
    }

    .visit-address-link:hover{
        color:#60a5fa;
        border-bottom-color:#60a5fa;
    }

    @media (max-width: 1100px){
        .filters-grid-visits{
            grid-template-columns:1fr 1fr;
        }

        .visits-filter-actions,
        .visits-filter-extra{
            justify-content:flex-start;
        }
    }

    @media (max-width: 768px){
        .visits-filter-card .card-body,
        .visits-table-card .card-body{
            padding:16px;
        }

        .filters-grid-visits{
            grid-template-columns:1fr;
            gap:12px;
        }

        .visits-filter-actions,
        .visits-filter-extra{
            width:100%;
        }

        .visits-filter-actions .btn,
        .visits-filter-extra .btn{
            flex:1 1 100%;
            width:100%;
        }

        .table-responsive{
            overflow:visible;
        }

        .visits-table,
        .visits-table thead,
        .visits-table tbody,
        .visits-table th,
        .visits-table td,
        .visits-table tr,
        .visits-table colgroup,
        .visits-table col{
            display:block;
            width:100%;
        }

        .visits-table thead{
            display:none;
        }

        .visits-table tbody{
            display:flex;
            flex-direction:column;
            gap:14px;
        }

        .visits-table tr{
            background:rgba(255,255,255,.04);
            border:1px solid rgba(255,255,255,.08);
            border-radius:18px;
            padding:14px;
            box-shadow:none;
        }

        .visits-table td{
            border:none !important;
            padding:0 !important;
            margin-bottom:14px;
            background:transparent !important;
        }

        .visits-table td:last-child{
            margin-bottom:0;
        }

        .visits-table td::before{
            content:attr(data-label);
            display:block;
            font-size:.78rem;
            font-weight:800;
            letter-spacing:.03em;
            text-transform:uppercase;
            color:rgba(255,255,255,.55);
            margin-bottom:6px;
        }

        .visit-user,
        .visit-place{
            font-size:.97rem;
        }

        .visit-address,
        .visit-report{
            font-size:.95rem;
        }

        .visits-count-badge{
            font-size:.87rem;
        }
    }
</style>

<div class="visits-page">

    @if(session('success'))
        <div class="alert-success-box">
            {{ session('success') }}
        </div>
    @endif

    <div class="card visits-filter-card">
        <div class="card-body">
            <div class="visits-filter-header">
                <div>
                    <h3 class="visits-filter-title">Filtros de busca</h3>
                    <p class="visits-filter-subtitle">
                        Filtre as visitas por dia e usuário para localizar registros com mais facilidade.
                    </p>
                </div>
            </div>

            <form method="GET" action="{{ route('admin.visits.index') }}">
                <div class="filters-grid-visits">
                    <div class="form-group">
                        <label class="form-label" for="date">Dia da visita</label>
                        <input
                            type="date"
                            name="date"
                            id="date"
                            class="form-control-custom"
                            value="{{ $filters['date'] ?? '' }}"
                        >
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="user_id">Usuário</label>
                        <select name="user_id" id="user_id" class="form-control-custom">
                            <option value="">Todos os usuários</option>
                            @foreach($users as $user)
                                <option
                                    value="{{ $user->id }}"
                                    @selected((string) ($filters['user_id'] ?? '') === (string) $user->id)
                                >
                                    {{ $user->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group visits-filter-actions">
                        <button type="submit" class="btn btn-primary">
                            Filtrar visitas
                        </button>

                        <a href="{{ route('admin.visits.index') }}" class="btn btn-secondary">
                            Limpar filtros
                        </a>
                    </div>

                    <div class="form-group visits-filter-extra">
                        <a href="{{ route('admin.visits.summary-selector') }}" class="btn btn-green">
                            Ver detalhado
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card visits-table-card">
        <div class="card-body">
            <div class="visits-toolbar">
                <div>
                    <h3 class="visits-toolbar-title">Histórico de visitas</h3>
                    <p class="visits-toolbar-subtitle">
                        Confira os registros com cabo de turma, data, local e descrição do que foi feito.
                    </p>
                </div>

                @if(method_exists($visits, 'total'))
                    <div class="visits-count-badge">
                        {{ $visits->total() }} {{ $visits->total() === 1 ? 'registro encontrado' : 'registros encontrados' }}
                    </div>
                @endif
            </div>

            <div class="table-responsive">
                <table class="table-custom visits-table">
                    <colgroup>
                        <col>
                        <col>
                        <col>
                        <col>
                    </colgroup>
                    <thead>
                        <tr>
                            <th>Cabo de turma</th>
                            <th>Data e hora</th>
                            <th>Local / Endereço</th>
                            <th>O que foi feito</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($visits as $visit)
                            <tr>
                                <td data-label="Cabo de turma">
                                    <div class="visit-user">
                                        {{ $visit->user?->name ?? 'Usuário não encontrado' }}
                                    </div>
                                </td>

                                <td data-label="Data e hora">
                                    <div class="visit-date">
                                        {{ $visit->visited_at?->format('d/m/Y') ?? '-' }}
                                    </div>
                                    <div class="visit-time">
                                        {{ $visit->visited_at?->format('H:i:s') ?? 'Horário não informado' }}
                                    </div>
                                </td>

                                <td data-label="Local / Endereço">
                                    <div class="visit-place">
                                        {{ $visit->location?->name ?: ($visit->display_name ?: 'Local não identificado') }}
                                    </div>

                                    @if($visit->location?->route?->name)
                                        <div class="visit-route">
                                            <strong>Rota:</strong> {{ $visit->location->route->name }}
                                        </div>
                                    @endif

                                    <div class="visit-address">
                                        @if($visit->address)
                                            <a
                                                href="https://www.google.com/maps/search/?api=1&query={{ urlencode($visit->address) }}"
                                                target="_blank"
                                                rel="noopener noreferrer"
                                                class="visit-address-link"
                                            >
                                                {{ $visit->address }}
                                            </a>
                                        @else
                                            Endereço não informado.
                                        @endif
                                    </div>
                                </td>

                                <td data-label="O que foi feito">
                                    @if(!empty($visit->service_report))
                                        <div class="visit-report">
                                            {{ $visit->service_report }}
                                        </div>
                                    @else
                                        <div class="visit-report visit-report-empty">
                                            Nenhum relato informado.
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4">
                                    <div class="empty-state-visits">
                                        <strong>Nenhuma visita encontrada.</strong>
                                        Não há registros para os filtros selecionados no momento.
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if(method_exists($visits, 'links'))
                <div style="margin-top:18px;">
                    {{ $visits->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection