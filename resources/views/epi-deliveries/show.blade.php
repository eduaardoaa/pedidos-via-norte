@extends('layouts.app')

@section('title', 'Detalhes da Entrega de EPI')

@section('content')
<div class="page-head">
    <div>
        <h2>Detalhes da Entrega</h2>
        <p>Visualize os dados completos da entrega registrada.</p>
    </div>

    <div class="actions-inline">
        <a href="{{ route('epi-deliveries.index') }}" class="btn btn-dark">
            Voltar
        </a>
    </div>
</div>

<div class="card" style="margin-bottom:16px;">
    <div class="card-body">
        <div style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px;">
            <div>
                <strong>Funcionário:</strong><br>
                {{ $epiDelivery->employee->name ?? '-' }}
            </div>

            <div>
                <strong>Cargo:</strong><br>
                {{ $epiDelivery->employee->cargo->name ?? '-' }}
            </div>

            <div>
                <strong>Data da entrega:</strong><br>
                {{ optional($epiDelivery->delivery_date)->format('d/m/Y') }}
            </div>

            <div>
                <strong>Registrado por:</strong><br>
                {{ $epiDelivery->user->name ?? '-' }}
            </div>

            <div style="grid-column: span 2;">
                <strong>Observações:</strong><br>
                {{ $epiDelivery->notes ?: 'Nenhuma observação.' }}
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body" style="overflow-x:auto;">
        <table class="table">
            <thead>
                <tr>
                    <th>Produto</th>
                    <th>Variação</th>
                    <th>Quantidade</th>
                    <th>Próxima previsão</th>
                </tr>
            </thead>
            <tbody>
                @forelse($epiDelivery->items as $item)
                    <tr>
                        <td>{{ $item->product->name ?? '-' }}</td>
                        <td>{{ $item->variant->name ?? '-' }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>{{ optional($item->next_expected_date)->format('d/m/Y') ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" style="text-align:center;padding:24px;">
                            Nenhum item nesta entrega.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection