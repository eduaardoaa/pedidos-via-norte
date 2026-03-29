@extends('layouts.app')

@section('title', 'Nova Entrega de EPI')

@section('content')
@php
    $productsJson = $products->map(function ($product) {
        return [
            'id' => $product->id,
            'name' => $product->name,
            'uses_variants' => (bool) ($product->uses_variants ?? false),
            'variants' => $product->variants->map(function ($variant) {
                return [
                    'id' => $variant->id,
                    'name' => $variant->name,
                ];
            })->values()->toArray(),
        ];
    })->values()->toArray();
@endphp

<div class="page-head">
    <div>
        <h2>Nova Entrega de EPI</h2>
        <p>Registre os itens entregues ao funcionário com previsão da próxima entrega.</p>
    </div>

    <div class="actions-inline">
        <a href="{{ route('epi.index') }}" class="btn btn-dark">Voltar</a>
    </div>
</div>

@if($errors->any())
    <div class="alert-error-box">
        <ul style="margin:0;padding-left:18px;">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form action="{{ route('epi-deliveries.store') }}" method="POST" class="card" id="formEntregaEpi">
    @csrf

    <div class="card-body">
        <div class="form-grid form-grid-epi-top">
            <div class="form-group">
                <label class="form-label" for="employee_id">Funcionário</label>
                <select name="employee_id" id="employee_id" class="form-control-custom" required>
                    <option value="">Selecione</option>
                    @foreach($employees as $employee)
                        <option
                            value="{{ $employee->id }}"
                            {{ (string) old('employee_id', $selectedEmployeeId ?? '') === (string) $employee->id ? 'selected' : '' }}
                        >
                            {{ $employee->name }}{{ $employee->cargo ? ' - ' . ($employee->cargo->nome ?? $employee->cargo->name ?? $employee->cargo->descricao ?? $employee->cargo->cargo ?? '') : '' }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label class="form-label" for="delivery_date">Data da entrega</label>
                <input
                    type="date"
                    name="delivery_date"
                    id="delivery_date"
                    class="form-control-custom"
                    value="{{ old('delivery_date', now()->format('Y-m-d')) }}"
                    required
                >
            </div>
        </div>

        <div class="section-spacer"></div>

        <div class="page-head" style="margin-bottom:14px;">
            <div>
                <h2 style="font-size:1.05rem;">Itens da entrega</h2>
                <p>Selecione os EPIs entregues e defina a próxima previsão de recebimento.</p>
            </div>

            <div class="actions-inline">
                <button type="button" class="btn btn-green" id="btnAdicionarItem">
                    + Adicionar Item
                </button>
            </div>
        </div>

        <div id="itens-wrapper" style="display:flex;flex-direction:column;gap:12px;"></div>

        <div class="section-spacer"></div>

        <div class="form-grid">
            <div class="form-group form-group-full">
                <label class="form-label" for="notes">Observações</label>
                <textarea
                    name="notes"
                    id="notes"
                    class="form-control-custom"
                    rows="4"
                    placeholder="Observações opcionais sobre a entrega"
                >{{ old('notes') }}</textarea>
            </div>
        </div>

        <div class="actions-inline" style="margin-top:18px;">
            <button type="submit" class="btn btn-green">
                Salvar Entrega
            </button>
            <a href="{{ route('epi.index') }}" class="btn btn-dark">Cancelar</a>
        </div>
    </div>
</form>

<style>
    .form-grid-epi-top{
        grid-template-columns:repeat(2, minmax(0, 1fr));
    }

    .epi-item-grid{
        display:grid;
        grid-template-columns:2fr 1.3fr 1fr 1.2fr 1.2fr auto;
        gap:12px;
        align-items:end;
    }

    .custom-date-wrapper{
        display:none;
    }

    .custom-date-wrapper.show{
        display:block;
    }

    @media (max-width: 1200px){
        .epi-item-grid{
            grid-template-columns:repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 720px){
        .form-grid-epi-top,
        .epi-item-grid{
            grid-template-columns:1fr;
        }
    }
</style>

<script>
    const epiProducts = @json($productsJson);
    const itensWrapper = document.getElementById('itens-wrapper');
    const btnAdicionarItem = document.getElementById('btnAdicionarItem');
    const deliveryDateInput = document.getElementById('delivery_date');
    let epiItemIndex = 0;

    function getProductById(productId) {
        return epiProducts.find(p => String(p.id) === String(productId));
    }

    function buildProductOptions(selectedId = '') {
        let html = '<option value="">Selecione</option>';

        epiProducts.forEach(product => {
            const selected = String(selectedId) === String(product.id) ? 'selected' : '';
            html += `<option value="${product.id}" ${selected}>${product.name}</option>`;
        });

        return html;
    }

    function buildVariantOptions(productId, selectedVariantId = '') {
        const product = getProductById(productId);

        if (!product || !product.variants || product.variants.length === 0) {
            return '<option value="">Sem variação</option>';
        }

        let html = '<option value="">Selecione</option>';

        product.variants.forEach(variant => {
            const selected = String(selectedVariantId) === String(variant.id) ? 'selected' : '';
            html += `<option value="${variant.id}" ${selected}>${variant.name}</option>`;
        });

        return html;
    }

    function updateVariantRequirement(productSelect, variantSelect, variantLabel) {
        const product = getProductById(productSelect.value);
        const hasVariants = !!(product && product.variants && product.variants.length);

        if (hasVariants) {
            variantSelect.setAttribute('required', 'required');
            variantLabel.innerHTML = 'Variação <span style="color:#fca5a5;">*</span>';
        } else {
            variantSelect.removeAttribute('required');
            variantLabel.textContent = 'Variação';
            variantSelect.value = '';
        }
    }

    function addMonthsToDate(dateString, monthsToAdd) {
        if (!dateString || !monthsToAdd) return '';

        const originalDate = new Date(dateString + 'T00:00:00');
        if (isNaN(originalDate.getTime())) return '';

        const year = originalDate.getFullYear();
        const month = originalDate.getMonth();
        const day = originalDate.getDate();

        const result = new Date(year, month + Number(monthsToAdd), day);

        if (result.getDate() !== day) {
            result.setDate(0);
        }

        const yyyy = result.getFullYear();
        const mm = String(result.getMonth() + 1).padStart(2, '0');
        const dd = String(result.getDate()).padStart(2, '0');

        return `${yyyy}-${mm}-${dd}`;
    }

    function updateNextExpectedDate(row) {
        const periodSelect = row.querySelector('.period-select');
        const customDateWrapper = row.querySelector('.custom-date-wrapper');
        const nextDateInput = row.querySelector('.next-date-input');
        const deliveryDate = deliveryDateInput.value;

        if (periodSelect.value === 'custom') {
            customDateWrapper.classList.add('show');
            nextDateInput.removeAttribute('readonly');
            nextDateInput.setAttribute('required', 'required');
            return;
        }

        customDateWrapper.classList.remove('show');
        nextDateInput.removeAttribute('required');
        nextDateInput.setAttribute('readonly', 'readonly');

        if (periodSelect.value) {
            nextDateInput.value = addMonthsToDate(deliveryDate, periodSelect.value);
        } else {
            nextDateInput.value = '';
        }
    }

    function addItemRow(data = {}) {
        const index = epiItemIndex++;
        const row = document.createElement('div');
        row.className = 'card';
        row.style.padding = '16px';

        const selectedPeriod = data.next_expected_period ?? (
            data.next_expected_date ? 'custom' : ''
        );

        row.innerHTML = `
            <div class="actions-inline" style="justify-content:space-between; align-items:center; margin-bottom:12px;">
                <div>
                    <strong>Item da entrega</strong>
                    <div class="text-muted-small">Selecione o produto, variação, quantidade e próxima previsão.</div>
                </div>

                <button type="button" class="btn btn-danger-soft btn-remover-item">
                    Remover
                </button>
            </div>

            <div class="epi-item-grid">
                <div class="form-group">
                    <label class="form-label">Produto</label>
                    <select name="items[${index}][product_id]" class="form-control-custom product-select" required>
                        ${buildProductOptions(data.product_id ?? '')}
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label variant-label">Variação</label>
                    <select name="items[${index}][product_variant_id]" class="form-control-custom variant-select">
                        <option value="">Sem variação</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Quantidade</label>
                    <input
                        type="number"
                        min="1"
                        step="1"
                        name="items[${index}][quantity]"
                        class="form-control-custom"
                        value="${data.quantity ?? 1}"
                        required
                    >
                </div>

                <div class="form-group">
                    <label class="form-label">Entregar novamente</label>
                    <select name="items[${index}][next_expected_period]" class="form-control-custom period-select">
                        <option value="">Selecione</option>
                        <option value="1" ${selectedPeriod === '1' ? 'selected' : ''}>1 mês</option>
                        <option value="3" ${selectedPeriod === '3' ? 'selected' : ''}>3 meses</option>
                        <option value="6" ${selectedPeriod === '6' ? 'selected' : ''}>6 meses</option>
                        <option value="9" ${selectedPeriod === '9' ? 'selected' : ''}>9 meses</option>
                        <option value="12" ${selectedPeriod === '12' ? 'selected' : ''}>1 ano</option>
                        <option value="custom" ${selectedPeriod === 'custom' ? 'selected' : ''}>Personalizado</option>
                    </select>
                </div>

                <div class="form-group custom-date-wrapper">
                    <label class="form-label">Data personalizada</label>
                    <input
                        type="date"
                        name="items[${index}][next_expected_date]"
                        class="form-control-custom next-date-input"
                        value="${data.next_expected_date ?? ''}"
                        readonly
                    >
                </div>

                <div class="form-group">
                    <button type="button" class="btn btn-dark btn-limpar-item" style="width:100%;">
                        Limpar
                    </button>
                </div>
            </div>
        `;

        itensWrapper.appendChild(row);

        const productSelect = row.querySelector('.product-select');
        const variantSelect = row.querySelector('.variant-select');
        const variantLabel = row.querySelector('.variant-label');
        const removeBtn = row.querySelector('.btn-remover-item');
        const clearBtn = row.querySelector('.btn-limpar-item');
        const quantityInput = row.querySelector(`input[name="items[${index}][quantity]"]`);
        const periodSelect = row.querySelector('.period-select');
        const nextDateInput = row.querySelector('.next-date-input');
        const customDateWrapper = row.querySelector('.custom-date-wrapper');

        function refreshVariants(selectedVariantId = '') {
            variantSelect.innerHTML = buildVariantOptions(productSelect.value, selectedVariantId);
            updateVariantRequirement(productSelect, variantSelect, variantLabel);
        }

        refreshVariants(data.product_variant_id ?? '');
        updateNextExpectedDate(row);

        productSelect.addEventListener('change', () => {
            refreshVariants('');
        });

        periodSelect.addEventListener('change', () => {
            if (periodSelect.value !== 'custom') {
                nextDateInput.value = '';
            }
            updateNextExpectedDate(row);
        });

        removeBtn.addEventListener('click', () => {
            row.remove();
        });

        clearBtn.addEventListener('click', () => {
            productSelect.value = '';
            variantSelect.innerHTML = '<option value="">Sem variação</option>';
            variantSelect.removeAttribute('required');
            variantLabel.textContent = 'Variação';
            quantityInput.value = 1;
            periodSelect.value = '';
            nextDateInput.value = '';
            nextDateInput.setAttribute('readonly', 'readonly');
            nextDateInput.removeAttribute('required');
            customDateWrapper.classList.remove('show');
        });
    }

    btnAdicionarItem.addEventListener('click', () => addItemRow());

    deliveryDateInput.addEventListener('change', () => {
        document.querySelectorAll('#itens-wrapper .card').forEach(row => {
            const periodSelect = row.querySelector('.period-select');
            if (periodSelect && periodSelect.value && periodSelect.value !== 'custom') {
                updateNextExpectedDate(row);
            }
        });
    });

    @if(old('items'))
        const oldItems = @json(old('items'));
        if (Array.isArray(oldItems) && oldItems.length) {
            oldItems.forEach(item => addItemRow(item));
        } else {
            addItemRow();
        }
    @else
        addItemRow();
    @endif
</script>
@endsection