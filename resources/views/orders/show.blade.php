@extends('layouts.app')

@section('title', 'Detalhes do Pedido')

@section('content')
<div class="page-head">
    <div>
        <h2>Pedido #{{ $order->id }}</h2>
        <p>Visualize os detalhes completos do pedido.</p>
    </div>

    <div class="actions-inline">
        <a href="{{ route('orders.index') }}" class="btn btn-dark">Voltar</a>
        <a href="{{ route('orders.edit', $order) }}" class="btn btn-warning-soft">Editar</a>
    </div>
</div>

<div class="grid grid-2">
    <div class="card">
        <div class="card-header">
            <div class="card-title">Informações do pedido</div>
            <div class="card-subtitle">Dados principais do pedido registrado.</div>
        </div>

        <div class="card-body">
            <div class="simple-list">
                <li>
                    <div>
                        <strong>Número</strong>
                        <small>#{{ $order->id }}</small>
                    </div>
                </li>

                <li>
                    <div>
                        <strong>Tipo</strong>
                        <small>
                            @if(($order->location->scope ?? null) === 'almoxarifado')
                                Almoxarifado
                            @else
                                Rota
                            @endif
                        </small>
                    </div>
                </li>

                <li>
                    <div>
                        <strong>Local</strong>
                        <small>{{ $order->location->name ?? '-' }}</small>
                    </div>
                </li>

                <li>
                    <div>
                        <strong>Rota</strong>
                        <small>{{ $order->route->name ?? ($order->location->route->name ?? '-') }}</small>
                    </div>
                </li>

                <li>
                    <div>
                        <strong>Usuário</strong>
                        <small>{{ $order->user->name ?? '-' }}</small>
                    </div>
                </li>

                <li>
                    <div>
                        <strong>Data do pedido</strong>
                        <small>{{ optional($order->order_date)->format('d/m/Y') }}</small>
                    </div>
                </li>

                <li>
                    <div>
                        <strong>Status</strong>
                        <small>{{ $order->status ?? '-' }}</small>
                    </div>
                </li>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <div class="card-title">Resumo</div>
            <div class="card-subtitle">Quantidade de itens e unidades do pedido.</div>
        </div>

        <div class="card-body">
            <div class="grid grid-2">
                <div class="kpi-card card" style="padding:18px;">
                    <div class="kpi-label">Itens diferentes</div>
                    <div class="kpi-value">{{ $order->items->count() }}</div>
                </div>

                <div class="kpi-card card" style="padding:18px;">
                    <div class="kpi-label">Total de unidades</div>
                    <div class="kpi-value">
                        {{ rtrim(rtrim(number_format((float) $order->items->sum('quantity'), 3, '.', ''), '0'), '.') }}
                    </div>
                </div>
            </div>

            @if(!empty($order->notes))
                <div class="section-spacer"></div>

                <div class="card" style="padding:16px;">
                    <div class="card-title" style="margin-bottom:8px;">Observações</div>
                    <div class="text-muted-small">{{ $order->notes }}</div>
                </div>
            @endif
        </div>
    </div>
</div>

<div class="section-spacer"></div>

<div class="card">
    <div class="card-header">
        <div class="card-title">Itens do pedido</div>
        <div class="card-subtitle">Produtos e variações incluídos neste pedido.</div>
    </div>

    <div class="card-body">
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>Produto</th>
                        <th>Variação</th>
                        <th>Quantidade</th>
                        <th>Unidade</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($order->items as $item)
                        <tr>
                            <td>
                                {{ $item->product_name_snapshot ?: ($item->product->name ?? '-') }}
                            </td>
                            <td>
                                {{ $item->variant->name ?? '-' }}
                            </td>
                            <td>
                                {{ rtrim(rtrim(number_format((float) $item->quantity, 3, '.', ''), '0'), '.') }}
                            </td>
                            <td>
                                {{ $item->unit_snapshot ?? ($item->product->unit->name ?? '-') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" style="text-align:center;">Nenhum item encontrado.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection