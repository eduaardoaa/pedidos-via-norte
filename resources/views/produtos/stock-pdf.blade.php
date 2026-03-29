<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Relatório de Estoque</title>
    <style>
        * {
            box-sizing: border-box;
            font-family: DejaVu Sans, sans-serif;
        }

        body {
            font-size: 12px;
            color: #111827;
            margin: 0;
            padding: 24px;
        }

        .header {
            margin-bottom: 18px;
            border-bottom: 1px solid #d1d5db;
            padding-bottom: 12px;
        }

        .header h1 {
            margin: 0 0 6px 0;
            font-size: 20px;
        }

        .header p {
            margin: 3px 0;
            color: #4b5563;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 16px;
        }

        .table th,
        .table td {
            border: 1px solid #d1d5db;
            padding: 8px;
            vertical-align: top;
            text-align: left;
        }

        .table th {
            background: #f3f4f6;
            font-weight: bold;
        }

        .muted {
            color: #6b7280;
            font-size: 11px;
        }

        .badge {
            display: inline-block;
            padding: 3px 7px;
            border-radius: 8px;
            font-size: 10px;
            background: #eef2ff;
            color: #1e3a8a;
            margin-right: 4px;
            margin-bottom: 4px;
        }

        .variant-list {
            margin: 0;
            padding-left: 16px;
        }

        .variant-list li {
            margin-bottom: 4px;
        }

        .empty {
            margin-top: 20px;
            padding: 14px;
            border: 1px dashed #9ca3af;
            color: #6b7280;
        }

        .footer {
            margin-top: 18px;
            font-size: 10px;
            color: #6b7280;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Relatório de Estoque de Produtos</h1>

        <p><strong>Gerado em:</strong> {{ $generatedAt->format('d/m/Y H:i') }}</p>

        <p>
            <strong>Filtro de local:</strong>
            @if($locationFilter === 'rota')
                Rota
            @elseif($locationFilter === 'almoxarifado')
                Almoxarifado
            @else
                Todos
            @endif
        </p>

        <p>
            <strong>Busca:</strong>
            {{ $search !== '' ? $search : 'Sem filtro de busca' }}
        </p>

        <p>
            <strong>Total de produtos:</strong>
            {{ $products->count() }}
        </p>
    </div>

    @if($products->isEmpty())
        <div class="empty">
            Nenhum produto encontrado para os filtros informados.
        </div>
    @else
        <table class="table">
            <thead>
                <tr>
                    <th style="width: 6%;">ID</th>
                    <th style="width: 22%;">Produto</th>
                    <th style="width: 14%;">SKU</th>
                    <th style="width: 12%;">Unidade</th>
                    <th style="width: 16%;">Locais</th>
                    <th style="width: 10%;">Tipo</th>
                    <th style="width: 20%;">Estoque</th>
                </tr>
            </thead>
            <tbody>
                @foreach($products as $product)
                    <tr>
                        <td>{{ $product->id }}</td>

                        <td>
                            <strong>{{ $product->name }}</strong>

                            @if($product->description)
                                <div class="muted" style="margin-top:4px;">
                                    {{ $product->description }}
                                </div>
                            @endif
                        </td>

                        <td>{{ $product->sku ?: '-' }}</td>

                        <td>
                            {{ $product->unit?->name ?? '-' }}
                            @if($product->unit?->abbreviation)
                                <div class="muted">{{ $product->unit->abbreviation }}</div>
                            @endif
                        </td>

                        <td>
                            @forelse($product->locations as $location)
                                <span class="badge">{{ $location->name }}</span>
                            @empty
                                <span class="muted">Sem local</span>
                            @endforelse
                        </td>

                        <td>
                            @if($product->uses_variants)
                                Com variações
                            @else
                                Simples
                            @endif
                        </td>

                        <td>
                            @if($product->uses_variants)
                                @if($product->variants->count())
                                    <ul class="variant-list">
                                        @foreach($product->variants as $variant)
                                            <li>
                                                <strong>{{ $variant->name }}</strong>
                                                @if($variant->sku)
                                                    <span class="muted">({{ $variant->sku }})</span>
                                                @endif
                                                - {{ $variant->formatted_stock }}
                                            </li>
                                        @endforeach
                                    </ul>
                                @else
                                    <span class="muted">Sem variações cadastradas</span>
                                @endif
                            @else
                                {{ $product->total_stock }}
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <div class="footer">
        Relatório gerado com base nos filtros aplicados na tela de produtos.
    </div>
</body>
</html>