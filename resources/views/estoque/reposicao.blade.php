@extends('layouts.app')

@section('title', 'Reposição de Estoque')

@section('content')
<div class="page-head">
    <div>
        <h2>Reposição de Estoque</h2>
        <p>Selecione o local, informe as quantidades recebidas e veja como o estoque ficará.</p>
    </div>

    <div class="actions-inline">
        <a href="{{ route('products.index') }}" class="btn btn-dark">
            Voltar para Produtos
        </a>
    </div>
</div>

@if(session('success'))
    <div class="alert-success-box">
        {{ session('success') }}
    </div>
@endif

@if($errors->any())
    <div class="alert-error-box">
        <strong>Corrija os erros abaixo:</strong>
        <ul style="margin-top:8px; padding-left:18px;">
            @foreach($errors->all() as $erro)
                <li>{{ $erro }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="card" style="margin-bottom:18px;">
    <div class="card-header">
        <div class="card-title">Selecionar Local</div>
        <div class="card-subtitle">Escolha para qual local você quer lançar a reposição.</div>
    </div>

    <div class="card-body">
        <form method="GET" action="{{ route('stock-replenishment.index') }}">
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Local</label>
                    <select name="stock_location_id" class="form-control-custom" required>
                        <option value="">Selecione</option>
                        @foreach($locations as $location)
                            <option value="{{ $location->id }}" @selected((string)$locationId === (string)$location->id)>
                                {{ $location->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Pesquisar produto</label>
                    <input
                        type="text"
                        name="search"
                        class="form-control-custom"
                        value="{{ $search ?? '' }}"
                        placeholder="Nome ou SKU"
                    >
                </div>

                <div class="form-group form-group-full">
                    <div class="actions-inline">
                        <button type="submit" class="btn btn-green">
                            Carregar Produtos
                        </button>
                        <a href="{{ route('stock-replenishment.index') }}" class="btn btn-dark">
                            Limpar
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

@if($locationId)
    <div class="card">
        <div class="card-header">
            <div class="card-title">Lançar Reposição</div>
            <div class="card-subtitle">Preencha apenas os itens que realmente receberam reposição.</div>
        </div>

        <div class="card-body">
            <form method="POST" action="{{ route('stock-replenishment.store') }}">
                @csrf

                <input type="hidden" name="stock_location_id" value="{{ $locationId }}">

                <div class="form-grid" style="margin-bottom:18px;">
                    <div class="form-group">
                        <label class="form-label">Data da reposição</label>
                        <input type="date" name="movement_date" class="form-control-custom" value="{{ old('movement_date', now()->toDateString()) }}" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Documento</label>
                        <input type="text" name="document_number" class="form-control-custom" value="{{ old('document_number') }}" placeholder="NF, recibo, pedido...">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Fornecedor / Origem</label>
                        <input type="text" name="source_name" class="form-control-custom" value="{{ old('source_name') }}">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Observação geral</label>
                        <input type="text" name="notes" class="form-control-custom" value="{{ old('notes') }}">
                    </div>
                </div>

                <div class="table-wrap">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Produto</th>
                                <th>Variação</th>
                                <th>Unidade</th>
                                <th>Estoque Atual</th>
                                <th>Qtd. que chegou</th>
                                <th>Estoque Final</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $rowIndex = 0; @endphp

                            @forelse($products as $product)
                                @if($product->uses_variants)
                                    @foreach($product->variants as $variant)
                                        @php $currentStock = (float) $variant->current_stock; @endphp
                                        <tr>
                                            <td>
                                                {{ $product->name }}
                                                <input type="hidden" name="items[{{ $rowIndex }}][product_id]" value="{{ $product->id }}">
                                                <input type="hidden" name="items[{{ $rowIndex }}][product_variant_id]" value="{{ $variant->id }}">
                                            </td>
                                            <td>{{ $variant->name }}</td>
                                            <td>{{ $product->unit?->abbreviation ?? '-' }}</td>
                                            <td class="current-stock" data-current="{{ $currentStock }}">{{ $variant->formatted_stock }}</td>
                                            <td>
                                                <input
                                                    type="number"
                                                    step="0.001"
                                                    min="0"
                                                    name="items[{{ $rowIndex }}][quantity]"
                                                    class="form-control-custom quantity-input"
                                                    value="{{ old("items.$rowIndex.quantity") }}"
                                                    placeholder="0"
                                                >
                                            </td>
                                            <td class="final-stock">{{ $variant->formatted_stock }}</td>
                                        </tr>
                                        @php $rowIndex++; @endphp
                                    @endforeach
                                @else
                                    @php $currentStock = (float) $product->current_stock; @endphp
                                    <tr>
                                        <td>
                                            {{ $product->name }}
                                            <input type="hidden" name="items[{{ $rowIndex }}][product_id]" value="{{ $product->id }}">
                                        </td>
                                        <td>-</td>
                                        <td>{{ $product->unit?->abbreviation ?? '-' }}</td>
                                        <td class="current-stock" data-current="{{ $currentStock }}">{{ $product->total_stock }}</td>
                                        <td>
                                            <input
                                                type="number"
                                                step="0.001"
                                                min="0"
                                                name="items[{{ $rowIndex }}][quantity]"
                                                class="form-control-custom quantity-input"
                                                value="{{ old("items.$rowIndex.quantity") }}"
                                                placeholder="0"
                                            >
                                        </td>
                                        <td class="final-stock">{{ $product->total_stock }}</td>
                                    </tr>
                                    @php $rowIndex++; @endphp
                                @endif
                            @empty
                                <tr>
                                    <td colspan="6">Nenhum produto encontrado para esse local.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div style="margin-top:18px;">
                    <div class="actions-inline">
                        <button type="submit" class="btn btn-green">
                            Salvar Reposição
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endif

<script>
document.addEventListener('DOMContentLoaded', function () {
    function formatStock(value) {
        const number = parseFloat(value || 0);
        if (Number.isInteger(number)) return String(number);
        return String(number.toFixed(3)).replace(/\.?0+$/, '');
    }

    document.querySelectorAll('tbody tr').forEach(function (row) {
        const currentCell = row.querySelector('.current-stock');
        const quantityInput = row.querySelector('.quantity-input');
        const finalCell = row.querySelector('.final-stock');

        if (!currentCell || !quantityInput || !finalCell) return;

        const currentStock = parseFloat(currentCell.dataset.current || 0);

        function updateFinalStock() {
            const quantity = parseFloat(quantityInput.value || 0);
            finalCell.textContent = formatStock(currentStock + quantity);

            if (quantity > 0) {
                row.style.background = 'rgba(25,135,84,.08)';
            } else {
                row.style.background = '';
            }
        }

        quantityInput.addEventListener('input', updateFinalStock);
        updateFinalStock();
    });
});
</script>
@endsection