<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Relatório de Visitas</title>
    <style>
        body{
            font-family: helvetica, sans-serif;
            font-size: 10px;
            color: #111827;
        }

        .title{
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 4px;
        }

        .subtitle{
            font-size: 10px;
            color: #4b5563;
            margin-bottom: 12px;
        }

        .section{
            margin-top: 18px;
        }

        .section-title{
            font-size: 13px;
            font-weight: bold;
            margin-bottom: 8px;
            padding: 6px 8px;
            background: #e5e7eb;
        }

        .meta-table,
        .data-table{
            width: 100%;
            border-collapse: collapse;
        }

        .meta-table td{
            border: 1px solid #d1d5db;
            padding: 7px;
            vertical-align: top;
        }

        .data-table th,
        .data-table td{
            border: 1px solid #d1d5db;
            padding: 7px;
            vertical-align: top;
        }

        .data-table th{
            background: #f3f4f6;
            font-weight: bold;
            text-align: left;
        }

        .small{
            font-size: 9px;
            color: #6b7280;
        }

        .strong{
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="title">Relatório de Visitas do Cabo de Turma</div>
    <div class="subtitle">
        Gerado em {{ $generatedAt }}
    </div>

    <table class="meta-table">
        <tr>
            <td><span class="strong">Funcionário:</span> {{ $user->name }}</td>
            <td><span class="strong">Mês de referência:</span> {{ $monthlyReport['reference_month'] }}</td>
        </tr>
        <tr>
            <td><span class="strong">Filtro por data:</span> {{ $filters['date'] ?: 'Não aplicado' }}</td>
            <td><span class="strong">Filtro por mês:</span> {{ $filters['month'] ?: 'Não aplicado' }}</td>
        </tr>
    </table>

    <div class="section">
        <div class="section-title">Resumo mensal</div>

        <table class="meta-table">
            <tr>
                <td><span class="strong">Total de visitas:</span> {{ $monthlyReport['total_visits'] }}</td>
                <td><span class="strong">Locais diferentes:</span> {{ $monthlyReport['unique_locations'] }}</td>
            </tr>
            <tr>
                <td><span class="strong">Dias com visitas:</span> {{ $monthlyReport['visited_days'] }}</td>
                <td>
                    <span class="strong">Local mais visitado:</span>
                    {{ $monthlyReport['most_visited_location']['location_name'] ?? 'Nenhum local' }}
                    ({{ $monthlyReport['most_visited_location']['total_visits'] ?? 0 }})
                </td>
            </tr>
            <tr>
                <td><span class="strong">Primeira visita do mês:</span> {{ $monthlyReport['first_visit'] }}</td>
                <td><span class="strong">Última visita do mês:</span> {{ $monthlyReport['last_visit'] }}</td>
            </tr>
        </table>
    </div>

    <div class="section">
        <div class="section-title">Quantidade de visitas por local no mês</div>

        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 28%;">Local</th>
                    <th style="width: 16%;">Rota</th>
                    <th style="width: 30%;">Endereço</th>
                    <th style="width: 10%;">Total</th>
                    <th style="width: 16%;">Última visita</th>
                </tr>
            </thead>
            <tbody>
                @forelse($monthlyLocationSummary as $summary)
                    <tr>
                        <td>{{ $summary['location_name'] }}</td>
                        <td>{{ $summary['route_name'] ?: 'Sem rota' }}</td>
                        <td>{{ $summary['address'] ?: 'Não informado' }}</td>
                        <td>{{ $summary['total_visits'] }}</td>
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

    <div class="section">
        <div class="section-title">Histórico detalhado</div>

        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 15%;">Data</th>
                    <th style="width: 10%;">Hora</th>
                    <th style="width: 18%;">Rota</th>
                    <th style="width: 22%;">Local</th>
                    <th style="width: 20%;">Endereço</th>
                    <th style="width: 15%;">O que foi feito</th>
                </tr>
            </thead>
            <tbody>
                @forelse($visits as $visit)
                    <tr>
                        <td>{{ $visit->visited_at?->format('d/m/Y') ?: '-' }}</td>
                        <td>{{ $visit->visited_at?->format('H:i:s') ?: '-' }}</td>
                        <td>{{ $visit->location?->route?->name ?: 'Sem rota' }}</td>
                        <td>{{ $visit->location?->name ?: ($visit->display_name ?: 'Local não identificado') }}</td>
                        <td>{{ $visit->address ?: 'Não informado' }}</td>
                        <td>{{ $visit->service_report ?: 'Nenhum relato informado.' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">Nenhuma visita encontrada para os filtros informados.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</body>
</html>