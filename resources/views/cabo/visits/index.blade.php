@extends('layouts.app')

@section('title', 'Minhas Visitas')
@section('pageTitle', 'Minhas Visitas')
@section('pageDescription', 'Acompanhe o histórico das visitas registradas.')

@section('content')
<style>
    .visits-page{
        display:flex;
        flex-direction:column;
        gap:16px;
    }

    .visits-filter-card .card-body,
    .visits-table-card .card-body,
    .visits-kpi-card .card-body{
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

    .visits-kpi-grid{
        display:grid;
        grid-template-columns:repeat(5, minmax(0, 1fr));
        gap:16px;
    }

    .visits-kpi-card{
        min-height:128px;
        display:flex;
        align-items:stretch;
    }

    .visits-kpi-card .card-body{
        width:100%;
        display:flex;
        flex-direction:column;
        justify-content:center;
    }

    .visits-kpi-label{
        color:rgba(255,255,255,.72);
        font-size:.9rem;
        margin-bottom:10px;
        line-height:1.35;
        font-weight:700;
    }

    .visits-kpi-value{
        font-size:1.9rem;
        font-weight:800;
        line-height:1;
        color:#fff;
    }

    .visits-kpi-value-text{
        font-size:1.1rem;
        font-weight:800;
        line-height:1.35;
        color:#fff;
        word-break:break-word;
    }

    .visits-kpi-helper{
        margin-top:10px;
        color:rgba(255,255,255,.58);
        font-size:.88rem;
        line-height:1.45;
    }

    .filters-grid-cabo-visits{
        display:grid;
        grid-template-columns:1fr 1fr 1fr auto auto;
        gap:14px;
        align-items:end;
    }

    .filters-grid-cabo-visits .form-group{
        margin-bottom:0;
    }

    .filters-grid-cabo-visits .form-label{
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
        width:170px;
    }

    .visits-table col:nth-child(2){
        width:150px;
    }

    .visits-table col:nth-child(3){
        width:280px;
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

    .visit-route-badge{
        display:inline-flex;
        align-items:center;
        justify-content:center;
        padding:7px 12px;
        border-radius:999px;
        background:rgba(34,197,94,.14);
        color:#22c55e;
        font-weight:800;
        font-size:.85rem;
        line-height:1.1;
        max-width:100%;
    }

    .visit-route-empty{
        display:inline-flex;
        align-items:center;
        justify-content:center;
        padding:7px 12px;
        border-radius:999px;
        background:rgba(255,255,255,.08);
        color:rgba(255,255,255,.72);
        font-weight:700;
        font-size:.85rem;
        line-height:1.1;
    }

    .visit-place{
        font-weight:800;
        font-size:1rem;
        color:#fff;
        line-height:1.35;
        margin-bottom:6px;
        word-break:break-word;
    }

    .visit-address{
        font-size:.93rem;
        color:rgba(255,255,255,.72);
        line-height:1.55;
        word-break:break-word;
        max-width:100%;
    }

    .visit-report{
        font-size:.94rem;
        color:rgba(255,255,255,.82);
        line-height:1.6;
        white-space:pre-line;
        word-break:break-word;
    }

    .visit-report-empty{
        color:rgba(255,255,255,.55);
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

    @media (max-width: 1300px){
        .visits-kpi-grid{
            grid-template-columns:repeat(3, minmax(0, 1fr));
        }

        .filters-grid-cabo-visits{
            grid-template-columns:1fr 1fr;
        }

        .visits-filter-actions,
        .visits-filter-extra{
            justify-content:flex-start;
        }
    }

    @media (max-width: 768px){
        .visits-filter-card .card-body,
        .visits-table-card .card-body,
        .visits-kpi-card .card-body{
            padding:16px;
        }

        .visits-kpi-grid,
        .filters-grid-cabo-visits{
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

        .visit-place{
            font-size:.97rem;
            margin-bottom:8px;
        }

        .visit-address{
            font-size:.95rem;
        }

        .visits-count-badge{
            font-size:.87rem;
        }
    }
    .visits-pagination-wrap{
    margin-top:18px;
    display:flex;
    justify-content:center;
}

.visits-pagination-wrap nav{
    width:100%;
}

.visits-pagination-wrap nav > div{
    display:flex;
    justify-content:space-between;
    align-items:center;
    gap:12px;
    flex-wrap:wrap;
}

.visits-pagination-wrap p{
    margin:0;
    color:rgba(255,255,255,.68);
    font-size:.92rem;
    line-height:1.45;
}

.visits-pagination-wrap .hidden{
    display:none !important;
}

.visits-pagination-wrap .relative.z-0.inline-flex{
    display:flex;
    align-items:center;
    gap:8px;
    flex-wrap:wrap;
}

.visits-pagination-wrap .relative.inline-flex.items-center{
    display:inline-flex !important;
    align-items:center !important;
    justify-content:center !important;
    min-width:42px;
    height:42px;
    padding:0 14px !important;
    border-radius:12px !important;
    border:1px solid rgba(255,255,255,.10);
    background:rgba(255,255,255,.04);
    color:#fff;
    text-decoration:none;
    font-weight:700;
    line-height:1;
    box-sizing:border-box;
}

.visits-pagination-wrap span.relative.inline-flex.items-center{
    background:rgba(30,144,255,.16);
    color:#60a5fa;
    border-color:rgba(96,165,250,.28);
}

.visits-pagination-wrap a.relative.inline-flex.items-center:hover{
    background:rgba(255,255,255,.08);
}

.visits-pagination-wrap .relative.inline-flex.items-center svg{
    width:16px !important;
    height:16px !important;
    max-width:16px !important;
    max-height:16px !important;
    flex:none;
}

@media (max-width: 768px){
    .visits-pagination-wrap nav > div{
        flex-direction:column;
        align-items:stretch;
    }

    .visits-pagination-wrap p{
        text-align:center;
        order:2;
    }

    .visits-pagination-wrap .relative.z-0.inline-flex{
        justify-content:center;
        order:1;
    }

    .visits-pagination-wrap .relative.inline-flex.items-center{
        min-width:40px;
        height:40px;
        padding:0 12px !important;
        font-size:.92rem;
    }
}
.visits-pagination-wrap .relative.z-0.inline-flex{
    width:100%;
    display:flex;
    justify-content:space-between;
    align-items:center;
}

.visits-pagination-wrap .relative.z-0.inline-flex a:first-child{
    margin-right:auto;
}

.visits-pagination-wrap .relative.z-0.inline-flex a:last-child{
    margin-left:auto;
}
</style>

<div class="visits-page">

    @if(session('success'))
        <div class="alert-success-box">
            {{ session('success') }}
        </div>
    @endif

    <div class="visits-kpi-grid">
        <div class="card visits-kpi-card">
            <div class="card-body">
                <div class="visits-kpi-label">Visitas no período</div>
                <div class="visits-kpi-value">{{ $kpis['total_visits'] }}</div>
                <div class="visits-kpi-helper">
                    Referência: {{ $kpis['period_label'] }}
                </div>
            </div>
        </div>

        <div class="card visits-kpi-card">
            <div class="card-body">
                <div class="visits-kpi-label">Locais visitados</div>
                <div class="visits-kpi-value">{{ $kpis['unique_locations'] }}</div>
                <div class="visits-kpi-helper">
                    Locais diferentes no período
                </div>
            </div>
        </div>

        <div class="card visits-kpi-card">
            <div class="card-body">
                <div class="visits-kpi-label">Dias com visitas</div>
                <div class="visits-kpi-value">{{ $kpis['visited_days'] }}</div>
                <div class="visits-kpi-helper">
                    Dias em que houve registro
                </div>
            </div>
        </div>

        <div class="card visits-kpi-card">
            <div class="card-body">
                <div class="visits-kpi-label">Rotas alcançadas</div>
                <div class="visits-kpi-value">{{ $kpis['unique_routes'] }}</div>
                <div class="visits-kpi-helper">
                    Quantidade de rotas diferentes
                </div>
            </div>
        </div>

        <div class="card visits-kpi-card">
            <div class="card-body">
                <div class="visits-kpi-label">Local mais visitado</div>
                <div class="visits-kpi-value-text">
                    {{ $kpis['most_visited_location'] }}
                </div>
                <div class="visits-kpi-helper">
                    {{ $kpis['most_visited_location_total'] }} visita(s) no período
                </div>
            </div>
        </div>
    </div>

    <div class="card visits-filter-card">
        <div class="card-body">
            <div class="visits-filter-header">
                <div>
                    <h3 class="visits-filter-title">Filtros de busca</h3>
                    <p class="visits-filter-subtitle">
                        Filtre suas visitas por dia, mês e rota para localizar registros com mais facilidade.
                    </p>
                </div>
            </div>

            <form method="GET" action="{{ route('cabo.visits.index') }}">
                <div class="filters-grid-cabo-visits">
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
                        <label class="form-label" for="month">Mês de referência</label>
                        <input
                            type="month"
                            name="month"
                            id="month"
                            class="form-control-custom"
                            value="{{ $filters['month'] ?? '' }}"
                        >
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="route_id">Rota</label>
                        <select name="route_id" id="route_id" class="form-control-custom">
                            <option value="">Todas as rotas</option>
                            @foreach($routes as $route)
                                <option
                                    value="{{ $route->id }}"
                                    @selected((string) ($filters['route_id'] ?? '') === (string) $route->id)
                                >
                                    {{ $route->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group visits-filter-actions">
                        <button type="submit" class="btn btn-primary">
                            Filtrar visitas
                        </button>

                        <a href="{{ route('cabo.visits.index') }}" class="btn btn-secondary">
                            Limpar filtros
                        </a>
                    </div>

                    <div class="form-group visits-filter-extra">
                        <a href="{{ route('cabo.visits.create') }}" class="btn btn-green">
                            Registrar nova visita
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
                        Confira os registros realizados com data, rota, local e descrição da visita.
                    </p>
                </div>

                <div class="visits-count-badge">
                    {{ $visits->total() }} {{ $visits->total() === 1 ? 'registro encontrado' : 'registros encontrados' }}
                </div>
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
                            <th>Data e hora</th>
                            <th>Rota</th>
                            <th>Local / Endereço</th>
                            <th>O que foi feito</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($visits as $visit)
                            <tr>
                                <td data-label="Data e hora">
                                    <div class="visit-date">
                                        {{ $visit->visited_at?->format('d/m/Y') ?? '-' }}
                                    </div>
                                    <div class="visit-time">
                                        {{ $visit->visited_at?->format('H:i:s') ?? 'Horário não informado' }}
                                    </div>
                                </td>

                                <td data-label="Rota">
                                    @if($visit->location?->route?->name)
                                        <span class="visit-route-badge">
                                            {{ $visit->location->route->name }}
                                        </span>
                                    @else
                                        <span class="visit-route-empty">
                                            Sem rota
                                        </span>
                                    @endif
                                </td>

                                <td data-label="Local / Endereço">
                                    <div class="visit-place">
                                        {{ $visit->location?->name ?: ($visit->display_name ?: 'Local não identificado') }}
                                    </div>

                                    <div class="visit-address">
                                        {{ $visit->address ?: 'Endereço não informado.' }}
                                    </div>
                                </td>

                                <td data-label="O que foi feito">
                                    @if(! empty($visit->service_report))
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

            <div class="visits-pagination-wrap">
    {{ $visits->links() }}
</div>
        </div>
    </div>
</div>
@endsection