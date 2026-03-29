@extends('layouts.app')

@section('title', 'Detalhes de Visitas do Usuário')
@section('pageTitle', 'Detalhes de Visitas')
@section('pageDescription', 'Acompanhe o histórico detalhado de visitas do usuário.')

@section('content')
<style>
    .user-visits-page{
        display:flex;
        flex-direction:column;
        gap:16px;
    }

    .user-visits-hero .card-body,
    .user-visits-filter-card .card-body,
    .user-visits-summary-card .card-body,
    .user-visits-table-card .card-body,
    .visit-kpi-card .card-body,
    .user-visits-report-card .card-body{
        padding:20px;
    }

    .user-visits-hero{
        overflow:hidden;
    }

    .user-visits-hero-inner{
        display:flex;
        justify-content:space-between;
        align-items:flex-start;
        gap:16px;
        flex-wrap:wrap;
    }

    .user-visits-title{
        margin:0 0 8px 0;
        font-size:1.4rem;
        font-weight:800;
        color:#fff;
        line-height:1.25;
    }

    .user-visits-description{
        margin:0;
        color:rgba(255,255,255,.72);
        line-height:1.6;
        font-size:.96rem;
    }

    .user-visits-user-badge{
        display:inline-flex;
        align-items:center;
        justify-content:center;
        min-height:40px;
        padding:8px 14px;
        border-radius:999px;
        background:rgba(34,197,94,.14);
        color:#22c55e;
        font-weight:800;
        font-size:.92rem;
        white-space:nowrap;
    }

    .visit-kpi-grid{
        display:grid;
        grid-template-columns:repeat(4, minmax(0, 1fr));
        gap:16px;
    }

    .visit-kpi-card{
        min-height:132px;
        display:flex;
        align-items:stretch;
    }

    .visit-kpi-card .card-body{
        width:100%;
        display:flex;
        flex-direction:column;
        justify-content:center;
    }

    .visit-kpi-label{
        color:rgba(255,255,255,.72);
        font-size:.94rem;
        margin-bottom:10px;
        line-height:1.35;
        font-weight:700;
    }

    .visit-kpi-value{
        font-size:2rem;
        font-weight:800;
        line-height:1;
        color:#fff;
    }

    .visit-kpi-helper{
        margin-top:10px;
        color:rgba(255,255,255,.58);
        font-size:.9rem;
        line-height:1.45;
    }

    .user-visits-filter-header,
    .user-visits-section-header{
        display:flex;
        justify-content:space-between;
        align-items:flex-start;
        gap:14px;
        flex-wrap:wrap;
        margin-bottom:18px;
    }

    .user-visits-section-title{
        margin:0;
        font-size:1.08rem;
        font-weight:800;
        color:#fff;
    }

    .user-visits-section-subtitle{
        margin:4px 0 0 0;
        color:rgba(255,255,255,.72);
        font-size:.94rem;
        line-height:1.5;
    }

    .filters-grid-user-visits{
        display:grid;
        grid-template-columns:1fr 1fr auto auto;
        gap:14px;
        align-items:end;
    }

    .filters-grid-user-visits .form-group{
        margin-bottom:0;
        min-width:0;
    }

    .filters-grid-user-visits .form-label{
        margin-bottom:8px;
        font-weight:700;
        color:#fff;
    }

    .user-visits-filter-actions{
        display:flex;
        gap:10px;
        flex-wrap:wrap;
        align-items:center;
    }

    .user-visits-filter-actions .btn,
    .user-visits-filter-extra .btn,
    .report-download-btn{
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

    .user-visits-filter-actions .btn-primary,
    .report-download-btn{
        background:linear-gradient(135deg, #2f80ed, #1c64d1);
        color:#fff;
        box-shadow:0 8px 20px rgba(47,128,237,.22);
    }

    .user-visits-filter-actions .btn-primary:hover,
    .report-download-btn:hover{
        transform:translateY(-1px);
        filter:brightness(1.05);
    }

    .user-visits-filter-actions .btn-secondary,
    .user-visits-filter-extra .btn-secondary{
        background:rgba(255,255,255,.06);
        color:#fff;
        border:1px solid rgba(255,255,255,.10);
    }

    .user-visits-filter-actions .btn-secondary:hover,
    .user-visits-filter-extra .btn-secondary:hover{
        background:rgba(255,255,255,.10);
    }

    .user-visits-filter-extra{
        display:flex;
        justify-content:flex-end;
        align-items:end;
    }

    .summary-list{
        display:grid;
        grid-template-columns:repeat(2, minmax(0, 1fr));
        gap:14px;
    }

    .summary-item,
    .report-stat-item{
        border:1px solid rgba(255,255,255,.08);
        border-radius:18px;
        padding:16px;
        background:rgba(255,255,255,.04);
    }

    .summary-item-title,
    .report-stat-title{
        font-weight:800;
        font-size:1rem;
        color:#fff;
        margin-bottom:10px;
        line-height:1.35;
        word-break:break-word;
    }

    .summary-item-meta,
    .report-stat-meta{
        color:rgba(255,255,255,.72);
        font-size:.93rem;
        line-height:1.65;
        word-break:break-word;
    }

    .summary-item-meta strong,
    .report-stat-meta strong{
        color:#fff;
    }

    .summary-route-badge{
        display:inline-flex;
        align-items:center;
        padding:6px 10px;
        border-radius:999px;
        background:rgba(34,197,94,.14);
        color:#22c55e;
        font-weight:800;
        font-size:.83rem;
        margin-top:10px;
    }

    .report-stats-grid{
        display:grid;
        grid-template-columns:repeat(4, minmax(0, 1fr));
        gap:14px;
        margin-top:16px;
    }

    .monthly-breakdown-table{
        width:100%;
        border-collapse:separate;
        border-spacing:0;
        margin-top:16px;
    }

    .monthly-breakdown-table th,
    .monthly-breakdown-table td{
        padding:14px 12px;
        text-align:left;
        border-bottom:1px solid rgba(255,255,255,.08);
        vertical-align:top;
    }

    .monthly-breakdown-table th{
        color:#fff;
        font-size:.88rem;
        font-weight:800;
    }

    .monthly-breakdown-table td{
        color:rgba(255,255,255,.78);
        font-size:.93rem;
        line-height:1.55;
    }

    .monthly-breakdown-table tr:last-child td{
        border-bottom:none;
    }

    .monthly-breakdown-count{
        font-weight:800;
        color:#fff;
    }

    .user-visits-count-badge{
        display:inline-flex;
        align-items:center;
        justify-content:center;
        min-height:38px;
        padding:8px 14px;
        border-radius:999px;
        background:rgba(29,122,255,.14);
        color:#1e90ff;
        font-weight:800;
        font-size:.92rem;
        white-space:nowrap;
    }

    .table-responsive{
        width:100%;
        overflow-x:auto;
    }

    .user-visits-table{
        width:100%;
        border-collapse:separate;
        border-spacing:0;
        table-layout:fixed;
    }

    .user-visits-table col:nth-child(1){
        width:170px;
    }

    .user-visits-table col:nth-child(2){
        width:150px;
    }

    .user-visits-table col:nth-child(3){
        width:300px;
    }

    .user-visits-table col:nth-child(4){
        width:auto;
    }

    .user-visits-table thead th{
        text-align:left;
        padding:14px 16px;
        font-size:.88rem;
        font-weight:800;
        color:#fff;
        border-bottom:1px solid rgba(255,255,255,.08);
        white-space:nowrap;
    }

    .user-visits-table tbody td{
        padding:16px;
        vertical-align:top;
        border-bottom:1px solid rgba(255,255,255,.06);
    }

    .user-visits-table tbody tr:last-child td{
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
        margin-bottom:8px;
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

    @media (max-width: 1200px){
        .visit-kpi-grid,
        .report-stats-grid{
            grid-template-columns:repeat(2, minmax(0, 1fr));
        }

        .filters-grid-user-visits{
            grid-template-columns:1fr 1fr;
        }

        .user-visits-filter-actions,
        .user-visits-filter-extra{
            justify-content:flex-start;
        }
    }

    @media (max-width: 768px){
        .user-visits-hero .card-body,
        .user-visits-filter-card .card-body,
        .user-visits-summary-card .card-body,
        .user-visits-table-card .card-body,
        .visit-kpi-card .card-body,
        .user-visits-report-card .card-body{
            padding:16px;
        }

        .user-visits-title{
            font-size:1.2rem;
        }

        .visit-kpi-grid,
        .summary-list,
        .filters-grid-user-visits,
        .report-stats-grid{
            grid-template-columns:1fr;
        }

        .visit-kpi-card{
            min-height:118px;
        }

        .visit-kpi-value{
            font-size:1.75rem;
        }

        .user-visits-filter-actions,
        .user-visits-filter-extra{
            width:100%;
        }

        .user-visits-filter-actions .btn,
        .user-visits-filter-extra .btn,
        .report-download-btn{
            flex:1 1 100%;
            width:100%;
        }

        .table-responsive{
            overflow:visible;
        }

        .user-visits-table,
        .user-visits-table thead,
        .user-visits-table tbody,
        .user-visits-table th,
        .user-visits-table td,
        .user-visits-table tr,
        .user-visits-table colgroup,
        .user-visits-table col{
            display:block;
            width:100%;
        }

        .user-visits-table thead{
            display:none;
        }

        .user-visits-table tbody{
            display:flex;
            flex-direction:column;
            gap:14px;
        }

        .user-visits-table tr{
            background:rgba(255,255,255,.04);
            border:1px solid rgba(255,255,255,.08);
            border-radius:18px;
            padding:14px;
        }

        .user-visits-table td{
            border:none !important;
            padding:0 !important;
            margin-bottom:14px;
            background:transparent !important;
        }

        .user-visits-table td:last-child{
            margin-bottom:0;
        }

        .user-visits-table td::before{
            content:attr(data-label);
            display:block;
            font-size:.78rem;
            font-weight:800;
            letter-spacing:.03em;
            text-transform:uppercase;
            color:rgba(255,255,255,.55);
            margin-bottom:6px;
        }

        .visit-address,
        .visit-report{
            font-size:.95rem;
        }

        .monthly-breakdown-table{
            display:block;
            overflow-x:auto;
            white-space:nowrap;
        }
    }
</style>

<div class="user-visits-page">
    <div class="card user-visits-hero">
        <div class="card-body">
            <div class="user-visits-hero-inner">
                <div>
                    <h3 class="user-visits-title">{{ $user->name }}</h3>
                    <p class="user-visits-description">
                        Resumo do período selecionado, indicadores mensais e histórico detalhado das visitas realizadas.
                    </p>
                </div>

                <div class="user-visits-user-badge">
                    Cabo de turma selecionado
                </div>
            </div>
        </div>
    </div>

    <div class="visit-kpi-grid">
        <div class="card visit-kpi-card">
            <div class="card-body">
                <div class="visit-kpi-label">Visitas no mês atual</div>
                <div class="visit-kpi-value">{{ $kpis['current_month_visits'] }}</div>
                <div class="visit-kpi-helper">
                    Mês anterior: {{ $kpis['previous_month_visits'] }}
                </div>
            </div>
        </div>

        <div class="card visit-kpi-card">
            <div class="card-body">
                <div class="visit-kpi-label">Locais distintos no mês</div>
                <div class="visit-kpi-value">{{ $kpis['current_month_unique_locations'] }}</div>
                <div class="visit-kpi-helper">
                    Mês anterior: {{ $kpis['previous_month_unique_locations'] }}
                </div>
            </div>
        </div>

        <div class="card visit-kpi-card">
            <div class="card-body">
                <div class="visit-kpi-label">Variação de visitas</div>
                <div class="visit-kpi-value">
                    {{ $kpis['visits_diff'] >= 0 ? '+' : '' }}{{ $kpis['visits_diff'] }}
                </div>
                <div class="visit-kpi-helper">
                    Comparação entre mês atual e mês anterior
                </div>
            </div>
        </div>

        <div class="card visit-kpi-card">
            <div class="card-body">
                <div class="visit-kpi-label">Variação de locais</div>
                <div class="visit-kpi-value">
                    {{ $kpis['locations_diff'] >= 0 ? '+' : '' }}{{ $kpis['locations_diff'] }}
                </div>
                <div class="visit-kpi-helper">
                    Comparação entre mês atual e mês anterior
                </div>
            </div>
        </div>
    </div>

    <div class="card user-visits-filter-card">
        <div class="card-body">
            <div class="user-visits-filter-header">
                <div>
                    <h3 class="user-visits-section-title">Filtros de análise</h3>
                    <p class="user-visits-section-subtitle">
                        Filtre por data específica ou por mês de referência para refinar os resultados.
                    </p>
                </div>
            </div>

            <form method="GET" action="{{ route('admin.visits.user-summary', $user->id) }}">
                <div class="filters-grid-user-visits">
                    <div class="form-group">
                        <label class="form-label" for="date">Data específica</label>
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

                    <div class="form-group user-visits-filter-actions">
                        <button type="submit" class="btn btn-primary">
                            Filtrar resultados
                        </button>

                        <a href="{{ route('admin.visits.user-summary', $user->id) }}" class="btn btn-secondary">
                            Limpar filtros
                        </a>
                    </div>

                    <div class="form-group user-visits-filter-extra">
                        <a href="{{ route('admin.visits.index') }}" class="btn btn-secondary">
                            Voltar para visitas
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card user-visits-report-card">
        <div class="card-body">
            <div class="user-visits-section-header">
                <div>
                    <h3 class="user-visits-section-title">Relatório mensal do cabo</h3>
                    <p class="user-visits-section-subtitle">
                        Resumo consolidado do mês de referência com frequência, locais visitados e histórico pronto para exportação.
                    </p>
                </div>

                <a
                    href="{{ route('admin.visits.user-summary.pdf', ['user' => $user->id, 'date' => $filters['date'] ?? null, 'month' => $filters['month'] ?? null]) }}"
                    target="_blank"
                    class="report-download-btn"
                >
                    Baixar PDF
                </a>
            </div>

            <div class="report-stats-grid">
                <div class="report-stat-item">
                    <div class="report-stat-title">Mês de referência</div>
                    <div class="report-stat-meta">
                        <strong>{{ $monthlyReport['reference_month_label'] }}</strong>
                    </div>
                </div>

                <div class="report-stat-item">
                    <div class="report-stat-title">Total de visitas</div>
                    <div class="report-stat-meta">
                        <strong>{{ $monthlyReport['total_visits'] }}</strong>
                    </div>
                </div>

                <div class="report-stat-item">
                    <div class="report-stat-title">Dias com visitas</div>
                    <div class="report-stat-meta">
                        <strong>{{ $monthlyReport['visited_days'] }}</strong>
                    </div>
                </div>

                <div class="report-stat-item">
                    <div class="report-stat-title">Locais diferentes</div>
                    <div class="report-stat-meta">
                        <strong>{{ $monthlyReport['unique_locations'] }}</strong>
                    </div>
                </div>
            </div>

            <div class="report-stats-grid">
                <div class="report-stat-item">
                    <div class="report-stat-title">Primeira visita do mês</div>
                    <div class="report-stat-meta">
                        <strong>{{ $monthlyReport['first_visit'] ?: '-' }}</strong>
                    </div>
                </div>

                <div class="report-stat-item">
                    <div class="report-stat-title">Última visita do mês</div>
                    <div class="report-stat-meta">
                        <strong>{{ $monthlyReport['last_visit'] ?: '-' }}</strong>
                    </div>
                </div>

                <div class="report-stat-item">
                    <div class="report-stat-title">Média por dia visitado</div>
                    <div class="report-stat-meta">
                        <strong>{{ $monthlyReport['average_per_visited_day'] }}</strong>
                    </div>
                </div>

                <div class="report-stat-item">
                    <div class="report-stat-title">Local mais visitado</div>
                    <div class="report-stat-meta">
                        <strong>{{ $monthlyReport['most_visited_location']['location_name'] ?? 'Nenhum local' }}</strong><br>
                        Total: {{ $monthlyReport['most_visited_location']['total_visits'] ?? 0 }}
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="monthly-breakdown-table">
                    <thead>
                        <tr>
                            <th>Local</th>
                            <th>Rota</th>
                            <th>Endereço</th>
                            <th>Total no mês</th>
                            <th>Última visita</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($monthlyLocationSummary as $summary)
                            <tr>
                                <td>{{ $summary['location_name'] }}</td>
                                <td>{{ $summary['route_name'] ?: 'Sem rota' }}</td>
                                <td>{{ $summary['address'] ?: 'Não informado' }}</td>
                                <td class="monthly-breakdown-count">{{ $summary['total_visits'] }}</td>
                                <td>{{ $summary['last_visit'] ?: '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5">Nenhum local visitado no mês selecionado.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card user-visits-summary-card">
        <div class="card-body">
            <div class="user-visits-section-header">
                <div>
                    <h3 class="user-visits-section-title">Resumo dos locais visitados no mês</h3>
                    <p class="user-visits-section-subtitle">
                        Veja os principais locais visitados, a rota vinculada e a quantidade de registros.
                    </p>
                </div>
            </div>

            <div class="summary-list">
                @forelse($monthlyLocationSummary as $summary)
                    <div class="summary-item">
                        <div class="summary-item-title">
                            {{ $summary['location_name'] }}
                        </div>

                        <div class="summary-item-meta">
                            <strong>Total de visitas:</strong> {{ $summary['total_visits'] }}<br>
                            <strong>Endereço:</strong> {{ $summary['address'] ?: 'Não informado' }}<br>
                            <strong>Última visita:</strong> {{ $summary['last_visit'] ?: '-' }}
                        </div>

                        <div class="summary-route-badge">
                            {{ $summary['route_name'] ?: 'Sem rota' }}
                        </div>
                    </div>
                @empty
                    <div class="summary-item">
                        <div class="summary-item-meta">
                            Nenhum local visitado no mês selecionado.
                        </div>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <div class="card user-visits-table-card">
        <div class="card-body">
            <div class="user-visits-section-header">
                <div>
                    <h3 class="user-visits-section-title">Visitas detalhadas</h3>
                    <p class="user-visits-section-subtitle">
                        Histórico completo das visitas registradas para os filtros selecionados, incluindo o que foi feito em cada visita.
                    </p>
                </div>

                <div class="user-visits-count-badge">
                    {{ $visits->total() }} {{ $visits->total() === 1 ? 'registro encontrado' : 'registros encontrados' }}
                </div>
            </div>

            <div class="table-responsive">
                <table class="table-custom user-visits-table">
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
                                        Não há registros para os filtros informados.
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div style="margin-top:18px;">
                {{ $visits->links() }}
            </div>
        </div>
    </div>
</div>
@endsection