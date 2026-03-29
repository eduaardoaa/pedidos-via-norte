@extends('layouts.app')

@section('title', 'Dashboard Administrativo - Vianorte')
@section('pageTitle', 'Painel Administrativo')
@section('pageDescription', 'Visão geral operacional do sistema.')

@section('content')
<style>
    .dashboard-kpi-value{
        font-size:2rem;
        font-weight:800;
        margin-top:8px;
        line-height:1.1;
        word-break:break-word;
    }

    .mini-bars{
        display:flex;
        align-items:flex-end;
        gap:10px;
        min-height:220px;
        padding-top:14px;
        overflow-x:auto;
        overflow-y:hidden;
        padding-bottom:6px;
    }

    .mini-bar-item{
        flex:1;
        min-width:42px;
        display:flex;
        flex-direction:column;
        align-items:center;
        gap:8px;
    }

    .mini-bar{
        width:100%;
        max-width:52px;
        border-radius:14px 14px 8px 8px;
        background: linear-gradient(180deg, rgba(22,163,74,.95), rgba(22,163,74,.45));
        min-height:10px;
        display:flex;
        align-items:flex-start;
        justify-content:center;
        color:#fff;
        font-size:.76rem;
        font-weight:700;
        padding-top:6px;
        transition:.2s ease;
    }

    .mini-bar.secondary{
        background: linear-gradient(180deg, rgba(59,130,246,.95), rgba(59,130,246,.45));
    }

    .mini-bar-label{
        font-size:.8rem;
        color:var(--muted);
        text-align:center;
        white-space:nowrap;
    }

    .mini-bar-value{
        font-size:.85rem;
        font-weight:700;
        text-align:center;
    }

    .dashboard-alert-list{
        display:flex;
        flex-direction:column;
        gap:12px;
    }

    .dashboard-alert-item{
        padding:12px 14px;
        border-radius:14px;
        background:rgba(255,255,255,.03);
        border:1px solid rgba(255,255,255,.05);
        overflow:hidden;
    }

    .dashboard-highlight{
        border:1px solid rgba(22,163,74,.20);
        box-shadow: 0 0 0 1px rgba(22,163,74,.06), 0 18px 40px rgba(0,0,0,.18);
    }

    .dashboard-summary-grid{
        display:grid;
        grid-template-columns:repeat(4, minmax(0, 1fr));
        gap:12px;
        margin-bottom:16px;
    }

    .dashboard-summary-box{
        padding:14px;
        border-radius:14px;
        background:rgba(255,255,255,.03);
        border:1px solid rgba(255,255,255,.05);
        min-width:0;
    }

    .dashboard-summary-box strong{
        display:block;
        font-size:1.35rem;
        margin-top:4px;
        line-height:1.1;
        word-break:break-word;
    }

    .table-wrap{
        width:100%;
        overflow-x:auto;
        overflow-y:hidden;
        -webkit-overflow-scrolling:touch;
        border-radius:14px;
    }

    .table-wrap::-webkit-scrollbar{
        height:8px;
    }

    .table-wrap::-webkit-scrollbar-thumb{
        background:rgba(255,255,255,.14);
        border-radius:999px;
    }

    .table{
        width:100%;
        min-width:100%;
        border-collapse:collapse;
    }

    .table th,
    .table td{
        white-space:nowrap;
        vertical-align:middle;
    }

    .table th:first-child,
    .table td:first-child{
        min-width:160px;
    }

    .table td{
        max-width:260px;
        overflow:hidden;
        text-overflow:ellipsis;
    }

    .card{
        min-width:0;
        overflow:hidden;
    }

    .card-header,
    .card-body{
        min-width:0;
    }

    .page-head,
    .topbar-left,
    .card-header{
        min-width:0;
    }

    .card-title,
    .card-subtitle,
    .text-muted-small{
        word-break:break-word;
    }

    .badge-status{
        white-space:nowrap;
    }

    .table-mobile-sm{
        min-width:520px;
    }

    .table-mobile-md{
        min-width:650px;
    }

    .table-mobile-lg{
        min-width:780px;
    }

    @media (max-width: 1100px){
        .dashboard-summary-grid{
            grid-template-columns:repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 768px){
        .dashboard-kpi-value{
            font-size:1.65rem;
        }

        .dashboard-summary-box{
            padding:12px;
        }

        .dashboard-summary-box strong{
            font-size:1.2rem;
        }

        .card-header{
            padding-bottom:10px;
        }

        .table th,
        .table td{
            font-size:.88rem;
            padding:10px 12px;
        }

        .mini-bars{
            min-height:180px;
            gap:8px;
        }

        .mini-bar{
            max-width:42px;
            border-radius:12px 12px 6px 6px;
            font-size:.72rem;
        }

        .mini-bar-label{
            font-size:.72rem;
        }

        .mini-bar-value{
            font-size:.78rem;
        }

        .dashboard-alert-item{
            padding:12px;
        }
    }

    @media (max-width: 640px){
        .dashboard-summary-grid{
            grid-template-columns:1fr;
        }

        .page-head{
            margin-bottom:14px;
        }

        .page-head h2{
            font-size:1.2rem;
            line-height:1.25;
        }

        .page-head p{
            font-size:.92rem;
            line-height:1.45;
        }

        .dashboard-kpi-value{
            font-size:1.45rem;
        }

        .table th,
        .table td{
            font-size:.84rem;
            padding:9px 10px;
        }

        .table th:first-child,
        .table td:first-child{
            min-width:140px;
        }

        .mini-bars{
            min-height:155px;
            padding-top:10px;
        }

        .mini-bar-item{
            min-width:36px;
            gap:6px;
        }

        .mini-bar{
            max-width:34px;
            padding-top:4px;
        }

        .dashboard-alert-item div{
            word-break:break-word;
        }
    }
</style>

<div class="page-head">
    <div>
        <h2>Dashboard Operacional</h2>
        <p>Acompanhe estoque, previsões de EPI, compras recomendadas e movimentação geral do sistema.</p>
    </div>
</div>

@if($criticalAlerts->count())
    <div class="alert-error-box" style="margin-bottom:18px;">
        <strong>Alertas críticos:</strong>
        <ul style="margin-top:8px; padding-left:18px;">
            @foreach($criticalAlerts as $alert)
                <li>{{ $alert }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="grid grid-4" style="margin-bottom:18px;">
    <div class="card">
        <div class="card-body">
            <div class="text-muted-small">Itens de rota abaixo de 50</div>
            <div class="dashboard-kpi-value">{{ $lowStockRota->count() }}</div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="text-muted-small">Itens de almoxarifado abaixo de 50</div>
            <div class="dashboard-kpi-value">{{ $lowStockAlmoxarifado->count() }}</div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="text-muted-small">Funcionários com EPI previsto no mês</div>
            <div class="dashboard-kpi-value">{{ $employeesDueThisMonth }}</div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="text-muted-small">Itens com risco de falta no EPI</div>
            <div class="dashboard-kpi-value">{{ $epiRiskItems->count() }}</div>
        </div>
    </div>
</div>

<div class="grid grid-1" style="margin-bottom:18px;">
    <div class="card dashboard-highlight">
        <div class="card-header">
            <div class="card-title">Necessidade mínima de EPI no mês</div>
            <div class="card-subtitle">Esse é o quadro principal para planejar reposição, separação e compra de itens.</div>
        </div>

        <div class="card-body">
            @php
                $epiItemsMissingCount = $monthlyEpiNeeds->filter(fn ($item) => $item['difference'] < 0)->count();
                $epiItemsTightCount = $monthlyEpiNeeds->filter(fn ($item) => $item['difference'] >= 0 && $item['difference'] <= 5)->count();
                $epiItemsSafeCount = $monthlyEpiNeeds->filter(fn ($item) => $item['difference'] > 5)->count();
                $totalNeededThisMonth = $monthlyEpiNeeds->sum('needed_quantity');
            @endphp

            <div class="dashboard-summary-grid">
                <div class="dashboard-summary-box">
                    <div class="text-muted-small">Entregas de EPI no mês</div>
                    <strong>{{ $epiDeliveriesThisMonth }}</strong>
                </div>

                <div class="dashboard-summary-box">
                    <div class="text-muted-small">Total previsto de itens</div>
                    <strong>{{ $totalNeededThisMonth }}</strong>
                </div>

                <div class="dashboard-summary-box">
                    <div class="text-muted-small">Itens em falta</div>
                    <strong>{{ $epiItemsMissingCount }}</strong>
                </div>

                <div class="dashboard-summary-box">
                    <div class="text-muted-small">Itens cobertos com folga</div>
                    <strong>{{ $epiItemsSafeCount }}</strong>
                </div>
            </div>

            <div class="table-wrap">
                <table class="table table-mobile-md">
                    <thead>
                        <tr>
                            <th>Produto</th>
                            <th>Variação</th>
                            <th>Necessário</th>
                            <th>Estoque</th>
                            <th>Saldo</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($monthlyEpiNeeds->take(20) as $item)
                            <tr>
                                <td>{{ $item['product_name'] }}</td>
                                <td>{{ $item['variant_name'] ?? '-' }}</td>
                                <td>{{ $item['needed_quantity'] }}</td>
                                <td>{{ $item['current_stock'] }}</td>
                                <td>
                                    @if($item['difference'] < 0)
                                        <span class="badge-status badge-warning">
                                            Falta {{ abs($item['difference']) }}
                                        </span>
                                    @elseif($item['difference'] <= 5)
                                        <span class="badge-status badge-info">
                                            Apertado: sobra {{ $item['difference'] }}
                                        </span>
                                    @else
                                        <span class="badge-status badge-success">
                                            Sobra {{ $item['difference'] }}
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5">Nenhuma entrega prevista para este mês.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="grid grid-2" style="margin-bottom:18px;">
    <div class="card">
        <div class="card-header">
            <div class="card-title">Estoque baixo - Rota</div>
            <div class="card-subtitle">Produtos e variações com estoque real abaixo de 50.</div>
        </div>
        <div class="card-body">
            <div class="table-wrap">
                <table class="table table-mobile-sm">
                    <thead>
                        <tr>
                            <th>Produto</th>
                            <th>Variação</th>
                            <th>Estoque real</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($lowStockRota->take(12) as $item)
                            <tr>
                                <td>{{ $item['product_name'] }}</td>
                                <td>{{ $item['variant_name'] ?? '-' }}</td>
                                <td>{{ $item['stock'] }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3">Nenhum item com estoque baixo na rota.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <div class="card-title">Estoque baixo - Almoxarifado / EPI</div>
            <div class="card-subtitle">Produtos e variações com estoque real abaixo de 50.</div>
        </div>
        <div class="card-body">
            <div class="table-wrap">
                <table class="table table-mobile-sm">
                    <thead>
                        <tr>
                            <th>Produto</th>
                            <th>Variação</th>
                            <th>Estoque real</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($lowStockAlmoxarifado->take(12) as $item)
                            <tr>
                                <td>{{ $item['product_name'] }}</td>
                                <td>{{ $item['variant_name'] ?? '-' }}</td>
                                <td>{{ $item['stock'] }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3">Nenhum item com estoque baixo no almoxarifado.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="grid grid-2" style="margin-bottom:18px;">
    <div class="card">
        <div class="card-header">
            <div class="card-title">Compras recomendadas para o mês</div>
            <div class="card-subtitle">Itens que precisam ser repostos para cobrir as previsões de EPI.</div>
        </div>
        <div class="card-body">
            <div class="table-wrap">
                <table class="table table-mobile-md">
                    <thead>
                        <tr>
                            <th>Produto</th>
                            <th>Variação</th>
                            <th>Comprar</th>
                            <th>Estoque</th>
                            <th>Necessário</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($purchaseRecommendations->take(10) as $item)
                            <tr>
                                <td>{{ $item['product_name'] }}</td>
                                <td>{{ $item['variant_name'] ?? '-' }}</td>
                                <td>
                                    <span class="badge-status badge-warning">{{ $item['buy_quantity'] }}</span>
                                </td>
                                <td>{{ $item['current_stock'] }}</td>
                                <td>{{ $item['needed_quantity'] }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5">Nenhuma compra necessária para cobrir o mês.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <div class="card-title">Próximas entregas de EPI (7 dias)</div>
            <div class="card-subtitle">Funcionários e itens que vencem nos próximos dias.</div>
        </div>
        <div class="card-body">
            <div class="table-wrap">
                <table class="table table-mobile-lg">
                    <thead>
                        <tr>
                            <th>Funcionário</th>
                            <th>Produto</th>
                            <th>Variação</th>
                            <th>Qtd</th>
                            <th>Previsão</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($upcomingEpi7Days->take(10) as $item)
                            <tr>
                                <td>{{ $item['employee_name'] }}</td>
                                <td>{{ $item['product_name'] }}</td>
                                <td>{{ $item['variant_name'] ?? '-' }}</td>
                                <td>{{ $item['quantity'] }}</td>
                                <td>{{ \Carbon\Carbon::parse($item['next_expected_date'])->format('d/m/Y') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5">Nenhuma entrega prevista para os próximos 7 dias.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="grid grid-2" style="margin-bottom:18px;">
    <div class="card">
        <div class="card-header">
            <div class="card-title">Risco de falta para EPI no mês</div>
            <div class="card-subtitle">Itens previstos para este mês com estoque insuficiente.</div>
        </div>
        <div class="card-body">
            <div class="table-wrap">
                <table class="table table-mobile-md">
                    <thead>
                        <tr>
                            <th>Produto</th>
                            <th>Variação</th>
                            <th>Necessário</th>
                            <th>Estoque</th>
                            <th>Falta</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($epiRiskItems->take(10) as $item)
                            <tr>
                                <td>{{ $item['product_name'] }}</td>
                                <td>{{ $item['variant_name'] ?? '-' }}</td>
                                <td>{{ $item['needed_quantity'] }}</td>
                                <td>{{ $item['current_stock'] }}</td>
                                <td>
                                    <span class="badge-status badge-warning">
                                        {{ abs($item['difference']) }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5">Nenhum item com risco de falta no EPI deste mês.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <div class="card-title">Itens zerados</div>
            <div class="card-subtitle">Produtos ou variações sem estoque no momento.</div>
        </div>
        <div class="card-body">
            <div class="table-wrap">
                <table class="table table-mobile-sm">
                    <thead>
                        <tr>
                            <th>Produto</th>
                            <th>Variação</th>
                            <th>Área</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($zeroStockItems->take(10) as $item)
                            <tr>
                                <td>{{ $item['product_name'] }}</td>
                                <td>{{ $item['variant_name'] ?? '-' }}</td>
                                <td>{{ $item['type'] }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3">Nenhum item zerado no momento.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="grid grid-2" style="margin-bottom:18px;">
    <div class="card">
        <div class="card-header">
            <div class="card-title">Top itens de EPI previstos no mês</div>
            <div class="card-subtitle">Itens com maior demanda prevista no período atual.</div>
        </div>
        <div class="card-body">
            <div class="table-wrap">
                <table class="table table-mobile-sm">
                    <thead>
                        <tr>
                            <th>Produto</th>
                            <th>Variação</th>
                            <th>Necessário</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($topEpiItemsThisMonth->take(10) as $item)
                            <tr>
                                <td>{{ $item['product_name'] }}</td>
                                <td>{{ $item['variant_name'] ?? '-' }}</td>
                                <td>{{ $item['needed_quantity'] }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3">Nenhum item previsto para EPI neste mês.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <div class="card-title">Funcionários com EPI atrasado</div>
            <div class="card-subtitle">Quantidade de funcionários com alguma previsão já vencida.</div>
        </div>
        <div class="card-body">
            <div class="dashboard-kpi-value">{{ $employeesWithOverdueEpi }}</div>
            <div class="text-muted-small" style="margin-top:10px;">
                Use esse número para priorizar entregas pendentes e evitar atraso de reposição.
            </div>
        </div>
    </div>
</div>

<div class="grid grid-2" style="margin-bottom:18px;">
    <div class="card">
        <div class="card-header">
            <div class="card-title">Itens mais pedidos nos últimos 7 dias</div>
            <div class="card-subtitle">Ranking por quantidade total pedida.</div>
        </div>
        <div class="card-body">
            <div class="table-wrap">
                <table class="table table-mobile-sm">
                    <thead>
                        <tr>
                            <th>Produto</th>
                            <th>Variação</th>
                            <th>Total 7 dias</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($topOrderedItems->take(10) as $item)
                            <tr>
                                <td>{{ $item['product_name'] }}</td>
                                <td>{{ $item['variant_name'] ?? '-' }}</td>
                                <td>{{ $item['total_7_days'] }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3">Nenhum pedido recente para montar ranking.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <div class="card-title">Top produtos consumidos no mês</div>
            <div class="card-subtitle">Itens mais consumidos no período mensal atual.</div>
        </div>
        <div class="card-body">
            <div class="table-wrap">
                <table class="table table-mobile-sm">
                    <thead>
                        <tr>
                            <th>Produto</th>
                            <th>Variação</th>
                            <th>Total mês</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($topOrderedItemsMonth->take(10) as $item)
                            <tr>
                                <td>{{ $item['product_name'] }}</td>
                                <td>{{ $item['variant_name'] ?? '-' }}</td>
                                <td>{{ $item['total_month'] }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3">Nenhum consumo no mês até agora.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="grid grid-2" style="margin-bottom:18px;">
    <div class="card">
        <div class="card-header">
            <div class="card-title">Previsão de duração do estoque</div>
            <div class="card-subtitle">Com base no consumo médio dos últimos 7 dias.</div>
        </div>
        <div class="card-body">
            <div class="table-wrap">
                <table class="table table-mobile-md">
                    <thead>
                        <tr>
                            <th>Produto</th>
                            <th>Variação</th>
                            <th>Média/dia</th>
                            <th>Dura aprox.</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($stockCoverage->take(10) as $item)
                            <tr>
                                <td>{{ $item['product_name'] }}</td>
                                <td>{{ $item['variant_name'] ?? '-' }}</td>
                                <td>{{ $item['avg_daily'] }}</td>
                                <td>
                                    @if($item['coverage_days'] === null)
                                        Sem consumo recente
                                    @elseif($item['coverage_days'] <= 7)
                                        <span class="badge-status badge-warning">{{ $item['coverage_days'] }} dias</span>
                                    @else
                                        <span class="badge-status badge-success">{{ $item['coverage_days'] }} dias</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4">Ainda não há consumo suficiente para calcular previsão.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <div class="card-title">Mudanças anormais de consumo</div>
            <div class="card-subtitle">Pedidos com quantidade muito acima da média recente do local.</div>
        </div>
        <div class="card-body">
            <div class="dashboard-alert-list">
                @forelse($consumptionAnomalies->take(8) as $alert)
                    <div class="dashboard-alert-item">
                        <div style="font-weight:700;">
                            {{ $alert['location_name'] }} — {{ $alert['product_name'] }}{{ $alert['variant_name'] ? ' / '.$alert['variant_name'] : '' }}
                        </div>
                        <div class="text-muted-small" style="margin-top:6px;">
                            Pedido atual: <strong>{{ $alert['current_qty'] }}</strong> |
                            Média anterior: <strong>{{ $alert['avg_qty'] }}</strong> |
                            Data: <strong>{{ \Carbon\Carbon::parse($alert['created_at'])->format('d/m/Y H:i') }}</strong>
                        </div>
                    </div>
                @empty
                    <div class="text-muted-small">Nenhuma anomalia relevante detectada nos pedidos recentes.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<div class="grid grid-2">
    <div class="card">
        <div class="card-header">
            <div class="card-title">Pedidos por dia (últimos 7 dias)</div>
            <div class="card-subtitle">Quantidade de pedidos por dia.</div>
        </div>
        <div class="card-body">
            @php
                $maxOrdersDay = max(1, $ordersByDay->max('value'));
            @endphp

            <div class="mini-bars">
                @foreach($ordersByDay as $day)
                    @php
                        $height = max(12, ($day['value'] / $maxOrdersDay) * 180);
                    @endphp
                    <div class="mini-bar-item">
                        <div class="mini-bar-value">{{ $day['value'] }}</div>
                        <div class="mini-bar" style="height: {{ $height }}px;"></div>
                        <div class="mini-bar-label">{{ $day['label'] }}</div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <div class="card-title">Itens saídos por dia (últimos 7 dias)</div>
            <div class="card-subtitle">Volume total de itens movimentados por pedidos.</div>
        </div>
        <div class="card-body">
            @php
                $maxItemsDay = max(1, $itemsOutByDay->max('value'));
            @endphp

            <div class="mini-bars">
                @foreach($itemsOutByDay as $day)
                    @php
                        $height = max(12, ($day['value'] / $maxItemsDay) * 180);
                    @endphp
                    <div class="mini-bar-item">
                        <div class="mini-bar-value">{{ $day['value'] }}</div>
                        <div class="mini-bar secondary" style="height: {{ $height }}px;"></div>
                        <div class="mini-bar-label">{{ $day['label'] }}</div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection