@php
    $payment ??= null;
    $editing = filled($payment?->id);

    // Controller should pass:
    // $accounts: bank/cash accounts user can select (ChartOfAccount rows)
    // $customers, $suppliers
    // $openSalesInvoices: list with id, invoice_no, total, outstanding
    // $openPurchaseInvoices: list with id, invoice_no, total, outstanding

    $oldAlloc = old('allocations');
    $allocations = $oldAlloc
        ? collect($oldAlloc)
        : collect($payment?->allocations ?? []);
@endphp

<div class="rounded-xl ring-1 ring-white/10 bg-black/10 p-4 space-y-4">

    {{-- Header fields --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

        <div>
            <label class="text-xs text-slate-300">Payment Type</label>
            <select name="payment_type" id="payment_type"
                    class="w-full mt-1 px-3 py-2 rounded-lg bg-black/30 text-slate-100 ring-1 ring-white/10 focus:ring-2 focus:ring-indigo-400/40 outline-none"
                    required>
                <option value="RECEIVE" @selected(old('payment_type', $payment?->payment_type) === 'RECEIVE')>Receive (Customer)</option>
                <option value="PAY" @selected(old('payment_type', $payment?->payment_type) === 'PAY')>Pay (Supplier)</option>
            </select>
        </div>

        <div>
            <label class="text-xs text-slate-300">Posting Date</label>
            <input type="date" name="posting_date"
                   value="{{ old('posting_date', optional($payment?->posting_date)->format('Y-m-d') ?? now()->toDateString()) }}"
                   class="w-full mt-1 px-3 py-2 rounded-lg bg-black/30 text-slate-100 ring-1 ring-white/10 focus:ring-2 focus:ring-indigo-400/40 outline-none"
                   required>
        </div>

        <div>
            <label class="text-xs text-slate-300">Payment Account (Bank/Cash)</label>
            <select name="payment_account_id" id="payment_account_id"
                    class="w-full mt-1 px-3 py-2 rounded-lg bg-black/30 text-slate-100 ring-1 ring-white/10 focus:ring-2 focus:ring-indigo-400/40 outline-none"
                    required>
                <option value="">Select account...</option>
                @foreach(($accounts ?? []) as $acc)
                    <option value="{{ $acc->id }}" @selected((string)old('payment_account_id', $payment?->payment_account_id) === (string)$acc->id)>
                        {{ $acc->code }} - {{ $acc->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="text-xs text-slate-300">Currency</label>
            <input name="currency" id="currency"
                   value="{{ old('currency', $payment?->currency ?? company_currency()) }}"
                   class="w-full mt-1 px-3 py-2 rounded-lg bg-black/30 text-slate-100 ring-1 ring-white/10 focus:ring-2 focus:ring-indigo-400/40 outline-none"
                   required>
        </div>

        <div>
            <label class="text-xs text-slate-300">Exchange Rate</label>
            <input name="exchange_rate" id="exchange_rate" step="0.00000001"
                   value="{{ old('exchange_rate', $payment?->exchange_rate ?? 1) }}"
                   class="w-full mt-1 px-3 py-2 rounded-lg bg-black/30 text-slate-100 ring-1 ring-white/10 focus:ring-2 focus:ring-indigo-400/40 outline-none"
                   required>
        </div>

        <div>
            <label class="text-xs text-slate-300">Amount</label>
            <input name="amount" id="amount" step="0.01"
                   value="{{ old('amount', $payment?->amount ?? 0) }}"
                   class="w-full mt-1 px-3 py-2 rounded-lg bg-black/30 text-slate-100 ring-1 ring-white/10 focus:ring-2 focus:ring-indigo-400/40 outline-none"
                   required>
            <div class="text-[11px] text-slate-400 mt-1">Allocated total must not exceed Amount.</div>
        </div>

        <div class="md:col-span-2">
            <label class="text-xs text-slate-300">Reference No</label>
            <input name="reference_no"
                   value="{{ old('reference_no', $payment?->reference_no ?? '') }}"
                   placeholder="Bank ref, receipt no, ecocash ref..."
                   class="w-full mt-1 px-3 py-2 rounded-lg bg-black/30 text-slate-100 placeholder:text-slate-400 ring-1 ring-white/10 focus:ring-2 focus:ring-indigo-400/40 outline-none">
        </div>

        <div>
            <label class="text-xs text-slate-300">Reference Date</label>
            <input type="date" name="reference_date"
                   value="{{ old('reference_date', optional($payment?->reference_date)->format('Y-m-d')) }}"
                   class="w-full mt-1 px-3 py-2 rounded-lg bg-black/30 text-slate-100 ring-1 ring-white/10 focus:ring-2 focus:ring-indigo-400/40 outline-none">
        </div>

        <div class="md:col-span-3">
            <label class="text-xs text-slate-300">Remarks</label>
            <input name="remarks"
                   value="{{ old('remarks', $payment?->remarks ?? '') }}"
                   class="w-full mt-1 px-3 py-2 rounded-lg bg-black/30 text-slate-100 placeholder:text-slate-400 ring-1 ring-white/10 focus:ring-2 focus:ring-indigo-400/40 outline-none">
        </div>
    </div>

    {{-- Party selector --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="md:col-span-1">
            <label class="text-xs text-slate-300">Party</label>

            {{-- customer --}}
            <select name="customer_id" id="customer_id"
                    class="w-full mt-1 px-3 py-2 rounded-lg bg-black/30 text-slate-100 ring-1 ring-white/10 focus:ring-2 focus:ring-indigo-400/40 outline-none">
                <option value="">Select customer...</option>
                @foreach(($customers ?? []) as $c)
                    <option value="{{ $c->id }}" @selected((string)old('customer_id', $payment?->customer_id) === (string)$c->id)>
                        {{ $c->name }}
                    </option>
                @endforeach
            </select>

            {{-- supplier --}}
            <select name="supplier_id" id="supplier_id"
                    class="w-full mt-1 px-3 py-2 rounded-lg bg-black/30 text-slate-100 ring-1 ring-white/10 focus:ring-2 focus:ring-indigo-400/40 outline-none">
                <option value="">Select supplier...</option>
                @foreach(($suppliers ?? []) as $s)
                    <option value="{{ $s->id }}" @selected((string)old('supplier_id', $payment?->supplier_id) === (string)$s->id)>
                        {{ $s->name }}
                    </option>
                @endforeach
            </select>

            <div class="text-[11px] text-slate-400 mt-1">
                Receive → Customer. Pay → Supplier.
            </div>
        </div>

        <div class="md:col-span-2 rounded-xl ring-1 ring-white/10 bg-black/10 p-3">
            <div class="text-xs text-slate-300 font-semibold">Allocation Summary</div>
            <div class="mt-2 grid grid-cols-1 sm:grid-cols-3 gap-3 text-sm">
                <div class="rounded-lg bg-white/5 ring-1 ring-white/10 p-3">
                    <div class="text-[11px] text-slate-400">Amount</div>
                    <div class="font-semibold" id="sum_amount">0.00</div>
                </div>
                <div class="rounded-lg bg-white/5 ring-1 ring-white/10 p-3">
                    <div class="text-[11px] text-slate-400">Allocated</div>
                    <div class="font-semibold" id="sum_allocated">0.00</div>
                </div>
                <div class="rounded-lg bg-white/5 ring-1 ring-white/10 p-3">
                    <div class="text-[11px] text-slate-400">Unallocated</div>
                    <div class="font-semibold" id="sum_unallocated">0.00</div>
                </div>
            </div>
        </div>
    </div>

</div>

{{-- Allocations table --}}
<div class="rounded-xl ring-1 ring-white/10 bg-black/10 overflow-hidden">
    <div class="p-4 border-b border-white/10 flex items-center justify-between">
        <div>
            <div class="font-semibold">Allocations</div>
            <div class="text-xs text-slate-300">Apply this payment to open invoices (auto enforces outstanding).</div>
        </div>
        <button type="button" id="btn_add_alloc"
                class="px-3 py-2 rounded-lg bg-white/10 ring-1 ring-white/10 hover:bg-white/15 text-sm">
            + Add Allocation
        </button>
    </div>

    <div class="overflow-auto">
        <table class="w-full text-sm" id="alloc_table">
            <thead class="bg-white/5">
                <tr>
                    <th class="p-3 text-left">Invoice</th>
                    <th class="p-3 text-right">Outstanding</th>
                    <th class="p-3 text-right w-56">Allocated</th>
                    <th class="p-3 text-right w-24">Remove</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-white/10" id="alloc_tbody">
                {{-- Existing allocations --}}
                @php($i = 0)
                @foreach($allocations as $a)
                    @php
                        $invType = is_array($a) ? ($a['invoice_type'] ?? '') : ($a->invoice_type ?? '');
                        $invId   = is_array($a) ? ($a['invoice_id'] ?? '') : ($a->invoice_id ?? '');
                        $alloc   = is_array($a) ? ($a['allocated_amount'] ?? 0) : ($a->allocated_amount ?? 0);
                    @endphp

                    <tr data-row="1">
                        <td class="p-3">
                            <select name="allocations[{{ $i }}][invoice_key]" class="alloc-invoice w-full px-3 py-2 rounded-lg bg-black/30 text-slate-100 ring-1 ring-white/10 focus:ring-2 focus:ring-indigo-400/40 outline-none">
                                <option value="">Select invoice...</option>

                                {{-- Sales invoices --}}
                                @foreach(($openSalesInvoices ?? []) as $si)
                                    @php($key = 'SI:' . $si->id)
                                    <option value="{{ $key }}"
                                            data-outstanding="{{ (float)$si->outstanding }}"
                                            @selected($invType === 'SalesInvoice' && (string)$invId === (string)$si->id)>
                                        {{ $si->invoice_no }} (Sales) • Out: {{ number_format((float)$si->outstanding,2) }}
                                    </option>
                                @endforeach

                                {{-- Purchase invoices --}}
                                @foreach(($openPurchaseInvoices ?? []) as $pi)
                                    @php($key = 'PI:' . $pi->id)
                                    <option value="{{ $key }}"
                                            data-outstanding="{{ (float)$pi->outstanding }}"
                                            @selected($invType === 'PurchaseInvoice' && (string)$invId === (string)$pi->id)>
                                        {{ $pi->invoice_no }} (Purchase) • Out: {{ number_format((float)$pi->outstanding,2) }}
                                    </option>
                                @endforeach
                            </select>

                            {{-- hidden structured fields (your request/service can parse these) --}}
                            <input type="hidden" class="alloc-invoice-type" name="allocations[{{ $i }}][invoice_type]" value="{{ $invType }}">
                            <input type="hidden" class="alloc-invoice-id" name="allocations[{{ $i }}][invoice_id]" value="{{ $invId }}">
                        </td>

                        <td class="p-3 text-right">
                            <span class="alloc-outstanding text-slate-200">0.00</span>
                        </td>

                        <td class="p-3 text-right">
                            <input name="allocations[{{ $i }}][allocated_amount]"
                                   value="{{ $alloc }}"
                                   class="alloc-amount w-full text-right px-3 py-2 rounded-lg bg-black/30 text-slate-100 ring-1 ring-white/10 focus:ring-2 focus:ring-indigo-400/40 outline-none"
                                   placeholder="0.00">
                            <div class="alloc-error text-[11px] text-rose-300 mt-1 hidden"></div>
                        </td>

                        <td class="p-3 text-right">
                            <button type="button" class="btn-remove px-3 py-2 rounded-lg bg-rose-500/15 ring-1 ring-rose-400/20 hover:bg-rose-500/20 text-sm">
                                ✕
                            </button>
                        </td>
                    </tr>
                    @php($i++)
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="p-3 text-[11px] text-slate-400 border-t border-white/10">
        Rule: Allocated per invoice cannot exceed invoice outstanding. Total allocated cannot exceed Amount.
    </div>
</div>

{{-- Template row for JS --}}
<template id="alloc_row_tpl">
    <tr data-row="1">
        <td class="p-3">
            <select class="alloc-invoice w-full px-3 py-2 rounded-lg bg-black/30 text-slate-100 ring-1 ring-white/10 focus:ring-2 focus:ring-indigo-400/40 outline-none">
                <option value="">Select invoice...</option>
            </select>

            <input type="hidden" class="alloc-invoice-type">
            <input type="hidden" class="alloc-invoice-id">
        </td>

        <td class="p-3 text-right">
            <span class="alloc-outstanding text-slate-200">0.00</span>
        </td>

        <td class="p-3 text-right">
            <input class="alloc-amount w-full text-right px-3 py-2 rounded-lg bg-black/30 text-slate-100 ring-1 ring-white/10 focus:ring-2 focus:ring-indigo-400/40 outline-none"
                   placeholder="0.00">
            <div class="alloc-error text-[11px] text-rose-300 mt-1 hidden"></div>
        </td>

        <td class="p-3 text-right">
            <button type="button" class="btn-remove px-3 py-2 rounded-lg bg-rose-500/15 ring-1 ring-rose-400/20 hover:bg-rose-500/20 text-sm">✕</button>
        </td>
    </tr>
</template>

{{-- Provide invoices list as JSON for JS --}}
<script>
window.__PAYMENT_ENTRY__ = {
    openSalesInvoices: @json(($openSalesInvoices ?? [])->map(fn($x)=>[
        'key' => 'SI:' . $x->id,
        'type' => 'SalesInvoice',
        'id' => $x->id,
        'no' => $x->invoice_no,
        'outstanding' => (float)$x->outstanding,
    ])),
    openPurchaseInvoices: @json(($openPurchaseInvoices ?? [])->map(fn($x)=>[
        'key' => 'PI:' . $x->id,
        'type' => 'PurchaseInvoice',
        'id' => $x->id,
        'no' => $x->invoice_no,
        'outstanding' => (float)$x->outstanding,
    ])),
};
</script>
