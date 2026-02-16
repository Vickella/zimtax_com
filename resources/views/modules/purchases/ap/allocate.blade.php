{{-- resources/views/modules/purchases/ap/allocate.blade.php --}}
@extends('layouts.erp')

@section('content')
<div class="p-6">
    <div class="flex items-center justify-between mb-4">
        <div>
            <h1 class="text-2xl font-semibold">Allocate Supplier Payment</h1>
            <p class="text-sm text-gray-500">Allocate a submitted supplier payment to open purchase invoices.</p>
        </div>
        <a href="{{ route('modules.purchases.ap.aging') }}" class="px-4 py-2 border rounded">Back</a>
    </div>

    <form method="POST" action="{{ route('modules.purchases.ap.allocate.store') }}" class="border rounded p-4">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-1">Payment (SUBMITTED)</label>
                <select name="payment_id" id="payment_id" class="w-full border rounded px-3 py-2" required>
                    <option value="">-- select --</option>
                    @foreach($payments as $p)
                        <option value="{{ $p->id }}"
                            data-supplier="{{ $p->party_id }}"
                            data-amount="{{ $p->amount }}"
                        >
                            {{ $p->payment_no }} • Supplier #{{ $p->party_id }} • {{ number_format((float)$p->amount,2) }} {{ $p->currency }} • {{ $p->posting_date?->format('Y-m-d') }}
                        </option>
                    @endforeach
                </select>
                @error('payment_id') <div class="text-sm text-red-600 mt-1">{{ $message }}</div> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Supplier (auto)</label>
                <select id="supplier_id" class="w-full border rounded px-3 py-2" disabled>
                    <option value="">--</option>
                    @foreach($suppliers as $s)
                        <option value="{{ $s->id }}">{{ $s->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="mt-6 border rounded p-4">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-lg font-medium">Open Invoices</h3>
                <div class="text-sm text-gray-600" id="remainingBox"></div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm" id="invTable">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="text-left p-2">Invoice</th>
                            <th class="text-left p-2">Posting</th>
                            <th class="text-left p-2">Due</th>
                            <th class="text-right p-2">Total</th>
                            <th class="text-right p-2">Allocate</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr><td class="p-4 text-gray-500" colspan="5">Select a payment to load invoices.</td></tr>
                    </tbody>
                </table>
            </div>

            @error('allocations') <div class="text-sm text-red-600 mt-2">{{ $message }}</div> @enderror
        </div>

        <div class="mt-6 flex gap-2">
            <button class="px-4 py-2 border rounded bg-black text-white" type="submit">Save Allocations</button>
            <a class="px-4 py-2 border rounded" href="{{ route('modules.purchases.ap.aging') }}">Cancel</a>
        </div>
    </form>
</div>

<script>
const paymentSelect = document.getElementById('payment_id');
const supplierSelect = document.getElementById('supplier_id');
const tbody = document.querySelector('#invTable tbody');
const remainingBox = document.getElementById('remainingBox');

let paymentAmount = 0;

function fmt(n){ return (Number(n)||0).toLocaleString(undefined,{minimumFractionDigits:2,maximumFractionDigits:2}); }

paymentSelect.addEventListener('change', async () => {
    const opt = paymentSelect.options[paymentSelect.selectedIndex];
    const supplierId = opt?.dataset?.supplier || '';
    paymentAmount = Number(opt?.dataset?.amount || 0);

    // set supplier dropdown
    supplierSelect.value = supplierId;

    tbody.innerHTML = `<tr><td class="p-4 text-gray-500" colspan="5">Loading...</td></tr>`;
    remainingBox.textContent = `Payment amount: ${fmt(paymentAmount)} (allocate across invoices)`;

    if(!supplierId){
        tbody.innerHTML = `<tr><td class="p-4 text-gray-500" colspan="5">Select a payment.</td></tr>`;
        return;
    }

    const url = `{{ route('modules.purchases.ap.open_invoices', ['supplierId' => 'SUP']) }}`.replace('SUP', supplierId);
    const res = await fetch(url);
    const invoices = await res.json();

    if(!Array.isArray(invoices) || invoices.length === 0){
        tbody.innerHTML = `<tr><td class="p-4 text-gray-500" colspan="5">No open invoices found.</td></tr>`;
        return;
    }

    tbody.innerHTML = '';
    invoices.forEach((inv, i) => {
        const tr = document.createElement('tr');
        tr.className = 'border-t';
        tr.innerHTML = `
            <td class="p-2">${inv.invoice_no}</td>
            <td class="p-2">${inv.posting_date ?? ''}</td>
            <td class="p-2">${inv.due_date ?? '-'}</td>
            <td class="p-2 text-right">${fmt(inv.total)} ${inv.currency}</td>
            <td class="p-2 text-right">
                <input type="hidden" name="allocations[${i}][purchase_invoice_id]" value="${inv.id}">
                <input type="number" step="0.01" min="0" name="allocations[${i}][amount]" value="0" class="w-28 border rounded px-2 py-1 text-right">
            </td>
        `;
        tbody.appendChild(tr);
    });
});
</script>
@endsection
