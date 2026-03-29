@extends('layouts.app')

@section('title', 'Histórico de EPI')

@section('content')
<div class="page-head">
    <div>
        <h2>Histórico de EPI</h2>
        <p>Visualize todo o histórico de recebimentos do funcionário.</p>
    </div>

    <div class="actions-inline">
        <a href="{{ route('epi.index') }}" class="btn btn-dark">
            Voltar
        </a>

        <a href="{{ route('epi-deliveries.create') }}?employee_id={{ $employee->id }}" class="btn btn-green">
            + Nova Entrega
        </a>
    </div>
</div>

<div class="card" style="margin-bottom:18px;">
    <div class="card-header">
        <div class="card-title">{{ $employee->name }}</div>
        <div class="card-subtitle">Resumo do funcionário e situação de recebimento</div>
    </div>

    <div class="card-body">
        <div class="form-grid">
            <div class="form-group">
                <label class="form-label">Nome</label>
                <input type="text" class="form-control-custom" value="{{ $employee->name }}" readonly>
            </div>

            <div class="form-group">
                <label class="form-label">Cargo</label>
                <input
                    type="text"
                    class="form-control-custom"
                    value="{{ $employee->cargo->nome ?? $employee->cargo->name ?? $employee->cargo->descricao ?? $employee->cargo->cargo ?? '-' }}"
                    readonly
                >
            </div>

            <div class="form-group">
                <label class="form-label">CPF</label>
                <input type="text" class="form-control-custom" value="{{ $employee->cpf }}" readonly>
            </div>

            <div class="form-group">
                <label class="form-label">Matrícula</label>
                <input type="text" class="form-control-custom" value="{{ $employee->registration }}" readonly>
            </div>

            <div class="form-group">
                <label class="form-label">Data de contratação</label>
                <input
                    type="text"
                    class="form-control-custom"
                    value="{{ optional($employee->hired_at)->format('d/m/Y') }}"
                    readonly
                >
            </div>

            <div class="form-group">
                <label class="form-label">Último recebimento</label>
                <input
                    type="text"
                    class="form-control-custom"
                    value="{{ $lastDeliveryDate ? \Carbon\Carbon::parse($lastDeliveryDate)->format('d/m/Y') : '-' }}"
                    readonly
                >
            </div>

            <div class="form-group form-group-full">
                <label class="form-label">Próximo item previsto</label>
                <input
                    type="text"
                    class="form-control-custom"
                    value="{{ $nextItem ? (($nextItem->product->name ?? '-') . (($nextItem->variant && $nextItem->variant->name) ? ' - '.$nextItem->variant->name : '') . ' | Próxima previsão: ' . \Carbon\Carbon::parse($nextItem->next_expected_date)->format('d/m/Y')) : '-' }}"
                    readonly
                >
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <div class="card-title">Histórico completo ({{ $historyItems->count() }})</div>
        <div class="card-subtitle">Todas as entregas registradas para este funcionário</div>
    </div>

    <div class="card-body">
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>Data da entrega</th>
                        <th>Produto</th>
                        <th>Variação</th>
                        <th>Quantidade</th>
                        <th>Próxima previsão</th>
                        <th>Registrado por</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($historyItems as $item)
                        <tr>
                            <td>
                                {{ $item->delivery_date ? \Carbon\Carbon::parse($item->delivery_date)->format('d/m/Y') : '-' }}
                            </td>

                            <td>
                                {{ $item->product->name ?? '-' }}
                            </td>

                            <td>
                                {{ $item->variant->name ?? '-' }}
                            </td>

                            <td>
                                {{ $item->quantity }}
                            </td>

                            <td>
                                {{ $item->next_expected_date ? \Carbon\Carbon::parse($item->next_expected_date)->format('d/m/Y') : '-' }}
                            </td>

                            <td>
                                {{ $item->registered_by }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">Nenhum histórico de entrega encontrado.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection